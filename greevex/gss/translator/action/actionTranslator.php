<?php

namespace greevex\gss\translator\action;

use greevex\gss\translator\helper;

class actionTranslator
    extends translatorBase
{
    protected $type = 'action';

    public function __construct($blockContent)
    {
        parent::__construct($blockContent);
        $this->process();
    }

    protected function process()
    {
        $resVar = '$gen_' . crc32(json_encode($this->blockContent));

        $args = [];
        foreach($this->blockContent['meta']['arguments'] as $argument) {
            $args[] = helper::getValueByToken($argument)['value'];
        }
        $instanceOf = helper::getValueByToken($this->blockContent['meta']['instanceOf'])['value'];
        if(isset($this->blockContent['meta']['factory'])) {
            $factorySection = helper::getValueByToken($this->blockContent['meta']['factory'])['value'];
            $this->sourceCode .= "{$this->soffset}{$resVar} = {$instanceOf}::factory('{$factorySection}'";
            if($args) {
                $this->sourceCode .= ', '. implode(', ', $args);
            }
            $this->sourceCode .= ');';
        } else {
            $this->sourceCode .= "{$this->soffset}{$resVar} = new {$instanceOf}(";
            $this->sourceCode .= implode(', ', $args) . ');';
        }
        foreach($this->blockContent['meta']['params'] as $param => $value) {
            $paramValue = helper::getValueByToken($value)['value'];
            $this->sourceCode .= "\n{$this->soffset}{$resVar}->setParam('{$param}', {$paramValue});";
        }
        if(isset($this->blockContent['meta']['put_result_to'])) {
            $outputVar = helper::getValueByToken($this->blockContent['meta']['put_result_to'])['value'];
        }
        $this->sourceCode .= "\n{$this->soffset}";
        if(isset($outputVar)) {
            $this->sourceCode .= "{$outputVar} = ";
        }
        $this->sourceCode .= "{$resVar}->execute();";
    }
}