<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Core\Http\Request;
use Core\Http\Response;
use Core\Http\Router;

// Test QueryBuilder — à supprimer après validation
$entity = new \ReflectionClass(\App\Entities\User::class);
$qb = new \Core\ORM\QueryBuilder($entity);

// Test 1 : select + orderBy + limit + offset
$qb->build()->select()->from()->orderBy('created_at', 'DESC')->limit(10)->offset(20);
echo $qb->debug() . "\n\n";

// Test 2 : where + param
$qb->build()->select()->from('u')->where('id', \Core\ORM\QueryConditions::EQ)->addParam('id', 42);
echo $qb->debug() . "\n\n";

// Test 3 : join
$qb->build()->select('u.*')->from('u')->innerJoin('roles r', 'r.id = u.role_id');
echo $qb->debug() . "\n\n";

// Test 4 : raw SQL
$qb->raw("SELECT * FROM users ORDER BY created_at DESC LIMIT :limit OFFSET :offset", ['limit' => 10, 'offset' => 0]);
echo $qb->debug() . "\n\n";

// Test 5 : hydratation
$row = ['id' => 1, 'name' => 'Ali', 'email' => 'ali@test.com', 'password' => 'hashed', 'created_at' => '2025-01-01'];
$user = \App\Entities\User::hydrate($row);
echo "Hydrate: " . $user->getName() . ' / ' . $user->getEmail() . "\n";
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