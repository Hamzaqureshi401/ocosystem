<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Validator;
use \App\Classes\UserData;
use \Log;
use \DB;

class TerminalController extends Controller
{
    //
	
	public function __construct()
    {
        $this->middleware('auth',['except' => ['updateRecieptCount']]);
        $this->middleware('CheckRole:super',['except' => ['updateRecieptCount']]);
    }

	function terminalMaster() {
		return view('terminal.terminal');
	}

	function landingDatatable(Request $request) {
		try {
	/*	$dataList = DB::table('opos_terminal')->
				join('lic_terminalkey','lic_terminalkey.terminal_id','opos_terminal.id')->
				join('opos_locationterminal','opos_locationterminal.terminal_id','opos_terminal.id')->
				join('merchantlocation','merchantlocation.location_id','opos_locationterminal.location_id')->
				join(''
				join('company','company.id', 'merchantlocation.merchant_id')->
				whereNotNull('lic_terminalkey.has_setup')->
				select('company.name as cname', 'company.systemid as merid','opos_terminal.id as t_id',
					'opos_terminal.hardware_addr','opos_terminal.systemid as terminal_id')->
				orderBy('merid','desc')->
				get();
	 */
			$dataList = DB::table('opos_terminal')->
				join('lic_terminalkey','lic_terminalkey.terminal_id','opos_terminal.id')->
				join('franchisemerchantlocterm', 'franchisemerchantlocterm.terminal_id','opos_terminal.id')->
				join('franchisemerchantloc', 'franchisemerchantloc.id', 'franchisemerchantlocterm.franchisemerchantloc_id')->
				join('franchisemerchant','franchisemerchant.id', 'franchisemerchantloc.franchisemerchant_id')->	
				join('company','company.id', 'franchisemerchant.franchisee_merchant_id')->
				whereNotNull('lic_terminalkey.has_setup')->
				select('company.name as cname', 'company.systemid as merid','opos_terminal.id as t_id',
					'opos_terminal.hardware_addr','opos_terminal.systemid as terminal_id')->
				orderBy('merid','desc')->
				get();

			$dataList->map(function($z) {
				$data = DB::table('terminalcount')->
					where('terminal_id', $z->t_id)->first();

				$z->current_rcount = $data->current_rcount ?? 0;
				$z->tCount = $data->allowed_receipt_count ?? 0;
			});

			return Datatables::of($dataList)->
				addIndexColumn()->
				addColumn('termid', function ($data) {
					return $data->terminal_id;
				})->
				addColumn('merid', function ($data) {
					return $data->merid;
				})->
				addColumn('mname', function ($data) {
					return $data->cname;
				})->
				addColumn('hw', function ($data) {
					return $data->hardware_addr;
				})->
				addColumn('count', function ($data) {
					return $data->current_rcount;
				})->
				addColumn('reset', function ($data) {

					return <<<EOD
					<img src="/images/pinkcrab_50x50.png"
						onclick="reset_confirm.display_confirm_reset($data->t_id)"
						style="width:25px;height:25px;cursor:pointer"/>
EOD;
				})->
				addColumn('threshold', function ($data) {
					$t_id = $data->t_id;

					$count =  $data->tCount;
					return <<< EOD
						<span class="os-linkcolor" onclick="threshold.showModal($t_id, $count)"
							style="cursor:pointer">$count</span>
EOD;
				})->
				escapeColumns([])->
				make(true);

		} catch (\Exception $e) {
			Log::error(
				"Error:" . $e->getMessage() . 
				"@". $e->getFile() .
				":". $e->getLine()
			);

			abort(500);
		}
	} 

	function updateTCount(Request $request) {
		try {

			$validation = Validator::make($request->all(), [
				"terminal_id"	=>	"required",
				"count"			=>	"required"
			]);
	
			if ($validation->fails())
				throw new \Exception("Validation failed");

			$array = [];

			$array['terminal_id'] 				= $request->terminal_id;

			$is_exist = DB::table('terminalcount')->
				where($array)->
				first();

			if (empty($is_exist)) {
				$array['allowed_receipt_count']		= $request->count;
				$array['created_at']		= now();
				$array['updated_at']		= now();
				DB::table('terminalcount')->insert($array);
			} else {
				$array_u = $array;
				$array_u['allowed_receipt_count']		= $request->count;
				$array_u['updated_at']		= now();
   				DB::table('terminalcount')->
					where($array)->
					update($array_u);
			}

			$this->syncTCount($request->terminal_id);
			$msg = "Threshold updated";
			return view('layouts.dialog', compact('msg'))->render();
		} catch (\Exception $e) {
			Log::error(
				"Error:" . $e->getMessage() . 
				"@". $e->getFile() .
				":". $e->getLine()
			);

			abort(500);
		}
	} 

	public function syncTCount($terminal_id) {
		try {
			\Log::info("###### Threshold Count Sync ###########");
			$terminal_data = DB::table('terminalcount')->
				join('opos_terminal','opos_terminal.id','terminalcount.terminal_id')->
				where('opos_terminal.id', $terminal_id)->
				select("terminalcount.*", "opos_terminal.systemid")->
				get()->toArray();

			$post = [
				'terminalcount' => json_encode($terminal_data)
			];

			\Log::info("POST DATA => " .!empty($post));

			$ipAddr = DB::table('terminalipaddr')->
				where('terminal_id', $terminal_id)->
				get();

			\Log::info("terminalipaddr count: ".$ipAddr->count());
			
			foreach($ipAddr as $t) {
				if (!empty($t->tsystem)) {
					$url = "http://$t->tsystem/interface/update_data";
					$cURLConnection = curl_init($url);
					curl_setopt($cURLConnection, CURLOPT_POSTFIELDS, $post);
					curl_setopt($cURLConnection, CURLOPT_RETURNTRANSFER, true);
					curl_setopt($cURLConnection, CURLOPT_SSL_VERIFYPEER, false);
					curl_setopt($cURLConnection, CURLOPT_SSL_VERIFYHOST, false);
					$apiResponse = curl_exec($cURLConnection);
					curl_close($cURLConnection);
					$data = json_decode($apiResponse, true);

					\Log::info([
						"url"		=> $url,
						"response"	=> $apiResponse
					]);
				}

			}


		}  catch (\Exception $e) {
			Log::error(
				"Error:" . $e->getMessage() . 
				"@". $e->getFile() .
				":". $e->getLine()
			);

			abort(500);
		}
	}
	
	function resetHardwareTerminal(Request $request) {
		try {
			$user_data = new UserData();	
			$post = [];
			
			Log::info('###### resetHardware(Request $request) #################');
			Log::info([
				"terminal_id" => $request->terminal_id,
				"location_id" => $request->location_id
			]);

			if (!empty ($request->terminal_id)) {	
				$terminal_data = DB::table('opos_terminal')->
					where('id', $request->terminal_id)->
					first();

				$ipAddr = DB::table('terminalipaddr')->
					where([
						'terminal_id'	=>	$request->terminal_id,
						'company_id'	=>	$user_data->company_id()
					])->
					limit(1)->orderBy('created_at', 'desc')->
					get();

				$post['terminal_systemid'] = $terminal_data->systemid;

			} elseif (!empty($request->location_id)) {

				$location_data = DB::table('location')->
					where('id', $request->location_id)->
					first();
			
				$ipAddr = DB::table('locationipaddr')->
					where([
							'location_id'	=>	 $request->location_id,
							'company_id'	=>	$user_data->company_id()
					])->
					limit(1)->orderBy('created_at', 'desc')->
					get();

				$post['location_systemid'] = $location_data->systemid;

			} else {
				throw new \Exception("validation failed, expecting location_id or terminal_id");
			}

			if ($ipAddr->isEmpty())
				$msg = "No remote system available";

			\Log::info($post);
			\Log::info("ipAddr => " . $ipAddr->count());
			foreach($ipAddr as $t) {
				if (!empty($t->ipaddr)) {
					$url = "http://$t->ipaddr/terminal/reset_hardware";
					$cURLConnection = curl_init($url);
					curl_setopt($cURLConnection, CURLOPT_POSTFIELDS, $post);
					curl_setopt($cURLConnection, CURLOPT_RETURNTRANSFER, true);
					curl_setopt($cURLConnection, CURLOPT_SSL_VERIFYPEER, false);
					curl_setopt($cURLConnection, CURLOPT_SSL_VERIFYHOST, false);
					$apiResponse = curl_exec($cURLConnection);
					curl_close($cURLConnection);
					$data = json_decode($apiResponse, true);

					if (empty($data['error'])) {

						if (!empty ($request->terminal_id)) {	
							DB::table('opos_terminal')->
								where('id', $request->terminal_id)->
								update([
									'hardware_addr'	=>	$data['hw_addr'],
									'updated_at'	=>	now()	
								]);
						} elseif (!empty($request->location_id)) {
							
							DB::table('serveraddr')->
								where([
									'location_id' => $request->location_id,
									'ip_addr'	=>	$t->ipaddr
								])->
								update([
									'hw_addr'	=>	$data['hw_addr'],	
									'updated_at'	=>	now()	
								]);
						}
						
						$msg = 'Hardware updated';
					} else {
						$msg = $data['error'];
					}

					\Log::info([
						"url"		=> $url,
						"response"	=> $apiResponse
					]);
				}
			}

			$msg = $msg ?? "Some error occured";
			return view('layouts.dialog', compact('msg'));

		}  catch (\Exception $e) {
			Log::error(
				"Error:" . $e->getMessage() . 
				"@". $e->getFile() .
				":". $e->getLine()
			);

			abort(500);
		}

	}

	function updateRecieptCount(Request $request) {
		try {
			
			$validation = Validator::make($request->all(), [
				"location_systemid"	=>	"required",
				"company_systemid"	=>	"required",
				"terminalcount"		=>	"required",
				"api_key"			=>	"required",
			]);
	
			if ($validation->fails())
				throw new \Exception("Validation failed");

			$company_exist = DB::table('company')->
				where([
					'systemid' => $request->company_systemid
				])->
				first();

			$location_data = DB::table('location')->
				where("systemid" , $request->location_systemid)->
                first();

 			if (empty($company_exist) || empty($location_data) || empty($request->api_key))
				throw new \Exception("Invalid data");

			app('App\Http\Controllers\LocalAccessController')->
				verifyAPIKey($company_exist->systemid, $location_data->id, $request->api_key);

			$terminalcount = json_decode($request->terminalcount, true);

			foreach( $terminalcount as $terminal) {

				$terminal['terminal_id'] = DB::table('opos_terminal')->
					where('systemid', $terminal['systemid'])->
					first()->id;

				unset($terminal['systemid']);

				$terminal_condition = [
					'terminal_id'	=> $terminal['terminal_id']
				];

				app('App\Http\Controllers\APIFcController')->
					updateOrInsert('terminalcount', $terminal_condition, $terminal);
			}

			$return = ['status' => "Count updated"];
		}  catch (\Exception $e) {
			Log::error(
				"Error:" . $e->getMessage() . 
				"@". $e->getFile() .
				":". $e->getLine()
			);
			
			$return = ['error' => $e->getMessage()];
		}

		\Log::info($return);

		return $return;
	}

}
