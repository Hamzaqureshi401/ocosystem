<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\usersrole;

class CRPTLandHeldDevController extends Controller
{
    //
public function showLandheldDevGRNCASaleView()
{
	$id = Auth::user()->id;
        $user_roles = usersrole::where('user_id',$id)->get();

        $is_king =  \App\Models\Company::where('owner_user_id',Auth::user()->id)->first();

        if ($is_king != null) {
            $is_king = true;
        } else {
            $is_king  = false;
        }
        return view('crpt.landheld_dev.landheld_dev',compact('user_roles','is_king'));
}
}
