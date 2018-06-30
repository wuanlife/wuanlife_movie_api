<?php

namespace App\Models\Movies;

use Illuminate\Database\Eloquent\Model;

class MoviesDirectors extends Model
{
    public $timestamps = false;
    protected $table = 'movies_directors';

    public function director(){
        return $this->hasOne('App\Models\Human\Directors','id','director_id');
    }
}
