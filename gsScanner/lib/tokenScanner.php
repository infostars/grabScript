<?php

namespace greevex\gsScanner\lib;
/**
 * @author greevex
 * @date: 10/1/14 6:10 PM
 */

class tokenScanner
{

    private $tokens;

    public function __construct($tokens)
    {
        if(empty($tokens)) {
            throw new \Exception("Tokens is empty!");
        }
        $this->tokens = $tokens;
    }

    public function scan()
    {
        $this->optimizeTokens();
        $this->parseTokens();
    }

    protected function parseTokens()
    {
        /** @var tokenParser $parser */
        static $parser;
        if(!isset($parser)) {
            $parser = new tokenParser();
        }
        foreach($this->tokens as $token) {
            switch($token['type']) {
                case 'T_OPEN_TAG':
                case 'T_WHITESPACE':
                    //@skip
                    break;
                case 'T_NAMESPACE':
                case 'T_CLASS':
                case 'T_COMMENT':
                case 'CHAR':
                case 'T_LIST':
                case 'T_STRING':
                case 'T_CONSTANT_ENCAPSED_STRING':
                case 'T_PLUS_EQUAL':
                case 'T_NEW':
                case 'T_FOREACH':
                default:
                    $parser->eat($token);
                    break;
            }
        }
    }

    protected function optimizeTokens()
    {
        $tokens = $this->tokens;
        $this->tokens = [];
        $line = 0;
        while($token = array_shift($tokens)) {
            if(is_string($token)) {
                $newToken = [
                    'type' => 'CHAR',
                    'value' => $token,
                    'line' => $line
                ];
            } else {
                $newToken = [
                    'type' => token_name($token[0]),
                ];
                if (isset($token[2])) {
                    $line = $token[2];
                }
                $newToken['line'] = $line;
                if (isset($token[1])) {
                    $newToken['value'] = $token[1];
                }
            }
            $this->tokens[] = $newToken;
        }
    }
}