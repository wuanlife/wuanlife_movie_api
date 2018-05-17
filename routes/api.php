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
        'verify_auth'
    ]
], function () {
    // R1 增加资源接口
    Route::post('/movies/{id}/resources', 'ResourceController@add');
    // R2 删除资源接口
    Route::delete('/movies/{id}/resources/{rid}', 'ResourceController@delete');
    // R3 编辑资源接口
    Route::put('/movies/{id}/resources/{rid}', 'ResourceController@edit');

    // M1 获取资源审核列表
    Route::post('/resource/background', 'UnreviewedResourceController@index');
    // M2 审核资源
    Route::put('/resource/{id}/background', 'UnreviewedResourceController@review');
    // M3 删除资源
    Route::delete('/resource/{id}/background', 'UnreviewedResourceController@deleteResource');

    // 获取午安影视积分接口
    Route::get('/users/{id}/movie_point', 'UsersController@getMoviePoint');
    // 获取午安账号积分接口
    Route::get('/users/{id}/wuan_point', 'UsersController@getWuanPoint');
    // 兑换午安账号积分接口
    Route::put('/users/{id}/point','UsersController@redeemWuanPoint');
});

// A1 首页接口
Route::get('/movies','MoviesController@home');
// A3 搜索影视
Route::post('/movies/search','SearchController@search');
// A4 获取分类条目
Route::get('/movies/type','TypeController@type');

// Z1 影视详情接口
Route::get('/movies/{id}','MoviesController@moviesDetails');
// Z2 显示资源接口
Route::get('/movies/{id}/resources', 'ResourceController@showResources');
// Z3 发现影视接口
Route::post('/movies','MoviesController@addMovie');
