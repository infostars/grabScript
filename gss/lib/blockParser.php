<?php

namespace greevex\gss\lib;
/**
 * @author greevex
 * @date: 10/1/14 6:10 PM
 */

class blockParser
{
    private $blockName;
    private $blockData = [
        'name' => null,
        'started' => false,
        'ended' => false,
        'contents' => []
    ];

    /**
     * @param $blockName
     *
     * @return self
     */
    public static function factory($blockName)
    {
        static $instances = [];
        if(!isset($instances[$blockName])) {
            $instances[$blockName] = new self($blockName);
        }

        return $instances[$blockName];
    }

    public function __construct($blockName)
    {
        $this->blockName = $blockName;
    }

    public function dump()
    {
        return $this->blockData;
    }

    public function eatLineTokens($lineTokens)
    {
        foreach($lineTokens as $lineTokenKey => $lineToken) {
            if($lineToken['type'] != 'WHITESPACE') {
                break;
            }
            array_shift($lineTokens);
        }
        /** @var foreachParser $foreachParser */
        /** @var variableMethodParser $variableMethod */
        /** @var inputParser $inputParser */
        /** @var foreachParser $foreachParser */
        switch($lineTokens[0]['type']) {
            case 'VARIABLE_METHOD':
                $variableMethod = new variableMethodParser($lineTokens);
                $this->blockData['contents'][] = $variableMethod->dump();
                if(!$variableMethod::CAN_WAIT) {
                    unset($variableMethod);
                }
                break;
            case 'OBJECT':
                break;
            case 'INPUT':
                $inputParser = new inputParser($lineTokens);
                $this->blockData['contents'][] = $inputParser->dump();
                if(!$inputParser::CAN_WAIT) {
                    unset($inputParser);
                }
                break;
                break;
            case 'FOREACH':
                new foreachParser($lineTokens);
                break;
            case 'IF':
                break;
            case 'RETURN':
                break;
            case 'WHITESPACE':
                break;
            default:
                if(foreachParser::hasInstance()) {
                    foreachParser::getInstance()->eatParams($lineTokens);
                }
                return error::throwNewException("Unexpected {$lineTokens[0]['type']}", $lineTokens[0]['line'], $lineTokens[0]['pos']);
                break;
        }

        return true;
    }

    public function setStarted()
    {
        $this->blockData['started'] = true;
    }

    public function setEnded()
    {
        $this->blockData['ended'] = true;
    }
}