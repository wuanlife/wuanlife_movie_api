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
    'namespace' => 'Admin',
], function() {
    Route::get('login', 'LoginController@create')->name('auth.login');
    Route::post('login', 'LoginController@store')->name('auth.login');
    Route::Delete('login', 'LoginController@destory')->name('auth.logout');
    Route::get('resources', 'ResourcesController@index')->name('resources.index');
    Route::post('resources', 'ResourcesController@auditResource')->where('id', '[0-9]+')->name('resources.audit');
    Route::get('admins', 'AdminsController@index')->name('admins.index');
    Route::post('admins', 'AdminsController@action')->name('admins.action');
});



