<?php

namespace greevex\gsScanner\lib;
/**
 * @author greevex
 * @date: 10/1/14 6:10 PM
 */

class codeScanner
{
    private $sourceCode;
    private $tokens;

    public function __construct($sourceCode)
    {
        if(empty($sourceCode)) {
            throw new \Exception("Source codes is empty!");
        }
        $this->sourceCode = $sourceCode;
    }

    public function scan()
    {
        $this->tokens = token_get_all("<?php " . $this->sourceCode);
        $tokenScanner = new tokenScanner($this->tokens);

        return $tokenScanner->scan();
    }
}