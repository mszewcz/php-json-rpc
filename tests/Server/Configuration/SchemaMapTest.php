<?php
/**
 * JSON RPC 2.0 client and server implementation for PHP
 *
 * @author      Michal Szewczyk <ms@msworks.pl>
 * @copyright   Michal Szewczyk
 * @license     MIT
 */
declare(strict_types=1);

namespace Server\Configuration;

use MS\Json\Rpc\Server\Configuration\SchemaMap;
use MS\Json\Rpc\Server\Handlers\AbstractNamespaceHandler;
use PHPUnit\Framework\TestCase;

class SchemaMapNamespaceHandler extends AbstractNamespaceHandler
{
    protected function sum(int $paramA, int $paramB): int
    {
        return $paramA + $paramB;
    }
}

class SchemaMapTest extends TestCase
{
    /**
     * @var \MS\Json\Rpc\Server\Configuration\SchemaMap;
     */
    private $schemaMap;
    private $nsMap = [
        'system' => 'MS\Json\Rpc\Server\Handlers\SystemNamespaceHandler',
    ];

    public function setUp()
    {
        $this->schemaMap = new SchemaMap($this->nsMap, 'http://foo.bar/');
    }

    public function testCreateClass()
    {
        $this->assertInstanceOf('MS\Json\Rpc\Server\Configuration\SchemaMap', $this->schemaMap);
    }

    /**
     * @depends testCreateClass
     */
    public function testGetClasses()
    {
        $expected = ['input' => null, 'output' => null];
        $this->assertEquals($expected, $this->schemaMap->getClasses('unknown', 'sum'));
        $this->assertEquals($expected, $this->schemaMap->getClasses('system', 'subtract'));

        $expected = [
            'input'  => '\MS\Json\Rpc\Server\Handlers\SystemNamespaceHandler\GetConfiguration\InputSchema',
            'output' => '\MS\Json\Rpc\Server\Handlers\SystemNamespaceHandler\GetConfiguration\OutputSchema',
        ];
        $this->assertEquals($expected, $this->schemaMap->getClasses('system', 'getConfiguration'));
    }

    /**
     * @depends testCreateClass
     */
    public function testGetUrls()
    {
        $expected = ['input' => null, 'output' => null];
        $this->assertEquals($expected, $this->schemaMap->getUrls('unknown', 'sum'));
        $this->assertEquals($expected, $this->schemaMap->getUrls('system', 'subtract'));

        $expected = [
            'input'  => 'http://foo.bar/schemas/system/getConfiguration/input-schema.json',
            'output' => 'http://foo.bar/schemas/system/getConfiguration/output-schema.json',
        ];
        $this->assertEquals($expected, $this->schemaMap->getUrls('system', 'getConfiguration'));
    }
}
