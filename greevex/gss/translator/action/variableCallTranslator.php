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
        $callable = helper::getValueByToken($this->blockContent['meta']['callable']['varData'])['value'];

        $this->sourceCode .= "{$this->soffset}{$callable}(";
        $args = [];
        foreach($this->blockContent['meta']['arguments'] as $argument) {
            $args[] = helper::getValueByToken($argument)['value'];
        }
        $this->sourceCode .= implode(', ', $args) . ");";
    }

}