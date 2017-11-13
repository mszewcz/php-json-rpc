<?php
/**
 * JSON RPC 2.0 client and server implementation for PHP
 *
 * @author      Michal Szewczyk <ms@msworks.pl>
 * @copyright   Michal Szewczyk
 * @license     MIT
 */
declare(strict_types=1);

namespace MS\Json\Rpc\Client;


class Notification extends Request
{
    /**
     * Notification constructor
     *
     * @param   string $method Method name
     * @param   array  $data   Notification data
     */
    public function __construct(string $method, array $data = [])
    {
        parent::__construct(null, $method, $data);
    }
}
