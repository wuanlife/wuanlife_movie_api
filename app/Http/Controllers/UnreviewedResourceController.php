<?php

namespace App\Http\Controllers;

use App\Model\Resources\Resource;
use App\Model\Resources\UnreviewedResources;
use Illuminate\Support\Facades\DB;

class UnreviewedResourceController extends Controller
{
    /**
     * 获取资源审核列表
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function index()
    {
        try {
            $resources = UnreviewedResources::with('resource.movie')->get();
            $res = [];
            foreach ($resources as $resource) {
                if (!$resource->resource) {
                    continue;
                }
                $api_url = env('OIDC_SERVER_GET_USER_INFO_API') . '/' . $resource->resource->sharer;
                $response = file_get_contents($api_url);
                $user = json_decode($response);
                $created_at = $resource->resource->created_at;
                $time = explode(' ', $created_at);
                $created_at = $time[0] . 'T' . $time[1] . 'Z';
                $res[] = [
                    'movie_id' => $resource->resource->movies_id,
                    'resource_id' => $resource->resource->resource_id,
                    'name' => $resource->resource->title,
                    'instruction' => $resource->resource->instruction,
                    'sharer' => $user->name,
                    'create_at' => $created_at,
                ];
            }
            return response(['resources' => $res ?? []], 200);
        } catch (\Exception $e) {
            return response(['error' => '获取待审核资源失败' . $e->getMessage()], 400);
        }
    }


    /**
     * 审核资源
     * @param $resource_id
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function review($resource_id)
    {
        try {
            $result = UnreviewedResources::where('resources_id', $resource_id)->get();
            if (($result->isEmpty())) {
                return response(['error' => '资源不存在'], 400);
            }
            $result->delete();
            return response('操作成功', 204);
        } catch (\Exception $e) {
            return response(['error' => '审核失败' . $e->getMessage()], 400);
        }
    }

    /**
     * 删除资源
     * @param $resource_id
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function deleteResource($resource_id)
    {
        DB::beginTransaction();
        try {
            UnreviewedResources::where('resources_id', $resource_id)->delete();
            Resource::where('resource_id', $resource_id)->delete();
            DB::commit();
            return response([], 204);
        } catch (\Exception $e) {
            DB::rollBack();
            return response(['error' => '删除失败' . $e->getMessage()], 400);
        }
    }
}
