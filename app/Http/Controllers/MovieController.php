<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Models\Movie;
use Illuminate\Support\Facades\Input;
use GuzzleHttp\Client;
class MovieController extends Controller{

    public function index(){

    }



    /**
     * @return mixed
     *  M1
     * 参数	类型	说明
     * id	Int	影片id
     * title	String	影片标题
     * original_title	String	影片原名
     * countries	String	制片国家/地区
     * year	String	年代
     * genres	String	影片类型
     * aka	String	影片别名
     * directors	String	导演
     * casts	String	主演
     * url_douban	String	豆瓣链接
     * summary	String	剧情简介
     * rating	float(1)	豆瓣评分
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


        $res1 = json_encode($finalresult[1],JSON_UNESCAPED_UNICODE);
        $res3 = json_encode($finalresult[3],JSON_UNESCAPED_UNICODE);
        $res6 = json_encode($finalresult[6],JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

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
        return response($final,200);
    }


    //获取post数据并提交API方法
    public function moviesfind()
    {
       $post_url =   Input::get('url');
       $post_type = Input::get('type');
       //调用发现影视方法
       $this -> doubanapi($post_url,$post_type);


    }

    /**
     *  M3 发现影视方法  豆瓣API调用方法
     */
    public function doubanapi($url,$type)
    {

        $pattern ="/subject\/\d+\/?/";
        preg_match_all($pattern,$url,$arr);
        preg_match_all('/\d+/',$arr[0][0],$arr2);
        $douban_i = (int)$arr2[0][0];
        $url = "https://api.douban.com/v2/movie/subject/".$douban_i;


        //获取豆瓣返回JASON
        $res = file_get_contents($url);
        $douban_arr = json_decode($res,true);



        //判断是否存在
        $movie_title_exist = DB::table('movies_base')->where('title', $douban_arr['title'] )->first();

        if($movie_title_exist){
            return response(['error'=>'资源已存在'],400);
            exit;
        }
        //获取插入ID
        $movie_insert_id = DB::table('movies_base')
            ->insertGetId(
                [
                    'title'=> $douban_arr['title'],
                    'digest' => ""
                ]
            );
        if(!$movie_insert_id){
            return response(['error'=>'添加失败'],400);
            exit;
        }
        // 剧情简介 summary
        $summary_insert = DB::table('movies_summary')
            ->insert(
                [
                    'id'=> $movie_insert_id,
                    'summary' => $douban_arr['summary']
                ]
            );

        // 豆瓣评分 rating
        $rating_insert = DB::table('movies_rating')
            ->insert(
                [
                    'id'=> $movie_insert_id,
                    'rating' => $douban_arr['rating']['average']
                ]
            );

        // 影片类型 genres
        for($i=0;$i<count($douban_arr['genres']);$i++){

            $genres_exist = DB::table('movies_genres_details')->where('type_name', $douban_arr['genres'][$i] )->first();
            //如果不存在，则插入
            if(!$genres_exist){
                $genres_insert_id[$i] = DB::table('movies_genres_details')
                    ->insertGetId(
                        [

                            'type_name' => $douban_arr['genres'][$i]
                        ]
                    );
            }else{
                $genres_insert_id[$i] = $genres_exist['genres_id'];
            }
        }
        //插入movies_genres
        if(isset($genres_insert_id)) {
            for ($i = 0; $i < count($douban_arr['genres']); $i++) {
                $movies_genres_insert = DB::table('movies_genres')
                    ->insert(
                        [
                            'movies_id' => $movie_insert_id,
                            'genres_id' => $genres_insert_id[$i]
                        ]
                    );
            }
        }

        // 导演 directors
        for($i=0;$i<count($douban_arr['directors']);$i++){

            $director_exist = DB::table('movies_director')->where('name', $douban_arr['directors'][$i]['name'] )->first();
            //如果不存在，则插入
            if(!$director_exist){
                $director_insert_id[$i] = DB::table('movies_director')
                    ->insertGetId(
                        [

                            'name' => $douban_arr['directors'][$i]['name']
                        ]
                    );
            }else{
                $director_insert_id[$i] = $director_exist['id'];
            }
        }
        //插入movies_directors
        if(isset($director_insert_id)) {
            for ($i = 0; $i < count($douban_arr['directors']); $i++) {
                $movies_directors_insert = DB::table('movies_directors')
                    ->insert(
                        [
                            'movies_id' => $movie_insert_id,
                            'directors_id' => $director_insert_id[$i]
                        ]
                    );
            }
        }

        // 主演 casts
        for($i=0;$i<count($douban_arr['casts']);$i++){

            $actor_exist = DB::table('actors')->where('name', $douban_arr['casts'][$i]['name'] )->first();
            //如果不存在，则插入
            if(!$actor_exist){
                $actor_insert_id[$i] = DB::table('actors')
                    ->insertGetId(
                        [

                            'name' => $douban_arr['casts'][$i]['name']
                        ]
                    );
            }else{
                $actor_insert_id[$i] = $actor_exist['id'];
            }
        }
        //插入movies_directors
        if(isset($actor_insert_id)) {
            for ($i = 0; $i < count($douban_arr['casts']); $i++) {
                $movies_directors_insert = DB::table('movies_actors')
                    ->insert(
                        [
                            'movies_id' => $movie_insert_id,
                            'actors_id' => $actor_insert_id[$i]
                        ]
                    );
            }
        }

        // 插入 movies_details
        $type_exist = DB::table('movies_type_details')->where('type_name', $type )->first();
        //如果不存在，则插入
        if(!$type_exist){
            $type_insert_id = DB::table('movies_type_details')
                ->insertGetId(
                    [

                        'type_name' => $type
                    ]
                );
        }else{
            $type_insert_id = $type_exist['type_id'];
        }
        $movies_details_insert = DB::table('movies_details')
            ->insert(
                [
                    'id'=> $movie_insert_id,
                    'original_title' => $douban_arr['original_title'],
                    'type_id' => $type_insert_id,
                    'countries' => $douban_arr['countries'][0],
                    'year' => $douban_arr['year'],
                    'aka' => $douban_arr['aka'][0],
                    'url_douban' => $douban_arr['alt']
                ]
            );

        return response(json_encode(['id' => $movie_insert_id]),200);

    }

}