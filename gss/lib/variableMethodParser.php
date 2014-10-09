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
        foreach($lineTokens as $lineToken) {
            switch($lineToken['type']) {
                case 'VARIABLE_METHOD':
                    $varData = explode('.', $lineToken['value']);
                    $this->variableCallData['meta']['callable'] = [
                        'varname' => $varData[0],
                        'method' => $varData[1],
                    ];
                    break;
                case 'STRING_QUOTED':
                    $this->variableCallData['meta']['params'][] = $lineToken;
                    break;
                case 'VARIABLE':
                    $this->variableCallData['meta']['params'][] = $lineToken;
                    break;
            }
        }
        if(!isset($this->variableCallData['meta']['callable'])) {
            return error::throwNewException("Unable to find callable", $lineTokens[0]['line'], $lineTokens[0]['pos']);
        }
    }

    public function dump()
    {
        return $this->variableCallData;
    }
}