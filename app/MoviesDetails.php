<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MoviesDetails extends Model
{
    public $timestamps = false;
    protected $table = 'movies_details';
    protected $primaryKey = 'id';

}
