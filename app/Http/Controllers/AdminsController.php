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
use Illuminate\Support\Facades\Log;
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
        $limit = $request->input('limit') ?? env('LIMIT');
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
                $url = '链接：<a target="_blank" href=' . $resource->resource->url . '>资源链接</a>；';
                $password = '密码：' . $resource->resource->password;
                $res[] = [
                    'movie_id'    => $resource->resource->movie->title,
                    'resource_id' => $resource->resource->resource_id,
                    'name'        => $resource->resource->movie->title,
                    'instruction' => $type . $title . $instruction . $url . $password,
                    'sharer'      => $user->name,
                    'created_at'  => $created_at,
                ];
            }
            return response(['resources' => $res ?? [], 'total' => $resources->total()], 200);
        } catch (\Exception $e) {
            Log::error(
                "'Failed to get un-reviewed resource:{$e->getMessage()}.In " . __METHOD__ . " on line {$e->getLine()}",
                [
                    'offset' => $offset,
                    'limit'  => $limit
                ]);
            return response(['error' => '获取待审核资源列表失败'], 400);
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
            return response(['error' => '资源不存在'], 404);
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
                    return response(['error' => '非法请求，错误的审核类型'], 400);
            }
            $unrev_result->delete();
            DB::commit();

            return response([], 204);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error(
                "Failed to audit:{$e->getMessage()}.In " . __METHOD__ . " on line {$e->getLine()}",
                [
                    'type'        => $type,
                    'resource_id' => $resource_id,
                ]);
            return response(['error' => '审核失败'], 400);
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
            return response(['error' => '指定的资源不存在'], 404);
        }
        DB::beginTransaction();
        try {
            Resource::where('resource_id', $resource_id)->delete();
            $unrev_result->delete();
            DB::commit();

            return response([], 204);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error(
                "Failed to delete:{$e->getMessage()}.In " . __METHOD__ . " on line {$e->getLine()}",
                [
                    'resource_id' => $resource_id,
                ]);
            return response(['error' => '无法删除指定资源'], 400);
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
            $validator = Validator::make($request->all(),
                [
                    'email' => 'required|email'
                ],
                [
                    'email.require' => '邮箱地址不能为空',
                    'email.email'   => '请输入正确的邮箱地址',
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

            if ($response['status_code'] !== 200) {
                return response(['error' => json_decode($response['contents'])->error], 400);
            }

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

            $response = Builder::requestInnerApi(
                env('OIDC_SERVER'),
                "/api/app/users/{$id}"
            );
            $user_info = json_decode($response['contents']);
            if ($res) {
                return response([
                    'id'         => $user_info->id,
                    'name'       => $user_info->name,
                    'avatar_url' => $user_info->avatar_url,
                ], 200);
            } else {
                return response(['error' => '添加管理员失败'], 400);
            }
        } catch (\Exception $e) {
            Log::error(
                "Failed to add admin:{$e->getMessage()}.In " . __METHOD__ . " on line {$e->getLine()}",
                [
                    'email' => $email ?? 'null',
                ]);
            return response(['error' => '添加管理员失败'], 400);
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
            'id'   => $id,
            'auth' => $auth,
        ])->delete();

        if ($res) {
            return response([], 204);
        } else {
            return response(['error' => '非法请求'], 400);
        }
    }

    /**
     * 获取管理员列表
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function listAdmin(Request $request)
    {
        $limit = $request->input('limit') ?? env('LIMIT');
        $offset = $request->input('offset') ?? 0;
        $page = ($offset / $limit) + 1;
        try {
            $users = UsersAuth::with([
                'detail' => function ($query) {
                    $query->where('identity', '管理员');
                }
            ])->paginate($limit, ['*'], '', $page);
            $res = [];
            foreach ($users as $user) {
                if (!$user->detail) {
                    continue;
                }
                $response = Builder::requestInnerApi(
                    env('OIDC_SERVER'), "/api/app/users/{$user->id}");

                $user_info = json_decode($response['contents']);
                $res[] = [
                    'id'   => $user->id,
                    'name' => $user_info->name,
                ];
            }
            return response(['admins' => $res ?? [], 'total' => $users->total()], 200);
        } catch (\Exception $e) {
            Log::error(
                "Failed to get admins list:{$e->getMessage()}.In " . __METHOD__ . " on line {$e->getLine()}",
                [
                    'limit'  => $limit,
                    'offset' => $offset,
                ]);
            return response(['error' => '获取管理员列表错误'], 400);
        }
    }
}
