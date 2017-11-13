<?php
/**
 * JSON RPC 2.0 client and server implementation for PHP
 *
 * @author      Michal Szewczyk <ms@msworks.pl>
 * @copyright   Michal Szewczyk
 * @license     MIT
 */
declare(strict_types=1);

use MS\Json\Rpc\SchemaServer;
use MS\Json\Rpc\Server\Configuration;
use PHPUnit\Framework\TestCase;


class SchemaServerTest extends TestCase
{
    private $nsMap = [
        'system' => 'MS\Json\Rpc\Server\Handlers\SystemNamespaceHandler',
    ];
    /**
     * @var Configuration
     */
    private $config;
    /**
     * @var SchemaServer
     */
    private $server;

    public function setUp()
    {
        $this->config = new Configuration($this->nsMap);
        $this->server = (new SchemaServer($this->config));
    }

    public function testCreateClass()
    {
        $this->assertInstanceOf('MS\Json\Rpc\SchemaServer', $this->server);
    }

    /**
     * @depends testCreateClass
     */
    public function testAddHeaders()
    {
        $this->server->addHeaders(['name' => 'Accept', 'value' => 'application/json']);
        $this->server->removeHeaders(['Expires']);
        $expected = [
            'Content-Type: application/json; charset=utf-8',
            'Access-Control-Allow-Origin: *',
            'Cache-Control: no-cache, no-store, must-revalidate',
            'Pragma: no-cache',
            'Accept: application/json',
        ];
        $this->assertEquals($expected, $this->server->getHeaders());
    }

    /**
     * @depends testAddHeaders
     */
    public function testRemoveHeaders()
    {
        $this->server->removeHeaders(['Accept', 'Expires', 'Pragma']);

        $expected = [
            'Content-Type: application/json; charset=utf-8',
            'Access-Control-Allow-Origin: *',
            'Cache-Control: no-cache, no-store, must-revalidate',
        ];
        $this->assertEquals($expected, $this->server->getHeaders());
    }

    /**
     * @depends testCreateClass
     */
    public function testGetResponse()
    {
        $expected = '{}';
        $this->assertEquals($expected, $this->server->listen()->getResponse());
    }
}
