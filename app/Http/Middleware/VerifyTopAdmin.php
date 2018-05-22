<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\DB;

class VerifyTopAdmin
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
        $id_token = $request->header('ID-Token');
        $id_token = json_decode(
            base64_decode(
                explode('.', $id_token)[1]
            )
        );
        $user_id = $id_token->uid;

        $res = DB::table('users_auth')
            ->join('auth_detail', 'users_auth.auth', 'auth_detail.id')
            ->where(
                [
                    'users_auth.id' => $user_id,
                    'identity' => '最高管理员',
                ])
            ->select('users_auth.auth')
            ->count();
        if (!$res) {
            return response(['error' => '权限不足，需要最高管理员权限'], 403);
        }

        return $next($request);
    }
}
