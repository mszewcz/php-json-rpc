<?php
/**
 * JSON RPC 2.0 client and server implementation for PHP
 *
 * @author      Michal Szewczyk <ms@msworks.pl>
 * @copyright   Michal Szewczyk
 * @license     MIT
 */
declare(strict_types=1);

namespace MS\Json\Rpc\Examples\Server;


use MS\Json\Rpc\Server\Configuration;
use \MS\Json\Rpc\Server\Handlers\AbstractNamespaceHandler;

class MathNamespaceHandler extends AbstractNamespaceHandler
{
    /**
     * Definition of output params for each method available in this API class.
     *
     * @var array
     */
    protected $outputParams = [
        'subtract' => [
            ['type' => 'int', 'always' => true],
        ],
        'sum'      => [
            ['name' => 'sum', 'type' => 'int', 'always' => true],
            ['name' => 'message', 'type' => 'string', 'always' => true],
        ],
    ];

    /**
     * MathNamespaceHandler constructor.
     *
     * @param Configuration $configuration
     */
    public function __construct(Configuration $configuration)
    {
        parent::__construct($configuration);
    }

    /**
     * @param int $minuend
     * @param int $subtrahend
     * @return int
     */
    protected function subtract(int $minuend, int $subtrahend)
    {
        return $minuend-$subtrahend;
    }

    /**
     * @param int $numberA
     * @param int $numberB
     * @return array
     */
    protected function sum(int $numberA, int $numberB)
    {
        return ['sum' => $numberA+$numberB, 'message' => 'Here you are!'];
    }
}
