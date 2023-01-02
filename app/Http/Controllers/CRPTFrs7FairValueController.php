<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\usersrole;
use App\Models\Company;
use Illuminate\Support\Facades\Auth;

class CRPTFrs7FairValueController extends Controller
{
    public function __construct() {
        $this->middleware('auth');
    }
    public function showFairValue($company_id){
		$user_id 	 = 	Auth::user()->id;
    	$user_roles  =	usersrole::where('user_id',$user_id)->get();
    	$is_king 	 =  Company::where('owner_user_id',Auth::user()->id)->get();
		return view('crpt.frs7_fair_value.frs7_fair_value',compact('user_roles','is_king'));
	}  
}