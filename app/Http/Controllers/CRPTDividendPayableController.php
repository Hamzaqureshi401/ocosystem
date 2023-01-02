<?php

namespace App\Http\Controllers;

use App\Models\usersrole;
use App\Models\Company;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class CRPTDividendPayableController extends Controller
{	
	public function __construct() {
        $this->middleware('auth');
    }

    public function showDividendPayable($company_id){
    	$user_id    =  Auth::user()->id;
	    $user_roles =  usersrole::where('user_id',$user_id)->get();
	    $is_king    =  Company::where('owner_user_id',Auth::user()->id)->get();
	    return view('crpt.dividend_payable.dividend_payable',compact('user_roles','is_king'));
    }
}
