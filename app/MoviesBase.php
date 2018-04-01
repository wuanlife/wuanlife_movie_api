<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MoviesBase extends Model
{
    public $timestamps = false;
    protected $table = 'movies_base';
}
