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
        $a =DB::table('users_auth')->get();
        return $a;
    }
}
