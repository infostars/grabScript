<?php

namespace greevex\gss\translator\action;

use greevex\gss\translator\helper;

class ifTranslator
    extends translatorBase
{
    protected $type = 'if';

    public function __construct($blockContent)
    {
        parent::__construct($blockContent);
        $this->process();
    }

    protected function process()
    {
        debug_zval_dump($this->blockContent);

        $item1 = helper::getValueByToken($this->blockContent['meta']['condition']['item1'])['value'];
        $item2 = helper::getValueByToken($this->blockContent['meta']['condition']['item2'])['value'];
        $cond = helper::getValueByToken($this->blockContent['meta']['condition']['cond'])['value'];
        $this->sourceCode .= "{$this->soffset}if({$item1} {$cond} {$item2}) {";
        if(isset($this->blockContent['meta']['call'])) {
            foreach($this->blockContent['meta']['call'] as $call) {
                $callable = helper::callableToSourceCode($call);
                $this->sourceCode .= "\n{$this->soffset}    {$callable}";
            }
        }
        if(isset($this->blockContent['meta']['params'])) {

            if(isset($this->blockContent['meta']['params']['break'])) {
                $this->sourceCode .= "\n\n{$this->soffset}    return;";
            }
        }

        $this->sourceCode .= "\n{$this->soffset}}";
    }
}