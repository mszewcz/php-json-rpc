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


use \MS\Json\Rpc\Client;
use \MS\Json\Rpc\Client\Notification;
use \MS\Json\Rpc\Client\Request;
use \MS\Json\Rpc\Client\Transport\CurlTransport;

function getServerUrl()
{
    $options = ['options' => ['default' => false]];
    $protocol = \filter_input(\INPUT_SERVER, 'HTTPS', \FILTER_DEFAULT, $options)===true ? 'https' : 'http';
    $options = ['options' => ['default' => '']];
    $serverName = \filter_input(\INPUT_SERVER, 'SERVER_NAME', \FILTER_DEFAULT, $options);
    $phpSelf = \filter_input(\INPUT_SERVER, 'PHP_SELF', \FILTER_DEFAULT, $options);

    $path = str_replace('client', 'server', dirname($phpSelf).'/');
    return $protocol.'://'.$serverName.$path;
}

$url = getServerUrl();

// Request server configuration using default transport class (StreamContextTransport)
$namespace = 'system/';

$text = '<pre>';
$text .= \sprintf('Request server configuration using default transport class (StreamContextTransport)%s', PHP_EOL);
$text .= \sprintf('-----------------------------------------------------------------------------------%s', PHP_EOL);
$text .= \sprintf('Response:%s%s', PHP_EOL, PHP_EOL);

try {
    $client = new Client($url.$namespace);
    $client->add(new Request(1, 'getConfiguration'));
    $client->send();
    $response = $client->getResponse();

    if ($response!==null) {
        $text .= json_encode($response, JSON_PRETTY_PRINT).PHP_EOL.PHP_EOL;
    }
} catch (\Exception $e) {
    $text .= $e->getMessage().PHP_EOL.PHP_EOL;
}

// Single request server 'math/subtract(42, 23)' method using cURL transport class
$namespace = 'math/';

$text .= \sprintf('Single request server \'math/sum(2, 5)\' method using cURL transport class%s', PHP_EOL);
$text .= \sprintf('------------------------------------------------------------------------%s', PHP_EOL);
$text .= \sprintf('Response:%s%s', PHP_EOL, PHP_EOL);

try {
    $client = new MS\Json\Rpc\Client($url.$namespace, new CurlTransport());
    $client->add(new Request(2, 'sum', [2, 5]));
    $client->send();
    $response = $client->getResponse();

    if ($response!==null) {
        $text .= json_encode($response, JSON_PRETTY_PRINT).PHP_EOL.PHP_EOL;
    }
} catch (\Exception $e) {
    $text .= $e->getMessage().PHP_EOL.PHP_EOL;
}

// Notification to server 'math/subtract()' method
$text .= \sprintf('Notification to server \'math/subtract()\' method%s', PHP_EOL);
$text .= \sprintf('-----------------------------------------------%s', PHP_EOL);
$text .= \sprintf('Response:%s%s', PHP_EOL, PHP_EOL);

try {
    $client = new MS\Json\Rpc\Client($url.$namespace);
    $client->add(new Notification('subtract', [42, 23]));
    $client->send();
    $response = $client->getResponse();

    if ($response!==null) {
        $text .= json_encode($response, JSON_PRETTY_PRINT).PHP_EOL.PHP_EOL;
    }
} catch (\Exception $e) {
    $text .= $e->getMessage().PHP_EOL.PHP_EOL;
}

// Batch request to server 'math/subtract()' method
$text .= \sprintf('Batch request to server \'math/subtract()\' and \'math/sum()\' methods%s', PHP_EOL);
$text .= \sprintf('------------------------------------------------------------------%s', PHP_EOL);
$text .= \sprintf('Response:%s%s', PHP_EOL, PHP_EOL);

try {
    $client = new MS\Json\Rpc\Client($url.$namespace);
    $client->add(new Request(1, 'subtract', [42, 23]));
    $client->add(new Request(2, 'sum', [10, 5]));
    $client->add(new Request(3, 'subtract', [20, 30]));
    $client->send();
    $response = $client->getResponse();

    if ($response!==null) {
        $text .= json_encode($response, JSON_PRETTY_PRINT).PHP_EOL.PHP_EOL;
    }
} catch (\Exception $e) {
    $text .= $e->getMessage().PHP_EOL.PHP_EOL;
}

// Batch request to server 'math/subtract()' method
$text .= \sprintf('Batch request & notification to server \'math/subtract()\' method%s', PHP_EOL);
$text .= \sprintf('---------------------------------------------------------------%s', PHP_EOL);
$text .= \sprintf('Response:%s%s', PHP_EOL, PHP_EOL);

try {
    $client = new MS\Json\Rpc\Client($url.$namespace);
    $client->add(new Request(1, 'subtract', [22, 41]));
    $client->add(new Notification('subtract', [11, 8]));
    $client->add(new Notification('subtract', [16, 3]));
    $client->add(new Request(2, 'subtract', [45, 15]));
    $client->add(new Request(3, 'subtract', [11, 3]));
    $client->send();
    $response = $client->getResponse();

    if ($response!==null) {
        $text .= json_encode($response, JSON_PRETTY_PRINT).PHP_EOL.PHP_EOL;
    }
} catch (\Exception $e) {
    $text .= $e->getMessage().PHP_EOL.PHP_EOL;
}

$text .= '</pre>';
echo filter_var($text, FILTER_DEFAULT);
