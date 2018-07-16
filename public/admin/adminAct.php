<?php

include './Curl.php';
include './Config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $method = $_POST['method'];
    $id = '';
    $email = '';

    if ('DELETE' == strtoupper($method)) {
        $id = $_POST['id'];
    } else {
        $email = $_POST['email'];
    }

    $data = [
        'email' => $email,
    ];
    session_start();
    $header[] = 'ID-Token:' . $_SESSION['id_token'];
    $header[] = 'Access-Token:' . $_SESSION['access_token'];
    $result = Curl::send(Config::$wuanlife_movie_api_url . '/admin/'. $id, $method, $header, $data);
    if ($result['code'] == 200 || $result['code'] == 204) {
        exit(json_encode(['code' => $result['code'], 'msg' => '操作成功']));
    } else {
        exit(json_encode(['code' => $result['code'], 'msg' => $result['error']]));
    }
} else {
    header('Location: ./resources.php');
}
