<?php

namespace App\Http\Controllers\Movies;

use App\Resource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;

class ResourceController extends Controller
{

    /**
     * 编辑资源
     * @param Request $request
     * @param $id
     * @param $rid
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function edit(Request $request, $id, $rid)
    {
        $validator = $this->validator($request->all());
        if ($validator->fails()) {
            return response(['error' => $validator->errors()], 422);
        }
        $data = $request->all();
        if (Resource::where(['movies_id' => $id, 'resource_id' => $rid])->update([
            'resource_type' => $data['type'],
            'title' => $data['title'],
            'password' => $data['password'],
            'url' => $data['url'],
            'instruction' => $data['instruction'],
            'sharer' => 1,
        ])) {
            return response([
                'id' => $id,
                'type' => $data['type'],
                'title' => $data['title'],
                'password' => $data['password'],
                'url' => $data['url'],
                'instruction' => $data['instruction'],
                'sharer' => 1,
            ]);
        }
        return response("修改资源失败", 400);
    }

    /**
     * 删除资源
     * @param $id
     * @param $rid
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function delete($id, $rid)
    {

        if (Resource::where(['movies_id' => $id, 'resource_id' => $rid])->delete()) {
            return response([], 204);
        }
        return response(['error' => "删除失败"], 400);
    }

    /**
     * 增加资源
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

        if ($resource->save()) {

            return response([
                'id' => $id,
                'type' => $data['type'],
                'title' => $data['title'],
                'password' => $data['password'],
                'url' => $data['url'],
                'instruction' => $data['instruction'],
                'sharer' => 1,
            ]);
        } else {
            return response("添加资源失败", 400);
        }

    }

    protected function create(array $data, $id)
    {
        return Resource::create([
            'movies_id' => $id,
            'resource_id' => $id,
            'resource_type' => $data['type'],
            'title' => $data['title'],
            'password' => $data['password'],
            'url' => $data['url'],
            'instruction' => $data['instruction'],
            'sharer' => 1,
        ]);
    }

    protected function validator(array $data)
    {
        return Validator::make($data, [
            'type' => 'required',
            'title' => 'required',
            'password' => 'required',
            'url' => 'required',
            'instruction' => 'required',
        ]);
    }
}
