<?php
declare(strict_types=1);

$rootFolder = dirname(__DIR__);
require $rootFolder . '/test-app-conf/bootstrap.php';
require $rootFolder . '/config/bootstrap_test.php';

Cake\Core\Plugin::getCollection()->add(new \Migrations\Plugin());
Cake\Core\Plugin::getCollection()->add(new \CakephpFixtureFactories\Plugin());

use Migrations\TestSuite\Migrator;

$migrator = new Migrator();
$migrator->run();

//Выполнение миграций для PostgreSql
(new Migrator())->run(
    ['connection' => 'test_postgres', 'source' => './Migrations/Postgres']
);
