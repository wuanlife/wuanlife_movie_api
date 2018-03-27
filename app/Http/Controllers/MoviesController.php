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
    	if(isset($_GET['limit']) && isset($_GET['offset']))
    	{
    		$limit = $_GET['limit'];
    		$offset = $_GET['offset'];
    		if (isset($_GET['type']) && Movies_type_details::where('type_name',$_GET['type'])) {
    			$type = $_GET['type'];
    			$id =DB::table('movies_type_details')->where('type_name',$type)->get()[0]->type_id;
    			$base = DB::table('movies_base')
    			->join('movies_poster','movies_poster.id','movies_base.id')
    			->join('movies_type','movies_type.movies_id','movies_base.id')
                ->join('movies_rating','movies_rating.id','movies_base.id')
    			->where('type_id',$id)
    			->skip($offset)
    			->take($limit)
    			->get();
                $base = $this->d($base);
                $base['total'] = count(DB::table('movies_base')
                ->join('movies_poster','movies_poster.id','movies_base.id')
                ->join('movies_type','movies_type.movies_id','movies_base.id')
                ->join('movies_rating','movies_rating.id','movies_base.id')
                ->where('type_id',$id)
                ->get());
    			return $base;
    		}
    		else
    		{
    			$base = DB::table('movies_base')
    			->join('movies_poster','movies_poster.id','movies_base.id')
    			->join('movies_rating','movies_rating.id','movies_base.id')
    			->join('movies_type','movies_type.movies_id','movies_base.id')
                ->skip($offset)
                ->take($limit)
    			->get();
    			$base = $this->d($base);
                $base['total'] = count($base);
                //$data['total'] = count(Movies_base::all());
                return $base;
			}

    		//$base = Movies_base::offset($offset)->limit($limit)->get();
    		//$base = Movies_base::paginate($perPage = $limit, $columns = ['*'], $pageName = '', $page = $offset);
    		// return $base;
    	}
    	else
    	{
    		$base = DB::table('movies_base')
    			->join('movies_poster','movies_poster.id','movies_base.id')
    			->join('movies_rating','movies_rating.id','movies_base.id')
    			->join('movies_type','movies_type.movies_id','movies_base.id')
    			->get();
            $base = json_decode($base,true); 
                foreach ($base as $key => $value) {
                    $base[$key]['poster'] = $base[$key]['url'];
                    unset($base[$key]['url']);
                    unset($base[$key]['movies_id']);
                    unset($base[$key]['type_id']);
                }
		$data['movies'] = $base;
		$data['total'] = count(Movies_base::all());
    	
    	return $data;
        }
    }

    function d($base)
    {
        //去除多余的
        $base = json_decode($base,true); 
                foreach ($base as $key => $value) {
                    $base[$key]['poster'] = $base[$key]['url'];
                    unset($base[$key]['url']);
                    unset($base[$key]['movies_id']);
                    unset($base[$key]['type_id']);
                }
        return $base;
    }
}
