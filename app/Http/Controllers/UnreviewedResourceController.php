<?php

namespace App\Http\Controllers;

use App\Model\Movies\MoviesBase;
use App\Model\Resources\Resource;
use App\Model\Resources\UnreviewedResources;

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
                    break;
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
            echo $e->getMessage();exit;
            return response(['error' => '非法请求'], 400);
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
            $res = UnreviewedResources::where('resources_id', $resource_id)->delete();
            if ($res) {
                return response('操作成功', 204);
            } else {
                return response(['error' => '资源不存在'], 400);
            }
        } catch (\Exception $e) {
            return response(['error' => '非法请求'], 400);
        }
    }

    /**
     * 删除资源
     * @param $resource_id
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function deleteResource($resource_id)
    {
        try {
            $resource = UnreviewedResources::with('resource.movie')->where('resources_id', $resource_id)->first();
            // 检测该影片是否存在于数据库中
            if (!MoviesBase::where('id', $resource->resource->movies_id)->first()) {
                throw new \Exception('影片信息不存在');
            };

            $resource = Resource::where(
                [
                    'movies_id' => $resource->resource->movies_id,
                    'resource_id' => $resource_id,
                    'sharer' => $resource->resource->sharer
                ]);
            if (!$resource->get()->count()) {
                throw new \Exception('资源不存在');
            }
            if ($resource->delete()) {
                return response([], 204);
            }
        } catch (\Exception $e) {
            return response(['error' => '非法请求'], 400);
        }
    }
}
