<?php

namespace App\Http\Api\Movies;

use App\Resource;
use Dingo\Api\Exception\StoreResourceFailedException;
use Dingo\Api\Routing\Helpers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;

class ResourceController extends Controller
{
    use Helpers;

    public function edit(Request $request, $id, $rid){

        $validator = $this->validator($request->all());
        if($validator->fails()){
            throw new StoreResourceFailedException("Validation Error", $validator->errors());
        }
        $data = $request->all();
        if(Resource::where(['movies_id'=>$id,'resource_id'=>$rid])->update([
                'resource_type' => $data['type'],
                'title' => $data['title'],
                'password' => $data['password'],
                'url' => $data['url'],
                'instruction' => $data['instructions'],
                'sharer'    => 1,
            ]))
            return $this->response->array([
                'id' => $id,
                'type' => $data['type'],
                'title' => $data['title'],
                'password' => $data['password'],
                'url' => $data['url'],
                'instructions' => $data['instructions'],
                'sharer'    => 1,
            ]);
        return $this->response->error("修改资源失败", 400);
    }
    public function delete($id, $rid){

        if(Resource::where(['movies_id'=>$id,'resource_id'=>$rid])->delete())
        return $this->response->noContent();
        return $this->response->error("删除失败", 400);
    }
    public function add(Request $request, $id){

        $validator = $this->validator($request->all());
        if($validator->fails()){
            throw new StoreResourceFailedException("Validation Error", $validator->errors());
        }
        $data = $request->all();
        $resource = $this->create($data, $id);

        if($resource->save()){

            return $this->response->array([
                'id' => $id,
                'type' => $data['type'],
                'title' => $data['title'],
                'password' => $data['password'],
                'url' => $data['url'],
                'instructions' => $data['instructions'],
                'sharer'    => 1,
                'create_at' => date('Y-m-d H:i:s'),
            ]);
        }else{
            return $this->response->error("添加资源失败", 400);
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
            'instruction' => $data['instructions'],
            'sharer'    => 1,
            'create_at' => date('Y-m-d H:i:s'),
        ]);
    }

    protected function validator(array $data)
    {
        return Validator::make($data, [
            'type' => 'required',
            'title' => 'required',
            'password' => 'required',
            'url' => 'required',
            'instructions' => 'required',
        ]);
    }
}
