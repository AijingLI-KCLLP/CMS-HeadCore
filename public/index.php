<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Core\Http\Request;
use Core\Http\Response;
use Core\Http\Router;

// Test hydratation — à supprimer après validation
$row = ['id' => 1, 'name' => 'Ali', 'email' => 'ali@test.com', 'password' => 'hashed', 'created_at' => '2025-01-01'];
$user = \App\Entities\User::hydrate($row);
echo $user->getName() . ' / ' . $user->getEmail();
exit();

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