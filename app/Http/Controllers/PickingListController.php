<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\usersrole;

class PickingListController extends Controller
{
    //

    public function showPickingView()
    {
        $id = Auth::user()->id;
        $user_roles = usersrole::where('user_id',$id)->get();
        
        

        $is_king =  \App\Models\Company::where('owner_user_id',Auth::user()->id)->first();

        if ($is_king != null) {
            $is_king = true;
        } else {
            $is_king  = false;
        }
        return view('pickinglist.pickinglist_template',compact('user_roles','is_king'));
    }
    public function showPickingListView()
    {
    	$id = Auth::user()->id;
        $user_roles = usersrole::where('user_id',$id)->get();
        
        

        $is_king =  \App\Models\Company::where('owner_user_id',Auth::user()->id)->first();

        if ($is_king != null) {
            $is_king = true;
        } else {
            $is_king  = false;
        }
    	return view('pickinglist.picking_list',compact('user_roles','is_king'));
    }
}
