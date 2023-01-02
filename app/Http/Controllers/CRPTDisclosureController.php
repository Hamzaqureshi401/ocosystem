<?php

namespace App\Http\Controllers;

use App\User;
use \App\Models\usersrole;
use App\Models\Company;
use \Illuminate\Support\Facades\Auth;

use Illuminate\Http\Request;

class CRPTDisclosureController extends Controller
{
    public function index($company_id){
       /* $items = array();
        for ($i = 0; $i < 15; $i++) {
            $items[$i]['no'] = $i;
            $items[$i]['name'] = str_repeat("", $i+1);
            $items[$i]['myr'] = 0.00;
        }
        return view('crpt.disclosure.disclosure')->with(['items' => $items]);*/

        $user_id = Auth::user()->id;
        $user_roles = usersrole::where('user_id', $user_id)->get();
        $is_king =  Company::where('owner_user_id', Auth::user()->id)->get();
    	return view('crpt.disclosure.disclosure', compact('user_roles', 'is_king'));
    }
}
