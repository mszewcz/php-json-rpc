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


use MS\Json\Rpc\Client\Exceptions\ConnectionException;
use MS\Json\Rpc\Client\Exceptions\NoRequestsException;
use MS\Json\Rpc\Client\Transport\AbstractTransport;
use MS\Json\Rpc\Client\Transport\StreamContextTransport;
use MS\Json\Rpc\Client\Request;
use MS\Json\Utils\Exceptions\DecodingException;
use MS\Json\Utils\Exceptions\EncodingException;
use MS\Json\Utils\Utils;

if (!defined('JSON_WEB_TOKEN_COOKIE')) {
    define('JSON_WEB_TOKEN_COOKIE', 'JWTOKEN');
}

class Client
{
    /**
     * Utils class
     *
     * @var Utils
     */
    private $utils;
    /**
     * Transport class
     *
     * @var AbstractTransport|null
     */
    private $transportClass = null;
    /**
     * Requests queue
     *
     * @var array
     */
    private $queue = [];
    /**
     * JSON web token from cookie
     *
     * @var string|null
     */
    private $jsonWebToken = null;
    /**
     * XSRF-TOKEN from cookie
     *
     * @var string|null
     */
    private $xsrfToken = null;

    /**
     * Client constructor.
     *
     * @param string                 $serverUrl
     * @param AbstractTransport|null $transportClass
     */
    public function __construct(string $serverUrl, ?AbstractTransport $transportClass = null)
    {
        $this->utils = new Utils();
        $this->transportClass = new StreamContextTransport();
        if (($transportClass!==null) && ($transportClass instanceof AbstractTransport)) {
            $this->transportClass = $transportClass;
        }
        $this->transportClass->setUrl($serverUrl);
        $this->setTokens();
    }

    /**
     * Gets tokens (JSON web token & XSRF token) from cookie and sets class variables
     *
     * @return void
     */
    private function setTokens(): void
    {
        $options = ['options' => ['default' => null]];
        $this->jsonWebToken = \filter_input(\INPUT_COOKIE, JSON_WEB_TOKEN_COOKIE, \FILTER_SANITIZE_STRING, $options);
        $this->xsrfToken = \filter_input(\INPUT_COOKIE, 'XSRF-TOKEN', \FILTER_SANITIZE_STRING, $options);
    }

    /**
     * Transforms array to JSON
     *
     * @param array $array
     * @return string
     * @throws EncodingException
     */
    private function encode(array $array): string
    {
        return $this->utils->encode($array);
    }

    /**
     * Transforms JSON response to array
     *
     * @param string $string
     * @return array|null
     * @throws DecodingException
     */
    private function decode(string $string): ?array
    {
        return $this->utils->decode($string);
    }

    /**
     * Adds request header(s)
     *
     * @param array $headers
     * @return void
     */
    public function addHeaders($headers): void
    {
        $this->transportClass->addHeaders($headers);
    }

    /**
     * Removes request header(s)
     *
     * @param array|string $headers
     * @return void
     */
    public function removeHeaders($headers): void
    {
        $this->transportClass->removeHeaders($headers);
    }

    /**
     * Clears request headers
     *
     * @return void
     */
    public function clearHeaders(): void
    {
        $this->transportClass->clearHeaders();
    }

    /**
     * Returns current request headers
     *
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->transportClass->getHeaders();
    }

    /**
     * Adds request to queue
     *
     * @param Request $request
     * @return void
     */
    public function add(Request $request): void
    {
        $this->queue[] = $request->build();
    }

    /**
     * Returns requests queue
     *
     * @return array
     */
    public function getQueue(): array
    {
        return $this->queue;
    }

    /**
     * Clears requests queue
     *
     * @return void
     */
    public function clearQueue()
    {
        $this->queue = [];
    }

    /**
     * Sends requests
     *
     * @throws ConnectionException
     * @throws EncodingException
     * @throws NoRequestsException
     */
    public function send(): void
    {
        if (\count($this->queue)===0) {
            throw new NoRequestsException();
        }
        if (\count($this->queue)===1) {
            $this->queue = \array_shift($this->queue);
        }
        $data = $this->encode($this->queue);

        $this->transportClass->addHeaders(['name' => 'Content-Length', 'value' => \strlen($data)]);
        if ($this->jsonWebToken!==null) {
            // @codeCoverageIgnoreStart
            $this->transportClass->addHeaders(['name' => 'Authorization', 'value' => 'Bearer '.$this->jsonWebToken]);
            // @codeCoverageIgnoreEnd
        } elseif ($this->xsrfToken!==null) {
            // @codeCoverageIgnoreStart
            $this->transportClass->addHeaders(['name' => 'X-XSRF-TOKEN', 'value' => $this->xsrfToken]);
            // @codeCoverageIgnoreEnd
        }
        $this->transportClass->setData($data);

        try {
            $this->transportClass->send();
            $this->clearQueue();
        } catch (ConnectionException $e) {
            throw $e;
        }
    }

    /**
     * Returns response
     *
     * @return array|null
     * @throws DecodingException
     */
    public function getResponse(): ?array
    {
        $response = $this->transportClass->getResponse();
        return $response!==null ? $this->decode($response) : null;
    }
}
