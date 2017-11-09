<?php
/**
 * JSON RPC 2.0 client and server implementation for PHP
 *
 * @author      Michal Szewczyk <ms@msworks.pl>
 * @copyright   Michal Szewczyk
 * @license     MIT
 */
declare(strict_types=1);

namespace MS\Json\Rpc\Server;


use MS\Json\Rpc\Server\Configuration\ParamsMap;
use MS\Json\Rpc\Server\Configuration\SchemaMap;
use MS\Json\Rpc\Server\Exceptions\ServerErrorException;
use MS\Json\Rpc\Server\Handlers\AbstractNamespaceHandler;

class Configuration
{
    /**
     * Server URL
     *
     * @var string
     */
    private $serverUrl = '';
    /**
     * Namespace map
     *
     * @var array
     */
    private $namespaceMap = [];
    /**
     * @var SchemaMap
     */
    private $schemaMap;
    /**
     * @var ParamsMap
     */
    private $paramsMap;
    /**
     * Current namespace
     *
     * @var string
     */
    private $currentNamespace = '';

    /**
     * Configuration constructor
     *
     * @param   array $namespaceMap Namespace map
     */
    public function __construct(array $namespaceMap)
    {
        $this->setServerUrl();
        $this->setCurrentNamespace();
        $this->setNamespaceMap($namespaceMap);
    }

    /**
     * Sets server url
     *
     * @return  void
     */
    private function setServerUrl(): void
    {
        $options = ['options' => ['default' => false]];
        $protocol = \filter_input(\INPUT_SERVER, 'HTTPS', \FILTER_DEFAULT, $options) === true ? 'https' : 'http';
        $options = ['options' => ['default' => '']];
        $serverName = \filter_input(\INPUT_SERVER, 'SERVER_NAME', \FILTER_DEFAULT, $options);
        $scriptName = \filter_input(\INPUT_SERVER, 'SCRIPT_NAME', \FILTER_DEFAULT, $options);

        $requestUri = \str_replace('/index.php', '', $scriptName);
        $this->serverUrl = \sprintf('%s://%s%s/', $protocol, $serverName, $requestUri);
    }

    /**
     * Sets current namespace name
     *
     * @return  void
     */
    private function setCurrentNamespace(): void
    {
        $options = ['options' => ['default' => '/system']];
        $namespace = \filter_input(\INPUT_SERVER, 'PATH_INFO', \FILTER_DEFAULT, $options);
        $namespace = \ltrim($namespace, '/');
        $namespacePattern = '/^[a-z][a-z0-9]+$/i';
        $this->currentNamespace = \preg_match($namespacePattern, $namespace) ? $namespace : 'system';
    }

    /**
     * Builds server configuration array based on namespace map
     *
     * @param   array $namespaceMap Namespace map
     * @return  Configuration
     */
    public function setNamespaceMap(array $namespaceMap): Configuration
    {
        $this->namespaceMap = $namespaceMap;
        $this->schemaMap = new SchemaMap($namespaceMap, $this->serverUrl);
        $this->paramsMap = new ParamsMap($namespaceMap);
        return $this;
    }

    /**
     * Returns current namespace name
     *
     * @return  string
     */
    public function getCurrentNamespaceName(): string
    {
        return $this->currentNamespace;
    }

    /**
     * Returns namespace map
     *
     * @return array
     */
    public function getNamespaceMap(): array
    {
        return $this->namespaceMap;
    }

    /**
     * Returns namespace url
     *
     * @param string $nsName
     * @return string
     */
    public function getNamespaceUrl(string $nsName): string
    {
        return \sprintf('%s%s/', $this->serverUrl, $nsName);
    }

    /**
     * Returns schema classes array for namespace method
     *
     * @param string $namespaceName
     * @param string $methodName
     * @return array
     */
    public function getSchemaClasses(string $namespaceName, string $methodName): array
    {
        return $this->schemaMap->getClasses($namespaceName, $methodName);
    }

    /**
     * Returns schema urls array for namespace method
     *
     * @param string $namespaceName
     * @param string $methodName
     * @return array
     */
    public function getSchemaUrls(string $namespaceName, string $methodName): array
    {
        return $this->schemaMap->getUrls($namespaceName, $methodName);
    }

    /**
     * Returns input params array for namespace method
     *
     * @param string $namespaceName
     * @param string $methodName
     * @return array
     */
    public function getInputParams(string $namespaceName, string $methodName): array
    {
        return $this->paramsMap->getParams($namespaceName, $methodName);
    }

    /**
     * Returns handler for current namespace
     *
     * @throws  ServerErrorException
     * @return  AbstractNamespaceHandler|null
     */
    public function getNamespaceHandler(): ?AbstractNamespaceHandler
    {
        $currentNamespaceName = $this->getCurrentNamespaceName();
        if (!\array_key_exists($currentNamespaceName, $this->namespaceMap)) {
            $msg = \sprintf('Handler for namespace \'%s\' was not found', $this->currentNamespace);
            throw new ServerErrorException($msg, -32000);
        }

        $namespaceHandlerClass = $this->namespaceMap[$currentNamespaceName];
        $namespaceHandler = new $namespaceHandlerClass($this);

        if (!($namespaceHandler instanceof AbstractNamespaceHandler)) {
            $msg = \sprintf(
                '%s MUST be an instance of \MS\Json\Rpc\Server\Handlers\AbstractNamespaceHandler',
                $namespaceHandlerClass
            );
            throw new ServerErrorException($msg, -32001);
        }
        return $namespaceHandler;
    }
}
