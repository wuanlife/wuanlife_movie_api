<?php

namespace App\Http\Controllers;

use App\Movies_type_details;

class TypeController extends Controller
{
    public function type()
    {
        $base = Movies_type_details::all();
        $base = json_decode($base, true);
        if (empty($base)) {
            return response(['error' => "获取分类信息失败"], 400);
        }
        return response(['type' => $base], 200);
    }

}
