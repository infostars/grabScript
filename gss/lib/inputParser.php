<?php

namespace greevex\gss\lib;
/**
 * @author greevex
 * @date: 10/1/14 6:10 PM
 */

class inputParser
{
    const TYPE = 'input';
    const CAN_WAIT = false;

    private $variableCallData = [
        'type' => self::TYPE,
        'meta' => [

        ]
    ];

    public function __construct($lineTokens)
    {
        $ftoken = reset($lineTokens);
        foreach($lineTokens as $lineToken) {
            switch($lineToken['type']) {
                case 'VARIABLE':
                    $this->variableCallData['meta'][] = $lineToken;
                    break;
            }
        }
        if(!$this->variableCallData['meta']) {
            error::throwNewException("Unable to find callable", $ftoken['line'], $ftoken['pos']);
        }
    }

    public function dump()
    {
        return $this->variableCallData;
    }
}