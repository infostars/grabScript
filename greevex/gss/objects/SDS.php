<?php

namespace greevex\gss\objects;

use greevex\gss\lib\error;

class SDS
{
    const DEFAULT_PATH = 'f_';

    private $path = self::DEFAULT_PATH;
    private $sessionStarted = false;

    /**
     * @return self
     */
    public static function getInstance()
    {
        static $instance;
        if(!isset($instance)) {
            $instance = new self();
        }

        return $instance;
    }

    protected function __construct()
    {

    }

    public function __get($property)
    {
        $this->sessionStarted = true;
        $this->path .= "_{$property}";
        if(method_exists($this, $this->path)) {
            $result = [$this, $this->path];
            $this->path = self::DEFAULT_PATH;
            $this->sessionStarted = false;
            return $result;
        }
        return $this;
    }

    public function __call($functionName, $params = [])
    {
        if(!$this->sessionStarted) {
            error::throwNewException("Expected path before method call");
        }
        $this->path .= "_{$functionName}";
        if(!method_exists($this, $this->path)) {
            error::throwNewException("Unable to find method {$this->path}->{$functionName}");
        }
        $result = call_user_func_array([$this, $this->path], $params);
        $this->path = self::DEFAULT_PATH;
        $this->sessionStarted = false;
        return $result;
    }

    public function f__output_print($string = null)
    {
        print $string . PHP_EOL;
    }
}