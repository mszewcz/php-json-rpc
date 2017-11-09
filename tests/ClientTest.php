<?php
/**
 * JSON RPC 2.0 client and server implementation for PHP
 *
 * @author      Michal Szewczyk <ms@msworks.pl>
 * @copyright   Michal Szewczyk
 * @license     MIT
 */
declare(strict_types=1);

use MS\Json\Rpc\Client;
use MS\Json\Rpc\Client\Transport\CurlTransport;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{
    public function setUp()
    {
    }

    /**
     * @return Client
     */
    public function testCreateClassDefaultTransport(): Client
    {
        $client = new Client('http://foo.bar/');
        $this->assertInstanceOf('MS\Json\Rpc\Client', $client);

        return $client;
    }

    /**
     * @depends testCreateClassDefaultTransport
     */
    public function testCreateClassCurlTransport()
    {
        $transport = new CurlTransport();
        $client = new Client('http://foo.bar/', $transport);
        $this->assertInstanceOf('MS\Json\Rpc\Client', $client);
    }

    /**
     * @depends testCreateClassDefaultTransport
     * @param Client $client
     * @return Client
     */
    public function testAddHeaders(Client $client): Client
    {
        $currentHeaders = $client->getHeaders();
        $client->addHeaders(['name' => 'Accept-encoding', 'value' => 'utf-8']);

        $expected = array_merge($currentHeaders, ['Accept-encoding: utf-8']);
        $this->assertEquals($expected, $client->getHeaders());

        return $client;
    }

    /**
     * @depends testAddHeaders
     * @param Client $client
     * @return Client
     */
    public function testRemoveHeaders(Client $client): Client
    {
        $currentHeaders = $client->getHeaders();
        $client->removeHeaders('Accept-encoding');

        $expected = array_diff($currentHeaders, ['Accept-encoding: utf-8']);
        $this->assertEquals($expected, $client->getHeaders());

        return $client;
    }

    /**
     * @depends testRemoveHeaders
     * @param Client $client
     * @return Client
     */
    public function testClearHeaders(Client $client): Client
    {
        $client->clearHeaders();
        $this->assertEmpty($client->getHeaders());

        return $client;
    }

    /**
     * @depends testClearHeaders
     * @param Client $client
     * @return Client
     */
    public function testAdd(Client $client): Client
    {
        $client->add(new Client\Request(1, 'testMethod', []));
        $this->assertCount(1, $client->getQueue());
        $client->add(new Client\Request(2, 'testMethod', []));
        $this->assertCount(2, $client->getQueue());
        $client->add(new Client\Request(3, 'testMethod', []));
        $client->add(new Client\Request(4, 'testMethod', []));
        $this->assertCount(4, $client->getQueue());

        return $client;
    }

    /**
     * @depends testAdd
     * @param Client $client
     * @return Client
     */
    public function testClearQueue(Client $client): Client
    {
        $this->assertCount(4, $client->getQueue());
        $client->clearQueue();
        $this->assertCount(0, $client->getQueue());

        return $client;
    }

    /**
     * @depends testClearQueue
     * @param Client $client
     * @throws Client\Exceptions\ConnectionException
     * @throws Client\Exceptions\NoRequestsException
     * @throws \MS\Json\Utils\Exceptions\EncodingException
     */
    public function testNoRequestsException(Client $client)
    {
        $this->expectExceptionMessage('No requests to send. Please add a request using add() method.');
        $client->send();
    }

    /**
     * @depends testCreateClassDefaultTransport
     * @throws Client\Exceptions\ConnectionException
     * @throws Client\Exceptions\NoRequestsException
     */
    public function testConnectionException()
    {
        $this->expectExceptionMessage('Unable to connect to url: http://foo.bar/');

        $class = new Client('http://foo.bar/');
        $class->add(new Client\Request(1, 'testMethod', []));
        $class->send();
    }

    /**
     * @depends testCreateClassDefaultTransport
     * @throws Client\Exceptions\ConnectionException
     * @throws Client\Exceptions\NoRequestsException
     */
    public function testSend()
    {
        $class = new Client('http://httpbin.org/post');
        $class->add(new Client\Request(1, 'testMethod', []));
        $class->send();
        $this->assertNotNull($class->getResponse());
    }
}
