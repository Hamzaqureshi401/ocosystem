<?php

namespace App\Http\Controllers;

use App\User;
use \App\Models\usersrole;
use App\Models\Company;
use \Illuminate\Support\Facades\Auth;

use Illuminate\Http\Request;

class CRPTBorrowingCostController extends Controller
{
     public function borrowingCost(){
    	$user_id = Auth::user()->id;
        $user_roles = usersrole::where('user_id', $user_id)->get();
        $is_king =  Company::where('owner_user_id', Auth::user()->id)->get();
    	return view('crpt.borrowing_cost.borrowing_cost', compact('user_roles', 'is_king'));
    }
}
