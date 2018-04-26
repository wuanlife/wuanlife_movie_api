<?php

namespace App\Http\Controllers;

use App\Model\Movies\{
    Moviesposter, MoviesRating, MoviesBase
};
use Illuminate\Http\Request;

class SearchController extends Controller
{

    public function search(Request $request)
    {
        if (!empty($request->input('q'))) {
            $keywords = $request->input('q');
            $limit = $request->input('limit') ?? 20;
            $offset = $request->input('offset') ?? 0;
            $base = MoviesBase::where('title', 'like', '%' . $keywords . '%')->orwhere('digest', 'like',
                '%' . $keywords . '%')->offset($offset)->limit($limit)->get();

            foreach ($base as $key => $value) {
                $base[$key]['poster'] = MoviesPoster::where('id', '=', $base[$key]['id'])->get()[0]['url'];
                $base[$key]['rating'] = MoviesRating::where('id', '=', $base[$key]['id'])->get()[0]['rating'];
            }
            $data['movies'] = $base;
            $data['total'] = MoviesBase::where('title', 'like', '%' . $keywords . '%')->orwhere('digest', 'like',
                '%' . $keywords . '%')->count();

            return response($data, 200);
        } else {
            return response(['error' => "q为空"], 400);
        }
    }
}
