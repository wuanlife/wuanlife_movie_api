<?php

namespace App\Http\Controllers;

use App\{
    Movies_base, Movies_poster, Movies_rating
};
use Dingo\Api\Http\Request;

class SearchController extends Controller
{

    public function search(Request $request)
    {
        if (!empty($request->input('q'))) {
            $keywords = $request->input('q');
            $limit = $request->input('limit') ?? 20;
            $offset = $request->input('offset') ?? 0;
            $base = Movies_base::where('title', 'like', $keywords . '%')->orwhere('digest', 'like',
                $keywords . '%')->offset($offset)->limit($limit)->get();

            foreach ($base as $key => $value) {
                $base[$key]['poster'] = Movies_poster::where('id', '=', $base[$key]['id'])->get()[0]['url'];
                $base[$key]['rating'] = Movies_rating::where('id', '=', $base[$key]['id'])->get()[0]['rating'];
            }
            $data['movies'] = $base;
            $data['total'] = Movies_base::where('title', 'like', $keywords . '%')->orwhere('digest', 'like',
                $keywords . '%')->count();

            return response($data, 200);
        } else {
            return response(['error' => "q为空"], 400);
        }
    }
}
