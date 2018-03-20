<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
//这句接管路由
$api = app('Dingo\Api\Routing\Router');

$api->version('v1', function ($api) {
    $api->post('movie/{id}/resources','App\Http\Api\Movies\ResourceController@add');
    $api->delete('movie/{id}/resources/{rid}','App\Http\Api\Movies\ResourceController@delete');
    $api->put('movie/{id}/resources/{rid}','App\Http\Api\Movies\ResourceController@edit');
    $api->post('login', 'App\Http\Controllers\Api\Auth\LoginController@login');
    $api->post('register', 'App\Http\Controllers\Api\Auth\RegisterController@register');

});



