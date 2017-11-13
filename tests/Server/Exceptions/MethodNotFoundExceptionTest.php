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

use MS\Json\Rpc\Server\Exceptions\MethodNotFoundException;
use PHPUnit\Framework\TestCase;

class MethodNotFoundExceptionTest extends TestCase
{
    public function testExceptionCodeAndMessage()
    {
        $this->expectExceptionCode(-32601);
        $this->expectExceptionMessage('Method not found');

        throw new MethodNotFoundException();
    }
}
