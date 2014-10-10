<?php

namespace greevex\gss\translator\action;

use greevex\gss\lib\error;

abstract class translatorBase
    implements translatorInterface
{
    protected $generator;
    protected $blockContent;
    protected $sourceCode = '';
    protected $type = null;
    protected $soffset = '        ';

    public function __construct($blockContent)
    {
        $this->blockContent = $blockContent;
        $this->validate();
        $exploded = explode("\\", get_called_class());
        $this->generator = array_pop($exploded);
    }

    protected function validate()
    {
        if($this->blockContent['type'] != $this->type) {
            error::throwNewCompileException("Unexpected translator type {$this->blockContent['type']}, expected {$this->type}");
        }
    }

    public function getSourceCode()
    {
        if(!empty($this->sourceCode)) {
            return "\n{$this->soffset}//@generator {$this->generator}\n{$this->sourceCode}";
        }
        return $this->sourceCode;
    }
}