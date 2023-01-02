<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\usersrole;
use App\Models\role;
use Yajra\DataTables\DataTables;
use App\Models\Company;

class ConsignmentLocationController extends Controller
{
    public function consignmentview()
    {
		$user_id = Auth::user()->id;
    	$user_roles = usersrole::where('user_id',$user_id)->get();
    	$is_king =  Company::where('owner_user_id',
			Auth::user()->id)->get();
    	return view('consignment_location.consignment_location',
			compact('user_roles','is_king'));
	}
}
