<?php
namespace App\Http\Controllers;
use App\Models\combine;
use App\Models\openBill;
use App\Models\openBillProduct;
use App\Models\opos_promo;
use App\Models\opos_promo_location;
use App\Models\opos_promo_product;
use App\Models\opos_receipt;
use App\Models\oposFtype;
use App\Models\platopenbillproductspecial;
use App\Models\productpreference;
use App\Models\reserve;
use App\Models\skipTable;
use App\Models\skipTableProduct;
use App\Models\skipTableProductSpecial;
use App\Models\splitTable;
use App\Models\opos_receiptproduct;
use App\Models\opos_extreceiptparam;
use App\User;
use App\Models\OgFuel;
use App\Models\OgFuelMovement;
use Carbon\Carbon;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Matrix\Exception;
use phpDocumentor\Reflection\Types\Null_;
use Yajra\DataTables\DataTables;
use \App\Classes\SystemID;
use \App\Classes\UserData;
use \App\Models\location;
use \App\Models\locationterminal;
use \App\Models\membership;
use \App\Models\merchantlocation;
use \App\Models\FranchiseMerchantLoc;
use \App\Models\merchantproduct;
use \App\Models\merchantprd_category;
use \App\Models\prd_inventory;
use \App\Models\product;
use \App\Models\restaurant;
use \App\Models\terminal;
use \App\Models\usersrole;
use \App\Models\voucher;
use \App\Models\warranty;
use \Illuminate\Support\Facades\Auth;
use App\Models\Company;
use App\Http\Controllers\OposComponentController;
use App\Models\prdcategory;
use App\Models\prd_subcategory;
use \App\Models\prd_special;
use \App\Models\productspecial;
use \App\Models\opos_btype;
use \App\Models\opos_terminalproduct;
use \App\Models\opos_refund;
use App\Models\opos_tablename;
use Log;
use DB;
use Milon\Barcode\DNS1D;
use App\Models\opos_receiptdetails;
use App\Models\takeaway;
use \App\Models\productcolor;
use \App\Models\Merchant;
use \App\Models\opos_brancheod;
use \App\Models\opos_eoddetails;
use \App\Models\opos_itemdetails;
use \App\Models\opos_itemdetailsremarks;
use \App\Models\opos_receiptproductspecial;
use \App\Models\locationproduct;
use \App\Models\opos_locationterminal;
use \App\Models\StockReport;
use \App\Models\stockreportremarks;
use \App\Models\opos_damagerefund;
use \App\Models\Staff;
use \App\Models\opos_wastage;
use \App\Models\opos_wastageproduct;
use \App\Models\productbarcode;
use \App\Models\warehouse;
use \App\Models\rack;
use \App\Models\rackproduct;
use \App\Models\stockreportproduct;
use \App\Models\stockreportproductrack;
use \App\Models\productbarcodelocation;
use \App\Models\voucherproduct;
use Illuminate\Support\Facades\Schema;
use \App\Classes\thumb;
use App\Models\FoodCourt;
use App\Models\FranchiseMerchantLocTerm;
use App\Models\voucherlist;
use App\Models\FoodCourtMerchantTerminal;
use App\Models\FoodCourtMerchant;


class OpossumController extends Controller
{

    protected $user_data;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('CheckRole:ret');
    }


    public function index()
    {
        $this->user_data = new UserData();
		$model = new terminal();
		$company_id = $this->user_data->company_id();

        $ids  = merchantlocation::
			join('location','location.id','=',
				'merchantlocation.location_id')->
            where('merchantlocation.merchant_id',
				$this->user_data->company_id())->
            whereNull('location.deleted_at')->
            pluck('merchantlocation.location_id');

		//dd($ids);
            Log::debug('company_id' . json_encode($this->user_data->company_id()));
		// $fids = FranchiseMerchantLoc::
		// 	join('location','location.id','=',
		// 		'franchisemerchantloc.location_id')->
        //     where('franchisemerchantloc.franchisemerchant_id',
		// 		$this->user_data->company_id())->
        //     whereNull('location.deleted_at')->
        //     pluck('franchisemerchantloc.location_id');

            $fids = FranchiseMerchantLoc::
            join('location','location.id','=',
		 		'franchisemerchantloc.location_id')->
			join('franchisemerchant','franchisemerchant.id','=',
				'franchisemerchantloc.franchisemerchant_id')->
            where('franchisemerchant.franchisee_merchant_id',
				$this->user_data->company_id())->
            whereNull('location.deleted_at')->
            pluck('franchisemerchantloc.location_id');

	//	$ids = array_merge($ids->toArray(),
	//		$fids->toArray());	
    	

        $foodCourtTerminalIds = FoodCourt::
			join('foodcourtmerchant', 'foodcourtmerchant.foodcourt_id',
				'=', 'foodcourt.id')->
			join('foodcourtmerchantterminal',
				'foodcourtmerchantterminal.foodcourtmerchant_id',
				'=', 'foodcourtmerchant.id')->
			where('foodcourtmerchant.tenant_merchant_id',
				$this->user_data->company_id())->
			pluck('foodcourtmerchantterminal.terminal_id');

        $terminalExcludeIds = FoodCourt::
			join('foodcourtmerchant', 'foodcourtmerchant.foodcourt_id',
				'=', 'foodcourt.id')->
			join('foodcourtmerchantterminal',
				'foodcourtmerchantterminal.foodcourtmerchant_id',
				'=', 'foodcourtmerchant.id')->
			where('foodcourt.owner_merchant_id',
				$this->user_data->company_id())->
			whereNotIn('foodcourtmerchantterminal.terminal_id',
				$foodCourtTerminalIds)->
			pluck('foodcourtmerchantterminal.terminal_id');

        $franchiseTerminalIds = FranchiseMerchantLocTerm::
			join('franchisemerchantloc', 'franchisemerchantloc.id', '=',
				'franchisemerchantlocterm.franchisemerchantloc_id')->
			join('franchisemerchant','franchisemerchant.id','=',
				'franchisemerchantloc.franchisemerchant_id')->	
        		 where('franchisemerchant.franchisee_merchant_id',
				$this->user_data->company_id())->
				pluck('franchisemerchantlocterm.terminal_id');
	
	$allTerminalIds = $foodCourtTerminalIds->toArray();
	$allTerminalIds2 = $franchiseTerminalIds->toArray();
        //Log::debug('ids='.json_encode($ids));
	//	dd( $allTerminalIds2);
	
	$data = $model->join('opos_locationterminal',
		'opos_locationterminal.terminal_id','=','opos_terminal.id')->
		whereNull('opos_terminal.deleted_at')->
		whereIn('location_id', $ids)->
		whereNotIn('opos_terminal.id', $terminalExcludeIds)->
		orWhereIn('opos_terminal.id', $allTerminalIds)->
		orderby('opos_terminal.created_at', 'asc')->
	    select(
			'opos_locationterminal.location_id',
			'opos_locationterminal.terminal_id'
		);

        $data2 = $model->join('franchisemerchantlocterm',
            'franchisemerchantlocterm.terminal_id','=','opos_terminal.id')
			->join('franchisemerchantloc',
            'franchisemerchantlocterm.franchisemerchantloc_id','=','franchisemerchantloc.id')->
            whereNull('opos_terminal.deleted_at')->
            whereIn('franchisemerchantloc.location_id', $fids)->
     //       whereNotIn('opos_terminal.id', $terminalExcludeIds)->
            WhereIn('opos_terminal.id', $allTerminalIds2)->
            orderby('opos_terminal.created_at', 'asc')
			->select(
				'franchisemerchantloc.location_id', 
				'franchisemerchantlocterm.terminal_id' 
			);			
		
		$merged = $data->unionAll($data2)->get();
		
		$merged = $merged->filter(function($z) use ($company_id) {
		$is_own = DB::table('franchisemerchantlocterm')->
			join('franchisemerchantloc','franchisemerchantloc.id',
				'=','franchisemerchantlocterm.franchisemerchantloc_id')->
			leftjoin('franchisemerchant','franchisemerchant.id',
				'=','franchisemerchantloc.franchisemerchant_id')->
			leftjoin('franchise','franchise.id','=','franchisemerchant.franchise_id')->
			where('franchisemerchantlocterm.terminal_id',$z->terminal_id)->
			where('franchise.owner_merchant_id',$company_id)->
			whereNull('franchise.deleted_at')->
			first();

			return empty($is_own);
		});
	
		return Datatables::of($merged)
		->addIndexColumn()
		->addColumn('loc_id', function ($location) {
			$location_systemid = location::
				find($location->location_id)->systemid ?? 0;
			return '<p data-field="loc_id" style="cursor: pointer;
				margin: 0; text-align: center;">' .
				$location_systemid . '</p>';
		})
		->addColumn('name', function ($location) {
			$terminal = location::find($location->location_id)->branch ?? 0;
			$locationname = empty($terminal) ? "Branch" : $terminal;
			return '<p class="os- linkcolor loyaltyOutput"
				data-field="branch" style=" margin: 0;"
				data-target="#branch" data-toggle="modal">' .
				$locationname . '</p>';
		})->
		addColumn('tsystem', function ($location) {
			$terminal = terminal::find($location->terminal_id);
			if ($terminal->tsystem == 'ministation')
				$terminal->tsystem = 'Mini Station';

			return ucwords($terminal->tsystem);	
		})
		->addColumn('mode', function ($location) use ($allTerminalIds,
			$foodCourtTerminalIds, $franchiseTerminalIds) {
			
			$merchantLocation = merchantlocation::where('location_id',
				$location->location_id)->first();
			
			if(in_array($location->terminal_id,
				$franchiseTerminalIds->toArray())) {
				$merchant = FranchiseMerchantLocTerm::select('company.name',
					'company.systemid','franchise.name as fname',
					'franchise.systemid as fsystemid')->
				join('franchisemerchantloc', 'franchisemerchantloc.id', '=',
					'franchisemerchantlocterm.franchisemerchantloc_id')->
				join('franchisemerchant','franchisemerchant.id','=',
					'franchisemerchantloc.franchisemerchant_id')->	
				join('franchise','franchise.id','=',
					'franchisemerchant.franchise_id')->
				join('company', 'franchise.owner_merchant_id', '=',
						'company.id')->			
				where('franchisemerchantlocterm.terminal_id',
					$location->terminal_id)->first();
			   // return 'Franchise';
			   $operatorName = '';
				$operatorId = '';

				if ($merchant != null) {
					$operatorName = $merchant->name;
					$operatorId = $merchant->systemid;
					$extra = "data-franchise='$merchant->fname' 
						data-franchise-id='$merchant->fsystemid'";
				}

				$extra = $extra ?? null;
			   
				return '<p class="os-linkcolor loyaltyOutput js-franchise-court" 
					data-field="foodcourt" data-operator-name="'.$operatorName.
					'" data-operator-id="'.$operatorId.'" style="cursor: pointer; margin: 0;
					 text-align: center;" '.$extra.'>Franchise</p>';

			} else if ($this->user_data->company_id() ==
				$merchantLocation->merchant_id &&
				!in_array($location->terminal_id, $allTerminalIds)) {
				return 'Direct';
			} else {
				
				$merchant = Company::select('company.name',
					'company.systemid')->
					where('company.id', $merchantLocation->merchant_id)->
						first();
				
				$operatorName = '';
				$operatorId = '';

				if ($merchant != null) {
					$operatorName = $merchant->name;
					$operatorId = $merchant->systemid;
				}

				return '<p class="os-linkcolor loyaltyOutput js-food-court" 
						data-field="foodcourt" data-operator-name="'.
						$operatorName.'" data-operator-id="'.$operatorId.
						'" style="cursor: pointer; margin: 0; text-align: center;">FoodCourt</p>';
			}
		})
		->addColumn('term_id', function ($location) use ($allTerminalIds, $franchiseTerminalIds) {
		$terminal = terminal::find($location->terminal_id);
			$terminal_systemid = empty($terminal->systemid) ? "Terminal ID" : $terminal->systemid;
			$merchantLocation = merchantlocation::where('location_id', $location->location_id)->first();
			$terminalId = $terminal->id;
			//check auth
			$is_auth = DB::table('userslocation')->
				where([
					"user_id"		=> Auth::User()->id,
					'location_id'	=> $location->location_id
				])->
				whereNull('deleted_at')->
				first();
			
			if (empty($is_auth) && !$this->user_data->is_super_admin() && !$this->user_data->is_king() ) {
				return <<<EOD
				$terminal_systemid
EOD;
			}
			//end check auth

			$factive = true;
			if ($this->user_data->company_id() == $merchantLocation->merchant_id &&
				!in_array($location->terminal_id, $allTerminalIds)) {
				$status = 'active';
			} else if(in_array($location->terminal_id,
				$franchiseTerminalIds->toArray())) {
				$fstatus = FranchiseMerchantLoc::
				join('franchisemerchantlocterm','franchisemerchantloc.id','=',
					'franchisemerchantlocterm.franchisemerchantloc_id')->
				join('franchisemerchant','franchisemerchant.id','=',
					'franchisemerchantloc.franchisemerchant_id')->
				where('franchisemerchantlocterm.terminal_id',
					$location->terminal_id)->
				first();
				$status = 'active';
				if(!is_null($fstatus)){
					if($fstatus->status == 'inactive'){
					//$status = $fstatus->status;
						$factive = false;
					}
				}
			} else {
							
				$status = FoodCourt::
				join('foodcourtmerchant', 'foodcourtmerchant.foodcourt_id', '=', 
					'foodcourt.id')->
				join('foodcourtmerchantterminal',
					'foodcourtmerchantterminal.foodcourtmerchant_id', '=', 
					'foodcourtmerchant.id')->
				where('foodcourtmerchantterminal.terminal_id', $terminalId)->
				where('foodcourtmerchant.tenant_merchant_id', $this->user_data->company_id())->
				where('foodcourtmerchant.status', 'active')->get()->count() > 0 ?
					'active' : 'inactive';
			}
			if($factive){
				return '<p class="os-linkcolor loyaltyOutput" data-active-status="'.
				$status.'" data-field="term_id" style="cursor: pointer; margin: 0;
					text-align: center;" data-target="#branch"
					data-toggle="modal">' . $terminal_systemid . '</p>';
			} else {
				return '<p class="loyaltyOutput" style="margin: 0;
					text-align: center;">' . $terminal_systemid . '</p>';
			}
		})
		->addColumn('deleted', function ($location) use ($allTerminalIds, $franchiseTerminalIds) {
			//$merchantLocation = merchantlocation::where(
			 //   'location_id', $location->location_id)->first();
			 $transaction = opos_receipt::where('terminal_id', $location->terminal_id)->get();
			if(in_array($location->terminal_id,
				$franchiseTerminalIds->toArray())) {
				return '';
			} else {
				if($transaction->count() != 0){
					return '<div class="text-center">
						<img class="" src="/images/redcrab_50x50.png"
						style="width:25px;height:25px;cursor:not-allowed;
						filter:grayscale(100%) brightness(200%)"/>
						</div>';

				} else {
					return '<div data-field="deleted"
						class="remove text-center">
						<img class="" src="/images/redcrab_50x50.png"
						style="width:25px;height:25px;cursor:pointer"/>
						</div>';
				}
			}
		})
		->addColumn('licence_key', function ($memberList) {
			$lic_terminalkey =  DB::table('lic_terminalkey')->
				where('terminal_id', $memberList->terminal_id)->
				first();

			if (empty($lic_terminalkey)) {
				$html =  <<< EOD
				<span class="os-linkcolor" style="cursor:pointer" 
					onclick="generate_license_key($memberList->terminal_id)">
					License Key
				</span>
EOD;
			} else {
				$key = $lic_terminalkey->license_key;
				$formated_key = '';
				
				for($x = 0; $x < strlen($key); $x++) {
					if ($x % 4 == 0 && $x != 0)
						$formated_key .= '-';
					$formated_key .= $key[$x];
				} 

				$html = <<< EOD
				<span class="os-linkcolor" style="cursor:pointer" 
					onclick="license_key_modal('$formated_key')">
					Issued
				</span>
EOD;	
			}
			return $html;
		})

		->escapeColumns([])
		->make(true);
    }


	public function generateLicenseKey(Request $request) {
		try {

			$validation		= Validator::make($request->all(), [
				"terminal_id"	=>	"required"
			]);

			if ($validation->fails())
				throw new \Exception("validations_fails");

			$user_data		= new UserData();
			
			$lic			= substr(preg_replace("/[^a-zA-Z0-9]+/", "", 
				base64_encode(random_bytes(16))), 0, 16);

			$terminal_id	= $request->terminal_id;

			$location_id = DB::table('opos_locationterminal')->
				where('terminal_id', $terminal_id)->
				first()->location_id ?? 0;

			$isExist = DB::table('lic_terminalkey')->
				where('terminal_id', $terminal_id)->
				first();
		
			if (empty($isExist)) {
				DB::table('lic_terminalkey')->insert([
					"license_key"	=>	$lic,
					'terminal_id'	=>	$terminal_id,
					"created_at"	=>	date("Y-m-d H:i:s"),
					"updated_at"	=>	date("Y-m-d H:i:s")
				]);
				
				DB::table('terminalcount')->insert([
					'terminal_id'			=> $terminal_id,
					'allowed_receipt_count'	=> 30000,
					'created_at'			=> now(),
					'updated_at'			=> now()
				]);

				$this->updateRealtimeTerminals($location_id);

				$msg = "Licence key generated";
				return view('layouts.dialog', compact('msg'));

			} else {
				Log::error('generateLicenseKey: Detected double '.
				'entry in lic_terminalkey for '.$terminal_id);
			}

		} catch (\Exception $e) {
			Log::error([
				"Error"	=>	$e->getMessage(),
				"File"	=>	$e->getFile(),
				"Line"	=>	$e->getLine()
			]);
			abort(404);
		}
	}


    public function terminalExists($terminalId) {
        $terminal = terminal::where('systemid', $terminalId)->first();
        if ($terminal != null) {
            return response()->json(['msg' => 'Terminal exist', 'status' => 'true']);
        } else {
            return response()->json(['msg' => 'Terminal has been deleted', 'status' => 'false']);
        }
    }
	
    public function terminalIdExists($terminalId) {
        $terminal = terminal::where('id', $terminalId)->first();
        if ($terminal != null) {
            return response()->json(['msg' => 'Terminal exist', 'status' => 'true']);
        } else {
            return response()->json(['msg' => 'Terminal has been deleted', 'status' => 'false']);
        }
    }	

	public function updateRealtimeTerminals($location_id) {
		try {

			$terminal_data = DB::table('opos_terminal')->
					join('opos_locationterminal', 'opos_locationterminal.terminal_id','opos_terminal.id')->
					where('opos_locationterminal.location_id', $location_id)->
					get()->toArray();

			$lic_terminalkey = DB::table('lic_terminalkey')->
				join('opos_locationterminal', 
					'opos_locationterminal.terminal_id','lic_terminalkey.terminal_id')->
				join('opos_terminal','opos_terminal.id','lic_terminalkey.terminal_id')->
				where('opos_locationterminal.location_id',$location_id)->
				select("lic_terminalkey.*","opos_terminal.systemid")->
				get();

			$post = [
				"terminal_data"		=>	json_encode($terminal_data),
				"lic_terminalkey"	=>	json_encode($lic_terminalkey)
			];
		
			/*		$terminals = DB::table('opos_terminal')->
				join('opos_locationterminal','opos_locationterminal.terminal_id','opos_terminal.id')->
				join('userslocation','userslocation.location_id','opos_locationterminal.location_id')->
				select('opos_terminal.*')->
				get();
			 */
			
			$location_ = DB::table('locationipaddr')->
				where('location_id', $location_id)->
				get();

			foreach ($location_ as $l) {
				if (!empty($l->tsystem)) {
					$url = "http://$l->tsystem/interface/update_data";
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
					break;
				}
			}

		} catch (\Exception $e) {
			$err = [
				"Error"	=>	$e->getMessage(),
				"File"	=>	$e->getFile(),
				"Line"	=>	$e->getLine()
			];

			\Log::info($err);
		}
		return $err ?? true;
	}



    public function store(Request $request)
    {
        try {

            $validation = Validator::make($request->all(), [
                'id' => 'required',
            ]);

            $location = location::find($request->id);

            if ($validation->fails() || !$location) {
                throw new Exception("error_validation", 50);
            }

            $systemid = new SystemID('terminal');

            $terminal = new terminal();
            $link     = new locationterminal();

            $terminal->systemid = $systemid;
            $terminal->btype_id = 1;
            $terminal->save();

            $link->terminal_id = $terminal->id;
            $link->location_id = $location->id;
            $link->save();

            $sq_name = 'receipt_seq_' . sprintf("%06d",$terminal->id);

            \DB::select(\DB::raw("create sequence $sq_name nocache nocycle"));

            $msg    = "Terminal created successfully.";
            $return = view('layouts.dialog', compact('msg'));

        } catch (\Exception $e) {

            if ($e->getMessage() == 'error_validation') {
                $msg = "Error: Invalid location choosen.";
            } else {
                $msg = "Error: Failed tp store new terminal in database";
            }

            $return = view('layouts.dialog', compact('msg'));

            \Log::error("Error @ " . $e->getLine() . " file " . $e->getFile() . " " .
                $e->getMessage() . " | " . $msg);
        }

        return $return;

    }

    public function openOpsum()
    {
        return view('opossum.terminal-list');
    }

    public function openDingo()
    {
        $this->user_data = new UserData();
        $id              = Auth::user()->id;
        $user_roles      = usersrole::where('user_id', $id)->get();

        $is_king = \App\Models\Company::where('owner_user_id', Auth::user()->id)->first() || $this->user_data->allow_all();

        if ($is_king != null) {
            $is_king = true;
        } else {
            $is_king = false;
        }

        return view('opossum.dingo', compact('user_roles', 'is_king'));
    }

    public function newDialog()
    {
        try {
            $this->user_data = new UserData();
            $modal           = "newTerminalDialog";
            $ids             = merchantlocation::where('merchant_id', $this->user_data->company_id())->pluck('location_id');
            $location        = location::where([['branch', '!=', 'null'],['foodcourt','!=',1]])->where('warehouse', 0)->whereIn('id', $ids)->latest()->get();

            $return = view('opossum.modal', compact('modal', 'location'));

        } catch (\Exception $e) {

            $msg = "Error occured occured while storing location in database";

            $return = view('layouts.dialog', compact('msg'));

            \Log::error("Error @ " . $e->getLine() . " file " . $e->getFile() . " " .
                $e->getMessage());
        }

        return $return;
    }

    public function showEditModel(Request $request)
    {
        try {
            $allInputs  = $request->all();
            $id         = $request->get('id');
            $field_name = $request->field_name;
            $terminal_id = $id;
            $transaction = opos_receipt::where('terminal_id', $id)->get();

			/*
            Log::debug('terminal_id='.$id);
            Log::debug('terminal_id='.$terminal_id);
            Log::debug('receipt_id='.json_encode($transaction));
            Log::debug('receipt_id count='.($transaction->count()));
			*/

           // if (!empty($transaction)) {
            if($transaction->count() > 0){
                $msg = "Terminal has transactions. Unable to delete";
                $return = view('layouts.dialog', compact('msg'));
            }else{
                $data = terminal::find($terminal_id);

                if (!$data) {
                    throw new Exception("terminal_not_found", 136);
                }

                if ($field_name == 'name') {
                    $modal = "editModal";
                } else if ($field_name == 'deleted') {
                    $modal = "deleteTerminal";
                }

                $return = view('opossum.modal', compact('modal', 'data'));
            }


        } catch (\Exception $e) {
            if ($e->getMessage() == 'terminal_not_found') {
                $msg = "Terminal record not found";
            } else {
                $msg = "Error occured occured while poping up edit model";
            }

            $return = view('layouts.dialog', compact('msg'));

            \Log::error("Error @ " . $e->getLine() . " file " . $e->getFile() . " " .
                $e->getMessage());
        }

        return $return;
    }

    public function update(Request $request)
    {
        try {

            $validation = Validator::make($request->all(), [
                'terminal_id' => 'required',
            ]);

            $terminal_id = $request->get('terminal_id');
            $terminal    = terminal::find($terminal_id);

            if ($validation->fails() || !$terminal) {
                throw new Exception("error_validation", 171);
            }

            $changed = false;

            if ($request->has('name')) {
                if ($request->name != $terminal->name) {
                    $terminal->name = $request->name;
                    $changed        = true;
                }
            }

            if ($changed == true) {

                $terminal->save();
                $msg    = "Terminal updated successfully";
                $return = view('layouts.dialog', compact('msg'));

            } else {
                $return = null;
            }

        } catch (\Exception $e) {

            if ($e->getMessage() == 'error_validation') {
                $msg = "Error: Failed to update, terminal data not found.";
            } else {
                $msg = "Error: Failed tp store new terminal in database";
            }

            $return = view('layouts.dialog', compact('msg'));

            \Log::error("Error @ " . $e->getLine() . " file " . $e->getFile() . " " .
                $e->getMessage() . " | " . $msg);
        }

        return $return;
    }

    public function destroy($id)
    {

        try {

            $transaction = opos_receipt::where('terminal_id', $id)->get();
            if($transaction->count() == 0){
                $locationterminal = locationterminal::where('terminal_id', $id)->first();
                $locationterminal->delete();

                $terminal = terminal::where('id', $id)->first();
                $terminal->delete();

                $msg = "Terminal deleted successfully";
            } else {
                $msg = "Terminal has transactions. Unable to delete";
            }

            return view('layouts.dialog', compact('msg'));
        } catch (\Illuminate\Database\QueryException $ex) {
            $msg = "Some error occured";

            return view('layouts.dialog', compact('msg'));
        }
    }

    public function opossum($terminal_id)
    {

       	$user_data  = new UserData(); 
        session(['terminalID' => $terminal_id]);
        //Auth::user()->last_login = date('Y-m-d');
        $login_time = \Session::get('login_time');
        $login_time = \Carbon\Carbon::create($login_time)->
            format('dMy h:m:s');
        $terminal = terminal::where('systemid', $terminal_id)->first();

        //check if terminal is inactive
        $is_active = "true";
        $FoodCourtMerchantID = FoodCourtMerchantTerminal::where("terminal_id" , $terminal->id)->first();
        if(!is_null($FoodCourtMerchantID)){
            $FoodCourtMerchantStataus = FoodCourtMerchant::where("id" , $FoodCourtMerchantID->foodcourtmerchant_id)->first();
            if(!is_null($FoodCourtMerchantStataus)){
                if($FoodCourtMerchantStataus->status == "inactive"){
                    $is_active = "false";
                }
            }
        }

        //end check
        $locationterminal = locationterminal::where('terminal_id', $terminal->id)->first();
		if(is_null($locationterminal)){
			$locationterminal = FranchiseMerchantLocTerm::join('franchisemerchantloc',
				'franchisemerchantlocterm.franchisemerchantloc_id','=','franchisemerchantloc.id')->
			where('terminal_id', $terminal->id)->first();
		}
        $location = location::where('id', $locationterminal->location_id)->first();
        $branch = $location->branch;
        $optHour = $location;
	
		//geting pump api data
		$pump_hardware = DB::table('og_pump')->
			join('og_controller','og_controller.id','=','og_pump.controller_id')->
			where('og_controller.location_id', $location->id)->
			where('og_controller.company_id', $user_data->company_id())->
			select('og_controller.ipaddress','og_pump.pump_no')->
			get()->unique('pump_no');
		
		$nozzleFuelData = DB::table('og_pumpnozzle')->
			join('og_pump','og_pump.id','=','og_pumpnozzle.pump_id')->
			join('og_controller','og_controller.id','=','og_pump.controller_id')->
			join('prd_ogfuel','prd_ogfuel.id','=','og_pumpnozzle.ogfuel_id')->
			where([
				'og_controller.location_id'	=>	$location->id,
				'og_controller.company_id'	=>	$user_data->company_id()
			])->
			whereNull('og_pumpnozzle.deleted_at')->
			whereNull('og_pump.deleted_at')->
			whereNull('og_controller.deleted_at')->
			select('prd_ogfuel.product_id','og_pump.pump_no','og_pumpnozzle.nozzle_no')->
			get();
		
		$btype = opos_btype::all();
        if (!$btype) {
            $btype = new opos_btype();
            $btype->btype = 'food_beverage';
            $btype->description = 'Food and Beverages';
            $btype->save();

            $terminal->btype_id = $btype->id;
            $terminal->update();
        }
        $terminal_btype = opos_btype::where('id', $terminal->btype_id)->first();

        // getting mode
        $user_data = new UserData();
        $isFoodCourtTerminal = FoodCourt::
         join('foodcourtmerchant', 'foodcourtmerchant.foodcourt_id', '=', 'foodcourt.id')
            ->join('foodcourtmerchantterminal', 'foodcourtmerchantterminal.foodcourtmerchant_id', '=', 'foodcourtmerchant.id')
            ->where('foodcourtmerchant.tenant_merchant_id', $user_data->company_id())
            ->where('foodcourtmerchantterminal.terminal_id', $terminal->id)
            ->get()->count();

        $company_id = $user_data->company_id();
        $company = Company::where('id', $company_id)->first();

	$is_franchise = !empty(
	       	DB::table('franchisemerchantlocterm')->
		join('franchisemerchantloc','franchisemerchantloc.id','=','franchisemerchantlocterm.franchisemerchantloc_id')->
		leftjoin('franchisemerchant','franchisemerchant.id','=','franchisemerchantloc.franchisemerchant_id')->
		leftjoin('franchise','franchise.id','=','franchisemerchant.franchise_id')->
		where('franchisemerchantlocterm.terminal_id',$terminal->id)->
		whereNull('franchise.deleted_at')->
		first()
	);


	if ($isFoodCourtTerminal > 0) {
		$mode = 'FoodCourt';
	} else if ($is_franchise) {
		$mode = 'Franchise';	
	} else {
		$mode = "Direct";
	}
       // $mode =  $isFoodCourtTerminal > 0 ? 'FoodCourt' : 'Normal';
      //  dd($terminal);
        return view('opossum.opossum',
			compact('terminal', 'branch', 'login_time', 'optHour', 'btype', 
			'terminal_btype', 'locationterminal', 'mode', 'company' , 'is_active', 
			'location','pump_hardware','nozzleFuelData'));
    }

    public function check_location($location)
    {
		$location = array_filter(explode('/', $location));
        $path = public_path();

        foreach ($location as $key) {
            $path .= "/$key";

            Log::debug('check_location(): $path='.$path);

            if (is_dir($path) != true) {
                mkdir($path, 0775, true);
            }
        }
    }

    public function promoPicture($promoId, $filename)
    {
        $is_exist = opos_promo::where(["id" => $promoId, "thumb_photo" => $filename])->first();
        if ($is_exist) {

            $headers = array('Content-Type: application/octet-stream', "Content-Disposition: attachment; filename=$filename");
            $location = "/images/opos_promo/$promoId/$filename";
            if (!file_exists(public_path() . $location)) {
                return abort(500);
            }

            $response = \Response::file(public_path() . ($location), $headers);

            ob_end_clean();

            return $response;

        } else {
            return abort(404);
        }
    }

    /**
     *
     */
    public function checkdelPromoBundle(Request $request)
    {
        $promoId = $request->input('promoId');
		$count = opos_receiptproduct::join('opos_promo', 'opos_receiptproduct.promo_id', '=', 'opos_promo.id')->where('opos_receiptproduct.promo_id',$promoId)->get()->count();
        return response()->json(['count' => $count]);
    }	 
	 
    public function delPromoBundle(Request $request)
    {
        $promoId = $request->input('promoId');
        opos_promo::find($promoId)->delete();
        opos_promo_product::where('promo_id', $promoId)->delete();
        opos_promo_location::where('promo_id', $promoId)->delete();
        return response()->json(['status' => 'true']);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function delPromoPicture(Request $request)
    {
        try {
            $validation = Validator::make($request->all(), [
                'systemid' => 'required',
            ]);

            if ($validation->fails())  {
                throw new \Exception("validation_error", 19);
            }

            $promo = opos_promo::where('systemid', $request->systemid)->first();

            if (!$promo) {
                throw new \Exception('product_not_found', 25);
            }

            $promo->thumb_photo = null;
            $promo->save();
            $return = response()->json(array("deleted" => "True"));

        } catch (\Exception $e) {
            $return = response()->json(array("deleted" => "False"));
        }

        return $return;
    }

    /**
     * save promo image
     */
    public function savePromoPicture(Request $request)
    {
        try {

            $promoId = null;
            $systemId = null;
            $promo = opos_promo::whereNull('title')->orWhereNull('price')->oldest()->first();
            if ($promo) {
                $promoId = $promo->id;
                $systemId = $promo->systemid;
            } else {
                $systemId = new SystemID('promo');
                $promo = new opos_promo();
                $promo->systemid = $systemId;
                $promo->save();
                $promoId = $promo->id;
            }


            if ($request->hasfile('file')) {
                $file = $request->file('file');
                $extension = $file->getClientOriginalExtension(); // getting image extension
                $company_id = Auth::user()->staff->company_id;

                if (!in_array($extension, array(
                    'jpg', 'JPG', 'png', 'PNG', 'jpeg', 'JPEG', 'gif', 'GIF', 'bmp', 'BMP', 'tiff', 'TIFF'))) {
                    return abort(403);
                }

                $filename = uniqid() . '.' . $extension;

                $this->check_location("/images/opos_promo/$promoId/");
                $file->move(public_path() . ("/images/opos_promo/$promoId/"), $filename);

                $this->check_location("/images/opos_promo/$promoId/thumb/");
                $thumb = new thumb();

                $dest = public_path() . "/images/opos_promo/$promoId/thumb/thumb_" . $filename;
                $thumb->createThumbnail(
                    public_path() . "/images/opos_promo/$promoId/" . $filename,
                    $dest,
                    200);

                $promo->thumb_photo = $filename;

                $promo->save();

                $return_arr = [
                    "name" => $filename,
                    "size" => 000,
                    "src" => "/images/opos_promo/$promoId/$filename",
                    "type" => in_array($extension, array(
                        'jpg', 'JPG', 'png', 'PNG', 'jpeg', 'JPEG', 'gif', 'GIF', 'bmp', 'BMP', 'tiff', 'TIFF',
                    )) ? "image" : "doc",
                    "promoId" => $promoId,
                    "systemId" => $systemId
                ];
                return response()->json($return_arr);
            } else {
                return abort(403);
            }

        } catch (\Exception $e) {

            Log::error(
                "Error @ " . $e->getLine() . " file " . $e->getFile() .
                ":" . $e->getMessage()
            );

        }

    }

    /**
     * Getting marchant location
     * @return \Illuminate\Http\JsonResponse
     */
    public function getLocations(Request $request)
    {
        $this->user_data = new UserData();
	
	$franchiseLocations = DB::table('franchise')->
		join('franchisemerchant','franchisemerchant.franchise_id','=','franchise.id')->
		join('franchisemerchantloc','franchisemerchantloc.franchisemerchant_id','=','franchisemerchant.id')->
		where([
			'franchise.owner_merchant_id' => $this->user_data->company_id()
		])->
		whereNull('franchisemerchant.deleted_at')->
		whereNull('franchisemerchantloc.deleted_at')->
		pluck('franchisemerchantloc.location_id')->unique();
	
	if ($request->has('is_franchise')) {
		$ids = $franchiseLocations;
	} else {
		$ids = merchantlocation::where('merchant_id', $this->user_data->company_id())->pluck('location_id');
		//$ids = $ids->diff($franchiseLocations);
	}

        $location = location::where([['branch', '!=', 'null']])->whereIn('id', $ids)->latest()->get();
        $response = [
            'data' => $location,
            'recordsTotal' => location::whereIn('id', $ids)->latest()->count(),
            'recordsFiltered' => location::whereIn('id', $ids)->latest()->count()
        ];
        return response()->json($response);
    }

    /**
     * save promo location
     */
    public function savePromoLocations(Request $request)
    {
        $promoId = $request->input('promoId');
        $locations = $request->input('locations', []);
        $promo = opos_promo::find($promoId);
        $promo->locations()->delete();
        foreach($locations as $locationId) {
            $oposPromoLocation = new opos_promo_location();
            $oposPromoLocation->promo_id = $promoId;
            $oposPromoLocation->location_id = $locationId;
            $oposPromoLocation->save();
        }
        return response()->json(['status' => 'true']);

    }

     /**
     * Load Promo Usage view
     */
    public function opossumPromo()
    {
        return view('opossum.opossum_promo');
    }
   
    public function opossumPromoFranchiseeLanding()
    {
        return view('opossum.opossum_franchisee_promo');
    }

    /**
     * Loads Promo definition view
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function promoDefinition(Request $request)
    {
	try {    
		$id = Auth::user()->id;
		$user_data = new UserData();

		if ($request->has('is_franchise')) {
			if (!$request->has('f_id')) {
				throw new \Exception("f_id invalid");
			}

			$f_id = $request->f_id;
			
			$franchise_p_id = DB::table('franchiseproduct')->
				join('franchise','franchise.id','=','franchiseproduct.franchise_id')->
				leftjoin('franchisemerchant',
					'franchisemerchant.franchise_id','=','franchiseproduct.franchise_id')->
				where([
					'franchise.id' => $f_id,
					'franchise.owner_merchant_id' => $user_data->company_id(),
					'franchiseproduct.active' => 1
				])->
				whereNull('franchiseproduct.deleted_at')->
				get();
			
			$ids = $franchise_p_id->pluck('product_id')->unique();

		} else {
			$ids = merchantproduct::where('merchant_id',
			   	$user_data->company_id())->pluck('product_id');
		}

       		 $inventory_data = product::where('ptype', 'inventory')->
			whereNotNull('name')->
			whereIn('id', $ids)->get();

       		 return view('opossum.opos_promodefinition',
			 compact('inventory_data'));
	} catch (\Exception $e) {
		Log::info([
			"Error"	=> $e->getMessage(),
			"Line"	=> $e->getLine(),
			"File"  => $e->getFile()
		]);
		abort(404);
	}
    }

    /**
     * store promo definition
     * @param Request $request
     */
    public function storePromoDefinition(Request $request)
    {
        $userData = new UserData();
        $merchantId = $userData->company_id();

        $promoId = $request->input('promoId');
        $promo = opos_promo::find($promoId);
        $promo->title = $request->input('name');
        $promo->valid_start_dt = date('Y-m-d', strtotime($request->input('valid_start_date')));
        $promo->valid_end_dt = date('Y-m-d', strtotime($request->input('valid_end_date')));
        $price = (float)str_replace(",", "", $request->input('price'));
        $promo->price = $price * 100;
        $promo->thumb_photo = $request->input('thumb_photo');
	if ($request->has('is_franchise')) {
		$promo->type = 'franchise';
	}
        $promo->save();

	$franchiseLocations = DB::table('franchise')->
		join('franchisemerchant','franchisemerchant.franchise_id','=','franchise.id')->
		join('franchisemerchantloc','franchisemerchantloc.franchisemerchant_id','=','franchisemerchant.id')->
		where([
			'franchise.owner_merchant_id' => $userData->company_id()
		])->
		whereNull('franchisemerchant.deleted_at')->
		whereNull('franchisemerchantloc.deleted_at')->
		pluck('franchisemerchantloc.location_id')->unique();

	if ($request->has('is_franchise')) {
		$merchantLocationIds = $franchiseLocations;
	} else {
		$merchantLocationIds = merchantlocation::where('merchant_id', $merchantId)->pluck('location_id');
		$merchantLocationIds = $merchantLocationIds->diff($franchiseLocations);
	}

	$locations = location::where([['branch', '!=', 'null']])->whereIn('id', $merchantLocationIds)->latest()->get();

        foreach($locations as $location) {
            $promoLocation = new opos_promo_location();
            $promoLocation->location_id = $location->id;
            $promoLocation->promo_id = $promoId;
            $promoLocation->save();
        }

        // promo product
        $products = $request->input('products');
        foreach($products as $product) {
            $promoProduct = new opos_promo_product();
            $promoProduct->promo_id = $promoId;
            $promoProduct->product_id = $product['productId'];
            $promoProduct->quantity = $product['qty'];
            $promoProduct->save();
        }

        if ($promoId) {
            return response()->json(['msg' => 'Promo Bundle saved successfully', 'systemId' => $promo->systemid]);
        }
    }


    /**
     * get location based promos
     * @param Request $request
     */
    public function getLocationPromos(opos_promo $oposPromo, Request $request) {
        $terminalId = $request->input('terminalId');
        $terminal = terminal::where('systemid', $terminalId)->first();
        $locationTerminal = locationterminal::where('terminal_id', $terminal->id)->first();

        $columns = $request->input('columns');

        $query = $oposPromo->select('opos_promo.*')
                ->join('opos_promolocation', 'opos_promolocation.promo_id', '=', 'opos_promo.id')
            ->where('opos_promo.valid_end_dt', '>=', date('Y-m-d').' 00:00:00')
            ->where('opos_promolocation.location_id', $locationTerminal->location_id);
	
	$is_franchise = FranchiseMerchantLocTerm::select('company.name','company.systemid')->
			join('franchisemerchantloc', 'franchisemerchantloc.id', '=',
				'franchisemerchantlocterm.franchisemerchantloc_id')->
			join('franchisemerchant','franchisemerchant.id','=',
				'franchisemerchantloc.franchisemerchant_id')->	
			join('franchise','franchise.id','=', 'franchisemerchant.franchise_id')->
			join('company', 'franchise.owner_merchant_id', '=','company.id')->			
			where(
				['franchisemerchantlocterm.terminal_id' => $terminal->id],
			//	["franchisemerchantlocterm.franchisemerchant_id" => $this->user_data->company_id()]
			)->
			first();

	if ($is_franchise) {
		$query->where('opos_promo.type', 'franchise');
	} else {
		$query->where('opos_promo.type', 'direct');
	}

        /*
        // name filter
        $search = $request->input('search');
        if (isset($search['value']) && $search['value'] != '')
            $query->where('title', 'like', '%'.$search['value'].'%');
        */

        /*
        // order by
        $order = $request->input('order');
        if (isset($order[0]['column']) && $order[0]['column'] != '') {
            if ($order[0]['column'] == 2) {
                $query->orderBy('title', $order[0]['dir']);
            }
            if ($order[0]['column'] == 4) {
                $query->orderBy('price', $order[0]['dir']);
            }
        }
        */

        /*
        $query->whereHas('locations')->with(['locations' => function ($innerQuery) use ($locationTerminal) {
            $innerQuery->where('location_id', $locationTerminal->location_id);
        }]);
        */

        $query->orderBy('opos_promo.id', 'desc');


        // applying limit
        //$promos = $query->skip($request->input('start'))->take($request->input('length'))->get();
        $promos = $query->get();

        $counter = 0 + $request->input('start');

        foreach ($promos as $key => $promo) {
            $promos[$key]['indexNumber'] = ++$counter;
            $promos[$key]['product'] = opos_promo_product::where('promo_id', $promo->id)->count();
            $promos[$key]['price'] = number_format($promo->price / 100, 2, '.', ',');
        }

        $response = [
            'data' => $promos,
            'recordsTotal' => $oposPromo->get()->count(),
            'recordsFiltered' => $oposPromo->get()->count()
        ];
        return response()->json($response);

        //$locationTerminal->location_id


    }


    public function promoProducts(Request $request)
    {
        $promoId = $request->input('promoId');

        $query = product::select('product.*', 'opos_promoproduct.quantity as bundle_product_qty')
                ->join('opos_promoproduct', 'opos_promoproduct.product_id', '=', 'product.id')
                ->where('opos_promoproduct.promo_id', $promoId);
        /*
        $ids = opos_promo_product::where('promo_id', $promoId)->pluck('product_id');

        $query = product::whereIn('id', $ids);
        */

        // name filter
        $search = $request->input('search');
        if (isset($search['value']) && $search['value'] != '')
            $query->where('name', 'like', '%'.$search['value'].'%');

        // order by
        $order = $request->input('order');
        if (isset($order[0]['column']) && $order[0]['column'] != '') {
            if ($order[0]['column'] == 1) {
                $query->orderBy('name', $order[0]['dir']);
            }
        }

        $productModel = $query;
        // applying limit

        if ($request->input('length') != -1) {
            $products = $query->skip($request->input('start'))->take($request->input('length'))->get();
        } else {
            $products = $query->get();
        }

        $response = [
            'data' => $products,
            'recordsTotal' => $productModel->get()->count(),
            'recordsFiltered' => $productModel->get()->count()
        ];
        return response()->json($response);
    }

    /**
     * get promo bundles for datatables
     *
     */
    public function getPromoBundles(opos_promo $oposPromo, Request $request)
    {
/** */  
        $promos = array();
        $userData = new UserData();
        $merchantId = $userData->company_id();
 	if ($request->has('is_franchise')) {
		$bType = 'franchise';
	} else {
		$bType = 'direct';
	}
           
        $sql = DB::table('opos_promo')
        ->select('opos_promo.*')
        ->join('opos_promoproduct','opos_promoproduct.promo_id','=','opos_promo.id')
        ->join('merchantproduct','merchantproduct.product_id','=','opos_promoproduct.product_id')
        ->where('merchantproduct.merchant_id','=', $merchantId)
	->where('opos_promo.type',$bType)
		->distinct()
        ->orderBy('updated_at','DESC')
        ->get();


        Log::debug('orderedItems=' . json_encode($sql));

        //$promos = opos_promo::orderBy("updated_at", "desc")->get();
		//dd($sql);
        $counter = 0;

        foreach ($sql as $key => $promo) {
            //Log::debug('key=' . json_encode($key));
            $promos[$key]['id'] = $promo->id;
            $promos[$key]['indexNumber'] = ++$counter;
            $promos[$key]['title'] = $promo->title;
            $promos[$key]['thumb_photo'] = $promo->thumb_photo;
            $promos[$key]['valid_start_dt'] = $promo->valid_start_dt;
            $promos[$key]['valid_end_dt'] = $promo->valid_end_dt;
            $promos[$key]['product'] = opos_promo_product::where('promo_id', $promo->id)->count();
            $promos[$key]['price'] = number_format($promo->price / 100, 2, '.', ',');
            $promos[$key]['locations_count'] = opos_promo_location::where("promo_id" , $promo->id)->get()->count();
            $stack = opos_promo_location::where("promo_id" , $promo->id)->pluck("location_id");
            $promos[$key]["locationsLocator"] = $stack;
            $promos[$key]["istransaction"] = opos_receiptproduct::join('opos_promo', 'opos_receiptproduct.promo_id', '=', 'opos_promo.id')->where('opos_receiptproduct.promo_id',$promo->id)->get()->count();
        }
        
		
        $response = [
            'data' => $promos
        ];
        return response()->json($response);
    }



    public function opossumtest($id)
    {
        return view('opossum.opossum1');
    }

    public function opossumCounter($terminal_id)
    {
           $this->user_data = new UserData();
        $id              = Auth::user()->id;
        $user_roles      = usersrole::where('user_id', $id)->get();

        $is_king = \App\Models\Company::where('owner_user_id', Auth::user()->id)->first() || $this->user_data->allow_all();


        if ($is_king != null) {
            $is_king = true;
        } else {
            $is_king = false;
        }
        $terminal = terminal::where('systemid', $terminal_id)->first();
        if (!$terminal) {return abort(404);}
        $opos_locationterminal = locationterminal::where('terminal_id', $terminal->id)->first();
        $branch   = location::where('id', $opos_locationterminal->location_id )->first()->branch;

        return view('plat_counter.plat_counter',compact('user_roles','is_king','branch','opos_locationterminal','terminal'));
    }
    public function wastage()
    {
        $id         = Auth::user()->id;
        $user_roles = usersrole::where('user_id', $id)->get();

        $is_king = \App\Models\Company::where('owner_user_id', Auth::user()->id)->first();

        if ($is_king != null) {
            $is_king = true;
        } else {
            $is_king = false;
        }
        return view('opossum.wastage', compact('user_roles', 'is_king'));
    }

    public function opossumproduct()
    {
        $id         = Auth::user()->id;
        $user_roles = usersrole::where('user_id', $id)->get();

        $is_king = \App\Models\Company::where('owner_user_id', Auth::user()->id)->first();

        if ($is_king != null) {
            $is_king = true;
        } else {
            $is_king = false;
        }
        return view('opossum.product', compact('user_roles', 'is_king'));
    }


    public function opencode_table(Request $request)
    {

        $data = $this->getActiveProductsForTerminal($request->terminal_id);

        return Datatables::of($data)
            ->addIndexColumn()
            ->addColumn('product_id', function ($memberList) {
                $product_id = $memberList->systemid;
                return '<p data-field="product_id" style="margin: 0;">' . ucfirst($product_id) . '</p>';
            })
            ->addColumn('product_name', function ($memberList) {
                if (!empty($memberList->thumbnail_1)) {
                    $img_src = '/images/product/' . $memberList->id . '/thumb/' . $memberList->thumbnail_1;
                    $img     = "<img src='$img_src' data-field='product_name' style=' width: 25px;height: 25px;display: inline-block;margin-right: 8px;object-fit:contain;'>";
                } else {
                    $img = null;
                }
                return $img . '<p class="os- linkcolor" data-field="restaurantnservices_pro_name" style="cursor: pointer; margin: 0;display: inline-block;color:#007bff;">' . (!empty($memberList->name) ? $memberList->name : 'Product Name') . '</p>';
            })
            ->addColumn('source', function ($memberList) {
                $member_type = $memberList->ptype == 'services' ? "Restaurant&nbsp;&&nbsp;Services " : $memberList->ptype;
                return '<p data-field="source" style="margin: 0;">' . ucfirst($member_type) . '</p>';
            })
            ->escapeColumns([])
            ->make(true);
    }

    // @return products from a terminal with validation
    public function getActiveProductsForTerminal($terminalId, $type = NULL, $cat_ids = NULL, $subcategoryId = NULL, $filter = false, $distinctType = false )
    {
        $this->user_data = new UserData();
        $this->manual($terminalId);
		$terminal = terminal::where('systemid', $terminalId)->first();
        // $model           = new restaurant();.
	
		$is_franchise = FranchiseMerchantLocTerm::select('company.name','company.systemid')->
			join('franchisemerchantloc', 'franchisemerchantloc.id', '=',
				'franchisemerchantlocterm.franchisemerchantloc_id')->
			join('franchisemerchant','franchisemerchant.id','=',
				'franchisemerchantloc.franchisemerchant_id')->	
			join('franchise','franchise.id','=', 'franchisemerchant.franchise_id')->
			join('company', 'franchise.owner_merchant_id', '=','company.id')->			
			where(
				['franchisemerchantlocterm.terminal_id' => $terminal->id],
				["franchisemerchantlocterm.franchisemerchant_id" => $this->user_data->company_id()]
			)->
			first();

		if (empty($is_franchise)) {
			
			$ids = merchantproduct::where('merchant_id',
  				$this->user_data->company_id())->
  				pluck('product_id');

			$ids = opos_terminalproduct::where('terminal_id', $terminal->id)->
			     whereIn('product_id', $ids)->pluck('product_id');
		   
	   	   // Get product ids that are hidden from preferences
			$prefids = productpreference::where('terminal_id', $terminal->id)->
				whereIn('product_id',$ids)->
				where('status','hide')->
				where('franchisee_merchant_id',$this->user_data->company_id())->
				pluck('product_id');


          // Get  product ids from restaurant that have no price
	  	$restaurantIds = restaurant::where('price','=',0)->
			 whereIn('product_id',$ids)->
			 orWhereNull('price')->
			 pluck('product_id');

		 // Get vouchers that have not expired and has quantity
		$now = \Carbon\Carbon::now()->format('Y-m-d'); // Get current time

		$getVoucID =  voucher::where('expiry','>',$now)->
			whereIn('product_id',$ids)->
			where("qty_unit","!=", 0)
             // ->where("type","!=", "pct")
                                     ->where("qty_unit" ,"!=", NULL)
                                   ->get();
			//dd($getVoucID);
             // Get inactive product id list with the $voucherIds  above
         $inactiveVoucProductIds = [];
		 $i = 0;
			
		 foreach ($getVoucID as $key => $item) {
			 $total_vouchers_left = voucherlist::
				 where("voucher_id" , $item->id)
				 ->orWhere("status" , "active")
				 ->orWhere("status" , "pending")
				 ->count();

			 if($total_vouchers_left == 0){
				 $activeProductIds[$i] = $item->product_id;
                         $i += 1;
			 }
		 
		 }

              // Get expired voucher product ids
		 $expiredVoucProdIds =  voucher::where('expiry','<=',$now)
						->whereIn('product_id',$ids)
						->get('product_id');


		 $pctVoucProdIds =  voucher::where('type','=','pct')
						->whereIn('product_id',$ids)
						->get('product_id');
		  // Get membership product ids that buy is empty
		 $membershipIds = membership::whereIn('product_id',$ids)
			 ->where( function($q){$q->whereNull('get')->orWhereNull('buy');})
			 ->pluck('product_id');

		 // Inventory price can either be 0 or NULL
		 $inventoryIds = prd_inventory::whereIn('product_id',$ids)
			 ->where( function($q){ $q->where('price',0)->orWhere('price', NULL);})
			 ->pluck('product_id');

	  	 // Availble product types
	  	// $ptype = ['services', 'voucher', 'warranty', 'inventory', 'membership'];

	   // Get  warranty products that price empty
		$warrantyProductIds  = warranty::whereIn('product_id',$ids)
		  ->where( function($q){ $q->where('price',0)->orWhere('price', NULL);})
		  ->pluck('product_id');

		} else {
	
			$franchise_p_id = DB::table('franchiseproduct')->
				join('franchisemerchant',
					'franchisemerchant.franchise_id','=','franchiseproduct.franchise_id')->
				join('franchisemerchantloc','franchisemerchantloc.franchisemerchant_id',
					'=','franchisemerchant.id')->
				join('franchisemerchantlocterm','franchisemerchantlocterm.franchisemerchantloc_id',
					'=','franchisemerchantloc.id')->
				leftjoin('product','product.id','=','franchiseproduct.product_id')->
				where([
					'franchisemerchant.franchisee_merchant_id' => $this->user_data->company_id(),
					'franchisemerchantlocterm.terminal_id' => $terminal->id,
					'franchiseproduct.active' => 1
				])->
				whereNull('franchiseproduct.deleted_at')->
				where([['ptype','!=','oilgas']])->
				get();
			
			$ids = $franchise_p_id->pluck('product_id');
			$prefids = productpreference::where('terminal_id', $terminal->id)->
				whereIn('product_id',$ids)->
				where('status','hide')->
				where('franchisee_merchant_id',$this->user_data->company_id())->
				pluck('product_id');

					$membershipIds = [];
					$restaurantIds = $inactiveVoucProductIds = $expiredVoucProdIds = [];
                  	$pctVoucProdIds = [];
                    $inventoryIds  = [];
                    $warrantyProductIds= []; 

		}
		

          		//	dd($distinctType);
            // Get valid products in terminal
	  if($type == NULL && $distinctType == false){
		 /* brand_id is not validated due to
		  * prd_restaurant does NOT have brand_id */
		//  dd($ids);
		 
		  return product::whereIn('id', $ids)->
			  //whereIn('ptype', $ptype)->
			  whereNotNull('name')->
			 whereNotNull('photo_1')->
			 whereNotNull('prdcategory_id')->
			 whereNotNull('prdsubcategory_id')->
			 whereNotNull('prdprdcategory_id')->
			 whereNotIn('id', $prefids)->
			 whereNotIn('id', $membershipIds)->
			 whereNotIn('id', $restaurantIds)->
			 whereNotIn('id', $inactiveVoucProductIds)->
			 whereNotIn('id', $expiredVoucProdIds)->
			 whereNotIn('id', $pctVoucProdIds)->
			 whereNotIn('id', $inventoryIds)->
			 whereNotIn('id', $warrantyProductIds)->
			 latest()->
			 get();
		}
		// Get valid products Types in terminal
		if($distinctType == true) {
			//dd($inactiveVoucProductIds);
			return product::whereIn('id', $ids)->
			//whereIn('ptype', $ptype)->
			whereNotNull('name')->
			whereNotNull('photo_1')->
			whereNotNull('prdcategory_id')->
			whereNotNull('prdsubcategory_id')->
			whereNotNull('prdprdcategory_id')->
			whereNotIn('id', $prefids)->
			whereNotIn('id', $membershipIds)->
			whereNotIn('id', $restaurantIds)->
			whereNotIn('id', $inactiveVoucProductIds)->
			whereNotIn('id', $expiredVoucProdIds)->
			whereNotIn('id', $pctVoucProdIds)->
			whereNotIn('id', $inventoryIds)->
			whereNotIn('id', $warrantyProductIds)->
			orderBy('ptype', 'asc')->
			distinct()->
			get(['ptype']);
		}
		
                // Get filter products
		 if ($subcategoryId != NULL ){
		  	return   product::where('ptype', $type)
				   // ->whereIn('ptype', $ptype)
				->where('prdsubcategory_id', $subcategoryId )
				->whereIn('id', $ids)
				->whereNotNull('name')
				->whereNotNull('photo_1')
				->whereNotNull('prdcategory_id')
				->whereNotNull('prdsubcategory_id')
				->whereNotNull('prdprdcategory_id')
				->whereIn('prdcategory_id', $cat_ids)
				->whereNotIn('id', $prefids)
				->whereNotIn('id', $inactiveVoucProductIds)
				->whereNotIn('id', $expiredVoucProdIds)
				->whereNotIn('id', $pctVoucProdIds)
				->whereNotIn('id', $membershipIds)
				->whereNotIn('id', $inventoryIds)
				->get();
		}
	  // Get valid filter subcategories
	  if($filter == true){
	 		return  product::where('ptype', $type)
			 //->whereIn('ptype', $ptype)
				 ->whereIn('id', $ids)
				 ->whereNotNull('name')
				 ->whereNotNull('photo_1')
				 ->whereNotNull('prdcategory_id')
				 ->whereNotNull('prdsubcategory_id')
				 ->whereNotNull('prdprdcategory_id')
				 ->whereIn('prdcategory_id', $cat_ids)
				 ->whereNotIn('id', $prefids)
				 ->whereNotIn('id', $inactiveVoucProductIds)
				 ->whereNotIn('id', $expiredVoucProdIds)
				 ->whereNotIn('id', $pctVoucProdIds)
				 ->whereNotIn('id', $membershipIds)
				 ->whereNotIn('id', $inventoryIds)
				 ->pluck('prdsubcategory_id');
              }
        // Get valid productypes
        if($filter == true){
            return  product::where('ptype', $type)
                ->whereIn('id', $ids)
                ->whereNotNull('name')
                ->whereNotNull('photo_1')
                ->whereNotNull('prdcategory_id')
                ->whereNotNull('prdsubcategory_id')
                ->whereNotNull('prdprdcategory_id')
                ->whereIn('prdcategory_id', $cat_ids)
                ->whereNotIn('id', $prefids)
                ->whereNotIn('id', $inactiveVoucProductIds)
				->whereNotIn('id', $pctVoucProdIds)
                ->whereNotIn('id', $expiredVoucProdIds)
                ->whereNotIn('id', $membershipIds)
                ->whereNotIn('id', $inventoryIds)
                ->pluck('prdsubcategory_id');
        }
    }


    public function opencode_getproduct(Request $request)
    {
		Log::debug('***** opencode_getproduct() *****');

        try {
            $validation = Validator::make($request->all(), [
                'id' => 'required',
            ]);

            $product_id = $request->get('id');
            //$locationproduct = locationproduct::where('product_id', $product_id)->first();
            $terminal 		= terminal::where('systemid', $request->terminal_id)->first();
			
			$location_id 	= DB::table('opos_locationterminal')->
				where('terminal_id', $terminal->id)->
				first()->location_id;	
			
			$user_data = new UserData();
            $product    = product::find($product_id);
            $is_special = productspecial::where('product_id',$product_id)->
				first();
            if ($product->ptype == 'services') {
                //  $product['price'] = 100;
                $restaurant = restaurant::where('product_id', $product_id)->
					first();
                $product['product_other_details'] = $restaurant;

            } else if ($product->ptype == 'voucher') {
                $voucher = voucher::where('product_id', $product_id)->first();
                $product['product_other_details'] = $voucher;
                $product['total_vouchers_left'] = voucherlist::
					where("voucher_id" , $voucher->id)->
					where("status" , "pending")->count();

				Log::debug('err product_other_details='.json_encode($voucher));
				Log::debug('err voucher_id='.$voucher->id);
				Log::debug('err total_vouchers_left='.json_encode($product['total_vouchers_left']));

                if($product['total_vouchers_left'] == 0){
                    throw new Exception("no_more_vouchers", 1);
                }

            } else if ($product->ptype == 'warranty') {
                $warranty = warranty::where('product_id', $product_id)->first();
                $product['product_other_details'] = $warranty;

            } else if ($product->ptype == 'inventory') {
                $prd_inventory =
					prd_inventory::where('product_id', $product_id)->first();
                $product['product_other_details'] = $prd_inventory;

            } else if ($product->ptype == 'membership') {
                $membership = membership::where('product_id', $product_id)->
					first();
                $membership['price'] = $membership->buy;
                $product['product_other_details'] = $membership;

            } else if ($product->ptype == 'oilgas') {
                $query = OgFuel::select('og_fuelprice.price AS price',
					'product.id','product.name', 'product.thumbnail_1')->
					join('product','product.id','=','prd_ogfuel.product_id')->
					join('og_fuelprice','og_fuelprice.ogfuel_id','=','prd_ogfuel.id')->
					where('product.id',$product_id)->
					where('product.name', '!=', null)->
					where('product.thumbnail_1', '!=', null)->
					where('og_fuelprice.price', '!=', null)->
					where('og_fuelprice.start', '!=', null)->
					whereDate('og_fuelprice.start', '<=', Carbon::now())->
					orderBy('og_fuelprice.id', 'DESC');

                $products = $query->skip(0)->take(1)->get()->first();
                $product['product_other_details'] = $products;
				$product['fuel_qty'] = (float) number_format( app('App\Http\Controllers\InventoryController')->
					location_productqty($product_id,$location_id),2);

				$local_price = DB::table('og_localfuelprice')->
					join('prd_ogfuel','prd_ogfuel.id','=','og_localfuelprice.ogfuel_id')->
					where([
						'prd_ogfuel.product_id'			=> $product_id,
						'og_localfuelprice.company_id' 	=> $user_data->company_id(),
						"og_localfuelprice.location_id" => $location_id
					])->
					whereDate('og_localfuelprice.start', '<=', Carbon::now())->
					select("og_localfuelprice.*")->
					first();
				
				if (!empty($local_price)) {
					$product['product_other_details']->price = $local_price->price;
				}

			}
			
			//###############################################################
			//@franchiseLogic
	    		$is_franchise = FranchiseMerchantLocTerm::select('company.name','company.systemid')->
				join('franchisemerchantloc', 'franchisemerchantloc.id', '=',
					'franchisemerchantlocterm.franchisemerchantloc_id')->
				join('franchisemerchant','franchisemerchant.id','=',
					'franchisemerchantloc.franchisemerchant_id')->	
				join('franchise','franchise.id','=', 'franchisemerchant.franchise_id')->
				join('company', 'franchise.owner_merchant_id', '=','company.id')->			
				where(
					['franchisemerchantlocterm.terminal_id' => $terminal->id],
					["franchisemerchantlocterm.franchisemerchant_id" => $user_data->company_id()]
				)->
				first();
			if (!empty($is_franchise)) {
				$franchise_product = DB::table('franchiseproduct')->
					leftjoin('franchisemerchant',
						'franchisemerchant.franchise_id','=','franchiseproduct.franchise_id')->
					where([
						'franchisemerchant.franchisee_merchant_id' => $user_data->company_id(),
						'franchiseproduct.product_id' => $product->id,
						'franchiseproduct.active' => 1
					])->
					whereNull('franchiseproduct.deleted_at')->
					select("franchiseproduct.*")->
					first();

				if (empty($franchise_product)) {
					throw new \Exception("Franchise product error");
				}
				
	    	if ($product->ptype == 'inventory') {
				$product->product_other_details->price = $product->product_other_details->cost  = $franchise_product->recommended_price;
				$product->product_other_details->max = $franchise_product->upper_price;
				$product->product_other_details->cogs = $franchise_product->lower_price;
			}
			$custom_locationPrice = $this->getFranchiseLocationPrice($franchise_product->id, $location_id);
			if ($custom_locationPrice != false) {
				$product->product_other_details->price = $product->product_other_details->cost  = $custom_locationPrice;
			}

	    	}
			//######################################################

            if ($is_special) {
                $product['special'] = 'true';
            }
            $tableNumber = $request->tableNumber;
            $table_name = $request->table_name;
            if(strlen($table_name) > 6){
                $terminal = terminal::where('systemid', $request->terminal_id)->first();
                $location_id = locationterminal::where('terminal_id',$terminal->id)->value('location_id');

                $table = substr($table_name,6,strlen($table_name));
                log::debug('table_name'.$request->table_name);
                log::debug('$table'.$table);
                $reserved_tables = reserve::join('opos_ftype','opos_ftype.id','=',
                    'plat_reserve.ftype_id')->
                    where('opos_ftype.fnumber',$table)->
                    whereDay('opos_ftype.created_at', '=', date('d'))->
                    where('status','active')->pluck('fnumber')->toArray();
                if(count($reserved_tables)>0) {
                    return response()->json(['response_invalid' => true]);
                }
            }
            if($tableNumber != 0 || ctype_alpha($tableNumber) || strpbrk($tableNumber, "+")){

                $terminal = terminal::where('systemid', $request->terminal_id)->first();
                $location_id = locationterminal::where('terminal_id',$terminal->id)->value('location_id');

            // $location_id = locationterminal::where('terminal_id',$terminal->id)->value('location_id');
            // $terminal_valid = terminal::pluck('id');
            // $terminal_ids = locationterminal::where('location_id',$location_id)->whereIn('terminal_id',$terminal_valid)->pluck('terminal_id');

                $ftype_id = oposFtype::where('fnumber',$tableNumber)->
                        where('ftype','table')->
                        where('location_id',$location_id)
                         ->whereNull('deleted_at')
                         ->orderby('id','desc')
                         ->whereDay('created_at', '=', date('d'))
                         ->first();

                if (!empty($ftype_id)) {
                    // $terminal = terminal::where('id', $ftype_id->terminal_id)->first();
                    $plat_openbill = openBill::where('ftype_id',$ftype_id->id)->
                    where('status', '!=','voided')
                    ->whereDay('created_at', '=', date('d'))
                    ->first();
                    if(!empty($plat_openbill)){
                        $plat_product = openBillProduct::where('openbill_id',$plat_openbill->id)->
                        where('product_id',$product_id)->
                        where('status', '!=','voided')
                        ->orderby('id','desc')
                        ->whereDay('created_at', '=', date('d'))
                            ->first();
                        if(!empty($plat_product)){
                            $plat_product->status = 'voided';
                            $plat_product->save();
                            $plat_products = openBillProduct::where('openbill_id',$plat_openbill->id)
                            ->whereDay('created_at', '=', date('d'))
                            ->where('status', '!=','voided')->count();
                            if($plat_products == 0){
                                $plat_openbill->status = 'voided';
                                $plat_openbill->save();
                                $ftype_id->deleted_at = Carbon::now();
                                $ftype_id->save();
                                if($ftype_id->name == 'Split'){
                                    $split_table = splitTable::where('split_fnumber',$tableNumber)->where('location_id',$location_id)->whereIn('status',array('active','pending'))->orderby('id','desc')->first();
                                    $split_table->status = 'void';
                                    $split_table->save();
                                }else if ($ftype_id->name == 'Combined'){
                                    $combine_table = combine::where('combine_ftype_id',$ftype_id->id)->where('status', '!=','void')->orderby('id','desc')->first();
                                    $combine_table->status = 'void';
                                    $combine_table->save();
                                }
                            }
                        }

                    }else{
                    }
                }
            }

          //  $product->product_other_details->price = (float) number_format(( $product->product_other_details->price / 100),2);
            if($request->skipNumber > 0){
                $terminal = terminal::where('systemid', $request->terminal_id)->first();
                $location_id = locationterminal::where('terminal_id',$terminal->id)->value('location_id');

                $ftype_id = oposFtype::where('fnumber',$request->skipNumber)->
                    where('ftype','skip')
                    ->where('location_id',$location_id)
                    ->whereNull('deleted_at')
                    ->whereDay('created_at', '=', date('d'))
                    ->orderby('id','desc')
                    ->first();

                $plat_skip = skipTable::where('ftype_id',$ftype_id->id)->
                where('status', '!=','voided')
                ->whereDay('created_at', '=', date('d'))
                ->orderby('id','desc')
                ->first();

                if(!empty($plat_skip)){
                    $plat_product = skipTableProduct::where('skip_id',$plat_skip->id)->
                    where('product_id',$product_id)->
                    where('status', '!=','voided')
                    ->orderby('id','desc')
                    ->first();
                    if(!empty($plat_product)){
                        $plat_product->status = 'voided';
                        $plat_product->save();
                        $plat_products = skipTableProduct::where('skip_id',$plat_skip->id)->
                        where('status', '!=','voided')->count();
                        if($plat_products == 0){
                            $plat_skip->status = 'voided';
                            $plat_skip->save();
                            $ftype_id->deleted_at = Carbon::now();
                            $ftype_id->save();
                        }
                    }
                }
            }


            Log::debug($terminal);
            $preference = productpreference::where('terminal_id', $terminal->id)->where('product_id', $product_id)->orderby('id','desc')->first();
            $product['preference'] = $preference;
            $product['product_terminal_id'] = $terminal->id;

            if ($validation->fails() || !$product) {
                throw new Exception("error_validation", 171);
            }
            //Here to remove green for table
//            echo '<pre>';
//            print_r($product);
//            echo '</pre>';
//            exit();

            /*
                added by Udemezue Miracle Obiajulu

                it includes the current stock level of the fuel product in the particular location
            */
            $locationterminal = locationterminal::where('terminal_id', $terminal->id)->first();
	    $locationproduct = locationproduct::orderBy("id", "desc")->where("product_id", $request->id)->where("location_id", $locationterminal->location_id)->first();
	    $qty = stockreport::join('stockreportproduct','stockreportproduct.stockreport_id','=','stockreport.id')->
		    where('stockreportproduct.product_id',$product_id)->
		    where('stockreport.location_id',$locationterminal->location_id)->
		    get()->sum('stockreportproduct.quantity');

	   // dd($qty);
            $product['locationproduct'] = $locationproduct;

            /*added by Udemezue  for selecting the actual value of ogfuel_id*/
            $og_fuel = Ogfuel::where("product_id", $product_id)->first();
            $og_fuel_id = $og_fuel ? $og_fuel->id : '';

            $current_day = date('Y-m-d');
            $fuelmovement = OgFuelMovement::where([
                        ['ogfuel_id' , $og_fuel_id],
                        ['location_id' ,  $locationterminal->location_id]
                    ])->get()->first();
            
            $product['fuelmovement'] = $fuelmovement;
	    
	    return response()->json($product);

        } catch(\Exception $e){
            Log::error(
                "Error @ " . $e->getLine() . " file " . $e->getFile() .
					":" . $e->getMessage()
            );

            if($e->getMessage() == "no_more_vouchers"){
                $msg = "Maximum voucher issued";
                $data["response_no_package"] = $msg;
                return $data;

            } else {
                $msg = "Product not found";
            }
            return view('layouts.dialog', compact('msg'));
        }
    }

    public function opencode(Request $request)
    {
        $terminal = terminal::where('systemid',
            $request->terminal_id)->first();

        return view('opos_component.code', compact('terminal'));
    }

	public function getFranchiseLocationPrice($product_id, $location_id) {
		try {
			
			$user_data = new UserData();

			$record = DB::table('locationproductprice')->
				where([
					'franchiseproduct_id'		=>	$product_id,
					'active'					=>	1,
					'location_id' 				=>	$location_id,
					'franchisee_merchant_id'	=>  $user_data->company_id()
				])->first();
			
			return $record->price ?? false;

		} catch (\Exception $e) {
			\Log::info([
				"Error"	=> $e->getMessage(),
				"File"	=> $e->getFile(),
				"Line"	=> $e->getLine()
			]);
			abort(404);
		}
	}

    public function stockin($terminal_id)
    {
        $id         = Auth::user()->id;
        $user_roles = usersrole::where('user_id', $id)->get();

        $is_king = \App\Models\Company::where('owner_user_id', Auth::user()->id)->first();
        $terminal = terminal::where('systemid', $terminal_id)->first();
        $locationterminal = locationterminal::where('terminal_id', $terminal->id)->first();

        $location_id = $locationterminal->location_id;
        $location = location::where('id',$locationterminal->location_id)->get();//->first();
        if ($is_king != null) {
            $is_king = true;
        } else {
            $is_king = false;
        }
	$opossum_location = location::where('id',$locationterminal->location_id)->first();
	//return view('opossum.opos_stockin', compact('user_roles', 'is_king','location_id','location'));
	return view('inventory.inventorystockin',compact('location','opossum_location' ));
    }

    public function productRedumption(){
        $user_id = Auth::user()->id;

        $data = DB::select("SELECT
    latest.id AS id,
    latest.name,
    latest.systemid,
    latest.type,
    latest.thumbnail_1,
    latest.merchant_id,
    locationproduct.location_id AS location,
    locationproduct.quantity    AS T_quantity,
    latest.created_at           AS created_at
FROM
(SELECT
    p.id,
    p.name,
    p.systemid,
    p.ptype            AS type,
    p.thumbnail_1      AS thumbnail_1,
    mp.merchant_id     AS merchant_id,
    lp.quantity,
    Max(lp.created_at) AS created_at
FROM
    product p,
    merchantproduct mp,
    company c,
    locationproduct lp,
    location l
WHERE
    mp.product_id = p.id
    AND mp.merchant_id = c.id
    AND lp.product_id = p.id
    AND p.name IS NOT NULL
    AND p.ptype != 'oilgas'
    AND lp.quantity IS NOT NULL
    AND c.id
    AND c.owner_user_id = $user_id -- Auth->user_id
    -- AND l.id = 52 -- location id
    AND l.id = lp.location_id
GROUP BY
    p.id
    -- l.id
) AS latest
INNER JOIN
    locationproduct ON locationproduct.created_at = latest.created_at
ORDER BY
    latest.created_at");

        return view('opossum.petrol_station.product_redemption.product_redemption', compact('data'));
    }

    public function stockout($terminal_id)
    {
        $id         = Auth::user()->id;
        $user_roles = usersrole::where('user_id', $id)->get();

        $terminal = terminal::where('systemid', $terminal_id)->first();
        $locationterminal = locationterminal::where('terminal_id', $terminal->id)->first();
        $location_id = $locationterminal->location_id;
        $location = location::where('id',$locationterminal->location_id)->get();//->first();

        $is_king = \App\Models\Company::where('owner_user_id', Auth::user()->id)->first();

        if ($is_king != null) {
            $is_king = true;
        } else {
            $is_king = false;
        }
	
	$opossum_location = location::where('id',$locationterminal->location_id)->first();
       // return view('opossum.opos_stockout', compact('user_roles', 'is_king','location_id','location'));
	return view('inventory.inventorystockout',compact('location','opossum_location'));
    }

    public function itemisedReport($terminal_id)
    {
        $id         = Auth::user()->id;
        $user_roles = usersrole::where('user_id', $id)->get();
        $terminal = terminal::where('systemid', $terminal_id)->first();

        $is_king = \App\Models\Company::where('owner_user_id', Auth::user()->id)->first();
        if ($is_king != null) {
            $is_king = true;
        } else {
            $is_king = false;
        }
        return view('opossum.itemised_report', compact('user_roles', 'is_king','terminal_id', 'terminal'));
    }

    public function get_itemised_table(Request $request)
    {
        // product.systemid as productsys_id
	$data  = array();
	$user_data = new UserData();    
        $terminal = terminal::where('systemid', $request->terminal_id)->first();
        $location_id = locationterminal::where('terminal_id',$terminal->id)->value('location_id');
	$date_value = '';

        if(!empty($request->date_val)){
            $date_value = $request->date_val;
            $day = date('d',strtotime($date_value));
        } else {
            $day = date('d');
	}

        log::debug('date_val'.$date_value);
        // Product Meta data (reciept, location, document no., etc.)
	
	$opos_product = opos_receiptproduct::select('opos_receipt.systemid as document_no','product.*',
		'product.systemid as productsys_id','opos_receiptproduct.receipt_id',
		'opos_itemdetails.id as item_detail_id','opos_itemdetails.receiptproduct_id',
		'opos_receiptproduct.quantity','opos_itemdetails.created_at as last_update',
		'location.branch as location','opos_receiptproduct.name','opos_itemdetails.amount',
		'location.id as locationid','opos_receiptdetails.void','product.id as product_id')
	    ->leftjoin('opos_itemdetails','opos_itemdetails.receiptproduct_id','=','opos_receiptproduct.id')
	    ->leftjoin('opos_receipt','opos_receipt.id','=','opos_receiptproduct.receipt_id')
	    ->leftjoin('product','product.id','=','opos_receiptproduct.product_id')
	    ->leftjoin('opos_receiptdetails','opos_receipt.id','=','opos_receiptdetails.receipt_id')
	    ->leftjoin('opos_locationterminal', 'opos_receipt.terminal_id', '=', 'opos_locationterminal.terminal_id')
	    ->leftjoin('location', 'location.id', '=', 'opos_locationterminal.location_id')
	    ->leftjoin('staff','staff.user_id','=','opos_receipt.staff_user_id')
	    ->where('staff.company_id',$user_data->company_id())
	    ->where('location.id', $location_id)
	    ->whereDay('opos_receipt.created_at', '=', $day)
	    ->orderby('opos_itemdetails.id','DESC')
	    ->get();

	$refund = opos_refund::select('opos_receipt.systemid as document_no','product.*',
	    'product.systemid as productsys_id','product.id as product_id','opos_receiptproduct.receipt_id',
	    'opos_receiptproduct.quantity','opos_refund.refund_type',
	    'opos_itemdetails.id as item_detail_id','opos_itemdetails.receiptproduct_id',
	    'opos_refund.created_at as last_update','location.branch as location',
	    'opos_receiptproduct.name','opos_itemdetails.amount','location.id as locationid',
	    'opos_receiptdetails.void')
        ->join("opos_receiptproduct",'opos_receiptproduct.id','=','opos_refund.receiptproduct_id')
        ->leftjoin('opos_itemdetails','opos_itemdetails.receiptproduct_id','=','opos_receiptproduct.id')
        ->leftjoin('opos_receipt','opos_receipt.id','=','opos_receiptproduct.receipt_id')
        ->leftjoin('product','product.id','=','opos_receiptproduct.product_id')
        ->leftjoin('opos_receiptdetails','opos_receipt.id','=','opos_receiptdetails.receipt_id')
        ->leftjoin('opos_locationterminal', 'opos_receipt.terminal_id', '=', 'opos_locationterminal.terminal_id')
        ->leftjoin('location', 'location.id', '=', 'opos_locationterminal.location_id')
	->leftjoin('staff','staff.user_id','=','opos_refund.confirmed_user_id')
	->where('staff.company_id', $user_data->company_id())
	->where('location.id', $location_id)
        ->whereIn('opos_refund.refund_type',array('C','Dx'))
        ->whereDay('opos_refund.created_at', '=', $day)
        ->get();

        $item_count = count($opos_product);
        foreach ($refund as $key => $value) {
            $refund_type = $value->refund_type;
            if($refund_type == "C" || $refund_type == "Dx"){
            // if($refund_type == "C" || $refund_type == "Cx" || $refund_type == "Dx"){
                // if($refund_type == 'C' || $refund_type == "Dx") {
                if($refund_type == 'C') {
                    $opos_product[$item_count] = $value;
                    $opos_product[$item_count]->sales_type = 'Refund C';
                    $opos_product[$item_count]->quantity = 1;
                    $item_count++;
                }
                // if($refund_type == 'Cx' || $refund_type == "Dx") {
                if($refund_type == "Dx") {
                    $opos_product[$item_count] = $value;
                    $opos_product[$item_count]->sales_type = 'Refund Dx';
                    $opos_product[$item_count]->quantity = 1;
                    $item_count++;
                }
            }
        }


        $wastage = opos_wastageproduct::
	    select('product.systemid as productsys_id','product.id as product_id',
		'product.thumbnail_1','product.name','opos_wastage.systemid as document_no',
		'opos_wastageproduct.wastage_qty as quantity','product.name',
		'opos_wastageproduct.created_at as last_update','location.branch as location',
		'location.id as locationid')
        ->join('opos_wastage','opos_wastage.id','=','opos_wastageproduct.wastage_id')
        ->join('location','location.id','=','opos_wastageproduct.location_id')
        ->join("product",'opos_wastageproduct.product_id','=','product.id')
	->join('staff','staff.user_id','=','opos_wastage.staff_user_id')
	->where('staff.company_id', $user_data->company_id())
	->where('location.id', $location_id)
        ->whereDay('opos_wastage.created_at', '=', $day)
	->get();

        $item_count = count($opos_product);
        foreach ($wastage as $key => $value) {
            $opos_product[$item_count] = $value;
            $opos_product[$item_count]->wastage = 1;
            $opos_product[$item_count]->sales_type = "Wastage & Damage";
            $opos_product[$item_count]->quantity = $value->quantity;
            $opos_product[$item_count]->amount = 0;
            $item_count++;
        }

        // Latest item remarks
        foreach ($opos_product as $key => $value) {
            $item_id = $value->item_detail_id;
            if($value->refund_type || $value->wastage) { continue; }
            if($value->void ==1) {
                $opos_product[$key]->sales_type = "Void Sales";
                $opos_product[$key]->quantity = 0 ;
            } else {
                $opos_product[$key]->sales_type = "Cash Sales";
                $opos_product[$key]->quantity = $value->quantity;
            }
        }

        if($request->datatable){
            if (!Schema::hasTable($request->datatable)) {
                     Schema::create($request->datatable, function($table){
                        $table->increments('id');
                        $table->timestamps();
                });
            } else {
                $data = DB::table($request->datatable)
                    ->whereDay('created_at', '=', date('d'))->get();
                if(count($data) <= 0) {
                    DB::table($request->datatable)->insert(
                        ['created_at' => now()]
                    );
                }
                $data1 = DB::table($request->datatable)->count();
                if($data1 > 30){
                    $dataname = DB::select("SHOW TABLES");
                    foreach ($dataname as $dkey => $dvalue) {
                        foreach ($dvalue as $tkey => $tvalue) {
                            Schema::dropIfExists($tvalue);
                        }
                    }
                }
            }
        }

        // opos_product sort by Lastupdate (db_table.created_at) Desc
        $opos_product = $opos_product->sortBy('last_update',SORT_REGULAR,true);
        log::debug('opos_product'.json_encode($opos_product));

        return Datatables::of($opos_product)->
        addIndexColumn()->
        addColumn('inven_pro_date', function ($memberList) {
            $date = date('dMy H:i:s',strtotime($memberList->last_update));
            if($memberList->wastage){
              return '<a href="/wastagereport/'.$memberList->document_no.'" target="_blank" style="cursor: pointer;">'.$date.'</a>';
            } else {
                $clickfunction = "show_receipt('".$memberList->document_no."')";
                return '<a href="#" style="cursor: pointer;" onclick="'.$clickfunction.'">'.$date.'</a>';
            }
        })->
        addColumn('inven_pro_id', function ($memberList) {
            return $memberList->productsys_id;
        })->
        addColumn('inven_pro_desc', function ($memberList) {
	    return '<img src="'.asset('images/product/'.$memberList->product_id.
		'/thumb/'.$memberList->thumbnail_1).
		'" style="height:40px;width:40px;object-fit:contain;margin-right:8px;">'.
		$memberList->name;
        })->
        addColumn('inven_pro_qty', function ($memberList) {
            return $memberList->quantity;
        })->
        addColumn('inven_pro_amount', function ($memberList) {
            if($memberList->quantity == 0 || $memberList->wastage == 1){
                return 0;
            }
            return number_format(($memberList->amount/100),2);
        })->
        addColumn('inven_pro_status', function ($memberList) {
            return $memberList->sales_type;
        })->
        escapeColumns([])->
        make(true);
    }

     public function opossumdynamictable(Request $request){
        $colscount      = $request->rows;

        $totalcols      = ($colscount *3);
        $startcols      = $totalcols-3;
        $closeposition  = $colscount;
        $leftposition   = $closeposition+1;
        $rightposition  = ($colscount*2)-2;
        $plusposition   = $rightposition+1;
        $extrabtn       = 3;
        $terminal_id = terminal::where('systemid',$request->terminal_id)->first();

        $locationterminal = locationterminal::where('terminal_id', $terminal_id->id)->first();
        $location = location::where('id', $locationterminal->location_id)->first();

        $tableAlpha = array('','A','B','C','D','E','F','I','G','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
        $empty_string = array('');
        $split_table = splitTable::whereIn('status',array('active','pending'))->
            where('location_id',$location->id)->pluck('split_fnumber')->toArray();

        $reserved_tables = reserve::join('opos_ftype','opos_ftype.id','=',
            'plat_reserve.ftype_id')->
            where('opos_ftype.location_id',$location->id)->
            whereDay('opos_ftype.created_at', '=', date('d'))->
            where('status','active')->pluck('fnumber')->toArray();

        $active_tables = oposFtype::join('plat_openbill','plat_openbill.ftype_id',
            '=','opos_ftype.id')->
            join('plat_openbillproduct','plat_openbillproduct.openbill_id','=',
            'plat_openbill.id')->
            where('opos_ftype.location_id',$location->id)->
            where('ftype','table')->
            whereDay('opos_ftype.created_at', '=', date('d'))->
            where('plat_openbill.status','active')
            ->pluck('fnumber')->toArray();

         $combined_tables_plus = combine::join('opos_ftype',
            'plat_combine.combine_ftype_id','=','opos_ftype.id')->
            where('plat_combine.status','active')->
            where('opos_ftype.location_id',$location->id)->
            whereDay('opos_ftype.created_at', '=', date('d'))->
            pluck('fnumber')->toArray();

         $combined_tables = $this->getCombinedTables($combined_tables_plus);

//         $tableName1 = array();

         $ftype = opos_tablename::where('location_id', $location->id)->get();

//
//         foreach ($ftype as $f){
//             if ($f->falias == null || $f->falias == '') {
//                 $name = $f->fnumber;
//             } else {
//                 $name = $f->falias;
//             }
//             $tableName1[] = $name;
//
//         }

        //$tableName1 = array('A','B','C','D','E','F','G','H','I', 'J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','1','2','3','4','5','6','7','8','9','10','11','12','13','14','15','16','17','18','19','20','21','22','23','24','25','26','27','28','29','30','31','32','33','34','35','36','37','38','39','40','41','42','43','44','45','46','47','48','49','50','51','52','53','54','55','56','57','58','59','60','61','62','63','64','65','66','67','68','69','70','71','72','73','74','75','76','77','78','79','80','81','82','83','84','85','86','87','88','89','90','91','92','93','94','95','96','97','98','99','100','101','102','103','104','105','106','107','108','109','110','111','112','113','114','115','116','117','118','119','120','121','122','123','124','125','126','127','128','129','130','131','132','133','134','135','136','137','138','139','140','141','142','143','144','145','146','147','148','149','150','151','152','153','154','155','156','157','158','159','160','161','162','163','164','165','166','167','168','169','170','171','172','173','174','175','176','177','178','179','180','181','182','183','184','185','186','187','188','189','190','191','192','193','194','195','196','197','198','199','200');

        $tableName1 = array('1','2','3','4','5','6','7','8','9','10','11','12','13','14','15','16','17','18','19','20','21','22','23','24','25','26','27','28','29','30','31','32','33','34','35','36','37','38','39','40','41','42','43','44','45','46','47','48','49','50','51','52','53','54','55','56','57','58','59','60','61','62','63','64','65','66','67','68','69','70','71','72','73','74','75','76','77','78','79','80','81','82','83','84','85','86','87','88','89','90','91','92','93','94','95','96','97','98','99','100','101','102','103','104','105','106','107','108','109','110','111','112','113','114','115','116','117','118','119','120','121','122','123','124','125','126','127','128','129','130','131','132','133','134','135','136','137','138','139','140','141','142','143','144','145','146','147','148','149','150','151','152','153','154','155','156','157','158','159','160','161','162','163','164','165','166','167','168','169','170','171','172','173','174','175','176','177','178','179','180','181','182','183','184','185','186','187','188','189','190','191','192','193','194','195','196','197','198','199','200');
        $tableName1 = array_merge($empty_string,$split_table,$combined_tables_plus, $tableName1);

         Log::debug('Active_tables'.json_encode($active_tables));


        // for($rv=$leftposition-1; $rv<25; $rv++){
        //     unset($tableName1[$rv]);
        // }

        $tableName = array_values($tableName1); // 'reindex' array

        $arrcount=count($tableName);

        return view('opossum.opossum_dynamic_table', compact('colscount','combined_tables','active_tables','reserved_tables','closeposition','leftposition','rightposition','tableName','totalcols','plusposition','startcols','arrcount','tableAlpha', 'ftype'));

    }


    public function opos_tablename($terminal_id){
        try {
            $id = Auth::user()->id;
            $user_roles = usersrole::where('user_id',$id)->get();

            $is_king =  \App\Models\Company::where('owner_user_id',
                Auth::user()->id)->first();

            if ($is_king != null) {
                $is_king = true;
            } else {
                $is_king  = false;
            }
            return view('opossum.opos_tablename', compact('terminal_id', 'user_roles', 'is_king'));

        } catch (\Exception $e) {

            Log::error(
                "Error @ " . $e->getLine() . " file " . $e->getFile() . ":" . $e->getMessage()
            );

        }
    }

    public function opos_tablename_list(Request $request){
        $terminal = terminal::where('systemid',$request->terminal_id)->first();
        $locationterminal = locationterminal::where('terminal_id', $terminal->id)->first();
        $location = location::where('id', $locationterminal->location_id)->first();

        $data = opos_tablename::where('location_id', $location->id)->get();


        return Datatables::of($data)
            ->addIndexColumn()
            ->addColumn('default_name', function ($data) {
                return $data->default_name;
            })
            ->addColumn('new_name', function ($data) {
                return '<input type="text" id="table_'.$data->default_name.'" class="text-center text-primary" style="font-size:20px;border:1px solid #c0c0c0;border-radius:10px;height:40px;margin:0px;" maxlength="5" size="30" value="'.$data->new_name.'" onchange="change_tablename(\''.$data->default_name.'\')" />';
            })

            ->escapeColumns([])
            ->make(true);
    }

    public function change_tablename(Request $request) {
        try {
            $terminal = terminal::where('systemid',$request->terminal_id)->first();
            $locationterminal = locationterminal::where('terminal_id', $terminal->id)->first();
            $location = location::where('id', $locationterminal->location_id)->first();

            $tablename = opos_tablename::where('location_id', $location->id)->where('default_name', $request->default_name)->first();
            $tablename->new_name = $request->new_name;
            $tablename->save();

            return response()->json();

        } catch (\Exception $e) {

            Log::error(
                "Error @ " . $e->getLine() . " file " . $e->getFile() . ":" . $e->getMessage()
            );

        }
    }

    public function getCombinedTables($combined_tables){
        $new_combined = [];
        foreach ($combined_tables as $combined_table){
            $combined_tables_fnumber = explode("+",$combined_table);
            $new_combined = array_merge($new_combined, $combined_tables_fnumber);
        }
        $combined_tables = array_unique($new_combined);
        return $combined_tables;
    }

    public function opossumSkip(Request $request){
        $colscount      = $request->rows;
        $totalcols      = ($colscount *3);
        $startcols      = $totalcols-3;
        $closeposition  = $colscount;
        $leftposition   = $closeposition+1;
        $rightposition  = ($colscount*2)-2;
        $plusposition   = $rightposition+1;
        $extrabtn       = 3;
        $terminal_id = terminal::where('systemid',$request->terminal_id)->first();
        Log::debug('Terminal ID'.json_encode($terminal_id));
        Log::debug('Terminal ID'.$request->terminal_id);
        $locationterminal = locationterminal::where('terminal_id', $terminal_id->id)->first();
        $location = location::find($locationterminal->location_id);
        $active_tables = oposFtype::join('plat_skip','plat_skip.ftype_id','=','opos_ftype.id')->

        where('opos_ftype.location_id',$location->id)->
        where('ftype','skip')->
        where('status','active')->
        pluck('fnumber')->toArray();


        $tableName = array('', '+Skip','1','2','3','4','5','6','7','8','9','10','11','12','13','14','15','16','17','18','19','20','21','22','23','24','25','26','27','28','29','30','31','32','33','34','35','36','37','38','39','40','41','42','43','44','45','46','47','48','49','50','51','52','53','54','55','56','57','58','59','60','61','62','63','64','65','66','67','68','69','70','71','72','73','74','75','76','77','78','79','80','81','82','83','84','85','86','87','88','89','90','91','92','93','94','95','96','97','98','99','100');

        $arrcount=count($tableName);

        return view('opossum.opossum_skip', compact('active_tables','colscount','closeposition','leftposition','rightposition','tableName','totalcols','plusposition','startcols','arrcount'));
    }

    public function loadProductFScreenD(){
        $id         = Auth::user()->id;
        $user_roles = usersrole::where('user_id', $id)->get();

        $is_king = \App\Models\Company::where('owner_user_id', Auth::user()->id)->first();

        if ($is_king != null) {
            $is_king = true;
        } else {
            $is_king = false;
        }
        return view('opossum.opossumloadproduct', compact('user_roles', 'is_king'));
    }
    public function showPreference($terminal_id)
    {
        $user_id = Auth::user()->id;
        $user_roles = usersrole::where('user_id', $user_id)->get();
        $is_king =  Company::where('owner_user_id', Auth::user()->id)->get();
        //modified
        $this->user_data = new UserData();
	$merchantId = $this->user_data->company_id();

        $this->manual($terminal_id);

//        $terminal_id = (\Session::get('terminalID'));
//        $terminal = strval($terminal_id);
//        terminal::where(['systemid' => $terminal])->pluck('id')->first();
	
        $terminal = terminal::where('systemid', $terminal_id)->first();
	$preference = productpreference::where('terminal_id', $terminal->id)->get();

	$is_franchise = FranchiseMerchantLocTerm::select('company.name','company.systemid',
		'franchise.id as f_id')->
		join('franchisemerchantloc', 'franchisemerchantloc.id', '=',
			'franchisemerchantlocterm.franchisemerchantloc_id')->
		join('franchisemerchant','franchisemerchant.id','=',
			'franchisemerchantloc.franchisemerchant_id')->	
		join('franchise','franchise.id','=', 'franchisemerchant.franchise_id')->
		join('company', 'franchise.owner_merchant_id', '=','company.id')->			
		where(
			['franchisemerchantlocterm.terminal_id' => $terminal->id],
			["franchisemerchantlocterm.franchisemerchant_id" => $this->user_data->company_id()]
		)->
		first();

        // $ids = merchantproduct::where('merchant_id', $this->user_data->company_id())->pluck('product_id');

        // $modelVoucher = new voucher();
        // $vouchersIDs = $modelVoucher->whereIn('product_id', $ids)->
        //     whereNotNull('price')->
        //     whereNotNull('package_qty')->
        //     whereNotNull('qty_unit')->
        //     whereNotNull('expiry')->
        //     pluck('product_id');

        // $idsTwo = opos_terminalproduct::where('terminal_id', $terminal->id)->whereIn('product_id', $ids)->pluck('product_id');
        // $ids = $idsTwo->merge($vouchersIDs);

	if (empty($is_franchise)) {
		$ids = merchantproduct::where('merchant_id', $this->user_data->company_id())->pluck('product_id');
        	$ids = opos_terminalproduct::where('terminal_id', $terminal->id)->whereIn('product_id', $ids)->pluck('product_id');
	} else {
		$franchise_p_id = DB::table('franchiseproduct')->
			//	leftjoin('franchisemerchant',
			//		'franchisemerchant.franchise_id','=','franchiseproduct.franchise_id')->
				where([
					'franchiseproduct.franchise_id'	  => $is_franchise->f_id,
					'franchiseproduct.active' => 1
				])->
				whereNull('franchiseproduct.deleted_at')->
				get();
		$ids = $franchise_p_id->pluck('product_id');
	}



//        $products = product::whereIn('id', $products_id)->get();
        $logo_db = terminal::where(['systemid' => $terminal_id])->first();
        // $model           = new restaurant();.

        /*$ids = merchantproduct::where('merchant_id', $this->user_data->company_id())->pluck('product_id');

        $restaurant_ids       = restaurant::where([['price', '!=', 'NULL']])->whereIn('product_id', $ids)->pluck('product_id')->toArray();
        $voucher_ids          = voucher::where([['price', '!=', 'NULL']])->whereIn('product_id', $ids)->pluck('product_id')->toArray();
        $warranty_ids         = warranty::where([['price', '!=', 'NULL']])->whereIn('product_id', $ids)->pluck('product_id')->toArray();
        $prd_inventory_ids    = membership::where([['get', '!=', 'NULL']])->whereIn('product_id', $ids)->pluck('product_id')->toArray();
        $memprd_inventory_ids = prd_inventory::where([['price', '!=', 'NULL']])->whereIn('product_id', $ids)->pluck('product_id')->toArray();

        $filter = array_merge($restaurant_ids, $voucher_ids, $warranty_ids, $prd_inventory_ids, $memprd_inventory_ids);

        $ptype = ['services', 'voucher', 'warranty', 'inventory', 'membership'];
        $data  = product::where([['prdcategory_id', '!=', 'NULL'], ['name', '!=', 'NULL']])->whereIn('id', $filter)->whereIn('ptype', $ptype)->latest()->get();
        */

        $model0 = new restaurant();

//        $ids = merchantproduct::where('merchant_id',$this->user_data->company_id())->pluck('product_id');
//        $ids = product::where('ptype','services')->whereNotNull('name')->whereIn('id',$ids)->pluck('id');
        $restaurant = $model0->whereIn('product_id', $ids)->orderby('created_at', 'asc')->latest()->get();
        foreach ($restaurant as $p) {
		$pref = productpreference::where('product_id', $p->product_id)->
			where('franchisee_merchant_id',$merchantId)->
			where('terminal_id', $terminal->id)->first();
            if (!$pref) {
                $pref = new productpreference();
                $pref->product_id = $p->product_id;
                $pref->terminal_id = $terminal->id;
                $pref->name = $p->product_name->name;
                $pref->photo_1 = $p->product_name->photo_1;
                $pref->thumb_photo = $p->product_name->thumbnail_1;
                $pref->local_price = $p->price;
                $pref->price_keyin = 0;
                $pref->status = 'show';
                $pref->weight = 0;
                $pref->price_per_unit = 1;
		$pref->franchisee_merchant_id = $merchantId;
                $pref->save();
            }
        }

        $model1 = new prd_inventory();
//      $ids = merchantproduct::where('merchant_id',$this->user_data->company_id())->pluck('product_id');
//      $ids = product::where('ptype','inventory')->whereNotNull('name')->whereIn('id',$ids)->pluck('id');

        $inventory = $model1->whereIn('product_id', $ids)->
            orderby('created_at', 'asc')->latest()->get();

        foreach ($inventory as $p) {
            $pref = productpreference::where('product_id', $p->product_id)->
		where('franchisee_merchant_id',$merchantId)->
                where('terminal_id', $terminal->id)->first();
            if (!$pref) {
                $pref = new productpreference();
                $pref->product_id = $p->product_id;
                $pref->terminal_id = $terminal->id;
                $pref->name = $p->product_name->name;
                $pref->photo_1 = $p->product_name->photo_1;
                $pref->thumb_photo = $p->product_name->thumbnail_1;
                $pref->local_price = $p->price;
                $pref->price_keyin = 0;
                $pref->status = 'show';
                $pref->weight = 0;
                $pref->price_per_unit = 1;
		$pref->franchisee_merchant_id = $merchantId;
                $pref->save();
            }
        }

        $model2 = new voucher();

//      $ids  = merchantproduct::where('merchant_id', $this->user_data->company_id())->pluck('product_id');
//        $ids  = product::where('ptype', 'voucher')->whereNotNull('name')->whereIn('id', $ids)->pluck('id');
        $voucher = $model2->whereIn('product_id', $ids)->orderby('created_at', 'desc')->latest()->get();
        foreach ($voucher as $p) {
		$pref = productpreference::where('product_id', $p->product_id)->
			where('franchisee_merchant_id',$merchantId)->
			where('terminal_id', $terminal->id)->first();
            if (!$pref) {
                $pref = new productpreference();
                $pref->product_id = $p->product_id;
                $pref->terminal_id = $terminal->id;
                $pref->name = $p->product_name->name;
                $pref->photo_1 = $p->product_name->photo_1;
                $pref->thumb_photo = $p->product_name->thumbnail_1;
                $pref->local_price = $p->price;
                $pref->price_keyin = 0;
                $pref->status = 'show';
                $pref->weight = 0;
                $pref->price_per_unit = 1;
		$pref->franchisee_merchant_id = $merchantId;
                $pref->save();
            }
        }

        $model3  = new warranty();
//        $ids  = merchantproduct::where('merchant_id', $this->user_data->company_id())->pluck('product_id');
//        $ids  = product::where('ptype', 'warranty')->whereNotNull('name')->whereIn('id', $ids)->pluck('id');
        $warranty = $model3->whereIn('product_id', $ids)->orderby('created_at', 'desc')->latest()->get();
        foreach ($warranty as $p) {
		$pref = productpreference::where('product_id', $p->product_id)->
			where('franchisee_merchant_id',$merchantId)->
			where('terminal_id', $terminal->id)->first();
            if (!$pref) {
                $pref = new productpreference();
                $pref->product_id = $p->product_id;
                $pref->terminal_id = $terminal->id;
                $pref->name = $p->product_name->name;
                $pref->photo_1 = $p->product_name->photo_1;
                $pref->thumb_photo = $p->product_name->thumbnail_1;
                $pref->local_price = $p->price;
                $pref->price_keyin = 0;
                $pref->status = 'show';
                $pref->weight = 0;
                $pref->price_per_unit = 1;
		$pref->franchisee_merchant_id = $merchantId;
                $pref->save();
            }
        }

        $model4 = new membership();
//        $ids = merchantproduct::where('merchant_id', $this->user_data->company_id())->pluck('product_id');
//        $ids = product::where('ptype', 'membership')->whereNotNull('name')->whereIn('id', $ids)->pluck('id');
        $membership = $model4->whereIn('product_id', $ids)->orderby('created_at', 'asc')->latest()->get();
        foreach ($membership as $p) {
		$pref = productpreference::where('product_id', $p->product_id)->
			where('franchisee_merchant_id',$merchantId)->
			where('terminal_id', $terminal->id)->first();

            if (!$pref) {
                $pref = new productpreference();
                $pref->product_id = $p->product_id;
                $pref->terminal_id = $terminal->id;
                $pref->name = $p->product_name->name;
                $pref->photo_1 = $p->product_name->photo_1;
                $pref->thumb_photo = $p->product_name->thumbnail_1;
                $pref->local_price = $p->buy;
                $pref->price_keyin = 0;
                $pref->status = 'show';
                $pref->weight = 0;
                $pref->price_per_unit = 1;
		$pref->franchisee_merchant_id = $merchantId;
                $pref->save();
            }
        }

        $location_id = locationterminal::where('terminal_id', $terminal->id)->pluck('location_id');
        $location = location::where('id', $location_id)->first();
        $products = $restaurant->toBase()->merge($inventory);
        $products = $products->toBase()->merge($voucher);
        $products = $products->toBase()->merge($warranty);
        $products = $products->toBase()->merge($membership);

//end modified
        return view('opossum.opossum_preference', compact('logo_db','products','user_roles', 'is_king', 'preference', 'terminal', 'location'));
    }

    public function change_preference_status(Request $request) {
        Log::debug('***** change_preference_status() *****');

        $terminal = terminal::where('systemid',
            $request->terminal_id)->first();

        $status = $request->status;
        $prd_id = $request->prd_id;

        Log::debug('prd_id='.$prd_id);
        Log::debug('terminal->id='.$terminal->id);

        $prd_pref = productpreference::where('product_id', $prd_id)->
            where('terminal_id', $terminal->id)->first();
        if (!$prd_pref) {
            $p = product::where('id', $prd_id)->first();
            $prd_pref = new productpreference();
            $prd_pref->product_id = $prd_id;
            $prd_pref->terminal_id = $terminal->id;
            $prd_pref->name = $p->name;
            $prd_pref->thumb_photo = $p->thumbnail_1;
            $prd_pref->price_keyin = 0;
            $prd_pref->status = $status;
            $prd_pref->save();
            Log::debug("here");
        }

        Log::debug($prd_pref);


        $prd_pref->status = $status;
        $prd_pref->update();
    }

    public function change_preference_priceKeyin(Request $request) {

		$user_data = new UserData();
		$merchantId = $user_data->company_id();
		$terminal = terminal::where('systemid', $request->terminal_id)->first();

        $key = $request->key;
        $prd_id = $request->prd_id;
		$prd_pref = productpreference::where('product_id', $prd_id)->
			where('franchisee_merchant_id',$merchantId)->
			where('terminal_id', $terminal->id)->first();

        if (!$prd_pref) {
            $p = product::where('id', $prd_id)->first();
            $prd_pref = new productpreference();
            $prd_pref->product_id = $prd_id;
            $prd_pref->terminal_id = $terminal->id;
            $prd_pref->name = $p->name;
            $prd_pref->photo_1 = $p->photo_1;
            $prd_pref->price_keyin = $key;
            $prd_pref->status = 'show';
			$prd_pref->franchisee_merchant_id = $merchantId;
            $prd_pref->save();
        }

        $prd_pref->price_keyin = $key;
        $prd_pref->update();
    }

    public function opossumWastage(){
        $id         = Auth::user()->id;
        $user_roles = usersrole::where('user_id', $id)->get();

        $is_king = \App\Models\Company::where('owner_user_id', Auth::user()->id)->first();

        if ($is_king != null) {
            $is_king = true;
        } else {
            $is_king = false;
        }
        return view('opossum.opossum_wastage', compact('user_roles', 'is_king'));
    }


    public function pending()
    {
        $this->user_data = new UserData();
        //$idd = Auth::user()->id;

        $ids = merchantproduct::where('merchant_id',
            $this->user_data->company_id())->
            pluck('product_id');

        $inventory_data = product::whereIn('ptype',
            ['inventory', 'rawmaterial'])->
            whereNotNull('name')->
            whereIn('id', $ids)->
            get();

        $ids = merchantlocation::where('merchant_id',
            $this->user_data->company_id())->
            pluck('location_id');

        // $stockreport = StockReport::find($stockreport_id);

        $user_id = $this->getCompanyUserId();

        $report_id = stockreport::count();
        $report_id += 1;

		$data = stockreport::where("stockreport.status", "pending")->
			leftjoin('stockreportproduct', 'stockreportproduct.stockreport_id', '=', 'stockreport.id')->
			join('product', 'product.id', '=', 'stockreportproduct.product_id')->get();

        // $data->name = product::where("id", $data->pluck('product_id'))->get();
        // dd($data);
        return view('opossum.opos_pending', compact('user_id', 'inventory_data', 'report_id', 'data'));
    }

    public function getCompanyUserId()
    {
        $userData = new UserData();
        $companyId = $userData->company_id();
        $company = Company::find($companyId);
        return $company->owner_user_id;
    }

    public function manual($terminal_id) {
        $this->user_data = new UserData();

        $ids = merchantproduct::where('merchant_id', $this->user_data->company_id())->pluck('product_id');

        $restaurant_ids       = restaurant::whereIn('product_id', $ids)->pluck('product_id')->toArray();
        $voucher_id_list          = voucher::where([['price', '!=', 'NULL']])->whereNotNull('package_qty')->whereNotNull('qty_unit')->whereNotNull('expiry')->whereIn('product_id', $ids)->pluck('product_id')->toArray();
        $warranty_ids         = warranty::where([['price', '!=', 'NULL']])->whereIn('product_id', $ids)->pluck('product_id')->toArray();
        $prd_inventory_ids    = membership::where([['buy', '!=', 'NULL']])->whereIn('product_id', $ids)->pluck('product_id')->toArray();
        log::debug('voucher_id_list'.json_encode($voucher_id_list));
        $voucher_ids = array();
        foreach ($voucher_id_list as $key => $value) {
            $active = 0;
            $voucherproduct = voucherproduct::where('voucher_id', $value)->get();
            if(count($voucherproduct) > 0) {
                $active = 1;
                $voucher_ids[$key] = $value;
            }
        }
        log::debug('$voucher_ids'.json_encode($voucher_ids));
        $memprd_inventory_ids = prd_inventory::where([['price', '!=', 'NULL']])->whereIn('product_id', $ids)->pluck('product_id')->toArray();
        // $memprd_inventory_ids = prd_inventory::whereIn('product_id', $ids)->pluck('product_id')->toArray();

        $filter = array_merge($restaurant_ids, $voucher_ids, $warranty_ids, $prd_inventory_ids, $memprd_inventory_ids);

        $ptype = ['services', 'voucher', 'warranty', 'inventory', 'membership'];
        $products  = product::where([['prdcategory_id', '!=', 'NULL'], ['name', '!=', 'NULL']])->whereIn('id', $filter)->whereIn('ptype', $ptype)->latest()->get();

        $terminal = terminal::where('systemid', $terminal_id)->first();

        foreach ($products as $p){
            $prod = opos_terminalproduct::where('product_id', $p->id)->where('terminal_id', $terminal->id)->first();
            if (!$prod) {
                $prod = new opos_terminalproduct();
                $prod->product_id = $p->id;
                $prod->terminal_id = $terminal->id;
                $prod->save();
            }
        }
    }



     public function opossumManual(Request $request) {
             $colscount      = $request->rows;
             $totalcols      = ($colscount *3);
             $startcols      = $totalcols-3;
             $closeposition  = $colscount;
             $leftposition   = $closeposition+1;
             $rightposition  = ($colscount*2)-2;
             $plusposition   = $rightposition+1;
             $extrabtn       = 3;

             // Get products with validation
             $products = $this->getActiveProductsForTerminal($request->terminal_id);

              $tableName = array('0' => array());

              foreach ($products as $p){
                  $tableName[] = array(asset('/images/product/'.$p->id.'/thumb/'.
                     $p->thumbnail_1), $p->name, $p->name,$p->id);
              }

             $arrcount=count($tableName);

             return view('opossum.opossum_manual', compact(
                 'colscount','closeposition','leftposition','rightposition',
                 'tableName','totalcols','plusposition','startcols','arrcount'
             ));
     }



    public function opossumFilter(Request $request) {

        $colscount      = 21;
        $totalcols      = ($colscount *3);
        $startcols      = $totalcols-3;
        $closeposition  = $colscount;
        $leftposition   = $closeposition+1;
        $rightposition  = ($colscount*2)-2;
        $plusposition   = $rightposition+1;
        $extrabtn       = 3;


        $terminal = terminal::where('systemid', $request->terminal_id)->first();

        // Get active product types
        $productsTypes = $this->getActiveProductsForTerminal($request->terminal_id, NULL, NULL, NULL,false, true);
		
        Log::debug($terminal);
        //return view('opossum.opossum_filter', compact('terminal','tableName'));


        $arrcount=count($productsTypes);

        return view('opossum.opossum_filter', compact('terminal',
            'colscount','closeposition','leftposition','rightposition',
            'productsTypes','totalcols','plusposition','startcols','arrcount'
        ));
    }

    public function opossum_filter_subcat (Request $request)
    {
        try{
            $colscount      = 9;
            $totalcols      = ($colscount *3);
            $startcols      = $totalcols-3;
            $closeposition  = $colscount;
            $leftposition   = $closeposition+1;
            $rightposition  = ($colscount*2)-2;
            $plusposition   = $rightposition+1;
            $extrabtn       = 3;

            $ptype = $request->ptype;
            $this->user_data = new UserData();
            Log::debug($request->terminal_id);
            $this->manual($request->terminal_id);

            $terminal = terminal::where('systemid', $request->terminal_id)->first();
			
			$is_franchise = DB::table('franchisemerchant')->
				join('franchisemerchantloc','franchisemerchantloc.franchisemerchant_id',
					'=','franchisemerchant.id')->
				join('franchisemerchantlocterm',
					'franchisemerchantlocterm.franchisemerchantloc_id','=',
					'franchisemerchantloc.id')->
				where([
					'franchisemerchant.franchisee_merchant_id' => $this->user_data->company_id(),
					'franchisemerchantlocterm.terminal_id' => $terminal->id
				])->
				first();
			
			if (empty($is_franchise)) {
			
				$cat_ids  = merchantprd_category::where('merchant_id', $this->user_data->company_id())->pluck('category_id');
			
			} else {
				
				$cat_ids = product::join('franchiseproduct','franchiseproduct.product_id','=','product.id')->
					whereNull('product.deleted_at')->
					where('franchiseproduct.franchise_id',$is_franchise->franchise_id)->
					pluck('prdcategory_id')->unique();

			}

            // Terminal product
            $prdcategory_id = $this->getActiveProductsForTerminal($request->terminal_id, $ptype, $cat_ids, NULL, true );

            // $prd_subcategory_id = prdcategory::whereIn('category_id', $prdcategory_id)->pluck('subcategory_id');
            $subcategory = prd_subcategory::whereIn('id', $prdcategory_id)->get();

            $typesOfProducts = $this->getActiveProductsForTerminal($request->terminal_id, NULL, NULL, NULL,false, true);

            $tableName = array('0' => '');
			$tableId = array('0' => '');

            foreach ($subcategory as $s){
                $tableName[] = $s->name;
				$tableId[] = $s->id;
            }

            $arrcount=count($tableName);



            return view('opossum.opossum_filter', compact('terminal','typesOfProducts','colscount','closeposition','leftposition','rightposition','tableId','tableName','totalcols','plusposition','startcols','arrcount', 'ptype', 'terminal'));

        }catch(\Exception $e){
            Log::error(
                "Error @ " . $e->getLine() . " file " . $e->getFile() . ":" . $e->getMessage()
            );
        }
    }

    public function opossum_filter_product (Request $request)
    {

        try{

            $colscount      = $request->rows;
            $totalcols      = ($colscount *3);
            $startcols      = $totalcols-3;
            $closeposition  = $colscount;
            $leftposition   = $closeposition+1;
            $rightposition  = ($colscount*2)-2;
            $plusposition   = $rightposition+1;
            $extrabtn       = 3;

            $name = $request->name;
            $ptype = $request->ptype;

            $this->user_data = new UserData();
            $this->manual($request->terminal_id);
		
			$terminal = terminal::where('systemid', $request->terminal_id)->first();

			$is_franchise = DB::table('franchisemerchant')->
				join('franchisemerchantloc','franchisemerchantloc.franchisemerchant_id',
					'=','franchisemerchant.id')->
				join('franchisemerchantlocterm',
					'franchisemerchantlocterm.franchisemerchantloc_id','=',
					'franchisemerchantloc.id')->
				where([
					'franchisemerchant.franchisee_merchant_id' => $this->user_data->company_id(),
					'franchisemerchantlocterm.terminal_id' => $terminal->id
				])->
				first();
			
			if (empty($is_franchise)) {
			
            // merchant product category
				$cat_ids  = merchantprd_category::where('merchant_id', $this->user_data->company_id())->pluck('category_id');

			} else {
			
				$cat_ids = product::join('franchiseproduct','franchiseproduct.product_id','=','product.id')->
					whereNull('product.deleted_at')->
					where('franchiseproduct.franchise_id',$is_franchise->franchise_id)->
					pluck('prdcategory_id')->unique();
			}

            $subcategory = prd_subcategory::where('id', $name)->first();
		//	dd($subcategory);
            $products = $this->getActiveProductsForTerminal($request->terminal_id, $ptype, $cat_ids, $subcategory->id);
			
            $tableName = array('0' => array());

            foreach ($products as $p){

                $tableName[] = array(asset('/images/product/'.$p->id.'/thumb/'.$p->thumbnail_1), $p->name, $p->name,$p->id);

            }

            $arrcount=count($tableName);

            return view('opossum.opossum_prdfilter', compact('colscount','closeposition','leftposition','rightposition','tableName','totalcols','plusposition','startcols','arrcount', 'ptype'));

        }catch(\Exception $e){
            Log::error(
                "Error @ " . $e->getLine() . " file " . $e->getFile() . ":" . $e->getMessage()
            );
        }
    }


    public function addTable(Request $request){

     try{
         $this->user_data = new UserData();
         $validation = Validator::make($request->all(), [
             'terminal_id' => 'required',
             'products'    => 'required',
         ]);
         if ($validation->fails()) {
             throw new \Exception("validation_error", 28);
         }

         $terminal = terminal::where('systemid', $request->terminal_id)->first();
         $location_id = locationterminal::where('terminal_id',$terminal->id)->value('location_id');
         $products = json_decode($request->products, true);
         $external_controller = new OposComponentController();
         // $products = $external_controller->remove_duplicate_array($products);
         $pax = $request->pax;
         $item_total = $request->item_total;
         $total_amount = $request->total_amount;
         $rounding = $request->rounding;
         $tableNumber = $request->tableNumber;
         $ftype = $request->type;
         $type = $this->tableType($tableNumber);
         $open_bill_exists = '';

         $opos_ftype_exists = oposFtype::where('location_id',$location_id)->
                                         where('ftype','table')->
                                         where('fnumber',$tableNumber)->
                                         whereDay('created_at', '=', date('d'))
                                         ->orderby('id','DESC')->first();

         if(empty($opos_ftype_exists)){
             $opos_ftype = new oposFtype();
             $opos_ftype->fnumber = $tableNumber;
             $opos_ftype->ftype = $ftype;
             $opos_ftype->terminal_id = $terminal->id;
             $opos_ftype->location_id = $location_id;
             $opos_ftype->name = $type;
             $opos_ftype->description = $type;
             $opos_ftype->save();
             $ftype_id = $opos_ftype->id;

         }else{
             $open_bill_exists = openBill::where('ftype_id',$opos_ftype_exists->id)->where('status','active')->orderby('id','DESC')->first();
             $ftype_id = $opos_ftype_exists->id;
         }

         Log::debug('Type'.$type);
         Log::debug('Number'.$tableNumber);
        if(empty($open_bill_exists)){

            $plat_openbill = new openBill();

            // $plat_openbill->systemid      = SystemID::reciept_system_id($terminal->id);
            $plat_openbill->terminal_id   = $terminal->id;
            $plat_openbill->service_tax   = $terminal->tax_percent;
            $plat_openbill->service_charge = $terminal->servicecharge;
            $plat_openbill->ftype_id = $ftype_id;
            $plat_openbill->item_total = $item_total * 100;
            $plat_openbill->total_amount = $total_amount * 100;
            $plat_openbill->rounding = $rounding * 100;
            $plat_openbill->status = 'active';
            $plat_openbill->remark = 'Done';
            $plat_openbill->save();
        }else{
            Log::debug('Not Empty');
            $plat_openbill = openBill::where('id',$open_bill_exists->id)
                                         ->where('status','active')
                                         ->orderby('id','DESC')
                                        ->first();
            Log::debug('$open_bill_exists->id = '.$open_bill_exists->id);
            $plat_openbill->item_total += $total_amount * 100;
            $plat_openbill->rounding += $rounding * 100;
            $plat_openbill->save();
        }

          if($type == 'Split'){
              $split_table = splitTable::where('split_fnumber',$tableNumber)->where('location_id',$location_id)->whereIn('status',array('active','pending'))->orderby('id','desc')->first();
              $split_table->status = 'active';
              $split_table->openbill_id = $plat_openbill->id;
              $split_table->split_ftype_id = $ftype_id;
              $split_table->save();
          }else if ($type == 'Combined'){

              $combine_table = combine::where('combine_ftype_id',$ftype_id)->first();
              $combine_table->openbill_id = $plat_openbill->id;
              $combine_table->status = 'active';
              $combine_table->save();
          }

          log::debug('products'.json_encode($products));
         foreach ($products as $product_tx_details) {

             $product_id   = $product_tx_details['id'];
             $product_type = $product_tx_details['type'];
             $product       = product::find($product_id);
             if ($product_type == "product") {

                 $is_own = merchantproduct::where(
                     ['merchant_id' => $this->user_data->company_id(), 'product_id' => $product_id])->first();

                 if (!$is_own) {

                     continue;
                 }
                 $this->add_openbill_product($product, $product_tx_details, $plat_openbill->id,$terminal->id);
             }
         }

        } catch (\Exception $e){
            Log::error(
            "Error @ " . $e->getLine() . " file " . $e->getFile() . ":" . $e->getMessage()
            );
        }
    }

    function add_openbill_product($product, $product_tx_details, $openBillId,$terminal_id){

        try{
            $row_index   = $product_tx_details['row_index'];
            $openBillProduct = new openBillProduct();
            $openBillProduct->openbill_id = $openBillId;
            $openBillProduct->product_id = $product->id;
            $openBillProduct->quantity = $product_tx_details['qty'];
            $openBillProduct->order_price = $product_tx_details['price'];
            $openBillProduct->status = 'active';
            $openBillProduct->save();
            $this->add_openbill_special_product($openBillProduct->id, $row_index, $terminal_id);
        }catch (\Exception $e){
            Log::debug($e->getMessage());
        }

    }

    function add_skip_product($product, $product_tx_details, $skipId,$terminal_id){

        try{
            $row_index   = $product_tx_details['row_index'];
            $skipProduct = new skipTableProduct();
            $skipProduct->skip_id = $skipId;
            $skipProduct->product_id = $product->id;
            $skipProduct->quantity = $product_tx_details['qty'];
            $skipProduct->order_price = $product_tx_details['price'];
            $skipProduct->status = 'active';
            $skipProduct->save();
            $this->add_skip_special_product($skipProduct->id, $row_index, $terminal_id);
        }catch (\Exception $e){
            Log::debug($e->getMessage());
        }

    }

    function add_openbill_special_product($openBillIdProduct, $assoc_id, $terminal_id){
        try {
            $request  = new Request($_POST);
            $products = json_decode($request->products, true);
            $external_controller = new OposComponentController();
            // $products = $external_controller->remove_duplicate_array($products);

            $result = array_filter($products, function ($item) use ($assoc_id) {
                if ($item['assoc_row_id'] == $assoc_id) {
                    return true;
                }
                return false;
            });
            log::debug('result'.json_encode($result));
            foreach ($result as $product_tx_details) {
                $qty         = $product_tx_details['qty'];
                $prd_special = prd_special::find($product_tx_details['id']);
                $plat_openbillproductspecial = new platopenbillproductspecial();
                $plat_openbillproductspecial->quantity = $qty;
                $plat_openbillproductspecial->special_id = $prd_special->id;
                $plat_openbillproductspecial->openbillproduct_id = $openBillIdProduct;
                $plat_openbillproductspecial->status = 'active';
                $plat_openbillproductspecial->save();
            }

        }catch (\Exception $e){
            Log::debug($e->getMessage());
        }
    }

    function add_skip_special_product($skipProductId, $assoc_id, $terminal_id){
        try {
            $request  = new Request($_POST);
            $products = json_decode($request->products, true);
            $external_controller = new OposComponentController();
            $products = $external_controller->remove_duplicate_array($products);

            $result = array_filter($products, function ($item) use ($assoc_id) {
                if ($item['assoc_row_id'] == $assoc_id) {
                    return true;
                }
                return false;
            });
            foreach ($result as $product_tx_details) {
                $qty         = $product_tx_details['qty'];
                $prd_special = prd_special::find($product_tx_details['id']);
                $plat_skipproductspecial = new skipTableProductSpecial();
                $plat_skipproductspecial->quantity = $qty;
                $plat_skipproductspecial->special_id = $prd_special->id;
                $plat_skipproductspecial->skipproduct_id = $skipProductId;
                $plat_skipproductspecial->status = 'active';
                $plat_skipproductspecial->save();
            }

        }catch (\Exception $e){
            Log::debug($e->getMessage());
        }
    }



    function fetchTable(Request $request){
        try{

            $terminal = terminal::where('systemid', $request->terminal_id)->first();
            $location_id = locationterminal::where('terminal_id',$terminal->id)->value('location_id');
            // $terminal_valid = terminal::pluck('id');
            // $terminal_ids = locationterminal::where('location_id',$location_id)->whereIn('terminal_id',$terminal_valid)->pluck('terminal_id');


            $ftype_id = oposFtype::where('fnumber',$request->tableNumber)->
				where('ftype','table')->
				whereNull('deleted_at')->
				orderby('id','desc')->
				whereDay('created_at', '=', date('d'))->
				value('id');

            Log::debug("FTYPE IS".$ftype_id);

            if (!$ftype_id) {
                throw new Exception("ftype_id_not_found", 1);
            }

           $products = openBill::join('plat_openbillproduct','plat_openbill.id','=',
				'plat_openbillproduct.openbill_id')->
				where('plat_openbillproduct.status','active')->
				where('ftype_id',$ftype_id)->get();

            if(!empty($products)){
                foreach ($products as $p){
                    $p->special_products = platopenbillproductspecial::where('openbillproduct_id',$p->id)
						->where('status','!=','deleted')
						->pluck('special_id');
                }

                Log::debug('Products'.json_encode($products));
                return $products;

            }else{
                return null;
            }

        }catch (\Exception $e){
            Log::debug($e->getMessage());
        }
    }


    public function tableType ($tableNumber){

        if (ctype_alpha($tableNumber)){
            $type ='Take Away';
        }elseif (is_numeric($tableNumber)){
            $type='Parent';
        }elseif (strpbrk($tableNumber, "+")){
            $type='Combined';
        }else{
            $type='Split';
        }
        return $type;
    }


    public function deleteSpecial(Request $request){
        try{
            $terminal = terminal::where('systemid', $request->terminal_id)->first();
            if(!empty($terminal)){
                $location_id = locationterminal::where('terminal_id',$terminal->id)->value('location_id');

                $ftype_id = oposFtype::where('fnumber',$request->tableNumber)->
                    where('ftype','table')
                    ->whereNull('deleted_at')
                    ->orderby('id','desc')
                    ->value('id');

                if (empty($ftype_id)) {
                    throw new Exception("ftype_id_not_found", 1);
                }
             $updated =    platopenbillproductspecial::
                join('plat_openbillproduct','plat_openbillproduct.id','=','plat_openbillproductspecial.openbillproduct_id')->
                join('plat_openbill','plat_openbill.id','=','plat_openbillproduct.openbill_id')->
                where('ftype_id',$ftype_id)->
                where('plat_openbillproductspecial.special_id',$request->id)->
                where('plat_openbillproductspecial.status','!=','deleted')->
                update(['plat_openbillproductspecial.status' => 'deleted']);
                if($updated){
                    Log::debug('Success');
                }
            }else{
                throw new Exception("invalid_terminal", 1);
            }
        }catch (\Exception $e){
            Log::debug($e->getMessage());
        }
    }
    public function deleteSkipSpecial(Request $request){
        try{
            $terminal = terminal::where('systemid', $request->terminal_id)->first();
            if(!empty($terminal)){
                $location_id = locationterminal::where('terminal_id',$terminal->id)->value('location_id');
                $ftype_id = oposFtype::where('fnumber',$request->tableNumber)->
                where('ftype','table')->
                where('location_id', $location_id)
                ->whereNull('deleted_at')
                    ->orderby('id','desc')
                    ->value('id');

                if (empty($ftype_id)) {
                    throw new Exception("ftype_id_not_found", 1);
                }
                $updated =    skipTableProductSpecial::
                join('plat_skipproduct','plat_skipproduct.id','=','plat_skipproductspecial.skipproduct_id')->
                join('plat_skip','plat_skip.id','=','plat_skipproduct.openbill_id')->
                where('ftype_id',$ftype_id)->
                where('plat_skipproductspecial.special_id',$request->id)->
                where('plat_skipproductspecial.status','!=','deleted')->
                update(['plat_skipproductspecial.status' => 'deleted']);
                if($updated){
                    Log::debug('Success');
                }
            }else{
                throw new Exception("invalid_terminal", 1);
            }
        }catch (\Exception $e){
            Log::debug($e->getMessage());
        }
    }


    public function addSkip(Request $request){

        try{

            $this->user_data = new UserData();
            $validation = Validator::make($request->all(), [
                'terminal_id' => 'required',
                'products'    => 'required',
            ]);
            if ($validation->fails()) {
                throw new \Exception("validation_error", 28);
            }

            $terminal = terminal::where('systemid', $request->terminal_id)->first();
            $location_id = locationterminal::where('terminal_id',$terminal->id)->value('location_id');
            $products = json_decode($request->products, true);
            $external_controller = new OposComponentController();
            $products = $external_controller->remove_duplicate_array($products);
            $pax = $request->pax;
            $item_total = $request->item_total;
            $total_amount = $request->total_amount;
            $rounding = $request->rounding;
            $skipNumber = $request->skipNumber;
            $ftype = $request->type;
            $plat_skip_exists = '';
            $opos_ftype_exists = oposFtype::where('location_id',$location_id)->
            where('ftype','skip')->
            where('terminal_id',$terminal->id)->
            where('fnumber',$skipNumber)->orderby('id','DESC')->first();

            if(empty($opos_ftype_exists)){
                $opos_ftype = new oposFtype();
                $opos_ftype->fnumber = $skipNumber;
                $opos_ftype->ftype = $ftype;
                $opos_ftype->terminal_id = $terminal->id;
                $opos_ftype->location_id = $location_id;
                $opos_ftype->name = $ftype;
                $opos_ftype->description = $ftype;
                $opos_ftype->save();
                $ftype_id = $opos_ftype->id;

            }else{
                $plat_skip_exists = skipTable::where('ftype_id',$opos_ftype_exists->id)->where('status','active')->first();
                $ftype_id = $opos_ftype_exists->id;
            }

            Log::debug('Type'.$ftype);
            Log::debug('Number'.$skipNumber);
            if(empty($open_bill_exists)){

                $plat_skip = new skipTable();

                // $plat_openbill->systemid      = SystemID::reciept_system_id($terminal->id);
                $plat_skip->terminal_id   = $terminal->id;
                $plat_skip->service_tax   = $terminal->tax_percent;
                $plat_skip->service_charge = $terminal->servicecharge;
                $plat_skip->ftype_id = $ftype_id;
                $plat_skip->item_total = $item_total * 100;
                $plat_skip->total_amount = $total_amount * 100;
                $plat_skip->rounding = $rounding * 100;
                $plat_skip->status = 'active';
                $plat_skip->remark = 'Done';
                $plat_skip->save();
            }else{
                Log::debug('Not Empty');
                $plat_skip = skipTable::where('id',$plat_skip_exists->id)
                    ->where('status','active')
                    ->first();
                Log::debug('$open_bill_exists->id = '.$plat_skip_exists->id);
                $plat_skip->item_total += $total_amount * 100;
                $plat_skip->rounding += $rounding * 100;
                $plat_skip->pax = (($pax == 'Pax') ? 1 : $pax);
                $plat_skip->save();
            }


            foreach ($products as $product_tx_details) {

                $product_id   = $product_tx_details['id'];
                $product_type = $product_tx_details['type'];
                $product       = product::find($product_id);
                if ($product_type == "product") {

                    $is_own = merchantproduct::where(
                        ['merchant_id' => $this->user_data->company_id(), 'product_id' => $product_id])->first();

                    if (!$is_own) {

                        continue;
                    }


                    $this->add_skip_product($product, $product_tx_details, $plat_skip->id,$terminal->id);

                }
            }


        }catch (\Exception $e){
            Log::error(
                "Error @ " . $e->getLine() . " file " . $e->getFile() . ":" . $e->getMessage()
            );
        }

    }
    function fetchSkip(Request $request){
        try{
            Log::debug("Skip Table Number".$request->skipNumber);
            $terminal = terminal::where('systemid', $request->terminal_id)->first();
            $location_id = locationterminal::where('terminal_id',$terminal->id)->value('location_id');

            $ftype_id = oposFtype::where('fnumber',$request->skipNumber)->
                         where('ftype','skip')->
                        where('location_id', $location_id)
                ->whereNull('deleted_at')
                ->orderby('id','desc')
                ->value('id');

            if (!$ftype_id) {
                throw new Exception("ftype_id_not_found", 1);
            }

            $products = skipTable::join('plat_skipproduct','plat_skip.id','=','plat_skipproduct.skip_id')->
            where('plat_skipproduct.status','active')->
            where('ftype_id',$ftype_id)->get();

            foreach($products as $product)
                $product->discount = empty($product->discount) ? 0:$product->discount;

            if(!empty($products)){
                foreach ($products as $p){
                    $p->special_products = skipTableProductSpecial::where('skipproduct_id',$p->id)
                        ->where('status','!=','deleted')
                        ->pluck('special_id');
                }
                Log::debug('Products'.json_encode($products));
                return $products;
            }else{

                return null;
            }

        }catch (\Exception $e){
            Log::debug($e->getMessage());
        }

    }
    function priceTag($id){
        $currency=$id;
    //  return $currency;
        return view('opossum.pricetag',compact('currency'));
    }
    function save_currency(Request $request){
        try{
            $currency = $request->currency;
            $terminal = terminal::where('systemid', $request->terminal_id)->first();

            Log::debug('currency-'.$currency);
            Log::debug('terminal-'.$terminal->id);
            $terminal->currency = $currency;
            $terminal->save();

            return;

        }catch (\Exception $e){
            Log::debug($e->getMessage());
        }

    }

    function opossum_product_fetch_barcodes_new(Request $request) {
	   try {
		   
		   $search_string = $request->search_string;
		   $is_matrix = false;
		   $barcode = DB::table('productbarcode')->
			   where('barcode', $search_string)->
			   whereNull('deleted_at')->
			   first();


		   if (empty($barcode)) {
			   $barcode = DB::table('productbmatrixbarcode')->
				   where('bmatrixbarcode',$search_string)->
				   whereNull('deleted_at')->
				   first();	  
			  $is_matrix = true; 
		   }

		   if (empty($barcode)) {
		   		throw new \Exception("Barcode not found");
		   }
		   
		   $product_id = $barcode->product_id ?? null;

		   return response()->json([
			   "product_id"		=>	$product_id,
			   "search_string"	=>	$search_string,
			   "barcode_id"		=>	$barcode->id,
			   "is_matrix"		=>	$is_matrix,
		   ]);

	   } catch (\Exception $e) {
			Log::error([
				"Error: "	=>	$e->getMessage(),
				"Line: "	=>	$e->getLine(),
				"File: "	=>	$e->getFile()
			]);

			abort(404);
	   }
    }

    function opossum_product_fetch_barcodes(Request $request){
	/*
		$this->manual($request->terminal_id);

		$terminal = terminal::where('systemid',
			$request->terminal_id)->first();

		$ids = merchantproduct::where('merchant_id',
			$this->user_data->company_id())->
			pluck('product_id');

		$ids = opos_terminalproduct::where('terminal_id',$terminal->id)->
			whereIn('product_id', $ids)->
			pluck('product_id');

		$prefids = productpreference::where('terminal_id',$terminal->id)->
			where('status', 'hide')->
			pluck('product_id');

		$products  = product::where([
			['prdcategory_id', '!=', 'NULL'], ['name', '!=', 'NULL']])->
			whereNotIn('id', $prefids)->
			whereIn('id', $ids)->
			latest()->
			get(['id','systemid','name']);
		*/

		/* Building a combined array of default systemid and custom
		 * barcodes and their tagged products */
		$query = "
		SELECT	-- Loading the primary products with default systemid
			p.id,
			p.systemid,
			p.name
		FROM
			product p,
			company c,
		opos_terminal t,
			merchantproduct mp,
			opos_terminalproduct tp
		WHERE
			t.systemid = '".$request->terminal_id."'
			AND c.owner_user_id = ".Auth::user()->id." -- DEFAULT BC
			AND mp.merchant_id = c.id
			AND mp.product_id = p.id
			AND tp.terminal_id = t.id
			AND tp.product_id = mp.product_id
			AND tp.product_id = p.id
			AND p.prdcategory_id is not null
			AND p.name is not null
		UNION
		SELECT	-- Loading in any custom barcodes that may be defined
			p.id,
			pbc.barcode as systemid,
			p.name
		FROM
			product p,
			company c,
			opos_terminal t,
			productbarcode pbc,
			merchantproduct mp,
			opos_terminalproduct tp
		WHERE
			t.systemid = '".$request->terminal_id."'
			AND c.owner_user_id = ".Auth::user()->id."
			AND mp.merchant_id = c.id
			AND tp.terminal_id = t.id
			AND tp.product_id = mp.product_id
			AND tp.product_id = p.id
			AND pbc.product_id = p.id
			AND pbc.merchantproduct_id = mp.id
			AND p.prdcategory_id is not null
			AND p.name is not null
			AND pbc.barcode is not null";

		$combined = DB::select($query);

		$ret = json_encode($combined);

		Log::debug('combined='.$ret);

        return $ret;
    }


    public function super_search(Request $request) {
        Log::debug($request->search);

        $merchant = Company::where('owner_user_id', Auth::user()->id)->first();
        $location_ids  = merchantlocation::where('merchant_id', $merchant->id)->pluck('location_id');
        $terminal_ids = locationterminal::WhereIn('location_id', $location_ids)->pluck('terminal_id');

        if($request->search != '') {
            $receipts = opos_receipt::whereIn('terminal_id', $terminal_ids)->where('systemid','LIKE','%'.$request->search."%")->get();
        } else {
            $receipts = '';
        }


        if (!$receipts || $receipts == null || $receipts == '') {
            $result = '';
        } else {
            $result = '';
            $count = 0;
            foreach ($receipts as $receipt) {
                if ( $count < 6) {
                    $id = 'result'.$count;
                    $result .= '<p class="searchresult" id="'.$id.'" onclick="selectsearch('.$id.')">'.$receipt->systemid.'</p>';
                }
                $count++;
            }
        }

        return response()->json(['result' => $result]);
    }

        //**b@ttle */
    public function save_unconfirmed_data(Request $request){
        $products = $request->get('products');
        $user_id = $request->get('user_id');
		$location_id = $request->get('location_id');
        $redeem_point = 0;
        $get_member_point = DB::table('opos_member')->where('id',$user_id)->first();
        $member_point = $get_member_point->loyaltypts;
        $isValidateQuantity = true;
		$uid = Auth::user()->id;
		
     //   dd($location_id);
        foreach($products as $product){
            $redeem_point = $redeem_point + $product['point_chosen'];
            $quantity = app('App\Http\Controllers\InventoryController')->check_quantity($product);

            if ( $product['qty'] < $quantity)
            { 
                $isValidateQuantity = false;
            }
        }

        if(($redeem_point > $member_point)){
            $msg = ['status' => 250, 'message' => 'Point to be redeemed is larger than the available point', $quantity];
        }
        else if(  $isValidateQuantity ){
            $msg = ['status' => 250, 'message' => 'Quantity to be redeemed is larger than available product', $quantity];
        }
        else{
			$systemid = new SystemID('pts_prd_redemption');
            foreach($products as $product){
				
				//dd($systemid);
                $redeem = DB::table('product_pts_redemption')->insert([
                    'product_id' => $product['id'],
                    'systemid' => $systemid,
                    'member_id' => $user_id,
                    'quantity' => $product['qty'],
                    'location_id' => $location_id,
                    'total_pts_redeemed' => $product['point_chosen'],
                    'staff_user_id' => $uid,
                    'created_at' => date('Y-m-d h:i:s'),
                    'updated_at' => date('Y-m-d h:i:s')
                ]);
			//	dd($redeem);
            }
            $new_point = $member_point - $redeem_point;
            $update_point = DB::table('opos_member')->where('id',$user_id)->update(['loyaltypts' => $new_point]);
            $msg = ['status' => 200, 'message' => 'Product(s) redeemed successfully', 'point_remaining' => $new_point];
        }

      return json_encode($msg);
    }

    //**b@ttle */
    public function getHistory(Request $request){

        $history = DB::table('product_pts_redemption')
        ->select('product_pts_redemption.*','product.name','product.thumbnail_1', 'product.id as prod_id')
        ->join('product', 'product_pts_redemption.product_id','=','product.id')
        ->where('member_id', $request->get('user_id'))
        ->orderBy('created_at', 'desc')
        ->get();

        return DataTables::of($history)
			->addIndexColumn()

			->addColumn('product_name',function($history){
                if (!empty($history->thumbnail_1)) {
                    $img_src = '/images/product/' . $history->prod_id . '/thumb/' . $history->thumbnail_1;
                    $img     = "<img src='$img_src' data-field='product_name' style=' width: 25px;height: 25px;display: inline-block;margin-right: 8px;object-fit:contain;'>";
                } else {
                    $img = null;
                }
                return $img . '<p class="os-" style="cursor: pointer; margin: 0;display: inline-block; style="text-align:left">' . (!empty($history->name) ? $history->name : 'Product Name') . '</p>';
           
			})
			->addColumn('quantity',function($history){
			return '<p data-field="retail_cust_name" style="margin:0;"  class="text-center">'.$history->quantity.'</p>';
			})
			->addColumn('points',function($history){
			return '<p data-field="retail_status" style="margin:0;" class="text-center">'.ucfirst($history->total_pts_redeemed).'</p>';
			})
			->addColumn('date',function($history){
                $date = date("dMy", strtotime($history->created_at));
				return '<p data-field="retail_nric" style="margin:0;" class="text-center">'.$date.'</p>';
			})
			->addColumn('status',function($history){
                return '<p data-field="retail_nric" style="margin:0;" class="text-center">Redeemed</p>';			})
    		
			->escapeColumns([])
			->make(true);
    }

    public function show_search(Request $request) {
        $terminal = terminal::where('systemid', $request->terminal_id)->first();
        Log::debug($terminal->id);

        try {
            if ($request->search != '') {
                $sst_number = terminal::where([
                    'id' => $terminal])->
                pluck('show_sst_no')->first();

                if ($sst_number == 1) {
                    $this->user_data = new UserData();
                    $company_id = $this->user_data->company_id();
                    $company_sstnumber = Company::find($this->
                    user_data->company_id());

                } else {
                    $company_sstnumber = "";
                }

                $opos_receipt = opos_receipt::where('terminal_id', $terminal->id)->where('systemid','LIKE','%'.$request->search."%")->first();
                $receipt_id = $opos_receipt->id;
//            $opos_receipt = opos_receipt::where('id',$receipt_id)->first();
                $barcode = DNS1D::getBarcodePNG(trim($opos_receipt->systemid), "C128");
                //$voucher_ids = $request->voucher_ids;
                //Log::debug("VOUCHER IDS".json_encode($voucher_ids));
                //$opos_receipt = opos_receipt::where('systemid',"$receipt_id")->first();

                /* Get the latest record. This is caused probably by table
                 * truncation. Resulting in having the receipt_id matching with
                 * multiple opos_receiptdetails */
                $opos_receiptdetails = opos_receiptdetails::where('receipt_id',$opos_receipt->id)->orderBy('id','desc')->first();


                Log::debug("Controller:\nopos_receipt=".
                    json_encode($opos_receipt));

                Log::debug("Controller:\nopos_receiptdetails=".
                    json_encode($opos_receiptdetails));


                $opos_receiptproduct = opos_receiptproduct::where('receipt_id',
                    $opos_receipt->id)->get();

                $terminal_id = terminal::find($opos_receipt->terminal_id);

                $locationterminal = locationterminal::where('terminal_id',
                    $terminal_id->id)->first();

                $location = location::find($locationterminal->location_id);
                $branch = $location->branch;

                $staff_details = User::find($opos_receipt->staff_user_id);
                $this->user_data = new UserData($staff_details);
                $company = Company::find($this->user_data->company_id());

                $date1 = Carbon::now()->toDateString();
                $date2 = Carbon::now()->endOfDay();

                $receipt_check_takeaway = opos_receipt::where('systemid',
                    $receipt_id)->
                whereBetween('created_at', [$date1, $date2])->
                first();
                $receipt_address = $opos_receipt->receipt_address;

                $table_name = 0;
                $takeaway_no = 0;
                $table_data = '';
                if ($receipt_check_takeaway != null) {
                    $table_data = openBill::where('receipt_id', $receipt_check_takeaway->id)->orderby('id','DESC')->pluck('ftype_id')->first();
                    if($table_data != null) {
                        $table_name = oposFtype::where('id', $table_data)->pluck('fnumber')->first();
                    }
                    $takeaway_data = takeaway::where('receipt_id',
                        $receipt_check_takeaway->id)->first();
                    if ($takeaway_data != null) {
                        $takeaway_no = $takeaway_data->takeaway_no;
                    }
                }

                if ($receipt_check_takeaway != null) {
                    $takeaway = 1;
                } else {
                    $takeaway = 0;
                }

                return view('opos_component.receipt_'.$opos_receipt->receipt_type, compact(
                    'barcode',
                    'takeaway_no',
                    'takeaway',
                    'table_data',
                    'opos_receipt',
                    'opos_receiptdetails',
                    'opos_receiptproduct',
                    'company',
                    'terminal_id',
                    'staff_details',
                    'company_sstnumber',
                    'receipt_address',
                    'table_name'
                ));

            } else {
                return response()->json(['status' => 404, 'message' =>  'Sorry, No record.']);
            }
        } catch (\Exception $e) {

            Log::error(
                "Error @ " . $e->getLine() . " file " . $e->getFile() .
                ":" . $e->getMessage()
            );
            return response()->json(['status' => 404, 'message' =>  'Sorry, No record.']);
        }
    }
}
