<?php

namespace App\Http\Controllers;

use App\Model\Points\Points;
use App\Model\Resources\Resource;
use App\Model\Resources\UnreviewedResources;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class AdminsController extends Controller
{
    /**
     * 获取资源审核列表
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getUnreviewedResources(Request $request)
    {
        try {
            $resources = UnreviewedResources::with('resource.movie')->get();
            $res = [];
            foreach ($resources as $resource) {
                if (!$resource->resource) {
                    continue;
                }
                $client = new Client(['base_uri' => env('OIDC_SERVER')]);
                $response = $client->request(
                    'GET',
                    "/api/app/users/{$resource->resource->sharer}?" . Builder::queryToken(),
                    [
                        'headers' => [
                            'ID-Token' => $request->header('ID-Token'),
                            'Access-Token' => $request->header('Access-Token'),
                        ]
                    ])
                    ->getBody()
                    ->getContents();
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
     * @param Request $request
     * @param $resource_id
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function auditResource(Request $request, $resource_id)
    {
        $type = $request->input('action');
        $result = UnreviewedResources::where('resource_id', $resource_id)->first();
        if (!$result) {
            return response(['error' => '资源不存在'], 400);
        }
        DB::beginTransaction();
        try {
            switch ($type) {
                case 'good':
                    $user_id = Resource::where('resource_id', $resource_id)->first()->sharer;
                    Points::find($user_id)->increment('points', 1);
                    break;
                case 'pass':
                    break;
                case 'delete':
                    Resource::where('resource_id', $resource_id)->delete();
                    break;
                default:
                    throw new \Exception('错误的请求类型');
            }
            $result->delete();
            DB::commit();

            return response('操作成功', 204);
        } catch (\Exception $e) {
            DB::rollBack();
            return response(['error' => '审核失败' . $e->getMessage()], 400);
        }
    }
}
