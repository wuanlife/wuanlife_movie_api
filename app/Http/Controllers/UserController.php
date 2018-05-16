<?php
/**
 * Created by PhpStorm.
 * User: csc
 * Date: 2018/5/4
 * Time: 22:00
 */

namespace App\Http\Controllers;


use App\Model\Point;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function getMoviePoint($id, Request $request)
    {
        $id_token = $request->get('id-token');
        try {
            if ($id != $id_token->uid) {
                throw new \Exception('非法请求，用户ID与令牌ID不符', 400);
            }
            $user = Point::find($id_token->uid);
            return response([
                'id'    =>$user['user_id'],
                'point' =>$user['point']
            ], 200);
        } catch (\Exception $exception) {
            if ($exception->getCode() <= 300 || $exception->getCode() > 510) {
                return response(['error' => $exception->getMessage()], 400);
            } else {
                return response(['error' => $exception->getMessage()], $exception->getCode());
            }
        }
    }

    public function redeemWuanPoint($id, Request $request)
    {
        $id_token = $request->get('id-token');
        $sub_point = $request->get('sub_point');
        try{
            if ($id != $id_token->uid) {
                throw new \Exception('非法请求，用户ID与令牌ID不符', 400);
            }
            DB::transaction(function () use ($request, $id, $sub_point, $id_token) {
                Point::find($id_token->uid)->decrement('point', 4*$sub_point);
                $client = new Client(['base_uri' => env('OIDC_SERVER')]);
                $res = $client->request(
                    'PUT',
                    "/api/users/{$id}/point",
                    [
                        'headers' => [
                            'ID-Token' => $request->header('ID-Token'),
                            'Access-Token' => $request->header('Access-Token'),
                        ],
                        'json'    => [
                            'sub_point' =>$sub_point,
                            'secret' => Crypt::encrypt($id_token)
                        ]
                    ]);
            });

        }
        catch (\Exception $exception){
            if ($exception->getCode() <= 300 || $exception->getCode() > 510) {
                return response(['error' => $exception->getMessage()], 400);
            } else {
                return response(['error' => $exception->getMessage()], $exception->getCode());
            }
        }
    }
    public function getWuanPoint($id, Request $request)
    {
        $client = new Client(['base_uri' => env('OIDC_SERVER')]);
        try {
            $res = $client->request(
                'GET',
                "/api/users/{$id}/point",
                [
                    'headers' => [
                        'ID-Token' => $request->header('ID-Token'),
                        'Access-Token' => $request->header('Access-Token'),
                    ]
                ]);
        } catch (GuzzleException $e) {
            return response(['error' => '权限验证失败:' . $e->getMessage()], 400);
        }
//        dump(json_decode($res->getBody()->getContents(),true));
        return response(json_decode($res->getBody()->getContents(),true));
    }
}