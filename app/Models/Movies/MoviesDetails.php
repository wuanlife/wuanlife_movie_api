<?php

namespace App\Models\Movies;

use Illuminate\Database\Eloquent\Model;

class MoviesDetails extends Model
{
    public $timestamps = false;
    protected $table = 'movies_details';
    protected $primaryKey = 'id';

    public function rating(){
        return $this->hasOne('App\Models\Movies\MoviesRating','id','id');
    }

    public function summary(){
        return $this->hasOne('App\Models\Movies\MoviesSummary','id','id');
    }

}
