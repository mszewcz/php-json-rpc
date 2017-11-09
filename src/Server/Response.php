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


final class Response
{
    /**
     * JSON RPC protocol version
     */
    private const JSON_RPC_PROTOCOL_VERSION = '2.0';
    /**
     * Response result
     *
     * @var ?array
     */
    private $result = null;
    /**
     * Response error
     *
     * @var ?array
     */
    private $error = null;
    /**
     * Response id
     *
     * @var ?mixed
     */
    private $id = null;

    /**
     * Response constructor
     *
     * @param   ?array  $result     Response result
     * @param   ?array  $error      Response error
     * @param   ?array  $id         Response id
     */
    public function __construct($result = null, $error = null, $id = null)
    {
        $this->result = $result;
        $this->error = $error;
        $this->id = $id;
    }

    /**
     * Builds and returns response array
     *
     * @return array
     */
    public function build(): array
    {
        $response = [
            'jsonrpc' => self::JSON_RPC_PROTOCOL_VERSION,
        ];
        if ($this->result!==null) {
            $response['result'] = $this->result;
        }
        if ($this->error!==null) {
            $response['error'] = $this->error;
        }
        $response['id'] = $this->id;

        return $response;
    }
}
