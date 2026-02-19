<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\State;

class CityController extends Controller
{
    public function index(State $state)
    {
        return response()->json(
            $state->cities()->orderBy('name')->get()
        );
    }
}
