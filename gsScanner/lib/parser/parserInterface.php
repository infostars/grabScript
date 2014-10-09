<?php

namespace greevex\gsScanner\lib\parser;
/**
 * @author greevex
 * @date: 10/1/14 6:10 PM
 */

interface parserInterface
{

    /**
     * @param array $tokens
     */
    public function __construct($tokens);

    /**
     * @return mixed
     */
    public function getResult();

    /**
     * @param array $token
     *
     * @return mixed
     */
    public function eat($token);
}