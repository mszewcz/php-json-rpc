<?php
/**
 * JSON RPC 2.0 client and server implementation for PHP
 *
 * @author      Michal Szewczyk <ms@msworks.pl>
 * @copyright   Michal Szewczyk
 * @license     MIT
 */
declare(strict_types=1);

namespace MS\Json\Rpc\Server\Handlers;


use MS\Json\Rpc\Server\Configuration;

final class SystemNamespaceHandler extends AbstractNamespaceHandler
{
    /**
     * SystemNamespaceHandler constructor.
     *
     * @param Configuration $config
     */
    public function __construct(Configuration $config)
    {
        parent::__construct($config);
    }

    /**
     * API method: returns server configuration (available services) and server timestamp
     *
     * @inputSchema \MS\Json\Rpc\Server\Handlers\SystemNamespaceHandler\GetConfiguration\InputSchema
     * @outputSchema \MS\Json\Rpc\Server\Handlers\SystemNamespaceHandler\GetConfiguration\OutputSchema
     * @return  array
     */
    protected function getConfiguration(): array
    {
        $namespaceMap = $this->config->getNamespaceMap();
        $services = [];

        foreach ($namespaceMap as $nsName => $nsHandlerClassName) {
            $reflectionClass = new \ReflectionClass($nsHandlerClassName);
            $classMethods = $reflectionClass->getMethods(\ReflectionMethod::IS_PROTECTED);

            foreach ($classMethods as $method) {
                $methodName = $method->name;
                $namespaceUrl = $this->config->getNamespaceUrl($nsName);
                $schemaUrls = $this->config->getSchemaUrls($nsName, $methodName);

                $services[$nsName][$methodName] = [
                    'inputSchema' => $schemaUrls['input'],
                    'outputSchema' => $schemaUrls['output'],
                    'url' => $namespaceUrl
                ];
            }
        }

        return [
            'serverConfiguration' => ['services' => $services],
            'serverTimestamp'     => \time(),
        ];
    }
}
