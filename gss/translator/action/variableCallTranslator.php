<?php

namespace greevex\gss\translator\action;

use greevex\gss\translator\helper;

class variableCallTranslator
    extends translatorBase
{
    protected $type = 'variable_call';

    public function __construct($blockContent)
    {
        parent::__construct($blockContent);
        $this->process();
    }

    protected function process()
    {
        $callable = $this->blockContent['meta']['callable'];
        $this->sourceCode .= "{$this->soffset}{$callable['varData']['value']}(";
        $args = [];
        foreach($this->blockContent['meta']['arguments'] as $argument) {
            $args[] = helper::getValueByToken($argument)['value'];
        }
        $this->sourceCode .= implode(', ', $args) . ");";
    }

}