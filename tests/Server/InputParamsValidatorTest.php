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
use MS\Json\Rpc\Server\InputParamsValidator;
use PHPUnit\Framework\TestCase;

class ValidatorNamespaceHandler
{
    /**
     * @param string $paramA
     * @param int    $paramB
     * @return string
     */
    protected function testMissing(string $paramA, int $paramB): string
    {
        return \sprintf('%s: %s', $paramA, $paramB);
    }

    /**
     * @param int $paramA
     * @return int
     */
    protected function testType(int $paramA): int
    {
        return $paramA;
    }
}

class InputParamsValidatorTest extends TestCase
{
    /**
     * @var InputParamsValidator
     */
    private $validator;

    public function setUp()
    {
        $nsMap = [
            'system' => 'Server\ValidatorNamespaceHandler',
        ];

        $config = new Configuration($nsMap);
        $this->validator = new InputParamsValidator($config);
    }

    public function testCreateClass()
    {
        $this->assertInstanceOf('MS\Json\Rpc\Server\InputParamsValidator', $this->validator);
    }

    /**
     * @depends testCreateClass
     */
    public function testValidateParamMissing()
    {
        $request = ['jsonrpc' => '2.0', 'method' => 'subtract', 'params' => ['paramA' => 'aaa'], 'id' => 1];
        $this->assertFalse($this->validator->validate('system', 'testMissing', $request));
    }

    /**
     * @depends testCreateClass
     */
    public function testValidateCheckType()
    {
        $curlHandler = \curl_init('http://foo.bar/');
        $request = ['jsonrpc' => '2.0', 'method' => 'subtract', 'params' => ['paramA' => 9.3], 'id' => 1];
        $this->assertFalse($this->validator->validate('system', 'testType', $request));
        $request = ['jsonrpc' => '2.0', 'method' => 'subtract', 'params' => ['paramA' => false], 'id' => 1];
        $this->assertFalse($this->validator->validate('system', 'testType', $request));
        $request = ['jsonrpc' => '2.0', 'method' => 'subtract', 'params' => ['paramA' => 'aaa'], 'id' => 1];
        $this->assertFalse($this->validator->validate('system', 'testType', $request));
        $request = ['jsonrpc' => '2.0', 'method' => 'subtract', 'params' => ['paramA' => null], 'id' => 1];
        $this->assertFalse($this->validator->validate('system', 'testType', $request));
        $request = ['jsonrpc' => '2.0', 'method' => 'subtract', 'params' => ['paramA' => [0, 2]], 'id' => 1];
        $this->assertFalse($this->validator->validate('system', 'testType', $request));
        $request = ['jsonrpc' => '2.0', 'method' => 'subtract', 'params' => ['paramA' => \json_decode('{"a":2, "b":4}')], 'id' => 1];
        $this->assertFalse($this->validator->validate('system', 'testType', $request));
        $request = ['jsonrpc' => '2.0', 'method' => 'subtract', 'params' => ['paramA' => $curlHandler], 'id' => 1];
        $this->assertFalse($this->validator->validate('system', 'testType', $request));
        $request = ['jsonrpc' => '2.0', 'method' => 'subtract', 'params' => ['paramA' => 5], 'id' => 1];
        $this->assertTrue($this->validator->validate('system', 'testType', $request));
        \curl_close($curlHandler);
    }
}
