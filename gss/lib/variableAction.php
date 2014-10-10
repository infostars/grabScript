<?php

namespace greevex\gss\lib;
/**
 * @author greevex
 * @date: 10/1/14 6:10 PM
 */

class variableAction
{
    const TYPE = 'variable_action';
    const CAN_WAIT = false;

    private $variableActionData = [
        'type' => self::TYPE,
        'meta' => [
            'item' => null,
            'type' => 'assign',
            'value_type' => null,
            'value' => [],
        ]
    ];

    public function __construct($lineTokens)
    {
        $ftoken = reset($lineTokens);
        foreach($lineTokens as $lineToken) {
            if(!isset($this->variableActionData['meta']['item'])) {
                switch ($lineToken['type']) {
                    case 'VARIABLE_METHOD':
                    case 'VARIABLE':
                    case 'VARIABLE_PATH':
                    case 'OBJECT_METHOD':
                    case 'OBJECT_PATH':
                        $this->variableActionData['meta']['item'] = $lineToken;
                        break;
                }
            } else {
                switch ($lineToken['type']) {
                    case 'VARIABLE_METHOD':
                    case 'VARIABLE':
                    case 'VARIABLE_PATH':
                    case 'OBJECT_METHOD':
                    case 'OBJECT_PATH':
                    case 'STRING':
                    case 'STRING_QUOTED':
                    case 'INTEGER':
                    case 'FLOAT':
                    case 'BOOL_TRUE':
                    case 'BOOL_FALSE':
                    case 'NULL':
                        $this->variableActionData['meta']['value'][] = $lineToken;
                        break;
                    case 'PLUS':
                    case 'MINUS':
                    case 'GT':
                    case 'GTE':
                    case 'LT':
                    case 'LTE':
                        $this->variableActionData['meta']['value_type'] = $lineToken;
                        break;
                }
            }
        }
        $count = count($this->variableActionData['meta']['value']);
        if(!$count) {
            error::throwNewException("Unable to find callable", $ftoken['line'], $ftoken['pos']);
        } elseif($count > 1 && !isset($this->variableActionData['meta']['value_type'])) {
            error::throwNewException("Action required between variables", $ftoken['line'], $ftoken['pos']);
        }
    }

    public function dump()
    {
        return $this->variableActionData;
    }
}