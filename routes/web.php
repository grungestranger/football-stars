<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::view('/', 'main')->middleware('main.page', 'verified');

Auth::routes(['verify' => true]);
Route::get('/login', function () { abort(404); });

Route::group(['middleware' => 'verified'], function () {
    Route::get('/team', 'TeamController@index')->name('team');
    Route::get('/team/get-schema/{id}', 'TeamController@getSchema');
    Route::post('/team/save-schema', 'TeamController@saveSchema');
    Route::post('/team/create-schema', 'TeamController@createSchema');
    Route::post('/team/remove-schema', 'TeamController@removeSchema');
    Route::post('/create-challenge', 'AppController@createChallenge');
    Route::post('/remove-challenge', 'AppController@removeChallenge');
    Route::post('/play', 'AppController@play');
    Route::get('/jwt', 'AppController@jwt');
    Route::get('/get-common-data', 'AppController@getCommonData');
    Route::get('/get-users', 'AppController@getUsers');
    Route::get('/match', 'MatchController@index')->name('match');
    Route::post('/match/save', 'MatchController@save');
});
