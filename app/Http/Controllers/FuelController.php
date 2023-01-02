<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FuelController extends Controller
{
     public function showfuelView(){

        return view('virtualcabinet.fuel');
    }

}
