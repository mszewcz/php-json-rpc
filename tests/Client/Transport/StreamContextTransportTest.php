<?php
/**
 * JSON RPC 2.0 client and server implementation for PHP
 *
 * @author      Michal Szewczyk <ms@msworks.pl>
 * @copyright   Michal Szewczyk
 * @license     MIT
 */
declare(strict_types=1);

namespace Client\Transport;

use MS\Json\Rpc\Client\Transport\StreamContextTransport;
use PHPUnit\Framework\TestCase;

class StreamContextTransportTest extends TestCase
{
    /**
     * @var StreamContextTransport
     */
    private $transport;

    public function setUp()
    {
        $this->transport = new StreamContextTransport();
    }

    public function testCreateClass() {
        $this->assertInstanceOf('MS\Json\Rpc\Client\Transport\StreamContextTransport', $this->transport);
    }

    /**
     * @depends testCreateClass
     */
    public function testSetUrl()
    {
        $this->transport->setUrl('http://foo.bar/');
        $this->assertEquals('http://foo.bar/', $this->transport->getUrl());
    }

    /**
     * @depends testSetUrl
     */
    public function testAddHeaders()
    {
        $expected = [
            'Content-Type: application/json',
            'Accept: application/json'
        ];
        $this->transport->addHeaders(['name' => 'Content-Type', 'value' => 'application/json']);
        $this->assertEquals($expected, $this->transport->getHeaders());
    }

    /**
     * @depends testAddHeaders
     */
    public function testRemoveHeaders()
    {
        $expected = [
            'Content-Type: application/json; charset=utf-8'
        ];
        $this->transport->removeHeaders(['Accept']);
        $this->assertEquals($expected, $this->transport->getHeaders());
    }

    /**
     * @depends testRemoveHeaders
     */
    public function testClearHeaders()
    {
        $expected = [];
        $this->transport->clearHeaders();
        $this->assertEquals($expected, $this->transport->getHeaders());
    }

    /**
     * @depends testClearHeaders
     */
    public function testSetData()
    {
        $data = \json_encode(['a' => 1, 'b' => 'test', 'c' => true, 'd' => ['e' => 1.2, 'f' => 'ok!']]);
        $this->transport->setData($data);
        $this->assertEquals($data, $this->transport->getData());
    }

    /**
     * @depends testSetData
     */
    public function testGetNullResponse()
    {
        $this->assertNull($this->transport->getResponse());
    }

    /**
     * @depends testGetNullResponse
     * @throws \MS\Json\Rpc\Client\Exceptions\ConnectionException
     */
    public function testConnectionException()
    {
        $this->expectExceptionMessage('Unable to connect to url: http://foo.bar');
        $this->transport->setUrl('http://foo.bar/');
        $this->transport->send();
    }

    /**
     * @depends testGetNullResponse
     * @throws \MS\Json\Rpc\Client\Exceptions\ConnectionException
     */
    public function testSend()
    {
        $this->transport->setUrl('http://httpbin.org/post');
        $this->transport->send();
        $this->assertNotNull($this->transport->getResponse());
    }
}
