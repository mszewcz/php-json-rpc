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

use MS\Json\Rpc\Server\Exceptions\InvalidParamsException;
use PHPUnit\Framework\TestCase;

class InvalidParamsExceptionTest extends TestCase
{
    public function testExceptionCodeAndMessage()
    {
        $this->expectExceptionCode(-32602);
        $this->expectExceptionMessage('Invalid params');

        throw new InvalidParamsException();
    }
}
