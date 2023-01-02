<?php

namespace App\Http\Controllers;

use App\User;
use \App\Models\usersrole;
use App\Models\Company;
use \Illuminate\Support\Facades\Auth;

use Illuminate\Http\Request;

class CRPTInvestmentsSubsidiariesController extends Controller
{
     public function investmentsSubsidiaries(){
    	$user_id = Auth::user()->id;
        $user_roles = usersrole::where('user_id', $user_id)->get();
        $is_king =  Company::where('owner_user_id', Auth::user()->id)->get();
    	return view('crpt.investments_subsidiaries.investments_subsidiaries', compact('user_roles', 'is_king'));
    }
}
