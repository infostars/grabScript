<?php

namespace greevex\gsScanner\lib;

use greevex\gsScanner\lib\parser\classParser;
use greevex\gsScanner\lib\parser\namespaceParser;
use greevex\gsScanner\lib\parser\parserInterface;

require_once __DIR__ . '/parser/namespaceParser.php';
require_once __DIR__ . '/parser/classParser.php';

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
    const TYPE_OBJECTPROPERTY_WVALUE = 'object.property-with-value';
    const TYPE_OBJECTPROPERTY_CALL = 'object.property-call';
    const TYPE_NEW_OBJECT = 'new-object';

    const STATUS_INITIALIZED = 0;
    const STATUS_IN_PROGRESS = 1;
    const STATUS_DONE = 2;

    private $type;
    private $status = self::STATUS_INITIALIZED;
    private $buffer = [];
    /**
     * @var parserInterface
     */
    private $child;

    public function __construct($type = null)
    {
        $this->type = $type;
    }

    public function eat($token)
    {
        $patterns = [
            '/namespace\s+[^;]+\s+;/' => self::TYPE_NAMESPACE,
            '/class\s+[^;{\s]+/' => self::TYPE_CLASS,
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

    /**
     * @param $type
     * @param $buffer
     *
     * @throws \Exception
     * @return self
     */
    public static function instantiate($type, $buffer)
    {
        error_log("FOUND {$type} - " . json_encode($buffer, 384));
        switch($type) {
            case self::TYPE_NAMESPACE:
                return new namespaceParser($buffer);
                break;
            case self::TYPE_CLASS:
                return new classParser($buffer);
                break;
        }
        throw new \Exception("Unable to find class");
    }

    protected function processBuffer()
    {

    }

    public function getStatus()
    {
        return $this->status;
    }
}