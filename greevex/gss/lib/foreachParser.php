<?php

namespace greevex\gss\lib;
/**
 * @author greevex
 * @date: 10/1/14 6:10 PM
 */

class foreachParser
{
    protected static $instance;

    public static function getInstance()
    {
        return self::$instance;
    }

    public static function dropInstance()
    {
        self::$instance = null;
    }

    public static function hasInstance()
    {
        return isset(self::$instance);
    }

    const TYPE = 'foreach';
    const CAN_WAIT = true;

    private $foreachData = [
        'type' => self::TYPE,
        'meta' => [
            'params' => [],
        ]
    ];

    public function __construct($lineTokens)
    {
        $ftoken = reset($lineTokens);
        foreach($lineTokens as $lineToken) {
            switch($lineToken['type']) {
                case 'VARIABLE_METHOD':
                case 'VARIABLE':
                case 'VARIABLE_PATH':
                case 'OBJECT_PATH':
                case 'OBJECT_METHOD':
                    $this->foreachData['meta']['from'] = $lineToken;
                    break;
            }
        }
        if(!isset($this->foreachData['meta']['from'])) {
            error::throwNewException("Unable to find any iterator object or variable", $ftoken['line'], $ftoken['pos']);
        }

        self::$instance = $this;
    }

    public function eatParams($lineTokens)
    {
//        var_dump($lineTokens);
        $ftoken = array_shift($lineTokens);
        switch($ftoken['type']) {
            case 'PARAM_SET':
                $set = [];
                foreach($lineTokens as $lineToken) {
                    if(!isset($set['param']) && $lineToken['type'] == 'OBJECT') {
                        $set['param'] = $lineToken['value'];
                    } elseif(isset($set['param'])) {
                        $set['value'] = $lineToken;
                    }
                }
                if(!isset($set['param'], $set['value'])) {
                    return error::throwNewException('No param or value', $ftoken['line'], $ftoken['pos']);
                }
                $this->foreachData['meta']['params'][$set['param']] = $set['value'];
                return true;
                break;
            case 'PARAM_CALL':
                array_shift($lineTokens);
                $call = [];
                foreach($lineTokens as $lineToken) {
                    switch($lineToken['type']) {
                        case 'WHITESPACE':
                            continue 2;
                        case 'THIS_METHOD':
                        case 'VARIABLE_METHOD':
                        case 'OBJECT_METHOD':
                        case 'OBJECT_PATH':
                            $call['callable'] = $lineToken;
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
                            if(!isset($call['arguments'])) {
                                $call['arguments'] = [];
                            }
                            $call['arguments'][] = $lineToken;
                            break;
                        default:
                            return error::throwNewException("Unexpected {$lineToken['type']}", $lineToken['line'], $lineToken['pos']);
                            break;
                    }
                }
                if(empty($call)) {
                    return error::throwNewException("Expected call params", $ftoken['line'], $ftoken['pos']);
                }
                if(!isset($this->foreachData['meta']['call'])) {
                    $this->foreachData['meta']['call'] = [];
                }
                $this->foreachData['meta']['call'][] = $call;
                return true;
                break;
            case 'PARAM_PUT_RESULT':
                array_shift($lineTokens);
                foreach($lineTokens as $lineToken) {
                    switch($lineToken['type']) {
                        case 'WHITESPACE':
                            continue 2;
                        case 'VARIABLE':
                            $this->foreachData['meta']['put_result_to'] = $lineToken;
                            break;
                        default:
                            return error::throwNewException("Unexpected {$lineToken['type']}", $lineToken['line'], $lineToken['pos']);
                            break;
                    }
                }
                if(!isset($this->foreachData['meta']['put_result_to'])) {
                    return error::throwNewException("Expected variable for result", $ftoken['line'], $ftoken['pos']);
                }
                return true;
                break;
            default:
                return false;
                break;
        }
    }

    public function dump()
    {
        return $this->foreachData;
    }
}