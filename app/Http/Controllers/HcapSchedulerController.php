<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\extraFunctions\functionsClass;

class HcapSchedulerController extends Controller
{
	public function __construct() {
        $this->middleware('auth');
    }
    
    public function showshedulerView(){
    	$data['tableTitle']		=	'Manager Scheduler';
    	$data['month'] 			=	 functionsClass::monthNameShort();
    	$data['tableData'] 		= 	 functionsClass::dumyData();	
    	return view('hcap_scheduler.shedulerManager',$data);
    }
}
