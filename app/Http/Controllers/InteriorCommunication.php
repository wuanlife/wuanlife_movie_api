<?php

namespace App\Http\Controllers;

use App\Models\Points\Points;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class InteriorCommunication extends Controller
{
    /**
     * 操作积分接口
     * @param $id
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function putPoints($id, Request $request)
    {
        // 验证参数完整性
        $validator = Validator::make($request->all(),
            [
                'sub_points' => 'required',
                'action'     => 'required',
            ],
            [
                'sub_points' => '兑换积分数不能为空',
                'action'     => '操作类型不能为空',
            ]
        );
        if ($validator->fails()) {
            return response(['error' => $validator->errors()->first()], 422);
        }

        try {
            $sub_points = $request->input('sub_points');
            $user = Points::find($id);
            if (!$user) {
                $user = Points::create([
                    'user_id' => $id,
                    'points'  => 0
                ]);
            }
            $points = $user->points;

            if ($sub_points < 0 && $sub_points > $points) {
                return response(['error' => '积分不足'], 400);
            }
            switch ($request->input('action')) {
                case 'increment':
                    $user->increment('points', $sub_points);
                    break;
                case 'decrement':
                    if ($points < $sub_points) {
                        return response(['error' => 'Lock points']);
                    }
                    $user->decrement('points', $sub_points);
                    break;
                default:
                    return response(['error' => '错误的请求类型'], 422);
            }
            return response([], 204);
        } catch (\Exception $e) {
            Log::error(
                "Failed to put points:{$e->getMessage()}.In " . __METHOD__ . " on line {$e->getLine()}",
                [
                    'user_id' => $id,
                    'action'  => $request->input('action'),
                ]);
            return response(['error' => '操作失败'], 400);
        }
    }

    /**
     * 获取积分
     * @param $id
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getPoints($id)
    {
        $res = Points::find($id);
        if (!$res) {
            Points::create([
                'user_id' => $id,
                'points'  => 0
            ]);
            return response(['points' => 0], 200);
        }
        return response(['points' => $res->points], 200);
    }
}
