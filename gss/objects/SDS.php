<?php

namespace greevex\gss\objects;

class SDS
{

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
        error_log("call>>> {$property}");
        return $this;
    }
}