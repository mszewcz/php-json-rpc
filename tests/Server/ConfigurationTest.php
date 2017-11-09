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

use MS\Json\Rpc\Server\Configuration;
use MS\Json\Rpc\Server\Exceptions\ServerErrorException;
use MS\Json\Rpc\Server\Handlers\AbstractNamespaceHandler;
use PHPUnit\Framework\TestCase;


class DummyNamespaceHandler
{
}

class SampleNamespaceHandler extends AbstractNamespaceHandler
{
    protected $outputParams = [];

    protected function sum(int $numberA, int $numberB = 0): int
    {
        return $numberA+$numberB;
    }
}

class ConfigurationTest extends TestCase
{
    /**
     * @var Configuration
     */
    private $config;

    public function setUp()
    {
        $this->config = new Configuration([]);
    }

    public function testCreateClass()
    {
        $this->assertInstanceOf('MS\Json\Rpc\Server\Configuration', $this->config);
    }

    /**
     * @depends testCreateClass
     */
    public function testGetCurrentNamespaceName()
    {
        $this->assertEquals('system', $this->config->getCurrentNamespaceName());
    }

    /**
     * @depends testCreateClass
     * @throws ServerErrorException
     */
    public function testHandlerNotFoundException()
    {
        $nsMap = [
            'dummy' => 'Server\DummyNamespaceHandler',
        ];

        $this->expectExceptionCode(-32000);
        $this->expectExceptionMessage('Server error');

        $this->config->setNamespaceMap($nsMap);
        $this->config->getNamespaceHandler();
    }

    /**
     * @depends testCreateClass
     * @throws ServerErrorException
     */
    public function testHandlerMustBeAnInstanceException()
    {
        $nsMap = [
            'system' => 'Server\DummyNamespaceHandler',
        ];

        $this->expectExceptionCode(-32001);
        $this->expectExceptionMessage('Server error');

        $this->config->setNamespaceMap($nsMap);
        $this->config->getNamespaceHandler();
    }
}
