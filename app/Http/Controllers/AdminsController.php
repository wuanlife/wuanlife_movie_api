<?php

namespace App\Http\Controllers;

use App\Models\Points\Points;
use App\Models\Resources\Resource;
use App\Models\Resources\ResourceTypeDetails;
use App\Models\Resources\UnreviewedResources;
use App\Models\Users\UsersAuth;
use App\Models\Users\UsersAuthDetail;
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
        $limit = $request->input('limit') ?? 20;
        $offset = $request->input('offset') ?? 0;

        $page = ($offset / $limit) + 1;
        try {
            $resources = UnreviewedResources::with('resource.movie')->paginate($limit, ['*'], '', $page);
            $res = [];
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
                $res[] = [
                    'movie_id' => $resource->resource->movie->title,
                    'resource_id' => $resource->resource->resource_id,
                    'name' => $resource->resource->movie->title,
                    'instruction' => $type . $title . $instruction . $url . $password,
                    'sharer' => $user->name,
                    'created_at' => $created_at,
                ];
            }
            return response(['resources' => $res ?? []], 200);
        } catch (\Exception $e) {
            return response(['error' => 'Failed to get unreviewed resource:  ' . $e->getMessage()], 400);
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
        $unrev_result = UnreviewedResources::where('resource_id', $resource_id)->first();
        if (!$unrev_result) {
            return response(['error' => 'The resource not found'], 400);
        }
        DB::beginTransaction();
        try {
            switch ($type) {
                case 'award':
                    $user_id = Resource::where('resource_id', $resource_id)->first()->sharer;
                    Points::find($user_id)->increment('points', 1);
                    break;
                case 'pass':
                    break;
                default:
                    throw new \Exception('Wrong enum type');
            }
            $unrev_result->delete();
            DB::commit();

            return response([], 204);
        } catch (\Exception $e) {
            DB::rollBack();
            return response(['error' => 'Failed to audit: ' . $e->getMessage()], 400);
        }
    }

    /**
     * 删除资源
     * @param $resource_id
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function rejectResource($resource_id)
    {
        $unrev_result = UnreviewedResources::where('resource_id', $resource_id)->first();
        if (!$unrev_result) {
            return response(['error' => 'The resource not found'], 400);
        }
        DB::beginTransaction();
        try {
            Resource::where('resource_id', $resource_id)->delete();
            $unrev_result->delete();
            DB::commit();

            return response([], 204);
        } catch (\Exception $e) {
            DB::rollback();
            return response(['error' => 'Failed to delete: '], 400);
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
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email'
            ]);
            if ($validator->fails()) {
                return response(['error' => $validator->errors()->first()], 422);
            }

            $email = $request->input('email');

            // 使用 email 获取用户 id
            $response = Builder::requestInnerApi(
                env('OIDC_SERVER'),
                "/api/app/users/email/{$email}"
            );
            $id = json_decode($response['contents'])->id;
            $auth = UsersAuthDetail::where('identity', '管理员')->first()->id;

            // 验证该用户是否已是管理员
            $user = new UsersAuth();
            if ($user->where([
                'id' => $id,
            ])->first()
            ) {
                return response(['error' => 'The user is already an admin or top admin'], 400);
            }

            // 添加管理员
            $user->id = $id;
            $user->auth = $auth;
            $res = $user->save();

            $response = Builder::requestInnerApi(
                env('OIDC_SERVER'),
                "/api/app/users/{$id}"
            );
            $user_info = json_decode($response['contents']);
            if ($res) {
                return response([
                    'id' => $user_info->id,
                    'name' => $user_info->name,
                    'avatar_url' => $user_info->avatar_url,
                ], 200);
            } else {
                return response(['error' => 'Failed to add admin'], 400);
            }
        } catch (\Exception $e) {
            return response(['error' => 'Failed to add admin: ' . $e->getMessage()], 400);
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
            return response(['error' => 'Illegal request'], 400);
        }
    }

    /**
     * 获取管理员列表
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function listAdmin()
    {    
    try {
            $users = UsersAuth::with([
                'detail' => function ($query) {
                    $query->where('identity', '管理员');
                }
            ])->get();
            $res = [];
            foreach ($users as $user) {
                if (!$user->detail) {
                    continue;
                }
                $response = Builder::requestInnerApi(
                    env('OIDC_SERVER'), "/api/app/users/{$user->id}");

                $user_info = json_decode($response['contents']);
                $res[] = [
                    'id' => $user->id,
                    'name' => $user_info->name,
                ];
            }
            return response(['admins' => $res ?? []], 200);
        } catch (\Exception $e) {
            return response(['error' => 'Failed to get admins list'], 400);
        }
    }
}
