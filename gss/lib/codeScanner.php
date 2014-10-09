<?php

namespace greevex\gss\lib;
/**
 * @author greevex
 * @date: 10/1/14 6:10 PM
 */

class codeScanner
{
    private $sourceCode;

    private $tokens = [
        'PACKAGE' => 'Package',
        'BLOCK' => 'Block',
        'INPUT' => 'Input',
        'FOREACH' => 'Foreach',
        'IF' => 'If',
        'WHILE' => 'While',
        'VAR' => 'Var',
        'RETURN' => 'Return',

        'PARAM_CALL' => '\-\s+call',
        'PARAM_BREAK' => '\-\s+break',
        'PARAM_PUT_RESULT' => '\-\s+put\s+result\s+to',
        'PARAM_SET' => '\-\s+set',
        'PARAM' => '\-',

        'BOOL_TRUE' => 'true',
        'BOOL_FALSE' => 'false',
        'NULL' => 'null',
        'FLOAT' => '\d+\.\d+',
        'INTEGER' => '\d+',
        'STRING_QUOTED' => '"(?:[^"\\\\]|\\\\.)+"',
        'VARIABLE_PATH' => '\$[a-zA-Z_][a-zA-Z_0-9]*(?:\.[a-zA-Z_][a-zA-Z_0-9]*){2,}',
        'VARIABLE_METHOD' => '\$[a-zA-Z_][a-zA-Z_0-9]*\.[a-zA-Z_][a-zA-Z_0-9]*',
        'VARIABLE' => '\$[a-zA-Z_][a-zA-Z_0-9]*',
        'THIS_METHOD' => 'this\.[a-zA-Z_][a-zA-Z_0-9]*',
        'OBJECT_PATH' => '[a-zA-Z_][a-zA-Z_0-9]*(?:\.[a-zA-Z_][a-zA-Z_0-9]*){2,}',
        'OBJECT_METHOD' => '[a-zA-Z_][a-zA-Z_0-9]*\.[a-zA-Z_][a-zA-Z_0-9]*',
        'OBJECT' => '[a-zA-Z_][a-zA-Z_0-9]*',
        'STRING' => '[a-zA-Z0-9а-яА-ЯёЁ]+',

        'BLOCK_START' => '{',
        'BLOCK_END' => '}',
        'PLUS' => '\+',
        'MINUS' => '\-',
        'MULTIPLY' => '\*',
        'DIVIDE' => '\/',
        'DOT' => '\.',
        'DOLLAR' => '\$',
        'COLON' => '\:',
        'EQUALS' => '==',
        'NOT_EQUALS' => '!=',
        'GTE' => '>=',
        'LTE' => '<=',
        'GT' => '>',
        'LT' => '<',
        'ASSIGN' => '=',

        'WHITESPACE' => '\s+',
        '__LINE_END__' => '$',
    ];

    private $rules = [
        '_PACKAGE_NAME' => [
            [['PACKAGE'],['WHITESPACE'],['STRING_QUOTED', true]],
            '{PACKAGE}{WHITESPACE}(?<PACKAGENAME>{STRING_QUOTED})'
        ],
        '_VARIABLE' => [
            [[['DOLLAR'],['NAME'], 'VAR_NAME']],
            '(?<VARNAME>{DOLLAR}{NAME})'
        ],
        '_PACKAGE_VARIABLE' => [
            [['VAR'],['WHITESPACE'],['NAME', 'TYPEOF'],['WHITESPACE'],['_VARIABLE']],
            '{VAR}{WHITESPACE}(?<TYPEOF>{NAME}){WHITESPACE}{_VARIABLE}'
        ],
        '_BLOCK_START' => [
            [['BLOCK'],['WHITESPACE'],['NAME', 'BLOCKNAME'],['WHITESPACE'],['BLOCK_START']],
            '{BLOCK}{WHITESPACE}(?<BLOCKNAME>{NAME}){WHITESPACE}{BLOCK_START}'
        ],
        '_BLOCK_END' => [
            [['BLOCK_END']],
            '{BLOCK_END}'
        ],
        '_VARIABLE_METHOD' => [
            [['_VARIABLE', 'FROMVAR'], ['DOT'], ['NAME', 'METHOD']],
            '(?<OBJECTVAR>{DOLLAR}{NAME}){DOT}(?<METHOD>{NAME}){WHITESPACE}(?<VALUE>.*)'
        ]
    ];

    public function __construct($sourceCode)
    {
        if(empty($sourceCode)) {
            throw new \Exception("Source codes is empty!");
        }
        $this->sourceCode = $sourceCode;
    }

    public function scan()
    {
        return $this->tokenize();
    }

    protected function prepareRules()
    {
        $patterns = $this->tokens;
        $rules = [];
        foreach($this->rules as $ruleName => $rule) {
            $ruleString = $rule[1];
            if(!preg_match_all('/{(?<name>[^}]+)}/', $ruleString, $matches)) {
                error::throwNewException("Incorrect rule {$ruleName}");
            }
            foreach($matches['name'] as $foundToken) {
                if(!isset($patterns[$foundToken])) {
                    error::throwNewException("Incorrect rule {$ruleName} with token {$foundToken}");
                }
                $ruleString = str_replace("{{$foundToken}}", $patterns[$foundToken], $ruleString);
            }
            $patterns[$ruleName] = $ruleString;
            $rules[$ruleName] = $ruleString;
        }

        return $rules;
    }

    protected function tokenize()
    {
        $lines = explode("\n", $this->sourceCode);
        $lineNumber = 0;
        $result = [];
        $this->rules = array_reverse($this->rules, true);
        foreach($lines as $line) {
            $originLine = $line;
            $lineNumber++;
            if(!isset($result[$lineNumber])) {
                $result[$lineNumber] = [
                    'tokens_str' => '',
                    'tokens' => []
                ];
            }
            $pos = 0;
            do {
                $found = false;
                foreach ($this->tokens as $token => $tokenPattern) {
                    $pattern = "/(?<FOUND>^{$tokenPattern})/u";
                    if (preg_match($pattern, $line, $match)) {
                        foreach($match as $key => $found) {
                            if(is_int($key)) {
                                unset($match[$key]);
                            }
                        }
                        $found = true;
                        if($token == '__LINE_END__') {
                            break;
                        }
                        $len = mb_strlen($match['FOUND']);
                        $tokenObject = [
                            'type' => $token,
                            'value' => $match['FOUND'],
                            'line' => $lineNumber,
                            'pos' => $pos,
                            'len' => $len
                        ];
                        $result[$lineNumber]['tokens_str'] .= "{{$token}}";
                        $result[$lineNumber]['tokens'][] = $tokenObject;

                        $pos += $len;
                        $line = mb_substr($originLine, $pos);
                        break;
                    }
                }
                if(!$found && !empty($line)) {
                    error::throwNewException("Unexpected token: " . json_encode($line, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), $lineNumber, $pos);
                    exit;
                }
            } while(!empty($line));
        }

        return $result;
    }
}