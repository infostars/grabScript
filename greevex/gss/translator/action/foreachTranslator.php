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
        $fromOrigin = $this->blockContent['meta']['from'];
        $from = helper::getValueByToken($fromOrigin);
        if(isset($this->blockContent['meta']['params']['item'])) {
            $item = helper::getValueByToken($this->blockContent['meta']['params']['item'])['value'];
        } else {
            $item = '$item';
        }
        $this->sourceCode .= <<<PHP
{$this->soffset}if(is_object({$from['value']}) && method_exists({$from['value']}, 'getNext')) {
{$this->soffset}    while({$item} = {$from['value']}->getNext()) {
{$this->soffset}        {$pointer}
{$this->soffset}    }
{$this->soffset}} elseif(is_array({$from['value']})) {
{$this->soffset}    while({$item} = array_shift({$from['value']})) {
{$this->soffset}        {$pointer}
{$this->soffset}    }
{$this->soffset}} elseif(is_object({$from['value']})) {
{$this->soffset}    {$from['value']} = (array){$from['value']};
{$this->soffset}    while({$item} = array_shift({$from['value']})) {
{$this->soffset}        {$pointer}
{$this->soffset}    }
{$this->soffset}} else {
{$this->soffset}    error::throwNewException("Unexpected variable type of \\{$from['value']}", {$fromOrigin['line']}, {$fromOrigin['pos']});
{$this->soffset}}
PHP;


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