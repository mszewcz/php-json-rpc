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

use MS\Json\Rpc\Client\Notification;
use PHPUnit\Framework\TestCase;

class NotificationTest extends TestCase
{
    public function testCreateClass()
    {
        $class = new Notification('testMethod');
        $this->assertInstanceOf('MS\Json\Rpc\Client\Notification', $class);
    }

    /**
     * @depends testCreateClass
     */
    public function testBuild()
    {
        $class = new Notification('testMethod');
        $expected = ['jsonrpc' => '2.0', 'method' => 'testMethod'];
        $this->assertEquals($expected, $class->build());

        $params = ['a' => 1, 'b' => 1.5, 'c' => ['d' => 'test', 'f' => false]];
        $class = new Notification('testMethod', $params);
        $expected = ['jsonrpc' => '2.0', 'method' => 'testMethod', 'params' => $params];
        $this->assertEquals($expected, $class->build());
    }
}
