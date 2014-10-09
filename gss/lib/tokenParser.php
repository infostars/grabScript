<?php

namespace greevex\gss\lib;
/**
 * @author greevex
 * @date: 10/1/14 6:10 PM
 */

class tokenParser
{
    private $codeTokens;
    private $result = [
        'package' => '',
        'vars' => [],
        'blocks' => []
    ];

    public function __construct($codeTokens)
    {
        $this->codeTokens = $codeTokens;
    }

    public function parse()
    {
        $currentBlock = null;
        foreach($this->codeTokens as $lineNumber => $lineTokens) {
            foreach($lineTokens['tokens'] as $lineToken) {
                switch($lineToken['type']) {
                    case 'PACKAGE':
                        $ok = false;
                        foreach($lineTokens['tokens'] as $_lineToken) {
                            if($_lineToken['type'] == 'STRING_QUOTED') {
                                $this->result['package'] = trim($_lineToken['value'], '"');
                                $ok = true;
                                break;
                            }
                        }
                        if(!$ok) {
                            return error::throwNewException('Unable to find package name', $lineNumber, $lineToken['pos']);
                        }
                        break 2;
                    case 'VAR':
                        $var = [];
                        foreach($lineTokens['tokens'] as $_lineToken) {
                            switch($_lineToken['type']) {
                                case 'OBJECT':
                                    $var['instanceOf'] = $_lineToken['value'];
                                    break;
                                case 'VARIABLE':
                                    $var['varName'] = ltrim($_lineToken['value'], '$');
                                    $var['varData'] = $_lineToken;
                                    break 2;
                            }
                        }
                        if(!isset($var['instanceOf'], $var['varName'])) {
                            return error::throwNewException('Unable to find variable initialization', $lineNumber, $lineToken['pos']);
                        }
                        $this->result['vars'][$var['varName']] = $var;
                        break 2;
                    case 'BLOCK':
                        $block = [];
                        foreach($lineTokens['tokens'] as $_lineToken) {
                            switch($_lineToken['type']) {
                                case 'OBJECT':
                                    $block['blockname'] = $_lineToken['value'];
                                    break;
                                case 'BLOCK_START':
                                    $block['start'] = true;
                                    break 2;
                            }
                        }
                        if(!isset($block['blockname'], $block['start'])) {
                            return error::throwNewException('Unable to find block name', $lineNumber, $lineToken['pos']);
                        }
                        $currentBlock = $block['blockname'];
                        blockParser::factory($currentBlock)->setStarted();
                        break 2;
                    case 'BLOCK_END':
                        if(!isset($currentBlock)) {
                            return error::throwNewException('Unexpected block end', $lineNumber, $lineToken['pos']);
                        }
                        blockParser::factory($currentBlock)->setEnded();
                        $this->result['blocks'][$currentBlock] = blockParser::factory($currentBlock)->dump();
                        unset($currentBlock);
                        break 2;
                    case 'WHITESPACE':
                        //@todo skip
                        break;
                    default:
                        if(!isset($currentBlock)) {
                            return error::throwNewException("Unexpected {$lineToken['type']}", $lineNumber, $lineToken['pos']);
                        }
                        blockParser::factory($currentBlock)->eatLineTokens($lineTokens['tokens']);
                        break 2;
                }
            }
        }

        return $this->result;
    }
}