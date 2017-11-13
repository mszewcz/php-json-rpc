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

use MS\Json\Rpc\Server\Configuration\ParamsMap;
use MS\Json\Rpc\Server\Handlers\AbstractNamespaceHandler;
use PHPUnit\Framework\TestCase;

class ParamsMapNamespaceHandler extends AbstractNamespaceHandler
{
    protected function sum(int $paramA, int $paramB): int
    {
        return $paramA + $paramB;
    }
}

class ParamsMapTest extends TestCase
{
    /**
     * @var \MS\Json\Rpc\Server\Configuration\ParamsMap;
     */
    private $paramsMap;
    private $nsMap = [
        'system' => 'Server\Configuration\ParamsMapNamespaceHandler',
    ];

    public function setUp()
    {
        $this->paramsMap = new ParamsMap($this->nsMap);
    }

    public function testCreateClass()
    {
        $this->assertInstanceOf('MS\Json\Rpc\Server\Configuration\ParamsMap', $this->paramsMap);
    }

    /**
     * @depends testCreateClass
     */
    public function testGetParams()
    {
        $expected = ['paramA', 'paramB'];
        $this->assertEquals($expected, $this->paramsMap->getParams('system', 'sum'));
    }
}
