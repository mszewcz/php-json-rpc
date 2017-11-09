<?php
/**
 * JSON RPC 2.0 client and server implementation for PHP
 *
 * @author      Michal Szewczyk <ms@msworks.pl>
 * @copyright   Michal Szewczyk
 * @license     MIT
 */
declare(strict_types=1);

namespace MS\Json\Rpc\Server\Configuration;


class SchemaMap
{
    /**
     * @var array
     */
    private $namespaceMap = [];
    /**
     * @var array
     */
    private $schemaClasses = [];
    /**
     * @var array
     */
    private $schemaUrls = [];
    /**
     * @var string
     */
    private $serverUrl = '';

    /**
     * Schemas constructor.
     *
     * @param array  $namespaceMap
     * @param string $serverUrl
     */
    public function __construct(array $namespaceMap, string $serverUrl)
    {
        $this->namespaceMap = $namespaceMap;
        $this->serverUrl = $serverUrl;
        $this->buildSchemaMap();
    }

    /**
     * Builds schema maps
     *
     * @return void
     */
    private function buildSchemaMap(): void
    {
        foreach ($this->namespaceMap as $nsName => $nsHandlerClassName) {
            $reflectionClass = new \ReflectionClass($nsHandlerClassName);
            $classMethods = $reflectionClass->getMethods(\ReflectionMethod::IS_PROTECTED);

            foreach ($classMethods as $method) {
                $methodName = $method->name;

                $inputSchemaClass = $this->getInputSchemaClass($method);
                $outputSchemaClass = $this->getOutputSchemaClass($method);
                $inputSchemaUrl = \sprintf('%sschemas/%s/%s/input-schema.json', $this->serverUrl, $nsName, $methodName);
                $outputSchemaUrl = \sprintf('%sschemas/%s/%s/output-schema.json', $this->serverUrl, $nsName, $methodName);

                $this->schemaClasses[$nsName][$methodName] = [
                    'input'  => $inputSchemaClass,
                    'output' => $outputSchemaClass,
                ];
                $this->schemaUrls[$nsName][$methodName] = [
                    'input'  => $inputSchemaUrl,
                    'output' => $outputSchemaUrl,
                ];
            }
        }
    }

    /**
     * Returns input schema class name
     *
     * @param \ReflectionMethod $method
     * @return null|string
     */
    private function getInputSchemaClass(\ReflectionMethod $method): ?string
    {
        $docComment = $method->getDocComment() ?: '';
        preg_match('/^[ \t]+\*[ \t]+@inputSchema[ \t]+(.*)$/mi', $docComment, $matches);
        return isset($matches[1]) && \class_exists($matches[1]) ? $matches[1] : null;
    }

    /**
     * Returns output schema class name
     *
     * @param \ReflectionMethod $method
     * @return null|string
     */
    private function getOutputSchemaClass(\ReflectionMethod $method): ?string
    {
        $docComment = $method->getDocComment() ?: '';
        preg_match('/^[ \t]+\*[ \t]+@outputSchema[ \t]+(.*)$/mi', $docComment, $matches);
        return isset($matches[1]) && \class_exists($matches[1]) ? $matches[1] : null;
    }

    /**
     * Returns schema classes for for provided namespace and method
     *
     * @param string $nsName
     * @param string $methodName
     * @return array
     */
    public function getClasses(string $nsName, string $methodName): array
    {
        if (!\array_key_exists($nsName, $this->schemaClasses)) {
            return ['input' => null, 'output' => null];
        }
        if (!\array_key_exists($methodName, $this->schemaClasses[$nsName])) {
            return ['input' => null, 'output' => null];
        }
        return $this->schemaClasses[$nsName][$methodName];
    }

    /**
     * Returns schema urls for given for provided namespace and method
     *
     * @param string $nsName
     * @param string $methodName
     * @return array
     */
    public function getUrls(string $nsName, string $methodName): array
    {
        if (!\array_key_exists($nsName, $this->schemaUrls)) {
            return ['input' => null, 'output' => null];
        }
        if (!\array_key_exists($methodName, $this->schemaUrls[$nsName])) {
            return ['input' => null, 'output' => null];
        }
        return $this->schemaUrls[$nsName][$methodName];
    }
}
