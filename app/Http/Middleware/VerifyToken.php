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
        try {
            if (!($id_token = $request->header('ID-Token'))) {
                return response(['error' => '缺少ID-Token'], 401);
            }
            if (!($access_token = $request->header('Access-Token'))) {
                return response(['error' => '缺少Access-Token'], 401);
            }

            $client = new Client(['base_uri' => env('OIDC_SERVER')]);
            $res = $client->request(
                'GET',
                '/api/auth',
                [
                    'headers' => [
                        'ID-Token' => $id_token,
                        'Access-Token' => $access_token,
                    ]
                ]);

            $id_token = json_decode(base64_decode(explode('.', $id_token)[1]));
            $request->attributes->add(['id-token' => $id_token]);

            return $next($request);
        } catch (\Exception $e) {
            return response(['error' => 'Permission verification failed: ' . $e->getMessage()], 400);
        }
    }
}
