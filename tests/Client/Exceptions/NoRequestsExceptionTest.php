<?php
/**
 * JSON RPC 2.0 client and server implementation for PHP
 *
 * @author      Michal Szewczyk <ms@msworks.pl>
 * @copyright   Michal Szewczyk
 * @license     MIT
 */
declare(strict_types=1);

namespace Client\Exceptions;

use MS\Json\Rpc\Client\Exceptions\NoRequestsException;
use PHPUnit\Framework\TestCase;

class NoRequestsExceptionTest extends TestCase
{
    public function testExceptionMessage()
    {
        $this->expectExceptionMessage('No requests to send. Please add a request using add() method.');

        throw new NoRequestsException();
    }
}
