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

Route::get('/', function () {
    return view('welcome');
});



Route::get('/api/movies/{id}','MovieController@moviesdetails');//M1 影视详情
Route::post('/api/movies','MovieController@moviesfind');//M3 发现影视


