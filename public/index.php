<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Core\Http\Request;
use Core\Http\Response;
use Core\Http\Router;

try {
    $request = new Request();
    $response = Router::route($request);
    $response->send();
    exit();
} catch (\RuntimeException $e) {
    Response::error($e->getMessage(), 400)->send();
} catch (\Exception $e) {
    $status = $e->getCode();
    if (!is_int($status) || $status < 400 || $status > 599) {
        $status = 500;
    }
    Response::error($e->getMessage(), $status)->send();
}