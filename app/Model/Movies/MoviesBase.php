<?php

namespace App\Model\Movies;

use Illuminate\Database\Eloquent\Model;

class MoviesBase extends Model
{
    public $timestamps = false;
    protected $table = 'movies_base';

    public function rating(){
        return $this->hasOne('App\Model\Movies\MoviesRating','id','id');
    }

    public function summary(){
        return $this->hasOne('App\Model\Movies\MoviesSummary','id','id');
    }
}
