<?php

namespace greevex\gss\translator\action;

class foreachTranslator
    extends translatorBase
{
    const TYPE = 'foreach';

    public function __construct($blockContent)
    {
        parent::__construct($blockContent);
    }
}