<?php
/**
 * JSON RPC 2.0 client and server implementation for PHP
 *
 * @author      Michal Szewczyk <ms@msworks.pl>
 * @copyright   Michal Szewczyk
 * @license     MIT
 */
declare(strict_types=1);

namespace MS\Json\Rpc\Shared;


class Headers
{
    /**
     * Headers array
     *
     * @var array
     */
    private $headers = [];

    /**
     * Headers constructor.
     *
     * @param array $defaultHeaders
     */
    public function __construct(array $defaultHeaders = [])
    {
        if (count($defaultHeaders)>0) {
            $this->addHeaders($defaultHeaders);
        }
    }

    /**
     * Adds response headers
     *
     * @param array $headers
     * @return void
     */
    public function addHeaders(array $headers): void
    {
        if (isset($headers['name']) && isset($headers['value'])) {
            $headers = [$headers];
        }
        foreach ($headers as $header) {
            if (isset($header['name']) && isset($header['value'])) {
                $this->headers[$header['name']] = $header['value'];
            }
        }
    }

    /**
     * Removes response headers
     *
     * @param array|string $headers
     * @return void
     */
    public function removeHeaders($headers): void
    {
        if (\is_string($headers)) {
            $headers = [$headers];
        }
        foreach ($headers as $header) {
            if (\array_key_exists($header, $this->headers)) {
                unset($this->headers[$header]);
            }
        }
    }

    /**
     * Clears all response headers
     *
     * @return void
     */
    public function clearHeaders(): void
    {
        $this->headers = [];
    }

    /**
     * Returns current request headers
     *
     * @return array
     */
    final public function getHeaders(): array
    {
        $headers = [];
        foreach ($this->headers as $headerName => $headerValue) {
            $headers[] = \sprintf('%s: %s', $headerName, $headerValue);
        }
        return $headers;
    }
}
