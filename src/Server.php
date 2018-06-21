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
use MS\Json\Rpc\Server\Error;
use MS\Json\Rpc\Server\Exceptions\InvalidRequestException;
use MS\Json\Rpc\Server\Exceptions\ParseErrorException;
use MS\Json\Rpc\Server\Exceptions\ServerErrorException;
use MS\Json\Rpc\Server\Response;
use MS\Json\Rpc\Shared\Headers;
use MS\Json\SchemaValidator\Validator;
use MS\Json\Utils\Utils;

final class Server
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
     * Request body
     *
     * @var string|null
     */
    private $request = null;
    /**
     * Server response
     *
     * @var string|null
     */
    private $response = null;
    /**
     * Whether received request is batch request or not
     *
     * @var bool
     */
    private $isBatchRequest = true;

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
        $this->request = \file_get_contents('php://input');
        $this->setCacheHeaders();
    }

    /**
     * Encodes response
     *
     * @param array $data
     * @return string
     * @throws ServerErrorException
     */
    private function encode(array $data): string
    {
        try {
            return $this->utils->encode($data);
        } catch (\Exception $e) {
            throw new ServerErrorException($e->getMessage(), -32002);
        }
    }

    /**
     * @param string $request
     * @return Server
     */
    public function provideRequest(string $request): Server
    {
        $this->request = $request;
        return $this;
    }

    /**
     * Adds response headers
     *
     * @param array $headers
     * @return Server
     */
    public function addHeaders(array $headers): Server
    {
        $this->headers->addHeaders($headers);
        return $this;
    }

    /**
     * Removes response headers
     *
     * @param array|string $headers
     * @return Server
     */
    public function removeHeaders($headers): Server
    {
        $this->headers->removeHeaders($headers);
        return $this;
    }

    /**
     * Clears all response headers
     *
     * @return Server
     */
    public function clearHeaders(): Server
    {
        $this->headers->clearHeaders();
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
     * Prepares response to request
     */
    private function prepareResponse(): void
    {
        try {
            $responses = [];
            $requests = $this->decodeRequest();

            foreach ($requests as $request) {
                $response = $this->processSingleRequest($request);

                if ($response !== null) {
                    $responses[] = $response;
                }
            }
            if (\count($responses) === 0) {
                return;
            }
            if (\count($responses) === 1 && !$this->isBatchRequest) {
                $responses = \array_shift($responses);
            }
        } catch (\Exception $e) {
            // always return this error as it's thrown when incorrect json received
            $error = (new Error($e->getCode(), $e->getMessage()))->build();
            $responses = (new Response(null, $error, null))->build();
        }

        try {
            $this->response = $this->encode($responses);
        } catch (ServerErrorException $e) {
            $error = (new Error($e->getCode(), $e->getMessage(), $e->getData()))->build();
            $response = (new Response(null, $error, null))->build();
            $this->response = \json_encode($response);
        }
    }

    /**
     * Decodes client's request
     *
     * @throws  ParseErrorException
     * @throws  InvalidRequestException
     * @return  array
     */
    private function decodeRequest(): array
    {
        try {
            $decoded = $this->utils->decode($this->request);
        } catch (\Exception $exception) {
            throw new ParseErrorException();
        }
        if (\is_array($decoded) && \count($decoded) == 0) {
            throw new InvalidRequestException();
        }
        if (!\is_numeric(\array_keys($decoded)[0])) {
            $decoded = [$decoded];
            $this->isBatchRequest = false;
        }
        return $decoded;
    }

    /**
     * Processes single request
     *
     * @param   mixed $request Single request
     * @return  array|null
     */
    private function processSingleRequest($request): ?array
    {
        $jsonRpcRequestSchema = [
            'type'       => 'object',
            'properties' => [
                'jsonrpc' => ['const' => '2.0'],
                'method'  => ['type' => 'string'],
                'params'  => ['oneOf' => [['type' => 'object'], ['type' => 'array']]],
                'id'      => ['oneOf' => [['type' => 'integer'], ['type' => 'string']]],
            ],
            'required'   => ['jsonrpc', 'method'],
        ];

        try {
            $validator = new Validator($jsonRpcRequestSchema);
            if (!$validator->validate($request)) {
                throw new InvalidRequestException();
            }
        } catch (\Exception $e) {
            $requestID = isset($request['id']) ? $request['id'] : null;
            $error = (new Error($e->getCode(), $e->getMessage()))->build();
            return (new Response(null, $error, $requestID))->build();
        }

        $requestMethod = isset($request['method']) ? $request['method'] : null;
        $requestID = isset($request['id']) ? $request['id'] : null;

        return $this->invokeHandler($requestMethod, $request, $requestID);
    }

    /**
     * Invokes namespace handler and returns response
     *
     * @param string|null $method
     * @param array       $request
     * @param mixed       $requestID
     * @return array|null
     */
    private function invokeHandler(?string $method, array $request, $requestID): ?array
    {
        try {
            $namespaceHandler = $this->config->getNamespaceHandler();
            $result = $namespaceHandler->invoke($method, $request);
            $response = (new Response($result, null, $requestID))->build();

            $this->setCacheHeaders($namespaceHandler->getCacheTTL());
        } catch (ServerErrorException $e) {
            $error = (new Error($e->getCode(), $e->getMessage(), $e->getData()))->build();
            $response = (new Response(null, $error, $requestID))->build();
        } catch (\Exception $e) {
            $error = (new Error($e->getCode(), $e->getMessage()))->build();
            $response = (new Response(null, $error, $requestID))->build();
        }

        if ($requestID !== null) {
            // return response only for requests, skip for notifications
            return $response;
        }

        // return null for notifications
        return null;
    }

    /**
     * Receives requests and builds reply if needed
     *
     * @return  Server
     */
    public function listen(): Server
    {
        $options = ['options' => ['default' => $this->config->getDefaultRequestMethod()]];
        $requestMethod = \filter_input(\INPUT_SERVER, 'REQUEST_METHOD', \FILTER_DEFAULT, $options);

        switch ($requestMethod) {
            case 'OPTIONS':
                // @codeCoverageIgnoreStart
                $headers = \getallheaders();
                $headers = \array_merge(['Content-Type', 'Authorization'], \array_keys($headers));
                $headers = \implode(',', \array_unique($headers));
                $this->addHeaders(['name' => 'Access-Control-Allow-Headers', 'value' => $headers]);
                break;
                // @codeCoverageIgnoreEnd
            case 'POST':
                $this->prepareResponse();
                break;
            default:
        }
        return $this;
    }

    /**
     * Returns server response
     *
     * @return string|null
     */
    public function getResponse(): ?string
    {
        $this->sendHeaders();
        return $this->response;
    }
}
