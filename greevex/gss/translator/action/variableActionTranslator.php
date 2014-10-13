<?php

namespace greevex\gss\translator\action;

use greevex\gss\lib\error;

class variableActionTranslator
    extends translatorBase
{
    protected $type = 'variable_action';

    public function __construct($blockContent)
    {
        parent::__construct($blockContent);
        $this->process();
    }

    protected function process()
    {
        $baseVar = $this->blockContent['meta']['item'];
        if($this->blockContent['meta']['type'] != 'assign') {
            error::throwNewCompileException("Unexpected {$this->type} type {$this->blockContent['meta']['type']}", $baseVar['line'], $baseVar['pos']);
        }
        $this->sourceCode .= "{$this->soffset}{$baseVar['value']} =";

        if(isset($this->blockContent['meta']['value_type'])) {
            $item1 = array_shift($this->blockContent['meta']['value']);
            $item2 = array_shift($this->blockContent['meta']['value']);
            $this->sourceCode .= " {$item1['value']} {$this->blockContent['meta']['value_type']['value']} {$item2['value']};";
        } else {
            $value = array_shift($this->blockContent['meta']['value']);
            $this->sourceCode .= " {$value['value']};";
        }
    }
}