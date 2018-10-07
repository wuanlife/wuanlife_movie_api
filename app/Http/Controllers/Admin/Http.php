<?php

/**
 * http 请求类
 */
namespace App\Http\Controllers\Admin;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class Http
{
    public static function request($base_url, $url, $method = 'GET', $header = [], $params = [])
    {
        $client = new Client(['base_uri' => $base_url, 'http_errors' => false]);
        try {
            $response = $client->request($method, $url, [
                    'headers' => $header,
                    'json' => $params,
                ]
            );
        } catch (GuzzleException $e) {
            return [
                'status_code' => '404',
                'contents' => ''
            ];
        }

        return [
            'status_code' => $response->getStatusCode(),
            'contents' => $response->getBody()->getContents(),
        ];
    }
}