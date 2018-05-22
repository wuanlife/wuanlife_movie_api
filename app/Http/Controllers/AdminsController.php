<?php

namespace App\Http\Controllers;

use App\Models\Points\Points;
use App\Models\Resources\Resource;
use App\Models\Resources\UnreviewedResources;
use App\Models\Users\UsersAuth;
use App\Models\Users\UsersAuthDetail;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;


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
                $response = Builder::requestInnerApi("/api/app/users/{$resource->resource->sharer}");
                $user = json_decode($response['contents']);
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
            return response(['error' => '获取待审核资源失败: ' . $e->getMessage()], 400);
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

    /**
     * 增加管理员
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function addAdmin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email'
        ]);
        if ($validator->fails()) {
            return response(['error' => $validator->errors()->first()], 422);
        }

        $email = $request->input('email');

        // 使用 email 获取用户 id
        $response = Builder::requestInnerApi("/api/app/users/email/{$email}");
        $id = json_decode($response['contents'])->id;
        $auth = UsersAuthDetail::where('identity', '管理员')->first()->id;

        // 验证该用户是否已是管理员
        $user = new UsersAuth();
        if ($user->where([
            'id' => $id,
        ])->first()
        ) {
            return response(['error' => '该用户已是管理员或最高管理员'], 400);
        }

        // 添加管理员
        $user->id = $id;
        $user->auth = $auth;
        $res = $user->save();

        $response = Builder::requestInnerApi("/api/app/users/{$id}");
        $user_info = json_decode($response['contents']);
        if ($res) {
            return response([
                'id' => $user_info->id,
                'name' => $user_info->name,
                'avatar_url' => $user_info->avatar_url,
            ], 200);
        } else {
            return response(['error' => '添加失败'], 400);
        }
    }

    /**
     * 删除管理员
     * @param $id
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function deleteAdmin($id)
    {
        $auth = UsersAuthDetail::where('identity', '管理员')->first()->id;

        $res = UsersAuth::where([
            'id' => $id,
            'auth' => $auth,
        ])->delete();
        if ($res) {
            return response([], 204);
        } else {
            return response(['error' => "非法请求"], 400);
        }
    }

    /**
     * 获取管理员列表
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function listAdmin()
    {
        $auth = UsersAuthDetail::where('identity', '管理员')->first()->id;

        $users['admins'] = UsersAuth::where('auth',$auth)->get();
        foreach ($users['admins'] as $key => $value) {
            $id = $value->id;
            $response = Builder::requestInnerApi("/api/app/users/{$id}");
            $user_info = json_decode($response['contents']);
            $users['admins'][$key]['name'] = $user_info->name;
            unset($users['admins'][$key]['auth']);
        }

        return response($users, 200);
    }
}
