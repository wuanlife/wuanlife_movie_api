<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Model\Movies_base;
use App\Model\Movies_poster;
use App\Model\Movies_rating;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    //
    public function search()
    {
        if (!empty($_GET['q']))
        {
            $keywords= $_GET['q'];
            $limit = $_GET['limit'] ?? 20;
            $offset = $_GET['offset'] ?? 0;
            $base = Movies_base::where('title', 'like', $keywords.'%')->orwhere('digest', 'like', $keywords.'%')->offset($offset)->limit($limit)->get();

            foreach ($base as $key => $value) {
                $base[$key]['poster'] = Movies_poster::where('id','=',$base[$key]['id'])->get()[0]['url'];
                $base[$key]['rating'] = Movies_rating::where('id','=',$base[$key]['id'])->get()[0]['rating'];
            }
            $data['movies'] = $base;
            $data['total'] = Movies_base::where('title', 'like', $keywords.'%')->orwhere('digest', 'like', $keywords.'%')->count();

            return $data;
        }
        else
        {
            return response(['error'=>"q为空"], 400);
        }
    }
}
