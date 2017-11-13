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


class ParamsMap
{
    /**
     * @var array
     */
    private $namespaceMap = [];
    /**
     * @var array
     */
    private $paramsMap = [];

    /**
     * Schemas constructor.
     *
     * @param array  $namespaceMap
     */
    public function __construct(array $namespaceMap)
    {
        $this->namespaceMap = $namespaceMap;
        $this->buildParamsMap();
    }

    /**
     * Builds params map
     *
     * @return void
     */
    private function buildParamsMap(): void
    {
        foreach ($this->namespaceMap as $nsName => $nsHandlerClassName) {
            $reflectionClass = new \ReflectionClass($nsHandlerClassName);
            $classMethods = $reflectionClass->getMethods(\ReflectionMethod::IS_PROTECTED);

            foreach ($classMethods as $method) {
                $methodName = $method->name;
                $reflectionMethod = new \ReflectionMethod($nsHandlerClassName, $methodName);
                $methodParams = $reflectionMethod->getParameters();

                foreach ($methodParams as $param) {
                    $this->paramsMap[$nsName][$methodName][] = $param->name;
                }
            }
        }
    }

    /**
     * Returns array of params for provided namespace and method
     *
     * @param string $nsName
     * @param string $methodName
     * @return array
     */
    public function getParams(string $nsName, string $methodName): array
    {
        if (!\array_key_exists($nsName, $this->paramsMap)) {
            return [];
        }
        if (!\array_key_exists($methodName, $this->paramsMap[$nsName])) {
            return [];
        }
        return $this->paramsMap[$nsName][$methodName];
    }
}
