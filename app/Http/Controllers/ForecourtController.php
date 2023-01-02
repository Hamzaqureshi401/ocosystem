<?php

namespace App\Http\Controllers;

use App\Models\merchantproduct;
use App\Models\OgFuel;
use App\Models\OgPumpNozzle;
use App\Models\OgPump;
use App\Models\OgFuelPrice;
use App\Models\opos_receiptproduct;
use App\Models\opos_wastageproduct;
use App\Models\StockReport;
use App\Models\ogController;
use Carbon\Carbon;
use Illuminate\Http\Request;
use \Illuminate\Support\Facades\Auth;
use \Illuminate\Support\Facades\Validator;
use \App\Models\usersrole;
use \App\Classes\PTS2;
use \App\Classes\UserData;
use App\Models\Company;
use App\Models\FranchiseMerchantLocTerm;
use \App\Classes\SystemID;
use Yajra\DataTables\DataTables;
use Yadakhov\InsertOnDuplicateKey;
use \App\Http\Controllers\IndustryOilGasController;
use Log;
use DB;

use \App\Http\Controllers\AnalyticsController;

class ForecourtController extends Controller
{
	public function index($locationId) {
		$user_data = new UserData();

		$branch_location = [];

		$analyticsController = new AnalyticsController();	
		$get_location = $analyticsController->get_location();
		foreach ($get_location as $key => $val) {
			$$key = $val;
			$location_id = array_column($branch_location, 'id');
			foreach($val as $location) {
				if (!in_array($location->id,$location_id)){
					$branch_location = array_merge($branch_location, [$location]);
				}
			}
		}

		$location = DB::table('location')->
			whereid($locationId)->
			whereNull('deleted_at')->
			first();

		if (empty($location)) {
			Log::info("Error: Location not found");
			abort(404);
		}
        return view('superadmin.controller_mgmt',
			compact('branch_location', 'location'));
	}


    public function fcContrlr($locationId, Request $request) {
		if (!empty($request->location_id)) {
			$locationId = $request->location_id;	
		} else {
			$locationId = 'all';
		}

		$user_data = new UserData();
		if ($locationId != 'all') {
			$dataList = DB::select('SELECT * FROM og_controller WHERE 
				location_id = ? and company_id = ? ORDER BY id DESC', [$locationId, $user_data->company_id()]);
		} else {
		
			$branch_location = [];

			$analyticsController = new AnalyticsController();	
			$get_location = $analyticsController->get_location();

			foreach ($get_location as $key => $val) {
				$$key = $val;
				$location_id = array_column($branch_location, 'id');
				foreach($val as $location) {
					if (!in_array($location->id,$location_id)){
						$branch_location = array_merge($branch_location, [$location->id]);
					}
				}
			}

			$dataList = DB::table('og_controller')->
				whereIn('location_id', $branch_location)->
				where('company_id', $company_id)->
				get();
		}

        $temp = [];
        foreach ($dataList as $elem) {
            $elem = get_object_vars($elem);
            $query = DB::select('SELECT * FROM og_pump WHERE controller_id = ?', [$elem["id"]]);

            $pumpNo = array("pump_no"=> count($query));
            $t = array_merge($elem, $pumpNo);
            array_push($temp, $t);
        }

        $data = Datatables::of($temp)->
        addIndexColumn()->
        addColumn('public_ip', function ($memberList) {
			
			if (empty($memberList['public_ipaddress'])) {
                $memberList['public_ipaddress'] = "0.0.0.0";
			}

			return "<p class='dt-center' data-field='public_ip' style='margin: 0;cursor:pointer'><a 
				style='color: #007bff' class='publicip-" . $memberList['id'] . "' data-field='publicip' id='" . 
				$memberList['id'] . "' onclick='editModal(this.id, this.innerHTML, this.dataset.field)'>". 
				$memberList['public_ipaddress'] ."</a></p><input type='hidden'  name='id[]' value=" . $memberList['id'] . 
				"><input type='hidden' class='ipv publicip-" . $memberList['id'] . "' style='border: 0' name='publicip[]' value=" . 
				$memberList['public_ipaddress'] . " placeholder='ENTER PUBLIC IP'>";
        })->
        addColumn('local_ip', function ($memberList) {
            if (empty($memberList['ipaddress'])) {
                $memberList['ipaddress'] = "0.0.0.0";
            }
			return "<p class='dt-center' data-field='local_ip' style='margin: 0;cursor:pointer'><a 
				style='color: #007bff' class='localip-" . $memberList['id'] . "' data-field='localip' id='" . 
				$memberList['id'] . "' onclick='editModal(this.id, this.innerHTML, this.dataset.field)'>". 
				$memberList['ipaddress'] ."</a></p><input type='hidden' style='border: 0' name='localip[]' 
				class='ipv localip-" . $memberList['id'] . "' value=" . $memberList['ipaddress'] .
				 " placeholder='ENTER PUBLIC IP'>";
        })->
        addColumn('controller_id', function ($memberList) {
			return "<p class='dt-center' data-field='controller_id' style='margin: 0;cursor:pointer'
				>". $memberList['systemid'] ."</p>";
        })->
        addColumn('device_id', function ($memberList) {  

			Log::debug('device_id='.json_encode($memberList));

            if(($memberList['device_id'])) {
            return '<p data-field="device_id" class="text-center" style="cursor: pointer;  margin: 0;">' .
				$memberList['device_id'] . '</p><input type="hidden" style="border: 0" name="deviceid[]" 
				class="deviceid" value="'.$memberList['device_id'].'">';

            } else {
				return '<p data-field="device_id" class="text-center" style="cursor: pointer;  margin: 0;"
					>-</p><input type="hidden" style="border: 0" name="deviceid[]" class="deviceid" value="-">';
            }
        })->
        addColumn('fw_rel_date', function ($memberList) {
            return '<p data-field="fw_rel_date" class="dt-center" style="cursor: pointer;  margin: 0;">' .
				$memberList['fw_rel_date'] . '</p><input type="hidden" style="border: 0" name="fw_rel_date[]" 
				value="'.$memberList['fw_rel_date'].'">';

        })->
        addColumn('battery', function ($memberList) {
            return '<p data-field="battery" class="dt-center" style="cursor: pointer;  margin: 0;">' .
				number_format($memberList['battery_voltage'],3) . 'V</p><input type="hidden" style="border: 0" 
				name="battery_voltage[]" value="'.$memberList['battery_voltage'].'">';
            
        })->
        addColumn('flash', function ($memberList) {
            return '<p data-field="flash" class="dt-center" style="cursor: pointer;  margin: 0;">' .
            number_format($memberList['free_storage'],0) . 'KB, '.
			number_format(($memberList['free_storage']/(
			(empty($memberList['total_storage']) ? 1 : $memberList['total_storage'])
			)) * 100, 2) .
			'%</p><input type="hidden" style="border: 0" name="free_storage[]" value="'.$memberList['free_storage'].'">';
            
        })->
        addColumn('atg', function ($memberList) {
			$count = DB::table('og_atg')->
				where('controller_id', $memberList['id'])->
				whereNull('deleted_at')->
				get()->count();

			return '<p data-field="atg" class="dt-center" style="cursor: pointer;  margin: 0;"><a class="atg"
			   	id="' . $memberList['id'] . '" onclick="atgMgmt(this.id)" >' .$count. '</a></p>';

        })->
        addColumn('pumps', function ($memberList) {
			return '<p data-field="pumps" class="dt-center" style="cursor: pointer;  margin: 0;"
				><a class="pumps" id="' . $memberList['id'] . '" onclick="pumpMgmt(this.id)" >' .
           		 $memberList['pump_no'] . '</a></p>';
        })->
       addColumn('bluecrab', function ($memberList) {
            return '<a class="bluecrabc allip" onClick="displayIpAddresses(this.id)" id="'.
				$memberList['id'] . '" data-id="' . $memberList['id'] .
				'"  data-toggle="modal" data-target="#ipaddress"><img data-field="bluecrab"
				src="/images/bluecrab_50x50.png"
				style="width:25px;height:25px; cursor: pointer;"/></a>';
		})->
		addColumn('pull', function ($memberList) {
			return '<a class="pull" onClick="confirmationAboutPulling(this.id,\''. 
				date("dMy H:i:s", strtotime($memberList['updated_at'])).'\')" id="' .
				$memberList['id'] . '" data-id="' . $memberList['id'] .
				'" ><img data-field="pull" 
				src="/images/yellowcrab_50x50.png"
				style="width:25px;height:25px;cursor: pointer;"/></a>';
        })-> 

		addColumn('converter', function ($memberList) {
			$count = DB::table('og_converter')->
				where('controller_id', $memberList['id'])->
				whereNull('deleted_at')->
				get()->count();
		
			$link = route('forecourtcontroller.convertor',$memberList['id']);

			return <<<EOD
			<a href="$link" target="_blank" class="os-linkcolor"
				 style="cursor:pointer;text-decoration:none">$count</a>
				
EOD;
		})->
        addColumn('deleted', function ($memberList) {
			
			$is_transition = DB::table('opos_receipt')->
				join('og_pump','og_pump.id','=','opos_receipt.pump_id')->
				where('og_pump.controller_id', $memberList['id'])->
				first();

			$is_delivery = DB::table('og_pump')->
				where([
					"controller_id"	=>	$memberList['id'],
					"delivered"		=>	1
				])->first();

			if (!empty($is_transition) || !empty($is_delivery)) {
					return <<< EOD
						<div><img 
						src="/images/redcrab_50x50.png" style="width:25px;height:25px;
					cursor: not-allowed;filter: grayscale(1) brightness(200%);" disabled/></div>
	
EOD;
			}

            return '<a class="delete" onClick="showDeletePrompt(this.id)" id="' .
				$memberList['id'] . '" data-id="' . $memberList['id'] .
				'" ><img data-field="deleted" 
				src="/images/redcrab_50x50.png"
				style="width:25px;height:25px;cursor: pointer;" 
				class="remove"/></a>';
        })->escapeColumns([])->make(true);
        
        return $data;
    }


	public function getConfigFile($ipaddr) {
		//Log::debug('getConfigFile: ipaddr='.$ipaddr);

		$pts2 = new PTS2(env('PTS_USER'), env('PTS_PASSWD'), null);
		$res = $pts2->get_pts_config_file($ipaddr);

		//Log::debug('getConfigFile: res[response]='.$res['response']);

		$pts2->close_channel();

        return response()->json(['data' => $res]);
	}


    public function newController(Request $request, $locationId)
    {	
		try {	
			$data =$request->all();
			$user_data = new UserData();
			
			$a = new SystemID('controller');

			$data['systemid'] = $a->__toString();
			
			$data['company_id'] = $user_data->company_id();
			$data['created_at'] = date("Y-m-d H:i:s");
			$data['updated_at'] = date("Y-m-d H:i:s");

			if ((count($data) > 0 && !empty($data['device_id'])) ||
				env('PUMP_HARDWARE') == NULL ) {
				if (array_key_exists('device_id', $data)) {
					$is_exist = DB::table('og_controller')->
						where('device_id', $data['device_id'])->
						first();

					if (!empty($is_exist)) {
						throw new \Exception("Device ID already exists");
					}

				} else {
					if (env('PUMP_HARDWARE') != NULL) {
						throw new \Exception("Device ID invalid");
					} else {
						$data['device_id']  = rand();
					}
				}

				DB::table('og_controller')->insert($data);

				$msg = "Controller added";
				return view("layouts.dialog", compact('msg'))->render();

			} else {
				throw new \Exception("Invalid hardware data.");
			}

		} catch (\Exception $e) {
			Log::info([
				"Error"	=> $e->getMessage(),
				"File"	=> $e->getFile(),
				"Line"	=> $e->getLine()
			]);

			$msg = $e->getMessage();//"Device ID already exists";
			return view("layouts.dialog", compact('msg'))->render();
		}
    }


    public function updateController(Request $request, $controller_id){

        if($controller_id){
            $data =$request->all();
			$data['updated_at'] = date("Y-m-d H:i:s");
            //$data['battery_voltage'] = $data['battery_voltage']/1000;
			if (count($data) > 0) {

				$data = DB::table('og_controller')->
					where('id',$controller_id)->
					update($data);

				return $data;
			} else {
				return '';
			}

        }
	}

	public function updateControllerDisp(Request $request) {
		try {

			$validation = Validator::make($request->all(), [
				"fk"	=>	"required",
				"data"	=>	"required"
			]);

			if ($validation->fails()) {
				throw new \Exception("Invalid data");
			}
			$data = DB::table('og_pump')->
				where('id',$request->fk)->
				update([
					'dispenser_serial_no'=> $request->data	
				]);


		
		} catch (\Exception $e) {
			Log::info([
				"Error" => $e->getMessage(),
				"File"	=> $e->getFile(),
				"Line"	=> $e->getLine()
			]);
			abort(404);
		}
	
	}

    public function saveController(Request $request){
        $localip   = request()->input('localip', []);
        $publicip   = request()->input('publicip', []);
        $deviceid   = request()->input('deviceid', []);
        $fw_rel_date   = request()->input('fw_rel_date', []);
        $battery_voltage   = request()->input('battery_voltage', []);
        $free_storage   = request()->input('free_storage', []);
        $id   = (count(request()->input('id', [])) > 0) ? request()->input('id', []) : '0';

        if($id == 0){
            return response()->json();
        }
        for($count=0; $count < count($localip); $count++){
			$controllers = ['id' => $id[$count],
			   	'ipaddress' => $localip[$count], 
				'public_ipaddress' => $publicip[$count],
			   	'device_id' => $deviceid[$count], 
				'fw_rel_date' => $fw_rel_date[$count],
				'battery_voltage' => $battery_voltage[$count], 
				'free_storage' => $free_storage[$count]];
            $data[] = $controllers;
        }
        
        ogController::insertOnDuplicateKey($data);
        //$query = DB::insert('INSERT INTO og_controller (id, location_id, device_id, fw_rel_date, battery_voltage, free_storage, ipaddress, public_ipaddress) VALUES (1210000000082,0,1,0,1,2,88888,2132), (1210000000081,0,1,0,1,2,12323,2132), (1210000000080,0,1,0,1,2,12323232,212332) ON DUPLICATE KEY UPDATE systemid = VALUES(systemid)');
       
        return response()->json($deviceid);
    }


    public function delController($controllerId) {
        $query = DB::delete('DELETE FROM og_controller WHERE id = ?', [$controllerId]);
        $msg = "Location deleted successfully";
        return response()->json($controllerId);
    }


    public function formatDate($date) {
        $newDate = date("dMy h:i:s", strtotime($date));
        return response()->json($newDate);
    }


    public function savePumpNozzleData(Request $request) {
		Log::debug('***** savePumpNozzleData() *****');

        $system_id = $request->pump_id;
        $controller_id = $request->controller_id;
        $pump_no = $request->pump_no;
        $pump_configuration_protocol = $request->pump_configuration_protocol;
        $baud_rate = $request->baud_rate;
        $pump_port = $request->pump_port;
        $communication_address = $request->communication_address;
        $nz=$request->nz;
        $nz_data=$request->nz_data;
		$og_ids = $request->og_ids;

        $pump_id=OgPump::where("systemid",$system_id)->first()->id;
        OgPump::whereId($pump_id)->update([
            "controller_id"=>$controller_id,
            "og_pts2_protocol_id"=>$pump_configuration_protocol,
            "baudrate"=>$baud_rate,
            "pump_port"=>$pump_port,
            "comm_address"=>$communication_address,
        ]);

		// Inserting to pumpnozzle table
        $this->user_data = new UserData();
    
		$model = new OgFuel();
	
		$ids = merchantproduct::where('merchant_id',
			$this->user_data->company_id())->
			pluck('product_id');
		
		$data = $model->whereIn('product_id', $ids)->get();

		Log::debug('data='.json_encode($data));
		Log::debug('nz='.json_encode($nz));
		Log::debug('nz_data='.json_encode($nz_data));

		foreach($og_ids as $i => $val) {
		
			if (!isset($nz[$i])) {
				OgPumpNozzle::where('pump_id', $pump_id)->
					where('nozzle_no',$i)->
					delete();
				continue;
			}	
			
			Log::debug('i='.$i);
			Log::debug('nz['.$i.']='.json_encode($nz[$i]));
			
			$ogfuel_id = $val;//$og_ids[$i];	

			OgPumpNozzle::updateOrCreate(
				['pump_id' => $pump_id, 'nozzle_no' => $i],
				['ogfuel_id' =>$ogfuel_id]
			);

			Log::info([
				"Abr LOG" =>	[
					'pump_id' => $pump_id, 
					'nozzle_no' => $i,
					'ogfuel_id' =>$ogfuel_id
				]
			]);
        }

        for($j = 0; $j <= 6; $j++){
			if (empty($og_ids[$j])) {
				OgPumpNozzle::where([
					'pump_id'	=> $pump_id,
					'nozzle_no' =>	$j	
				])->
				delete();
			}
        }

        return response()->json();
    }


    public function pumpNozzle(Request $request){
		$ret = [];

        $this->user_data = new UserData();
		$pumpSystemid = $request->pump_id;
        
		$model = new OgFuel();

		$location_id = DB::table('og_controller')->
			join('og_pump','og_pump.controller_id', '=', 'og_controller.id')->
			where('og_pump.systemid', $pumpSystemid)->
			first()->location_id ?? null;

		if ($location_id == null) {
			abort(404);
		}

		$fuelRecord = $this->getFuelData();
		return response()->json($fuelRecord);
	}

	public function getFuelData() {
		$industryController = new IndustryOilGasController();
		$fuelRecord = $industryController->getOgFuelQualifiedProducts();
		// make json
		$index = 1;
		$fuelRecord = array_map(function($f) use ($index) {
			$array = [];
			$array['ogfuel_id'] =	$f->og_f_id;
			$array['name'] 		=	$f->name;
			$array['price'] 	=	$this->get_execute_price($f->og_f_id);
			$array['systemid']	=	$f->systemid;	
			$array['id']		=	$f->id;	
			
			$index++;
			return $array;
		}, $fuelRecord);

		
		//fixing order
		uasort($fuelRecord, function($a,$b){
		    return strcmp($a['systemid'], $b['systemid']);
		});

		$temp = [];	
		foreach( $fuelRecord as $val) {
			$temp[] = $val;
		}
		return $temp;
    }


    public function savePump(Request $request){
		Log::debug('***** savePump() *****');
        $pump_id = $request->input('pump_id');

		Log::debug(json_encode($request->input('nz')));

        $nz  = (count($request->input('nz')) > 0) ?
			$request->input('nz') : 0;

        if($nz == 0){
            return response()->json();
        }

		// Inserting to pumpnozzle table
        for($i=1; $i <= count($nz); $i++){
			$opn = new OgPumpNozzle();
			$opn->nozzle_no = $i;
			$opn->pump_id = $pump_id;
			$opn->ogfuel_id = $nz[$i]['ogfuel_id'];
			$opn->save();
        }
        
        return response()->json();
    }


    public function get_execute_price($id)
    {
        $ogFuelPrice = OgFuelPrice::where('ogfuel_id', $id)
            ->where('price', '!=', null)
            ->where('start', '!=', null)
            ->whereDate('start', '<=', Carbon::now())
            ->orderBy('id', 'DESC')
            ->first();
        
        if(!empty($ogFuelPrice)){
            $price = $ogFuelPrice->price;
        }else{
            $price = '0.00';
        }
        
        return $price;
    }

    public function PumpMgment($controllerId)
	{   
		$branch_location = [];
		$user_data = new UserData();
		$analyticsController = new AnalyticsController();	
		$get_location = $analyticsController->get_location();
		foreach ($get_location as $key => $val) {
			$$key = $val;
			$location_id = array_column($branch_location, 'id');
			foreach($val as $location) {
				if (!in_array($location->id,$location_id)){
					$branch_location = array_merge($branch_location, [$location]);
				}
			}
		}


		$og_Controller = ogController::where([
			"id" 			=> $controllerId,
			"company_id"	=> $user_data->company_id()
		])->first();
		
		if (empty($og_Controller)) {
			\Log::info("Controller record not found");
			abort(404);
		}

		$location = DB::table('location')->
			find($og_Controller->location_id);


		if (empty($location)) {
			\Log::info("Location record not found");
			abort(404);
		}

	
		$pump_data = DB::table('og_pump')->
			join('og_controller','og_controller.id','=','og_pump.controller_id')->
			where([
				'og_controller.location_id' =>	$location->id,
				'og_controller.company_id'	=>	$user_data->company_id()
			])->
			select('og_pump.pump_no')->
			pluck('pump_no')->toArray();

		$pump_data = json_encode($pump_data);

		$pump_config = DB::table('og_pump')->
			where('controller_id',$og_Controller->id)->
			select('systemid','pump_no', 'id as pump_id')->
			get();


		$x = 1;
		foreach ($pump_config as	$key => $d) {
			$d->serial = $x;
			$data[$key] = $d;
			$x++;
		}

		$data = DB::select('SELECT og_pts2_protocol.id, og_pts2_protocol.protocol_no, og_pts2_protocol.protocol_name 
			from og_pts2_protocol');

		$baud = DB::select('SELECT * from og_pts2_baudrate order by `index` asc');

		$fuelRecord = $this->getFuelData();
		
		$index = 1;
		foreach( $fuelRecord as $key => $f) {
			$array  = [];
			$array["Id"] 	= $index;
			$array["Name"] 	= $f['name'];
			$array["Price"] = number_format($f['price']/100,2);
			$index++;
			$fuelRecord[$key] =  $array;	
		}
		
		return view('superadmin.pump_mgmt',compact('data', 'baud', 'branch_location', 'location', 'pump_data', 'pump_config', 'fuelRecord'));
    }
    
  
	public function AtgMgment($controllerId)
    {   
		$branch_location = [];

		$user_data = new UserData();

		$analyticsController = new AnalyticsController();	
		$get_location = $analyticsController->get_location();
		foreach ($get_location as $key => $val) {
			$$key = $val;
			$location_id = array_column($branch_location, 'id');
			foreach($val as $location) {
				if (!in_array($location->id,$location_id)){
					$branch_location = array_merge($branch_location, [$location]);
				}
			}
		}
		
		$og_Controller = ogController::find($controllerId);
		
		if (empty($og_Controller)) {
			\Log::info("Controller record not found");
			abort(404);
		}

		$location = DB::table('location')->
			find($og_Controller->location_id);


		if (empty($location)) {
			\Log::info("Location record not found");
			abort(404);
		}
		


		$tank_no_data = DB::table('og_tank')->
			where([
				['og_tank.location_id' ,'=', $location->id ], 
				['og_tank.tank_no', '!=',0],
				['og_tank.height', '!=',0],
				['og_tank.franchise_merchant_id', '=',$user_data->company_id()]
			])->
			whereNotNull('og_tank.product_id')->
			select('og_tank.tank_no')->
			get()->pluck('tank_no')->values()->toArray();
		
		$used_tank_nos = DB::table('og_atg')->
			where('controller_id', $og_Controller->id)->
			pluck('tank_no')->values()->toArray();

		$data = DB::select('SELECT id, protocol_no, protocol_name from og_probe_protocol 
				order by `protocol_no` asc');

		$baud = DB::select('SELECT * from og_pts2_baudrate order by `index` asc');

		return view('superadmin.atg_mgmt',compact('data', 'branch_location', 'location', 
			'controllerId','tank_no_data', 'used_tank_nos', 'baud'));
    }
   
	public function ATGMgmentUpdateTankNo(Request $request) {
		try {
		
			$validation = Validator::make($request->all(),[
				"atg_id"	=>		"required",
				"data"		=>		"required"
			]);

			if ($validation->fails())
				throw new \Exception("Validation failed");

			$user_data = new UserData();
			
			$og_Controller = ogController::find($request->controller_id);

			$selected_tank =  DB::table('og_tank')->
				where([
					['og_tank.location_id' ,'=', $og_Controller->location_id ], 
					['og_tank.tank_no', '=',$request->data],
					['franchise_merchant_id', '=',$user_data->company_id()]
					])->
				select('og_tank.*')->
				first();

			DB::table('og_atg')->
				where("id", $request->atg_id)->
				update([
					"tank_no"	=>	$request->data,
					'tank_id'	=>	$selected_tank->id
				]);

			$used_tank_nos = DB::table('og_atg')->
				where('controller_id', $request->controller_id)->
				pluck('tank_no')->values()->toArray();

			$msg = "Tank no updated";
			$html = view('layouts.dialog',compact('msg'));

			return response()->json([
				"html" 		=> $html,
				'tank_data' =>	$used_tank_nos
			]);

		} catch (\Exception $e) {
			\Log::error([
				"Error"	=>	$e->getMessage(),
				"File"	=>	$e->getFile(),
				"Line"	=>	$e->getLine()
			]);

			abort(404);
		}
	}

    public function pumpMgmt($controllerId)
    {   
		$data = DB::select('SELECT og_pump.id, og_pump.systemid, og_pump.controller_id, og_pump.dispenser_serial_no, 
			og_pump.pump_no, og_pump.created_at, og_controller.id AS c_id, og_pump.delivered,
 			og_controller.systemid AS c_systemid
			FROM og_pump INNER JOIN og_controller ON  og_controller.id = og_pump.controller_id 
			WHERE controller_id = ?', [$controllerId] );
	
		$x = 1;
		foreach ($data as	$key => $d) {
			$d->serial = $x;
			$data[$key] = $d;
			$x++;
		}
     //   return response()->json($data);
		
		return Datatables::of($data)->
				addIndexColumn()->

				addColumn('controller_id', function ($data) {
					return $data->c_systemid;
				})->		
			
				addColumn('disp_no', function ($data) {
					$disp = $data->dispenser_serial_no ?? 0;
					return <<<EOD
			<span class="os-linkcolor" style="cursor:pointer" onclick="dispModelEdit('$data->id', '$data->dispenser_serial_no')">$disp</span>
EOD;
		})->


				addColumn('pump_id', function ($data) {
					return <<<EOD
					<a id="pump-id" onclick="pumpConfig('$data->serial', '$data->systemid')"
						class="os-linkcolor" style="cursor:pointer">$data->systemid</a>
EOD;
				})->


				addColumn('pump_no', function ($data) {
					return <<<EOD
						<span id="$data->id" class="pump-no" onclick="editModal(this.id, this.innerHTML)"
							class="os-linkcolor" style="cursor:pointer">$data->pump_no</span>
EOD;
				})->


				addColumn('delete', function ($data) {
					
					$is_transition = DB::table('opos_receipt')->
						where('pump_id', $data->id)->
						first();
					
					if (!empty($is_transition) || $data->delivered == 1) {
						return <<< EOD
						<div><img 
						src="/images/redcrab_50x50.png" style="width:25px;height:25px;cursor: not-allowed;
   									 filter: grayscale(1) brightness(200%);" disabled/></div>
EOD;
					}

					return <<< EOD
					<div data-id="$data->id" data-field="deleted" class="remove"><img 
						src="/images/redcrab_50x50.png" style="width:25px;height:25px;cursor:pointer;"/></div>
	
EOD;
				})->		
				escapeColumns([])->
				make(true);

    }

	public function ATGMgmentDataTable(Request $request) {
		try {

			$data = DB::table('og_atg')->
				join('og_controller','og_controller.id','=','og_atg.controller_id')->
				where('og_atg.controller_id', $request->controller_id)->
				whereNull('og_atg.deleted_at')->
				select("og_atg.*", "og_controller.systemid as c_systemId")->
				get();

			return Datatables::of($data)->
				addIndexColumn()->

				addColumn('controller_id', function ($data) {
					return $data->c_systemId;
				})->
				addColumn('atg_id', function ($data) {
					return <<<EOD
					<a id="pump-id" onclick="atgConfig('', '$data->systemid')"
						class="os-linkcolor" style="cursor:pointer">$data->systemid</a>
EOD;
				})->
				addColumn('tank_id', function ($data) {

					$systemid = DB::table('og_tank')->find($data->tank_id)->systemid ?? '';					
					return <<<EOD
						<span id="$data->id" class="tank-id" onclick="editModal(this.id, this.innerHTML)"
							class="os-linkcolor" style="cursor:pointer">$systemid</span>
EOD;
				})->
				addColumn('tank_no', function ($data) {
					return <<<EOD
						<span id="$data->id" class="tank-no os-linkcolor" onclick="tankNoSelectModal(this.id, $data->tank_no)"
							 style="cursor:pointer">$data->tank_no</span>
EOD;
				})->
				addColumn('delete', function ($data) {
					return <<< EOD
					<div data-id="$data->id" data-field="deleted" class="remove"
						onclick="delete_ATG($data->id)"><img src="/images/redcrab_50x50.png" 
						style="width:25px;height:25px;cursor:pointer;"/></div>
	
EOD;
				})->		
				escapeColumns([])->
				make(true);


		} catch (\Exception $e) {
			\Log::info([
				"Error"	=>	$e->getMessage(),
				"File"	=>	$e->getFile(),
				"Line"	=>	$e->getLine()
			]);
			abort(404);
		}
	}

    public function newPump($controllerId)
    {   
        $a = new SystemID('pump');
        $systemId = $a->__toString();

        $query = DB::insert('INSERT INTO og_pump (systemid, controller_id) VALUES (?, ?)', [$systemId, $controllerId]);

        $id = DB::select('SELECT id FROM og_pump WHERE systemid = ?', [$systemId]);
        $c_systemid = DB::select('SELECT systemid FROM og_controller WHERE id = ?', [$controllerId]);

        $data = array('id' => $id[0]->id, 'systemid' => $systemId, 'controllerid' => $controllerId, 'c_systemid' => $c_systemid[0]->systemid);

        return response()->json($data);
    }

	public function ATGMgmentNew(Request $request) {
		try {
		
			$a = new SystemID('atg');
			$systemId = $a->__toString();

			$validation = Validator($request->all(), [
				"controller_id" =>	"required"
			]);

			if ($validation->fails()) {
				throw new \Exception("Controller missing");
			}

			DB::table('og_atg')->
				insert([
					"systemid"		=>	$systemId,
					"controller_id"	=>	$request->controller_id,
					"created_at"	=>	date("Y-m-d H:i:s"),
					"updated_at"	=>	date("Y-m-d H:i:s")
				]);

			$msg = "New Auto Tank Gauge added";
			return view('layouts.dialog', compact('msg'));

		} catch (\Exception $e) {
			\Log::info([
				"Error"	=>	$e->getMessage(),
				"File"	=>	$e->getFile(),
				"Line"	=>	$e->getLine()
			]);
			abort(404);
		}
	}

	public function ATGDelete(Request $request) {
		try {
			$validation = Validator($request->all(), [
				"atg_id" =>	"required"
			]);

			if ($validation->fails())
				throw new \Exception("atg_id failed");

			DB::table('og_atg')->
				where('id', $request->atg_id)->
				update(["deleted_at"	=>	date("Y-m-d H:i:s")]);

			$msg = "Auto Tank Gauge deleted";
			return view('layouts.dialog', compact('msg'));

		} catch (\Exception $e) {
			\Log::info([
				"Error"	=>	$e->getMessage(),
				"File"	=>	$e->getFile(),
				"Line"	=>	$e->getLine()
			]);
			abort(404);
		}
	}

    function updateIp ($ipAddress, $controllerId, $fieldType) {
        $query = DB::update('UPDATE og_controller SET ' . $fieldType . ' = ? WHERE id = ?', [$ipAddress, $controllerId]);

        return response()->json([
            'ipAddress' => $ipAddress,
            'controllerId' => $controllerId,
            'fieldType' => $fieldType
        ]);
    }

    function updatePump ($id, $pump_no) {

		$user_data = new UserData();
		$pump = DB::table('og_pump')->
			join('og_controller','og_controller.id','=','og_pump.controller_id')->
			where("og_pump.id",$id)->first();

		$is_pump_no_exist = DB::table('og_pump')->
			join('og_controller','og_controller.id','=','og_pump.controller_id')->
			where([
				'og_controller.location_id' 	=>	$pump->location_id,
				'og_pump.pump_no'				=>	$pump_no,
				'og_controller.company_id'		=>	$user_data->company_id()
			])->
			first();
		
		if (!empty($is_pump_no_exist)) {
			$msg = "Pump No. already exist";
		} else {
			$query = DB::update('UPDATE og_pump SET pump_no = ? WHERE id = ?', [$pump_no, $id]);
			$msg = "Pump No. updated";
		}
	
		$is_pump_no_exist = DB::table('og_pump')->
			join('og_controller','og_controller.id','=','og_pump.controller_id')->
			where([
				'og_controller.location_id' 	=>	$pump->location_id,
				'og_controller.company_id'		=>	$user_data->company_id()
			])->
			select('og_pump.pump_no')->
			pluck('pump_no')->toArray();

		return response()->json(["html"=>$msg , "pump_data" => $is_pump_no_exist]);
    }

    public function delPump($pumpId) {
        $query = DB::delete('DELETE FROM og_pump WHERE id = ?', [$pumpId]);

        $response = (new ApiMessageController())->
			saveresponse('Client deleted successfully!');
        
        return response()->json($pumpId);
    }


	/* Need to get some data for pull operation:
	   ipaddress, public_ipaddress, PTS_MODE */
	function getPullData($controllerId) {
		$ipaddress = null;

        $ips = DB::select('SELECT ipaddress, public_ipaddress FROM og_controller WHERE id = '.$controllerId);

		Log::debug('IP Addresses = '.json_encode($ips));

        return response()->json([
            'ipaddress' => $ips[0]->ipaddress,
            'public_ipaddress' => $ips[0]->public_ipaddress,
            'pts_mode' => env('PTS_MODE')
        ]);
	}


	/* Update IP address */
	function updateIpAddress(Request $request) {
		$controller_id = $request->input('controller_id');
		$ipaddress = $request->input('ipaddress');
		$public_ipaddress = $request->input('public_ipaddress');

        $query = DB::update('UPDATE og_controller SET ipaddress="'.
			$ipaddress. '", public_ipaddress="'. $public_ipaddress.
			'" WHERE id = '. $controller_id);

        return response()->json($query);
	}


	// Having a og_controller.id from Admin, get the company.owner_user_id
	public function getOwnerUserId($controller_id) {
		$query = "
		SELECT
			c.owner_user_id
		FROM
			og_controller ogc,
			merchantlocation ml,
			company c
		WHERE
			ogc.location_id = ml.location_id AND
			ml.merchant_id = c.id AND
			ogc.id = ".$controller_id." 
		";

		$result = DB::select(DB::raw($query));

		//Log::debug('result='.json_encode($result));

		return $result;
	}

	/* Retrieve OG Fuel Products */
    public function getOGFuelProducts($owner_user_id) {
		/* This is normally run as admin, so we need to get the actual 
		 * user_id or merchant_id */

		$query = "
		SELECT
			latest.id,
			latest.name,
			og_fuelprice.ogfuel_id as No,
			og_fuelprice.price
		FROM (
		SELECT
			p.id,
			p.name,
			max(fp.created_at) as fp_created_at
		FROM
			product p,
			prd_ogfuel pof,
			merchantproduct mp,
			company c,
			og_fuelprice fp
		WHERE
			pof.product_id = p.id 
			AND mp.product_id = p.id
			AND mp.merchant_id = c.id
			AND fp.ogfuel_id = pof.id
			AND p.name is not null
			AND fp.price is not null
			AND c.owner_user_id = ".$owner_user_id." 
		GROUP BY
			p.id
		) as latest
		INNER JOIN
			og_fuelprice
		ON
			og_fuelprice.created_at = latest.fp_created_at
		ORDER BY
			og_fuelprice.ogfuel_id
		LIMIT 8
		";

		$og_fuels = DB::select(DB::raw($query));

        return response()->json($og_fuels);
    }


	/* Retrieve currently active and defined OG Fuel products
	 * Note that this should retrieve all active products from ALL
	 * controllers owned by the company */
    public function getActiveOGFuelProducts($owner_user_id) {
		$query = "
			SELECT
				og_fuelprice.ogfuel_id as No,
				latest.nozzle_no,
				latest.name,
				og_fuelprice.price
			FROM (
			SELECT
				p.name,
				pn.nozzle_no,
				max(fp.created_at) as created_at
			FROM
				product p,
				prd_ogfuel of,
				merchantproduct mp,
				company c,
				og_fuelprice fp,
				og_pump op,
				og_controller oc,
				og_pumpnozzle pn
			WHERE
				of.product_id = p.id 
				AND mp.product_id = p.id
				AND mp.merchant_id = c.id
				AND fp.ogfuel_id = of.id
				AND pn.pump_id = op.id
				AND pn.ogfuel_id = of.id
				AND op.controller_id = oc.id
				AND p.name is not null
				AND fp.price is not null
				AND c.owner_user_id = ".$owner_user_id." 
			GROUP BY
				p.id
			) as latest
			INNER JOIN
				og_fuelprice
			ON
				og_fuelprice.created_at = latest.created_at
			ORDER BY
				og_fuelprice.ogfuel_id
			LIMIT 8
		";

		$active_og_fuels = DB::select(DB::raw($query));

        return response()->json($active_og_fuels);
	}

	/* Retrieve product/nozzle mapping per pump for all the pumps given
	 * the owner_user_id */

    public function getNozzleProductMapping($owner_user_id) {
		$query = "
			SELECT
				oc.id as controller_id,
				op.id as pump_id,
				pn.nozzle_no,
				pn.ogfuel_id
			FROM
				og_controller oc,
				og_pump op,
				og_pumpnozzle pn,
				prd_ogfuel prd,
				product p,
				merchantlocation ml,
				company c
			WHERE
				pn.pump_id = op.id AND
				pn.ogfuel_id = prd.id AND
				prd.product_id = p.id AND
				op.controller_id = oc.id AND
				oc.location_id = ml.location_id AND
				ml.merchant_id = c.id AND
				c.owner_user_id = ".$owner_user_id."
			";

		$nozprod = DB::select(DB::raw($query));

        return response()->json($nozprod);
	}
        

	function getPumpData($controllerId, $pumpNo, $pumpId,
		Request $request) {

		Log::debug('getPumpData: controllerId='.$controllerId);
		Log::debug('getPumpData: pumpNo      ='.$pumpNo);
		Log::debug('getPumpData: pumpId      ='.$pumpId);

		$pumpDetails=OgPump::select([
			'id as pump_id',
			'og_pts2_protocol_id',
			'baudrate',
			'pump_port',
			'comm_address',
			'controller_id',
			'pump_no',
			'systemid'])->
			where([
				"controller_id" => "$controllerId",
			//	"pump_no" => $pumpNo,
				"systemid" => $pumpId
			])->first();
	
		Log::debug('getPumpData: pumpDetails='.json_encode($pumpDetails));

		if (!empty($pumpDetails)) {
			$nozzle = DB::table('og_pumpnozzle')->
				where('pump_id', $pumpDetails->pump_id)->
				select('pump_id','nozzle_no','ogfuel_id')->
				get();
			$nozzle_formated_data = collect();
			for($i = 1 ; $i <= 6; $i++) {
				$data_packet_nozzle = $nozzle->
					where('nozzle_no', $i)->first();
				if (!empty($data_packet_nozzle)) {
					$nozzle_formated_data->push($data_packet_nozzle);
				} else {
					$nozzle_formated_data->push([]);
				}
			}

			$pumpDetails->pump_nozzles_data = $nozzle_formated_data;
		}
		
		return response()->json($pumpDetails);
	}


	public function saveHardwareDumpFile(Request $request) {
		try {
			if ($request->has('protocols')) {
				$protocols = $request->protocols;
				
				//	\Log::info(["protocols to be populated" => $protocols]);

				foreach ($protocols as $ele){
				
					if ($ele['type'] == 0) 
						$table = 'og_pts2_protocol';
					else if ($ele['type'] == 1) 
						$table = 'og_probe_protocol';
					else
						continue;

					//Log::debug('ele='.json_encode($ele));
					$is_exist = DB::table($table)->
						where([
							'protocol_name' => $ele['name'],
							"protocol_no"	=> $ele['index']
						])->first();

					//Log::debug('is_exist='.json_encode($is_exist));
					
					if (empty($is_exist)) {
						DB::table($table)->insert([
							'protocol_name' => $ele['name'],
							"protocol_no"	=> $ele['index'],
							"created_at"	=> date('Y-m-d H:i:s'),
							"updated_at"	=> date('Y-m-d H:i:s')
						]);
					}
				}
			}

			if ($request->has('bauds')) {
				$baud = $request->bauds;

				//Log::debug('1. baud='.json_encode($baud));

				// Sort $baud by index first
				$index = array_column($baud, 'index');
				array_multisort($index, SORT_ASC, $baud);

				//Log::debug('2. baud='.json_encode($baud));
		
				foreach($baud as $ele) {
					//Log::debug('ele='.json_encode($ele));
					$is_exist = DB::table('og_pts2_baudrate')->
						where([
							'baudrate' => $ele['rate'],
							"index"    => $ele['index']
						])->first();

					//Log::debug('is_exist='.json_encode($is_exist));
					
					if (empty($is_exist)) {
						DB::table('og_pts2_baudrate')->insert([
							'baudrate'   => $ele['rate'],
							"index"      =>	$ele['index'],
							"created_at" => date('Y-m-d H:i:s'),
							"updated_at" => date('Y-m-d H:i:s')
						]);
					}
				}
			}

		} catch (Exception $e) {
			Log::error([
				"Error"	=>	$e->getMessage(),
				"File"	=>	$e->getFile(),
				"Line"	=>	$e->getLine()
			]);
			abort(404);
		}
	}


	public function converterLanding(Request $request) {
		try {
			$controllerId = $request->controllerId;
		
			$controller_data = DB::table('og_controller')->
				where('id', $controllerId)->
				first();

			$location = DB::table('location')->
				where('id', $controller_data->location_id)->
				first();

			return view('superadmin.convertor_mgmt', compact('controllerId', 'location'));

		} catch (\Exception $e) {
			\Log::info([
				"Error"	=>	$e->getMessage(),
				"File"	=>	$e->getFile(),
				"Line"	=>	$e->getLine()
			]);
			abort(404);
		}
	}

	public function converterMainTable(Request $request) {
		try {

			$controller_id = $request->controller_id;
			
			$controller = DB::table('og_controller')->
				where('id', $controller_id)->
				first();

			$data = DB::table('og_converter')->
				where('controller_id', $controller_id)->
				whereNull('deleted_at')->
				get();	

			return Datatables::of($data)->
				addIndexColumn()->

				addColumn('controller_id', function($data) use ($controller) {
					return $controller->systemid;
				})->
				addColumn('convertor_id', function ($data) {
					return $data->systemid;
				})->

				addColumn('model', function ($data) {
					$model = $data->model ?? 'Model';
					return <<<EOD
					<a id="pump-id" onclick="updateModel('$data->id', '$data->model')"
						class="os-linkcolor" style="cursor:pointer">$model</a>
EOD;
				})->


				addColumn('port', function ($data) {
					
					$count = DB::table('og_converterport')->
						where('converter_id', $data->id)->
						whereNull('deleted_at')->
						get()->count();

					$model = $data->model ?? 'Model';

					return <<<EOD
						<span onclick="port_open($data->id, '$model','$data->systemid')"
							class="os-linkcolor" style="cursor:pointer">$count</span>
EOD;
				})->


				addColumn('delete', function ($data) {
					return <<< EOD
					<div onclick="delete_convetor($data->id)"><img 
						src="/images/redcrab_50x50.png" style="width:25px;height:25px;cursor:pointer;"/></div>
	
EOD;
				})->		
				escapeColumns([])->
				make(true);


		} catch (\Exception $e) {
			\log::info([
				"error"	=>	$e->getmessage(),
				"file"	=>	$e->getfile(),
				"line"	=>	$e->getline()
			]);
			abort(404);
		}
	}

	public function newConvertor(Request $request) {
		try {
			$validation = Validator::make($request->all(), [
				'controller_id' => 'required'
			]);

			if ($validation->fails()) {
				throw new \Exception('Validation failed');
			}

			$systemId = new SystemID('converter');

			DB::table('og_converter')->insert([
				'controller_id'	=> $request->controller_id,
				'systemid'		=> $systemId,
				'created_at' 	=> date("Y-m-d"),
				'updated_at'	=> date("Y-m-d")
			]);

			$msg = "Convertor added";
			return view('layouts.dialog', compact('msg'));
		} catch (\Exception $e) {
			\Log::info([
				"error"	=>	$e->getmessage(),
				"file"	=>	$e->getfile(),
				"line"	=>	$e->getline()
			]);
			abort(404);
		}
	}

	public function convertorUpdate(Request $request) {
		try {

			$validation = Validator::make($request->all(), [
				'convertor_id' 	=> 'required',
				'field'			=>	'required'	
			]);

			if ($validation->fails()) {
				throw new \Exception("Validation failed");
			}

			$update_array = [];

			switch($request->field) {
				case 'model':
					$update_array['model'] = $request->data;
					$msg = ucfirst($request->field)." updated";
					break;
				case 'delete':
					$update_array['deleted_at'] = date('Y-m-d H:i:s');
					$msg = "Convertor deleted";
					break;
				default:
					$msg = "Nothing to update/delete";
					break;
			}

			$update_array['updated_at'] = date("Y-m-d H:i:s");
			
			DB::table('og_converter')->
				where('id', $request->convertor_id)->
				update($update_array);

			return view("layouts.dialog", compact('msg'));

		} catch (\Exception $e) {
			\Log::info([
				"error"	=>	$e->getmessage(),
				"file"	=>	$e->getfile(),
				"line"	=>	$e->getline()
			]);
			abort(404);
		}
	}

	public function convertorPortTable(Request $request) {
		try {

			$convertor_id = $request->convertor_id;
			$data = DB::table('og_converterport')->
				where('converter_id', $convertor_id)->	
				whereNull('deleted_at')->
				get();	

			return Datatables::of($data)->
				addIndexColumn()->

				addColumn('port_id', function ($data) {
					return $data->systemid;
				})->

				addColumn('dispenser_connected', function ($data) {
					$disp = $data->dispenser_connected;
					return <<<EOD
					<input class="form-control" placeholder="Dispenser" value="$disp"
						p-id="$data->id"
						onchange="updateDispenserField(this)" />
EOD;
				})->


				addColumn('dispenser_serialno', function ($data) {
					$port = $data->dispenser_serialno;	
					return <<<EOD
						<input class="form-control" placeholder="Serial Number" value="$port"
							p-id="$data->id" style='text-align:center'
							onchange="updateSerialField(this)" />

EOD;
				})->


				addColumn('delete', function ($data) {
					return <<< EOD
					<div onclick="delete_port($data->id)"><img 
						src="/images/redcrab_50x50.png" style="width:25px;height:25px;cursor:pointer;"/></div>
	
EOD;
				})->		
				escapeColumns([])->
				make(true);


		} catch (\Exception $e) {
			\Log::info([
				"error"	=>	$e->getmessage(),
				"file"	=>	$e->getfile(),
				"line"	=>	$e->getline()
			]);
			abort(404);
		}	
	}

	public function convertorPortNew(Request $request) {
		try {
			$validation = Validator::make($request->all(), [
				'convertor_id' => 'required'
			]);

			if ($validation->fails()) {
				throw new \Exception('Validation failed');
			}

			$systemId = new SystemID('converterport');

			DB::table('og_converterport')->insert([
				'converter_id'	=> $request->convertor_id,
				'systemid'		=> $systemId,
				'created_at' 	=> date("Y-m-d"),
				'updated_at'	=> date("Y-m-d")
			]);

			$msg = "Port added";
			return view('layouts.dialog', compact('msg'));
		} catch (\Exception $e) {
			\Log::info([
				"error"	=>	$e->getmessage(),
				"file"	=>	$e->getfile(),
				"line"	=>	$e->getline()
			]);
			abort(404);
		}
	}

	public function convertorPortUpdate(Request $request) {
	try {
			$validation = Validator::make($request->all(), [
				'port_id' 	=> 'required',
				'field'			=>	'required'	
			]);

			if ($validation->fails()) {
				throw new \Exception("Validation failed");
			}

			$update_array = [];

			switch($request->field) {
				case 'disp':
					$update_array['dispenser_connected'] = $request->data;
					$msg = "Dispenser connected updated";
					break;
				case 'delete':
					$update_array['deleted_at'] = date('Y-m-d H:i:s');
					$msg = "Port deleted";
					break;
				case 'serial_no':
					$update_array['dispenser_serialno'] = $request->data;
					$msg = "Serial Number updated";
					break;
				default:
					$msg = "Nothing to update/delete";
					break;
			}

			$update_array['updated_at'] = date("Y-m-d H:i:s");
			
			DB::table('og_converterport')->
				where('id', $request->port_id)->
				update($update_array);

			return view("layouts.dialog", compact('msg'));

		} catch (\Exception $e) {
			Log::info([
				"error"	=>	$e->getmessage(),
				"file"	=>	$e->getfile(),
				"line"	=>	$e->getline()
			]);
			abort(404);
		}

	}

	public function detectHardware() {
		try {
			#run the external command, break output into lines
			if (PHP_OS_FAMILY == 'Windows')
				$arp=`arp -a`;
			else
				$arp=`arp -n`;

			Log::info(["Raw output: " => $arp]);

			$lines=explode("\n", ($arp));

			Log::info(["Line after \n" => $lines]);

			$data = [];

			foreach($lines as $line) {
				$cols=preg_split('/\s+/', trim($line));

				Log::info(["Cols: " => $cols]);

				if ($cols[0] != '') {
					$detect_mac = false;
					$detect_ip = false;
					$buffer = [];
					foreach($cols as $hardwareConnectedData) { 
						if (filter_var($hardwareConnectedData, FILTER_VALIDATE_IP)) {
							if ($hardwareConnectedData != '255.255.255.255') {
								$detect_ip = true;
								$buffer['IP'] = $hardwareConnectedData;
							}
						}

						if (filter_var($hardwareConnectedData, FILTER_VALIDATE_MAC)) {
							$detect_mac = true;
							$buffer['MAC'] = $hardwareConnectedData;
						}  
					}

					if ($detect_mac && $detect_ip) {
						$data[] = $buffer;
					}
				}
			}
			//Your username.
			$username = env('PTS_USER');
			//Your password.
			$password = env('PTS_PASSWD');

			$pts = [];
			foreach ($data as $result) {
				$url = "http://".$result['IP'];
				Log::debug('result='.json_encode($result));
				Log::debug('url='.$url);

				//Initiate cURL.
				$ch = curl_init($url);

				// Specify the username and password using the
				// CURLOPT_USERPWD option.
				curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);

				//process compressed data
				curl_setopt($ch,CURLOPT_ENCODING , "");
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 

				//Execute the cURL request.
				$response = curl_exec($ch);

				//Check for errors.
				if(curl_errno($ch)){
					//If an error occured, throw an Exception.
					Log::info([
						"Error" => "Curl Error: ".curl_errno($ch),
						"IP"	=>	$result['IP']
					]);
					continue;

				} else {
					// Test if string contains certain text
					Log::debug('response='.json_encode($response));
					if ((strpos($response, 'PTS-2') !== false) or
						(strpos($response, 'pts_config_en.js') !== false) or
						(strpos($response, 'pts_config_ru.js') !== false) or
						(strpos($response, 'Technotrade') !== false) or
						(strpos($response, 'pts.min.js') !== false)) {

						$pts[] = $result['IP'];
					}

					Log::debug('pts='.json_encode($pts));
				}
			}

			if (env('PUMP_HARDWARE') == NULL) {
				$pts[] = "127.0.0.1";
				$pts[] = "0.0.0.0";
			}

			Log::info(['Response' => $pts]);
			return json_encode($pts);

		}  catch (\Exception $e) {
			Log::info([
				"error"	=>	$e->getmessage(),
				"file"	=>	$e->getfile(),
				"line"	=>	$e->getline()
			]);
			abort(404);
		}
	}
}
