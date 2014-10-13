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
        'input' => [],
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
        $this->blockData['name'] = $this->blockName;
    }

    public function dump()
    {
        $copy = $this->blockData;
        unset($copy['started']);
        unset($copy['ended']);
        return $copy;
    }

    public function eatLineTokens($lineTokens)
    {
        foreach($lineTokens as $lineTokenKey => $lineToken) {
            if($lineToken['type'] != 'WHITESPACE') {
                break;
            }
            array_shift($lineTokens);
        }
        $ftoken = $lineTokens[0];
        /** @var foreachParser $foreachParser */
        /** @var variableMethodParser $variableMethod */
        /** @var inputParser $inputParser */
        /** @var foreachParser $foreachParser */
        switch($ftoken['type']) {
            case 'INPUT':
                if($this->checkInstances($lineTokens)) {
                    break;
                }
                $this->setEnded();
                $inputParser = new inputParser($lineTokens);
                $this->blockData['input'] = $inputParser->dump();
                if(!$inputParser::CAN_WAIT) {
                    unset($inputParser);
                }
                break;
            case 'VARIABLE_METHOD':
                if($this->checkInstances($lineTokens)) {
                    break;
                }
                $this->setEnded();
                $variableMethod = new variableMethodParser($lineTokens);
                $this->blockData['contents'][] = $variableMethod->dump();
                if(!$variableMethod::CAN_WAIT) {
                    unset($variableMethod);
                }
                break;
            case 'VARIABLE':
            case 'VARIABLE_PATH':
                if($this->checkInstances($lineTokens)) {
                    break;
                }
                $this->setEnded();
                $variableAction = new variableAction($lineTokens);
                $this->blockData['contents'][] = $variableAction->dump();
                if(!$variableAction::CAN_WAIT) {
                    unset($variableAction);
                }
                break;
            case 'OBJECT':
                if($this->checkInstances($lineTokens)) {
                    break;
                }
                $this->setEnded();
                new actionParser($lineTokens);
                break;
            case 'FOREACH':
                $this->setEnded();
                new foreachParser($lineTokens);
                break;
            case 'IF':
                $this->setEnded();
                new ifParser($lineTokens);
                break;
            case 'RETURN':
                $this->setEnded();
                $returnParser = new returnParser($lineTokens);
                $this->blockData['return'] = $returnParser->dump();
                if(!$returnParser::CAN_WAIT) {
                    unset($returnParser);
                }
                break;
            case 'WHITESPACE':
                break;
            default:
                if($this->checkInstances($lineTokens)) {
                    break;
                }
                $this->setEnded();
                return error::throwNewException("Unexpected {$ftoken['type']}", $ftoken['line'], $ftoken['pos']);
                break;
        }

        return true;
    }

    protected function checkInstances($lineTokens)
    {
        if(foreachParser::hasInstance()) {
            $status = foreachParser::getInstance()->eatParams($lineTokens);
            if(!$status) {
                $this->blockData['contents'][] = foreachParser::getInstance()->dump();
                foreachParser::getInstance()->dropInstance();
            }
            return $status;
        }
        if(actionParser::hasInstance()) {
            $status = actionParser::getInstance()->eatParams($lineTokens);
            if(!$status) {
                $this->blockData['contents'][] = actionParser::getInstance()->dump();
                actionParser::getInstance()->dropInstance();
            }
            return $status;
        }
        if(ifParser::hasInstance()) {
            $status = ifParser::getInstance()->eatParams($lineTokens);
            if(!$status) {
                $this->blockData['contents'][] = ifParser::getInstance()->dump();
                ifParser::getInstance()->dropInstance();
            }
            return $status;
        }
        return false;
    }

    public function setStarted($lineNumber)
    {
        $this->blockData['started'] = true;
        $this->blockData['line'] = $lineNumber;
    }

    public function setEnded()
    {
        $this->blockData['ended'] = true;
        if(foreachParser::hasInstance()) {
            $this->blockData['contents'][] = foreachParser::getInstance()->dump();
            foreachParser::getInstance()->dropInstance();
        }
        if(actionParser::hasInstance()) {
            $this->blockData['contents'][] = actionParser::getInstance()->dump();
            actionParser::getInstance()->dropInstance();
        }
        if(ifParser::hasInstance()) {
            $this->blockData['contents'][] = ifParser::getInstance()->dump();
            ifParser::getInstance()->dropInstance();
        }
    }
}