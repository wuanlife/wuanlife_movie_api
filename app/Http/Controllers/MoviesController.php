<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Model\Movies_base;
use App\Model\Movies_poster;
use App\Model\Movies_rating;
use App\Model\Movies_type;
use App\Model\Movies_type_details;
use Illuminate\Http\Request;

class MoviesController extends Controller
{
    //
    function home()
    {
        //主页 limit offset type三个参数
        $limit = $_GET['limit'] ?? 20;
        $offset = $_GET['offset'] ?? 0;
        if (isset($_GET['type']) ) {
            //type 参数也存在
            $type = $_GET['type'];
            $base['movies'] = DB::table('movies_base')
                ->join('movies_poster','movies_poster.id','movies_base.id')
                ->join('movies_type','movies_type.movies_id','movies_base.id')
                ->join('movies_rating','movies_rating.id','movies_base.id')
                ->where('type_id',$type)
                ->skip($offset)
                ->take($limit)
                ->select('movies_base.id','movies_base.title','movies_base.digest','movies_poster.url as poster ','movies_type.type_id','movies_rating.rating')
                ->get();
            $base['movies'] = json_decode($base['movies'],true);
            $base['total'] = DB::table('movies_base')
                ->join('movies_poster','movies_poster.id','movies_base.id')
                ->join('movies_type','movies_type.movies_id','movies_base.id')
                ->join('movies_rating','movies_rating.id','movies_base.id')
                ->where('type_id',$type)
                ->count();
            return response($base,200);
        }
        else
        {
            //type参数不存在
            $base['movies'] = DB::table('movies_base')
                ->join('movies_poster','movies_poster.id','movies_base.id')
                ->join('movies_rating','movies_rating.id','movies_base.id')
                ->skip($offset)
                ->take($limit)
                ->select('movies_base.id','movies_base.title','movies_base.digest','movies_poster.url as poster ','movies_rating.rating')
                ->get();
            $base['movies'] = json_decode($base['movies'],true);
            $base['total'] = count($base);
            return response($base,200);
        }
    }
}
