<?php
/**
 * JSON RPC 2.0 client and server implementation for PHP
 *
 * @author      Michal Szewczyk <ms@msworks.pl>
 * @copyright   Michal Szewczyk
 * @license     MIT
 */
declare(strict_types=1);

namespace MS\Json\Rpc\Server\Exceptions;


class ServerErrorException extends \Exception
{
    /**
     * Detailed error description
     *
     * @var string
     */
    private $data = '';

    /**
     * ServerErrorException constructor
     *
     * @param   string     $data Detailed error description
     * @param   int        $code Error code
     * @param   \Throwable $previous
     */
    public function __construct($data = '', $code = 0, \Throwable $previous = null)
    {
        $this->data = $data;
        $message = 'Server error';
        parent::__construct($message, $code, $previous);
    }

    /**
     * Returns detailed error description
     *
     * @return  string
     */
    public function getData(): string
    {
        return $this->data;
    }
}
