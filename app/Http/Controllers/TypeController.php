<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB; 
use Illuminate\Http\Request;
use App\Model\Movies_type_details;

class TypeController extends Controller
{
    public function type()
    {
    	$base = Movies_type_details::all();
    	$base = json_decode($base,true);
    	if (empty($base)) {
    		return response(['error'=>"获取分类信息失败"], 400);
    	}
    	foreach ($base as $key => $value) {
                    $base[$key]['en_name'] = $base[$key]['type_id'];
                    $base[$key]['cn_name'] = $base[$key]['type_name'];
                    unset($base[$key]['type_id']);
                    unset($base[$key]['type_name']);
                }
    	return $base;
    }
    
}
