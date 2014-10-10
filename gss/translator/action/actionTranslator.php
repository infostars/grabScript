<?php

namespace greevex\gss\translator\action;

class actionTranslator
    extends translatorBase
{
    const TYPE = 'action';

    public function __construct($blockContent)
    {
        parent::__construct($blockContent);
    }
}