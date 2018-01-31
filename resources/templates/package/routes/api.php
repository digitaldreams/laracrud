<?php

$router = app('Dingo\Api\Routing\Router');
//'middleware'=>['api.auth', 'api.throttle']
$router->version('v1', ['namespace' => '@@packageNamespace@@\Http\Controllers\Api', 'middleware' => []], function ($api) {
    // include __DIR__ . '/version1.php'; // Include the api route file.
});