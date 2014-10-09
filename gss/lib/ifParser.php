<?php

namespace greevex\gss\lib;
/**
 * @author greevex
 * @date: 10/1/14 6:10 PM
 */

class ifParser
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

    const TYPE = 'if';
    const CAN_WAIT = true;

    private $ifData = [
        'type' => self::TYPE,
        'meta' => [
            'condition' => [
                'item1' => null,
                'cond' => null,
                'item2' => null,
            ]
        ]
    ];

    public function __construct($lineTokens)
    {
        $ftoken = reset($lineTokens);
        foreach($lineTokens as $lineToken) {
            if(!isset($this->ifData['meta']['condition']['item1'])
            || isset($this->ifData['meta']['condition']['cond']) && !isset($this->ifData['meta']['condition']['item2'])) {
                switch($lineToken['type']) {
                    case 'VARIABLE':
                    case 'VARIABLE_METHOD':
                    case 'VARIABLE_PATH':
                    case 'OBJECT_METHOD':
                    case 'OBJECT_PATH':
                    case 'STRING':
                    case 'STRING_QUOTED':
                    case 'INTEGER':
                    case 'BOOL_TRUE':
                    case 'BOOL_FALSE':
                    case 'NULL':
                        if(!isset($this->ifData['meta']['condition']['item1'])) {
                            $this->ifData['meta']['condition']['item1'] = $lineToken;
                        } else {
                            $this->ifData['meta']['condition']['item2'] = $lineToken;
                        }
                        break;
                    default:
                        break;
                }
            } elseif(!isset($this->ifData['meta']['condition']['cond'])) {
                switch($lineToken['type']) {
                    case 'EQUALS':
                    case 'NOT_EQUALS':
                    case 'GTE':
                    case 'LTE':
                    case 'GT':
                    case 'LT':
                        $this->ifData['meta']['condition']['cond'] = $lineToken;
                        break;
                    default:
                        break;
                }
            }
        }

        if(!isset($this->ifData['meta']['condition']['item1'])
            || !isset($this->ifData['meta']['condition']['item2'])
            || !isset($this->ifData['meta']['condition']['cond'])) {
            error::throwNewException('Expected two arguments and condition', $ftoken['line'], $ftoken['pos']);
        }

        self::$instance = $this;
    }

    public function eatParams($lineTokens)
    {
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
                $this->ifData['meta']['params'][$set['param']] = $set['value'];
                return true;
                break;
            case 'PARAM_BREAK':
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
                if(!isset($this->ifData['meta']['call'])) {
                    $this->ifData['meta']['call'] = [];
                }
                $this->ifData['meta']['call'][] = $call;
                return true;
                break;
            case 'PARAM_PUT_RESULT':
                array_shift($lineTokens);
                foreach($lineTokens as $lineToken) {
                    switch($lineToken['type']) {
                        case 'WHITESPACE':
                            continue 2;
                        case 'VARIABLE':
                            $this->ifData['meta']['put_result_to'] = $lineToken;
                            break;
                        default:
                            return error::throwNewException("Unexpected {$lineToken['type']}", $lineToken['line'], $lineToken['pos']);
                            break;
                    }
                }
                if(!isset($this->ifData['meta']['put_result_to'])) {
                    return error::throwNewException("Expected variable for result", $ftoken['line'], $ftoken['pos']);
                }
                return true;
                break;
            case 'PARAM':
                return true;
                break;
            default:
                return false;
                break;
        }
    }

    public function dump()
    {
        return $this->ifData;
    }
}