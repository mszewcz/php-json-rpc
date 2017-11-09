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

use MS\Json\Rpc\Server\Exceptions\InvalidRequestException;
use PHPUnit\Framework\TestCase;

class InvalidRequestExceptionTest extends TestCase
{
    public function testExceptionCodeAndMessage()
    {
        $this->expectExceptionCode(-32600);
        $this->expectExceptionMessage('Invalid request');

        throw new InvalidRequestException();
    }
}
