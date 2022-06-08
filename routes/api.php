<?php

$api = app('Dingo\Api\Routing\Router');

$api->version('v1', ['middleware' => ['api','cors']], function($api) {
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
        });

        $api->group([
            'prefix' => 'auth'
        ], function ($api) {
            $api->post('register', 'Auth\AuthController@register');
        });

        $api->group([
        ], function ($api) {
            $api->get('users/profile-search', 'UsersController@search');
            $api->get('users/{user}', 'UsersController@show');
            $api->get('users/{user}/chart', 'UsersController@chart') ;
            $api->get('users/{user}/tree', 'UsersController@tree') ;
            $api->get('users/{user}/grandtree', 'UsersController@grandtree') ;

            $api->get('users/{user}/death', 'UsersController@death') ;
            $api->patch('users/{user}/photo-upload', 'UsersController@photoUpload') ;
            $api->patch('users/{user}', 'UsersController@update') ;
            $api->delete('users/{user}', 'UsersController@destroy') ;
            $api->post('users/{user}/photo-upload', 'UsersController@photoUpload');
        });

        $api->group([
        ], function ($api) {
            $api->get('users/{user}/marriages', 'UserMarriagesController@index') ;
        });

        $api->group([
        ], function ($api) {
            $api->get('couples/{couple}', 'CouplesController@show');
            $api->patch('couples/{couple}', 'CouplesController@update');
        });

        $api->group([
        ], function ($api) {
            $api->post('family-actions/{user}/set-father', 'FamilyActionsController@setFather');
            $api->post('family-actions/{user}/set-mother', 'FamilyActionsController@setMother');
            $api->post('family-actions/{user}/add-child', 'FamilyActionsController@addChild');
            $api->post('family-actions/{user}/add-wife', 'FamilyActionsController@addWife');
            $api->post('family-actions/{user}/add-husband', 'FamilyActionsController@addHusband');
            $api->post('family-actions/{user}/set-parent', 'FamilyActionsController@setParent');
            
        });

    });
});