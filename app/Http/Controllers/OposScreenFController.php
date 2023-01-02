<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Matrix\Exception;
use Yajra\DataTables\DataTables;
use \App\Classes\SystemID;
use \App\Classes\UserData;
use \App\Models\location;
use \App\Models\locationterminal;
use \App\Models\membership;
use \App\Models\merchantlocation;
use \App\Models\merchantproduct;
use \App\Models\prd_inventory;
use \App\Models\product;
use \App\Models\restaurant;
use \App\Models\terminal;
use \App\Models\usersrole;
use \App\Models\voucher;
use \App\Models\warranty;
use \Illuminate\Support\Facades\Auth;
use App\Models\Company;


use \App\Models\prd_special;
use \App\Models\productspecial;
class OposScreenFController extends Controller
{
    public function index($terminal_id='')
    {
        
        //Auth::user()->last_login = date('Y-m-d');
        $login_time = \Session::get('login_time');
        $login_time = \Carbon\Carbon::create($login_time)->
            format('dMy h:m:s');
        $terminal = terminal::where('systemid', $terminal_id)->first();
        $branch   = location::find(
            locationterminal::where('terminal_id',
                $terminal->id)->first())->first()->branch;
        $optHour = location::find(locationterminal::where('terminal_id', $terminal->id)->first())->first();

        return view('opossum.opossum_f',
            compact('terminal', 'branch', 'login_time', 'optHour'));
        
        
    }
}
