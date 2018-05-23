<?php
/**
 * Created by PhpStorm.
 * User: csc
 * Date: 2018/5/4
 * Time: 22:00
 */

namespace App\Http\Controllers;


use App\Models\Points\Points;
use App\Models\Points\PointsOrder;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UsersController extends Controller
{
    /**
     * 获取影视积分
     * @param $id
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getMoviePoint($id, Request $request)
    {
        $id_token = $request->get('id-token');
        try {
            if ($id != $id_token->uid) {
                return response(['error' => 'Illegal request,user id do not match token'], 403);
            }
            $user = Points::find($id_token->uid);

            // 如果用户积分不存在，则初始化用户积分到积分表，并返回0
            if (!$user) {
                Points::create([
                    'user_id' => $id_token->uid,
                    'points' => 0
                ]);

                return response(['id' => $id_token->uid, 'points' => 0,], 200);
            }

            return response(['id' => $user->user_id, 'points' => $user->points], 200);
        } catch (\Exception $exception) {
            return response(['error' => $exception->getMessage()], 400);
        }
    }

    /**
     * 兑换积分
     * @param $id
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function redeemWuanPoint($id, Request $request)
    {
        try {
            $id_token = $request->get('id-token');
            $sub_point = $request->input('sub_point');
            if ($id != $id_token->uid) {
                return response(['error' => 'Illegal request,user id do not match token'], 403);
            }
            if (!is_numeric($sub_point)) {
                return response(['error' => 'Illegal request,sub_point must be a number'], 422);
            }

            // 如果用户积分不存在，则初始化用户积分到积分表
            $user = Points::find($id_token->uid);
            if (!$user) {
                Points::create([
                    'user_id' => $id_token->uid,
                    'points' => 0
                ]);
            }

            DB::transaction(function () use ($request, $id, $sub_point, $id_token) {
                $movie_points = Points::find($id_token->uid)->points;
                if ($movie_points - 4 * $sub_point < 0) {
                    return response(['error' => 'Redemption failed:Insufficient points'], 400);
                }
                Points::find($id_token->uid)->decrement('points', 4 * $sub_point);
                PointsOrder::create([
                    'user_id' => $id,
                    'points_alert' => -4 * $sub_point,
                ]);
                $response = Builder::requestInnerApi("/api/app/users/{$id}/point", 'PUT',
                    [
                        'ID-Token' => $request->header('ID-Token'),
                        'Access-Token' => $request->header('Access-Token'),
                    ],
                    [
                        'sub_point' => $sub_point,
                    ]
                );

                if ($response['status_code'] != 204) {
                    return response(['error' => 'Failed to Redeem points']);
                } else {
                    return response([], 204);
                }
            });
            return response([], 204);
        } catch (\Exception $exception) {
            return response(['error' => $exception->getMessage()], 400);
        }
    }

    /**
     * 获取午安积分
     * @param $id
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getWuanPoint($id)
    {
        try {
            $response = Builder::requestInnerApi("/api/app/users/{$id}/point", 'GET'
            );

            return response(json_decode($response['contents'], true));
        } catch (GuzzleException $e) {

            return response(['error' => 'Permission verification failed: ' . $e->getMessage()], 400);
        }
    }

}