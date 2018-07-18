<?php

session_start();
include './Curl.php';
include './Config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];

    function login()
    {
        // 获取账号和密码]
        if (isset($_POST['email']) && empty($_POST['email'])) {
            exit(json_encode(['code' => 400, 'msg' => '用户名不能为空']));
        }

        if (isset($_POST['password']) && empty($_POST['password'])) {
            exit(json_encode(['code' => 400, 'msg' => '密码不能为空']));
        }

        $email = $_POST['email'];
        $password = $_POST['password'];

        $data = [
            'email' => $email,
            'password' => $password,
            'client_id' => 1
        ];
        $result = Curl::send(Config::$wuanlife_oidc_url . '/users/login', 'post', [], $data);

        if (200 == $result['code']) {
            $_SESSION['id_token'] = $result['ID-Token'];
            exit(json_encode(['code' => $result['code'], 'msg' => '登陆成功', 'id_token' => $result['ID-Token']]));
        } else {
            exit(json_encode(['code' => 400, 'msg' => $result['error']]));

        }
    }

    function logout()
    {
        session_destroy();
        header('Location: ./login.php');
    }

    switch ($action) {
        case 'login':
            login();
            break;
        case 'logout':
            logout();
            break;
        default:
            logout();
    }

} else {
    header('Location: ./login.php');
}
