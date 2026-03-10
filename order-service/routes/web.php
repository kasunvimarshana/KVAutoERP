<?php

/** @var \Laravel\Lumen\Routing\Router $router */

$router->get('/health', function () {
    return response()->json([
        'status'  => 'ok',
        'service' => 'order-service',
        'version' => '1.0.0',
    ]);
});

$router->group(['prefix' => 'api/orders', 'middleware' => 'auth'], function () use ($router) {
    $router->post('/',     'OrderController@store');
    $router->get('/',      'OrderController@index');
    $router->get('/{id}',  'OrderController@show');
    $router->patch('/{id}/cancel', 'OrderController@cancel');
});
