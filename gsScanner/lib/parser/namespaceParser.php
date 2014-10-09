<?php

namespace greevex\gsScanner\lib\parser;
/**
 * @author greevex
 * @date: 10/1/14 6:10 PM
 */

require_once __DIR__ . '/parserInterface.php';

class namespaceParser
{
    private $namespace;

    public function __construct($tokens)
    {
        array_shift($tokens);
        array_pop($tokens);
        $buffer = [];
        foreach($tokens as $token) {
            if($token['value'] == '.') {
                continue;
            }
            $buffer[] = $token['value'];
        }
        $this->namespace = implode('\\', $buffer);
    }

    public function getResult()
    {
        return $this->namespace;
    }

    public function eat($token)
    {
        return false;
    }
}