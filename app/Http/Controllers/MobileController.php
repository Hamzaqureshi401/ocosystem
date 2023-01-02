<?php

namespace App\Http\Controllers;

use auth;
use App\User;
use Illuminate\Http\Request;

class MobileController extends Controller
{
     public function __construct()
    {
        $this->middleware('auth');
    }
    public function index()
    {
       $user = auth()->user();
        return view('landing.mob_landing', compact('user'));
    }
}
