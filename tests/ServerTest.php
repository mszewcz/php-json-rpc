<?php
/**
 * JSON RPC 2.0 client and server implementation for PHP
 *
 * @author      Michal Szewczyk <ms@msworks.pl>
 * @copyright   Michal Szewczyk
 * @license     MIT
 */
declare(strict_types=1);

use MS\Json\Rpc\Server;
use MS\Json\Rpc\Server\Configuration;
use MS\Json\Rpc\Server\Handlers\AbstractNamespaceHandler;
use PHPUnit\Framework\TestCase;


class TestNamespaceHandler extends AbstractNamespaceHandler
{
    /**
     * TestNamespaceHandler constructor.
     *
     * @param Configuration $config
     */
    public function __construct(Configuration $config)
    {
        parent::__construct($config);
        $this->enableCache(60);
    }

    /**
     * @param int $minuend
     * @param int $subtrahend
     * @return int
     */
    protected function subtract(int $minuend, int $subtrahend)
    {
        return $minuend - $subtrahend;
    }

    /**
     * @param int $numberA
     * @param int $numberB
     * @param int $numberC
     * @return int
     */
    protected function sum(int $numberA, int $numberB, int $numberC): int
    {
        return $numberA + $numberB + $numberC;
    }

    /**
     * @return array
     */
    protected function getData(): array
    {
        return ['hello', 5];
    }

    /**
     * @return string
     */
    protected function invalidUTF(): string
    {
        return "\xB1\x31";
    }
}

class InvalidNamespaceHandler
{
}

class ServerTest extends TestCase
{
    private $nsMap = [
        'system' => 'TestNamespaceHandler',
    ];
    /**
     * @var Configuration
     */
    private $config;
    /**
     * @var Server
     */
    private $server;

    public function setUp()
    {
        $this->config = new Configuration($this->nsMap);
        $this->config->setDefaultRequestMethod('POST');
        $this->server = (new Server($this->config));
    }

    public function testCreateClass()
    {
        $this->assertInstanceOf('MS\Json\Rpc\Server', $this->server);
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
     * @depends testRemoveHeaders
     */
    public function testClearHeaders()
    {
        $this->server->clearHeaders();
        $this->assertEquals([], $this->server->getHeaders());
    }

    /**
     * @depends testCreateClass
     */
    public function testServerError()
    {
        $nsMap = ['system' => 'InvalidNamespaceHandler'];
        $this->config->setNamespaceMap($nsMap);
        $input = '{"jsonrpc":"2.0","method":"subtract","params":[42,23],"id":1}';
        $expected = '{"jsonrpc":"2.0","error":{"code":-32001,"message":"Server error",';
        $expected .= '"data":"InvalidNamespaceHandler MUST be an instance of \\\\MS\\\\Json';
        $expected .= '\\\\Rpc\\\\Server\\\\Handlers\\\\AbstractNamespaceHandler"},"id":1}';
        $server = (new Server($this->config))->provideRequest($input)->listen();
        $this->assertEquals($expected, $server->getResponse());

        $this->config->setNamespaceMap($this->nsMap);
        $input = '{"jsonrpc":"2.0","method":"invalidUTF","id":1}';
        $expected = '{"jsonrpc":"2.0","error":{"code":-32002,"message":"Server error",';
        $expected .= '"data":"JSON encoding error: Malformed UTF-8 characters, possibly ';
        $expected .= 'incorrectly encoded"},"id":null}';
        $server = (new Server($this->config))->provideRequest($input)->listen();
        $this->assertEquals($expected, $server->getResponse());
    }

    /**
     * @depends testCreateClass
     */
    public function testJsonRpcOrgExamples()
    {
        $this->config->setNamespaceMap($this->nsMap);

        $input = '{"jsonrpc":"2.0","method":"subtract","params":[42,23],"id":1}';
        $expected = '{"jsonrpc":"2.0","result":19,"id":1}';
        $server = (new Server($this->config))->provideRequest($input)->listen();
        $this->assertEquals($expected, $server->getResponse());

        $input = '{"jsonrpc":"2.0","method":"subtract","params":[23,42],"id":2}';
        $expected = '{"jsonrpc":"2.0","result":-19,"id":2}';
        $server = (new Server($this->config))->provideRequest($input)->listen();
        $this->assertEquals($expected, $server->getResponse());

        $input = '{"jsonrpc":"2.0","method":"subtract","params":{"subtrahend":23,"minuend":42},"id":3}';
        $expected = '{"jsonrpc":"2.0","result":19,"id":3}';
        $server = (new Server($this->config))->provideRequest($input)->listen();
        $this->assertEquals($expected, $server->getResponse());

        $input = '{"jsonrpc":"2.0","method":"subtract","params":{"minuend":42,"subtrahend":23},"id":4}';
        $expected = '{"jsonrpc":"2.0","result":19,"id":4}';
        $server = (new Server($this->config))->provideRequest($input)->listen();
        $this->assertEquals($expected, $server->getResponse());

        $input = '{"jsonrpc":"2.0","method":"update","params":[1,2,3,4,5]}';
        $expected = '';
        $server = (new Server($this->config))->provideRequest($input)->listen();
        $this->assertEquals($expected, $server->getResponse());

        $input = '{"jsonrpc":"2.0","method":"foobar"}';
        $expected = '';
        $server = (new Server($this->config))->provideRequest($input)->listen();
        $this->assertEquals($expected, $server->getResponse());

        $input = '{"jsonrpc":"2.0","method":"foobar","id":"1"}';
        $expected = '{"jsonrpc":"2.0","error":{"code":-32601,"message":"Method not found"},"id":"1"}';
        $server = (new Server($this->config))->provideRequest($input)->listen();
        $this->assertEquals($expected, $server->getResponse());

        $input = '{"jsonrpc":"2.0","method":"foobar,"params":"bar","baz]';
        $expected = '{"jsonrpc":"2.0","error":{"code":-32700,"message":"Parse error"},"id":null}';
        $server = (new Server($this->config))->provideRequest($input)->listen();
        $this->assertEquals($expected, $server->getResponse());

        $input = '{"jsonrpc":"2.0","method": 1,"params":"bar"}';
        $expected = '{"jsonrpc":"2.0","error":{"code":-32600,"message":"Invalid request"},"id":null}';
        $server = (new Server($this->config))->provideRequest($input)->listen();
        $this->assertEquals($expected, $server->getResponse());

        $input = '[{"jsonrpc": "2.0", "method": "sum", "params": [1,2,4], "id": "1"},{"jsonrpc": "2.0", "method"]';
        $expected = '{"jsonrpc":"2.0","error":{"code":-32700,"message":"Parse error"},"id":null}';
        $server = (new Server($this->config))->provideRequest($input)->listen();
        $this->assertEquals($expected, $server->getResponse());

        $input = '[]';
        $expected = '{"jsonrpc":"2.0","error":{"code":-32600,"message":"Invalid request"},"id":null}';
        $server = (new Server($this->config))->provideRequest($input)->listen();
        $this->assertEquals($expected, $server->getResponse());

        $input = '[1]';
        $expected = '[{"jsonrpc":"2.0","error":{"code":-32600,"message":"Invalid request"},"id":null}]';
        $server = (new Server($this->config))->provideRequest($input)->listen();
        $this->assertEquals($expected, $server->getResponse());

        $input = '[1,2,3]';
        $expected = '[{"jsonrpc":"2.0","error":{"code":-32600,"message":"Invalid request"},"id":null},';
        $expected .= '{"jsonrpc":"2.0","error":{"code":-32600,"message":"Invalid request"},"id":null},';
        $expected .= '{"jsonrpc":"2.0","error":{"code":-32600,"message":"Invalid request"},"id":null}]';
        $server = (new Server($this->config))->provideRequest($input)->listen();
        $this->assertEquals($expected, $server->getResponse());

        $input = '[{"jsonrpc":"2.0","method":"sum","params":[1,2,4],"id":"1"},';
        $input .= '{"jsonrpc":"2.0","method":"notify_hello","params":[7]},';
        $input .= '{"jsonrpc":"2.0","method":"subtract","params":[42,23],"id":"2"},';
        $input .= '{"foo":"boo"},{"jsonrpc":"2.0","method":"foo.get","params":{"name":"myself"},"id":"5"},';
        $input .= '{"jsonrpc":"2.0","method":"getData","id":"9"}]';
        $expected = '[{"jsonrpc":"2.0","result":7,"id":"1"},';
        $expected .= '{"jsonrpc":"2.0","result":19,"id":"2"},';
        $expected .= '{"jsonrpc":"2.0","error":{"code":-32600,"message":"Invalid request"},"id":null},';
        $expected .= '{"jsonrpc":"2.0","error":{"code":-32601,"message":"Method not found"},"id":"5"},';
        $expected .= '{"jsonrpc":"2.0","result":["hello",5],"id":"9"}]';
        $server = (new Server($this->config))->provideRequest($input)->listen();
        $this->assertEquals($expected, $server->getResponse());

        $input = '[{"jsonrpc":"2.0","method":"notify_sum","params":[1,2,4]},';
        $input .= '{"jsonrpc":"2.0","method":"notify_hello","params":[7]}]';
        $expected = '';
        $server = (new Server($this->config))->provideRequest($input)->listen();
        $this->assertEquals($expected, $server->getResponse());
    }
}
