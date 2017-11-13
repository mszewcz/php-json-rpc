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


use MS\Json\SchemaValidator\Validator;

class InputParamsValidator
{
    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * InputParamsValidator constructor.
     *
     * @param Configuration $configuration
     */
    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * Validates provided params against method params
     *
     * @param string $nsName
     * @param string $methodName
     * @param array  $request
     * @return bool
     * @throws \MS\Json\Utils\Exceptions\DecodingException
     */
    public function validate(string $nsName, string $methodName, array $request): bool
    {
        $schemaProvider = new SchemaProvider($this->configuration);
        $schema = $schemaProvider->getSchema($nsName, $methodName, 'input');
        $validator = new Validator($schema);
        return $validator->validate($request);
    }
}
