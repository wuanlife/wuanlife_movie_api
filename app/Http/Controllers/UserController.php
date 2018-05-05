<?php
/**
 * Created by PhpStorm.
 * User: csc
 * Date: 2018/5/4
 * Time: 22:00
 */

namespace App\Http\Controllers;


use App\Model\Scores;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function getMovieScore($id, Request $request)
    {
        $id_token = $request->get('id-token');
        try {
            if ($id != $id_token->uid) {
                throw new \Exception('非法请求，用户ID与令牌ID不符', 400);
            }
            $user = Scores::find($id_token->uid);
            return response([
                'id'    =>$user['user_id'],
                'score' =>$user['scores']
            ], 200);
        } catch (\Exception $exception) {
            if ($exception->getCode() <= 300 || $exception->getCode() > 510) {
                return response(['error' => $exception->getMessage()], 400);
            } else {
                return response(['error' => $exception->getMessage()], $exception->getCode());
            }
        }
    }

    public function getWuanScore($id, Request $request)
    {
        $client = new Client(['base_uri' => env('OIDC_SERVER')]);
        try {
            $res = $client->request(
                'GET',
                "/api/users/{$id}/score",
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