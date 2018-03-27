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
//$api = app('Dingo\Api\Routing\Router');
//
//$api->version('v1', function ($api) {
//    $api->post('movies/{id}/resources','App\Http\Api\Movies\ResourceController@add');
//    $api->delete('movies/{id}/resources/{rid}','App\Http\Api\Movies\ResourceController@delete');
//    $api->put('movies/{id}/resources/{rid}','App\Http\Api\Movies\ResourceController@edit');
//    $api->post('login', 'App\Http\Controllers\Api\Auth\LoginController@login');
//    $api->post('register', 'App\Http\Controllers\Api\Auth\RegisterController@register');
//
//});
Route::group([
    'middleware' => [
        'check_id_token',
        'check_access_token',
    ]
], function () {
    Route::post('/movies/{id}/resources', 'Movies\ResourceController@add');
    Route::delete('/movies/{id}/resources/{rid}', 'Movies\ResourceController@delete');
    Route::put('/movies/{id}/resources/{rid}', 'Movies\ResourceController@edit');
});


