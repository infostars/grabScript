<?php

namespace greevex\gss\translator\action;

use greevex\gss\lib\error;

abstract class translatorBase
    implements translatorInterface
{
    protected $blockContent;
    protected $sourceCode = '';

    public function __construct($blockContent)
    {
        $this->blockContent = $blockContent;
        $this->validate();
    }

    protected function validate()
    {
        if($this->blockContent['type'] != self::TYPE) {
            error::throwNewCompileException("Unexpected translator type {$this->blockContent['type']}, expected " . self::TYPE);
        }
    }

    public function getSourceCode()
    {
        return $this->sourceCode;
    }
}