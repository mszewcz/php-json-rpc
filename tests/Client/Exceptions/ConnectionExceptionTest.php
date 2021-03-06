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

use MS\Json\Rpc\Client\Exceptions\ConnectionException;
use PHPUnit\Framework\TestCase;

class ConnectionExceptionTest extends TestCase
{
    public function testExceptionMessage()
    {
        $this->expectExceptionMessage('Unable to connect to url: http://foo.bar/');

        throw new ConnectionException('http://foo.bar/');
    }
}
