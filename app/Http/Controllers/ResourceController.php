<?php

namespace App\Http\Controllers;

use App\Models\Movies\MoviesBase;
use App\Models\Resources\{
    Resource, ResourceTypeDetails, UnreviewedResources
};
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
                return response(['error' => 'Movie info does not exist'], 404);
            };
            $data = $request->all();
            $resource = Resource::where([
                'movies_id'   => $id,
                'resource_id' => $rid,
                'sharer'      => $request->get('id-token')->uid
            ]);
            if (!$resource->get()->count()) {
                return response(['error' => 'Illegal request'], 400);
            }
            $type = ResourceTypeDetails::where('type_name', $data['type'])->first();
            if (!$type) {
                return response(['error' => 'Wrong type of resource'], 422);
            }
            $type_id = $type->type_id;
            if ($resource->update([
                'resource_type' => $type_id,
                'title'         => $data['title'],
                'password'      => $data['password'] ?? '',
                'url'           => $data['url'],
                'instruction'   => $data['instruction'] ?? '无'
            ])) {
                return response([
                    'id'          => $rid,
                    'type'        => $data['type'],
                    'title'       => $data['title'],
                    'password'    => $data['password'] ?? '',
                    'url'         => $data['url'],
                    'instruction' => $data['instruction'] ?? '无',
                    'sharer'      => [
                        'id'   => $request->get('id-token')->uid,
                        'name' => $request->get('id-token')->uname
                    ]
                ]);
            } else {
                return response(['error' => 'Unknown mistake'], 400);
            }
        } catch (\Exception $e) {
            return response(['error' => 'Failed to edit resource: ' . $e->getMessage()], 400);
        }
    }

    /**
     * 验证请求参数
     * @param array $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        if ($data['type'] == '网盘') {
            return Validator::make($data, [
                'type'     => 'required',
                'title'    => 'required',
                'url'      => 'required',
                'password' => 'between:1,8',
            ]);
        } else {
            return Validator::make($data, [
                'type'  => 'required',
                'title' => 'required',
                'url'   => 'required',
            ]);
        }
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
                return response(['error' => 'Movie info does not exist'], 404);
            };

            $resource = Resource::where(
                [
                    'movies_id'   => $id,
                    'resource_id' => $rid,
                    'sharer'      => $request->get('id-token')->uid
                ]);
            if (!$resource->get()->count()) {
                return response(['error' => 'Illegal request'], 400);
            }
            if ($resource->delete()) {
                return response([], 204);
            } else {
                return response(['error' => 'Unknown mistake'], 400);
            }
        } catch (\Exception $e) {
            return response(['error' => 'Failed to delete resource: ' . $e->getMessage()], 400);
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
                return response(['error' => 'Movie info does not exist'], 404);
            };
            $data = $request->all();
            $type_cn = $data['type'];
            $data['type'] = ResourceTypeDetails::where('type_name', $data['type'])->first();
            if (!$data['type']) {
                return response(['error' => 'Wrong type of resource'], 422);
            }
            $data['type'] = $data['type']->type_id;
            $resource = $this->create($data, $id);
            $u_resource = new UnreviewedResources();
            $u_resource->resource_id = $resource->id;
            $u_resource->save();

            if ($res = $resource->save()) {

                return response([
                    'id'          => $resource->id,
                    'type'        => $type_cn,
                    'title'       => $data['title'],
                    'password'    => $data['password'] ?? '',
                    'url'         => $data['url'],
                    'instruction' => $data['instruction'],
                    'sharer'      => [
                        'id'   => $request->get('id-token')->uid,
                        'name' => $request->get('id-token')->uname
                    ]
                ]);
            } else {
                return response(['error' => 'Unknown mistake'], 400);
            }
        } catch (\Exception $e) {
            return response(['error' => 'Failed to add resource: ' . $e->getMessage()], 400);
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
            'movies_id'     => $id,
            'resource_type' => $data['type'],
            'title'         => $data['title'],
            'password'      => $data['password'] ?? '',
            'url'           => $data['url'],
            'instruction'   => $data['instruction'] ?? '无',
            'sharer'        => request()->get('id-token')->uid
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
                return response(['error' => 'Movie info does not exist'], 404);
            }
            $resources = Resource::where('movies_id', $movie_id)->get();
            foreach ($resources as $key => $resource) {
                $response = Builder::requestInnerApi(
                    env('OIDC_SERVER'),
                    "/api/app/users/{$resource->sharer}"
                );
                $user = json_decode($response['contents']);

                $created_at = $resource->created_at;
                $time = explode(' ', $created_at);
                $created_at = $time[0] . 'T' . $time[1] . 'Z';
                $res[] = [
                    'id'          => $resource->resource_id,
                    'type'        => ResourceTypeDetails::find($resource->resource_type)->type_name,
                    'title'       => $resource->title,
                    'instruction' => $resource->instruction,
                    'password'    => $resource->password,
                    'url'         => $resource->url,
                    'create_at'   => $created_at,
                    'sharer'      => [
                        'id'   => $user->id,
                        'name' => $user->name,
                    ]
                ];
            }
            return response(['resources' => $res ?? []], 200);
        } catch (\Exception $e) {
            return response(['error' => 'Failed to get resources: ' . $e->getMessage()], 400);
        }
    }
}
