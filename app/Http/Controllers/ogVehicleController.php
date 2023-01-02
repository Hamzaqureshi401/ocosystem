<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Validator;
use App\Classes\UserData;
use \App\Classes\SystemID;
use Yajra\DataTables\DataTables;
use DB;
use Log;
use \App\User;
class ogVehicleController extends Controller
{
	public $user_data = null;

	function __construct() {
	}

	public function og_vehicleMgmt_tableData(Request $request) {
		try {
		
			$user_data = new UserData();

			$data = DB::table('og_vehicle')->
				where('og_vehicle.merchant_id', $user_data->company_id())->
				whereNull('og_vehicle.deleted_at')->
				get();

			return Datatables::of($data)->
				addIndexColumn()->

				addColumn('number', function ($data) {
					$num_plate =  escapeDefault($data->number_plate, "Number Plate");
					return <<<EOD
					<span class="os-linkcolor" data-toggle="modal" data-target="#vehicle_modal"
							style="cursor:pointer" onclick="ogVehicleNumberUpdate($data->id, '$data->number_plate')">$num_plate</span>
EOD;
				})->

				addColumn('ownership', function ($data) {
					return escapeDefault(ucfirst($data->ownership), "Own");
				})->

				addColumn('deliveryman_user_id', function ($data) {
				//	$dilveryman = escapeDefault($data->deliveryman_user_id, "Deliveryman");
					if (!empty($data->deliveryman_user_id)) {
						$deliveryman = User::find($data->deliveryman_user_id)->name;
					} else {
						$deliveryman = "Deliveryman";
					}
					return <<<EOD
					<span class="os-linkcolor" style="cursor:pointer" 
						onclick="deliverymanSelectFunc('$data->deliveryman_user_id', $data->id)">$deliveryman</span>
EOD;
				})->

				addColumn('c1_max', function ($data) {
					$c1_max = escapeDefault($data->c1_max, '0');
					return <<< EOD
						<div id="masterBackBar">
						<div id="myBar"
							style="background-color:green;width:10%;">
						</div>
						<div id="myProgress">
							<span id="c1">$c1_max</span>&nbsp;&ell;
						</div>
					</div>

EOD;
				})->

				addColumn('c2_max', function ($data) {
					$c2_max = escapeDefault($data->c2_max, '0');
					return <<< EOD
					<div id="masterBackBar">
						<div id="myBar"
							style="background-color:green;width:10%;">
						</div>
						<div id="myProgress">
							<span id="c2">$c2_max</span>&nbsp;&ell;
						</div>
					</div>
EOD;
				})->

				addColumn('c3_max', function ($data) {
					$c3_max = escapeDefault($data->c3_max, '0');
					return <<< EOD
					<div id="masterBackBar">
						<div id="myBar"
							style="background-color:green;width:10%;">
						</div>
						<div id="myProgress">
							<span id="c3">$c3_max</span>&nbsp;&ell;
						</div>
					</div>
EOD;
				})->

				addColumn('c4_max', function ($data) {
					$c4_max = escapeDefault($data->c4_max, '0');
					return <<< EOD
					<div id="masterBackBar">
						<div id="myBar"
							style="background-color:green;width:10%;">
						</div>
						<div id="myProgress">
							<span id="c4">$c4_max</span>&nbsp;&ell;
						</div>
					</div>
EOD;
				})->

				addColumn('c5_max', function ($data) {
					$c5_max = escapeDefault($data->c5_max, '0');
					return <<< EOD
					
					<div id="masterBackBar">
						<div id="myBar"
							style="background-color:green;width:10%;">
						</div>
						<div id="myProgress">
							<span id="c5">$c5_max</span>&nbsp;&ell;
						</div>
					</div>
EOD;
				})->

				addColumn('c6_max', function ($data) {
					$c6_max = escapeDefault($data->c6_max, '0');
					return <<< EOD
					<div id="masterBackBar">
						<div id="myBar"
							style="background-color:green;width:10%;">
						</div>
						<div id="myProgress">
							<span id="c6">$c6_max</span>&nbsp;&ell;
						</div>
					</div>
EOD;
				})->

				addColumn('c7_max', function ($data) {
					$c7_max = escapeDefault($data->c7_max, '0');
					return <<< EOD
					<div id="masterBackBar">
						<div id="myBar"
							style="background-color:green;width:10%;">
						</div>
						<div id="myProgress">
							<span id="c7">$c7_max</span>&nbsp;&ell;
						</div>
					</div>
EOD;
				})->

				addColumn('c8_max', function ($data) {
					$c8_max = escapeDefault($data->c8_max, '0');
					return <<< EOD
					<div id="masterBackBar">
						<div id="myBar"
							style="background-color:green;width:10%;">
						</div>
						<div id="myProgress">
							<span id="c8">$c8_max</span>&nbsp;&ell;
						</div>
					</div>
EOD;
				})->

				addColumn('map',function($data) {
					return <<< EOD
					<img style="width:25px;height:25px;cursor:pointer"
						id="fleetmgmt_btn" 
						class="mt=0 mb-0 text-center" data-toggle="modal"
						data-target="#fleetmgmt_modal" 
						src="/images/yellowcrab_25x25.png">
EOD;
				})->

				addColumn('blueCrab',function($data) {
					return <<< EOD
					<img style="width:25px;height:25px;cursor:pointer"
						class="mt=0 mb-0 text-center" 
						src="/images/bluecrab_25x25.png">
EOD;
				})->

				addColumn('redCrab',function($data) {
					return <<< EOD
					<img style="width:25px;height:25px;cursor:pointer"
						class="mt=0 mb-0 text-center" 
						onclick="delete_deliverymanFunc($data->id)"
						src="/images/redcrab_25x25.png">
EOD;
				})->

				escapeColumns([])->
				make(true);

		} catch (\Exception $e) {
			Log::error([
				"Error" 	=> $e->getMessage(),
				"File"  	=> $e->getFile(),
				"Line No" 	=> $e->getLine()
			]);
		}
	}

	public function og_newVehicle(Request $request) {
		try {
			
			$user_data = new UserData();
			
			$compartment_details = $request->compt_details;
			
			$insert_data = [];
			$insert_data['systemid'] = new SystemID('vehicle');
			$insert_data['merchant_id'] = $user_data->company_id();	
			$insert_data["created_at"] = date("Y-m-d H:i:s");	
			$insert_data["updated_at"] = date("Y-m-d H:i:s");	

			for ($x = 0; $x < 8; $x++ ) {
				
				if (empty($compartment_details[$x])) {
					continue;
				}
				$key = "c".($x + 1)."_max";
				$insert_data[$key] = $compartment_details[$x];
			}
			
			DB::table('og_vehicle')->insert($insert_data);
			
			$msg = "Vehicle added";
			return view('layouts/dialog', compact('msg'));

		} catch (\Exception $e) {
			
			Log::info([
				"Error" 	=> $e->getMessage(),
				"File"  	=> $e->getFile(),
				"Line No" 	=> $e->getLine()
			]);

			abort(404);
		}
	}

	function get_deliveryman_dialog(Request $request) {
		try {
		
			$user_data = new UserData();
			
			$deliveryman_list = DB::table('users')->
				join('usersfunction','usersfunction.user_id','=','users.id')->
				join('function','function.id','=','usersfunction.function_id')->
				where('usersfunction.company_id',$user_data->company_id())->
				where('function.slug','dlv')->
				select('users.*')->
				get();
			
			$select_id = $request->selected_id ?? null;
			$fKey = $request->fKey ?? null;

			return view('user.deliveryman', compact('select_id', 'deliveryman_list', 'fKey'));

		} catch (\Exception $e) {
	
			Log::info([
				"Error" 	=> $e->getMessage(),
				"File"  	=> $e->getFile(),
				"Line No" 	=> $e->getLine()
			]);

			abort(404);

		}
	}

	function selectDeliveryman(Request $request) {
		try {
			
			
			$allInputs = $request->all();
			$validation = Validator::make($allInputs, [
				'user_id' => 'required',
				'ogVehicle_id' => 'required',
			]);
			
			if ($validation->fails()) {
				throw new Exception("Validation Error", 1);
			}

			DB::table("og_vehicle")->
				where('id', $request->ogVehicle_id)->
				update([
					"deliveryman_user_id"	=>	$request->user_id
				]);

			$msg = "Deliverman updated";
			return view('layouts/dialog', compact('msg'));
	
		} catch (\Exception $e) {
	
			Log::info([
				"Error" 	=> $e->getMessage(),
				"File"  	=> $e->getFile(),
				"Line No" 	=> $e->getLine()
			]);

			abort(404);


		}
	}

	public function delete_delivaryman(Request $request) {
		try  {

			$allInputs = $request->all();
			$validation = Validator::make($allInputs, [
				'ogVehicle_id' => 'required'
			]);
			
			if ($validation->fails()) {
				throw new Exception("Validation Error", 1);
			}

			
			DB::table("og_vehicle")->
				where('id', $request->ogVehicle_id)->
				update([
					"deleted_at"	=>	date("Y-m-d H:i:s")
				]);


			$msg = "Deliverman deleted";
			return view('layouts/dialog', compact('msg'));
		} catch (\Exception $e) {
	
			Log::info([
				"Error" 	=> $e->getMessage(),
				"File"  	=> $e->getFile(),
				"Line No" 	=> $e->getLine()
			]);

			abort(404);
		}

	}

	public function updateNumberPlate(Request $request){
		try {
			$allInputs = $request->all();
			$validation = Validator::make($allInputs, [
				'ogVehicle_id' => 'required',
				'data' => 'required'
			]);
			
			if ($validation->fails()) {
				throw new Exception("Validation Error", 1);
			}

	
			DB::table("og_vehicle")->
				where('id', $request->ogVehicle_id)->
				update([
					"number_plate"	=> $request->data
				]);


			$msg = "Number plate updated";
			return view('layouts/dialog', compact('msg'));

		} catch (\Exception $e) {
			Log::info([
				"Error" 	=> $e->getMessage(),
				"File"  	=> $e->getFile(),
				"Line No" 	=> $e->getLine()
			]);

			abort(404);
		}


	}
}
	function escapeDefault($field, $default) {
		if (empty($field)) {
			return $default;
		} 
		return $field;
	}
