<?php

namespace App\Http\Controllers;

use App\Models\opos_locationterminal;
use App\Models\opos_terminalproduct;
use App\Models\opos_calibration;
use Carbon\Carbon;
use Illuminate\Http\Request;
use \App\Classes\SystemID;
use DB;
use Log;
use Exception;
use \App\Classes\UserData;
use \App\Models\OgFuel;
use \App\Models\terminal;
use \App\Models\location;
use \App\Models\attachment;
use \App\Models\locationterminal;
use Yajra\DataTables\Facades\DataTables;

class OposCalibrationController extends Controller
{
	public function prefData(Request $request)
    {
        $terminal_id = (\Session::get('terminalID'));
        $terminal = strval($terminal_id);
        $this->user_data = new UserData();
        $company_id = $this->user_data->company_id();
        $data = $request->all();
        $btype = terminal::where(['systemid' => $terminal])->
			pluck('address_preference')->first();

        if($btype == "branch"){
        	$terminal_id = terminal::where(['systemid' => $terminal])->
				pluck('id')->first();
	        $location_id = locationterminal::where([
				'terminal_id' => $terminal_id])->
				pluck('location_id')->first();

	        $address_line1 = location::where(['id' => $location_id])->
				pluck('address_line1')->first();

	        $address_line2 = location::where(['id' => $location_id])->
				pluck('address_line2')->first();

	        $address_line3 = location::where(['id' => $location_id])->
				pluck('address_line3')->first();

	        $sst = terminal::where(['systemid' => $terminal])->
				pluck('show_sst_no')->first();

	        $logo = terminal::where(['systemid' => $terminal])->
				pluck('local_logo')->first();

	        $attachmentId = attachment::where(['company_id' => $company_id])->
				latest('created_at')->first();

			if (!empty($attachmentId)) {
				$logo_address = "/company/" . $company_id. "/attachment/" .
					$attachmentId["id"] . "/" . $attachmentId["filename"];
			} else {
				$logo_address = null;
			}

	        $myArray = array( "btype" => $btype,
				"address_line1" => $address_line1,
				"address_line2" => $address_line2,
				"address_line3" => $address_line3,
				"sst" => $sst,
				"logo" => $logo,
				"logo_address" => $logo_address,
				"terminal_id" => $terminal_id,
				"attachmentId" => $attachmentId
			);
	        return response()->json($myArray);


        } else if ($btype == "company") {
	        $sst = terminal::where(['systemid' => $terminal])->
				pluck('show_sst_no')->first();
	        $logo = terminal::where(['systemid' => $terminal])->
				pluck('local_logo')->first();

	        $attachmentId = attachment::where(['company_id' => $company_id])->
				latest('created_at')->first();


			Log::debug('company_id='.$company_id);
			Log::debug('attachment='.json_encode($attachmentId));

			if (!empty($attachmentId)) {
				$logo_address = "/company/" . $company_id. "/attachment/" .
					$attachmentId["id"] . "/" . $attachmentId["filename"];
			} else {
				$logo_address = null;
			}

	        $myArray = array(
				"btype" => $btype,
				"sst" => $sst, "logo" => $logo,
				"logo_address" => $logo_address,
				"terminal_id" => $terminal_id,
				"attachmentId" => $attachmentId );

	        return response()->json($myArray);
        }
    }


	public function getSystemId() {
	    $a = new SystemID('calibration');
	    $systemId = $a->__toString();

	    return response()->json($systemId);
	}


	public function updateDb(Request $request) {
		$allinputs	= $request->all();
		$systemId   = $request->get('systemid');
		$locationId = $request->get('location_id');
		$terminalId = $request->get('terminal_id');
		$staffId    = $request->get('staff_id');
		$totalAmnt  = $request->get('total_amt');
		$pumpNo     = $request->get('pumpNo');
		$nozzle     = $request->get('nozzle');
		$logo       = $request->get('logo');
		$address    = $request->get('address');
		$companyId  = $request->get('company_id');

		Log::debug('CB updateDb(): allinputs='.json_encode($allinputs));

        $attachmentId = attachment::where(['company_id' => $companyId])->
			latest('created_at')->
			first();

		Log::debug('CB updateDb(): attachmentId='.json_encode($attachmentId));

		if (!empty($attachmentId)) {
			$logo_address = "/company/" . $companyId. "/attachment/" .
				$attachmentId["id"] . "/" . $attachmentId["filename"];
		} else {
			$logo_address = '';
		}

		Log::info([
			"SQL" => 'CB INSERT INTO opos_calibration (
                 systemid, location_id, terminal_id, staff_user_idi,
                 total_litre, pump_no, nozzle, local_logo, local_address)
				 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)',
			"data" => [
				"systemid"		=>	$systemId,    
				"locationId"	=>	$locationId,  
				"terminalId"	=>  $terminalId,  
				"staffId"		=>	$staffId,     
				"totalAmnt"		=>	$totalAmnt,
				"pumpNo"		=>	$pumpNo, 
				"nozzle"		=>	$nozzle,      
				"logo_address"	=>	$logo_address,
				"address"		=>	$address
			],
		]);

	    $query = DB::insert(
			'INSERT INTO opos_calibration (
				systemid, location_id, terminal_id, staff_user_id,
				total_litre, pump_no, nozzle, local_logo, local_address)
			VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)', [
			$systemId,
			$locationId,
			$terminalId,
			$staffId,
			$totalAmnt,
			$pumpNo,
			$nozzle,
			$logo_address,
			$address
		]);

		Log::debug('CB updateDb(): insert='.json_encode($query));

	    return response()->json($systemId . "-" . $locationId . "-" .
			$terminalId . "-" . $staffId . "-" . $logo_address . "-" .
			$address);
	}


	public function calibrationList() {
	    $data =
	    DB::select("SELECT * FROM opos_calibration ORDER BY id DESC");

	    foreach ($data as $datum){
            $datum->created_at= Carbon::parse($datum->created_at)->
			format('dMy H:i:s');
        }

	    return response()->json($data);
	}


    public function receipt_calibration_list_data(Request $request) {
        try {
			Log::debug('CB ***** receipt_calibration_list_data() *****');

            $terminal_id = terminal::where('systemid',$request->systemID)->first()->id;
            $location_id = opos_locationterminal::where('terminal_id',$terminal_id)->first()->location_id;
            $allData = opos_calibration::where('location_id',$location_id)->orderBy('created_at','desc')->get();

				return Datatables::of($allData)
					->addIndexColumn()
					->addColumn('date', function ($data) {
                    $datef= Carbon::parse($data->created_at)->
						format('dMy H:i:s');
                    return $datef;
                })
                ->addColumn('receipt_id', function ($data) {

                    $id = $data->id;
                    return '<a href="javascript:void()" style="text-decoration:none;" data-toggle="modal" data-target="#gauravModal" onclick="calListReceiptId('.$id.')">'.$data->systemid.'</a>';
                })
                ->addColumn('total', function ($data) {

					Log::debug('CB data='.json_encode($data));

                    return number_format($data->total_litre, 2);
                })
                ->escapeColumns([])
                ->make(true);


        } catch (Exception $e) {
            Log::error(
                "Error @ " . $e->getLine() . " file " . $e->getFile() .
				":" . $e->getMessage()
            );
        }
    }


	public function calibrationReceipt($id) {
		Log::debug('calibrationReceipt('.$id.')');
		$user_data = new UserData();

	    $data =
	    DB::select('
		SELECT
			opos_calibration.*,
			staff.systemid as staff_system_id
		FROM
			opos_calibration,
			staff
		WHERE
			opos_calibration.staff_user_id = staff.user_id
			AND opos_calibration.id ='.$id
		);
		
		$product_name = DB::table('og_pumpnozzle')->
			join('og_pump','og_pump.id','=','og_pumpnozzle.pump_id')->
			join('og_controller','og_controller.id','=','og_pump.controller_id')->
			join('prd_ogfuel','prd_ogfuel.id','=','og_pumpnozzle.ogfuel_id')->
			leftjoin('product','product.id','=','prd_ogfuel.product_id')->
			where([
				"og_pumpnozzle.nozzle_no" 	=> $data[0]->nozzle,
				"og_pump.pump_no"			=> $data[0]->pump_no,
				"og_controller.location_id"	=> $data[0]->location_id,
				'og_controller.company_id'	=> $user_data->company_id()
			])->
			select("product.name")->
			first();
		
		$data[0]->product_name = $product_name->name;

		$data[0]->created_at =
			Carbon::parse($data[0]->created_at)->format('dMy H:i:s');
		
		return response()->json($data);
	}

	public function formatDate($date) {
		$newDate = date("dMy h:i:s", strtotime($date));
		return response()->json($newDate);
	}

	public function productName($nozzle) {
		Log::debug('CB productName('.$nozzle.')');

		$user_data = new UserData();
		$pump_details = DB::table('og_pump')->
			join('og_controller','og_controller.id','=','og_pump.controller_id')->
			where([
				"og_pump.pump_no"			=> request()->pump_no,
				'og_controller.location_id'	=> request()->location_id,
				'og_controller.company_id'	=> $user_data->company_id()
			])->
			whereNull('og_pump.deleted_at')->
			select("og_pump.*")->
			first();

		Log::info([
			"pump_no"		=> request()->pump_no,
			'location_id'	=> request()->location_id,
			"pump_details"	=> $pump_details
		]);

		$nozzle_details = DB::table('og_pumpnozzle')->
			where([
				"pump_id"	=>	$pump_details->id,
				"nozzle_no" =>	$nozzle
			])->
			whereNull('deleted_at')->
			first();

		Log::info([
			"pump_id"		=>	$pump_details->id,
			"nozzle_no" 	=>	$nozzle,
			"nozzle_details"=>	$nozzle_details
		]);


		$product_name = OgFuel::find($nozzle_details->ogfuel_id)->
			product_name()->first()->name;

		Log::debug('CB product_name='.$product_name);

	    return response()->json($product_name);
	}
}
