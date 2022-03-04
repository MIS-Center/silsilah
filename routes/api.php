<?php

$api = app('Dingo\Api\Routing\Router');

$api->version('v1', ['middleware' => ['api']], function($api) {
    $api->group(['namespace' => 'App\Http\Controllers\Api'], function($api) {

        $api->get('test', function () {
            $user = app('Dingo\Api\Auth\Auth')->user();
            // dd( $user ) ;
            return $user;
        });

        // Auth
        // $api->post('auth/create', 'Auth\AuthController@create');
        // $api->get('auth/login', 'Auth\AuthController@login');
        // $api->resource('categories', 'Settings\Categories');

        $api->group([
            'prefix' => 'auth'
        ], function ($api) {
        
            $api->post('login', 'Auth\AuthController@login');
            $api->post('logout', 'Auth\AuthController@logout');
            $api->post('refresh', 'Auth\AuthController@refresh');
            $api->post('me', 'Auth\AuthController@me');

            $api->post('register', 'Auth\AuthController@create');
        });

    });
});