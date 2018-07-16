<?php

class Curl
{
    public static function send($url, $method = 'GET', $header, $data)
    {
        $ch = curl_init();
        $timeout = 300;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        if ($header) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            curl_setopt($ch, CURLINFO_HEADER_OUT, 0);
        }

        switch(strtoupper($method)) {
            case 'GET':
                break;
            case 'POST':
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                break;
            case 'DELETE':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                break;
            case 'PUT':
                break;
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $res_data = curl_exec($ch);
        $http_code = curl_getinfo($ch)['http_code'];
        curl_close($ch);
        return ($res_data ? json_decode($res_data, true) + ['code' => $http_code] : ['code' => $http_code]);
    }
}