<?php
/**
 * JSON RPC 2.0 client and server implementation for PHP
 *
 * @author      Michal Szewczyk <ms@msworks.pl>
 * @copyright   Michal Szewczyk
 * @license     MIT
 */
declare(strict_types=1);

namespace Server\Handlers;

use MS\Json\Rpc\Server\Configuration;
use MS\Json\Rpc\Server\Exceptions\InvalidParamsException;
use MS\Json\Rpc\Server\Exceptions\MethodNotFoundException;
use MS\Json\Rpc\Server\Handlers\AbstractNamespaceHandler;
use MS\Json\Rpc\Server\Handlers\SystemNamespaceHandler;
use PHPUnit\Framework\TestCase;

class SecondNamespaceHandler extends AbstractNamespaceHandler
{
    /**
     * @param int $numberA
     * @param int $numberB
     * @return int
     */
    protected function subtract(int $numberA, int $numberB): int
    {
        return $numberA - $numberB;
    }
}

class SystemNamespaceHandlerTest extends TestCase
{
    /**
     * @var Configuration
     */
    private $config;

    public function setUp()
    {
        $nsMap = [
            'system' => 'MS\Json\Rpc\Server\Handlers\SystemNamespaceHandler',
        ];
        $this->config = new Configuration($nsMap);
    }

    public function testCreateClass(): SystemNamespaceHandler
    {
        $class = new SystemNamespaceHandler($this->config);
        $this->assertInstanceOf('MS\Json\Rpc\Server\Handlers\AbstractNamespaceHandler', $class);
        $this->assertInstanceOf('MS\Json\Rpc\Server\Handlers\SystemNamespaceHandler', $class);

        return $class;
    }

    /**
     * @depends testCreateClass
     * @param SystemNamespaceHandler $class
     * @return SystemNamespaceHandler
     */
    public function testCache(SystemNamespaceHandler $class): SystemNamespaceHandler
    {
        $class->enableCache(30);
        $this->assertEquals(30, $class->getCacheTTL());
        $class->disableCache();
        $this->assertEquals(0, $class->getCacheTTL());

        return $class;
    }

    /**
     * @depends testCache
     * @param SystemNamespaceHandler $class
     * @return SystemNamespaceHandler
     * @throws InvalidParamsException
     * @throws MethodNotFoundException
     * @throws \MS\Json\Utils\Exceptions\DecodingException
     */
    public function testInvokeGetConfiguration(SystemNamespaceHandler $class): SystemNamespaceHandler
    {
        $options = ['options' => ['default' => false]];
        $protocol = \filter_input(\INPUT_SERVER, 'HTTPS', \FILTER_DEFAULT, $options) === true ? 'https' : 'http';
        $options = ['options' => ['default' => '']];
        $serverName = \filter_input(\INPUT_SERVER, 'SERVER_NAME', \FILTER_DEFAULT, $options);
        $scriptName = \filter_input(\INPUT_SERVER, 'SCRIPT_NAME', \FILTER_DEFAULT, $options);

        $request = ['jsonrpc' => '2.0', 'method' => 'getConfiguration', 'id' => 1];
        $response = $class->invoke('getConfiguration', $request);
        $expected = [
            'serverConfiguration' => [
                'services' => [
                    'system' => [
                        'getConfiguration' => [
                            'inputSchema'  => \sprintf(
                                '%s://%s%s/schemas/system/getConfiguration/inputSchema.json',
                                $protocol,
                                $serverName,
                                $scriptName
                            ),
                            'outputSchema' => \sprintf(
                                '%s://%s%s/schemas/system/getConfiguration/outputSchema.json',
                                $protocol,
                                $serverName,
                                $scriptName
                            ),
                            'url'          => \sprintf('%s://%s%s/system/', $protocol, $serverName, $scriptName),
                        ],
                    ],
                ],
            ],
            'serverTimestamp'     => $response['serverTimestamp'],
        ];
        $this->assertEquals($expected, $response);

        return $class;
    }

    /**
     * @depends testInvokeGetConfiguration
     * @param SystemNamespaceHandler $class
     * @throws InvalidParamsException
     * @throws MethodNotFoundException
     * @throws \MS\Json\Utils\Exceptions\DecodingException
     */
    public function testMethodNotFoundException(SystemNamespaceHandler $class)
    {
        $this->expectExceptionMessage('Method not found');
        $class->invoke('getNonExistingMethod');
    }

    /**
     * @depends testMethodNotFoundException
     * @throws InvalidParamsException
     * @throws MethodNotFoundException
     */
    public function testInvalidParamsException()
    {
        $this->expectExceptionMessage('Invalid params');

        $nsMap = [
            'system' => 'Server\Handlers\SecondNamespaceHandler',
        ];
        $this->config->setNamespaceMap($nsMap);

        $request = ['jsonrpc' => '2.0', 'method' => 'subtract', 'params' => ['c' => 2, 'd' => 5], 'id' => 1];
        $class = new SecondNamespaceHandler($this->config);
        $class->invoke('subtract', $request);
    }

    /**
     * @depends testInvalidParamsException
     * @return SecondNamespaceHandler
     * @throws InvalidParamsException
     * @throws MethodNotFoundException
     * @throws \MS\Json\Utils\Exceptions\DecodingException
     */
    public function testParamsNumeric(): SecondNamespaceHandler
    {
        $nsMap = [
            'system' => 'Server\Handlers\SecondNamespaceHandler',
        ];
        $this->config->setNamespaceMap($nsMap);

        $request = ['jsonrpc' => '2.0', 'method' => 'subtract', 'params' => [5, 2], 'id' => 1];
        $class = new SecondNamespaceHandler($this->config);
        $this->assertEquals(3, $class->invoke('subtract', $request));

        return $class;
    }

    /**
     * @depends testParamsNumeric
     * @param SecondNamespaceHandler $class
     * @throws InvalidParamsException
     * @throws MethodNotFoundException
     * @throws \MS\Json\Utils\Exceptions\DecodingException
     */
    public function testParamsUnsorted(SecondNamespaceHandler $class)
    {
        $nsMap = [
            'system' => 'Server\Handlers\SecondNamespaceHandler',
        ];
        $this->config->setNamespaceMap($nsMap);

        $request = [
            'jsonrpc' => '2.0', 'method' => 'subtract', 'params' => ['numberB' => 2, 'numberA' => 5], 'id' => 1
        ];
        $this->assertEquals(3, $class->invoke('subtract', $request));
    }
}
