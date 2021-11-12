<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
 */

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->group(['prefix' => 'auth'], function () use ($router) {
    $router->post('register', function () {
        // TODO: Routes this to the right controller
    });

    $router->post('login', function () {
        // TODO: Routes this to the right controller
    });
});

$router->group(['middleware' => 'auth'], function () use ($router) {
    $router->group(['prefix' => 'users'], function () use ($router) {
        $router->get('/{user}', function () {
            // TODO: Routes this to the right controller
        });
    });

    $router->group(['prefix' => 'books'], function () use ($router) {
        $router->get('/', function () {
            // TODO: Routes this to the right controller
        });

        $router->get('/{book}', function ($book) {
            // TODO: Routes this to the right controller
        });
    });

    $router->group(['prefix' => 'transactions'], function () use ($router) {
        $router->get('/{transaction}', function ($transaction) {
            // TODO: Routes this to the right controller
        });
    });
});

$router->group(['middleware' => 'auth:admin'], function () use ($router) {
    $router->group(['prefix' => 'users'], function () use ($router) {
        $router->get('/', function () {
            // TODO: Routes this to the right controller
        });
    });

    $router->group(['prefix' => 'books'], function () use ($router) {
        $router->post('/{book}', function ($book) {
            // TODO: Routes this to the right controller
        });

        $router->put('/{book}', function ($book) {
            // TODO: Routes this to the right controller
        });

        $router->delete('/{book}', function ($book) {
            // TODO: Routes this to the right controller
        });
    });

    $router->group(['prefix' => 'transactions'], function () use ($router) {
        $router->get('/', function () {
            // TODO: Routes this to the right controller
        });

        $router->put('/{transaction}', function ($transaction) {
            // TODO: Routes this to the right controller
        });
    });
});

$router->group(['middleware' => 'auth:user'], function () use ($router) {
    $router->group(['prefix' => 'users'], function () use ($router) {
        $router->put('/{user}', function ($user) {
            // TODO: Routes this to the right controller
        });

        $router->delete('/{user}', function ($user) {
            // TODO: Routes this to the right controller
        });
    });

    $router->group(['prefix' => 'transactions'], function () use ($router) {
        $router->get('/', function () {
            // TODO: Routes this to the right controller
        });

        $router->post('/', function () {
            // TODO: Routes this to the right controller
        });
    });
});
