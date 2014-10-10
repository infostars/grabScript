<?php

namespace greevex\gss\translator\action;

class variableCallTranslator
    extends translatorBase
{
    const TYPE = 'variable_call';

    public function __construct($blockContent)
    {
        parent::__construct($blockContent);
    }
}