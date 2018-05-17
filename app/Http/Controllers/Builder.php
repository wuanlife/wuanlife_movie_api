<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Hash;

class Builder extends Controller
{
    public static function queryToken()
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
        $query = "app={$app}&info={$info}&key={$key}";

        return $query;
    }
}
