<?php

namespace App\Http\Controllers;

use DB;
use Log;
use Illuminate\Http\Request;
use App\Models\usersrole;
use App\Models\role;
use App\Models\Company;
use Illuminate\Support\Facades\Auth;
use \App\Models\StockReport;
use Yajra\DataTables\DataTables;
use \App\Models\stockreportproduct;
use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\Validator;
use \App\Http\Controllers\AnalyticsController;
use \App\Classes\UserData;
use \App\Classes\SystemID;
use App\Models\locationipaddr;
class LogisticsController extends Controller
{
    public function showLogisticsView() {
        return view('logistics.logistics');
    }

    public function showdeliveryControlView(Request $request) {
	    $this->user_data = new UserData();
		$analyticsController = new AnalyticsController();
		$company_id = $this->user_data->company_id();
		$my_company_detail =  \App\Models\Company::find($company_id);

		$delivery = DB::table('users')->
			join('usersfunction','usersfunction.user_id','=','users.id')->
			leftjoin('function','function.id','=','usersfunction.function_id')->
			where(["usersfunction.company_id" => $my_company_detail->id, 
				'function.slug'=>'dlv'])->
			whereNull('usersfunction.deleted_at')->
			select("usersfunction.id as id", "users.name as name", 'users.id as user_id')->
			get();
		
		$branch_location = [];

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
		
		$StockReportTRin = [];
		if (!empty($request->location_id)) { 
				$StockReportTRin = $StockReportTRin->where('$array_element',$request->location_id);
				$selected_location = DB::table('location')->where('id', $request->location_id)->first();
			} else {
				$selected_location = null;
		}

		return view('logistics.deliverycontrol', compact(
			'StockReportTRin',
			'delivery',
			'branch_location',
			'selected_location'
		));
	}

	public function cADataTable(Request $request) {
		try {
		
			$user_data	= new UserData();
			$mycompany	= DB::table('company')->find($user_data->company_id());

			$data = collect();

			//processing SO to display
			$salesOrderRecord = DB::table('salesorder')->
				leftjoin('salesorderdeliveryorder','salesorderdeliveryorder.salesorder_id','salesorder.id')->
				leftjoin('deliveryorder','deliveryorder.id','=','salesorderdeliveryorder.deliveryorder_id')->
				where('salesorder.creator_user_id', $mycompany->owner_user_id)->
				select('salesorder.*', 
				'deliveryorder.issuer_location_id as creator_location_id',
				'deliveryorder.receiver_location_id',
				'deliveryorder.systemid as dsystemid', 'deliveryorder.id as DO_id',
				'deliveryorder.deliveryman_user_id'
				)->
				get();
			$salesOrderRecord = $salesOrderRecord->filter(function($f) {

				$product  = DB::table('product')->
					join('salesorderitem','salesorderitem.product_id','=','product.id')->
					where('salesorderitem.salesorder_id',$f->id)->
					whereNotIn('ptype', ['oilgas'])->
					first();
				return !empty($product);
			});


			$salesOrderRecord->map(function($f) use ($data) {
				$isDOIssued = DB::table('deliveryorderproduct')->
					where('deliveryorder_id', $f->DO_id)->first();
				$packet = collect();
				$packet->source_doc 	= $f->systemid;
				$packet->source_doc_url	= url("/salesorder/$f->systemid");
				$packet->source			= 'Sales Order';
				$packet->date			= date("dMy", strtotime($f->created_at));
				$packet->delivery_id	= !empty($isDOIssued) ? $f->dsystemid: ''; 
				$packet->delivery_url	= url("/deliveryorder/salesorder/$f->dsystemid");
				$packet->to				= DB::table('location')->
											find($f->receiver_location_id)->branch ?? '';
				$packet->delivery_man	= 'Deliveryman';
				$packet->deliveryman_id =  0;
				$packet->status			= 'Pending';
				$packet->created_at		= $f->created_at;
				$packet->from_location_id	= $f->receiver_location_id ?? 0;
				if ($f->is_void == 1)	
					$packet->status			= 'Void';
				elseif (!empty($isDOIssued))
					$packet->status			= 'Approved';
				else
					$packet->status			= 'Pending';
				$data->push($packet);
			});

			//processing PO to display
			$purchaseOrderRecord = DB::table('purchaseorder')->
				join('merchantpurchaseorder',
					'merchantpurchaseorder.purchaseorder_id','=','purchaseorder.id')->
				leftjoin('purchaseorderdeliveryorder','purchaseorderdeliveryorder.purchaseorder_id',
						'=','purchaseorder.id')->
				leftjoin('deliveryorder','deliveryorder.id','=','purchaseorderdeliveryorder.deliveryorder_id')->
				where('merchantpurchaseorder.merchant_id', $mycompany->id)->
				select('purchaseorder.*', 'deliveryorder.receiver_location_id',
					'deliveryorder.systemid as dsystemid', 'deliveryorder.id as DO_id',
					'deliveryorder.deliveryman_user_id'
				)->
				get();

			$purchaseOrderRecord = $purchaseOrderRecord->filter(function($f) {
	
				$product  = DB::table('product')->
					join('purchaseorderproduct','purchaseorderproduct.product_id','=','product.id')->
					where('purchaseorderproduct.purchaseorder_id',$f->id)->
					whereNotIn('ptype', ['oilgas'])->
					first();
				return !empty($product);
			});	

			$purchaseOrderRecord->map(function($f) use ($data) {
				$packet = collect();
				$packet->source_doc 		= $f->systemid;
				$packet->source_doc_url		= url("/purchaseorder/$f->id");
				$packet->source				= 'Purchase Order';
				$packet->date				= date("dMy", strtotime($f->created_at));
				$packet->delivery_id		= $f->dsystemid ?? '' ; 
				$packet->delivery_url		= url("/deliveryorder/purchaseorder/$packet->delivery_id");
				$packet->to					= DB::table('location')->
												find($f->receiver_location_id)->branch ?? '';
				$packet->delivery_man		=  DB::table('users')->find($f->deliveryman_user_id)->name ?? 'Deliveryman';;
				$packet->deliveryman_id 	=  $f->deliveryman_user_id;
				$packet->status				= 'Pending';
				$packet->created_at			= $f->created_at;
				$packet->from_location_id	= $f->receiver_location_id;
				$packet->dsystemid	=	$f->dsystemid;

				if ($f->is_void  == 1)
					$packet->status			= 'Void';
				elseif (!empty($f->dsystemid))
					$packet->status			= 'Approved';
				else
					$packet->status			= 'Pending';
			
				$data->push($packet);
			});

			if ($request->has('location_id') && $request->location_id != 'all') {
				$data = $data->where('from_location_id', $request->location_id);
			}

			$data = $data->sortByDESC('created_at')->values();

			return Datatables::of($data)->
				addIndexColumn()->

				addColumn('source_doc', function ($data) {
					return <<<EOD
				<a href="javascript: openNewTabURL('$data->source_doc_url');"
					style="text-decoration:none"
					>$data->source_doc</a>
EOD;
				})->
				
				addColumn('source', function ($data) {
					return $data->source;
				})->
				
				addColumn('date', function ($data) {
					return $data->date;
				})->
				
				addColumn('delivery_id', function ($data) {
				
					return <<<EOD
					<a href="javascript: openNewTabURL('$data->delivery_url');"
						style="text-decoration:none">$data->delivery_id</a>
EOD;

				})->
				
				addColumn('to', function ($data) {
					return $data->to;
				})->
				
				addColumn('delivery_man', function ($data) {
					return <<<EOD
					<a href="javascript:deliveryman($data->deliveryman_id,'$data->delivery_id')"
						style="text-decoration:none">$data->delivery_man</a>
EOD;
				})->
				
				addColumn('status', function ($data) {
					return $data->status;
				})->
				
				addColumn('yellowcrab', function ($data) {
					return '';
				})->
				
				addColumn('bluecrab', function ($data) {
					return '';
				})->
				
				escapeColumns([])->
				make(true);


		} catch (\Exception $e) {
			\Log::error([
				"Error"	=>	$e->getMessage(),
				"File"	=>	$e->getFile(),
				"Line"	=>	$e->getLine()
			]);
			abort(404);
		}
	}

    public function po_products(Request $request)
	{
        $products =array();
      //  $rack_id = $request->get('rack_id');
      //  $product_id = $request->get('product_id');

        $report_id = $request->get('report_id');
        if($report_id) {
			$products = PurchaseOrder::select('product.thumbnail_1 as thumbnail_1','product.systemid as systemid','product.id as product_id'
				,'product.name as product_name','purchaseorderproduct.quantity'
				,'purchaseorderproduct.created_at as lastupdate')
				->join('purchaseorderproduct','purchaseorderproduct.purchaseorder_id','=','purchaseorder.id')
				->join('product','product.id','=','purchaseorderproduct.product_id')
				->where('purchaseorder.id',$report_id)
				->get();	
		}

	return Datatables::of($products)->
		addIndexColumn()->
		addColumn('product_id', function ($product) {
			return $product->systemid;
		})->
		addColumn('product_name', function ($product) {
			return '<img src="' . asset('images/product/' .
				$product->product_id . '/thumb/' . 
				$product->thumbnail_1) .
				'" style="height:25px;width:25px;
				object-fit:contain;margin-right:8px;">' .
				$product->product_name;
		})->
		addColumn('product_qty', function ($product) {
			return $product->quantity;
		})->
		addColumn('product_checker', function ($product) {
			 return $product->quantity;
		})->
		addColumn('product_difference', function ($product) {
			 return 0;
		})->
		escapeColumns([])->
		make(true);		  
	}


    public function products(Request $request)
    {
        $products =array();
      //  $rack_id = $request->get('rack_id');
      //  $product_id = $request->get('product_id');
        $report_id = $request->get('report_id');
        if($report_id) {
			$products = stockreportproduct::select('product.thumbnail_1 as thumbnail_1','product.systemid as systemid','product.id as product_id'
				,'product.name as product_name','stockreportproduct.quantity','stockreportproduct.received'
				,'stockreport.type','stockreportproduct.created_at as lastupdate')
				->join('product','product.id','=','stockreportproduct.product_id')
				->join('stockreport','stockreportproduct.stockreport_id','=','stockreport.id')
				->where('stockreport.id',$report_id)
				->get();
        }

		return Datatables::of($products)->
			addIndexColumn()->
			addColumn('product_id', function ($product) {
				return $product->systemid;
			})->
			addColumn('product_name', function ($product) {
			   return '<img src="' . asset('images/product/' . $product->product_id . '/thumb/' . $product->thumbnail_1) . '" style="height:25px;width:25px;object-fit:contain;margin-right:8px;">' . $product->product_name;
			})->
			addColumn('product_qty', function ($product) {
				return $product->quantity;
			})->
			addColumn('product_checker', function ($product) {
				 return $product->received;
			})->
			addColumn('product_difference', function ($product) {
				 return $product->received - $product->quantity;
			})->
			escapeColumns([])->
			make(true);
    }	


    public function showdeliveryManView() {
        return view('logistics.deliveryman');
	}


	public function showVehicleManagementView() {
		$user_data = new UserData();
		$company_id = $user_data->company_id();

		$delivery = DB::table('users')->
			join('usersfunction','usersfunction.user_id','=','users.id')->
			leftjoin('function','function.id','=','usersfunction.function_id')->
			where(["usersfunction.company_id" => $company_id, 
				'function.slug'=>'dlv'])->
			whereNull('usersfunction.deleted_at')->
			select("users.id as id", "users.name as name")->
			get();
			//first get franchise id
			$franchisemerchant = DB::table('franchisemerchant')->
			where(["franchisemerchant.franchisee_merchant_id" => $company_id])->
			get()->first();
			$franchise = DB::table('franchise')->
			where(["franchise.id" => $franchisemerchant->franchise_id])->
			get()->first();
			$locations = DB::table('merchantlocation')->
			join('location','location.id','=','merchantlocation.location_id')->
			where(["merchantlocation.merchant_id" => $franchise->owner_merchant_id])->
			whereNotNull('location.branch')->
			get();
			$actual_locations= array();
			$i =0;
			foreach($locations as $loc){
				$lic_locationkey = DB::table('lic_locationkey')->
				where('location_id', $loc->location_id)->get()->first();
				if($lic_locationkey!=null){

					$location = locationipaddr::
						where('location_id', $loc->location_id)->
						get()->first();

					/* Protect against truncated locationipaddr table */
					if (!empty($location)) {
						$url = $location->ipaddr;
						$lic = $lic_locationkey->license_key;

						$responseCheckKey = $this->checkLicKey(
							array('license_key'=>$lic), $url);

						if($responseCheckKey){
							$actual_locations[$i]['id'] = $loc->location_id;
							$actual_locations[$i]['branch'] = $loc->branch; 
						}
					}
				}
			}

		Log::info([
			'delivery man' => json_encode($delivery->pluck('name')),
		]);
        return view('logistics.vehiclemanagement',
			compact('delivery', 'actual_locations'));
	}


	public function vehicleManagementTable(Request $request) {
		try {
			$user_data = new UserData();

			$data = DB::table('lg_vehiclemgmt')->
				wheremerchant_id($user_data->company_id())->
				whereNull('deleted_at')->
				orderBy('id', 'DESC')->
				get();


			return Datatables::of($data)->
				addIndexColumn()->
	
				addColumn('numberPlate', function ($data) {
					$numberPlate = !empty($data->vehicle_license) ?
						$data->vehicle_license : 'Number Plate';
						return <<<EOD
						<span class='os-linkcolor' style='cursor:pointer;' 
							onclick="changeVehicleNumberPlate($data->id,
								'$data->vehicle_license',
								'$data->systemid' )">$numberPlate
						</span>
EOD;
				})->
	
				addColumn('type', function ($data) {
					$type = !empty($data->type) ? $data->type : 'Type';
					return <<<EOD
					<span class='os-linkcolor' style='cursor:pointer;'
						onclick="changeVehicleType($data->id)">$type
					</span>
EOD;
				})->

				addColumn('max_volmentric', function ($data) {
					$max_v = !empty($data->max_volumetric) ?
						$data->max_volumetric : 'Max';
					
					return <<<EOD
					<span class='os-linkcolor' style='cursor:pointer;' 
						onclick="changeVehicleMaxVolme($data->id,
							$data->max_volumetric)">$max_v
					</span>
EOD;
				})->
				
				addColumn('capabilities', function ($data) {
					$capabilities = !empty($data->capabilities) ?
						$data->capabilities : "Capabilities";

					return <<<EOD
					<span class='os-linkcolor' style='cursor:pointer;'
						onclick="changeVehicleCap($data->id)">$capabilities
					</span>
EOD;
				})->

				addColumn('ownership', function ($data) {
					$ownership = !empty($data->ownership) ?
						$data->ownership : "Ownership";

					return <<<EOD
					<span class='os-linkcolor' style='cursor:pointer;'
						onclick="changeVehicleOwnership($data->id)">$ownership
					</span>
EOD;
				})->

				addColumn('deliveryman', function ($data) {
					$deliveryman = !empty($data->deliveryman_user_id) ? 
						\App\User::find($data->deliveryman_user_id)->name ??
						'Deliveryman' :"Deliveryman";

					return <<<EOD
					<span class='os-linkcolor' style='cursor:pointer;'
						onclick='deliveryman($data->id)' >$deliveryman
					</span>
EOD;
				})->

				addColumn('location', function ($data) {

					$locationdata = DB::table('lg_vehiclemgmtlocation')->
				select(DB::raw("GROUP_CONCAT(location_id SEPARATOR '-') as `location` ,  count(location_id) as total_locations "))->	
				where('vehiclemgmt_id', $data->id)->
				get()->first();

					return <<<EOD
					<span class='os-linkcolor' style='cursor:pointer;'
						onclick='changeVehicalLocation($data->id ,
						"$locationdata->location")'
						data-val='$locationdata->location'>
						$locationdata->total_locations
					</span>
EOD;
				})->

				addColumn('pinkcrab', function ($data) {
					return <<< EOD
						<img src="/images/pinkcrab_50x50.png"
							onclick="changeVehicleGps($data->id,'')"
							style="width:25px;height:25px;cursor:pointer"/>
EOD;
				})->

				addColumn('bluecrab', function ($data) {
					$class ='';
					if($data->status=='rfid_active'){
						$class ='test';
					}
					return <<< EOD
						<span class="$class">
							<img src="/images/bluecrab_50x50.png"
							onclick="changeVehicleRifd($data->id,
							'$data->rfid_no','$data->status')"
							style="width:25px;height:25px;cursor:pointer"/>
						</span>
EOD;
				})->

				addColumn('redcrab', function ($data) {
					return <<< EOD
						<img src="/images/redcrab_50x50.png"
							onclick="deleteVehicle($data->id)"
							style="width:25px;height:25px;cursor:pointer"/>
EOD;
				})->
				escapeColumns([])->
				make(true);

		} catch (\Exception $e) {
			\Log::info([
				"Error" 	=> $e->getMessage(),
				"File"		=> $e->getFile(),
				"Line No"	=> $e->getLine()
			]);
			abort(404);
		}
	}


	public function vehicleManagementAddVehicle() {

		try {
			$objsystemid = new SystemID('vehicle');
			$systemid = $objsystemid->__toString();
			$user_data = new UserData();
			$last_id = DB::table('lg_vehiclemgmt')->insertGetId([

				'systemid'			=> $systemid,
				'vehicle_license'	=> '',
				'location_id'       => '',
				'type'				=> '',
				'max_volumetric'	=> '',
				'capabilities'		=> '',
				'ownership'			=> '',
				'rfid_no'			=> '',
				'status'			=> 'rfid_inactive',
				"merchant_id"		=> $user_data->company_id(),
				'created_at'		=> date("Y-m-d H:i:s"),
				'updated_at'		=> date("Y-m-d H:i:s")
			]);

			$vehicle_data= DB::table('lg_vehiclemgmt')->
				where('id' , $last_id)->get()->first();


			Log::debug('vehicleManagementAddVehicle: vehicle_data='.
				json_encode($vehicle_data));

			
			
		} catch (Exception $e) {
			Log::info([
				"Error" 	=> $e->getMessage(),
				"File"		=> $e->getFile(),
				"Line No"	=> $e->getLine()
			]);
			abort(404);
		}
	}
public function checkLicKey($data , $url){
		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL => "http://".$url."/api/checkLicKey",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30000,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "POST",
			CURLOPT_POSTFIELDS => json_encode($data),
			CURLOPT_HTTPHEADER => array(
				// Set here requred headers
				"accept: */*",
				"accept-language: en-US,en;q=0.8",
				"content-type: application/json",
			),
		));
		$response = curl_exec($curl);
		$err = curl_error($curl);
		curl_close($curl);

		Log::debug('checkLicKey: response='.
			json_encode($response));

		if ($err) {
			//echo "cURL Error #:" . $err;
			return 	$err;
		} else {
			return $response;
		}
	}

	public function downVehicleData($data , $url){
		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL => "http://".$url."/api/downVehicleData",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30000,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "POST",
			CURLOPT_POSTFIELDS => json_encode($data),
			CURLOPT_HTTPHEADER => array(
				// Set here requred headers
				"accept: */*",
				"accept-language: en-US,en;q=0.8",
				"content-type: application/json",
			),
		));
		$response = curl_exec($curl);
		$err = curl_error($curl);
		curl_close($curl);

		Log::debug('downVehicleData: response='.
			json_encode($response));

		if ($err) {
			//echo "cURL Error #:" . $err;
			return 	$err;
		} else {
			return $response;
		}
	}


	public function updatedownVehicleData($data , $url){
		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL => "http://".$url."/api/updateDownVehicleData",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30000,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "POST",
			CURLOPT_POSTFIELDS => json_encode($data),
			CURLOPT_HTTPHEADER => array(
				// Set here requred headers
				"accept: */*",
				"accept-language: en-US,en;q=0.8",
				"content-type: application/json",
			),
		));
		$response = curl_exec($curl);
		$err = curl_error($curl);
		curl_close($curl);

		if ($err) {
			//echo "cURL Error #:" . $err;
			return 	$err;
		} else {
			  return $response;
		}
	}


	public function vehicleManagementUpdateVehicle(Request $request) {
		try {
			$allInputs = $request->all();
			$validation = Validator::make($allInputs, [
				'fk' 	=> "required",
				"field" => "required"
			]);	

			if ($validation->fails())
				throw new \Exception("Validation Failed");
	
			$user_data = new UserData();
			$update_array = [];

			switch ($request->field) {
				case 'type':
					$update_array["type"] = $request->data;
					break;
				case "capabilities":
					$update_array["capabilities"] = $request->data;
					break;
				case "ownership":
					$update_array["ownership"]  = $request->data;
					break;
				case 'deliveryman':
					$update_array["deliveryman_user_id"]  = $request->data;
					break;
				case "Max volumetric":
					$update_array["max_volumetric"]  = $request->data;
					break;
				case "Number Plate":
					$update_array["vehicle_license"]  = $request->data;
					break;
				case "delete":
					$update_array["deleted_at"]  = date("Y-m-d H:i:s");
					break;
				case "RFID No":
					$update_array["rfid_no"]  = $request->data;
					break;
				case "RFID Status":
					$update_array["status"]  = $request->data;
					break;
				case "Location":
					$update_array["Location"]  = 'Location';
					break;
				default:
					throw new \Exception("Invalid field");
					break;
			}

			if(isset($update_array["Location"]) &&
				$update_array["Location"]=='Location' ){

				DB::table('lg_vehiclemgmtlocation')->
				where('vehiclemgmt_id', $request->fk)->
				delete();
				
				if($request->data!=null){
					foreach($request->data as $d){
						DB::table('lg_vehiclemgmtlocation')->insert([
							'vehiclemgmt_id'	=> $request->fk,
							'location_id'		=> $d,
							'created_at'		=> date("Y-m-d H:i:s"),
							'updated_at'		=> date("Y-m-d H:i:s")
						]);
					}
				}


			} else {

			
				$update_array["updated_at"]  = date("Y-m-d H:i:s");
				DB::table('lg_vehiclemgmt')->
				where('id', $request->fk)->
				where("merchant_id", $user_data->company_id())->
				update($update_array);
			}

				//if no location status must be inactive

				$lg_vehiclemgmtlocation_data = DB::table('lg_vehiclemgmtlocation')->
				where('vehiclemgmt_id', $request->fk)->get()->first();
				if(empty($lg_vehiclemgmtlocation_data)){
					$new_array["status"]='rfid_inactive';	
					$new_array["updated_at"]  = date("Y-m-d H:i:s");
					DB::table('lg_vehiclemgmt')->
					where('id', $request->fk)->
					where("merchant_id", $user_data->company_id())->
					update($new_array);
				}
				
			

			if ($request->field == 'RFID Status' ||
				$request->field == 'Number Plate'  || $request->field == 'delete' || $request->field == 'Location' ) {

				$vehicle_data = DB::table('lg_vehiclemgmt')->
				where('id', $request->fk)->
				where("merchant_id", $user_data->company_id())->
				get()->first();
				if( ($vehicle_data!=null && 
						$lg_vehiclemgmtlocation_data!=null &&
						$vehicle_data->vehicle_license!='') ||
						$request->field == 'delete' ){
					
						$selfMerchantId = $user_data->company_id();
						$location = locationipaddr::
							where('company_id', $selfMerchantId)->
							groupBy('location_id')->get();
						foreach ($location as $loc) {
							$d = $this->updatedownVehicleData(
								(array)$vehicle_data,
								$loc->ipaddr
							);
						}
					
					
				}
			}

			if ($request->field != 'delete') 
				$msg = ucfirst($request->field)." updated";
			else
				$msg = "Record deleted";

			return view('layouts.dialog', compact('msg'));

		} catch (Exception $e) {
			Log::info([
				"Error" 	=> $e->getMessage(),
				"File"		=> $e->getFile(),
				"Line No"	=> $e->getLine()
			]);
			abort(404);
		}
	}


	public function updateDeliverymanCentralAdmin(Request $request) {
		try {

			$d_man_id = $request->d_man_id;
			$deliveryorder_id = $request->deliveryorder_id;
			DB::table('deliveryorder')->where([
				'systemid' =>	$deliveryorder_id
			])->update([
				"deliveryman_user_id"	=> $d_man_id
			]);

			$msg = "Deliveryman updated";
			return view('layouts.dialog', compact('msg'));
		}  catch (Exception $e) {
			Log::info([
				"Error" 	=> $e->getMessage(),
				"File"		=> $e->getFile(),
				"Line No"	=> $e->getLine()
			]);
			abort(404);
		}

	}

	public function showtallyChartView() {
		return view('logistics.tallychart');
	}


	public function showFormRegionView() {
		return view('logistics.formregion');
	}


	public function showtallyByProductView() {
		return view('logistics.tallybyproduct');
	}

	public function showpickUpControlView() {
		return view('logistics.pickupcontrol');
	}
}
