<?php

/** @var \Laravel\Lumen\Routing\Router $router */

$router->get('/health', function () {
    return response()->json([
        'status'  => 'ok',
        'service' => 'user-service',
        'version' => '1.0.0',
    ]);
});

$router->group(['prefix' => 'api/users'], function () use ($router) {
    // Public routes
    $router->post('/register', 'UserController@register');
    $router->post('/login',    'UserController@login');

    // Protected routes
    $router->group(['middleware' => 'auth'], function () use ($router) {
        $router->get('/me',       'UserController@me');
        $router->get('/{id}',     'UserController@show');
        $router->put('/{id}',     'UserController@update');
    });
});
