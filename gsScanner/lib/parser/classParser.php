<?php

namespace greevex\gsScanner\lib\parser;
/**
 * @author greevex
 * @date: 10/1/14 6:10 PM
 */

use greevex\gsScanner\lib\tokenParser;

require_once __DIR__ . '/parserInterface.php';

class classParser
{
    private $namespace;

    public function __construct($tokens)
    {
        array_shift($tokens);
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
        $patterns = [
            '/namespace\s+[^;]+\s+;/' => tokenParser::TYPE_NAMESPACE,
            '/class\s+[^;{\s]+/' => tokenParser::TYPE_CLASS,
            '/[A-Z][A-Za-z0-9_]+\s+[a-zA-Z_][A-Za-z0-9_]+\s+(?:;|,)/' => tokenParser::TYPE_PROPERTY_WOVALUE,
            '/[A-Z][A-Za-z0-9_]+\<[A-Z][A-Za-z0-9_]+\>\s+[a-zA-Z_][A-Za-z0-9_]+\s+(?:;|,)/' => tokenParser::TYPE_PROPERTY_WOVALUE,
            '/[A-Z][A-Za-z0-9_]+\s+[a-zA-Z_][A-Za-z0-9_]+\s+=/' => tokenParser::TYPE_PROPERTY_WVALUE,
            '/[A-Z][A-Za-z0-9_]+\<[A-Z][A-Za-z0-9_]+\>\s+[a-zA-Z_][A-Za-z0-9_]+\s+=/' => tokenParser::TYPE_PROPERTY_WVALUE,
            '/[A-Z][A-Za-z0-9_]+\s+[a-zA-Z_][A-Za-z0-9_]+\s+\(/' => tokenParser::TYPE_METHOD,
            '/[A-Z][A-Za-z0-9_]\.[A-Za-z0-9_]+\s+[a-zA-Z_][A-Za-z0-9_]+\s+=/' => tokenParser::TYPE_OBJECTPROPERTY_WVALUE,
            '/[A-Z][A-Za-z0-9_]\.[A-Za-z0-9_]+\s+[a-zA-Z_][A-Za-z0-9_]/' => tokenParser::TYPE_OBJECTPROPERTY_CALL,
            '/new [A-Z][A-Za-z0-9_]+/' => tokenParser::TYPE_NEW_OBJECT,
        ];

        if(!isset($this->child) || !$this->child->eat($token)) {
            if(isset($this->child)) {
                error_log("RESULT: " . json_encode($this->child->getResult(), 384));
                $this->child = null;
            }
            $this->buffer[] = $token;
            $imploded = "";
            foreach($this->buffer as $item) {
                $imploded .= "{$item['value']} ";
            }
            $imploded = substr($imploded, 0, -1);
            error_log(">token eat: {$imploded}");
            $found = false;
            foreach ($patterns as $pattern => $type) {
                $pregMatchResult = preg_match($pattern, $imploded);
                //                error_log("\nCHECK MATCH\n" . json_encode($pregMatchResult) . "\n{$pattern}\n{$imploded}");
                if ($pregMatchResult) {
                    $this->child = self::instantiate($type, $this->buffer);
                    $this->buffer = [];
                    $found = true;
                    break;
                }
            }
            return $found;
        }
        return true;
    }
}