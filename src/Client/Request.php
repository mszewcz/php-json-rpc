<?php
/**
 * JSON RPC 2.0 client and server implementation for PHP
 *
 * @author      Michal Szewczyk <ms@msworks.pl>
 * @copyright   Michal Szewczyk
 * @license     MIT
 */
declare(strict_types=1);

namespace MS\Json\Rpc\Client;


class Request
{
    /**
     * JSON RPC protocol version
     */
    private const JSON_RPC_PROTOCOL_VERSION = '2.0';
    /**
     * Request method
     *
     * @var string
     */
    private $method = null;
    /**
     * Request params
     *
     * @var array
     */
    private $params = [];
    /**
     * Request id
     *
     * @var mixed
     */
    private $id = null;

    /**
     * Request constructor
     *
     * @param   mixed  $id     Request ID
     * @param   string $method Method name
     * @param   array  $params Request params
     */
    public function __construct($id = null, string $method = '', array $params = [])
    {
        $this->id = $id;
        $this->method = $method;
        $this->params = $params;
    }

    /**
     * Builds and returns request
     *
     * @return  array
     */
    public function build(): array
    {
        $request = [
            'jsonrpc' => self::JSON_RPC_PROTOCOL_VERSION,
            'method'  => $this->method,
        ];
        if (\count($this->params)!==0) {
            $request['params'] = $this->params;
        }
        if ($this->id!==null) {
            $request['id'] = $this->id;
        }
        return $request;
    }
}
