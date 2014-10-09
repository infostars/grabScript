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

    public static function dotPathToNamespace($path)
    {
        $path = trim($path);
        $result = implode('\\', explode('.', $path));

        return $result;
    }

    public static function splitNamespaceAndClass($namespace)
    {
        $namespace = trim($namespace);
        $exploded = implode('\\', $namespace);
        $class = array_pop($exploded);
        $namespace = implode('\\', $exploded);

        return [
            'class' => $class,
            'namespace' => $namespace
        ];
    }
}