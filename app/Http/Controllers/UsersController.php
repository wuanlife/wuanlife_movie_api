<?php
/**
 * Created by PhpStorm.
 * User: csc
 * Date: 2018/5/4
 * Time: 22:00
 */

namespace App\Http\Controllers;


use App\Model\Points\Points;
use App\Model\Points\PointsOrder;
use GuzzleHttp\Client;
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
                throw new \Exception('非法请求，用户ID与令牌ID不符', 400);
            }
            $user = Points::find($id_token->uid);

            // 如果用户积分不存在，则初始化用户积分到积分表，并返回0
            if (!$user) {
                Points::create([
                    'user_id' => $id_token->uid,
                    'points' => 0
                ]);

                return response(['id' => $id_token->uid, 'point' => 0,], 200);
            }

            return response(['id' => $user->user_id, 'point' => $user->points], 200);
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
                throw new \Exception('非法请求，用户ID与令牌ID不符', 400);
            }
            if (!is_numeric($sub_point)) {
                throw new \Exception('非法请求，sub_point必须是数字', 400);
            }

            DB::transaction(function () use ($request, $id, $sub_point, $id_token) {
                $movie_points = Points::find($id_token->uid)->points;
                if ($movie_points - 4 * $sub_point < 0) {
                    throw new \Exception('积分不足,兑换失败');
                }
                Points::find($id_token->uid)->decrement('points', 4 * $sub_point);
                PointsOrder::create([
                    'user_id' => $id,
                    'points_alert' => -4 * $sub_point,
                ]);
                $client = new Client(['base_uri' => env('OIDC_SERVER')]);
                $res = $client->request(
                    'PUT',
                    "/api/app/users/{$id}/point?" . Builder::queryToken(),
                    [
                        'headers' => [
                            'ID-Token' => $request->header('ID-Token'),
                            'Access-Token' => $request->header('Access-Token'),
                        ],
                        'json' => [
                            'sub_point' => $sub_point,
                        ]
                    ]);
            });

            return response([], 204);
        } catch (\Exception $exception) {

            return response(['error' => $exception->getMessage()], 400);
        }
    }

    /**
     * 获取午安积分
     * @param $id
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getWuanPoint($id, Request $request)
    {
        $client = new Client(['base_uri' => env('OIDC_SERVER')]);
        try {
            $res = $client->request(
                'GET',
                "/api/app/users/{$id}/point?" . Builder::queryToken(),
                [
                    'headers' => [
                        'ID-Token' => $request->header('ID-Token'),
                        'Access-Token' => $request->header('Access-Token'),
                    ]
                ]);

            return response(json_decode($res->getBody()->getContents(), true));
        } catch (GuzzleException $e) {

            return response(['error' => '权限验证失败:' . $e->getMessage()], 400);
        }
    }

}