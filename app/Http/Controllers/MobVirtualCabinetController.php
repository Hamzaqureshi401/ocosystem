<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \App\Models\usersrole;
use \Illuminate\Support\Facades\Auth;
use \Illuminate\Support\Facades\DB;
use \App\Classes\UserData;
use \App\Models\Company;
use \App\Models\opos_eoddetails;
use \App\Models\opos_shiftdetails;
use \App\Models\opos_locationterminal;
use \Carbon\Carbon;

use \App\Http\Controllers\AnalyticsController;

class MobVirtualCabinetController extends Controller
{
    //
    public function virtual_cabinet(Request $request)
    {
        $this->user_data = new UserData();
        $id              = Auth::user()->id;

		$analyticsC = new AnalyticsController();
		$get_location = $analyticsC->get_location();

		$terminals_av = $analyticsC->excluded_term();
		$terminals_av = $terminals_av->pluck('terminal_id');
		
		$branch_location = [];
		foreach ($get_location as $key => $val) {
			$location_id = array_column($branch_location, 'id');
			foreach($val as $location) {
				
				if (!in_array($location->id,$location_id)){
					$branch_location = array_merge($branch_location, [$location]);
				}
			}
		}

		$valid_terminals = DB::table('location')->
			join('opos_locationterminal', 'opos_locationterminal.location_id', 'location.id')->
			join('merchantlocation', 'opos_locationterminal.location_id', 'merchantlocation.location_id')->
            select([
				'merchantlocation.id', 
				'location.branch', 
				'opos_locationterminal.terminal_id',
				'opos_locationterminal.location_id'
			])->
			whereIn('opos_locationterminal.terminal_id', $terminals_av)->
			whereNull('location.deleted_at')->
			whereNull('opos_locationterminal.deleted_at')->
			orderBy('location.branch', 'asc')->
			get();
	
		$branch_location = collect($branch_location);

        $newCollection = collect();

        foreach($valid_terminals as $terminal){
            $eod_details = opos_eoddetails::orderBy('id', 'desc')
                                        ->where("logterminal_id", $terminal->terminal_id)
                                        //->where("eod_id", '!=', NULL)
                                        ->where("startdate", Carbon::parse(Carbon::parse($request->startdate)->format('Y-m-d 00:00:00')))
                                        ->first();	
			
			$location_info = $branch_location->where('id', $terminal->location_id)->first();

			if (!empty($location_info->foodcourt)) {	
				if ( $location_info->foodcourt == 1) {
					$terminal->loc_type = "Foodcourt";
				} 
			}


			if (!empty($location_info->franchise)) {	
		   		if ( $location_info->franchise == 1) {
						$terminal->loc_type = "Franchise";
				} 
			}
				
			if (empty($terminal->loc_type)) {
				$terminal->loc_type = "Direct";
			}
			
			//if($eod_details){
			$terminal->eod_details = $eod_details;
			$newCollection->push($terminal);
			//}
        }

        $terminal_ids = $valid_terminals->pluck("terminal_id");

        $branch_sales = opos_eoddetails::whereIn("logterminal_id", $terminal_ids)
                                        ->where("startdate", Carbon::parse(Carbon::parse($request->startdate)->format('Y-m-d 00:00:00')))
                                        ->get()
                                        ->sum("sales");

        $valid_terminals = $newCollection->all();

		$approved_at = Company::find($this->user_data->company_id())->approved_at;
		$approved_at = empty($approved_at) ? date('Y-m-d'):$approved_at;
		return view('mob_virtualcabinet.mob_virtualcabinet', compact(['valid_terminals', 'branch_sales','approved_at']));
    }
    //
    public function virtual_cabinet_get(Request $request)
    {
        $this->user_data = new UserData();
        $id              = Auth::user()->id;

        $valid_terminals = Company::join('merchantlocation', 'company.id', 'merchantlocation.merchant_id')
                        ->join('location', 'location.id', 'merchantlocation.location_id')
                        ->join('opos_locationterminal', 'opos_locationterminal.location_id', 'location.id')
                        ->select([
                            'merchantlocation.id', 
                            'location.branch', 
                            'opos_locationterminal.terminal_id',
                        ])
                        ->where('owner_user_id', $id)
                        ->where('location.branch', '!=', NULL)
                        ->orderBy('location.branch', 'asc')
                        ->get();

        $newCollection = collect();

        foreach($valid_terminals as $terminal){
            $eod_details = opos_eoddetails::orderBy('id', 'desc')
                                        ->where("logterminal_id", $terminal->terminal_id)
                                        //->where("eod_id", '!=', NULL)
                                        ->where("startdate", Carbon::parse(Carbon::parse($request->startdate)->format('Y-m-d 00:00:00')))
                                        ->first();
            //if($eod_details){
                $terminal->eod_details = $eod_details;
                $newCollection->push($terminal);
            //}
        }

        $terminal_ids = $valid_terminals->pluck("terminal_id");

        $branch_sales = opos_eoddetails::whereIn("logterminal_id", $terminal_ids)
                                        ->where("startdate", Carbon::parse(Carbon::parse($request->startdate)->format('Y-m-d 00:00:00')))
                                        ->get()
                                        ->sum("sales");

        return response()->json([
            'branch_sales' => $branch_sales,
            'data' => $newCollection->toJson(),
            'startdate' => Carbon::parse($request->startdate)->format('Y-m-d 00:00:00')
        ]);
    }
    //
    public function virtual_cabinet_eod(Request $request)
    {

        $eod_detail = opos_eoddetails::with('opos_shiftdetails')->orderBy('id', 'desc')
                                    ->where("id", $request->id)
                                    ->first();

        $branch_details = opos_eoddetails::join('opos_locationterminal', 'opos_locationterminal.terminal_id', 'opos_eoddetails.logterminal_id')
                                    ->join('location', 'location.id', 'opos_locationterminal.location_id')
                                    ->join('opos_terminal', 'opos_locationterminal.terminal_id', 'opos_terminal.id')
                                    ->leftJoin('opos_btype', 'opos_btype.id', 'opos_terminal.btype_id')
                                    ->select([
                                        'location.branch', 
                                        'location.systemid as location_id',
                                        'opos_terminal.systemid as terminal_id',
                                        'opos_terminal.servicecharge',
                                        'opos_terminal.tax_percent',
                                        'opos_btype.btype'
                                    ])
                                    ->where("opos_eoddetails.id", $request->id)
                                    ->first();

        $location_id = opos_eoddetails::join('opos_locationterminal', 'opos_locationterminal.terminal_id', 'opos_eoddetails.logterminal_id')
                                    ->select([
                                        'opos_locationterminal.location_id as id',
                                    ])
                                    ->where("opos_eoddetails.id", $request->id)
                                    ->first()->id;

        $terminal_ids = opos_locationterminal::where("location_id", $location_id)->get()->pluck("terminal_id");

        $branch_sales = opos_eoddetails::whereIn("logterminal_id", $terminal_ids)
                                        ->where("startdate", Carbon::parse(Carbon::parse($eod_detail->startdate)->format('Y-m-d 00:00:00')))
                                        ->get()
                                        ->sum("sales");

    	return view('mob_virtualcabinet.mob_virtualcabinet_eod', compact(['eod_detail', 'branch_sales', 'branch_details']));
    }

    //
    public function virtual_cabinet_eod_shift(Request $request)
    {

        $opos_shiftdetail = opos_shiftdetails::orderBy('id', 'desc')
                                    ->where("id", $request->id)
                                    ->first();

        $branch_details = opos_shiftdetails::join('opos_eoddetails', 'opos_eoddetails.id', 'opos_shiftdetails.eoddetails_id')
                                    ->join('opos_locationterminal', 'opos_locationterminal.terminal_id', 'opos_eoddetails.logterminal_id')
                                    ->join('location', 'location.id', 'opos_locationterminal.location_id')
                                    ->join('opos_terminal', 'opos_locationterminal.terminal_id', 'opos_terminal.id')
                                    ->leftJoin('opos_btype', 'opos_btype.id', 'opos_terminal.btype_id')
                                    ->select([
                                        'location.branch', 
                                        'location.systemid as location_id',
                                        'opos_terminal.systemid as terminal_id',
                                        'opos_terminal.servicecharge',
                                        'opos_terminal.tax_percent',
                                        'opos_btype.btype'
                                    ])
                                    ->where("opos_shiftdetails.id", $request->id)
                                    ->first();

        $location_id = opos_shiftdetails::join('opos_eoddetails', 'opos_eoddetails.id', 'opos_shiftdetails.eoddetails_id')
                                    ->join('opos_locationterminal', 'opos_locationterminal.terminal_id', 'opos_eoddetails.logterminal_id')
                                    ->select([
                                        'opos_locationterminal.location_id as id',
                                    ])
                                    ->where("opos_shiftdetails.id", $request->id)
                                    ->first()->id;

        $terminal_ids = opos_locationterminal::where("location_id", $location_id)->get()->pluck("terminal_id");

        $branch_sales = opos_eoddetails::whereIn("logterminal_id", $terminal_ids)
                                        ->where("startdate", Carbon::parse(Carbon::parse($opos_shiftdetail->startdate)->format('Y-m-d 00:00:00')))
                                        ->get()
                                        ->sum("sales");
        $index = $request->index;
        return view('mob_virtualcabinet.mob_virtualcabinet_eod_shift', compact(['opos_shiftdetail', 'index', 'branch_sales', 'branch_details']));
    }
}
