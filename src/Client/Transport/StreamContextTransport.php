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

class StreamContextTransport extends AbstractTransport
{
    /**
     * StreamContextTransport constructor.
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
            'http' => [
                'method'  => 'POST',
                'header'  => \implode("\r\n", $this->getHeaders()),
                'content' => $this->getData(),
            ],
        ];

        $context = \stream_context_create($options);
        $result = @\file_get_contents($this->getUrl(), false, $context);

        if ($result===false) {
            throw new ConnectionException($this->getUrl());
        }
        $this->setResponse($result);
    }
}
