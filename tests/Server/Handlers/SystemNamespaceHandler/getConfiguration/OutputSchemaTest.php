<?php
/**
 * JSON RPC 2.0 client and server implementation for PHP
 *
 * @author      Michal Szewczyk <ms@msworks.pl>
 * @copyright   Michal Szewczyk
 * @license     MIT
 */
declare(strict_types=1);

namespace Server\Handlers\SystemNamespaceHandler\getConfiguration;

use MS\Json\Rpc\Server\Handlers\SystemNamespaceHandler\GetConfiguration\OutputSchema;
use PHPUnit\Framework\TestCase;

class OutputSchemaTest extends TestCase
{
    /**
     * @var \MS\Json\Rpc\Server\Handlers\SystemNamespaceHandler\GetConfiguration\OutputSchema
     */
    private $outputSchema;

    public function setUp()
    {
        $this->outputSchema = new OutputSchema();
    }

    public function testCreateClass()
    {
        $this->assertInstanceOf(
            'MS\Json\Rpc\Server\Handlers\SystemNamespaceHandler\GetConfiguration\OutputSchema',
            $this->outputSchema
        );
    }

    public function testGet()
    {
        $expected = '{"type": "object", "properties": {"jsonrpc": {"const": "2.0"},"id": {';
        $expected.= '"oneOf": [{"type":"integer"},{"type":"string"},{"type":"null"}]},';
        $expected.= '"result": {"type": "object", "properties": {"serverConfiguration": {';
        $expected.= '"$ref": "#/definitions/serverConfiguration"}, "serverTimestamp": { "type": "integer"}},';
        $expected.= '"additionalProperties": false,"required": ["serverConfiguration", "serverTimestamp"]},';
        $expected.= '"required": ["jsonrpc", "id", "result"]},"definitions": {"serverConfiguration": {';
        $expected.= '"type": "object","properties": {"services": {"$ref": "#/definitions/services"}},';
        $expected.= '"additionalProperties": false,"required": ["services"]},"services": {"type": "object",';
        $expected.= '"patternProperties": {"^[a-z](?i:[a-z0-9]+)$": {"$ref": "#/definitions/service"}},';
        $expected.= '"additionalProperties": false},"service": {"type": "object","patternProperties": {';
        $expected.= '"^[a-z](?i:[a-z0-9]+)$": {"$ref": "#/definitions/method"}},"additionalProperties": false},';
        $expected.= '"method": {"type": "object","properties": {"inputSchema": {"type": "string","format": "uri"';
        $expected.= '},"outputSchema": {"type": "string","format": "uri"},"url": {"type": "string","format": "uri"';
        $expected.= '}},"additionalProperties": false,"required": "url"}}}';

        $this->assertEquals(\json_decode($expected), \json_decode($this->outputSchema->get()));
    }
}
