<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    //
    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function addAdmin(Request $request)
    {
        // M4 新增管理员
        // 验证权限
//        $validator = $this->validator($request->all());
//        if ($validator->fails()) {
//            return response(['error' => $validator->errors()], 422);
//        }
        $uid = $request->get('id-token')->uid;
        if (DB::table('users_auth')->where('id'.$uid)->get()->toArray()[0]['auth'] != 2) {
            return response(['error' => '没有权限操作'], 422);
        };
        //用email从某个接口获取id
        $id = 1;
        if(DB::table('users_auth')->where('id',$id)->update(['auth'=>1]))
        {
            return response("新增成功",204);
        }
        else{
            return response(['error'=>"非法请求"],400);
        }
    }

    public function deleteAdmin(Request $request,$id)
    {
        // M5 取消管理员
        //auth 0普通用户 1管理员 2超级管理员

        $uid = $request->get('id-token')->uid;
        if (DB::table('users_auth')->where('id'.$uid)->get()->toArray()[0]['auth'] != 2) {
            return response(['error' => '没有权限操作'], 422);
        };

        if(DB::table('users_auth')->where('id',$id)->update(['auth'=>0]))
        {
            return response("取消成功",204);
        }
        else{
            return response(['error'=>"非法请求"],400);
        }
    }

    public function listAdmin(Request $request)
    {

        //获取管理员列表
        //验证权限
        $uid = $request->get('id-token')->uid;
        if (DB::table('users_auth')->where('id'.$uid)->get()->toArray()[0]['auth'] != 2) {
            return response(['error' => '没有权限操作'], 422);
        };
        //
        $users['admins'] = DB::table('users_auth')->get()->toArray();
        foreach ($users['admins'] as $key => $value) {
            $api_url = env('OIDC_SERVER_GET_USER_INFO_API') . $users['admins'][$key]['id'];
            $response = file_get_contents($api_url);
            $user = json_decode($response);
            $users['admins'][$key]['name'] = $user->name;
            unset($users['admins'][$key]['auth']);
        }
        return response($users, 200);
    }
}
