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

/*****************************************
 * 需要登录后操作的接口
 *****************************************/
Route::group([
    'middleware' => [
        'logged',
    ]
], function () {
    // R1 增加资源接口
    Route::post('/movies/{id}/resources', 'ResourceController@add');
    // R2 删除资源接口
    Route::delete('/movies/{id}/resources/{rid}', 'ResourceController@delete');
    // R3 编辑资源接口
    Route::put('/movies/{id}/resources/{rid}', 'ResourceController@edit');

    // S1 获取午安账号积分接口
    Route::get('/users/{id}/wuan_point', 'UsersController@getWuanPoint');
    // S2 获取午安影视积分接口
    Route::get('/users/{id}/movie_point', 'UsersController@getMoviePoint');
    // S3 兑换午安账号积分接口
    Route::put('/users/{id}/point', 'UsersController@redeemWuanPoint');
});

/*****************************************
 * 需要管理员权限的接口
 *****************************************/
Route::group([
    'middleware' => [
        'logged',
        'admin',
    ]
], function () {
    // M1 获取资源审核列表
    Route::get('/resources/background', 'AdminsController@getUnreviewedResources');
    // M2 审核资源
    Route::put('/resources/{id}/background', 'AdminsController@auditResource');
    // M3 删除资源
    Route::delete('/resources/{id}/background', 'AdminsController@rejectResource');
});

// Lwy
// 新增管理员
Route::post('/admin','AdminController@addAdmin');
// 取消管理员
Route::delete('/admin/{id}','AdminController@deleteAdmin');
// 获取管理列表
Route::get('/admin','AdminController@listAdmin');

/*****************************************
 * 不需要权限验证的接口
 *****************************************/
Route::group([

], function () {
    // A1 首页接口
    Route::get('/movies', 'MoviesController@home');
    // A3 搜索影视
    Route::post('/movies/search', 'SearchController@search');
    // A4 获取分类条目
    Route::get('/movies/type', 'TypeController@type');

    // Z1 影视详情接口
    Route::get('/movies/{id}', 'MoviesController@moviesDetails');
    // Z2 显示资源接口
    Route::get('/movies/{id}/resources', 'ResourceController@showResources');
    // Z3 发现影视接口
    Route::post('/movies', 'MoviesController@addMovie');


});
