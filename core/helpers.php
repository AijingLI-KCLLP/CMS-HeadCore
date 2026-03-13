<?php

use Core\Config\Config;

if (!function_exists('config')) {
    function config(string $key, mixed $default = null): mixed {
        return Config::get($key, $default);
    }
}