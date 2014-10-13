<?php

namespace greevex\gss\translator;
/**
 * @author greevex
 * @date: 10/1/14 6:10 PM
 */

class helper
{
    public static function dotPathToPhpPath($path)
    {
        $path = trim($path);
        if(strpos($path, 'this.') === 0) {
            $path = "\${$path}";
        }
        $result = '';
        $items = explode('.', $path);
        $isObject = strpos($path, '$') === 0;
        if($isObject) {
            $result = implode('->', $items);
        } else {
            foreach ($items as $item) {
                if (empty($result)) {
                    $result .= "{$item}";
                    continue;
                }
                $result .= "['{$item}']";
            }
        }

        return $result;
    }

    public static function callableToSourceCode($data)
    {
        $args = [];
        foreach($data['arguments'] as $argument) {
            $args[] = helper::getValueByToken($argument)['value'];
        }
        $callable = helper::getValueByToken($data['callable'])['value'];
        return "{$callable}(" . implode(', ', $args) . ");";
    }

    public static function dotPathToNamespace($path)
    {
        $path = trim($path);
        $result = implode('\\', explode('.', $path));

        return $result;
    }

    public static function splitNamespaceAndClass($namespace)
    {
        $namespace = trim($namespace);
        $exploded = explode('\\', $namespace);
        $class = array_pop($exploded);
        $namespace = implode('\\', $exploded);

        return [
            'class' => $class,
            'namespace' => $namespace
        ];
    }

    public static function getValueByToken($token)
    {
        $result = [
            'type' => null,
            'value' => null
        ];
        switch($token['type']) {
            case 'VARIABLE_PATH':
            case 'VARIABLE_METHOD':
            case 'THIS_METHOD':
                $result['type'] = 'mixed';
                $result['value'] = self::dotPathToPhpPath($token['value']);
                break;
            case 'OBJECT_PATH':
                $result['type'] = 'mixed';
                $result['value'] = self::dotPathToPhpPath('$' . $token['value']);
                break;
            case 'OBJECT':
                $result['type'] = 'object';
                $result['value'] = $token['value'];
                break;
            case 'STRING':
                $result['type'] = 'string';
                $result['value'] = "'{$token['value']}'";
                break;
            case 'INTEGER':
                $result['type'] = 'int';
                $result['value'] = (int)$token['value'];
                break;
            case 'FLOAT':
                $result['type'] = 'float';
                $result['value'] = (float)$token['value'];
                break;
            case 'VARIABLE':
                $result['type'] = 'mixed';
                $result['value'] = $token['value'];
                break;
            case 'STRING_QUOTED':
                $result['type'] = 'string';
                $result['value'] = $token['value'];
                break;
            case 'BOOL_TRUE':
                $result['type'] = 'bool';
                $result['value'] = $token['value'];
                break;
            case 'BOOL_FALSE':
                $result['type'] = 'bool';
                $result['value'] = $token['value'];
                break;
            case 'NULL':
                $result['type'] = 'null';
                $result['value'] = $token['value'];
                break;
            case 'EQUALS':
            case 'NOT_EQUALS':
            case 'GT':
            case 'GTE':
            case 'LT':
            case 'LTE':
                $result['type'] = 'operator';
                $result['value'] = $token['value'];
                break;
        }

        return $result;
    }
}