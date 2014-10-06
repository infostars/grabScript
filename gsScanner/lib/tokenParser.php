<?php

namespace greevex\gsScanner\lib;
/**
 * @author greevex
 * @date: 10/1/14 6:10 PM
 */

class tokenParser
{
    const TYPE_NAMESPACE = 'namespace';
    const TYPE_CLASS = 'class';
    const TYPE_PROPERTY_WOVALUE = 'property-without-value';
    const TYPE_PROPERTY_WVALUE = 'property-with-value';
    const TYPE_METHOD = 'method';

    const STATUS_INITIALIZED = 0;
    const STATUS_IN_PROGRESS = 1;
    const STATUS_DONE = 2;

    private $status = self::STATUS_INITIALIZED;

    public function __construct()
    {

    }

    public function eat($token)
    {
        static $buffer = [];
        $patterns = [
            '/namespace\s+[^;]+\s+;/' => self::TYPE_NAMESPACE,
            '/class\s+[^;{]+\s+{/' => self::TYPE_CLASS,
            '/[A-Z][A-Za-z0-9_]+\s+[a-zA-Z_][A-Za-z0-9_]+\s+(?:;|,)/' => self::TYPE_PROPERTY_WOVALUE,
            '/[A-Z][A-Za-z0-9_]+\<[A-Z][A-Za-z0-9_]+\>\s+[a-zA-Z_][A-Za-z0-9_]+\s+(?:;|,)/' => self::TYPE_PROPERTY_WOVALUE,
            '/[A-Z][A-Za-z0-9_]+\s+[a-zA-Z_][A-Za-z0-9_]+\s+=/' => self::TYPE_PROPERTY_WVALUE,
            '/[A-Z][A-Za-z0-9_]+\<[A-Z][A-Za-z0-9_]+\>\s+[a-zA-Z_][A-Za-z0-9_]+\s+=/' => self::TYPE_PROPERTY_WVALUE,
            '/[A-Z][A-Za-z0-9_]+\s+[a-zA-Z_][A-Za-z0-9_]+\s+\(/' => self::TYPE_METHOD,
        ];

        $buffer[] = $token['value'];
        error_log(">token eat: " . json_encode($token));
        $imploded = implode(' ', $buffer);
        foreach($patterns as $pattern => $type) {
            $pregMatchResult = preg_match($pattern, $imploded);
//            error_log("\nCHECK MATCH\n" . json_encode($pregMatchResult) . "\n{$pattern}\n{$imploded}");
            if($pregMatchResult) {
                error_log("MATCH-TYPE   >>> {$type}");
                error_log("MATCH-RESULT >>> " . json_encode($imploded));
                $buffer = [];
                break;
            }
        }

    }

    public function getStatus()
    {
        return $this->status;
    }
}