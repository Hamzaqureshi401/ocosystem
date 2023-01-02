<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TTSController extends Controller
{
    function showTTSView(){
      return view('tts.tts');
    }
  
}
