<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    //
    public function addAdmin()
    {
        // M4 新增管理员
        $id = 1;

        if(DB::table('users_auth')->where('id',$id)->update(array('auth'=>1)))
        {
            return response("新增成功",200);
        }
        else{
            return response(['error'=>"非法请求"],400);
        }

    }

    public function deleteAdmin($id)
    {
        // M5 取消管理员
        //auth 0普通用户 1管理员 2超级管理员
        if(DB::table('users_auth')->where('id',$id)->update(array('auth'=>0)))
        {
            return response("取消成功",200);
        }
        else{
            return response(['error'=>"非法请求"],400);
        }
    }

    public function listAdmin()
    {
        //获取管理员列表
        $r =DB::table('users_auth')->get();
        $re['admins'] = json_decode(json_encode($r),true);
//        print_r($re['admins'][0]);
        foreach ($re['admins'] as $key => $value) {
            $api_url = env('OIDC_SERVER_GET_USER_INFO_API') . '/api/app/users/' . $re['admins'][$key]['id'];
            $response = file_get_contents($api_url);
            $user = json_decode($response);
            $value['name'] = $user->name;
        }
        return response($re, 200);
    }
}
