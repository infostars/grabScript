<?php

namespace greevex\gss;

use greevex\gss\lib\fileScanner;
use greevex\gss\translator\gsphp;

require_once __DIR__ . '/lib/fileScanner.php';
require_once __DIR__ . '/lib/codeScanner.php';
require_once __DIR__ . '/lib/tokenParser.php';
require_once __DIR__ . '/lib/blockParser.php';
require_once __DIR__ . '/lib/variableMethodParser.php';
require_once __DIR__ . '/lib/variableAction.php';
require_once __DIR__ . '/lib/foreachParser.php';
require_once __DIR__ . '/lib/inputParser.php';
require_once __DIR__ . '/lib/actionParser.php';
require_once __DIR__ . '/lib/ifParser.php';
require_once __DIR__ . '/lib/returnParser.php';
require_once __DIR__ . '/lib/error.php';
require_once __DIR__ . '/translator/gsphp.php';
require_once __DIR__ . '/translator/helper.php';

$scanner = new fileScanner(__DIR__ . '/sample.gss');
$structure = $scanner->scan();
echo json_encode($structure, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
$gsphp = new gsphp($structure);
$gsphp->process();
