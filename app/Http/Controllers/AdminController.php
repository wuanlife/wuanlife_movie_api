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
//        $id_token = $request->get('id-token');
//        print_r($id_token);
//        die;
        $id = 1;
        if(DB::table('users_auth')->where('id',$id)->update(['auth'=>1]))
        {
            return response("新增成功",204);
        }
        else{
            return response(['error'=>"非法请求"],400);
        }
    }

    public function deleteAdmin($id)
    {
        // M5 取消管理员
        //auth 0普通用户 1管理员 2超级管理员
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
        //$id_token = $request->get('id-token');
        $users = DB::table('users_auth')->get()->toArray();
        foreach ($users as $key => $value) {
            $api_url = env('OIDC_SERVER_GET_USER_INFO_API') . $users[$key]['id'];
            $response = file_get_contents($api_url);
            $user = json_decode($response);
            $users[$key]['name'] = $user->name;
            unset($users[$key]['auth']);
        }
        return response($users, 200);
    }
}
