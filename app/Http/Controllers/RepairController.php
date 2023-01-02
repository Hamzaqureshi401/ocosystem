<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
class RepairController extends Controller
{
    function showRepariView(){
      return view('repair.repair');
    }
}
