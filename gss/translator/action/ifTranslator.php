<?php

namespace greevex\gss\translator\action;

class ifTranslator
    extends translatorBase
{
    const TYPE = 'if';

    public function __construct($blockContent)
    {
        parent::__construct($blockContent);
    }
}