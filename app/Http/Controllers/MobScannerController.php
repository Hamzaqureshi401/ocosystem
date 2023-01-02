<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MobScannerController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function Scan()
    {
        return view('scanner.scan');
    }
}
