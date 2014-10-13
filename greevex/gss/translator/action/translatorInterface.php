<?php

namespace greevex\gss\translator\action;

interface translatorInterface
{
    public function __construct($blockContent);

    public function getSourceCode();
}