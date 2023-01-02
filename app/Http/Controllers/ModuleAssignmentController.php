<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

use Matrix\Exception;
use \Log;

use Yajra\DataTables\DataTables;
use \App\Classes\SystemID;
use \App\Classes\UserData;

use App\Models\Staff;
use App\Models\role;
use App\Models\usersrole;
use \App\Models\globaldata;
use App\User;

class ModuleAssignmentController extends Controller
{
	
	function __construct() {
		$this->middleware('auth');
		$this->middleware('CheckRole:super');
		$this->middleware('CheckRole:mrc');
	}

	public function landing($id) {

		$merchant = DB::table('company')->whereNull('deleted_at')->find($id);
		/*
		$merchant = DB::table('company')->
			whereNull('deleted_at')->
			where('systemid',$id)->
			first();
		*/

		Log::debug('id='.$id);
		Log::debug('merchant='.json_encode($merchant));

		return view('merchant.module_assignment', compact([
			'id', 'merchant'
		]));
	}


	public function load_table(Request $request) {
		try {
			$merchant_id = $request->merchant_id;
			
			$multiple = DB::table('module')->
				whereNull('module.deleted_at')->
				join('mobmodule', 'module.slug','=','mobmodule.slug')->
				whereNull('mobmodule.deleted_at')->
				get();
			
			$multiple->map(function($z) {
				$z->module_id = DB::table('module')->
					where('slug',$z->slug)->
					whereNull('deleted_at')->
					first()->id;

				$z->mobmodule_id = DB::table('mobmodule')->
					where('slug', $z->slug)->
					whereNull('deleted_at')->
					first()->id;
			});
		
			$mob_role = DB::table('mobmodule')->
				whereNull('deleted_at')->
				whereNotIn('slug', $multiple->pluck('slug'))->
				get();
		
			$mob_role->map(function($z) {
				$z->mobmodule_id = DB::table('mobmodule')->
					where('slug', $z->slug)->
					whereNull('deleted_at')->
					first()->id;
			});

			$role = DB::table('module')->
				whereNull('deleted_at')->
				whereNotIn('slug',$multiple->pluck('slug'))->
				get();

			$role->map(function($z) {
				$z->module_id = DB::table('module')->
					where('slug',$z->slug)->
					whereNull('deleted_at')->
					first()->id;
			});

			$data = $role->merge($multiple);
			$data = $data->merge($mob_role);

			
			$prefer = ["stg","loc",
				"data",
				'dmgmt','alli','umgmt','fnch','prcu',"csgn",
				"prod",
				"ana",
				'cash','stk','oper','job',
				"rpt",
				'grp','conso','crep','rvpy',
				"vcab",
				'auto','man','pgn',
				"ind", 
				'oilg','mall','autm','ecom','insr',
				"comm", 
				"snm",
				'ast','tts','repr','wrnt','cmr','csr','cpcr',
				"prdt","dist",
				'cadm','logs','vhc','dlvr','drum',
				"ret",
				'mbr','opos','indt',
				"crm","humn",
				'stf','schd','attd'];
			$z = collect();

			foreach($prefer as $pr) {
				foreach($data as  $key => $d) {
					if ($d->slug == $pr) {
						$z->push($d);
						$data->forget($key);
					}
				}
			}

			$data = $z->merge($data);
	
			foreach($data as $z) {
				
				if (!empty($z->module_id)) {
					$z->is_web_active = !empty(
						DB::table('merchantmodule')->
						where([
							'module_id' => $z->module_id,
							'merchant_id' => $merchant_id
						])->
						whereNull('deleted_at')->
						first()
					);
				}

				if (!empty($z->mobmodule_id)) {
					$z->is_mob_active = !empty(
						DB::table('merchantmobmodule')->
						where([
							'mobmodule_id' => $z->mobmodule_id,
							'merchant_id' => $merchant_id
						])->
						whereNull('deleted_at')->
						first()
					);
				}

				$z->merchant_id = $merchant_id;
			}

			return Datatables::of($data)->
				addIndexColumn()->
				addColumn('name', function ($data) {
					return $data->description;
				})->

				addColumn('web', function($data) {
					if (!empty($data->module_id)) {
						$active = $data->is_web_active ? 'active_button_activated':'';
						$htmlTemplate = <<< EOD
						<button  
							class="prawn btn trigger_save_1 active_button $active" 
							onclick="activate_role($data->module_id,'web',this)"
							merchant-id="$data->merchant_id"
							style="min-width:75px">Active
						</button>
						EOD;

					} else {
						$htmlTemplate = '';
					}
					return $htmlTemplate;
				})->

				addColumn('mob', function($data) {
					if (!empty($data->mobmodule_id)) {
						$active = $data->is_mob_active ? 'active_button_activated':'';
						$htmlTemplate = <<< EOD
						<button  
							class="prawn btn trigger_save_1 active_button $active" 
							onclick="activate_role($data->mobmodule_id,'mob',this)"
							merchant-id="$data->merchant_id"
							style="min-width:75px">Active
						</button>
						EOD;

					} else {
						$htmlTemplate = '';
					}

					return $htmlTemplate;
				})->
				escapeColumns([])->
				make(true);

		} catch (\Exception $e) {
			Log::info($e);
			abort(404);
		}
	}
	

	public function toggle_rule(Request $request) {
		try {

			$merchant_id = $request->merchant_id;
			$role_id = $request->id;
			$type = $request->type;

			if ($type == 'web') {
				
				$role_table = DB::table('module');
				$container = DB::table('merchantmodule');
				$row_name = "module_id";

			} else if ($type == 'mob') {

				$role_table = DB::table('mobmodule');
				$container = DB::table('merchantmobmodule');
				$row_name = "mobmodule_id";

			} else {
				throw new \Exception('Invalid type');
			}
			
			$is_role_exist = $role_table
					->where('id',$role_id)
					->whereNull('deleted_at')
					->first();

			if (empty($is_role_exist)) {
				throw new \Exception("Role doesn't exist");
			}

			$role_state = $container->
				where([
					$row_name => $is_role_exist->id,
					"merchant_id" => $merchant_id
				])->
				whereNull('deleted_at')->
				first();

			if (empty($role_state)) {
				$container->
					insert([
					$row_name => $is_role_exist->id,
					"merchant_id" => $merchant_id,
					"created_at" => date("Y-m-d H:m:s")
				]);
			} else {

				$container->
					where([
						$row_name => $is_role_exist->id,
						"merchant_id" => $merchant_id,
					])->
					whereNull('deleted_at')->
					update([
						"deleted_at" => date("Y-m-d H:m:s")
					]);
			}

			return response()->json(["done" => "true"]);

		} catch (\Exception $e) {
			Log::info($e);
			abort(404);
		}
	}
}
