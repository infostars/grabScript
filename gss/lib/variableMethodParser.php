<?php

namespace greevex\gss\lib;
/**
 * @author greevex
 * @date: 10/1/14 6:10 PM
 */

class variableMethodParser
{
    const TYPE = 'variable_call';
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
                case 'VARIABLE_METHOD':
                    $varData = explode('.', $lineToken['value']);
                    $this->variableCallData['meta']['callable'] = [
                        'varName' => $varData[0],
                        'method' => $varData[1],
                        'varData' => $lineToken
                    ];
                    break;
                case 'VARIABLE':
                case 'VARIABLE_PATH':
                case 'STRING':
                case 'STRING_QUOTED':
                case 'INTEGER':
                case 'FLOAT':
                case 'BOOL_TRUE':
                case 'BOOL_FALSE':
                case 'NULL':
                    $this->variableCallData['meta']['arguments'][] = $lineToken;
                    break;
            }
        }
        if(!isset($this->variableCallData['meta']['callable'])) {
            error::throwNewException("Unable to find callable", $ftoken['line'], $ftoken['pos']);
        }
    }

    public function dump()
    {
        return $this->variableCallData;
    }
}