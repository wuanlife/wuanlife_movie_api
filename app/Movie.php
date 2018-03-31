<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Movie extends Model
{
    //

    public static function movies_base_select($id)
    {
        $result = DB::select("select * from movies_base where id = ?", [$id]);
        return $result;
    }

    public static function movies_base_select_title($id)
    {
        $result = DB::select("select title from movies_base where id = ?", [$id]);
//        var_dump($result[0]["title"]);exit;
        return $result[0]["title"];
    }

    public static function movies_details_select($id)
    {
        $result = DB::select("select original_title,countries,year,aka,url_douban from movies_details where id = ?",
            [$id]);
        return $result[0];
    }

    public static function movies_summary_select_summary($id)
    {
        $result = DB::select("select summary from movies_summary where id = ?", [$id]);
        return $result[0]["summary"];
    }

    public static function movies_rating_select_rating($id)
    {
        $result = DB::select("select rating from movies_rating where id = ?", [$id]);
        return $result[0]["rating"];
    }

    public static function movies_genres_type_name($id)
    {
        $result = DB::select("select type_name from movies_genres , movies_genres_details where movies_id = ? and movies_genres.genres_id = movies_genres_details.genres_id",
            [$id]);
        return $result;
    }

    public static function movies_directors_select($id)
    {
        $result = DB::select("select name from movies_director , movies_directors where movies_id = ? and movies_director.id = movies_directors.directors_id",
            [$id]);
        return $result;
    }

    public static function movies_casts_select($id)
    {
        $result = DB::select("select name from actors , movies_actors where movies_id = ? and actors.id = movies_actors.actors_id",
            [$id]);
        return $result;
    }

    public static function actors()
    {

        $result = DB::select("select * from actors");
        return $result;
    }


}
