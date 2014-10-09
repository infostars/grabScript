<?php

$content = file_get_contents(__DIR__ . '/sample.gcm.php');
debug_zval_dump(optimizeTokens(token_get_all($content)));

function optimizeTokens($tokens)
{
    $result = [];
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
        $result[] = $newToken;
    }

    return $result;
}