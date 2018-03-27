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

		if(!empty($_GET['limit']) && !empty($_GET['offset']))
    	{
    		$limit = $_GET['limit'];
    		$offset = $_GET['offset'];
    		$base = Movies_base::where('title', 'like', $keywords.'%')->orwhere('digest', 'like', $keywords.'%')->offset($offset)->limit($limit)->get();
		// $base = DB::table('movies_base')
  //               ->where('title', 'like', $keywords.'%')
  //               ->orwhere('digest', 'like', $keywords.'%')
  //               ->get();
    	// foreach ($base as $key => $value) {
    	}
    	else
    	{
			$base = Movies_base::where('title', 'like', $keywords.'%')->orwhere('digest', 'like', $keywords.'%')->get();
		}
    		foreach ($base as $key => $value) {

    		$base[$key]['poster'] = Movies_poster::where('id','=',$base[$key]['id'])->get()[0]['url'];
    		$base[$key]['rating'] = Movies_rating::where('id','=',$base[$key]['id'])->get()[0]['rating'];
			}
			$data['movies'] = $base;
			$data['total'] = count(Movies_base::where('title', 'like', $keywords.'%')->orwhere('digest', 'like', $keywords.'%')->get());
		
    	// $base1 = Movies_poster::find(1)->belongsToMovies_base;
    	// $data['movies'] = $base['data'];
    	// $data['total'] = $base['total'];
    	// print_r($data['movies']);
    	
    	return $data;
    	
	}
	else
	{
		return response(['error'=>"q为空"], 400);
	}
	}
}
