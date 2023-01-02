<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use \App\Models\attachment;

class PigeonController extends Controller
{
	public function showDocmentManagementView(){
		$company_id = Auth::user()->staff->company_id;
		$attachment = attachment::where('company_id',$company_id)->get();
		return view('pigeon.pigeon',compact(['attachment']));
	}
}
