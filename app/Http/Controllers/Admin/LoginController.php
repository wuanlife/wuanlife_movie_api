<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Builder;
use App\Http\Controllers\Controller;
use App\Models\Users\UsersAuth;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    public function create()
    {
        return view('admin.auth.login');
    }

    /**
     * 登陆
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $credentials = $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required',
            'client_id' => 'required'
        ]);

        // 调用 oidc 登陆
        {
            $result = Http::request(env('OIDC_SERVER'), '/api/users/login', 'POST', [], $credentials);
            $content = json_decode($result['contents'], true);
            // 判断登陆结果
            if (200 !== $result['status_code']) {
                session()->flash('danger', $content['error']);
                return redirect()->back()->withInput();
            }

            $id_token = $content['ID-Token'];
        }

        // 验证权限
        $user_info = json_decode(base64_decode(explode('.', $id_token)[1]));
        $user_id = $user_info->uid;

        if (!UsersAuth::checkAuth($user_id)) {
            session()->flash('danger', '权限不足');
            return redirect()->back()->withInput();
        }

        // 获取 Access-Token
        {
            $header = ['ID-Token' => $id_token];
            $params = ['scope' => 1];
            $result = Http::request(env('OIDC_SERVER'), 'api/auth', 'POST', $header, $params);
            $content = json_decode($result['contents'], true);
            if (200 !== $result['status_code']) {
                session()->flash('danger', $content['error']);
                return redirect()->back()->withInput();
            }
            $access_token =  $content['Access-Token'];
        }

        // 获取用户信息
        $response = Builder::requestInnerApi(
            env('OIDC_SERVER'),
            "api/app/users/{$user_id}"
        );
        $content = json_decode($response['contents'], true);
        if ($response['status_code'] !== 200) {
            return response(['error' => $content['error']], 400);
        }

        $request->session()->put('wuan', ['ID-Token' => $id_token, 'Access-Token' => $access_token, 'user_info' => $content]);
        session()->flash('success', '欢迎回来! ');
        return redirect()->intended(route('resources.index'));
    }

    public function destroy(Request $request)
    {
        $request->session()->flush();
        $request->session()->flash('success', '您已成功退出!');
        return redirect(route('auth.login'));
    }
}
