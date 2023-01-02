<?php

namespace App\Http\Controllers;

use DB;
use Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Matrix\Exception;
use Yajra\DataTables\DataTables;
use \App\Classes\SystemID;
use \App\Classes\UserData;
use \App\Models\location;
use \App\Models\opos_brancheod;
use \App\Models\merchantlocation;
use App\Models\locationterminal;
use App\Models\opos_eoddetails;
use App\Models\Staff;
use App\Models\opos_tablename;
use \App\Models\usersrole;
use \Illuminate\Support\Facades\Auth;
use App\Models\MerchantLink;
use App\Models\Company;
use App\Models\FoodCourt;
use App\Models\FoodCourtMerchant;
use App\Models\terminal;
use App\Models\FoodCourtMerchantTerminal;
use Illuminate\Support\Facades\Schema;
use App\Models\opos_receipt;
use \App\Models\LocationBarcode;

use \App\Http\Controllers\AnalyticsController;

use \App\Models\product;
use \App\Models\Merchant;


use Milon\Barcode\DNS1D;
use Milon\Barcode\DNS2D;

class LocationController extends Controller
{
    protected $user_data;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('CheckRole:loc');
    }

    public function index()
    {
        $this->user_data = new UserData();
        // $model = new location();
        // $data = $model->latest()->get();
	
		$company_id = $this->user_data->company_id();

        $userId = $this->getCompanyUserId();
        $responderIds = MerchantLink::where('initiator_user_id', $userId)->pluck('responder_user_id')->toArray();
        $initiatorIds = MerchantLink::where('responder_user_id', $userId)->pluck('initiator_user_id')->toArray();
        $merchantUserIds = array_merge($responderIds, $initiatorIds);

		$linkMerchantIds = Company::whereIn('company.owner_user_id', $merchantUserIds)->
			pluck('company.id')->toArray();
			//join('merchant', 'merchant.company_id', '=', 'company.id')->

		$linkMerchantInitIds = Company::whereIn('company.owner_user_id', $initiatorIds)->
			pluck('company.id')->toArray();
			//join('merchant', 'merchant.company_id', '=', 'company.id')->

		$linkMerchantResIds = Company::whereIn('company.owner_user_id', $responderIds)->
			pluck('company.id')->toArray();
			//join('merchant', 'merchant.company_id', '=', 'company.id')->
	
		//        $linkMerchantIds = [];
		//        $linkMerchantIds[] = $this->user_data->company_id();

        $relatedFoodCourts = FoodCourt::join('foodcourtmerchant',
			'foodcourtmerchant.foodcourt_id', '=', 'foodcourt.id')->
			where('tenant_merchant_id', $this->user_data->company_id())->
			pluck('foodcourt.location_id')->
			toArray();

        $relatedData = location::select('location.*',
			'merchantlocation.merchant_id')->
			join('merchantlocation', 'merchantlocation.location_id', '=',
			'location.id')->
			whereIn('location.id', $relatedFoodCourts)->latest()->get();

        //$model           = new location();

        $data = location::select('location.*',
			'merchantlocation.merchant_id')->
			join('merchantlocation', 'merchantlocation.location_id', '=',
			'location.id')->
			whereIn('merchantlocation.merchant_id', [$company_id])-> // $linkMerchantIds)->
			latest()->get();

        $data = $relatedData->merge($data);
	
		$relatedData = location::select('location.*',
				'franchisemerchantloc.franchisemerchant_id')->
				join('franchisemerchantloc', 'franchisemerchantloc.location_id',
					'=', 'location.id')->
				leftjoin('franchisemerchant','franchisemerchant.id','=','franchisemerchantloc.franchisemerchant_id')->
				leftjoin('franchise','franchise.id','=','franchisemerchant.franchise_id')->
				whereIn('franchisemerchant.franchisee_merchant_id',
					$linkMerchantResIds)->
				where('franchise.owner_merchant_id',$company_id)->
				latest()->get();

	
		$data = $relatedData->merge($data);

		//	$relatedData->map(function($z) use ($data) {
				//$data->push($z);
		//	});

		$relatedData = location::select('location.*',
				'franchisemerchantloc.franchisemerchant_id')->
				join('franchisemerchantloc', 'franchisemerchantloc.location_id',
					'=', 'location.id')->
				leftjoin('franchisemerchant','franchisemerchant.id','=','franchisemerchantloc.franchisemerchant_id')->
				leftjoin('franchise','franchise.id','=','franchisemerchant.franchise_id')->
				where('franchisemerchant.franchisee_merchant_id',
					$company_id)->
				whereIn('franchise.owner_merchant_id',$linkMerchantInitIds)->
				latest()->get();
	
		//$relatedData->map(function($z) use ($data) {
		//	$data->push($z);
		//});

		$data = $relatedData->merge($data);

        /*
		$ids  = merchantlocation::whereIn('merchant_id', $linkMerchantIds)->pluck('location_id');
		$data = $model->whereIn('id', $ids)->orderBy('created_at', 'desc')->latest()->get();
        */

        return Datatables::of($data)
            ->addIndexColumn()
            ->addColumn('loc_id', function ($location) {
                if($location->foodcourt){
                    if ($location->merchant_id == $this->user_data->company_id()) {
                        return '<p data-field="location_id" style="cursor: auto; margin: 0; text-align: center;"><a target="_blank" style="text-decoration:none" href="' .route('location.barcode_page',$location->systemid ).'">'. $location->systemid . '</a></p>';
                    } else {
                        // show static
                        $merchant = Company::select('company.name', 'company.systemid')
                           // ->join('merchant', 'merchant.company_id', '=', 'company.id')
                            ->where('company.id', $location->merchant_id)->first();

                        $operatorName = '';
                        $operatorId = '';

                        if ($merchant != null) {
                            $operatorName = $merchant->name;
                            $operatorId = $merchant->systemid;
                        }

                        return '<p class="os-linkcolor loyaltyOutput js-food-court" data-field="foodcourt" data-operator-name="'.$operatorName.'" data-operator-id="'.$operatorId.'" style="cursor: pointer; margin: 0; text-align: center;"><a target="_blank" style="text-decoration:none" href="'.route('location.barcode_page',$location->systemid ).'">'.$location->systemid.'</a></p>';
                    }
                } else {
                    return '<p data-field="location_id" style="cursor: auto; margin: 0; text-align: center;"><a target="_blank" style="text-decoration:none" href="'.route('location.barcode_page',$location->systemid ) .'">'. $location->systemid . '</a></p>';
                }

            })
            ->addColumn('branch', function ($location) {
                $branch = empty($location->branch) ? "Branch" : $location->branch;
                if ($location->merchant_id == $this->user_data->company_id()) {
                    return '<p class="os-linkcolor loyaltyOutput" data-field="branch" style="cursor: pointer; margin: 0;" >' . $branch . '</p>';
                } else {
                    return $branch;
                }
            })
            ->addColumn('address', function ($location) {
                $address = empty($location->address_line1) ? "Address" : $location->address_line1;
                if ($location->merchant_id == $this->user_data->company_id()) {
                    return '<p class="os-linkcolor loyaltyOutput" data-field="address" style="cursor: pointer; margin: 0;">' . $address . '</p>';
                } else {
                    return $address;
                }
            })
            ->addColumn('warehouse', function ($location) {
                if ($location->warehouse) {
                    return '<p data-field="warehouse" class="bg-tick" style="margin:0"><i class="fa fa-check" aria-hidden="true"></i></p>';
                } else {
                    return '';
                }

            })
            ->addColumn('foodcourt', function ($location) {
                if($location->foodcourt){

					$numberofFoodCourt = DB::table('foodcourtmerchant')->
					   join('foodcourt','foodcourtmerchant.foodcourt_id', '=', 'foodcourt.id')->
						where([
							'foodcourt.location_id' => $location->id,
                     	 	'foodcourtmerchant.status' => 'active'
						])->get()->count();

                    //$numberofFoodCourt++;

                    if ($location->merchant_id == $this->user_data->company_id()) {
                        // show link
						return '<a href="/foodcourt-tenant/'.$location->systemid.'" 
							style="text-decoration:none" class="btn-link os-linkcolor foodcourt-link" 
							target="_blank">'.$numberofFoodCourt.'</a>';
                    } else {
                        // show static
                        return $numberofFoodCourt;
                    }
                }else{
                    return '';
                }
            })
			->addColumn('licence_key', function ($memberList) use ($company_id) {
				$lic_locationkey =  DB::table('lic_locationkey')->
					where('location_id', $memberList->id)->
					first();

				if (empty($lic_locationkey)) {
					$html =  <<< EOD
			<span class="os-linkcolor" style="cursor:pointer" 
					onclick="generate_license_key($memberList->id)">Licence Key</span>
EOD;
				} else {
					$key = $lic_locationkey->license_key;
					$formated_key = '';
					
					for($x = 0; $x < strlen($key); $x++) {
						if ($x % 4 == 0 && $x != 0)
							$formated_key .= '-';
						$formated_key .= $key[$x];
					} 

					$html = <<< EOD
				<span class="os-linkcolor" style="cursor:pointer" 
					onclick="license_key_modal('$formated_key')">Issued</span>
EOD;	
				}
				return $html;
			})->
			addColumn('hwaddr', function ($memberList) {
				$serveraddr = DB::table('serveraddr')->
					where('location_id', $memberList->id)->
					first();
				return $serveraddr->hw_addr ?? null;
			})->
			addColumn('retag', function ($memberList) {
				$html = <<< EOD
					<img style="width:25px;height:25px;cursor:pointer"
						class="mt=0 mb-0 text-center"
						onclick="reset_confirm.display_confirm_reset($memberList->id)"
						src="/images/pinkcrab_50x50.png"/>
EOD;	
				return $html;
			})->
        	addColumn('bluecrab', function ($memberList) {
				$is_controller_disabled = (!empty($memberList->warehouse) || !empty($memberList->foodcourt)) ? 'true':'false';

				return '<div data-field="bluecrab"
					data-toggle="modal"
					data-target="#selectOptions"
					data-controller='."'$is_controller_disabled'".'
					onclick="check_controler_allowed(this)"
					id="quickPrompt">
					<img style="width:25px;height:25px;cursor:pointer"
					class="mt=0 mb-0 text-center"
					src="/images/bluecrab_25x25.png"/></div>';
            })
            ->addColumn('deleted', function ($memberList) {
                if ($memberList->merchant_id == $this->user_data->company_id()) {
					return '<div data-field="deleted"
					class="remove">
					<img style="width:25px;height:25px;cursor:pointer"
					class="mt=0 mb-0 text-center"
					src="/images/redcrab_25x25.png"/>';

                } else {
                    return '';
                }

            })
            ->escapeColumns([])
            ->make(true);
    }

	public function generateLicenseKey(Request $request) {
		try {

			$validation		= Validator::make($request->all(), [
				"location_id"	=>	"required"
			]);

			if ($validation->fails())
				throw new \Exception("validations_fails");

			$user_data		= new UserData();
			
			$lic			= substr(preg_replace("/[^a-zA-Z0-9]+/", "", 
				base64_encode(random_bytes(16))), 0, 16);

			$location_id	= $request->location_id;

			$is_franchsie 	= DB::table('franchisemerchant')->
				join('franchisemerchantloc','franchisemerchantloc.franchisemerchant_id', 
					'franchisemerchant.id')->
				where('franchisemerchantloc.location_id', $location_id)->
				select("franchisemerchant.*")->
				first();

			if (!empty($is_franchsie)) {
				$location_company_id = $is_franchsie->franchisee_merchant_id;
			} else {
				$location_company_id = DB::table('merchantlocation')->
					where('location_id',$location_id)->first()->merchant_id;
			}

			DB::table('lic_locationkey')->insert([
				"license_key"	=>	$lic,
				"company_id"	=>	$location_company_id,
				"location_id"	=>	$location_id,
				"created_at"	=>	date("Y-m-d H:i:s"),
				"updated_at"	=>	date("Y-m-d H:i:s")
			]);

			$msg = "Licence key generated";
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

    public function foodCourtMerchantTerminal($locationSystemId, $merchantId)
    {
        $id = Auth::user()->id;
        $user_data = new UserData();
        $user_roles = usersrole::where('user_id',$id)->get();
        $is_king =  \App\Models\Company::where('owner_user_id',Auth::user()->id)->first();

        $is_king = $is_king != null ? true : false;

        $location = location::where('systemid', $locationSystemId)->first();

        return view('location.foodcourt_merchant_terminal',compact('user_roles','is_king', 'location', 'merchantId'));
    }

    public function getCompanyUserId()
    {
        $userData = new UserData();
        $companyId = $userData->company_id();
        $company = Company::find($companyId);
        return $company->owner_user_id;
    }

    public function merchantTransactionExist($merchantId) {
        return merchantlocation::select('merchantlocation.id')
            ->join('opos_locationterminal', 'opos_locationterminal.location_id', '=', 'merchantlocation.location_id')
            ->join('opos_receipt', 'opos_receipt.terminal_id', '=', 'opos_locationterminal.terminal_id')
            ->where('merchantlocation.merchant_id', $merchantId)
            ->get()->count() > 0 ? true : false;
    }


    public function foodCourtTenant($locationSystemId)
    {
        //$id = Auth::user()->id;
		
		$user_data = new UserData();
        $merchant_id = $user_data->company_id();
                
		$location = location::where('systemid', $locationSystemId)->first();
        $locationId = $location->id;


        $userId = $this->getCompanyUserId();
        $responderIds = MerchantLink::where('initiator_user_id', $userId)->pluck('responder_user_id')->toArray();
        $initiatorIds = MerchantLink::where('responder_user_id', $userId)->pluck('initiator_user_id')->toArray();
        $merchantUserIds = array_merge($responderIds, $initiatorIds);

        $first = Company::select('company.id as company_id',
            'company.name as company_name',
            'company.business_reg_no as company_business_reg_no',
            'company.systemid as company_system_id',
            'company.owner_user_id',
            'merchant.id as merchant_id'
        )->join('merchant', 'merchant.company_id', '=', 'company.id')
            ->whereIn('company.owner_user_id', $merchantUserIds);

        $query = Company::select('company.id as company_id',
            'company.name as company_name',
            'company.business_reg_no as company_business_reg_no',
            'company.systemid as company_system_id',
            'company.owner_user_id',
            'merchant.id as merchant_id'
        )->join('merchant', 'merchant.company_id', '=', 'company.id');

        $query->where('company.owner_user_id', $userId)->union($first);

        $foodCourtTenants = $query->get();

        /*$activeTenants = FoodCourt::
                 join('foodcourtmerchant', 'foodcourtmerchant.foodcourt_id','=', 'foodcourt.id')
                ->where('foodcourt.location_id', $location->id)
                ->pluck('tenant_merchant_id')->toArray();


        $haveActiveTenants = count($activeTenants);*/

        $haveActiveTenants = 0;

        foreach ($foodCourtTenants as $key => $foodCourtTenant) {

            $foodCourtTenants[$key]['permanent_status'] = $this->merchantTransactionExist($foodCourtTenant->company_id) ? 'active' : 'inactive';

            /*
            $ids  = merchantlocation::join('location','location.id','=',
                'merchantlocation.location_id')->
            where('merchantlocation.merchant_id',$foodCourtTenant->merchant_id)->
            where('merchantlocation.location_id', $locationId)->
            whereNull('location.deleted_at')->
            pluck('merchantlocation.location_id');

            $query = locationterminal::join('opos_terminal',
                'opos_locationterminal.terminal_id','=','opos_terminal.id')->
            whereNull('opos_terminal.deleted_at')->
            whereIn('location_id', $ids)->
            orderby('opos_terminal.created_at', 'asc');
            */

            $foodCourtTenants[$key]['terminal'] = $terminalIds = FoodCourt::
            join('foodcourtmerchant', 'foodcourtmerchant.foodcourt_id', '=', 'foodcourt.id')
                ->join('foodcourtmerchantterminal', 'foodcourtmerchantterminal.foodcourtmerchant_id', '=', 'foodcourtmerchant.id')
                ->where('foodcourt.location_id', $locationId)
                ->where('foodcourtmerchant.tenant_merchant_id', $foodCourtTenant->company_id)
                ->get()->count();

            /*
            merchantlocation::join('opos_locationterminal', 'opos_locationterminal.location_id', '=', 'merchantlocation.location_id')
            ->where('merchantlocation.merchant_id', $foodCourtTenant->merchant_id)->get()->count();
            */

            $foodCourtTenants[$key]['status'] = 'inactive';

            $tenantStatus = FoodCourt::
                    join('foodcourtmerchant', 'foodcourtmerchant.foodcourt_id','=', 'foodcourt.id')
                    ->where('foodcourt.location_id', $location->id)
                    ->where('foodcourtmerchant.tenant_merchant_id',$foodCourtTenant->company_id)
                    ->where('foodcourtmerchant.status', 'active')->get()->count();

            if ($tenantStatus > 0) {
                $foodCourtTenants[$key]['status'] = 'active';
                $haveActiveTenants++;
            }
        }

        return view('location.foodcourt_tenant',compact(
            'locationSystemId',
            'location',
            'foodCourtTenants',
            'haveActiveTenants',
            'merchant_id'
        ));
    }

    public function saveFoodcourtTenant(Request $request)
    {
        $userData = new UserData();
        $ownerMerchantId = $userData->company_id();
        $locationId = $request->input('locationId');

        $foodCourt  = FoodCourt::where('location_id', $locationId)->first();
        if ($foodCourt == null) {
            $foodCourt = new FoodCourt();
        }

        $foodCourt->owner_merchant_id = $ownerMerchantId;
        $foodCourt->location_id = $locationId;
        $foodCourt->save();

        $foodCourtId = $foodCourt->id;

        /*
        $foodCourtMerchant = FoodCourtMerchant::where('foodcourt_id', $foodCourtId)->where('tenant_merchant_id', '!=' , $ownerMerchantId)->get();
        foreach($foodCourtMerchant as $fcMerchant) {
           $fcMerchantTerminals = FoodCourtMerchantTerminal::where('foodcourtmerchant_id', $fcMerchant->id)->get();
           foreach($fcMerchantTerminals as $fcMerchantTerminal) {
               terminal::where('id', $fcMerchantTerminal->terminal_id)->delete();
           }
            FoodCourtMerchantTerminal::where('foodcourtmerchant_id', $fcMerchant->id)->delete();
        }

        FoodCourtMerchant::where('foodcourt_id', $foodCourtId)->where('tenant_merchant_id', '!=' , $ownerMerchantId)->delete();
        */

        $activeMerchants = $request->input('activeMerchants');
        foreach ($activeMerchants as $tenant) {

            if ($tenant['merchantId'] == $ownerMerchantId) {
                $status = FoodCourtMerchant::where('foodcourt_id', $foodCourtId)->where('tenant_merchant_id',$ownerMerchantId)->first();
                if ($status != null) {
                    continue;
                }
            }

            $foodCourtMerchant = FoodCourtMerchant::where('foodcourt_id', $foodCourtId)->where('tenant_merchant_id',$tenant['merchantId'])->first();
            if (is_null($foodCourtMerchant)) {
                $foodCourtMerchant = new FoodCourtMerchant();
            }

            $foodCourtMerchant->foodcourt_id = $foodCourtId;
            $foodCourtMerchant->tenant_merchant_id = $tenant['merchantId'];
            $foodCourtMerchant->status = $tenant['status'];
            $foodCourtMerchant->save();
        }

        /*
        foreach($activeMerchants as $merchantId) {
            if ($merchantId == $ownerMerchantId) {
                $status = FoodCourtMerchant::where('foodcourt_id', $foodCourtId)->where('tenant_merchant_id',$ownerMerchantId)->first();
                if ($status != null) {
                    continue;
                }
            }
            $foodCourtMerchant = new FoodCourtMerchant();
            $foodCourtMerchant->foodcourt_id = $foodCourtId;
            $foodCourtMerchant->tenant_merchant_id = $merchantId;
            $foodCourtMerchant->save();
        }
        */

        if ($foodCourtId) {
            return response()->json(['msg' => 'FoodCourt tenant saved successfully']);
        }

    }

    public function saveTenantTerminal(Request $request)
    {
        $merchantId = $request->input('merchantId');
        $locationId = $request->input('locationId');

        $systemid = new SystemID('terminal');

        $terminal = new terminal();
        $link     = new locationterminal();

        $terminal->systemid = $systemid;
        $terminal->btype_id = 1;
        $terminal->save();

        $link->terminal_id = $terminal->id;
        $link->location_id = $locationId;
        $link->save();

        $sq_name = 'receipt_seq_' . sprintf("%06d",$terminal->id);

        \DB::select(\DB::raw("create sequence $sq_name nocache nocycle"));

        $foodCourtMerchant = FoodCourt::select('foodcourtmerchant.id')->
            join('foodcourtmerchant', 'foodcourtmerchant.foodcourt_id', '=',
                'foodcourt.id')->
            where('foodcourt.location_id', $locationId)->
            where('foodcourtmerchant.tenant_merchant_id', $merchantId)->
            first();

        if ($foodCourtMerchant != null) {
            $fcMerchantTerminal = new FoodCourtMerchantTerminal();
            $fcMerchantTerminal->foodcourtmerchant_id = $foodCourtMerchant->id;
            $fcMerchantTerminal->terminal_id = $terminal->id;
            $fcMerchantTerminal->save();
        }

        return response()->json([
            'msg' => 'Terminal created successfully.',
            'status' => 'true'
        ]);
    }

    function delTenantTerminal(Request $request) {
        $fcmtId = $request->input('fcmtId');
        $fcmt = FoodCourtMerchantTerminal::find($fcmtId);

        $terminalId = $fcmt->terminal_id;

        $locationterminal = locationterminal::where('terminal_id', $terminalId)->first();
        $locationterminal->delete();

        $terminal = terminal::where('id', $terminalId)->first();
        $terminal->delete();

        $fcmt->delete();

        return response()->json(['msg' => 'Terminal deleted successfully', 'status' => 'true']);

    }

    public function tenantTerminals(Request $request)
    {

        $merchantId = $request->input('merchantId');
        $locationId = $request->input('locationId');

        $model           = new locationterminal();

        $terminalIds = FoodCourt::
            join('foodcourtmerchant', 'foodcourtmerchant.foodcourt_id', '=', 'foodcourt.id')
            ->join('foodcourtmerchantterminal', 'foodcourtmerchantterminal.foodcourtmerchant_id', '=', 'foodcourtmerchant.id')
            ->where('foodcourt.location_id', $locationId)
            ->where('foodcourtmerchant.tenant_merchant_id', $merchantId)
            ->pluck('foodcourtmerchantterminal.terminal_id');


        $query = $model->join('opos_terminal',
            'opos_locationterminal.terminal_id','=','opos_terminal.id')->
        whereNull('opos_terminal.deleted_at')->
        whereIn('opos_terminal.id', $terminalIds)->
        orderby('opos_terminal.created_at', 'asc');

        $recordsTotal = $query->get()->count();

        // applying limit
        $data = $query->skip($request->input('start'))->take($request->input('length'))->get();

        $counter = 0 + $request->input('start');

        foreach ($data as $key => $terminal) {
            $data[$key]['indexNumber'] = ++$counter;
            $data[$key]['locationSystemId'] = location::find($terminal->location_id)->systemid;

            $branchName     = location::find($terminal->location_id)->branch;
            $data[$key]['branchName'] = empty($branchName) ? "Branch" : $branchName;

            $terminalData          = terminal::find($terminal->terminal_id);
            $data[$key]['terminalSystemId'] = empty($terminalData->systemid) ? "Terminal ID" : $terminalData->systemid;

            $fcmt = FoodCourt::select('foodcourtmerchantterminal.id')->
            join('foodcourtmerchant', 'foodcourtmerchant.foodcourt_id', '=', 'foodcourt.id')
                ->join('foodcourtmerchantterminal', 'foodcourtmerchantterminal.foodcourtmerchant_id', '=', 'foodcourtmerchant.id')
                ->where('foodcourt.location_id', $locationId)
                ->where('foodcourtmerchant.tenant_merchant_id', $merchantId)
                ->where('foodcourtmerchantterminal.terminal_id', $terminal->terminal_id)
                ->first();

            $data[$key]['fcmt_id'] = $fcmt->id;

            $data[$key]['deleteStatus'] = opos_receipt::where('terminal_id', $terminal->terminal_id)->get()->count() > 0 ? 'inactive' : 'active';
        }

        $response = [
            'data' => $data,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsTotal
        ];
        return response()->json($response);
    }

    public function edit($id)
    {
        //
    }

    public function store(Request $request)
    {
        //Create a new product here
        try {

            $this->user_data  = new UserData();
            $merchantlocation = new merchantlocation();
            $SystemID         = new SystemID('location');
            $location         = new location();

            $location->systemid = $SystemID;

            if ($request->has('warehouse')) {
                if ($request->warehouse == 'yes') {
                    $location->warehouse = true;
                }
            }
            if ($request->has('foodcourt')) {
                if ($request->foodcourt == 'yes') {
                    $location->foodcourt = true;
                }
            }
            $location->save();

            //tablename
			$this->new_tablename($location->id);

            $sq_name = 'takeaway_seq_' . sprintf("%06d",$location->id);
            \DB::select(\DB::raw("create sequence $sq_name nocache nocycle"));

            $merchantlocation->location_id = $location->id;
            $merchantlocation->merchant_id = $this->user_data->company_id();
            $merchantlocation->save();

            $msg = "Location added successfully";

        } catch (\Illuminate\Database\QueryException $e) {
            $msg = "Error occured occured while storing location in database";

            Log::error("Error @ " . $e->getLine() . " file " . $e->getFile() . " " .
                $e->getMessage());
        }
        return view('layouts.dialog', compact('msg'));
    }

    public function showEditModal(Request $request)
    {
        try {
            $this->user_data = new UserData();
            $allInputs       = $request->all();
            $id              = $request->get('id');
            $fieldName       = $request->get('field_name');

            $is_exist = merchantlocation::where(
                ['location_id' => $id, 'merchant_id' => $this->user_data->company_id()])->first();

            if (!$is_exist && $request->field_name != 'bluecrab') {
                throw new Exception("Location not found", 1);
            }

            $location = location::find($id);

            if (!$location) {
                throw new Exception("Location not found", 1);
            }

            $validation = Validator::make($allInputs, [
                'id'         => 'required',
                'field_name' => 'required',
            ]);

            if ($validation->fails()) {
                throw new Exception("Invalid validation", 1);
            }

            if ($request->field_name == 'branch') {
                $model = 'branch';
            } else if ($request->field_name == 'address') {
                $model = 'address';
            } elseif ($request->field_name == 'deleted') {
                $model = "deleted";
            } else {
                return '';
            }

            return view('location.edit-model', compact('location', 'model'));

        } catch (\Illuminate\Database\QueryException $e) {
            $msg = "Some error occured";
            log::debug($e);
            return view('layouts.dialog', compact('msg'));
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|string
     */
    public function update(Request $request)
    {
        try {
            $this->user_data = new UserData();
            $location_id     = $request->location_id;

          /*  $is_exist = merchantlocation::where(
                ['location_id' => $location_id, 'merchant_id' => $this->user_data->company_id()])->first();

            if (!$is_exist) {
                throw new Exception("Location not found", 1);
            }
		   
		  */

            $location = location::find($location_id);
			
			if (!$location) {
                throw new Exception("Location not found", 1);

            }

            $changed = false;

            if ($request->has('branch')) {
                if ($location->branch != $request->branch) {
                    $location->branch = $request->branch;
                    $changed = true;
                }
            }

            if ($request->has('address')) {
                if ($location->address_line1 != $request->address) {
                    $location->address_line1 = $request->address;
                    $changed           = true;
                }
            }

			$purple = false;
            if ($request->has('e_table_header_color')) {
                if ($location->e_table_header_color != $request->e_table_header_color) {
                    $location->e_table_header_color = $request->e_table_header_color;
                    $changed = true;
					$purple = true;
					$msg = "Screen E Details updated";
                }
            }

            if ($request->has('e_bottom_panel_color')) {
                if ($location->e_bottom_panel_color != $request->e_bottom_panel_color) {
                    $location->e_bottom_panel_color = $request->e_bottom_panel_color;
                    $changed = true;
					$purple = true;
					$msg = "Screen E Details updated";
                }
            }

            if ($request->has('e_right_panel_color')) {
                if ($location->e_right_panel_color != $request->e_right_panel_color) {
                    $location->e_right_panel_color = $request->e_right_panel_color;
                    $changed = true;
					$purple = true;
					$msg = "Screen E Details updated";
                }
            }

            if ($changed == true) {
                $location->save();

				Log::debug('OUTSIDE  purple='.$purple);

				if ($purple) {
					// Note that this is not being used at the blade as
					// the blade is popping up another modal
					Log::debug('INSIDE  purple='.$purple);
					return view('layouts.purpledialog', compact('msg'));


				} else {
					$msg = "Data updated";
					return view('layouts.dialog', compact('msg'));
				}
            } else {
                return '';
            }

        } catch (\Exception $e) {
            $msg = "Some error occured";
            log::error($e);
            return view('layouts.dialog', compact('msg'));
        }
    }

    public function destroy($id)
    {
        try {
            $this->user_data = new UserData();
            $location        = location::find($id);

            $is_exist = merchantlocation::where(
                ['location_id' => $id, 'merchant_id' => $this->user_data->company_id()])->first();

            if (!$is_exist) {
                throw new Exception("Location not found", 1);
            }

            if (!$location) {
                throw new Exception("Location not found", 1);
            }

            //delete tablename
            $tablenames = opos_tablename::where('location_id', $location->id)->get();
            foreach ($tablenames as $tablename) {
                $tablename->delete();
            }

            $sq_name = 'takeaway_seq_' . sprintf("%06d",$location->id);
            \DB::select(\DB::raw("drop sequence $sq_name"));

            $location->delete();

            $msg = "Location deleted successfully";
            return view('layouts.dialog', compact('msg'));

        } catch (\Exception $e) {
            $msg = "Some error occured";
            log::error($e);
            return view('layouts.dialog', compact('msg'));
        }
    }

    public function showInventoryView()
    {
        return view('inventory.inventory');
    }

    public function showInventoryQtyView()
    {
        return view('inventory.inventoryqty');
    }


    public function getTerminalTime(Request $request)
    {
        if ($request->ajax()) {
            # code...
            try{

                $rowId = $request->rowId;

                // validate row id
                $validateId = Location::find($rowId);

                if($validateId){

                    // get location teminal time
                    $location = location::where('id', $rowId)->first();
                    $starttime = $location->start_work;
                    $endtime = $location->close_work;

                    return response()->json(['status' => 202, 'starttime' => $starttime, 'endtime' => $endtime]);
                }

            }catch(\Exception $e){
                Log::error(
                    "Error @ " . $e->getLine() . " file " . $e->getFile() . ":" . $e->getMessage()
                );
            }
        }
    }

    public function updateTerminalTime(Request $request)
    {
        if ($request->ajax()) {
            # code...
            try{

                $rowId = $request->rowId;
                $starttime = $request->starttime;
                $endtime = $request->endtime;
                $staff_id = $request->staff_id;
                $staff = Staff::where('systemid', $staff_id)->first();
                $user_id = $staff->user_id;

                // validate row id
                $validateId = Location::find($rowId);

                if($validateId){

                    // update location teminal time
                    $location = Location::find($rowId);
                    $location->start_work = $starttime;
                    $location->close_work = $endtime;
                    $location_id = $location->id;
                    $location->update();

                    //locks the terminal if time is reset
                    $opos_brancheod = new opos_brancheod();
                    $opos_brancheod->eod_presser_user_id = $user_id;
                    $opos_brancheod->location_id = $location_id;
                    $opos_brancheod->save();

                    //update id in eoddetails
                    $ids = locationterminal::where('location_id', $location->id)->pluck('terminal_id');
                    foreach ($ids as $id) {
                        $eod_details = opos_eoddetails::where('logterminal_id', $id)->latest('created_at')->first();
                        if ($eod_details) {
                            $eod_details->eod_id = $opos_brancheod->id;
                            $eod_details->update();
                        }
                    }


                    return response()->json(['status' => 202, 'message' => 'Terminal operation hour updated successfully.']);
                }else{
                    return response()->json(['status' => 404, 'message' =>  'Sorry, failed to update record.']);
                }
            }catch(\Exception $ex){
                Log::error(
                    "Error @ " . $ex->getLine() . " file " . $ex->getFile() . ":" . $ex->getMessage()
                );
                return response()->json(['status' => 404, 'message' =>  'Sorry, failed to update record.']);
            }
        }
    }

    public function new_tablename($location_id) {
        $tableName1 = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','1','2','3','4','5','6','7','8','9','10','11','12','13','14','15','16','17','18','19','20','21','22','23','24','25','26','27','28','29','30','31','32','33','34','35','36','37','38','39','40','41','42','43','44','45','46','47','48','49','50','51','52','53','54','55','56','57','58','59','60','61','62','63','64','65','66','67','68','69','70','71','72','73','74','75','76','77','78','79','80','81','82','83','84','85','86','87','88','89','90','91','92','93','94','95','96','97','98','99','100','101','102','103','104','105','106','107','108','109','110','111','112','113','114','115','116','117','118','119','120','121','122','123','124','125','126','127','128','129','130','131','132','133','134','135','136','137','138','139','140','141','142','143','144','145','146','147','148','149','150','151','152','153','154','155','156','157','158','159','160','161','162','163','164','165','166','167','168','169','170','171','172','173','174','175','176','177','178','179','180','181','182','183','184','185','186','187','188','189','190','191','192','193','194','195','196','197','198','199','200');
		$string = [];
		$date = date("Y-m-d H:i:s");
		foreach ($tableName1 as $t) {
			$string[] = [
				"location_id"  => $location_id,
				"default_name" => $t,
				"created_at"   => $date,
				"updated_at"   => $date
			];	
		}

		DB::table('opos_tablename')->insert($string);
    }


    public function getBranchLocationByLoggedInUser(){
		/*
		$id = $this->getCompanyUserId();

        Log::debug('id='.$id);

        $branchLocationObj = new location();
        $rs = $branchLocationObj->getBranchLocationByLoggedInUserId($id);
		 */
		$analyticsController = new AnalyticsController();

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

       
      	$branch_location = collect($branch_location); 
		$branch_location = $branch_location->unique('id')->values();
        $rs_loc = response()->json($branch_location);
       
        // dd($rs_loc);
        return $rs_loc;
    }

    public function saveLocationImage(Request $request)
    {

        try {
            $validation = Validator::make($request->all(), [
                'location_id' => 'required',
            ]);

            if ($validation->fails()) {
                throw new \Exception("validation_error", 19);
            }

            $location = location::where('id', $request->location_id)->first();

            if (!$location) {
                throw new \Exception('location_not_found', 25);
            }

            if ($request->hasfile('file')) {
                $file = $request->file('file');
                $extension = $file->getClientOriginalExtension(); // getting image extension
                $company_id = Auth::user()->staff->company_id;

                if (!in_array($extension, array(
					'jpg', 'JPG', 'png', 'PNG', 'jpeg', 'JPEG', 'gif', 'GIF', 'bmp', 'BMP', 'tiff', 'TIFF',
					'mp4', '3gp', 'avi', 'flv', 'mpeg'
				))) {
                    return abort(403);
                }

                $filename = ('p' . sprintf("%010d", $location->id)) . '-m' . sprintf("%010d", $company_id) . rand(1000, 9999) . '.' . $extension;

                $location_id = $location->id;

                $this->check_location("/images/location/$location_id/");
                $file->move(public_path() . ("/images/location/$location_id/"), $filename);

                $location->e_right_panel_image_file = $filename;
                $location->save();

                $return_arr = array("name" => $filename, "size" => 000, "src" => "/images/location/$location_id/$filename");
                return response()->json($return_arr);
            } else {
                return abort(403);
            }

        } catch (\Exception $e) {

            if ($e->getMessage() == 'validation_error') {
                return '';
            }
            if ($e->getMessage() == 'location_not_found') {
                $msg = "Error occured while uploading, Invalid location selected";
            }
            {
                $msg = "Error occured while uploading picture";
            }

            Log::error(
                "Error @ " . $e->getLine() . " file " . $e->getFile() .
                ":" . $e->getMessage()
            );

            $data = view('layouts.dialog', compact('msg'));
        }
        return $data;
    }

    public function deleteLocationImage(Request $request)
    {

        try {
            $validation = Validator::make($request->all(), [
                'location_id' => 'required',
            ]);

            if ($validation->fails()) {
                throw new \Exception("validation_error", 19);
            }

            $location = location::where('id', $request->location_id)->first();

            if (!$location) {
                throw new \Exception('location_not_found', 25);
            }
            unlink(public_path() . ("/images/location/$location->id/$location->e_right_panel_image_file"));
            $location->e_right_panel_image_file = null;
            $location->save();
            $return = response()->json(array("deleted" => "True"));

        } catch (\Exception $e) {
            $return = response()->json(array("deleted" => "False"));
        }

        return $return;

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


    public function showBarCodePage($locationId)
    {
        $id = Auth::user()->id;
        $user_data = new UserData();

        $user_roles = usersrole::where('user_id',$id)->get();
    
        $is_king =  \App\Models\Company::
			where('owner_user_id',Auth::user()->id)->first();
    
        if ($is_king != null) {
            $is_king = true;	
        } else {
            $is_king  = false;
        }
    
        if (!$user_data->company_id()) {
            abort(404);
        }

        $location = location::where('systemid',$locationId)->first();
	
	$merchantlocation = DB::table('merchantlocation')->
		where('location_id',$location->id)-> 
		first();

        if (empty($location)) {
            Log::info('Invalid Location id');
            abort(404);
	}

	$show_barcode = $merchantlocation->merchant_id == $user_data->company_id();
        // dd($user_roles,$is_king);
        return view('location.barcode_landing_page',
			compact('user_roles','is_king','location','show_barcode'));
    }


    public function fetchBarCodePage($locationId)
    {
		$id = Auth::user()->id;
        $user_data = new UserData();

        $user_roles = usersrole::where('user_id',$id)->first();

        $location = location::where('systemid',$locationId)->first();

        if (empty($location)) {
            Log::info('Invalid Location id');
            abort(404);
        }

	
	$merchantlocation = DB::table('merchantlocation')->
		where('location_id',$location->id)-> 
		first();
	
	$LocationBarcode = LocationBarcode::where('location_id',$location->id)->get();
		
		$address = [$location->address_line1, $location->address_line2, $location->address_line3];
        $address =  array_filter($address,function($value) {
            return ($value == '') ? false:true;
        });

        $address = implode(', ',$address);
        
        foreach($LocationBarcode as $add) {
            $add->addr = $address;
        }

		$ipconfExist = DB::table('ipconf')->where('location_systemid',$locationId)->first();
		$public_ip = $ipconfExist->public_ip.":".$ipconfExist->public_port ?? '0.0.0.0:80';
		$local_ip = $ipconfExist->local_ip.":".$ipconfExist->local_port ??  '0.0.0.0:80';
        //system_default entry
        $ipaddrfield = "";
        if(($user_roles->role_id ?? 0) == '18'){
        	$ipaddrfield = "<div style='text-align: center;'><label style='height: 10px; display: block;'>Public IP </label><a href='#' style='width:150px;text-align: center;' id='publicipaddress' data-toggle='modal' data-target='#ipaddressupdate' data-localip='".$local_ip."' data-publicip='".$public_ip."' placeholder='Public IP'/>".$public_ip."</a> <br> <label style='height: 10px; display: block;'>Local IP </label><a href='#' id='localipaddress' style='width:150px;text-align: center;' data-toggle='modal' data-target='#ipaddressupdate' data-localip='".$local_ip."' data-publicip='".$public_ip."'  placeholder='Local IP'>".$local_ip."</a></div>";
        }
		$system_default = [    
        	"id" => "system_default",
   		    "location_id" => $locationId,
        	"name" => "$location->branch",
        	"notes"=>'',
        	"barcode" => $locationId,
			"addr"=> $address,
			"ipaddress"=>$ipaddrfield
		];

	//$location = location::where('systemid',$locationid)->first();
	$franchisemerchantloc = DB::table('franchisemerchantloc')->
		join('franchisemerchant','franchisemerchant.id','=','franchisemerchantloc.franchisemerchant_id')->
		join('franchise','franchise.id','=','franchisemerchant.franchise_id')->
		where('franchisemerchantloc.location_id', $location->id)->
		whereNull(['franchisemerchantloc.deleted_at',
			'franchise.deleted_at','franchisemerchant.deleted_at'])->get();
	
	$franchisemerchantloc->map(function($z) use ($system_default, $LocationBarcode) {
			
		$new_collection = (object) collect($system_default)->reject(function($item){})->all();
	 	$company_data = company::find($z->owner_merchant_id);
		$new_collection->notes = <<<EOD
					$z->name 
					<br/>
					$company_data->name
					<br/>
					$company_data->systemid

EOD;
		$new_collection->assigned_to = $z->franchisee_merchant_id;
        	$LocationBarcode->prepend($new_collection);

	});
	
	
	if ($franchisemerchantloc->isEmpty()) {
        	$new_collection = (object) collect($system_default)->reject(function($item){})->all();
		$LocationBarcode->prepend($new_collection);
	}
	if ($merchantlocation->merchant_id != $user_data->company_id()) {
		$my_merchant_id = $user_data->company_id();
		
		$LocationBarcode = $LocationBarcode->filter(function($z) use ($my_merchant_id) {
			
			if (empty($z->assigned_to)) {
				return false;
			}

			return $z->assigned_to == $my_merchant_id;
		});

	}

        $LocationBarcode = $LocationBarcode->filter(function($bar) {
            return empty($bar->barcode) ? false:true;
        });

	$foodcourt = DB::table('foodcourt')->
		where('location_id',$location->id)->
		first();
	
	if (!empty($foodcourt)) {
		if ($user_data->company_id() != $foodcourt->owner_merchant_id) {
			$new_collection = (object) collect($system_default)->reject(function($item){})->all();
	 		$company_data = company::find($foodcourt->owner_merchant_id);
			$new_collection->notes = <<<EOD
					$company_data->name
					<br/>
					$company_data->systemid

EOD;
        	$LocationBarcode->prepend($new_collection);
		}
	}
        return  Datatables::of($LocationBarcode)
        ->addIndexColumn()
        
        ->addColumn('barcode',function($memberList){
            $barcode = DNS1D::getBarcodePNG(trim($memberList->barcode), "C128");
            $img = asset("images/barcode.png");

            $name = (empty($memberList->name)) ? "Barcode Name":$memberList->name;
            //$name = ($memberList->id == 'system_default')  ? '':$name;
            
            $htmlTemplate = <<<EOD
                <a class="name" data-barcode_id="$memberList->id" href="#">$name</a>
                <br/><img src='data:image/png;base64,$barcode' width='200' height='60' alt=''><br/>$memberList->barcode
            EOD;

            return $htmlTemplate;
        })
        ->addColumn('qrcode',function($memberList){
            $img = asset("images/qr.png");
            $qr = DNS2D::getBarcodePNG(trim($memberList->barcode), "QRCODE");
          
            $htmlTemplate = <<<EOD
                <img src='data:image/png;base64,$qr' width='70' height='70' alt=''></td>
            EOD;

            return $htmlTemplate;
        })
        ->addColumn('address',function($memberList){
            return  empty($memberList->addr) ? "Address":"$memberList->addr";
        })
        ->addColumn('note',function($memberList){
            return $memberList->notes;
        })
        ->addColumn('print',function($memberList){
            $htmlTemplate = <<<EOD
            <a href="#" ><button class="btn btn-success btn-log bg-web sellerbutton" style="color:#fff;margin:0;float:none"><span>Print</span></button></a>
            EOD;
            return $htmlTemplate;
        })
        ->addColumn('deleted', function ($memberList) {
            $htmlTemplate = <<< EOD
                <div data-barcode_id="$memberList->id" data-field="deleted"
                class="remove-barcode">
                <img style="width:25px;height:25px;cursor:pointer"
                class="mt=0 mb-0 text-center"
                src="/images/redcrab_25x25.png"/>
            EOD;
            $htmlTemplate  = ($memberList->id == 'system_default')  ? '':$htmlTemplate;
            return $htmlTemplate;
        })
        ->escapeColumns([])
        ->make(true);
    }

    public function new_location_barcode(Request $request)
    {
        try {

            if (!$request->has('barcodes')) {
                abort(404);
            }

            $barcodes = trim($request->barcodes);
		    $barcodes = str_replace("\n", ";", $barcodes);
		    $barcodes = str_replace(",", ";", $barcodes);
            $parts = explode(';', $barcodes);
            
            $this->user_data = new UserData();
            $merchant_id = $this->user_data->company_id();
            
            $location = location::where('systemid',$request->location_id)->first();
            
            $merchantlocation = merchantlocation::where(["location_id"=>$location->id,"merchant_id"=>$merchant_id])->first();
            $duplicate_barcodes = [];
            $system_id_barcodes = [];
            $msg_1 = $msg_2 ='';

            foreach ($parts as $barcode)
            {

                if (empty($barcode)) {
                    continue;
                }
                
                $is_exist = LocationBarcode::where(['barcode'=>$barcode])->first();
                if ($is_exist) {
                    $is_duplicate = true;
                    $duplicate_barcodes[] = $barcode;
                    continue;
                }


                $system_ids = DB::select( DB::raw(
                        "SELECT
                             t.systemid
                                    FROM (
                                        SELECT systemid FROM product WHERE systemid='$barcode' UNION
                                        SELECT systemid FROM staff WHERE systemid='$barcode' UNION
                                        SELECT systemid FROM company WHERE systemid='$barcode' UNION
                                        SELECT systemid FROM opos_terminal WHERE systemid='$barcode' UNION
                                        SELECT systemid FROM opos_receipt WHERE systemid='$barcode' UNION
                                        SELECT systemid FROM stockreport WHERE systemid='$barcode' UNION
                                        SELECT systemid FROM plat_counter WHERE systemid='$barcode' UNION
                                        SELECT systemid FROM opos_promo WHERE systemid='$barcode' UNION
                                        SELECT systemid FROM opos_member WHERE systemid='$barcode' UNION
                                        SELECT systemid FROM voucherlist WHERE systemid='$barcode' UNION
                                        SELECT systemid FROM opos_wastage WHERE systemid='$barcode' UNION
                                        SELECT systemid FROM rack WHERE systemid='$barcode' Union
                                        SELECT systemid FROM opos_loyaltyptslog WHERE systemid='$barcode' UNION
                                        SELECT systemid FROM opos_membershipmtslog WHERE systemid='$barcode' UNION
                                        SELECT systemid FROM franchise WHERE systemid='$barcode' UNION
                                        SELECT systemid FROM og_tank WHERE systemid='$barcode' UNION
                                        SELECT systemid FROM opos_calibration WHERE systemid='$barcode' UNION
                                        SELECT systemid FROM og_controller WHERE systemid='$barcode' UNION
                                        SELECT systemid FROM og_pump WHERE systemid='$barcode' UNION
                                        SELECT systemid FROM opos_calibration WHERE systemid='$barcode' UNION
                                        SELECT systemid FROM og_controller WHERE systemid='$barcode' UNION
                                        SELECT systemid FROM og_pump WHERE systemid='$barcode' UNION
                                        SELECT systemid FROM invoice WHERE systemid='$barcode' UNION
                                        SELECT systemid FROM stocktakemgmt WHERE systemid='$barcode' UNION
                                        SELECT systemid FROM ec_ecommercemgmt WHERE systemid='$barcode' UNION
                                        SELECT systemid FROM cmrmgmt WHERE systemid='$barcode' UNION
                                        SELECT systemid FROM creditnote WHERE systemid='$barcode' UNION
                                        SELECT systemid FROM debitnote WHERE systemid='$barcode' UNION
                                        SELECT systemid FROM product_pts_redemption WHERE systemid='$barcode' UNION
                                        SELECT systemid FROM comm_agent WHERE systemid='$barcode' UNION
                                        SELECT systemid FROM comm_company WHERE systemid='$barcode' UNION
                                        SELECT systemid FROM csrmgmt WHERE systemid='$barcode' UNION
                                        SELECT systemid FROM projmgmt WHERE systemid='$barcode' UNION
                                        SELECT systemid FROM pjob WHERE systemid='$barcode' UNION
                                        SELECT systemid FROM salesorder WHERE systemid='$barcode' UNION
                                        SELECT systemid FROM `location` WHERE systemid='$barcode'  UNION
                                        SELECT systemid FROM opos_refund WHERE systemid='$barcode'
                                    ) as t;" 
                                    ) );

                if (!empty($system_ids)) {
                    $is_system_id_barcode = true;
                    $system_id_barcodes[] = $barcode; 
                        continue;
                }


                $LocationBarcode = new LocationBarcode();
                $LocationBarcode->location_id = $location->id;
                $LocationBarcode->merchantlocation_id = $merchantlocation->id;
                $LocationBarcode->barcode = $barcode;
                $LocationBarcode->save();
            }

            if (isset($is_duplicate)) {
                $duplicate_barcodes = implode("<br/>",$duplicate_barcodes );
                $msg_1 = "Duplicated barcodes found:<br> $duplicate_barcodes<br/>";
            }

            if (isset( $is_system_id_barcode)) {
                $system_id_barcodes = implode("<br/>",$system_id_barcodes );
                $msg_2 = "System IDs cannot be used as barcodes:<br/>$system_id_barcodes<br/>";
            }
            if (isset($is_duplicate) || isset( $is_system_id_barcode)) {
                $msg = $msg_1 .$msg_2;
                $html = view('layouts.dialog', compact('msg'))->render();
                return $html;
            } else {
                return 0;
            }

            return response()->json(["success"=>"true"]);

        } catch (\Exceptional $e) {
            Log::error($e);
        }
    }

    public function location_barcode_name_update(Request $request) {
        try {
            
            $this->user_data = new UserData();
            $merchant_id = $this->user_data->company_id();
            
            $location = location::where('systemid',$request->location_id)->first();
            
            $merchantlocation = merchantlocation::where(["location_id"=>$location->id,"merchant_id"=>$merchant_id])->first();

            $is_exist = LocationBarcode::where(['id'=>$request->barcode_id,"merchantlocation_id"=>$merchantlocation->id])->first();
             
            if ($is_exist) {
                $is_exist->name = $request->name;
                $is_exist->update();
                $msg = "Barcode name updated.";
            } else {
                $msg = "Barcode not found";
            }
            
            return view('layouts.dialog', compact('msg'))->render();

        } catch (\Exceptional $e) {
            Log::error($e);
        }
    }

    public function location_barcode_delete(Request $request) {
        try {
            
            $this->user_data = new UserData();
            $merchant_id = $this->user_data->company_id();
            
            $location = location::where('systemid',$request->location_id)->first();
            
            $merchantlocation = merchantlocation::where(["location_id"=>$location->id,"merchant_id"=>$merchant_id])->first();

            $is_exist = LocationBarcode::where(['id'=>$request->barcode_id,"merchantlocation_id"=>$merchantlocation->id])->first();
             
            if ($is_exist) {
                $is_exist->name = $request->name;
                $is_exist->delete();
                $msg = "Barcode deleted.";
            } else {
                $msg = "Barcode not found";
            }
            
            return view('layouts.dialog', compact('msg'))->render();

        } catch (\Exceptional $e) {
            Log::error($e);
        } 
    }
}
