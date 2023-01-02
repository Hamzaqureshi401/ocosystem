<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \Illuminate\Support\Facades\Auth;

use \App\Models\role;
use \App\Classes\UserData;
use \App\Models\usersrole;

class HcapAttendanceController extends Controller
{
    /**
    * Show attendance report 
    * table view
    */
    public function showAttendanceReportTableView(){
        return view('hcap_attendance.attendance');
    }

    /**
    * show attendance day
    * report view
    */
    public function showAttendanceDayReportTableView(){
        $id = Auth::user()->id;
        $user_data = new UserData();
        $user_data->exit_merchant();

        $user_roles = usersrole::where('user_id',$id)->get();

        $is_king =  \App\Models\Company::where('owner_user_id',Auth::user()->id)->first();

        if ($is_king != null) {
            $is_king = true;    
        } else {
            $is_king  = false;
        }

        if (!$user_data->company_id()) {
            abort(404);
        }
        
    	return view('hcap_attendance.day_attendance', compact('user_roles','is_king'));
    }
}
