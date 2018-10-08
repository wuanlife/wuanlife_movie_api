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
        $limit = $request->query('limit') ?? 20;
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
}