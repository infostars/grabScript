<?php
/**
 * @author greevex
 * @date: 10/1/14 6:10 PM
 */

class lexer
{
    private $filepath;
    private $content;

    public function __construct()
    {
        $tokens = token_get_all(file_get_contents('sample.gs.cs'));
        $newTokens = [];
        foreach($tokens as $token) {
            if(is_string($token)) {
                $newToken = [
                    'type' => 'CHAR',
                    'value' => $token
                ];
            } else {
                $newToken = [
                    'type' => token_name($token[0]),
                ];
                if (isset($token[2])) {
                    $newToken['line'] = $token[2];
                }
                if (isset($token[1])) {
                    $newToken['value'] = $token[1];
                }
            }
            $newTokens[] = $newToken;
        }
        var_dump($newTokens);
        exit;
    }

    public function setFile($filepath)
    {
        $this->filepath = $filepath;
    }

    public function parse()
    {
        $this->content = file_get_contents($this->filepath);
//        $this->debug();
        $this->parseStr();
    }

    protected function tok($str)
    {
        static $initialized = false;
        if(!$initialized) {
            $initialized = true;
            return strtok($this->content, $str);
        } else {
            return strtok($str);
        }
    }

    protected function debug()
    {
        var_dump($this->tok(" \n"));
        var_dump($this->tok(";"));
        var_dump($this->tok(" \n"));
        var_dump($this->tok(" \n"));
        var_dump($this->tok(" \n"));
        exit;
    }

    protected function parseStr()
    {
        $result = [];
        $level = 0;
        for(;;) {
            $token = $this->tok(" \n");
            if($token === false) {
                break;
            }
            error_log("Token: " . json_encode($token));
            switch($token) {
                case 'namespace':
                    $result['namespace'] = trim(strtok(";"));
                    break;
                case 'class':
                    $result['class'] = trim(strtok(" \n"));
                    break;
                case '{':
                    $level++;
                    error_log("LEVEL+> {$level}");
                    break;
                case '}':
                case '},':
                case '};':
                    $level--;
                    error_log("LEVEL-> {$level}");
                    break;

                default:

                    break;
            }
        }
        var_dump($result);
    }
}

$lexer = new lexer();
$lexer->setFile(__DIR__ . '/sample.gs.cs');
$lexer->parse();