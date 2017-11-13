<?php
/**
 * JSON RPC 2.0 client and server implementation for PHP
 *
 * @author      Michal Szewczyk <ms@msworks.pl>
 * @copyright   Michal Szewczyk
 * @license     MIT
 */
declare(strict_types=1);

namespace MS\Json\Rpc\Server\Handlers\SystemNamespaceHandler\GetConfiguration;


use MS\Json\Rpc\Server\SchemaInterface;

final class InputSchema implements SchemaInterface
{
    /**
     * InputSchema constructor.
     */
    public function __construct()
    {
    }

    /**
     * Returns schema
     *
     * @return string
     */
    public function get(): string
    {
        return \file_get_contents(\str_replace('.php', '.json', __FILE__));
    }
}
