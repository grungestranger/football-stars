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

Route::view('/', 'main')->middleware('main.page', 'verified', 'verified.scope');

Auth::routes(['verify' => true]);
Route::get('/login', function () { abort(404); });

Route::group(['middleware' => ['verified', 'verified.scope']], function () {
    Route::get('/team', 'TeamController@index');
    Route::post('/team/save', 'TeamController@save');
    Route::post('/team/save-as', 'TeamController@saveAs');
    Route::post('/team/remove', 'TeamController@remove');
    Route::post('/create-challenge', 'AppController@createChallenge');
    Route::post('/remove-challenge', 'AppController@removeChallenge');
    Route::post('/play', 'AppController@play');
    Route::get('/jwt', 'AppController@jwt');
    Route::get('/get-common-data', 'AppController@getCommonData');
    Route::get('/get-users', 'AppController@getUsers');
    Route::get('/match', 'MatchController@index');
    Route::post('/match/save', 'MatchController@save');
});
