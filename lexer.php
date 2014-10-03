<?php
/**
 * @author greevex
 * @date: 10/1/14 6:10 PM
 */

class lexer
{
    const T_METHOD = 'method';
    const T_CALL = 'call';
    const T_UNKNOWN = 'unknown';
    const T_VAR_ASSIGN = 'var-assign';
    const T_VAR_CALL = 'var-call';
    const T_INPUT_VAR = 'input-var';
    const T_CONST = 'const';
    const T_STRING = 'string';
    const T_INT = 'integer';
    const T_FLOAT = 'float';
    const T_OBJECT = 'object';
    const T_ACTION = 'action';
    const T_RETURN = 'return';

    private $codeContent = null;
    private $codePrepend = "<?php ";
    private $tokens;
    private $currentType;
    private $structuredData = [
        'namespace' => null,
        'class' => null,
        'meta' => [
            'levels' => [

            ]
        ]
    ];
    private $currentBraceLevel = 0;
    private $errors;

    private $testData = [
        'namespace' => 'asd',
        'class' => 'asd',
        'properties' => [
            'config' => [
                'visibility' => 'public',
                'type' => 'Json',
                'value' => '{
        start_url: "http://ya.ru/",
        userAgent: "Curl 0.7",
        parserDriver: "grabtemplate"
    }'
            ]
        ],
        'methods' => [
            'onProcessFail' => [
                'input' => [
                    0 => [
                        'input-type' => 'var',
                        'meta' => [
                            'name' => 'request',
                            'type' => 'Request',
                        ]
                    ]
                ],
                //'Debug::dump($request, 123);'
                'content' => [
                    73 => [
                        'line' => 73,
                        'type' => 'call',
                        'meta' => [
                            'object' => 'Debug',
                            'method' => 'dump',
                            'params' => [
                                0 => [
                                    'input-type' => 'assigned-var',
                                    'meta' => [
                                        'type' => 'var',
                                        'name' => 'request'
                                    ]
                                ],
                                1 => [
                                    'input-type' => 'const',
                                    'meta' => [
                                        'type' => 'integer',
                                        'value' => 123
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],

        ],
        'content' => [
            18 => [
                'line' => 18,
                'type' => 'var-assign',
                'meta' => [
                    'name' => 'item',
                    'type' => 'ParsedItem',
                    'action' => 'assignment',
                    'value' => [
                        'type' => 'var-value',
                        'name' => 'item'
                    ]
                ]
            ]
        ]
    ];

    public function __construct($codeContent)
    {
        $this->codeContent = $codeContent;
        $this->buildTokens();
    }

    protected function buildTokens()
    {
        $tokens = token_get_all("{$this->codePrepend}{$this->codeContent}");
        $line = 0;
        while($token = array_shift($tokens)) {
            if(is_string($token)) {
                $newToken = [
                    'type' => 'CHAR',
                    'value' => $token,
                    'line' => $line
                ];
            } else {
                $newToken = [
                    'type' => token_name($token[0]),
                ];
                if (isset($token[2])) {
                    $line = $token[2];
                }
                $newToken['line'] = $line;
                if (isset($token[1])) {
                    $newToken['value'] = $token[1];
                }
            }
            $this->tokens[] = $newToken;
        }
    }

    public function parse()
    {
        foreach($this->tokens as $token) {
            $this->processToken($token);
        }

        var_dump("Errors", $this->errors);
        var_dump("Parsed", $this->structuredData);
    }

    const TYPE_NAMESPACE = 'namespace';
    const TYPE_CLASS = 'class';
    const TYPE_COMMENT = 'comment';

    protected function assignToken($token)
    {
        switch($this->currentType) {
            case self::TYPE_NAMESPACE:
                $this->assignNamespaceToken($token);
                break;
            case self::TYPE_CLASS:
                $this->assignClassToken($token);
                break;
            case self::TYPE_COMMENT:
                $this->assignComment($token);
                break;
            default:
                $this->assignSomething($token);
                break;
        }
    }

    protected function assignSomething($token)
    {
        error_log("AS>{$token['line']} [{$this->currentType}] {$token['type']} - " . json_encode($token['value']));
        switch($token['type']) {
            case 'T_STRING':
            case 'T_CONSTANT_ENCAPSED_STRING':
            case 'T_LIST':
            case 'CHAR':
                $this->processLineToken($token);
                break;
        }
    }

    protected function processLineToken($token)
    {
        static $iteration = 0;
        static $type = null;
        static $object = [];
        static $inputLevel = 0;
        static $paramIteration = 0;
        static $currentMethod = null;

        error_log("[ITERATION-{$iteration}] object: " . json_encode($object));

        switch($iteration) {
            case 0:
                if($token['type'] == 'CHAR') {
                    $this->error_unexpectedToken($token, "Expecting var type assignment");
                } elseif($type === null) {
                    error_log("[OK] Object type received");
                    $type = self::T_UNKNOWN;
                    $object['instanceOf'] = $token['value'];
                    $iteration++;
                } else {
                    $this->error_unexpectedToken($token, "Iteration repeated");
                }
                break;
            case 1:
                if($token['type'] == 'CHAR') {
                    if($token['value'] == '<') {
                        $object['inner-type-waiting'] = true;
                        error_log("[OK] Inner type assignment received:open");
                    } elseif($token['value'] == '>') {
                        unset($object['inner-type-waiting']);
                        error_log("[OK] Inner type assignment received:close");
                    } else {
                        $this->error_unexpectedToken($token, "Expecting var name or inner-type assignment");
                    }
                } elseif($type === null) {
                    $this->error_unexpectedToken($token, "Expecting type was assigned");
                } else {
                    if(isset($object['inner-type-waiting']) && $object['inner-type-waiting']) {
                        error_log("[OK] Object inner-type received");
                        $object['inner-type'] = $token['value'];
                        $object['inner-type-waiting'] = false;
                    } else {
                        error_log("[OK] Object name received");
                        $object['name'] = $token['value'];
                        $iteration++;
                    }
                }
                break;
            default:
                if($token['type'] == 'CHAR') {
                    switch($token['value']) {
                        case ';':
                            error_log("[OK] Object received, storing");
                            $object['type'] = self::T_VAR_ASSIGN;
                            $this->structuredData['content'][$token['line']] = $object;
                            $type = null;
                            $iteration = 0;
                            $object = [];
                            $inputLevel = 0;
                            $paramIteration = 0;
                            break;
                        case '=':
                            error_log("[OK] Object is variable, waiting value");
                            $object['type'] = self::T_VAR_ASSIGN;
                            $object['has_value'] = true;
                            $iteration++;
                            break;
                        case '(':
                            error_log("[OK] Object is method, waiting params");
                            $object['type'] = self::T_METHOD;
                            $object['waiting-params'] = true;
                            $object['input-params'][$inputLevel] = [];
                            break;
                        case ')':
                            error_log("[OK] Object is method, no more waiting params");
                            $maxInput = $inputLevel - 1;
                            if($maxInput < 0) {
                                $object['input-params'][$inputLevel] = [
                                    'type' => self::T_VAR_CALL,
                                    'name' => array_shift($object['input-params'][$inputLevel])
                                ];
                            }
                            unset($object['waiting-params']);
                            break;
                        case '{':
                            error_log("[OK] Method assignment received, storing meta");
                            $this->currentBraceLevel++;
                            $inputLevel = 0;
                            $currentMethod = $object['name'];
                            $this->structuredData['methods'][$currentMethod] = $object;
                            $type = null;
                            $iteration = 0;
                            $object = [];
                            break;
                            break;
                        case '}':
                            error_log("[OK] Method end received");
                            $this->currentBraceLevel--;
                            $currentMethod = null;
                            $iteration = 0;
                            $object = [];
                            break;
                        case '.':
                            error_log("[OK] Value is call, iteration--");
                            $object['value-is-call'] = true;
                            $iteration--;
                            break;
                        default:
                            $this->error_unexpectedToken($token, "Expecting something");
                            break;
                    }
                } elseif($type === null) {
                    $this->error_unexpectedToken($token, "Expecting type was assigned");
                } else {
                    if(isset($object['waiting-params'])) {
                        static $paramIterations = [
                            0 => 'instanceOf',
                            1 => 'name'
                        ];
                        if(isset($paramIterations[$paramIteration])) {
                            error_log("[OK] Received input param");
                            $object['input-params'][$inputLevel][$paramIterations[$paramIteration]] = $token['value'];
                            if(++$paramIteration == 2) {
                                $paramIteration = 0;
                                $inputLevel++;
                            }
                        } else {

                        }
                    } elseif(isset($object['value-is-call'])) {
                        error_log("[OK] Value-is-call, converting value to call");
                        $object['value'] = [
                            'type' => self::T_CALL,
                            'object' => $object['value'],
                            'method' => $token['value']
                        ];
                    } else {
                        error_log("[OK] Object variable value received");
                        $object['value'] = $token['value'];
                    }
                }
                break;
        }
    }

    protected function assignComment($token)
    {
        $this->currentType = null;
        error_log("COMMENT>{$token['line']} [{$this->currentType}] {$token['type']} - " . json_encode($token['value']));
    }

    protected function assignClassToken($token)
    {
        if(empty($this->structuredData['class'])) {
            $this->structuredData['class'] = $token['value'];
        } elseif($token['type'] == 'CHAR' && $token['value'] == '.') {
            $this->structuredData['class'] .= '\\';
        } elseif($token['type'] == 'T_STRING') {
            $this->structuredData['class'] .= $token['value'];
        } elseif($token['type'] == 'CHAR' && $token['value'] == '{') {
            $this->structuredData['meta']['class_opened'] = true;
            $this->currentBraceLevel++;
            $this->currentType = null;
            return;
        }else {
            $this->error_unexpectedToken($token);
        }
    }

    protected function assignNamespaceToken($token)
    {
        if(empty($this->structuredData['namespace'])) {
            $this->structuredData['namespace'] = $token['value'];
        } elseif($token['type'] == 'CHAR' && $token['value'] == '.') {
            $this->structuredData['namespace'] .= '\\';
        } elseif($token['type'] == 'T_STRING') {
            $this->structuredData['namespace'] .= $token['value'];
        } elseif($token['type'] == 'CHAR' && $token['value'] == ';') {
            $this->currentType = null;
            return;
        } else {
            $this->error_unexpectedToken($token);
        }
    }

    protected function error_unexpectedToken($token, $comment = null)
    {
        $this->errors[] = [
            'line' => $token['line'],
            'error' => "Unexpected {$token['type']}",
            'token' => $token,
            'comment' => $comment
        ];

        debug_zval_dump("EXCEPTION", $this->errors, $this->structuredData);
        exit;
    }

    protected function processToken($token)
    {
        switch($token['type']) {
            case 'T_OPEN_TAG':
            case 'T_WHITESPACE':
                break;
            case 'T_NAMESPACE':
                $this->currentType = self::TYPE_NAMESPACE;
                break;
            case 'T_CLASS':
                $this->currentType = self::TYPE_CLASS;
                break;
            case 'T_COMMENT':
                $this->currentType = self::TYPE_COMMENT;
                break;
            case 'CHAR':
            case 'T_LIST':
            case 'T_STRING':
            case 'T_CONSTANT_ENCAPSED_STRING':
            case 'T_PLUS_EQUAL':
            case 'T_NEW':
            case 'T_FOREACH':
            default:
                $this->assignToken($token);
                break;
        }
    }
}

$code = file_get_contents(__DIR__ . '/sample.gs.cs');
$lexer = new lexer($code);
$lexer->parse();