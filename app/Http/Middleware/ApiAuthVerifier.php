<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Hash;

class ApiAuthVerifier
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
            if (!$request->input('app') or
                !$secret = env(strtoupper($request->input('app')) . '_SECRET')
            ) {
                return response(['error' => 'illegal request'], 400);
            } elseif (!$info = $request->input('info')) {
                return response(['error' => 'The info field is required.'], 422);
            } elseif (!$key = $request->input('key')) {
                return response(['error' => 'The key field is required.'], 422);
            };

            // 应用名、请求时间、过期时间
            $require = ['app', 'iat', 'exp',];
            $info_d = json_decode($info);
            foreach ($require as $item) {
                if (empty($info_d->$item)) {
                    throw new \Exception('Lack of necessary information:' . $item);
                }
            }

            if ($info_d->exp < time()) {
                return response(['error' => 'Request is expired'], 400);
            }

            if (!Hash::check($info . $secret, $key)) {
                return response(['error' => 'Failed to verify auth'], 403);
            }
        } catch (\Exception $exception) {
            return response(['error' => $exception->getMessage()], 400);
        }
        return $next($request);
    }
}
