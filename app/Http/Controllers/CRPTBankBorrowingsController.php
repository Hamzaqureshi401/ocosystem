<?php

namespace App\Http\Controllers;
use App\Models\usersrole;
use App\Models\Company;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class CRPTBankBorrowingsController extends Controller
{
   public function __construct() {
        $this->middleware('auth');
    }
    
	public function index(Request $request){
		
	
	}
    public function showCRPT_bank_borrowings($company_id){        
    $user_id = Auth::user()->id;
    $user_roles = usersrole::where('user_id',$user_id)->get();
    $is_king =  Company::where('owner_user_id',Auth::user()->id)->get();
    return view('crpt.bank_borrowings.bank_borrowing',compact('user_roles','is_king'));
  }
}
