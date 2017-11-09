<?php
/**
 * JSON RPC 2.0 client and server implementation for PHP
 *
 * @author      Michal Szewczyk <ms@msworks.pl>
 * @copyright   Michal Szewczyk
 * @license     MIT
 */
declare(strict_types=1);

require_once __DIR__.'/../../vendor/autoload.php';

$nsMap = [
    'system' => '\MS\Json\Rpc\Server\Handlers\SystemNamespaceHandler',
    'math'   => '\MS\Json\Rpc\Examples\Server\MathNamespaceHandler',
];
$config = new \MS\Json\Rpc\Server\Configuration($nsMap);
$server = (new \MS\Json\Rpc\Server($config))->listen();
echo filter_var($server->getResponse(), FILTER_DEFAULT);
