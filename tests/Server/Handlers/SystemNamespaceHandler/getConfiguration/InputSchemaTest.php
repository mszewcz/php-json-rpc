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

use MS\Json\Rpc\Server\Handlers\SystemNamespaceHandler\GetConfiguration\InputSchema;
use PHPUnit\Framework\TestCase;

class InputSchemaTest extends TestCase
{
    /**
     * @var \MS\Json\Rpc\Server\Handlers\SystemNamespaceHandler\GetConfiguration\InputSchema
     */
    private $inputSchema;

    public function setUp()
    {
        $this->inputSchema = new InputSchema();
    }

    public function testCreateClass()
    {
        $this->assertInstanceOf(
            'MS\Json\Rpc\Server\Handlers\SystemNamespaceHandler\GetConfiguration\InputSchema',
            $this->inputSchema
        );
    }

    public function testGet()
    {
        $expected = '{"type": "object", "properties": {"jsonrpc": {"const": "2.0"},';
        $expected.= '"method": {"const": "getConfiguration"}, "id": {"oneOf":[{"type": "integer"},{"type": "string"}]}';
        $expected.= '},"required": ["jsonrpc", "method", "id"]}';
        $this->assertEquals(\json_decode($expected), \json_decode($this->inputSchema->get()));
    }
}
