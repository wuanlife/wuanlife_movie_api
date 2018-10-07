<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Builder;
use App\Http\Controllers\Controller;
use App\Models\Users\UsersAuth;
use function foo\func;
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
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function store(Request $request)
    {
        $credentials = $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required',
            'client_id' => 'required'
        ]);

        // 调用 oidc 登陆
        $login = Builder::requestInnerApi(
            env('OIDC_SERVER'),
            "/api/users/login",
            'POST',
            [],
            $credentials
        );
        $content = json_decode($login['contents'], true);
        // 判断登陆结果
        if (200 !== $login['status_code']) {
            $request->session()->flash('danger', $content['error']);
            return redirect()->back()->withInput();
        }

        // 验证权限
        $id_token = $content['ID-Token'];
        $id_token = json_decode(base64_decode(explode('.', $id_token)[1]));
        $user_id = $id_token->uid;

        if (!UsersAuth::checkAuth($user_id)) {
            $request->session()->flash('danger', '权限不足');
            return redirect()->back()->withInput();
        }

        $request->session()->put('wuan', ['ID-Token' => $content['ID-Token']]);
        session()->flash('success', '欢迎回来! ');
        return redirect()->intended(route('admin.resources'));
    }

    public function destroy(Request $request)
    {
        $request->session()->flush();
        $request->session()->flash('success', '您已成功退出!');
        return redirect('login');
    }
}
