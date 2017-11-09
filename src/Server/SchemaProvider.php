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


use MS\Json\Utils\Utils;

class SchemaProvider
{
    /**
     * @var Configuration
     */
    private $configuration;
    /**
     * @var Utils
     */
    private $utils;

    /**
     * SchemaProvider constructor.
     *
     * @param Configuration $configuration
     */
    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
        $this->utils = new Utils();
    }

    /**
     * Returns validation schema for provided namespace, method and type
     *
     * @param string $nsName
     * @param string $methodName
     * @param string $schemaType
     * @return array
     * @throws \MS\Json\Utils\Exceptions\DecodingException
     */
    public function getSchema(string $nsName, string $methodName, string $schemaType): array
    {
        $schemaClasses = $this->configuration->getSchemaClasses($nsName, $methodName);
        if (\array_key_exists($schemaType, $schemaClasses)
            && ($schemaClasses[$schemaType] !== null)
            && \class_exists($schemaClasses[$schemaType])
        ) {
            /** @var \MS\Json\Rpc\Server\SchemaInterface $schemaClass */
            $schemaClass = new $schemaClasses[$schemaType];
            $schema = $schemaClass->get();
            return $this->utils->decode($schema);
        }
        return $this->getDefaultSchema($nsName, $methodName, $schemaType);
    }

    /**
     * Returns default schema for provided namespace, method and type
     *
     * @param string $nsName
     * @param string $methodName
     * @param string $schemaType
     * @return array
     */
    private function getDefaultSchema(string $nsName, string $methodName, string $schemaType): array
    {
        switch ($schemaType) {
            case 'input':
                $schema = $this->getDefaultInputSchema($nsName, $methodName);
                break;
            case 'output':
                $schema = $this->getDefaultOutputSchema($nsName, $methodName);
                break;
            default:
                $schema = [];
                break;
        }
        return $schema;
    }

    /**
     * Returns default input schema for provided namespace amd method
     *
     * @param string $nsName
     * @param string $methodName
     * @return array
     */
    private function getDefaultInputSchema(string $nsName, string $methodName): array
    {
        $namespaceMap = $this->configuration->getNamespaceMap();
        if (\array_key_exists($nsName, $namespaceMap) && \method_exists($namespaceMap[$nsName], $methodName)) {
            $reflectionMethod = new \ReflectionMethod($namespaceMap[$nsName], $methodName);
            $methodParams = $reflectionMethod->getParameters();
            $properties = $items = $required = [];

            foreach ($methodParams as $param) {
                $paramName = $param->getName();
                $paramType = $param->getType()->getName();
                $paramRequired = !$param->isOptional();

                if ($paramType === 'int') {
                    $paramType = 'integer';
                }

                $properties[$paramName] = ['type' => $paramType];
                $items[] = ['type' => $paramType];
                if ($paramRequired) {
                    $required[] = $paramName;
                }
            }

            $paramsAsArray = [
                'type'            => 'array',
                'items'           => $items,
                'additionalItems' => false,
            ];

            if (\count($items) === 0) {
                unset($paramsAsArray['items']);
            }

            $paramsAsObject = [
                'type'                 => 'object',
                'properties'           => $properties,
                'additionalProperties' => false,
                'required'             => $required,
            ];

            if (\count($properties) === 0) {
                unset($paramsAsObject['properties']);
            }
            if (\count($required) === 0) {
                unset($paramsAsObject['required']);
            }

            return [
                'definitions' => [
                    'paramsAsArray'  => $paramsAsArray,
                    'paramsAsObject' => $paramsAsObject,
                ],
                'type'        => 'object',
                'properties'  => [
                    'jsonrpc' => [
                        'const' => '2.0',
                    ],
                    'method'  => [
                        'type' => 'string',
                    ],
                    'params'  => [
                        'oneOf' => [
                            ['$ref' => '#/definitions/paramsAsArray'],
                            ['$ref' => '#/definitions/paramsAsObject'],
                        ],
                    ],
                    'id'      => [
                        'oneOf' => [
                            ['type' => 'integer'],
                            ['type' => 'string'],
                        ],
                    ],
                ],
                'required'    => ['jsonrpc', 'method'],
            ];
        }
        return [];
    }

    /**
     * Returns default output schema for all namespaces and methods
     *
     * @return array
     */
    private function getDefaultOutputSchema(string $nsName, string $methodName): array
    {
        $namespaceMap = $this->configuration->getNamespaceMap();
        if (\array_key_exists($nsName, $namespaceMap) && \method_exists($namespaceMap[$nsName], $methodName)) {
            return [
                'oneOf' => [
                    [
                        'type'       => 'object',
                        'properties' => [
                            'jsonrpc' => [
                                'const' => '2.0',
                            ],
                            'result'  => [
                                'oneOf' => [
                                    ['type' => 'boolean'], ['type' => 'number'], ['type' => 'integer'],
                                    ['type' => 'float'], ['type' => 'string'], ['type' => 'array'],
                                    ['type' => 'object'], ['type' => 'null'],
                                ],
                            ],
                            'id'      => [
                                'oneOf' => [
                                    ['type' => 'integer'], ['type' => 'string'],
                                ],
                            ],
                        ],
                        'required'   => ['jsonrpc', 'result', 'id'],
                    ],
                    [
                        'type'       => 'object',
                        'properties' => [
                            'jsonrpc' => [
                                'const' => '2.0',
                            ],
                            'error'   => [
                                'type'       => 'object',
                                'properties' => [
                                    'code'    => [
                                        'type' => 'integer',
                                    ],
                                    'message' => [
                                        'type' => 'string',
                                    ],
                                ],
                                'required'   => ['code', 'message'],
                            ],
                            'id'      => [
                                'oneOf' => [
                                    ['type' => 'integer'], ['type' => 'string'], ['type' => 'null'],
                                ],
                            ],
                        ],
                        'required'   => ['jsonrpc', 'error', 'id'],
                    ],
                    [
                        'type' => 'null',
                    ],
                ],
            ];
        }
        return [];
    }
}
