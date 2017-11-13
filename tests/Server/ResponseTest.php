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

use MS\Json\Rpc\Server\Response;
use PHPUnit\Framework\TestCase;

class ResponseTest extends TestCase
{
    public function testCreateClass()
    {
        $class = new Response();
        $this->assertInstanceOf('MS\Json\Rpc\Server\Response', $class);
    }

    /**
     * @depends testCreateClass
     */
    public function testBuild()
    {
        $class = new Response();
        $expected = ['jsonrpc' => '2.0', 'id' => null];
        $this->assertEquals($expected, $class->build());

        $class = new Response(['a' => 1, 'b' => 2]);
        $expected = ['jsonrpc' => '2.0', 'result'=> ['a' => 1, 'b' => 2], 'id' => null];
        $this->assertEquals($expected, $class->build());

        $class = new Response(['a' => 1, 'b' => 2], null, 3);
        $expected = ['jsonrpc' => '2.0', 'result'=> ['a' => 1, 'b' => 2], 'id' => 3];
        $this->assertEquals($expected, $class->build());

        $class = new Response(null, ['c' => 3, 'd' => 4]);
        $expected = ['jsonrpc' => '2.0', 'error'=> ['c' => 3, 'd' => 4], 'id' => null];
        $this->assertEquals($expected, $class->build());

        $class = new Response(null, ['c' => 3, 'd' => 4], 5);
        $expected = ['jsonrpc' => '2.0', 'error'=> ['c' => 3, 'd' => 4], 'id' => 5];
        $this->assertEquals($expected, $class->build());

        $class = new Response(['a' => 1, 'b' => 2], ['c' => 3, 'd' => 4]);
        $expected = ['jsonrpc' => '2.0', 'result'=> ['a' => 1, 'b' => 2], 'error'=> ['c' => 3, 'd' => 4], 'id' => null];
        $this->assertEquals($expected, $class->build());

        $class = new Response(['a' => 1, 'b' => 2], ['c' => 3, 'd' => 4], 7);
        $expected = ['jsonrpc' => '2.0', 'result'=> ['a' => 1, 'b' => 2], 'error'=> ['c' => 3, 'd' => 4], 'id' => 7];
        $this->assertEquals($expected, $class->build());
    }
}
