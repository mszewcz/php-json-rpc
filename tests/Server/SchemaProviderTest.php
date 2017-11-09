<?php
/**
 * JSON RPC 2.0 client and server implementation for PHP
 *
 * @author      Michal Szewczyk <ms@msworks.pl>
 * @copyright   Michal Szewczyk
 * @license     MIT
 */
declare(strict_types=1);

namespace Server;

use MS\Json\Rpc\Server\Configuration;
use MS\Json\Rpc\Server\Handlers\AbstractNamespaceHandler;
use MS\Json\Rpc\Server\SchemaProvider;
use PHPUnit\Framework\TestCase;

class SchemaDefaultNamespaceHandler extends AbstractNamespaceHandler
{
    protected function sum(int $paramA, int $paramB): int
    {
        return $paramA + $paramB;
    }
}

class SchemaProviderTest extends TestCase
{
    /**
     * @var \MS\Json\Rpc\Server\SchemaProvider
     */
    private $schemaProvider;
    /**
     * @var \MS\Json\Rpc\Server\SchemaProvider
     */
    private $schemaProviderDefault;

    private $nsMap = [
        'system' => 'MS\Json\Rpc\Server\Handlers\SystemNamespaceHandler',
    ];
    private $nsMapDefault = [
        'system' => 'Server\SchemaDefaultNamespaceHandler',
    ];

    public function setUp()
    {
        $config = new Configuration($this->nsMap);
        $this->schemaProvider = new SchemaProvider($config);
        $configDefault = new Configuration($this->nsMapDefault);
        $this->schemaProviderDefault = new SchemaProvider($configDefault);
    }

    public function testCreateClass()
    {
        $this->assertInstanceOf('MS\Json\Rpc\Server\SchemaProvider', $this->schemaProvider);
        $this->assertInstanceOf('MS\Json\Rpc\Server\SchemaProvider', $this->schemaProviderDefault);
    }

    /**
     * @depends testCreateClass
     */
    public function testGetSchema()
    {
        $expected = [
            'type'       => 'object',
            'properties' => [
                'jsonrpc' => ['const' => '2.0'],
                'method'  => ['const' => 'getConfiguration'],
                'id'  => [
                    'oneOf' => [
                        ['type'=>'integer'],
                        ['type'=>'string']
                    ]
                ],
            ],
            'required'   => ['jsonrpc', 'method', 'id'],
        ];
        $this->assertEquals($expected, $this->schemaProvider->getSchema('system', 'getConfiguration', 'input'));

        $expected = '{"type": "object", "properties": {"jsonrpc": {"const": "2.0"},"id": {';
        $expected .= '"oneOf": [{"type":"integer"},{"type":"string"},{"type":"null"}]},';
        $expected .= '"result": {"type": "object", "properties": {"serverConfiguration": {';
        $expected .= '"$ref": "#/definitions/serverConfiguration"}, "serverTimestamp": { "type": "integer"}},';
        $expected .= '"additionalProperties": false,"required": ["serverConfiguration", "serverTimestamp"]},';
        $expected .= '"required": ["jsonrpc", "id", "result"]},"definitions": {"serverConfiguration": {';
        $expected .= '"type": "object","properties": {"services": {"$ref": "#/definitions/services"}},';
        $expected .= '"additionalProperties": false,"required": ["services"]},"services": {"type": "object",';
        $expected .= '"patternProperties": {"^[a-z](?i:[a-z0-9]+)$": {"$ref": "#/definitions/service"}},';
        $expected .= '"additionalProperties": false},"service": {"type": "object","patternProperties": {';
        $expected .= '"^[a-z](?i:[a-z0-9]+)$": {"$ref": "#/definitions/method"}},"additionalProperties": false},';
        $expected .= '"method": {"type": "object","properties": {"inputSchema": {"type": "string","format": "uri"';
        $expected .= '},"outputSchema": {"type": "string","format": "uri"},"url": {"type": "string","format": "uri"';
        $expected .= '}},"additionalProperties": false,"required": "url"}}}';
        $this->assertEquals(\json_decode($expected, true), $this->schemaProvider->getSchema('system', 'getConfiguration', 'output'));
    }

    /**
     * @depends testCreateClass
     */
    public function testGetDefaultSchema()
    {
        $expected = [
            'type'        => 'object',
            'properties'  => [
                'jsonrpc' => ['const' => '2.0'],
                'method'  => ['type' => 'string'],
                'params'  => [
                    'oneOf' => [
                        ['$ref' => '#/definitions/paramsAsArray'], ['$ref' => '#/definitions/paramsAsObject']
                    ],
                ],
                'id'      => [
                    'oneOf' => [
                        ['type' => 'integer'], ['type' => 'string']
                    ],
                ],
            ],
            'required'    => ['jsonrpc', 'method'],
            'definitions' => [
                'paramsAsArray'  => [
                    'type'            => 'array',
                    'items'           => [['type' => 'integer'], ['type' => 'integer']],
                    'additionalItems' => false,
                ],
                'paramsAsObject' => [
                    'type'                 => 'object',
                    'properties'           => [
                        'paramA' => ['type' => 'integer'],
                        'paramB' => ['type' => 'integer'],
                    ],
                    'additionalProperties' => false,
                    'required'             => ['paramA', 'paramB'],
                ],
            ],
        ];
        $this->assertEquals($expected, $this->schemaProviderDefault->getSchema('system', 'sum', 'input'));

        $expected = [
            'oneOf' => [
                [
                    'type'       => 'object',
                    'properties' => [
                        'jsonrpc' => [
                            'const' => '2.0',
                        ],
                        'result'  => [
                            'oneOf' => [
                                ['type' => 'boolean'], ['type' => 'number'], ['type' => 'integer'], ['type' => 'float'],
                                ['type' => 'string'], ['type' => 'array'], ['type' => 'object'], ['type' => 'null'],
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
        $this->assertEquals($expected, $this->schemaProviderDefault->getSchema('system', 'sum', 'output'));

        $expected = [];
        $this->assertEquals($expected, $this->schemaProviderDefault->getSchema('system', 'sum', 'unknown'));

        $expected = [];
        $this->assertEquals($expected, $this->schemaProviderDefault->getSchema('system', 'unknown', 'input'));

        $expected = [];
        $this->assertEquals($expected, $this->schemaProviderDefault->getSchema('unknown', 'sum', 'input'));

        $expected = [];
        $this->assertEquals($expected, $this->schemaProviderDefault->getSchema('system', 'unknown', 'output'));

        $expected = [];
        $this->assertEquals($expected, $this->schemaProviderDefault->getSchema('unknown', 'sum', 'output'));
    }
}
