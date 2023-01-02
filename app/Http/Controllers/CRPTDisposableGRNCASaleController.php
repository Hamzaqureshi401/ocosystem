<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\usersrole;

class CRPTDisposableGRNCASaleController extends Controller
{
    //

    public function showDisposableGRNCASaleView()
    {
    	$id = Auth::user()->id;
        $user_roles = usersrole::where('user_id',$id)->get();

        $is_king =  \App\Models\Company::where('owner_user_id',Auth::user()->id)->first();

        if ($is_king != null) {
            $is_king = true;
        } else {
            $is_king  = false;
        }
    	return view('crpt.disposable_grnca_sale.disposable_grnca_sale',compact('user_roles','is_king'));

    }
}
