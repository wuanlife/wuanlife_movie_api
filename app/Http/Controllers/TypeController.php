<?php

namespace App\Http\Controllers;

use App\Models\Movies\MoviesTypeDetails;

class TypeController extends Controller
{
    public function type()
    {
        $base = MoviesTypeDetails::all();
        $base = json_decode($base, true);
        if (empty($base)) {
            return response(['error' => 'Failed to get Classified information'], 400);
        }
        return response(['type' => $base], 200);
    }

}
