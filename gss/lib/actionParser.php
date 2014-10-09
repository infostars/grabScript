<?php

namespace greevex\gss\lib;
/**
 * @author greevex
 * @date: 10/1/14 6:10 PM
 */

class actionParser
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

    const TYPE = 'action';
    const CAN_WAIT = true;

    private $actionData = [
        'type' => self::TYPE,
        'meta' => [
            'arguments' => [],
            'params' => [],
        ]
    ];

    public function __construct($lineTokens)
    {
        $ftoken = reset($lineTokens);
        foreach($lineTokens as $lineToken) {
            if(!isset($this->actionData['meta']['instanceOf']) && $lineToken['type'] == 'OBJECT') {
                $this->actionData['meta']['instanceOf'] = $lineToken;
                array_shift($lineTokens);
            } elseif($lineToken['type'] == 'COLON') {
                array_shift($lineTokens);
                $this->actionData['meta']['factory'] = array_shift($lineTokens);
            } elseif($lineToken['type'] != 'WHITESPACE') {
                if(isset($this->actionData['meta']['factory']) && $lineToken != $this->actionData['meta']['factory'] || !isset($this->actionData['meta']['factory'])) {
                    $this->actionData['meta']['arguments'][] = $lineToken;
                }
            }
        }
        if(!isset($this->actionData['meta']['instanceOf'])) {
            error::throwNewException("Expected instance for ", $ftoken['line'], $ftoken['pos']);
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
                    } elseif(isset($set['param']) && $lineToken['type'] != 'WHITESPACE') {
                        $set['value'] = $lineToken;
                    }
                }
                if(!isset($set['param'], $set['value'])) {
                    return error::throwNewException('No param or value', $ftoken['line'], $ftoken['pos']);
                }
                $this->actionData['meta']['params'][$set['param']] = $set['value'];
                return true;
                break;
            case 'PARAM_BREAK':
                return true;
                break;
            case 'PARAM_CALL':
                return true;
                break;
            case 'PARAM_PUT_RESULT':
                array_shift($lineTokens);
                foreach($lineTokens as $lineToken) {
                    switch($lineToken['type']) {
                        case 'WHITESPACE':
                            continue 2;
                        case 'VARIABLE':
                            $this->actionData['meta']['put_result_to'] = $lineToken;
                            break;
                        default:
                            return error::throwNewException("Unexpected {$lineToken['type']}", $lineToken['line'], $lineToken['pos']);
                            break;
                    }
                }
                if(!isset($this->actionData['meta']['put_result_to'])) {
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
        return $this->actionData;
    }
}