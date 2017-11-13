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
use MS\Json\Rpc\Shared\Headers;

abstract class AbstractTransport
{
    /**
     * Server url
     *
     * @var string
     */
    private $url = '';
    /**
     * Headers array
     *
     * @var Headers
     */
    private $headers;
    /**
     * Data to send
     *
     * @var string
     */
    private $data = '';
    /**
     * Received response
     *
     * @var null|string
     */
    private $response = null;

    /**
     * AbstractTransport constructor.
     */
    public function __construct()
    {
        $this->headers = new Headers([
            ['name' => 'Content-Type', 'value' => 'application/json; charset=utf-8'],
            ['name' => 'Accept', 'value' => 'application/json'],
        ]);
    }

    /**
     * Sets server url
     *
     * @param string $url
     * @return void
     */
    final public function setUrl(string $url): void
    {
        $this->url = $url;
    }

    /**
     * Returns server url
     *
     * @return string
     */
    final public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * Adds request headers
     *
     * @param array $headers
     * @return void
     */
    final public function addHeaders(array $headers): void
    {
        $this->headers->addHeaders($headers);
    }

    /**
     * Removes request headers
     *
     * @param array|string $headers
     * @return void
     */
    final public function removeHeaders($headers): void
    {
        $this->headers->removeHeaders($headers);
    }

    /**
     * Clears request headers
     *
     * @return void
     */
    final public function clearHeaders(): void
    {
        $this->headers->clearHeaders();
    }

    /**
     * Returns current request headers
     *
     * @return array
     */
    final public function getHeaders(): array
    {
        return $this->headers->getHeaders();
    }

    /**
     * Sets request data
     *
     * @param string $data
     * @return void
     */
    final public function setData(string $data): void
    {
        $this->data = $data;
    }

    /**
     * Returns request data
     *
     * @return string
     */
    final public function getData(): string
    {
        return $this->data;
    }

    /**
     * Sets server response
     *
     * @param string $response
     */
    final protected function setResponse(string $response): void
    {
        $this->response = $response;
    }

    /**
     * Returns server response
     *
     * @return string|null
     */
    final public function getResponse(): ?string
    {
        return \is_null($this->response) || trim($this->response)==='' ? null : $this->response;
    }

    /**
     * Sends request to server
     *
     * @throws ConnectionException
     * @return void
     */
    abstract public function send(): void;
}
