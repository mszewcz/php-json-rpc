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

use MS\Json\Rpc\Client\Transport\CurlTransport;
use PHPUnit\Framework\TestCase;

class CurlTransportTest extends TestCase
{
    /**
     * @var CurlTransport
     */
    private $transport;

    public function setUp()
    {
        $this->transport = new CurlTransport();
    }

    public function testCreateClass()
    {
        $this->assertInstanceOf('MS\Json\Rpc\Client\Transport\CurlTransport', $this->transport);
    }

    /**
     * @depends testCreateClass
     * @throws \MS\Json\Rpc\Client\Exceptions\ConnectionException
     */
    public function testConnectionException()
    {
        $this->expectExceptionMessage('Unable to connect to url: http://foo.bar');
        $this->transport->setUrl('http://foo.bar/');
        $this->transport->send();
    }

    /**
     * @depends testCreateClass
     * @throws \MS\Json\Rpc\Client\Exceptions\ConnectionException
     */
    public function testSend()
    {
        $this->transport->setUrl('http://httpbin.org/post');
        $this->transport->send();
        $this->assertNotNull($this->transport->getResponse());
    }
}
