<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MoviesDetails extends Model
{
    public $timestamps = false;
    protected $table = 'movies_details';
    protected $primaryKey = 'id';

    public function rating(){
        return $this->hasOne('App\MoviesRating','id','id');
    }

    public function summary(){
        return $this->hasOne('App\MoviesSummary','id','id');
    }

}
