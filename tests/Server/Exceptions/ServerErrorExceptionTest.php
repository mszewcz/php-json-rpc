<?php
/**
 * JSON RPC 2.0 client and server implementation for PHP
 *
 * @author      Michal Szewczyk <ms@msworks.pl>
 * @copyright   Michal Szewczyk
 * @license     MIT
 */
declare(strict_types=1);

namespace Server\Exceptions;

use MS\Json\Rpc\Server\Exceptions\ServerErrorException;
use PHPUnit\Framework\TestCase;

class ServerErrorExceptionTest extends TestCase
{
    public function testExceptionCodeMessageAndData()
    {
        $this->expectExceptionCode(-32000);
        $this->expectExceptionMessage('Server error');

        $exception = new ServerErrorException('Exception data', -32000);
        $this->assertEquals('Exception data', $exception->getData());
        throw $exception;
    }
}
