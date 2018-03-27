<?php

namespace App\Http\Middleware;

use Closure;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\JwtVerifier;

class CheckIdToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        try {
            // 检测 ID-Token 是否存在
            $id_token = $request->header('ID_Token');
            if (!$id_token) {
                throw new \Exception('缺少ID-Token', 400);
            }
            // 检测 ID-Token 合法性
            $data = JwtVerifier::verifyToken($id_token, 'ID');
            $request->attributes->add(['id-token' => $data]);
        } catch (\Exception $exception) {
            return \response(['error' => $exception->getMessage()], $exception->getCode());
        }

        return $next($request);
    }
}
