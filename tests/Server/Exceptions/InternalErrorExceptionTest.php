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

use MS\Json\Rpc\Server\Exceptions\InternalErrorException;
use PHPUnit\Framework\TestCase;

class InternalErrorExceptionTest extends TestCase
{
    public function testExceptionCodeAndMessage()
    {
        $this->expectExceptionCode(-32603);
        $this->expectExceptionMessage('Internal error');

        throw new InternalErrorException();
    }
}
