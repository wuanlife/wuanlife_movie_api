<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ResourcesController extends Controller
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
        $result = Http::request(env('APP_URL'), '/api/resources/background', 'GET', $header, $params);
        $resources = json_decode($result['contents'], true);
        return view('admin.resources.index', compact('resources', 'page', 'max_page'));
    }

    public function auditResource(Request $request)
    {
        $action = $request->input('action');
        $resource_id = $request->input('resource_id');
        $id_token = session('wuan.ID-Token');
        $access_token = session('wuan.Access-Token');
        $action == 'delete' ? $method = 'DELETE' : $method = 'POST';
        $params = [
            'action' => $action,
        ];
        $header = [
            'ID-Token' => $id_token,
            'Access-Token' => $access_token
        ];

        $result = Http::request(env('APP_URL'), '/api/resources/'. $resource_id .'/background', $method, $header, $params);
        if ($result['status_code'] != 204) {
            return response(["code" => $result['status_code'], 'msg' => $result['error']]);
        } else {
            return response(["code" => $result['status_code'], 'msg' => '操作成功']);
        }
    }
}