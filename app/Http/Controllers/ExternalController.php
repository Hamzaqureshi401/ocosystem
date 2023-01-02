<?php

namespace App\Http\Controllers;

use \App\Classes\SystemID;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;
use \Log;
use \DB;


class ExternalController extends Controller
{
	function __construct() {
		$this->middleware('auth');
		$this->middleware('CheckRole:super');
	}

	function landing() {
		try {
			return view('external_superadmin.external');	
		} catch (\Exception $e) {
			$error = [
				"message"	=> $e->getMessage(),
				"file"		=> $e->getFile(),
				"line"		=> $e->getLine()
			]; 
			Log::error($error);
			abort(400);
		}
	}

	function mainDatatable() {
		try {
			$data = DB::table('users')->
				join('staff','staff.user_id', 'users.id')->
				where('users.type', 'external')->
				select('users.*', 'staff.systemid')->
				get();

			return Datatables::of($data)->
				addIndexColumn()->

				addColumn('systemid', function($data) {
					return $data->systemid;
				})->

				addColumn('name', function($data) {
					$name = empty($data->name) ? "User Name":$data->name;
					return <<<EOD
						<span style="cursor:pointer !important" 
						 class="os-linkcolor" onclick="edit_details($data->id)">$name</span>
EOD;
				})->

				addColumn('role', function($data) {
					return <<<EOD
							<span style="cursor:pointer !important" 
							 class="os-linkcolor" onclick="edit_role($data->id)">Role</span>
EOD;

				})->

				addColumn('status', function($data) {
					$status =	ucfirst($data->status);
					$display = empty($data->name) || empty($data->email);
					
					return $display ? $status : <<<EOD
							<span style="cursor:pointer !important" 
							 class="os-linkcolor" onclick="edit_status($data->id)">$status</span>
EOD;
				})->

				addColumn('bluecrab', function($data) {
					return <<< EOD
						<img src="/images/bluecrab_50x50.png"
							onclick="auth_location($data->id,this)"
							style="width:25px;height:25px;cursor:pointer"/>

EOD;
				})->

				addColumn('redcrab', function($data) {
			
					return <<< EOD
						<img src="/images/redcrab_50x50.png"
							onclick="delete_ext($data->id,this)"
							style="width:25px;height:25px;cursor:pointer"/>

EOD;

				})->

				escapeColumns([])->
				make(true);
		} catch (\Exception $e) {
			$error = [
				"message"	=> $e->getMessage(),
				"file"		=> $e->getFile(),
				"line"		=> $e->getLine()
			]; 
			Log::error($error);
			abort(400);
		}
	}

	function addExternalUser() {
		try {
			$SystemID        = new SystemID('individual');
			$new_user_id = DB::table('users')->insertGetId([
				'name'		 => '',
				'password'	 => \Hash::make(rand(1000,9999999)),
				'type'		 => 'external',
				"created_at" => now(),
				"updated_at" => now()	
			]);

			DB::table('staff')->insert([
				'systemid'		=> $SystemID,
				'user_id'		=> $new_user_id,
				"created_at" 	=> now(),
				"updated_at" 	=> now()	
			]);

			$msg = 'External user added';
		} catch (\Exception $e) {
			$error = [
				"message"	=> $e->getMessage(),
				"file"		=> $e->getFile(),
				"line"		=> $e->getLine()
			]; 
			Log::error($error);
			$msg = "Some error occured";
		}

		return view('layouts.dialog', compact('msg'));
	}

	function viewOceaniaAuthorization($id) {
		try {
			$selected_user = DB::table('users')->
				join('staff','staff.user_id', 'users.id')->
				where([
					'users.id'		=> $id,
					'users.type'	=> 'external'
				])->
				select('users.*', 'staff.systemid')->
				first();

			if (empty($selected_user))
				abort(404);
			
			$count_location = DB::table('location')->
				whereNotNull('location.branch')->
				get()->count();

			
			$isExist = DB::table('userslocation')->where([
						'user_id'		=> $id 
					])->get()->count();
			
			if ($count_location == $isExist) {
				$isActiveAll = 'true';
			} else {
				$isActiveAll = 'false';
			}

			return view('external_superadmin.oceania_authorization', compact('selected_user','isActiveAll'));
		} catch (\Exception $e) {
			$error = [
				"message"	=> $e->getMessage(),
				"file"		=> $e->getFile(),
				"line"		=> $e->getLine()
			]; 
			Log::error($error);
			abort(404);
		}
	}

	function oceaniaAuthorizationDatatable(Request $request) {
		try {
			$data = DB::table('location')->
				whereNotNull('location.branch')->
				get();

			return Datatables::of($data)->
				addIndexColumn()->
		
				addColumn('systemid', function($data) {
					return $data->systemid;
				})->

				
				addColumn('name', function($data) {
					return $data->branch;
				})->


				addColumn('active', function($data) {
			
					$isExist = DB::table('userslocation')->where([
						'location_id'	=> $data->id,
						'user_id'		=> request()->user_id
					])->first();

					$active = !empty($isExist) ? 'active_button_activated':'';

					$htmlTemplate = <<< EOD
						<button  
							class="prawn btn trigger_save_1 active_button $active" 
							onclick="activate_location($data->id,this)"
							style="min-width:75px">Active
						</button>
						EOD;
					
					return $htmlTemplate;
				})->

				escapeColumns([])->
				make(true);

		} catch (\Exception $e) {
			$error = [
				"message"	=> $e->getMessage(),
				"file"		=> $e->getFile(),
				"line"		=> $e->getLine()
			]; 
			Log::error($error);
			abort(404);
		}
	}

	function oceaniaAuthorizationActivateUser(Request $request) {
		try {
			
			$validation = Validator::make($request->all(), [
				'user_id'		=>	'required',
				'location_id'	=>	'required'
			]);	
			
			if ($validation->fails())
				throw new \Exception("user_id or location_id is missing");
			
			$data = [];

			$data['user_id'] 		= $request->user_id;
			$data['location_id']	= $request->location_id;

			$isExist = DB::table('userslocation')->
				where($data)->first();

			if (!empty($isExist)) {
				DB::table('userslocation')->
					where($data)->delete();
				app('\App\Http\Controllers\UserController')->
					deleteRealtimeTerminals($request->user_id, $request->location_id);
				$msg = "Location deactivated";
			} else {
				$data['created_at'] = now();
				$data['updated_at'] = now();
				DB::table('userslocation')->insert($data);
				app('\App\Http\Controllers\UserController')->
					updateRealtimeTerminals($request->user_id);
				$msg = "Location activated";
			}

		} catch (\Exception $e) {
			$error = [
				"message"	=> $e->getMessage(),
				"file"		=> $e->getFile(),
				"line"		=> $e->getLine()
			]; 
			Log::error($error);
			$msg = "Some error occured";
		}

		return view('layouts.dialog', compact('msg'));
	}

	function oceaniaAuthorizationToggleActive(Request $request) {
		try {
			
			$validation = Validator::make($request->all(), [
				'isActiveAll'	=>	'required',
				'user_id'		=>	'required',
			]);	
			
			if ($validation->fails())
				throw new \Exception("user_id or isActiveAll is missing");

			$data = DB::table('location')->
				whereNotNull('location.branch')->
				get();

			$insertData 	= [];
			$populatedLIds 	= [];
			
			foreach ($data as $location) {
				$packet = [];
				$packet['user_id'] 		= $request->user_id;
				$packet['location_id']    = $location->id;

				$isExist =	DB::table('userslocation')->
					where($packet)->first();

				if (!empty($isExist)) {
					$populatedLIds[] = $location->id;
				} else {
					if ($request->isActiveAll == 'false') {
						$packet['created_at'] = now();
						$packet['updated_at'] = now();
						$insertData[] = $packet;
					}
				}
			}

			if ($request->isActiveAll == 'false') {
				DB::table('userslocation')->insert($insertData);
				app('\App\Http\Controllers\UserController')->
					updateRealtimeTerminals($request->user_id);
				$msg = "All location activated";
			} else {
				DB::table('userslocation')->
					where('user_id', $request->user_id)->
					whereIn('location_id', $populatedLIds)->
					delete();

				app('\App\Http\Controllers\UserController')->
					deleteRealtimeTerminals($request->user_id, $populatedLIds);

				$msg = "All location deactivated";
			}

		} catch (\Exception $e) {
			$error = [
				"message"	=> $e->getMessage(),
				"file"		=> $e->getFile(),
				"line"		=> $e->getLine()
			]; 

			Log::error($error);
			$msg = "Some error occured";
		}

		return view('layouts.dialog', compact('msg'));
	}
}
