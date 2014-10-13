<?php

namespace greevex\gss\objects;

abstract class _objectBase
{
    protected $params = [];

    public function setParam($param, $value)
    {
        $this->params[$param] = $value;
    }

    abstract public function execute();

}