<?php

include './Curl.php';
include './Config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $action = $_POST['action'];
    $resource_id = $_POST['resource_id'];
    $method = $_POST['method'];

    $data = [
        'action' => $action,
    ];
    session_start();
    $header[] = 'ID-Token:' . $_SESSION['id_token'];
    $header[] = 'Access-Token:' . $_SESSION['access_token'];
    $result = Curl::send(Config::$wuanlife_movie_api_url . '/resources/'. $resource_id .'/background', $method, $header, $data);

    if ($result['code'] == 400) {
        exit(json_encode(['code' => $result['code'], 'msg' => $result['error']]));
    } else {
        exit(json_encode(['code' => $result['code'], 'msg' => '操作成功']));
    }
} else {
    header('Location: ./resources.php');
}
