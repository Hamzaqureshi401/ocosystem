<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\usersrole;

class QuotationController extends Controller
{
    //
    public function showQuotationView()
    {

    	return view('quotation.quotation');
    }
    public function showQuotationIssuedListView()
    {
    	$id = Auth::user()->id;
        $user_roles = usersrole::where('user_id',$id)->get();
        $merchant_id=12;
        $customer_id=3;

        $is_king =  \App\Models\Company::where('owner_user_id',Auth::user()->id)->first();

        if ($is_king != null) {
            $is_king = true;
        } else {
            $is_king  = false;
        }
    	return view('quotation.quotation_issued_list',compact('user_roles','is_king','merchant_id','customer_id'));

    }
    public function showQuotationReceivedListView()
    {
    	$id = Auth::user()->id;
        $user_roles = usersrole::where('user_id',$id)->get();
        $merchant_id=12;
        $customer_id=8;

        $is_king =  \App\Models\Company::where('owner_user_id',Auth::user()->id)->first();

        if ($is_king != null) {
            $is_king = true;
        } else {
            $is_king  = false;
        }
       return view('quotation.quotation_received_list',compact('user_roles','is_king','merchant_id','customer_id'));
    }
}
