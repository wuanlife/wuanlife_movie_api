<?php

namespace App\Http\Controllers;

use App\Model\Movies\MoviesBase;
use App\Model\Resources\{
    Resource, ResourceTypeDetails, UnreviewedResources
};
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ResourceController extends Controller
{

    /**
     * 编辑资源接口
     * @param $id
     * @param $rid
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function edit($id, $rid, Request $request)
    {
        try {
            $validator = $this->validator($request->all());
            if ($validator->fails()) {
                return response(['error' => $validator->errors()], 422);
            }
            // 检测该影片是否存在于数据库中
            if (!MoviesBase::where('id', $id)->first()) {
                throw new \Exception('影片信息不存在');
            };
            $data = $request->all();
            $resource = Resource::where([
                'movies_id' => $id,
                'resource_id' => $rid,
                'sharer' => $request->get('id-token')->uid
            ]);
            if (!$resource->get()->count()) {
                throw new \Exception('非法请求');
            }
            $type = ResourceTypeDetails::where('type_name', $data['type'])->first();
            if (!$type) {
                throw new \Exception('错误的资源类型', 422);
            }
            $type_id = $type->type_id;
            if ($resource->update([
                'resource_type' => $type_id,
                'title' => $data['title'],
                'password' => $data['password'],
                'url' => $data['url'],
                'instruction' => $data['instruction'] ?? '无'
            ])) {
                return response([
                    'id' => $rid,
                    'type' => $data['type'],
                    'title' => $data['title'],
                    'password' => $data['password'],
                    'url' => $data['url'],
                    'instruction' => $data['instruction'] ?? '无',
                    'sharer' => [
                        'id' => $request->get('id-token')->uid,
                        'name' => $request->get('id-token')->uname
                    ]
                ]);
            }
        } catch (\Exception $e) {
            return response(['error' => '编辑资源失败：' . $e->getMessage()], 400);
        }
    }

    /**
     * 验证请求参数
     * @param array $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'type' => 'required',
            'title' => 'required',
            'url' => 'required',
        ]);
    }

    /**
     * 删除资源接口
     * @param $id
     * @param $rid
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function delete($id, $rid, Request $request)
    {
        try {
            // 检测该影片是否存在于数据库中
            if (!MoviesBase::where('id', $id)->first()) {
                throw new \Exception('影片信息不存在');
            };

            $resource = Resource::where(
                [
                    'movies_id' => $id,
                    'resource_id' => $rid,
                    'sharer' => $request->get('id-token')->uid
                ]);
            if (!$resource->get()->count()) {
                throw new \Exception('非法请求');
            }
            if ($resource->delete()) {
                return response([], 204);
            }
        } catch (\Exception $e) {
            return response(['error' => '删除资源失败：' . $e->getMessage()], 400);
        }
    }

    /**
     * 增加资源接口
     * @param Request $request
     * @param $id
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function add(Request $request, $id)
    {

        try {
            $validator = $this->validator($request->all());
            if ($validator->fails()) {
                throw new \Exception($validator->errors(), 422);
            }
            // 检测该影片是否存在于数据库中
            if (!MoviesBase::where('id', $id)->first()) {
                throw new \Exception('影片信息不存在');
            };
            $data = $request->all();
            $type_cn = $data['type'];
            $data['type'] = ResourceTypeDetails::where('type_name', $data['type'])->first();
            if (!$data['type']) {
                throw new \Exception('错误的资源类型', 422);
            }
            $data['type'] = $data['type']->type_id;
            $resource = $this->create($data, $id);
            $u_resource = new UnreviewedResources();
            $u_resource->resources_id = $resource->id;
            $u_resource->save();

            if ($res = $resource->save()) {

                return response([
                    'id' => $resource->id,
                    'type' => $type_cn,
                    'title' => $data['title'],
                    'password' => $data['password'] ?? 'null',
                    'url' => $data['url'],
                    'instruction' => $data['instruction'],
                    'sharer' => [
                        'id' => $request->get('id-token')->uid,
                        'name' => $request->get('id-token')->uname
                    ]
                ]);
            } else {
                throw new \Exception('未知错误', 400);
            }
        } catch (\Exception $e) {
            return response(['error' => '添加资源失败：' . $e->getMessage()], 400);
        }

    }

    /**
     * 使用 create 方法创建资源
     * @param array $data
     * @param $id
     * @return mixed
     */
    protected function create(array $data, $id)
    {
        return Resource::create([
            'movies_id' => $id,
            'resource_type' => $data['type'],
            'title' => $data['title'],
            'password' => $data['password'],
            'url' => $data['url'],
            'instruction' => $data['instruction'] ?? '无',
            'sharer' => request()->get('id-token')->uid
        ]);
    }

    /**
     * 显示资源接口
     * @param $movie_id
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function showResources($movie_id)
    {
        try {
            if (!MoviesBase::find($movie_id)) {
                throw new \Exception('电影信息不存在', 400);
            }
            $resources = Resource::where('movies_id', $movie_id)->get();
            foreach ($resources as $key => $resource) {
                if (UnreviewedResources::find($resources[$key]['resource_id'])){
                    unset($resources->$key);
                    continue;
                }
                $client = new Client(['base_uri' => env('OIDC_SERVER')]);
                $response = $client->request(
                    'GET',
                    "/api/app/users/{$resource->sharer}?" . Builder::queryToken(),
                    []
                )
                    ->getBody()
                    ->getContents();
                $user = json_decode($response);
                $created_at = $resource->created_at;
                $time = explode(' ', $created_at);
                $created_at = $time[0] . 'T' . $time[1] . 'Z';
                $res[] = [
                    'id' => $resource->resource_id,
                    'type' => ResourceTypeDetails::find($resource->resource_type)->type_name,
                    'title' => $resource->title,
                    'instruction' => $resource->instruction,
                    'url' => $resource->url,
                    'create_at' => $created_at,
                    'sharer' => [
                        'id' => $user->id,
                        'name' => $user->name,
                    ]
                ];
            }
            return response(['resources' => $res ?? []], 200);
        } catch (\Exception $e) {
            return response(['error' => '获取资源失败：' . $e->getMessage()], 400);
        }
    }
}
