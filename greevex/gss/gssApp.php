<?php

namespace greevex\gss;

use greevex\gss\lib\error;
use greevex\gss\lib\fileScanner;
use greevex\gss\translator\gsphp;

$rootpath = dirname(dirname(__DIR__));

require_once "{$rootpath}/greevex/autoloader.php";
$autoloader = new \SplClassLoader('greevex', $rootpath);
$autoloader->register();

if(!isset($argv[1])) {
    error::throwNewException('Filepath required to run');
}
$filepath = $argv[1];
if(!file_exists($filepath) || !is_readable($filepath)) {
    error::throwNewException("Filepath is not readable: {$filepath}");
}

$content = (new gsphp((new fileScanner($filepath))->scan()))->process();
file_put_contents("{$filepath}.php", $content);
eval($content);
