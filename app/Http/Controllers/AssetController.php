<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AssetController extends Controller
{
    public function show_asset(){
        $assets  =1;
        return view('asset.asset',compact('assets'));
    }



}
