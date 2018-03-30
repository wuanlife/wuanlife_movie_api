<?php

namespace App\Model;
use Illuminate\Database\Eloquent\Model;

class Movies_base extends Model
{
    protected $table = 'movies_base';
    //$this->hasOne('App\Model\Movie_poster');

    public function movies_poster()
    {
    	return $this->hasOne('App\Model\Movies_poster','id','id');
    }

    public function movies_rating()
    {
    	return $this->hasOne('App\Model\Movies_rating','id','id');
    }
    public function movies_type()
    {
    	return $this->hasOne('App\Model\Movies_type');
    }

}
