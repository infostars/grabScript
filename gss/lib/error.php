<?php

namespace greevex\gss\lib;
/**
 * @author greevex
 * @date: 10/1/14 6:10 PM
 */

class error
{

    /**
     * @param      $message
     * @param null $line
     * @param null $pos
     *
     * @return bool
     */
    public static function throwNewException($message, $line = null, $pos = null)
    {
        $errorMessage = "GrabScript Exception: {$message}";
        if(isset($line, $pos)) {
            $errorMessage .= <<<TEXT
 on line {$line} at offset {$pos}
TEXT;
        }
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 2)[1];
        $errorMessage .= <<<TEXT

 from {$backtrace['class']}.{$backtrace['function']} at {$backtrace['line']}
TEXT;
        error_log($errorMessage);
        debug_print_backtrace();
        return exit(1);
    }

    /**
     * @param      $message
     * @param null $line
     * @param null $pos
     *
     * @return bool
     */
    public static function throwNewCompileException($message, $line = null, $pos = null)
    {
        $errorMessage = "GrabScript compile exception: {$message}";
        if(isset($line, $pos)) {
            $errorMessage .= <<<TEXT
 on line {$line} at offset {$pos}
TEXT;
        }
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 2)[1];
        $errorMessage .= <<<TEXT

 from {$backtrace['class']}.{$backtrace['function']} at {$backtrace['line']}
TEXT;
        error_log($errorMessage);
        return exit(1);
    }
}