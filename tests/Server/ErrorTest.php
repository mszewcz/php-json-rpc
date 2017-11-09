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

use MS\Json\Rpc\Server\Error;
use PHPUnit\Framework\TestCase;

class ErrorTest extends TestCase
{
    public function testCreateClass()
    {
        $error = new Error();
        $this->assertInstanceOf('MS\Json\Rpc\Server\Error', $error);
    }

    /**
     * @depends testCreateClass
     */
    public function testBuild()
    {
        $error = new Error(100, 'Error message');
        $expected = ['code' => 100, 'message' => 'Error message'];
        $this->assertEquals($expected, $error->build());

        $error = new Error(100, 'Error message', 'Error data');
        $expected = ['code' => 100, 'message' => 'Error message', 'data' => 'Error data'];
        $this->assertEquals($expected, $error->build());
    }

}
