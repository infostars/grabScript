<?php

namespace greevex\gsScanner;

use greevex\gsScanner\lib\fileScanner;

require_once __DIR__ . '/lib/codeScanner.php';
require_once __DIR__ . '/lib/fileScanner.php';
require_once __DIR__ . '/lib/tokenScanner.php';
require_once __DIR__ . '/lib/tokenParser.php';

$scanner = new fileScanner(__DIR__ . '/../sample.gs.cs');
$scanner->scan();