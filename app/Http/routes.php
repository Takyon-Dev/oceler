<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', function () {
    return view('welcome');
});
/*
Route::get('/player', function () {
    return view('layouts.player.main');
});
*/

/*
Route::get('player', function () {
    return '[INSERT LOGIN PAGE HERE]';
});
*/

Route::get('player/', 'PlayerController@getShow');

Route::post('solution', 'PlayerController@postSolution');
Route::get('listen/solution/{id}', 'PlayerController@getListenSolution');

Route::post('message', 'MessageController@postMessage');
Route::get('listen/message/{id}', 'MessageController@getListenMessage');

// Authentication routes...
Route::get('auth/login', 'Auth\AuthController@getLogin');
Route::post('auth/login', 'Auth\AuthController@postLogin');
Route::get('auth/logout', 'Auth\AuthController@getLogout');

// Registration routes...
Route::get('auth/register', 'Auth\AuthController@getRegister');
Route::post('auth/register', 'Auth\AuthController@postRegister');

Route::get('password/email', 'Auth\PasswordController@getEmail');
Route::post('password/email', 'Auth\PasswordController@postEmail');

// Password reset routes...
Route::get('password/reset/{token}', 'Auth\PasswordController@getReset');
Route::post('password/reset', 'Auth\PasswordController@postReset');
