<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MallController extends Controller
{
    //
	public function landing_view() {
		return view('mall.mall_management');
	}		
}
