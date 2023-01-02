<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TargetController extends Controller
{
    //

    public function __construct()
    {
        $this->middleware('auth');
         $this->middleware('CheckRole:stg');
    }

	public function showTargetView() {
        $id = Auth::user()->id;
        return view('target.target');
	}
}
