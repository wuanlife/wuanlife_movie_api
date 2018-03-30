<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Models\Movie;
class MovieController extends Controller{

    public function index(){
        echo "hello<br>";
//        $res = DB::select('select * from movies_details where id = ?',$id);
//        dd($res);

        $res = movie::movies_base('1');
        return $res;
//        return Movie::actors();
    }
    /**
     * @return mixed
     *  M1
    参数	类型	说明
    id	Int	影片id
    title	String	影片标题
    original_title	String	影片原名
    countries	String	制片国家/地区
    year	String	年代
    genres	String	影片类型
    aka	String	影片别名
    directors	String	导演
    casts	String	主演
    url_douban	String	豆瓣链接
    summary	String	剧情简介
    rating	float(1)	豆瓣评分
     *
     */


    public function moviesdetails($id)
    {

        $title = movie::movies_base_select_title($id);
        if(!$title){
            return response(['error'=>'影片不存在'],404);
            exit;
        }
        $movies_details = movie::movies_details_select($id);
//        var_dump($title);
        $summary = movie::movies_summary_select_summary($id);
        $rating = movie::movies_rating_select_rating($id);
        $genres = movie::movies_genres_type_name($id);
        $directors = movie::movies_directors_select($id);
        $casts = movie::movies_casts_select($id);

        $finalresult[1] = [
            'id'=> $id ,
            'title' => $title,
            'original_title'=> $movies_details["original_title"],
            'countries'=>$movies_details["countries"],
            'year'=>$movies_details["year"],

            ];
        $finalresult[3] = [

            'aka'=>$movies_details["aka"]

        ];


        $finalresult[6] = [
            'summary'=>$summary,
            'rating'=> 9.5,
            'url_douban'=>$movies_details["url_douban"]
        ];


        $res1 = json_encode($finalresult[1]);
        $res3 = json_encode($finalresult[3]);
        $res6 = json_encode($finalresult[6]);

        $res1 = substr($res1, 0, -1) . ',';

        $res3 = substr($res3, 0, -1) . ',';
        $res3 = substr($res3, 1) ;



        $temp2_genres = "";
        for($i=0;$i<count($genres);$i++){
            $temp2_genres = $temp2_genres."{\"type\":\"".$genres[$i]['type_name']."\"},";
        }
        $temp2_genres = substr($temp2_genres, 0, -1);
        $temp_genres = "{\"genres\":[".$temp2_genres."]}";
        $res2 = substr($temp_genres, 0, -1) . ',';
        $res2 = substr($res2, 1) ;
//
        $temp2_directors = "";
        for($i=0;$i<count($directors);$i++){
            $temp2_directors = $temp2_directors."{\"name\":\"".$directors[$i]['name']."\"},";
        }
        $temp2_directors = substr($temp2_directors, 0, -1);
        $temp_directors = "{\"directors\":[".$temp2_directors."]}";
        $res4 = substr($temp_directors, 0, -1) . ',';
        $res4 = substr($res4, 1) ;
//
        $temp2_casts = "";
        for($i=0;$i<count($casts);$i++){
            $temp2_casts = $temp2_casts."{\"name\":\"".$casts[$i]['name']."\"},";
        }
        $temp2_casts = substr($temp2_casts, 0, -1);
        $temp_casts = "{\"casts\":[".$temp2_casts."]}";
        $res5 = substr($temp_casts, 0, -1) . ',';
        $res5 = substr($res5, 1) ;

        $final = $res1.$res2.$res3.$res4.$res5.$res6;
//        echo $final;
        return response($final,200);
    }


}