<?php

namespace greevex\gss\lib;
/**
 * @author greevex
 * @date: 10/1/14 6:10 PM
 */

class fileScanner
{
    private $filepath;

    public function __construct($filepath)
    {
        $this->filepath = $filepath;
    }

    public function scan()
    {
        if(!file_exists($this->filepath)) {
            throw new \Exception("File not exists!");
        }
        if(!is_readable($this->filepath)) {
            throw new \Exception("File is not readable!");
        }
        $content = file_get_contents($this->filepath);
        if(!$content) {
            throw new \Exception("Unable to load file contents");
        }

        $codeScanner = new codeScanner($content);

        $codeTokens = $codeScanner->scan();
        foreach($codeTokens as $line => $codeToken) {
            echo "[{$line}] {$codeToken['tokens_str']}\n";
        }

        $tokenParser = new tokenParser($codeTokens);
        return $tokenParser->parse();
    }
}