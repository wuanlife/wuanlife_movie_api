<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Hash;

class Builder extends Controller
{
    /**
     * 构造内部通信请求
     * @param $url
     * @param string $method
     * @param array $header
     * @param array $param
     * @return array|\Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function requestInnerApi($url, $method = 'GET', $header = [], $param = [])
    {
        $client = new Client(['base_uri' => env('OIDC_SERVER')]);
        $params = array_merge(self::getParam(), $param);
        $response = $client->request($method, $url, [
                'headers' => $header,
                'json' => $params,
            ]
        );

        return [
            'status_code' => $response->getStatusCode(),
            'contents' => $response->getBody()->getContents(),
        ];
    }

    /**
     * 构造内部请求时验证所需的信息
     * @return array
     */
    private static function getParam()
    {
        $app = env('APP_NAME');
        $secret = env('SECRET');
        $info = json_encode([
                'app' => $app,
                'iat' => time(),
                'exp' => time() + 60,
            ]
        );
        $key = Hash::make($info . $secret);

        return [
            'app' => $app,
            'info' => $info,
            'key' => $key,
        ];
    }
}
