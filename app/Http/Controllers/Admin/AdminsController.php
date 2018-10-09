<?php

namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminsController extends Controller
{
    public function index(Request $request)
    {
        $id_token = session('wuan.ID-Token');
        $access_token = session('wuan.Access-Token');
        $limit = $request->query('limit') ?? env('LIMIT');
        $limit = $limit > env('LIMIT') ? env('LIMIT') : $limit;
        $offset = $request->query('offset') ?? 0;
        $header = [
            'ID-Token' => $id_token,
            'Access-Token' => $access_token
        ];
        $params = [
            'limit' => $limit,
            'offset' => $offset,
        ];
        $page = ($offset / $limit) + 1;
        $result = Http::request(env('APP_URL'), '/api/admin', 'GET', $header, $params);
        $admins = json_decode($result['contents'], true);
        return view('admin.admins.index', compact('admins', 'page'));
    }

    public function action(Request $request)
    {
        $method = $request->input('method');
        $id_token = session('wuan.ID-Token');
        $access_token = session('wuan.Access-Token');

        $id = '';
        $email = '';

        if ('DELETE' == strtoupper($method)) {
            $id = $request->input('id');
        } else {
            $email = $request->input('email');
        }
        $params = [
            'email' => $email,
        ];
        $header = [
            'ID-Token' => $id_token,
            'Access-Token' => $access_token
        ];

        $result = Http::request(env('APP_URL'), '/api/admin/'. $id, $method, $header, $params);
        if ($result['status_code'] == 200 || $result['status_code'] == 204) {
            if ($result['status_code'] == 200) {
                $content = json_decode($result['contents'], true);
                return response(["code" => $result['status_code'], 'data' => $content, 'msg' => '操作成功']);
            }
            return response(["code" => $result['status_code'], 'msg' => '操作成功']);
        } else {
            $msg = json_decode($result['contents'], true)['error'];
            return response(["code" => $result['status_code'], 'msg' => $msg]);
        }
    }
}