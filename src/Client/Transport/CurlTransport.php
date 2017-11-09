<?php
/**
 * JSON RPC 2.0 client and server implementation for PHP
 *
 * @author      Michal Szewczyk <ms@msworks.pl>
 * @copyright   Michal Szewczyk
 * @license     MIT
 */
declare(strict_types=1);

namespace MS\Json\Rpc\Client\Transport;


use MS\Json\Rpc\Client\Exceptions\ConnectionException;

class CurlTransport extends AbstractTransport
{
    /**
     * CurlTransport constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Sends request to server
     *
     * @throws ConnectionException
     * @return void
     */
    public function send(): void
    {
        $options = [
            CURLOPT_TIMEOUT        => 5,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_CUSTOMREQUEST  => 'POST',
            CURLOPT_POSTFIELDS     => $this->getData(),
            CURLOPT_HTTPHEADER     => $this->getHeaders(),
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_RETURNTRANSFER => true,
        ];

        $curlHandler = \curl_init($this->getUrl());
        \curl_setopt_array($curlHandler, $options);
        $result = \curl_exec($curlHandler);
        \curl_close($curlHandler);

        if ($result===false) {
            throw new ConnectionException($this->getUrl());
        }
        $this->setResponse($result);
    }
}
