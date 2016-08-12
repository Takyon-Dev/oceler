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

Route::get('/home', function(){

  if(Auth::user()->role_id == 2){
    $route = 'admin_home';
  }
  else {
    $route = 'player_home';
  }

  return Redirect::route($route);

});



// Player routes...

Route::get('player/', [
  'as' => 'player_home',
	'middleware' => ['auth', 'roles'], // A 'roles' middleware must be specified
	'uses' => 'PlayerController@home',
	'roles' => ['player'] // Only a player role can view this page
]);


Route::get('player/trial', [
	'middleware' => ['auth', 'roles'], // A 'roles' middleware must be specified
	'uses' => 'PlayerController@playerTrial',
	'roles' => ['player'] // Only a player role can view this page
]);

Route::get('player/trial/initialize', [
	'middleware' => ['auth', 'roles'], // A 'roles' middleware must be specified
	'uses' => 'PlayerController@initializeTrial',
	'roles' => ['player'] // Only a player role can view this page
]);

Route::get('player/trial/queue', [
	'middleware' => ['auth', 'roles'], // A 'roles' middleware must be specified
	'uses' => 'TrialController@enterQueue',
	'roles' => ['player'] // Only a player role can view this page
]);

Route::get('player/trial/queue/status', [
	'middleware' => ['auth', 'roles'], // A 'roles' middleware must be specified
	'uses' => 'TrialController@queue',
	'roles' => ['player'] // Only a player role can view this page
]);

Route::get('player/trial/end', [
	'middleware' => ['auth', 'roles'], // A 'roles' middleware must be specified
	'uses' => 'PlayerController@endTrialRound',
	'roles' => ['player'] // Only a player role can view this page
]);

Route::get('player/trial/new-round', [
	'middleware' => ['auth', 'roles'], // A 'roles' middleware must be specified
	'uses' => 'PlayerController@startTrialRound',
	'roles' => ['player'] // Only a player role can view this page
]);

Route::post('/solution', [
	'middleware' => ['auth', 'roles'],
	'uses' => 'PlayerController@postSolution',
	'roles' => ['player']
]);

Route::get('/listen/solution/{id}', [
	'middleware' => ['auth', 'roles'],
	'uses' => 'PlayerController@getListenSolution',
	'roles' => ['player']
]);

Route::post('/message', [
	'middleware' => ['auth', 'roles'],
	'uses' => 'MessageController@postMessage',
	'roles' => ['player']
]);

Route::get('/listen/message/{id}', [
	'middleware' => ['auth', 'roles'],
	'uses' => 'MessageController@getListenMessage',
	'roles' => ['player']
]);

Route::post('/reply', [
	'middleware' => ['auth', 'roles'],
	'uses' => 'ReplyController@postReply',
	'roles' => ['player']
]);

Route::post('/search', [
	'middleware' => ['auth', 'roles'],
	'uses' => 'SearchController@postSearch',
	'roles' => ['player']
]);

//Route::post('solution', 'PlayerController@postSolution');
//Route::get('listen/solution/{id}', 'PlayerController@getListenSolution');

//Route::post('message', 'MessageController@postMessage');
//Route::get('listen/message/{id}', 'MessageController@getListenMessage');

//Route::post('reply', 'ReplyController@postReply');

// Admin routes...
Route::get('/admin/players', [
  'as' => 'admin_home',
	'middleware' => ['auth', 'roles'],
	'uses' => 'AdminController@showPlayers',
	'roles' => ['administrator']
]);

Route::get('/admin/listen/queue', [
	'middleware' => ['auth', 'roles'],
	'uses' => 'AdminController@getListenQueue',
	'roles' => ['administrator']
]);

Route::get('/admin/listen/trial', [
	'middleware' => ['auth', 'roles'],
	'uses' => 'TrialController@getListenAllTrialPlayers',
	'roles' => ['administrator']
]);

Route::get('/admin/trial', [
	'middleware' => ['auth', 'roles'],
	'uses' => 'TrialController@index',
	'roles' => ['administrator']
]);

Route::get('/admin/trial/create', [
	'middleware' => ['auth', 'roles'],
	'uses' => 'TrialController@create',
	'roles' => ['administrator']
]);

Route::post('/admin/trial', [
	'middleware' => ['auth', 'roles'],
	'uses' => 'TrialController@store',
	'roles' => ['administrator']
]);

Route::get('/admin/trial/{id}', [
	'middleware' => ['auth', 'roles'],
	'uses' => 'TrialController@getTrial',
	'roles' => ['administrator']
]);

Route::delete('/admin/trial/{id}', [
  'as' => 'trial.delete',
	'middleware' => ['auth', 'roles'],
	'uses' => 'TrialController@destroy',
	'roles' => ['administrator']
]);


Route::get('/admin/trial/toggle/{id}', [
	'middleware' => ['auth', 'roles'],
	'uses' => 'TrialController@toggle',
	'roles' => ['administrator']
]);

Route::get('/admin/log', [
	'middleware' => ['auth', 'roles'],
	'uses' => 'AdminController@showLogs',
	'roles' => ['administrator']
]);

Route::get('/admin/config-files', [
	'middleware' => ['auth', 'roles'],
	'uses' => 'AdminController@showConfigFiles',
	'roles' => ['administrator']
]);


// Authentication routes...
Route::get('/login', 'Auth\AuthController@getLogin');
Route::post('/login', 'Auth\AuthController@postLogin');
Route::get('/logout', 'Auth\AuthController@getLogout');

// Registration routes...
Route::get('/register', 'Auth\AuthController@getRegister');
Route::post('/register', 'Auth\AuthController@postRegister');

Route::get('password/email', 'Auth\PasswordController@getEmail');
Route::post('password/email', 'Auth\PasswordController@postEmail');

// Password reset routes...
Route::get('password/reset/{token}', 'Auth\PasswordController@getReset');
Route::post('password/reset', 'Auth\PasswordController@postReset');
