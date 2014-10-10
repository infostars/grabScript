<?php

namespace greevex\gss\objects;

class _objectBase
{
    protected $params = [];

    public function setParam($param, $value)
    {
        $this->params[$param] = $value;
    }

    public function execute()
    {

    }
}