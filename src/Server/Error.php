<?php
/**
 * JSON RPC 2.0 client and server implementation for PHP
 *
 * @author      Michal Szewczyk <ms@msworks.pl>
 * @copyright   Michal Szewczyk
 * @license     MIT
 */
declare(strict_types=1);

namespace MS\Json\Rpc\Server;


class Error
{
    /**
     * Error code
     *
     * @var int
     */
    private $code;
    /**
     * Error message
     *
     * @var string
     */
    private $message;
    /**
     * Error detailed description
     *
     * @var string
     */
    private $data;

    /**
     * Error constructor.
     *
     * @param   int    $code    Error code
     * @param   string $message Error message
     * @param   string $data    Error detailed description
     */
    public function __construct(int $code = -32099, string $message = '', string $data = '')
    {
        $this->code = $code;
        $this->message = $message;
        $this->data = $data;
    }

    /**
     * Builds error array and returns it
     *
     * @return array
     */
    public function build(): array
    {
        $error = ['code' => $this->code, 'message' => $this->message];
        if ($this->data!=='') {
            $error['data'] = $this->data;
        }
        return $error;
    }
}
