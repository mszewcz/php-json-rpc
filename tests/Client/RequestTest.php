<?php
/**
 * JSON RPC 2.0 client and server implementation for PHP
 *
 * @author      Michal Szewczyk <ms@msworks.pl>
 * @copyright   Michal Szewczyk
 * @license     MIT
 */
declare(strict_types=1);

namespace Client;

use MS\Json\Rpc\Client\Request;
use PHPUnit\Framework\TestCase;

class RequestTest extends TestCase
{
    public function testCreateClass()
    {
        $class = new Request(1, 'testMethod');
        $this->assertInstanceOf('MS\Json\Rpc\Client\Request', $class);
    }

    /**
     * @depends testCreateClass
     */
    public function testBuild()
    {
        $class = new Request(1, 'testMethod');
        $expected = ['jsonrpc' => '2.0', 'method' => 'testMethod', 'id' => 1];
        $this->assertEquals($expected, $class->build());

        $params = ['a' => 1, 'b' => 1.5, 'c' => ['d' => 'test', 'f' => false]];
        $class = new Request(2, 'testMethod', $params);
        $expected = ['jsonrpc' => '2.0', 'method' => 'testMethod', 'params' => $params, 'id' => 2];
        $this->assertEquals($expected, $class->build());
    }

}
