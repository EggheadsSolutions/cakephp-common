<?php
declare(strict_types=1);

$tableAliasesDir = APP . 'Model' . DS . 'table_names.php';
if (file_exists($tableAliasesDir)) {
    require_once $tableAliasesDir;
}

require_once __DIR__ . '/functions.php';

if (!defined('VERSION_FILE')) {
    define('VERSION_FILE', CONFIG . 'version.txt');
}

if (!defined('CORE_VERSION')) {
    $version = 0;
    if (file_exists(VERSION_FILE)) {
        $version = preg_replace('/\D/', '', file_get_contents(VERSION_FILE));
        if (empty($version)) {
            $version = 0;
        }
    }
    define('CORE_VERSION', (int)$version);
    unset($version);
}
