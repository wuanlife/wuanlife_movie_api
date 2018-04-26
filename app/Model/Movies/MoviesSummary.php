<?php

namespace App\Model\Movies;

use Illuminate\Database\Eloquent\Model;

class MoviesSummary extends Model
{
    public $timestamps = false;
    protected $table = 'movies_summary';
    protected $primaryKey = 'id';

}
