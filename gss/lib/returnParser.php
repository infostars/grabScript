<?php

namespace greevex\gss\lib;
/**
 * @author greevex
 * @date: 10/1/14 6:10 PM
 */

class returnParser
{
    const TYPE = 'return';
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
                case 'RETURN':
                    break;
                case 'VARIABLE':
                case 'VARIABLE_PATH':
                case 'STRING':
                case 'STRING_QUOTED':
                case 'INTEGER':
                case 'BOOL_TRUE':
                case 'BOOL_FALSE':
                case 'NULL':
                    $this->variableCallData['meta']['put_result_to'] = $lineToken;
                    break;
            }
        }
        if(!isset($this->variableCallData['meta']['put_result_to'])) {
            error::throwNewException("Expected something to return", $ftoken['line'], $ftoken['pos']);
        }
    }

    public function dump()
    {
        return $this->variableCallData;
    }
}