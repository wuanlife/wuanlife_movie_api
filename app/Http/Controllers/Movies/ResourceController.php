<?php

namespace App\Http\Controllers\Movies;

use App\Resource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;

class ResourceController extends Controller
{

    /**
     * 编辑资源接口
     * @param Request $request
     * @param $id
     * @param $rid
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function edit($id, $rid, Request $request)
    {
        $validator = $this->validator($request->all());
        if ($validator->fails()) {
            return response(['error' => $validator->errors()], 422);
        }
        $data = $request->all();
        $resource = Resource::where([
            'movies_id' => $id,
            'resource_id' => $rid,
            'sharer' => $request->get('id-token')->id
        ]);
        if (!$resource->get()->count()) {
            return response(['error' => '非法请求，修改失败'], 400);
        }
        if ($resource->update([
            'resource_type' => $data['type'],
            'title' => $data['title'],
            'password' => $data['password'],
            'url' => $data['url'],
            'instruction' => $data['instruction']
        ])) {
            return response([
                'id' => $rid,
                'type' => $data['type'],
                'title' => $data['title'],
                'password' => $data['password'],
                'url' => $data['url'],
                'instruction' => $data['instruction'],
                'sharer' => [
                    'id' => $request->get('id-token')->id,
                    'name' => $request->get('id-token')->name
                ]
            ]);
        }
        return response("修改资源失败", 400);
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
        $resource = Resource::where(
            [
                'movies_id' => $id,
                'resource_id' => $rid,
                'sharer' => $request->get('id-toen')->id
            ]);
        if (!$resource->get()->count()) {
            return response(['error' => '非法请求，删除失败'], 400);
        }
        if ($resource->delete()) {
            return response([], 204);
        }
        return response(['error' => "删除失败"], 400);
    }

    /**
     * 增加资源接口
     * @param Request $request
     * @param $id
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function add(Request $request, $id)
    {

        $validator = $this->validator($request->all());
        if ($validator->fails()) {
            return response(['error' => $validator->errors()], 422);
        }
        $data = $request->all();
        $resource = $this->create($data, $id);

        if ($res = $resource->save()) {

            return response([
                'id' => $resource->id,
                'type' => $data['type'],
                'title' => $data['title'],
                'password' => $data['password'] ?? 'null',
                'url' => $data['url'],
                'instruction' => $data['instruction'],
                'sharer' => [
                    'id' => $request->get('id-token')->id,
                    'name' => $request->get('id-token')->name
                ]
            ]);
        } else {
            return response("添加资源失败", 400);
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
            'instruction' => $data['instruction'],
            'sharer' => request()->get('id-token')->id
        ]);
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
            'instruction' => 'required',
        ]);
    }
}
