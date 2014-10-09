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
        unset(self::$instance);
    }

    public static function hasInstance()
    {
        return isset(self::$instance);
    }

    const TYPE = 'foreach';
    const CAN_WAIT = true;

    private $variableCallData = [
        'type' => self::TYPE,
        'meta' => [

        ]
    ];

    public function __construct($lineTokens)
    {
        foreach($lineTokens as $lineToken) {
            switch($lineToken['type']) {
                case 'VARIABLE':
                    $this->variableCallData['meta']['from'] = $lineToken;
                    break;
            }
        }
        if(!$this->variableCallData['meta']) {
            error::throwNewException("Unable to find callable", $lineTokens[0]['line'], $lineTokens[0]['pos']);
        }

        self::$instance = $this;
    }

    public function eatParams($lineTokens)
    {
        var_dump($lineTokens);
        $first = reset($lineTokens);
        switch($first['type']) {
            case 'PARAM_SET':
                break;
            case 'PARAM_BREAK':
                break;
            case 'PARAM_CALL':
                break;
            case 'PARAM_PUT_RESULT':
                break;
            case 'PARAM':
                break;
            default:
                error::throwNewException("Unexpected {$first['type']}", $first['line'], $first['pos']);
                break;
        }
    }

    public function dump()
    {
        return $this->variableCallData;
    }
}