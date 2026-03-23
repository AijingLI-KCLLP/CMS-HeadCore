<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Core\Config\Config;

// Test Config
$routes = config('routes');
$sessionLifetime = config('auth.session_lifetime');
$missing = config('auth.nonexistent', 'default_value');

echo "routes: " . json_encode($routes) . "\n";
echo "auth.session_lifetime: $sessionLifetime\n";
echo "auth.nonexistent (default): $missing\n";
