<?php

namespace greevex\gss\translator\action;

use greevex\gss\translator\helper;

class foreachTranslator
    extends translatorBase
{
    protected $type = 'foreach';

    public function __construct($blockContent)
    {
        parent::__construct($blockContent);
        $this->process();
    }

    protected function process()
    {
        $pointer = '%pointer%';
        $from = helper::getValueByToken($this->blockContent['meta']['from']);
        if(isset($this->blockContent['meta']['params']['item'])) {
            $item = helper::getValueByToken($this->blockContent['meta']['params']['item'])['value'];
        } else {
            $item = '$item';
        }
        $fromValue = $from['type'] == 'object' ? "{$from['value']}->getNext()" : "array_shift({$from['value']})";

        $this->sourceCode .= "{$this->soffset}while({$item} = {$fromValue}) {\n{$this->soffset}    {$pointer}\n{$this->soffset}}";

        if(isset($this->blockContent['meta']['call'])) {
            foreach($this->blockContent['meta']['call'] as $call) {
                $callable = helper::callableToSourceCode($call);
                $callable .= "\n{$this->soffset}{$pointer}";
                $this->sourceCode = str_replace($pointer, $callable, $this->sourceCode);
            }
        }
        $this->sourceCode = str_replace(["\n{$this->soffset}{$pointer}", $pointer], '', $this->sourceCode);
    }
}