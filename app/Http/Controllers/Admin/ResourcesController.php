<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Builder;
use App\Models\Resources\ResourceTypeDetails;
use App\Models\Resources\UnreviewedResources;
use Illuminate\Http\Request;

class ResourcesController extends BaseController
{
    public function index(Request $request)
    {
        if (!Verify::VerifyToken($request)) {
            session()->flash('danger', 'Permission verification failed');
            return redirect(route('admin.login'));
        }


        $id_token = session('wuan.ID-Token');
        $user_info = json_decode(base64_decode(explode('.', $id_token)[1]));
        $user_id = $user_info->uid;
        if (!Verify::VerifyAdmin($user_id))
        {
            session()->flash('danger', 'Insufficient permissions,need administrator rights');
            return redirect(route('admin.login'));
        }

        $limit = $request->input('limit') ?? env('LIMIT');
        $page = $request->input('page');
            try {
            $resources = UnreviewedResources::with('resource.movie')->paginate($limit, ['*'], 'page', $page);
            foreach ($resources as $resource) {
                if (!$resource->resource) {
                    continue;
                }
                $response = Builder::requestInnerApi(
                    env('OIDC_SERVER'),
                    "/api/app/users/{$resource->resource->sharer}"
                );
                $user = json_decode($response['contents']);
                $created_at = $resource->resource->created_at;
                $time = explode(' ', $created_at);
                $created_at = $time[0] . 'T' . $time[1] . 'Z';
                $type = '【' . ResourceTypeDetails::find($resource->resource->resource_type)->type_name . '】';
                $title = $resource->resource->title . '<br>';
                $instruction = '说明：' . $resource->resource->instruction . '<br>';
                $url = '链接：<a href=' . $resource->resource->url . '>资源链接</a>；';
                $password = '密码：' . $resource->resource->password;


                $resource->movie_id = $resource->resource->movie->id;
                $resource->resource_id = $resource->resource->resource_id;
                $resource->name = $resource->resource->movie->title;
                $resource->instruction = $type . $title . $instruction . $url . $password;
                $resource->sharer = $user->name;
                $resource->created_at = $created_at;
            }

        } catch (\Exception $e) {
            session()->flash('danger', 'Failed to get unreviewed resource');
            $resources = [];
        }

        return view('admin.resources.index', compact('resources'));
    }
}