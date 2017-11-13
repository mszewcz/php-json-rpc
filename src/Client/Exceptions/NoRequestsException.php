<?php
/**
 * JSON RPC 2.0 client and server implementation for PHP
 *
 * @author      Michal Szewczyk <ms@msworks.pl>
 * @copyright   Michal Szewczyk
 * @license     MIT
 */
declare(strict_types=1);

namespace MS\Json\Rpc\Client\Exceptions;


class NoRequestsException extends \Exception
{
    /**
     * NoRequestsException constructor
     *
     * @param   string     $message Error message
     * @param   int        $code    Error code
     * @param   \Throwable $previous
     */
    public function __construct($message = '', $code = 0, \Throwable $previous = null)
    {
        $message = 'No requests to send. Please add a request using add() method.';
        parent::__construct($message, $code, $previous);
    }
}
