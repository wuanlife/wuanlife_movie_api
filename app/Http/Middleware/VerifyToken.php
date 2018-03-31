<?php

namespace App\Http\Middleware;

use Closure;
use GuzzleHttp\Client;

class VerifyToken
{
    /**
     * @param $request
     * @param Closure $next
     * @return \Illuminate\Contracts\Routing\ResponseFactory|mixed|\Symfony\Component\HttpFoundation\Response
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handle($request, Closure $next)
    {
        $id_token = $request->header('ID-Token') ?? null;
        $access_token = $request->header('Access-Token') ?? null;
        $client = new Client(['base_uri' => env('OIDC-SERVER')]);
        try {
            $res = $client->request(
                'GET',
                '/api/auth',
                [
                    'headers' => [
                        'ID-Token' => $id_token,
                        'Access-Token' => $access_token,
                    ]
                ]);
        } catch (\Exception $e) {
            return response(['error' => '权限验证失败:' . $e->getMessage()], 400);
        }
        $request->attributes->add(['id-token' => json_decode($res->getBody())->id_token]);
        return $next($request);
    }
}
