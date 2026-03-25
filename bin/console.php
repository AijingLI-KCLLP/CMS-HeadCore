<?php
require_once __DIR__ . '/../vendor/autoload.php';

$commands = [
    'create:schema' => \Core\Commands\CreateSchema::class,
    'create:database' => \Core\Commands\CreateDatabase::class,
    'drop:database'   => \Core\Commands\DropDatabase::class,
];

$input = $argv[1] ?? '';

if (!$input || !isset($commands[$input])) {
    echo "Commandes disponibles :\n";
    foreach (array_keys($commands) as $cmd) {
        echo "  php bin/console.php $cmd\n";
    }
    exit(1);
}

new $commands[$input]()->execute();