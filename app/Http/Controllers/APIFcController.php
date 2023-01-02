<?php

namespace App\Http\Controllers;

use Log;
use \App\Classes\UserData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
class APIFcController extends Controller
{
	protected $init_company 	= false;
	protected $dest_location 	= null;
	protected $src_location 	= null;
	protected $target_table		= [
		'controller'		=> 	'local_controller',
		'pump'		 		=> 	'local_pump',
		'nozzle'	 		=>	'local_pumpnozzle',
		'pts_baudrate' 		=>	'local_pts2_baudrate',
		'pts_protocol'		=>	'local_pts2_protocol',
		'probe_protocol'	=>	'local_probe_protocol',
		'terminalcount'		=>	'terminalcount'
	];
	protected $src_table		= [
		'controller' 		=>	'og_controller',
		'pump'				=>	'og_pump',
		'nozzle'			=>	'og_pumpnozzle',
		'pts_baudrate'		=>	'og_pts2_baudrate',
		'pts_protocol'		=>	'og_pts2_protocol',
		'probe_protocol'	=>	'og_probe_protocol',
		'terminalcount'		=>	'terminalcount'
	];

	protected $api_except_route = ['send_payload','send_payload_custom','user.location.auth', 
		'terminal.r_count', 'product.updatecustom', 'product.savePicture'];

	/*
	 * Json Structure (Request):
	 * [
	 * 		[
	 * 			{
	 * 				controller: {..}, 
	 * 				pump: [ 
	 *		 			{
	 *		 			pump: {..}, 
	 *		 			nozzle: [
	 *		 				{..}, 
	 *		 				{..}
	 *		 			]
	 *		 			}
	 * 				]
	 * 			}
	 * 		],
	 * 		[...]
	 * 	]
	 */

	public function __construct(){
		try {
		
			$routeName = request()->route()->getName();
			/*	
			if (!in_array($routeName, $this->api_except_route)) {		
				if (empty(request()->header('X-API-KEY-FC')))
					throw new \Exception("Security key not found");

				if (!$this->auth_key(request()->header('X-API-KEY-FC')))
					throw new \Exception("Invalid security key");
			}*/

		} catch (\Exception $e) {
			$this->handleError($e);
		}
	}

	/*
	 *
	 * Misc Functions
	 *
	*/

	function sendPayload(Request $request) {
		try {
			$user_data  = new UserData();
			$validation = Validator::make($request->all(), [
				"location_id"	=>	"required"
			]);

			if ($validation->fails())
				throw new \Exception("packet data is missing", 400);

			$packets = [];

			$controllerTable = $this->getSrcTable('controller');
			$pumpTable = $this->getSrcTable('pump');
			$nozzleTable = $this->getSrcTable('nozzle');

			$controllerData = $controllerTable->where([
				'location_id' => $request->location_id,
				'company_id'  => $user_data->company_id()
			])->get();
			
			foreach($controllerData as $controller) {
				$pumpData = $pumpTable->where('controller_id', $controller->id)->
					get();
				
				$pumpLevel = [];
				foreach($pumpData as $pump) {

					$nozzleData = $nozzleTable->where('og_pumpnozzle.pump_id', $pump->id)->
						join('prd_ogfuel','prd_ogfuel.id','og_pumpnozzle.ogfuel_id')->
						join('product','product.id','prd_ogfuel.product_id')->
						select('og_pumpnozzle.*','product.systemid as p_systemid')->
						get();

					$pumpLevel[] = ["pump" => $pump, "nozzle" => $nozzleData];
					$nozzleTable = $this->getSrcTable('nozzle'); //reset var
				}

				$packets[] = ["controller" => $controller, "pump" => $pumpLevel];
			}

			$baudrate = $this->getSrcTable('pts_baudrate')->get();
			$pts_protocol = $this->getSrcTable('pts_protocol')->get();
			$probe_protocol = $this->getSrcTable('probe_protocol')->get();

			//non api process
			$this->init_company = DB::table('company')->
				where('id', $user_data->company_id())->
				first();

			$this->dest_location = DB::table('location')->
				where('id',$request->location_id)->
				first();
			
			$new_request = new Request();
			$new_request->packets 			= json_encode($packets);
			$new_request->baudrate	 		= json_encode($baudrate);
			$new_request->pts_protocol		= json_encode($pts_protocol);
			$new_request->probe_protocol	= json_encode($probe_protocol);
			$this->push_fc($new_request);
				
			//API PROCESS
			$location_F_product_ids = DB::table('franchiseproduct')->
				join('franchisemerchant','franchisemerchant.franchise_id','franchiseproduct.franchise_id')->
				join('franchisemerchantloc', 'franchisemerchantloc.franchisemerchant_id','franchisemerchant.id')->
				where([
					'franchisemerchant.franchisee_merchant_id'	=>	$user_data->company_id(),
					'franchisemerchantloc.location_id'			=>	$request->location_id
				])->select('franchiseproduct.*')->
				get()->
				pluck('product_id')->unique();

			Log::debug('LFP location_F_product_ids='.$location_F_product_ids);
				
			$products = DB::table('product')->
				join('merchantproduct','merchantproduct.product_id','product.id')->
				where([
					'merchantproduct.merchant_id' => $user_data->company_id(),
				])->
				orWhereIn('product.id',$location_F_product_ids)->
				whereIn('product.ptype', ['oilgas','inventory','services'])->
				select('product.*')->
				get();

			Log::debug('LFP products='.json_encode($products));

			$thumbnailData = $this->generateThumbnailContent($products);

			Log::debug('LFP thumbnailData='.json_encode($thumbnailData));

			$og_localfuelprice = collect(); 
			
			$products->map(function($prd) use ($og_localfuelprice) {
				$price = DB::table('og_fuelprice')->
					join('prd_ogfuel','prd_ogfuel.id','og_fuelprice.ogfuel_id')->
					join('product','product.id','prd_ogfuel.product_id')->
					leftjoin('staff', 'staff.user_id', 'og_fuelprice.user_id')->
					where('prd_ogfuel.product_id', $prd->id)->
					whereDate('og_fuelprice.start', '<=', \Carbon\Carbon::now())->
					orderBy('og_fuelprice.start', 'desc')->
					select('og_fuelprice.*','product.systemid', 'staff.systemid as user_systemid')->
					first();

				if (!empty($price))
					$og_localfuelprice->push($price);
			});

			\Log::info("LFP og_localfuelprice=>".json_encode($og_localfuelprice));

			$locationPrice = DB::table('franchiseproduct')->
				join('franchisemerchant','franchisemerchant.franchise_id','franchiseproduct.franchise_id')->
				join('franchisemerchantloc', 'franchisemerchantloc.franchisemerchant_id','franchisemerchant.id')->
				join('product','product.id','franchiseproduct.product_id')->
				where([
					'franchisemerchant.franchisee_merchant_id'	=>	$user_data->company_id(),
					'franchisemerchantloc.location_id'			=>	$request->location_id
				])->
				whereIn('product.ptype', ['inventory'])->
				select("franchiseproduct.*","product.systemid")->
				get();
		/*
		 	$local_fuelPrice = DB::table('og_localfuelprice')->
				join('prd_ogfuel','prd_ogfuel.id','og_localfuelprice.ogfuel_id')->
				join('product','product.id','prd_ogfuel.product_id')->
				whereIn('prd_ogfuel.product_id', $products->pluck('id'))->
				where([
					'og_localfuelprice.company_id'	=> $user_data->company_id(),
					'og_localfuelprice.location_id' => $request->location_id
				])->
				select('og_localfuelprice.*','product.systemid')->
				get();

			\Log::info("LFP id=".$products->pluck('id'));
			\Log::info("LFP company_id=".$user_data->company_id());
			\Log::info("LFP location_id=".$request->location_id);
			\Log::info("LFP local_fuelPrice=>".json_encode($local_fuelPrice));

			$og_localfuelprice = $og_localfuelprice->merge($local_fuelPrice);
		 */

			$new_request = [
				'packets' 			=> json_encode($packets),
				'baudrate'	 		=> json_encode($baudrate),
				'pts_protocol'		=> json_encode($pts_protocol),
				'probe_protocol'	=> json_encode($probe_protocol),
				'products'			=> json_encode($products),
				'thumbnailData'		=> json_encode($thumbnailData),
				'og_localfuelprice' => json_encode($og_localfuelprice),
				'locationPrice'		=> json_encode($locationPrice)
			];

			$location_data = DB::table('locationipaddr')->
				where('location_id',$request->location_id)->
				get();

			foreach ($location_data as $l) {
				if (!empty($l->ipaddr))
					$this->init_send_data_via_api($l->ipaddr,$new_request);
			}
			
			return response()->json(["status" => "done"]);

		} catch (\Exception $e) {
			$this->handleError($e, 404);
		}
	}
	
	public function sendPayloadCustom(Request $request) {
		try {
		
			$user_data  = new UserData();
			$products	=	DB::table('product')->
				join('merchantproduct','merchantproduct.product_id','product.id')->
				where([
					'merchantproduct.merchant_id'	=>	$user_data->company_id(),
					'product.ptype'					=>	'oilgas'	
				])->
				select('product.*')->
				get();

			$thumbnailData = $this->generateThumbnailContent($products);
			$og_localfuelprice = DB::table('og_localfuelprice')->
				join('prd_ogfuel','prd_ogfuel.id','og_localfuelprice.ogfuel_id')->
				join('product','product.id','prd_ogfuel.product_id')->
				whereIn('prd_ogfuel.product_id', $products->pluck('id'))->
				where([
					'og_localfuelprice.company_id'	=> $user_data->company_id(),
					'og_localfuelprice.location_id' => $request->location_id
				])->
				select('og_localfuelprice.*','product.systemid')->
				get();
		
			$new_request = [
				'packets' 			=> json_encode([]),
				'baudrate'	 		=> json_encode([]),
				'pts_protocol'		=> json_encode([]),
				'probe_protocol'	=> json_encode([]),
				'products'			=> json_encode($products),
				'thumbnailData'		=> json_encode($thumbnailData),
				'og_localfuelprice' => json_encode($og_localfuelprice)
			];


			/*$termial_data = DB::table('opos_terminal')->
				join('opos_locationterminal','opos_locationterminal.terminal_id','opos_terminal.id')->
				where('location_id', $request->location_id)->
				select('opos_terminal.*')->
				get();*/
			
			$location_data = DB::table('locationipaddr')->
				where('location_id',$request->location_id)->
				select('ipaddr')->
				get()->unique();


			foreach ($location_data as $l) {
				if (!empty($l->ipaddr))
					$this->init_send_data_via_api($l->ipaddr,$new_request);
			}
			
			return response()->json(["status" => "done"]);
		} catch (\Exception $e) {
			$this->handleError($e, 404);
		}	
	}

	public function init_send_data_via_api($ip_addr , $post) {

		Log::debug('init_send_data_via_api:'.json_encode($post));

		try {
			$url = "http://$ip_addr/interface/push_data";
			$cURLConnection = curl_init($url);
			curl_setopt($cURLConnection, CURLOPT_POSTFIELDS, $post);
			curl_setopt($cURLConnection, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($cURLConnection, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($cURLConnection, CURLOPT_SSL_VERIFYHOST, false);
			$apiResponse = curl_exec($cURLConnection);
			curl_close($cURLConnection);

			$data = json_decode($apiResponse, true);
			Log::info([
				"url"		=> $url,
				"response"	=> $apiResponse
			]);
			return $data;

		} catch (Exception $e) {
			$this->handleError($e, 404);
		}
	}


	public function generateThumbnailContent($product) {
		try {
			
			$productArray = [];
			foreach ($product as $prd) {
				$product_id			= $prd->id;
				$product_systemid	= $prd->systemid;
				$image				= $prd->thumbnail_1;
				$internalFileAddr 	= public_path() .
					("/images/product/$product_id/thumb/$image");

				$productArray[] = [
					"folderAddr" =>	"/images/product/$product_systemid/thumb/",
					"fileAddr" => "/images/product/$product_systemid/thumb/$image",
					"httpAddr" => env('APP_URL')."/images/product/$product_id/thumb/$image",
				];
			}

			return $productArray;

		} catch (\Exception $e) {
			$this->handleError($e, 404);
		}
	}


	public function push_fc(Request $request) {
		try {
			
			$validation = Validator::make($request->all(), [
				"packets"	=>	"required",
				'baudrate'	=>	'required',
				'protocol'	=>	'required'
			]);

			//if ($validation->fails())
				//throw new \Exception("packet data is missing", 400);

			$packets			= json_decode($request->packets, true);
			$baudrate 			= json_decode($request->baudrate, true);
			$pts_protocol 		= json_decode($request->pts_protocol, true);
			$probe_protocol 	= json_decode($request->probe_protocol, true);

			if (empty($packets))
				throw new \Exception("Invalid JSON", 400);

			if (count($packets) > 0) {
				foreach($packets as $packet) {
					$this->insertController($packet);
				}
			}

			if (count($baudrate) > 0) {
				foreach ($baudrate as $BR) {
					$this->insertBaudrate($BR);
				}
			}

			if (count($pts_protocol) > 0) {
				foreach ($pts_protocol as $prt) {
					$this->insertPTSProtocol($prt);
				}
			}
		
			if (count($probe_protocol) > 0) {
				foreach ($probe_protocol as $prt) {
					$this->insertPROBEProtocol($prt);
				}
			}
			
			return json_encode($this->init_company);
		} catch (\Exception $e) {
			$this->handleError($e, $e->getCode());
		}
	}

	/*
	 * Parsing function
	*/

	public function insertPROBEProtocol($protocolData) {
		try {
			unset($protocolData['id']);
			$protocolCondition = [
				"protocol_no"	=> $protocolData['protocol_no'],
				'protocol_name'	=> $protocolData['protocol_name']
			];

			$this->updateOrInsert('probe_protocol', $protocolCondition, $protocolData);

		} catch (\Exception $e) {
			$this->handleError($e, $e->getCode());
		}
	}	
	public function insertPTSProtocol($protocolData) {
		try {
			unset($protocolData['id']);
			$protocolCondition = [
				"protocol_no"	=> $protocolData['protocol_no'],
				'protocol_name'	=> $protocolData['protocol_name']
			];

			$this->updateOrInsert('pts_protocol', $protocolCondition, $protocolData);

		} catch (\Exception $e) {
			$this->handleError($e, $e->getCode());
		}
	}	

	public function insertBaudrate($baudrateData) {
		try {
			unset($baudrateData['id']);
			$baudrateCondition = [
				"index"		=> $baudrateData['index'],
				'baudrate'	=> $baudrateData['baudrate']
			];

			$this->updateOrInsert('pts_baudrate', $baudrateCondition, $baudrateData);

		} catch (\Exception $e) {
			$this->handleError($e, $e->getCode());
		}
	}	

	public function insertController($packet) {
		try {
			$controllerData = $packet['controller'];
			$pumpData 		= $packet['pump'];
			
			if ($controllerData['company_id'] != $this->init_company->id)
				throw new \Exception("Non-auth controller Packet");

			$controllerData['location_id'] = $this->dest_location->id;
			unset($controllerData['id']);
			$controllerCondition = [
				"systemid"		=>	$controllerData['systemid'],
				"location_id"	=>	$controllerData['location_id'],
				"company_id"	=>	$controllerData['company_id']
			];
			
			$controllerId = $this->updateOrInsert('controller', $controllerCondition, $controllerData);
			
			if (count($pumpData) > 0) {
				foreach ($pumpData as $pd) {
					$this->insertPump($pd, $controllerId);	
				}
			}

		} catch (\Exception $e) {
			$this->handleError($e, $e->getCode());
		}
	}

	private function insertPump($myPump, $controllerId) {
		try {
			$pumpData	= $myPump['pump'];
			$nozzleData	= $myPump['nozzle'];

			$pumpData['controller_id'] = $controllerId;
			$pumpData['pts2_protocol_id'] = $pumpData['og_pts2_protocol_id'];

			unset($pumpData['og_pts2_protocol_id']);
			unset($pumpData['id']);

			$pumpCondition = [
				"systemid"		=>	$pumpData['systemid'],
				"controller_id"	=>	$controllerId
			];
		
			$pumpId = $this->updateOrInsert('pump', $pumpCondition, $pumpData);
			if (count($nozzleData) > 0) {
				foreach ($nozzleData as $nozzle) {
					$this->insertNozzle($nozzle, $pumpId);	
				}
			}

		} catch (\Exception $e) {
			$this->handleError($e, $e->getCode());
		}
	}

	private function insertNozzle($nozzleData, $pumpId) {
		try {
			if (isset($nozzleData['p_systemid']))
				unset($nozzleData['p_systemid']);
			$nozzleData['pump_id'] = $pumpId;
			unset($nozzleData['id']);
			$nozzleCondition = [
				"nozzle_no"	=>	$nozzleData['nozzle_no'],
				'pump_id'	=>	$pumpId
			];

			$this->updateOrInsert('nozzle', $nozzleCondition, $nozzleData);

		} catch (\Exception $e) {
			$this->handleError($e, $e->getCode());
		}
	}

	 function updateOrInsert($tableName, $targetCondition, $targetData) {
		try {
			$targetTable = $this->getTargetTable($tableName);
	
			$shouldInsert = $targetTable->
				where($targetCondition)->
				first();
			
			if (empty($shouldInsert)) {
				$targetId = $targetTable->insertGetId($targetData);	
			} else {
				$targetCondition['updated_at']  = $targetData['updated_at']; 

				$shouldUpdate = $targetTable->
					where($targetCondition)->
					first();

				if (empty($shouldUpdate))
					$targetTable->where($targetCondition)->update($targetData);

				$targetId = $shouldInsert->id;
			}
			return $targetId;	
		}	catch (\Exception $e) {
			$this->handleError($e, $e->getCode());
		}
	}

	private function auth_key($key) {
		$is_valid_key = DB::table('fc_apisecurity')->
			where('app_key', $key)->
			first();
		
		if (!empty($is_valid_key)) {
			$this->init_company = DB::table('company')->
				where('systemid', $is_valid_key->company_systemid)->
				first();

			$this->dest_location = DB::table('location')->
				where('systemid',$is_valid_key->dest_location_systemid)->
				first();

			$this->src_location	= DB::table('location')->
				where('systemid',$is_valid_key->source_location_systemid)->
				first();

			$return = true;
		} else {
			$return = false;
		}
		return $return;
	}
	

	/*
	 *
	 * Misc Functions
	 *
	*/
	private function getTargetTable($name) {
		return	DB::table($this->target_table[$name]);
	}

	private function getSrcTable($name) {
		return	DB::table($this->src_table[$name]);
	}



	private function handleError(\Exception $e, $error_code = 403) {
		\Log::info([
				"Error"	=> 	$e->getMessage(),
				"File"	=>	$e->getFile(),
				"Line"	=>	$e->getLine()
			]);
		abort(response()->json(
			['message' => $e->getMessage()], 404)); 
	}

}
