<?php
return [
    'Datasources' => ['test' => ['username' => 'root', 'password' => 'root', 'host' => '127.0.0.1', 'port' => '3306', 'database' => 'cakephp_test', 'persistent' => false, 'encoding' => 'utf8'], 'default' => ['username' => 'root', 'password' => 'root', 'host' => '127.0.0.1', 'port' => '3306', 'database' => 'cakephp', 'persistent' => false, 'encoding' => 'utf8']],
    'EmailTransport' => ['test' => ['className' => 'ArtSkills.TestEmail']],
    'debug' => true,
    'Security' => ['salt' => '7c47f1e793a39c7f518efc6b909b920ed5ba7a7470efc0501f2960973b7954dd'],
    'Sentry' => ['dsn' => ''],
    'testServerName' => 'eggheads.solutions',
];
