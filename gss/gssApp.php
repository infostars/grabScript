<?php

namespace greevex\gss;

use greevex\gss\lib\fileScanner;

require_once __DIR__ . '/lib/fileScanner.php';
require_once __DIR__ . '/lib/codeScanner.php';
require_once __DIR__ . '/lib/tokenParser.php';
require_once __DIR__ . '/lib/blockParser.php';
require_once __DIR__ . '/lib/variableMethodParser.php';
require_once __DIR__ . '/lib/foreachParser.php';
require_once __DIR__ . '/lib/inputParser.php';
require_once __DIR__ . '/lib/actionParser.php';
require_once __DIR__ . '/lib/ifParser.php';
require_once __DIR__ . '/lib/returnParser.php';
require_once __DIR__ . '/lib/error.php';

$scanner = new fileScanner(__DIR__ . '/sample.gss');
$result = $scanner->scan();
echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);