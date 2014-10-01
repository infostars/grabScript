<?php
/**
 * @author greevex
 * @date: 10/1/14 6:10 PM
 */

class lexer
{
    private $filepath;

    public function __construct()
    {

    }

    public function setFile($filepath)
    {
        $this->filepath = $filepath;
    }

    public function parse()
    {
        $content = file_get_contents($this->filepath);
        $this->parseStr($content);
    }

    protected function parseStr($str)
    {
        $class = [];
        $level = 0;
        for($token = strtok($str, ' ');$token != null;$token = strtok(" \n\t")) {
            switch($token) {
                case 'namespace':
                    $class['namespace'] = strtok(';');
                    break;
                case 'class':
                    $class['class'] = strtok(" \n{");
                    break;
                case '{':
                    $level++;
                    var_dump($level);
                    break;
                case '}':
                case '};':
                case '},':
                    $level--;
                    var_dump($level);
                    break;
            }
        }
        var_dump($class);
    }
}

$lexer = new lexer();
$lexer->setFile(__DIR__ . '/sample.gs.cs');
$lexer->parse();