<?php

namespace greevex\gss\objects;

require_once __DIR__ . '/_objectBase.php';

class Load
    extends _objectBase
{

    private $url;

    public function __construct($url)
    {
        $this->url = $url;
    }

    public function execute()
    {
        file_get_contents($this->url);
    }
}