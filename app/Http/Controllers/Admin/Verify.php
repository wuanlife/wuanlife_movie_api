<?php

namespace App\Http\Controllers\Admin;

use App\Models\Users\UsersAuth;
use Illuminate\Http\Request;

class Verify
{
    public static function VerifyToken(Request $request)
    {
        if (!($id_token = session('wuan.ID-Token'))) {
            return response(['error' => '缺少ID-Token'], 401);
        }
        if (!($access_token = session('wuan.Access-Token'))) {
            return response(['error' => '缺少Access-Token'], 401);
        }

        $headers = [
            'ID-Token' => $id_token,
            'Access-Token' => $access_token,
        ];
        $res = Http::request(env('OIDC_SERVER'), '/api/auth', 'GET', $headers);

        if ($res['status_code'] !== 200) {
            return false;
        }
        $id_token = json_decode(base64_decode(explode('.', $id_token)[1]));
        $request->attributes->add(['id-token' => $id_token]);
        return true;
    }

    public static function VerifyAdmin($user_id)
    {
        return UsersAuth::checkAuth($user_id);
    }
}