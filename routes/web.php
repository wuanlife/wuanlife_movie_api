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

Route::group([
    'prefix' => 'admin',
    'middleware' => [
        'logged',
        'admin',
    ]
], function () {
    Route::get('resources', 'Admin\ResourcesController@create')->name('admin.resources');
});

Route::group([
    'prefix' => 'admin',
    'namespace' => 'Admin',
], function() {
    Route::get('login', 'LoginController@create')->name('admin.login');
    Route::post('login', 'LoginController@store')->name('admin.login');
});



