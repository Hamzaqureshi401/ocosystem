<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\usersrole;

class ConsignmentController extends Controller
{
    //
    public function showConsignmentView(){
    	return view('consignment.consignment');
    }


    public function showConsignmentIssuedListView(){
    	$id = Auth::user()->id;
        $user_roles = usersrole::where('user_id',$id)->get();
        $consignment_id=12;
        

        $is_king =  \App\Models\Company::where('owner_user_id',Auth::user()->id)->first();

        if ($is_king != null) {
            $is_king = true;
        } else {
            $is_king  = false;
        }
    	return view('consignment.consignment_issued_list',
			compact('user_roles','is_king','consignment_id'));
    }


    public function showConsignmentReceivedlistView(){
    	$id = Auth::user()->id;
        $user_roles = usersrole::where('user_id',$id)->get();
        $consignment_id=8;

        $is_king =  \App\Models\Company::where('owner_user_id',Auth::user()->id)->first();

        if ($is_king != null) {
            $is_king = true;
        } else {
            $is_king  = false;
        }
    	return view('consignment.consignment_received_list',
			compact('user_roles','is_king','consignment_id'));
    }
}
