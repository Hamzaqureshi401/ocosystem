<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \App\Models\usersrole;
use \App\Models\Company;
use \App\Models\role;
use \Illuminate\Support\Facades\Auth;
use App\Models\merchantlocation;
use App\Models\location;
use App\Classes\UserData;
use Carbon\Carbon;
use DB;
use \App\Http\Controllers\AnalyticsController;
class OilGasAnalyticsController extends Controller
{
	public function index() {
		$user_data = new UserData();
		$analyticsController = new AnalyticsController;
		
		$branch_location = [];

		$get_location = $analyticsController->get_location();
		foreach ($get_location as $key => $val) {
			//$$key = $val;
			$location_id = array_column($branch_location, 'id');
			foreach($val as $location) {
				$is_t_loc = DB::table('opos_locationterminal')->
					where('location_id',$location->id)->
					whereNull('deleted_at')->first();
				
				if (!in_array($location->id,$location_id) && !empty($is_t_loc) ){
					$branch_location = array_merge($branch_location, [$location]);
				}
			}
		}
				
		$pumps = [];
		$volumes = [];
		$json_pumps = json_encode($pumps, JSON_HEX_APOS);
		$json_volumes = json_encode($volumes, JSON_HEX_APOS);
		$userApprovedDate = Company::find( $user_data->company_id() )->approved_at;
			return view('industry.oil_gas.analytics.og_pump_volume', compact('branch_location','json_pumps',
				'json_volumes','userApprovedDate'));
    }
	
	public function loadChartData(Request $request) {
		
		$user_data = new UserData();

		$all_data = DB::table('og_pumplog')->
			join('og_controller','og_controller.id','=','og_pumplog.controller_id')->
			where('og_pumplog.merchant_id',$user_data->company_id())->
			where('og_controller.company_id', $user_data->company_id())->
			whereNull('og_pumplog.deleted_at')->
			select('og_pumplog.pump','og_controller.location_id','volume',
				'og_pumplog.product','og_pumplog.created_at')->
			get();
	
		if (!empty($request->loc_id)  && $request->loc_id != 'all') {
				$all_data = $all_data->where('location_id', $request->loc_id);
				$landing = false;
		}
	
		if (!empty($request->from_date_all)) { 
			
			$dateTimeFrom = strtotime($request->from_date_all);
			
			$all_data = $all_data->filter(function($z) use ($dateTimeFrom) {
				if (strtotime($z->created_at) >= $dateTimeFrom) {
					return true;
				} else {
					return false;
				}
			});

		}

		$result = collect();
	
		$products_ = $all_data->pluck('product')->unique();
		$pump_ = $all_data->pluck('pump')->unique();

		$pump_->map(function($z) use ($all_data, $products_, $result) {
			$products_->map(function($y) use ($all_data,$z, $result){ 
				
				$cell['volume'] =  $all_data->
					where('pump',$z)->
					where('product',$y)->
					sum('volume');

					$cell['pump'] = $z;
					$cell['product'] = $y;

					$result->push(collect($cell));

			});
		});

		$pumps = [];
		$volumes = [];
		$product = [];
		
		$grouped_result  = $result->groupBy('product');
		
		foreach($grouped_result as $key => $res) {
			
			$temp = [];
			foreach($res as $pump_volume){
				$temp[$pump_volume['pump']] = sprintf("%.2f", $pump_volume['volume']);
			}

			$volumes[$key][$key] = $temp;

		}
		
		$flated_volume = [];
		foreach ($volumes as $v) {
			$flated_volume[] = $v;
		}
		
		foreach ($pump_ as $z) {
			$pumps[] = "Pump No $z";
		}
		
		$json_pumps = json_encode($pumps, JSON_HEX_APOS);
		
		$json_volumes = json_encode($flated_volume, JSON_HEX_APOS);
		
		return response()->json([
			'json_pumps' => $json_pumps,
			'json_volumes' => $json_volumes,
		]);
    }
	
}

