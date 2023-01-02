<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\usersrole;
use App\Models\Company;
use Illuminate\Support\Facades\Auth;

class CRPTDividendExpenseController extends Controller
{
    public function __construct() {
        $this->middleware('auth');
    }
    public function showDividendExpense($company_id){
    	$user_id 	 = 	Auth::user()->id;
    	$user_roles  =	usersrole::where('user_id',$user_id)->get();
    	$is_king 	 =  Company::where('owner_user_id',Auth::user()->id)->get();
    	return view('crpt.dividend_expense.dividend_expense',compact('is_king','user_roles'));
    }
}
