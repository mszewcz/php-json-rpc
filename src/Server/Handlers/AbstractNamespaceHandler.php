<?php
/**
 * JSON RPC 2.0 client and server implementation for PHP
 *
 * @author      Michal Szewczyk <ms@msworks.pl>
 * @copyright   Michal Szewczyk
 * @license     MIT
 */
declare(strict_types=1);

namespace MS\Json\Rpc\Server\Handlers;


use MS\Json\Rpc\Server\Configuration;
use MS\Json\Rpc\Server\Exceptions\InvalidParamsException;
use MS\Json\Rpc\Server\Exceptions\MethodNotFoundException;
use MS\Json\Rpc\Server\InputParamsValidator;

abstract class AbstractNamespaceHandler
{
    /**
     * Server config object instance
     *
     * @var Configuration
     */
    protected $config;
    /**
     * Input params validator object
     *
     * @var InputParamsValidator
     */
    private $paramsValidator;
    /**
     * @var string
     */
    protected $nsName = '';
    /**
     * Namespace cache TTL in seconds (0 = cache disabled)
     *
     * @var int
     */
    private $cacheTTL = 0;

    /**
     * AbstractNamespaceHandler constructor.
     *
     * @param Configuration $config
     */
    public function __construct(Configuration $config)
    {
        $this->config = $config;
        $this->nsName = $this->config->getCurrentNamespaceName();
        $this->paramsValidator = new InputParamsValidator($config);
    }

    /**
     * Enables namespace cache
     *
     * @param int $ttl
     * @return void
     */
    final public function enableCache(int $ttl = 60): void
    {
        $this->cacheTTL = $ttl;
    }

    /**
     * Disables namespace cache
     *
     * @return void
     */
    final public function disableCache(): void
    {
        $this->cacheTTL = 0;
    }

    /**
     * Return namespace cache TTL
     *
     * @return int
     */
    final public function getCacheTTL(): int
    {
        return $this->cacheTTL;
    }

    /**
     * Sorts params according to method's input schema
     *
     * @param   string $methodName Method name
     * @param   array  $params     Array of params passed to method
     * @return  array
     */
    final private function sortParams(string $methodName, array $params): array
    {
        if (\count($params)!==0) {
            if (\is_numeric(\array_keys($params)[0])) {
                return $params;
            }
        }

        $inputParams = $this->config->getInputParams($this->nsName, $methodName);
        $sortedParams = [];

        foreach ($inputParams as $inputParam) {
            $sortedParams[] = $params[$inputParam];
        }

        return $sortedParams;
    }

    /**
     * Invokes API method and returns result or error in case method wasn't found
     *
     * @param string $methodName
     * @param array  $request
     * @return mixed
     * @throws InvalidParamsException
     * @throws MethodNotFoundException
     * @throws \MS\Json\Utils\Exceptions\DecodingException
     */
    final public function invoke(string $methodName = '', array $request = [])
    {
        if (\in_array($methodName, \get_class_methods($this))) {
            if ($this->paramsValidator->validate($this->nsName, $methodName, $request)) {
                $params = isset($request['params']) ? (array)$request['params'] : [];

                return \call_user_func_array([$this, $methodName], $this->sortParams($methodName, $params));
            }
            throw new InvalidParamsException();
        }
        throw new MethodNotFoundException();
    }
}
