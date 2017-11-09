<?php
/**
 * JSON RPC 2.0 client and server implementation for PHP
 *
 * @author      Michal Szewczyk <ms@msworks.pl>
 * @copyright   Michal Szewczyk
 * @license     MIT
 */
declare(strict_types=1);

namespace MS\Json\Rpc;


use MS\Json\Rpc\Server\Configuration;
use MS\Json\Rpc\Server\SchemaProvider;
use MS\Json\Rpc\Shared\Headers;
use MS\Json\Utils\Exceptions\EncodingException;
use MS\Json\Utils\Utils;

final class SchemaServer
{
    /**
     * Utils class
     *
     * @var Utils
     */
    private $utils;
    /**
     * Server config
     *
     * @var Configuration
     */
    private $config;
    /**
     * Response headers
     *
     * @var Headers
     */
    private $headers;
    /**
     * Server response
     *
     * @var array
     */
    private $response = [];
    /**
     * Namespace name
     *
     * @var string
     */
    private $nsName = '';
    /**
     * Method name
     *
     * @var string
     */
    private $methodName = '';
    /**
     * Schema type
     *
     * @var string
     */
    private $schemaType = '';

    /**
     * Server constructor.
     *
     * @param Configuration $config
     */
    public function __construct(Configuration $config)
    {
        $this->utils = new Utils();
        $this->config = $config;
        $this->headers = new Headers([
            ['name' => 'Content-Type', 'value' => 'application/json; charset=utf-8'],
            ['name' => 'Access-Control-Allow-Origin', 'value' => '*'],
        ]);
        $this->setCacheHeaders();
        $this->parseUrlParams();
    }

    /**
     * Parses url params and sets required variables
     */
    private function parseUrlParams(): void
    {
        $options = ['options' => ['default' => '/system/getConfiguration/unknown-schema.json']];
        $pathInfo = \filter_input(\INPUT_SERVER, 'PATH_INFO', \FILTER_DEFAULT, $options);
        $pathInfo = explode('/', \trim($pathInfo, '/'));

        if (isset($pathInfo[0])) {
            $this->nsName = $pathInfo[0];
        }
        if (isset($pathInfo[1])) {
            $this->methodName = $pathInfo[1];
        }
        if (isset($pathInfo[2])) {
            \preg_match('/^(input|output|unknown)-schema\.json$/', $pathInfo[2], $matches);
            if (isset($matches[1])) {
                $this->schemaType = $matches[1];
            }
        }
    }

    /**
     * Encodes response
     *
     * @param array $data
     * @return string
     */
    private function encode(array $data): string
    {
        try {
            return count($data)===0 ? '{}' : $this->utils->encode($data);
            // @codeCoverageIgnoreStart
        } catch (EncodingException $e) {
            die($e->getMessage());
            // @codeCoverageIgnoreEnd
        }
    }

    /**
     * Adds response headers
     *
     * @param array $headers
     * @return SchemaServer
     */
    public function addHeaders(array $headers): SchemaServer
    {
        $this->headers->addHeaders($headers);
        return $this;
    }

    /**
     * Removes response headers
     *
     * @param array|string $headers
     * @return SchemaServer
     */
    public function removeHeaders($headers): SchemaServer
    {
        $this->headers->removeHeaders($headers);
        return $this;
    }

    /**
     * Returns response headers
     *
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers->getHeaders();
    }

    /**
     * Sets cache specific headers
     *
     * @param int $ttl
     * @return void
     */
    private function setCacheHeaders(int $ttl = 0): void
    {
        $maxAge = $ttl !== 0 ? \sprintf('max-age=%s', $ttl) : 'no-cache, no-store, must-revalidate';
        $expiresTime = $ttl !== 0 ? \time() + $ttl : \time() - 1;

        $headers = [
            ['name' => 'Cache-Control', 'value' => $maxAge],
            ['name' => 'Expires', 'value' => \gmdate('D, d M Y H:i:s \G\M\T', $expiresTime)],
        ];

        if ($ttl === 0) {
            $headers[] = ['name' => 'Pragma', 'value' => 'no-cache'];
        }

        $this->addHeaders($headers);
    }

    /**
     * Sends response headers
     *
     * @return  void
     */
    private function sendHeaders(): void
    {
        $headers = $this->getHeaders();
        foreach ($headers as $header) {
            \header($header);
        }
    }

    /**
     * Builds response
     *
     * @return SchemaServer
     * @throws \MS\Json\Utils\Exceptions\DecodingException
     */
    public function listen(): SchemaServer
    {
        $schemaProvider = new SchemaProvider($this->config);
        $this->response = $schemaProvider->getSchema($this->nsName, $this->methodName, $this->schemaType);
        return $this;
    }

    /**
     * Returns server response
     *
     * @return string
     * @throws EncodingException
     */
    public function getResponse(): string
    {
        $this->sendHeaders();
        return $this->encode($this->response);
    }
}
