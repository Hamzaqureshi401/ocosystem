<?php

namespace App\Http\Controllers;

//use App\FranchiseMerchantLoc;
use App\Models\FranchiseMerchantLoc;
use Illuminate\Http\Request;
use App\Classes\UserData;
use \App\Models\usersrole;
use \Illuminate\Support\Facades\Auth;
use App\Models\Franchise;
use App\Models\FranchiseMerchant;
use App\Models\MerchantLink;
use App\Models\Company;
use App\Models\opos_receiptproduct;
use App\Models\StockReport;
use App\Models\opos_refund;
use App\Models\voucher;
use App\Models\opos_wastageproduct;
use Yajra\DataTables\DataTables;
use \App\Classes\SystemID;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\Merchant;
use App\Models\merchantlocation;
use App\Models\MerchantLinkRelation;
use App\Models\opos_receipt;
use App\Models\location;
use Illuminate\Support\Facades\Input;
use App\Models\terminal;
use App\Models\locationterminal;
use App\Models\OgFuelPrice;
use App\Models\product as Product;
use App\Models\merchantproduct;
use App\Models\FranchiseMerchantLocTerm;
use \App\Http\Controllers\IndustryOilGasController as OilGasCon;
use \App\Http\Controllers\ForecourtController;
use \App\Http\Controllers\OposPetrolStationPumpController;
use Log;
class FranchiseController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function showFranchiseManagement() {

        $user_data = new UserData();

        $merchantId = $user_data->company_id();


        $franchises = Franchise::where('owner_merchant_id', $merchantId)->get();

        return view('franchise.franchise_management', compact( 'franchises'));
    }


    public function showFranchiseList($id){
		try {
			$user_data = new UserData();
			$merchantId = $user_data->company_id();
			
			$franchise_detail = DB::table('franchise')->
				where([
					'id' => $id, 
					"owner_merchant_id" => $merchantId
				])->whereNull('deleted_at')->
				first();

			if (empty($franchise_detail)) {
				throw new \Exception("Franchise not found");
			}

			return view('franchise.franchise_list',compact('id', 'franchise_detail'));
		} catch (\Exception $e) {
			\Log::info([
				"error" => $e->getMessage(),
				"Line"	=> $e->getLine(),
				"File"	=> $e->getFile()
			]);
			abort(404);
		}
    }


    public function showFranchiseTerminalList(){
        $user_data = new UserData();
        $merchantId = $user_data->company_id();
        return view('franchise.franchise_terminal_list');
    }
    
    /**
     * save franchise management data
     * 
     * @param Request $request
     * @return type
     */
    public function saveFranchise(Request $request)
    {
		//Create a new product here
        try {
        $userData = new UserData();
        $SystemID = new SystemID('franchise');
        $merchantId = $userData->company_id();
            
        $franchise = new Franchise();
        $franchise->owner_merchant_id = $merchantId;
        $franchise->systemid = $SystemID;;
        $franchise->name = 'Franchise Name';
        $franchise->save();

		Log::debug('SF saveFranchise(): AFTER save()');

        $thisuserID = $this->getCompanyUserId();

        $franchisee_user_id = MerchantLink::where("initiator_user_id" ,
			$thisuserID)->pluck("responder_user_id");

        foreach ($franchisee_user_id as $item) {
            $company_id = Company::where("owner_user_id" , $item)->
				first()->id;
            $franchise_merchant_id = Merchant::where("company_id",
				$company_id)->first()->id;

            $check = FranchiseMerchant::where("franchise_id" ,
				$franchise->id)->
				where("franchisee_merchant_id" , $franchise_merchant_id)->
				where("deleted_at" , NULL)->count();

            if($check == 0){
                $franchiseMerchant = new FranchiseMerchant;
                $franchiseMerchant->franchise_id = $franchise->id;
                $franchiseMerchant->franchisee_merchant_id = $franchise_merchant_id;
                $franchiseMerchant->status = "inactive";
                $franchiseMerchant->save();
            }
        }
		
		$franchisee_user_id = MerchantLink::where("responder_user_id" , $thisuserID)->pluck("initiator_user_id");
        foreach ($franchisee_user_id as $item) {
            $company_id = Company::where("owner_user_id" , $item)->first()->id;
            $franchise_merchant_id = Merchant::where("company_id" , $company_id)->first()->id;
            $check = FranchiseMerchant::where("franchise_id" , $franchise->id)->where("franchisee_merchant_id" , $franchise_merchant_id)->where("deleted_at" , NULL)->count();
            if($check == 0){
                $franchiseMerchant = new FranchiseMerchant;
                $franchiseMerchant->franchise_id = $franchise->id;
                $franchiseMerchant->franchisee_merchant_id = $franchise_merchant_id;
                $franchiseMerchant->status = "inactive";
                $franchiseMerchant->save();
            }
        }		
		

        return response()->json([
			'msg' => 'Franchise added successfully',
			'status' => 'true'
		]);

        } catch (\Exception $e) {
            return response()->json([
				'msg' => $e->getMessage(),
				'status' => 'false'
			]);
        }
    }

    public function saveFranchiseMerchantLocation(Request $request){
        //Create a new product here
        try {
            $merchantId = $request->merchantId;
            if($request->locationIds){
                $selectedLocationIds = FranchiseMerchantLoc::where('franchisemerchant_id', $merchantId)->whereNotIn('location_id', $request->locationIds)->delete();
                foreach ($request->locationIds as $locationId) {
                    $selectedLocationId = FranchiseMerchantLoc::where('franchisemerchant_id', $merchantId)->where('location_id', $locationId)->count();
                    if (!$selectedLocationId){
                        $franchise = new FranchiseMerchantLoc();
                        $franchise->franchisemerchant_id = $merchantId;
                        $franchise->location_id = $locationId;        
                        $franchise->save();
                    }
                }
            } else {
                FranchiseMerchantLoc::where('franchisemerchant_id', $merchantId)->delete();
            }
            return response()->json([
                'msg' => 'Franchise Merchant Location added successfully',
                'status' => 'true'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'msg' => $e->getMessage(),
                'status' => 'false'
            ]);
        }        
    }

    public function getCompanyUserId()
    {
        $userData = new UserData();
        $companyId = $userData->company_id();
        $company = Company::find($companyId);
        return $company->owner_user_id;
    }

    /***************************/                        
    /*  PRODUCTS OF FRANCHISE  */
    /***************************/

    public function getFranchiseProducts($franchiseid){
	    //Log::debug('franchise id'.json_encode($franchiseid));
	    $franchise_details = DB::table('franchise')->
		    where('id',$franchiseid)->
		    whereNull('deleted_at')->
		    first();
	    
	    if (empty($franchise_details)) {
		    Log::info(["Error" => "Broken record"]);
		    abort(404);
	    }
		return view('franchise.franchise_products', compact('franchiseid','franchise_details'));
    }


    public function getFranchiseMerchants($id, Request $request)
    {
		//dd($id);
        $userId = $this->getCompanyUserId();

        $responderIds = MerchantLink::where('initiator_user_id', $userId)->pluck('responder_user_id')->toArray();
        $initiatorIds = MerchantLink::where('responder_user_id', $userId)->pluck('initiator_user_id')->toArray();

        $merchantUserIds = array_merge($responderIds, $initiatorIds);
	//	dd($merchantUserIds);
        $query = Company::select('company.id as company_id',
            'company.name as company_name',
            'company.business_reg_no as company_business_reg_no',
            'company.systemid as company_system_id',
            'company.owner_user_id',
            'merchant.id as merchant_id'
        )->join('merchant', 'merchant.company_id', '=', 'company.id')
            ->whereIn('company.owner_user_id', $merchantUserIds);

        $merchants = $query->get();
		
	//	 dd($merchants);

        $counter = 0;
        foreach ($merchants as $key => $merchant) {
            $merchants[$key]['indexNumber'] = ++$counter;
            $merchants[$key]['merchant_link_id'] = 0;
            $merchants[$key]['merchant_link_relation_id'] = 0;
            $merchants[$key]['merchant_location'] = 'Location';
            $merchants[$key]['merchant_location_id'] = '';
            $merchants[$key]['franchise_merchant_locations'] = [];
            $merchants[$key]['franchiseMerchantLocTermResult'] = [];
            $merchants[$key]['merchant_royalty'] = '';
            $merchants[$key]['franchise_id'] = '';
            $merchants[$key]['franchisemerchant_id'] = 0;
            $merchants[$key]['franchise_has_transaction'] = '';
            $merchants[$key]['status'] = 'inactive';

            if (!is_null($merchant->merchant_id)) {
                $franchiseMerchantResult = FranchiseMerchant::where('franchisee_merchant_id', $merchant->company_id)
															->where('franchise_id', $id)->first();
                
                if (!empty($franchiseMerchantResult)) {
                    $merchants[$key]['merchant_royalty'] = $franchiseMerchantResult->overall_royalty;
                    $merchants[$key]['status'] = $franchiseMerchantResult->status;
                    $merchants[$key]['franchisemerchant_id'] = $franchiseMerchantResult->id;
                }
            }

            if (!is_null($merchant->merchant_id)) {
				$franchiseMerchantResult = FranchiseMerchant::where('franchisee_merchant_id', $merchant->company_id)
															->where('franchise_id', $id)->first();
				if(!is_null($franchiseMerchantResult)){											
					$franchiseMerchantLocationResult = FranchiseMerchantLoc::where('franchisemerchant_id', $franchiseMerchantResult->id)->get();
					$locationIds = [];
					for ($i=0; $i < count($franchiseMerchantLocationResult); $i++) { 
						$locationIds[] = $franchiseMerchantLocationResult[$i]->location_id;
					}
					$merchants[$key]['franchise_merchant_locations'] = $locationIds;
				}
            }
           

            if (!is_null($merchant->merchant_id)) {
				$franchiseMerchantResult = FranchiseMerchant::where('franchisee_merchant_id', $merchant->company_id)
															->where('franchise_id', $id)->first();
				if(!is_null($franchiseMerchantResult)){	
					$franchiseMerchantLocTermResult = FranchiseMerchantLocTerm::select('franchisemerchantlocterm.id', 
								'location.id as location_id', 'location.systemid as location_systemid', 'opos_terminal.systemid as terminal_systemid')
							->join('franchisemerchantloc', 'franchisemerchantloc.id', '=', 'franchisemerchantlocterm.franchisemerchantloc_id')
							->join('location', 'franchisemerchantloc.location_id', '=', 'location.id')
							->join('opos_terminal', 'opos_terminal.id', '=', 'franchisemerchantlocterm.terminal_id')
							->where('franchisemerchantloc.franchisemerchant_id', $franchiseMerchantResult->id)
							->whereNull('opos_terminal.deleted_at')
							->get();
					//dd($franchiseMerchantLocTermResult);
					$locationIds = [];
					for ($i=0; $i < count($franchiseMerchantLocTermResult); $i++) { 
						$locationIds[] = $franchiseMerchantLocTermResult[$i]->location_id;
					}
					
					//dd($locationIds);
					$merchants[$key]['franchiseMerchantLocTermResult'] = $locationIds;
				}
            }

            if (!is_null($merchant->merchant_id)) {
				$franchiseMerchantResult = FranchiseMerchant::where('franchisee_merchant_id', $merchant->company_id)
															->where('franchise_id', $id)->first();
				if(!is_null($franchiseMerchantResult)){	
					$franchiseHasTransaction = franchisemerchantlocterm::join('franchisemerchantloc', 'franchisemerchantloc.id', '=', 'franchisemerchantlocterm.franchisemerchantloc_id')
							->join('opos_receipt', 'opos_receipt.terminal_id', '=', 'franchisemerchantlocterm.terminal_id')
							->where('franchisemerchantloc.franchisemerchant_id', $merchant->company_id)
							->count();
					
					$merchants[$key]['franchise_has_transaction'] = $franchiseHasTransaction;
				}
            }
			
			if (!is_null($merchant->merchant_id)) {
				$merchantResult = Merchant::where('company_id', $merchant->company_id)->first();
                if (!is_null($merchantResult->supplier_default_location_id)) {
                    $merchantLocation = location::where('id', $merchantResult->supplier_default_location_id)->first();
                    $merchants[$key]['merchant_location'] = $merchantLocation->branch;
                    $merchants[$key]['merchant_location_id'] = $merchantResult->supplier_default_location_id;
                }
            }

            if (in_array($merchant->owner_user_id, $responderIds)) {
                $merchants[$key]['row_type'] = 'twoway';
                $merchants[$key]['responder'] = '0';
                $merchantLink = MerchantLink::where('initiator_user_id', $userId)->where('responder_user_id', $merchant->owner_user_id)->first();
                $merchants[$key]['merchant_link_id'] = $merchantLink->id;

                // initiator
                $merchantLinkRelation = MerchantLinkRelation::where('merchantlink_id', $merchantLink->id)->where('ptype', 'dealer')->first();
                if ($merchantLinkRelation != null) {
                    $merchants[$key]['merchant_link_relation_id'] = $merchantLinkRelation->id;

                    if (!is_null($merchantLinkRelation->default_location_id)) {
                        $location = location::where('id', $merchantLinkRelation->default_location_id)->first();
                        $merchants[$key]['merchant_location'] = $location->branch;
                        $merchants[$key]['merchant_location_id'] = $merchantLinkRelation->default_location_id;
                    }
                }

            }
            if (in_array($merchant->owner_user_id, $initiatorIds)) {
                $merchants[$key]['row_type'] = 'twoway';
                $merchantLink = MerchantLink::where('initiator_user_id', $merchant->owner_user_id)->where('responder_user_id', $userId)->first();
                $merchants[$key]['merchant_link_id'] = $merchantLink->id;
                $merchants[$key]['responder'] = '1';

                // responder
                $merchantLinkRelation = MerchantLinkRelation::where('merchantlink_id', $merchantLink->id)->where('ptype', 'supplier')->first();
                
                if ($merchantLinkRelation != null) {
                    $merchants[$key]['merchant_link_relation_id'] = $merchantLinkRelation->id;

                    if (!is_null($merchantLinkRelation->default_location_id)) {
                        Log::debug('default location es ' . json_encode($merchantLinkRelation->default_location_id));
                        $location = location::where('id', $merchantLinkRelation->default_location_id)->first();
                        Log::debug('location trae' . json_encode($location));
                        $merchants[$key]['merchant_location'] = $location->branch;
                        $merchants[$key]['merchant_location_id'] = $merchantLinkRelation->default_location_id;
                    }

                }

            }
        }

//        $selected_locations = FranchiseMerchantLoc::where('franchisemerchant_id', $merchant->franchisemerchant[0]->id)->pluck('location_id')->toArray();
//        $merchants[$key]['selected_locations'] = implode(',', $selected_locations);
        // $merchants[$key]['selected_locations'] = '';
//        $merchants[$key]['selected_locations_count'] = count($selected_locations);

	//	dd($merchants);
        $response = [
            'data' => $merchants,
            'recordsTotal' => $query->get()->count(),
            'recordsFiltered' => $query->get()->count()
        ];
        return response()->json($response);


    }
    
    /**
     * get franchise management list using ajax
     * @return type
     */
    public function getFranchiseManagementList()
    {

        $this->user_data = new UserData();
        $model = new Franchise();
        $data = $model->where('owner_merchant_id', $this->user_data->company_id())->
			orderBy('created_at', 'asc')->
            latest()->get();
	
		$data->map(function($z) {
		$z->prd_count = DB::table('franchiseproduct')->
			where('franchise_id',$z->id)->
			where('active',1)->
			whereNull('deleted_at')->
			get()->count();
		}); 
	
        return Datatables::of($data)
            ->addIndexColumn()
            ->addColumn('og_franchise_id', function ($franchiseList) {
				return '<p class="os-linkcolor franchiseList" 
					data-field="og_franchise_id" style="cursor: pointer; margin: 0; 
					text-align: center;"><a href="franchise-list/' . $franchiseList->id . 
					'" target="_blank">' . $franchiseList->systemid . '</a></p>';
            })
            ->addColumn('og_franchise_name', function ($franchiseList) {
				return $franchiseList->prd_count > 0 ? (!empty($franchiseList->name) ? 
					$franchiseList->name : 'Franchise Name') :
					'<p class="os-linkcolor" data-field="og_franchise_name" 
					style="cursor: pointer; margin: 0;display:inline-block" 
					onclick="details(' . $franchiseList->systemid . ')">' .
					 (!empty($franchiseList->name) ? $franchiseList->name : 'Franchise Name') . 
					'</p>';
            })
            ->addColumn('og_franchise_royalty', function ($franchiseList) {
				return '<p class="os-linkcolor" data-field="og_franchise_royalty" 
					style="cursor: pointer; margin: 0;display:inline-block"
				onclick="">0.00</p>';
            })
            ->addColumn('og_franchise_product', function ($franchiseList) {
				return '<p class="os-linkcolor franchiseList" 
						data-field="og_franchise_product" style="cursor: pointer; 
						margin: 0; text-align: center;"><a href="franchise-products/' . 
						$franchiseList->id . '" target="_blank">' . $franchiseList->prd_count . 
						'</a></p>';
            })
            ->addColumn('deleted', function ($franchiseList) {

	
		    $franchiseHasTransaction = franchisemerchantlocterm::join('franchisemerchantloc', 
					'franchisemerchantloc.id', '=', 'franchisemerchantlocterm.franchisemerchantloc_id')
                     ->join('opos_receipt', 'opos_receipt.terminal_id', '=', 'franchisemerchantlocterm.terminal_id')
					 ->join('franchisemerchant', 'franchisemerchant.id', '=', 'franchisemerchantloc.franchisemerchant_id')
				 	 ->join('staff','staff.user_id','=','opos_receipt.staff_user_id')
					 ->where(['franchisemerchant.franchise_id' => $franchiseList->id])
				 	 ->where([['staff.company_id','!=',$this->user_data->company_id()]])
					 ->count();
                
                if($franchiseHasTransaction != 0){

					return '<div><img class="" src="/images/redcrab_50x50.png"
						style="width:25px;height:25px;cursor:not-allowed;
						filter:grayscale(100%) brightness(200%)"/>
						</div>';

			     } else {

					return '<div data-field="deleted"
						onclick="removeFranchiseManagementModel('.
						$franchiseList->id.')" class="remove">
						<img class="" src="/images/redcrab_50x50.png"
						style="width:25px;height:25px;cursor:pointer"/>
						</div>';

					/*
                    return '<p data-field="deleted"
                    onclick="removeFranchiseManagementModel('.$franchiseList->id.')"
                    style="background-color:red;
                    border-radius:5px;margin:auto;
                    width:25px;height:25px;
                    display:block;cursor: pointer;"
                    class="text-danger remove">
                    <i class="fas fa-times text-white"
                    style="color:white;opacity:1.0;
                    margin-left:7px;padding-top:4px;
                    -webkit-text-stroke: 1px red;"></i></p>';
					*/
                }
			
			})->
		escapeColumns([])->
		make(true);
	}

	public function get_product(Request $request) {
		try {
			$user_data = new UserData();
			
			$oilGasController = new OilGasCon();

			$validation = Validator::make($request->all(), [
				'franchiseid'	=> 	'required'	
			]);

			if ($validation->fails()) {
				throw new \Exception("validation_error", 19);
			}

			$franchiseid = $request->franchiseid;

			$merchantproduct = merchantproduct::
				where('merchant_id', $user_data->company_id())->
				pluck('product_id');
			
			$product = Product::whereIn('id',$merchantproduct)->
				whereIn('ptype',['inventory','services','voucher','oilgas'])->
				whereNotNull(['name','photo_1','prdcategory_id','prdsubcategory_id'])->
				whereNull('deleted_at')->
				get();
			
			$product->map(function ($z) use ($franchiseid, $oilGasController) {
				$franchiseproduct  = DB::table('franchiseproduct')->
					where(['product_id' => $z->id, 'franchise_id' => $franchiseid])->
					first();

				$z->min_price 	 = $franchiseproduct->lower_price ?? 0;
				$z->max_price	 = $franchiseproduct->upper_price ?? 0;
				$z->retail_price = $franchiseproduct->recommended_price ?? 0;
				$z->is_active	 = $franchiseproduct->active ?? false;

				if($z->ptype == 'services') {
					$z->retail_price = DB::table('prd_restaurant')->
						where('product_id',$z->id)->
						first()->price ?? 0;
				} else if ($z->ptype == 'voucher') {	
					$z->retail_price = DB::table('prd_voucher')->
						where('product_id',$z->id)->
						first()->price ?? 0;
				} else if ($z->ptype == 'oilgas') {	
					$ogFuel = DB::table('prd_ogfuel')->
						where('product_id',$z->id)->
						first();
			//		dd($ogFuel);
					$z->retail_price = $oilGasController->get_execute_price($ogFuel->id);
				}
			});

			return DataTables::of($product)->
				
				addIndexColumn()->
			
				addColumn('product_systemid', function ($data) {
					return $data->systemid;
				})->

				addColumn('product_name', function ($data) {
					$img_src = '/images/product/' .
					$data->id . '/thumb/' .
					$data->thumbnail_1;
				
					$img = "<img src='$img_src' data-field='inven_pro_name' style=' width: 25px;
					height: 25px;display: inline-block;margin-right: 8px;object-fit:contain;'>";

					return $img.$data->name;
				})->

				addColumn('product_type', function ($data) {
					$data->ptype  = $data->ptype == 'services' ? 'Restaurant & Services':$data->ptype;
					
					if ($data->ptype == 'services') {
						$data->ptype = 'Restaurant & Services';
					} else if ($data->ptype == 'oilgas') {
						$data->ptype = 'Oil & Gas';
					}
					return ucfirst($data->ptype);
				})->
				
				addColumn('product_cost', function ($data) {
					$price = number_format($data->retail_price / 100,2);
					
					if ($data->ptype == 'inventory' && !$data->is_active ) {
						$fn = "inventory_modal('0.00')";	
					} else {
						return $price;
					}
					return <<< EOD
						<span data-field='product_cost' class='os-linkcolor' 
							style='cursor:pointer'
							onclick="$fn">$price</span>
					EOD;
				})->

				addColumn('active', function ($data) {
					$active = $data->is_active ? 'active_button_activated':'';
					$htmlTemplate = <<< EOD
						<button  
							class="prawn btn trigger_save_1 active_button $active" 
							onclick="activate_product($data->id,this)"
							merchant-id="$data->merchant_id"
							style="min-width:75px">Active
						</button>
						EOD;


					return $htmlTemplate;
				})->
				escapeColumns([])->
				make(true);

		} catch (\Exception $e) {
			\Log::error([
				"Error " => $e->getMessage(),
				"Line " =>	$e->getLine(),
				"File "	=> $e->getFile(),
			]);
			abort(404);			
		}
	}

	public function set_product_price(Request $request) {
		try {
			$user_data = new UserData();	

			$validation = Validator::make($request->all(), [
				'franchiseid' => 'required',
				'product_id'  => 'required'
			]);
			
			if ($validation->fails()) {
				throw new \Exception("validation_error", 19);
			}


			$franchise_id =  $request->franchiseid;
			$is_exist_franchise = Franchise::find($franchise_id);		
			
			if (empty($is_exist_franchise)) {
				throw new \Exception("Franchise record not found");
			}

			$product_id = $request->product_id;
			$product = product::find($product_id);

			$merchant_produdct = merchantproduct::where([
				"product_id" => $product_id,
				"merchant_id" => $user_data->company_id()
			])->first();

			if (empty($merchant_produdct)) {
			//	throw new \Exception("Invalid/Forbidden Product");
			}

			if (($request->min_price > $request->max_price || 
				$request->min_price > $request->retail_price) ||
				$request->retail_price > $request->max_price ) {

				$msg = "Invalid price range";
				return view('layouts.dialog',compact('msg'));

			}

			$is_exist_franchiseproduct = DB::table('franchiseproduct')->
				where([
					'product_id' => $product_id, 
					"franchise_id" => $franchise_id
				])->
				first();
			$check_if_added = 0;
			if (empty($is_exist_franchiseproduct)) {
			
				$is_exist_franchiseproduct = DB::table('franchiseproduct')->
					insertGetId([
						'product_id' 	=> $product_id,
						"franchise_id"	=> $franchise_id,
						'created_at'	=> date('Y-m-d h:i:s'),
						'updated_at'	=> date('Y-m-d h:i:s')
					]);
					$check_if_added = 1;
			} else {
				$is_exist_franchiseproduct = $is_exist_franchiseproduct->id;	
			}

			$update = [
				'lower_price'			=> $request->min_price ?? 0,
				'upper_price' 			=> $request->max_price ?? 0,
				'recommended_price' 	=> $request->retail_price ?? 0,
				'updated_at'	=> date('Y-m-d h:i:s')
			];

			DB::table('franchiseproduct')->
				where('id', $is_exist_franchiseproduct)->
				whereNull('deleted_at')->
				update($update);
				$franchiseSync = new FranchiseeOceaniaSync();
                
			if($check_if_added){
				$franchiseSync->syncProductToOceania($product->systemid);
				//$this->syncProductToOceania($product->systemid);
			}
			
			$msg = "Price updated";
			return view('layouts.dialog',compact('msg'));

		} catch (\Exception $e) {
			\Log::error([
				"Error " => $e->getMessage(),
				"Line " =>	$e->getLine(),
				"File "	=> $e->getFile(),
			]);
			abort(404);			
		}
	}


	public function toggle_product_active(Request $request) {	
		try {
	
			$user_data = new UserData();	

			$validation = Validator::make($request->all(), [
				'franchiseid' => 'required',
				'product_id'  => 'required'
			]);
			
			if ($validation->fails()) {
				throw new \Exception("validation_error", 19);
			}


			$franchise_id =  $request->franchiseid;
			$is_exist_franchise = Franchise::find($franchise_id);		
			
			if (empty($is_exist_franchise)) {
				throw new \Exception("Franchise record not found");
			}

			$product_id = $request->product_id;
			
			$merchant_produdct = merchantproduct::where([
				"product_id" => $product_id,
				"merchant_id" => $user_data->company_id()
			])->first();
			$product = product::find($product_id);
			if (empty($merchant_produdct) || empty($product)) {
				throw new \Exception("Invalid/Forbidden Product");
			}

			$is_exist_franchiseproduct = DB::table('franchiseproduct')->
				where([
					'product_id' => $product_id, 
					"franchise_id" => $franchise_id
				])->
				first();
				$product_details = product::where('id', $product_id)->first();

				$production = DB::table('og_fuelprice')
				->join('prd_ogfuel', 'prd_ogfuel.id', '=', 'og_fuelprice.ogfuel_id' )
				->join('product', 'product.id','=' ,'prd_ogfuel.product_id')
				->where('product.systemid', $product_details->systemid)
				->orderBy('start', 'DESC')
				->select('og_fuelprice.*')->first();
				Log::debug('Production: '.json_encode($production));
				$ipLocations = null;

			if (empty($is_exist_franchiseproduct) && $product->ptype == 'inventory' ) {
				$msg = "Please add price first";
			} else {

				if (empty($is_exist_franchiseproduct)) {
					$is_exist_franchiseproduct = DB::table('franchiseproduct')->
					insertGetId([
						'product_id' 	=> $product_id,
						"franchise_id"	=> $franchise_id,
						'active'		=> 1,
						'created_at'	=> date('Y-m-d h:i:s'),
						'updated_at'	=> date('Y-m-d h:i:s')
					]);
					$active_state = 0;
				/*	
					$ipLocations = 	DB::table('product')
						->leftjoin('franchiseproduct', 'franchiseproduct.product_id', '=',  'product.id')
						->leftjoin('franchisemerchant', 'franchisemerchant.franchise_id', '=',  'franchiseproduct.franchise_id')
						->leftjoin('locationipaddr', 'locationipaddr.company_id', '=',  'franchisemerchant.franchisee_merchant_id')
						->leftjoin('location', 'locationipaddr.location_id', '=',  'location.id')
						->leftjoin('franchisemerchantloc', 'franchisemerchantloc.location_id', '=',  'location.id')
						->leftjoin('franchisemerchantloc as fml', 'franchisemerchant.id', '=',  'fml.franchisemerchant_id')
						->where('product.systemid', $product_details->systemid)
						->select('locationipaddr.ipaddr','locationipaddr.location_id', 'location.branch', 'locationipaddr.company_id')
						->groupBy('location.id')
						->get();
					
	
					$endpoint = '/interface/update/product';
					$call = new APIFranchiseController($endpoint);

					Log::debug('IPLocation '.json_encode($ipLocations));
					Log::debug('Product: '.json_encode($production));
					Log::debug('Active State: '.$active_state);
					if(!empty($product_details->ptype) && !empty($product_details->prdcategory_id) && !empty($product_details->photo_1)&& !empty($product_details->prdsubcategory_id)&& !empty($product_details->brand_id))
					{
						if (!empty($ipLocations)) {
							foreach($ipLocations as $location){
								//Make a call
								$payload = array(
									'product_id'=>$product_details->id,
									'prdcategory_id'=>$product_details->prdcategory_id,
									'prdsubcategory_id'=>$product_details->prdsubcategory_id,
									'ptype'=>$product_details->ptype,
									'location_id'=>$location->location_id,
									'brand_id'=>$product_details->brand_id,
									'company_id'=>$location->company_id,
									'photo_1'=>env('APP_URL').'/images/product/'.
										$product_details->id.'/'.$product_details->photo_1,
									'username'=> $request->user()->name,
									'price'=>$production->price??0,
									'product_name'=>$product_details->name,
									'product_systemid'=>$product_details->systemid,
									'userid'=>$request->user()->id,
									'time'=>date('Y-m-d H:i:s')
								);
								$payload = json_encode($payload);
								$response = $call->sendToOceania(
									$location->ipaddr, $payload);
	
								}
						}
					}
					
				 */

				} else {
					$is_exist_franchiseproduct_id = $is_exist_franchiseproduct->id;
					$active_state = $is_exist_franchiseproduct->active;

					DB::table('franchiseproduct')->
						where('id', $is_exist_franchiseproduct_id)->
						update([
							'active' => !$active_state,
							'updated_at'	=> date('Y-m-d h:i:s')
						]);
					/*
					if(!$active_state == 1){
						$endpoint = '/interface/update/product';
					$call = new APIFranchiseController($endpoint);
						$env = env('APP_URL');
					Log::debug('IPLocation '.json_encode($ipLocations));
					Log::debug('Product: '.json_encode($product));
					Log::debug('Active State: '.$active_state);
					Log::debug('APP URL: '.$env);


					$ipLocations = 	DB::table('product')
						->leftjoin('franchiseproduct', 'franchiseproduct.product_id', '=',  'product.id')
						->leftjoin('franchisemerchant', 'franchisemerchant.franchise_id', '=',  'franchiseproduct.franchise_id')
						->leftjoin('locationipaddr', 'locationipaddr.company_id', '=',  'franchisemerchant.franchisee_merchant_id')
						->leftjoin('location', 'locationipaddr.location_id', '=',  'location.id')
						->leftjoin('franchisemerchantloc', 'franchisemerchantloc.location_id', '=',  'location.id')
						->leftjoin('franchisemerchantloc as fml', 'franchisemerchant.id', '=',  'fml.franchisemerchant_id')
						->where('product.systemid', $product_details->systemid)
						->select('locationipaddr.ipaddr','locationipaddr.location_id', 'location.branch', 'locationipaddr.company_id')
						->groupBy('location.id')
						->get();
						if(!empty($product_details->ptype) && !empty($product_details->prdcategory_id) && !empty($product_details->photo_1)&& !empty($product_details->prdsubcategory_id)&& !empty($product_details->brand_id))
						{
							if (!empty($ipLocations)) {
								foreach($ipLocations as $location){
									//Make a call
									$payload = array(
										'product_id'=>$product_details->id,
										'prdcategory_id'=>$product_details->prdcategory_id,
										'prdsubcategory_id'=>$product_details->prdsubcategory_id,
										'ptype'=>$product_details->ptype,
										'location_id'=>$location->location_id,
										'brand_id'=>$product_details->brand_id,
										'company_id'=>$location->company_id,
										'photo_1'=>$env.'/images/product/'.
											$product_details->id.'/'.$product_details->photo_1,
										'username'=> $request->user()->name,
										'price'=>$production->price??0,
										'product_name'=>$product_details->name,
										'product_systemid'=>$product_details->systemid,
										'userid'=>$request->user()->id,
										'time'=>date('Y-m-d H:i:s')
									);
								$payload = json_encode($payload);

								Log::debug('Payload: '.$payload);
									$response = $call->sendToOceania(
										$location->ipaddr, $payload);

								Log::debug('response: '.$response);
									}
								}
							}
					}*/
				}
				$franchiseSync = new FranchiseeOceaniaSync();
				$franchiseSync->syncProductToOceania($product->systemid);
				$msg = "Product ".(!$active_state ? "activated":"deactivated");
			}	

			return view('layouts.dialog',compact('msg'));
		} catch (\Exception $e) {
			\Log::error([
				"Error " => $e->getMessage(),
				"Line " =>	$e->getLine(),
				"File "	=> $e->getFile(),
			]);
			abort(404);
		}
	
	}


	public function syncProductToOceania($systemid) {
		Log::debug('***** syncProductToOceania() *****');

		try {
			$user_data = new UserData();	
			$location_data = DB::table('locationipaddr')->
				where('company_id', $user_data->company_id())->
				select('ipaddr')->
				get()->unique();
	
			Log::debug('syncProductToOceania: location_data='.
				json_encode($location_data));

			$locationPrice = DB::table('franchiseproduct')->
				join('franchisemerchant','franchisemerchant.franchise_id','franchiseproduct.franchise_id')->
				join('product','product.id','franchiseproduct.product_id')->
				where([
					'product.systemid' => $systemid
				])->
				select("franchiseproduct.*","product.systemid")->
				get();


			Log::debug('syncProductToOceania: locationPrice='.
				json_encode($locationPrice));

			if ($locationPrice->isEmpty())
				return;
		
			$api = new APIFcController();

			$new_request = [
				'locationPrice'	=> json_encode($locationPrice)
			];

			Log::debug('syncProductToOceania: new_request='.
				json_encode($new_request));

			foreach ($location_data as $l) {
				if (!empty($l->ipaddr)) {
					$api->init_send_data_via_api($l->ipaddr , $new_request);
				}
			}

		} catch (Exception $e) {
			Log::error([
				"Error"	=> 	$e->getMessage(),
				"File"	=>	$e->getFile(),
				"Line"	=>	$e->getLine()
			]);
		}
	}

		/**
         * 
         * 
         * @param Request $request
         * @return string
         * @throws \Exception
         */
        function getFranchiseManagementDetail(Request $request){
            try {
                $validation = Validator::make($request->all(), [
                    'franchise_id' => 'required',
                ]);

                if ($validation->fails()) {
                    throw new \Exception("validation_error", 19);
                }

                $franchise_details = Franchise::where('systemid',
                    $request->franchise_id)->first();

            if (!$franchise_details) {
                throw new \Exception('franchise_not_found', 25);
            }
            return  response()->json(['name' =>$franchise_details->name, 'status' => 'true']);

            
        } catch (\Exception $e) {
//            return $e->getMessage();
            if ($e->getMessage() == 'validation_error') {
                return '';

            } else if ($e->getMessage() == 'product_not_found') {
                return response()->json([
					'message' =>"Error occured while opening dialog, invalid product selected",
					'status' => 'false']);

            } else {
                return response()->json([
					'message' =>$e->getMessage(),
					'status' => 'false']);
            }
        }
    }
    
    
    
    /**
         * 
         * 
         * @param Request $request
         * @return string
         * @throws \Exception
         */
        function updateFranchiseManagementDetail(Request $request){
            
            try {

            $allInputs = $request->all();
            $systemid       = $request->get('systemid');
            $changed = false;

            $validation = Validator::make($allInputs, [
                'systemid'         => 'required',
            ]);

            if ($validation->fails()) {
                throw new Exception("franchise_not_found", 1);
            }

             $franchise = Franchise::where('systemid', $systemid)->first();

             if (!$franchise) {
                throw new Exception("franchise_not_found", 1);
            }

            if ($request->has('franchise_name')) {
				$franchise->name = ($request->franchise_name);
				$changed = true;
				$msg = "Franchise Name updated";
            }

            if ($changed == true) {
                $franchise->save();
                $response = response()->json(['msg' => $msg,
					'status' => 'true']);
            } else {
                $response = response()->json([
					'msg' =>'Franchise Name not found', 'status' => 'false']);
            }

        }  catch (\Exception $e) {
            if ($e->getMessage() == 'product_not_found') {
                $msg = "Product not found";
            } else if ($e->getMessage() == 'invalid_cost') {
                $msg = "Invalid cost";
            } else {
                $msg = $e->getMessage();
            }
            $response = response()->json(['msg' =>$msg, 'status' => 'false']);
        }

        return $response;
    }
    
    
    public function getfranchiseManagmentModel(Request $request)
    {
        
        try {
            $allInputs = $request->all();
            $id        = $request->get('id');
            $fieldName = $request->get('field_name');
            $franchise = Franchise::where('id', $id)->first();
            return view('franchise.franchise-management-modals', compact(['id', 'fieldName', 'franchise']));
            

        } catch (\Illuminate\Database\QueryException $ex) {
            $response = (new ApiMessageController())->queryexception($ex);
        }
    }

    public function getfranchiseTerminalModel(Request $request)
    {
        
        try {
            $allInputs = $request->all();
            $id        = $request->get('id');
            $fieldName = $request->get('field_name');
            $franchise = terminal::where('systemid', $id)->first();
            return view('franchise.franchise_terminal_modals', compact(['id', 'fieldName', 'franchise']));
            

        } catch (\Illuminate\Database\QueryException $ex) {
            $response = (new ApiMessageController())->queryexception($ex);
        }
    }
    

    public function destoryFranchiseManagement($id)
    {
        try {
            $this->user_data = new UserData();
            $franchise          = Franchise::find($id);
            $franchiseCompanyID = $franchise->owner_merchant_id; 
            $franchiseOwnerID = Company::where("id" , $franchiseCompanyID)->first()->owner_user_id;
            $franchise->delete();

            $franchisee_user_id = MerchantLink::where("initiator_user_id" , $franchiseOwnerID)->pluck("responder_user_id");

            foreach ($franchisee_user_id as $item) {
                $company_id = Company::where("owner_user_id" , $item)->first()->id;
                $franchise_merchant_id = Merchant::where("company_id" , $company_id)->first()->id;
                $check = FranchiseMerchant::where("franchise_id" , $id)->where("franchisee_merchant_id" , $franchise_merchant_id)->whereNull("deleted_at");

                $check->update(['deleted_at' => $franchise->deleted_at]);
            }


            $msg = "Franchise deleted successfully";
            return view('layouts.dialog', compact('msg'));

        } catch (\Illuminate\Database\QueryException $ex) {
            //$msg = "Some error occured";
            $msg = $ex;

            return view('layouts.dialog', compact('msg'));
        }
    }

    public function destoryFranchiseTerminal($id)
    {
        try {
            $this->user_data = new UserData();
            $franchise_terminal         = terminal::find($id);
            if($franchise_terminal){
                $franchise_terminal->delete();
            }

            $location_terminal          = locationterminal::where('terminal_id', $id)->first();
            if($location_terminal){
                $location_terminal->delete();
            }

            $franchisemerchantlocterm   = FranchiseMerchantLocTerm::where('terminal_id', $id)->first();
            if($franchisemerchantlocterm){
                $franchisemerchantlocterm->delete();
            }

            $msg = "Franchise deleted successfully";
            return view('layouts.dialog', compact('msg'));

        } catch (\Illuminate\Database\QueryException $ex) {
            //$msg = "Some error occured";
            $msg = $ex;

            return view('layouts.dialog', compact('msg'));
        }
    }
    
    /**
     * Getting marchant location
     * @return \Illuminate\Http\JsonResponse
     */
    public function getLocations()
    {
        $this->user_data = new UserData();

        $ids = merchantlocation::where('merchant_id',
			$this->user_data->
			company_id())->
			pluck('location_id');

        $location = location::where([['branch', '!=', 'null']])->
			whereIn('id', $ids)->
			where('warehouse', 0)->
		//	where('foodcourt', 0)->
			latest()->
			get();

        $response = [
            'data' => $location,
            'recordsTotal' => location::whereIn('id', $ids)->
				latest()->count(),
            'recordsFiltered' => location::whereIn('id', $ids)->
				latest()->count()
        ];
        return response()->json($response);
    }
    
    /**
       * @param Request $request
       * @return string
       * @throws \Exception
       */
	   
    function updateFranchiseMerchant(Request $request){

		try {
			$validation = Validator::make($request->all(), [
				'franchisId' => 'required',
			]);

			if ($validation->fails()) {
				throw new \Exception("validation_error", 19);
			}
			
			$franchise_details = FranchiseMerchant::where('franchise_id',
				$request->franchisId)->where('franchisee_merchant_id',
				$request->merchantId)->first();
			
			if (empty($franchise_details)) {
				$franchise_details = new FranchiseMerchant();
				$franchise_details->franchise_id = $request->franchisId;
				$franchise_details->franchisee_merchant_id = $request->merchantId;
				$franchise_details->overall_royalty = 0;
				$franchise_details->save();
			}
//dd($franchise_details, $request->franchisId,$request->merchantId);
				if(!is_null($request->royalty)){
					FranchiseMerchant::where('franchise_id', $request->franchisId)->where('franchisee_merchant_id',
					$request->merchantId)->update(array(
						'overall_royalty'=> $request->royalty
						));
				}
				
				if(!is_null($request->status)){
					FranchiseMerchant::where('franchise_id', $request->franchisId)->where('franchisee_merchant_id',
					$request->merchantId)->update(array(
						'status'=> $request->status
						));
				}		
			
			return  response()->json([
				'royalty' => $franchise_details->overall_royalty,
				'status' => 'true'
			]);

		} catch (\Exception $e) {
			if ($e->getMessage() == 'validation_error') {
				return '';

			} else if ($e->getMessage() == 'franchise_not_found') {
				return  response()->json([
					'message' =>"Error occured while opening dialog, Invalid franchise selected",
					'status' => 'false'
				]);

			} else {
				return  response()->json([
					'message' =>$e->getMessage(),
					'Line No' => $e->getLine(),
					'status' => 'false'
				]);
			}
		}
	}	   
	   
    function getFranchiseRoyalty(Request $request){

		try {
			$validation = Validator::make($request->all(), [
				'franchisId' => 'required',
			]);

			if ($validation->fails()) {
				throw new \Exception("validation_error", 19);
			}

			$franchise_details = FranchiseMerchant::where('franchise_id',
				$request->franchisId)->where('franchisee_merchant_id',
				$request->merchantId)->first();

			if (!$franchise_details) {
				throw new \Exception('franchise_not_found', 25);
			}

			return  response()->json([
				'royalty' =>$franchise_details->overall_royalty,
				'status' => 'true'
			]);

		} catch (\Exception $e) {
			if ($e->getMessage() == 'validation_error') {
				return '';

			} else if ($e->getMessage() == 'franchise_not_found') {
				return  response()->json([
					'message' =>"Error occured while opening dialog, Invalid franchise selected",
					'status' => 'false'
				]);

			} else {
				return  response()->json([
					'message' =>$e->getMessage(),
					'status' => 'false'
				]);
			}
		}
	}


    public function saveMerchantLocation(Request $request) {
//        Franchise
        $model = new FranchiseMerchantLoc();
        $model->location_id = $request->get('location_id');
        $model->franchisemerchant_id = $request->get('franchisemerchant_id');
        $model->save();
    }


    public function saveFranchiseeTerminalList(Request $request) {
        try {
        $merchantId = $request->input('merchantId');
        $locationId = $request->input('locationId');

        $systemid = new SystemID('terminal');
        $terminal = new terminal();
        $franchisemerchantlocterm = new FranchiseMerchantLocTerm();

        $terminal->systemid = $systemid;
        $terminal->btype_id = 1;
        $terminal->save();

        $sq_name = 'receipt_seq_' . sprintf("%06d",$terminal->id);
        \DB::select(\DB::raw("create sequence $sq_name nocache nocycle"));        
        $franchisemerchantlocId = franchisemerchantloc::
			where('franchisemerchant_id', $merchantId)->
			where('location_id', $locationId)->
			value('id');

        $franchisemerchantlocterm->franchisemerchantloc_id =
			$franchisemerchantlocId;

        $franchisemerchantlocterm->terminal_id = $terminal->id;
        $franchisemerchantlocterm->save();
	
	DB::table('opos_locationterminal')->
		insert([
			"location_id"	=> $locationId,
			"terminal_id" 	=> $terminal->id,
			"created_at"	=> date("Y-m-d H:i:s"),
			"updated_at"	=> date("Y-m-d H:i:s")
		]);

        return response()->json([
            'msg' => 'Franchisee merchant location terminal added successfully',
            'status' => 'true'
        ]);

        } catch (\Exception $e) {
            return response()->json([
                'msg' => $e->getMessage(),
                'status' => 'false'
            ]);
        }
    }


    public function getFranchiseeTerminalList() {
        $merchantID = Input::get('merchant_id');
        $terminals = FranchiseMerchantLocTerm::select('franchisemerchantlocterm.id', 'location.branch', 'location.systemid as location_systemid', 'opos_terminal.systemid as terminal_systemid')
			->join('franchisemerchantloc', 'franchisemerchantloc.id', '=', 'franchisemerchantlocterm.franchisemerchantloc_id')
			->join('location', 'franchisemerchantloc.location_id', '=', 'location.id')
			->join('opos_terminal', 'opos_terminal.id', '=', 'franchisemerchantlocterm.terminal_id')
			->where('franchisemerchantloc.franchisemerchant_id', $merchantID)
            ->whereNull('opos_terminal.deleted_at')
			->orderBy('opos_terminal.systemid', 'desc')
			->get();

        $counter = 0;
        foreach ($terminals as $key => $terminal) {
            $terminals[$key]['indexNumber'] = ++$counter;
        }
        $response = [
            'data' => $terminals,
            'recordsTotal' => $terminals->count(),
            'recordsFiltered' => $terminals->count()
        ];
        return response()->json($response);
    }

    public function getTerminalLocations() {

//        Franchise
        $ids = franchisemerchantloc::where('franchisemerchant_id', Input::get('merchant_id'))->pluck('location_id');
        $location = location::where([['branch', '!=', 'null']])->whereIn('id', $ids)->latest()->get();
        $response = [
            'data' => $location
        ];
        return response()->json($response);
    }

    public function removeMerchantLocation(Request $request) {
//        Franchise
        $model = new FranchiseMerchantLoc();
        $model->where('location_id', $request->get('location_id'))->where('franchisemerchant_id', $request->get('franchisemerchant_id'))->delete();
    }


	public function location_price($terminal_id) {
		$user_data = new UserData();
		
		$franchise = DB::table('franchise')->
			join('franchisemerchant','franchisemerchant.franchise_id','=','franchise.id')->
			join('franchisemerchantloc','franchisemerchantloc.franchisemerchant_id','=','franchisemerchant.id')->
			join('franchisemerchantlocterm','franchisemerchantlocterm.franchisemerchantloc_id','=','franchisemerchantloc.id')->
			where([
				"franchisemerchantlocterm.terminal_id"	=>	$terminal_id,
				"franchisemerchant.franchisee_merchant_id" => $user_data->company_id()
			])->
			select("franchise.*", "franchisemerchantloc.location_id")->
			first();
		
		$location = DB::table('location')->
			where('id',$franchise->location_id)->
			first();
		
		$approvedAt = DB::table('company')->
			where('id', $user_data->company_id())->
			first();

		$approvedAt = $approvedAt->approved_at ?? $approvedAt->created_at;

		$is_active_all = DB::table('locationproductprice')->
			where([
				"franchisee_merchant_id"	=>	$user_data->company_id(),
				"location_id"				=>	$location->id,
				"active"					=>  1,
			])->get();
	
		$is_deactive_all = DB::table('locationproductprice')->
			where([
				"franchisee_merchant_id"	=>	$user_data->company_id(),
				"location_id"				=>	$location->id,
				"active"					=>  0,
			])->get();

		$data = DB::table('franchiseproduct')->
				leftjoin('franchisemerchant',
					'franchisemerchant.franchise_id','=','franchiseproduct.franchise_id')->
				join('product','franchiseproduct.product_id', '=',
					'product.id')->
				join('franchisemerchantloc','franchisemerchantloc.franchisemerchant_id','=',
					'franchisemerchant.id')->
				where([
					'franchisemerchant.franchisee_merchant_id' => $user_data->company_id(),
					'franchiseproduct.active' => 1,
					'franchiseproduct.franchise_id' => $franchise->id,
					'franchisemerchantloc.location_id' => $location->id
				])->
				whereNull('franchiseproduct.deleted_at')->
				select('product.systemid','product.id as product_id', 'product.name', 'product.thumbnail_1',
						'franchiseproduct.upper_price','franchiseproduct.lower_price',
							'franchiseproduct.id as f_pid','franchisemerchantloc.location_id')->
				get()->count();

		$is_all_active = 0;
		if ($is_active_all->count() == $data) {
			$is_all_active = 1;
		}

		if ($is_deactive_all->count() == $data) {
			$is_all_active = 0;
		}

		$og_controller = DB::table('og_controller')->
			where('location_id', $location->id)->
			where('company_id', $user_data->company_id())->
			select('ipaddress', 'public_ipaddress')->get()->toArray();

		return view('franchise.franchise_screen_d',
			compact('franchise', 'location','approvedAt', 'is_all_active', 'og_controller'));
	}


	public function locationPriceTable(Request $request) {
		try {
			$user_data	 = new UserData();
			$franchiseid = $request->franchiseid;
			$locationid  = $request->locationid;
			$date		 = $request->date;
			
			$data = DB::table('franchiseproduct')->
				leftjoin('franchisemerchant',
					'franchisemerchant.franchise_id','=','franchiseproduct.franchise_id')->
				join('product','franchiseproduct.product_id', '=',
					'product.id')->
				join('franchisemerchantloc','franchisemerchantloc.franchisemerchant_id','=',
					'franchisemerchant.id')->
				where([
					'franchisemerchant.franchisee_merchant_id' => $user_data->company_id(),
					'franchiseproduct.active' => 1,
					'franchiseproduct.franchise_id' => $franchiseid,
					'franchisemerchantloc.location_id' => $locationid
				])->
				whereNull('franchiseproduct.deleted_at')->
				where([["product.ptype",'!=','oilgas']])->
				select('product.systemid','product.id as product_id', 'product.name', 'product.thumbnail_1',
						'franchiseproduct.upper_price','franchiseproduct.lower_price',
							'franchiseproduct.id as f_pid','franchisemerchantloc.location_id')->
				get();
			
				$data->map(function($z) use ($user_data) {
				$locationproductprice_data = DB::table('locationproductprice')->
					where([
						"franchiseproduct_id"	=> $z->f_pid,
						"franchisee_merchant_id"=> $user_data->company_id(),
						"location_id"			=> $z->location_id
					])->first();

					$z->price = $locationproductprice_data->price ?? null;
					$z->active = $locationproductprice_data->active ?? null;
					$z->stock_level = $this->locationPrice_stockLevel(
						$z->product_id, $z->location_id);
					$z->value = $z->stock_level * $z->price;
				});

        	return Datatables::of($data)->
				addIndexColumn()->
				addColumn('product_systemid', function ($data) {
					return $data->systemid ?? 0;
				})->
				addColumn('product_name', function ($data) {
					if (!empty($data->thumbnail_1)) {
						$img_src = '/images/product/' .
						$data->product_id . '/thumb/' .
						$data->thumbnail_1;
						$img = "<img src='$img_src' data-field='inven_pro_name' style=' width: 25px;
						height: 25px;display: inline-block;margin-right: 8px;object-fit:contain;'>";

					}
					return ($img ?? ''). $data->name ?? "Product Name";
				})->
				addColumn('product_upper', function ($data) {
					return number_format($data->upper_price/100,2) ?? "0.00";
				})->
				addColumn('product_price', function ($data) {
					$price = number_format($data->price/100, 2) ?? "0.00";
					$price_inp = $data->price ?? "";
	
					$ptype = DB::table('product')->
						where('id', $data->product_id)->
						select('ptype')->first()->ptype;
				
					$validation = $ptype != 'inventory' ?  'bypass':'strict';	
					
					return <<<EOD
					<span class="os-linkcolor" style="cursor:pointer" 
						onclick="updatePrice('$price_inp','$data->f_pid',
						'$data->lower_price', '$data->upper_price','$validation' )">$price</span>
EOD;
				})->
				addColumn('product_lower', function ($data) {
					return number_format($data->lower_price/100,2) ?? "0.00";
				})->
				addColumn('product_loyalty', function ($data) {
					return $data->loyalty ?? 0;
				})->
				addColumn('product_stock', function ($data) {
					return number_format($data->stock_level,2);
				})->
				addColumn('product_value', function ($data) {
					return number_format($data->value/100,2) ?? "0.00";
				})->
				addColumn('active', function($data) {
					$active  = 	$data->active == 1 ? "active_button_activated":'';
					return <<<EOD
				<button  
					class="prawn btn active_button $active" 
					onclick="activate_func($data->f_pid)"
					style="min-width:75px">Active
				</button>

EOD;
			})->
			escapeColumns([])->
			make(true);
		} catch (\Exception $e) {
			\Log::info([
				"Error"	=> $e->getMessage(),
				"File"	=> $e->getFile(),
				"Line"	=> $e->getLine()
			]);
			abort(404);
		}
	}


	public function locationPriceUpdate(Request $request) {
		try {
			$user_data = new UserData();	
			$validation = Validator::make($request->all(), [
				"field" 		=> "required",
				"data"			=> "required",
				"location_id"	=> "required",
				"f_id"			=> "required"
			]);

			if ($validation->fails()) {
				new \Exception("Invalid data");
			}

			$product_details = DB::table('product')->
				join('franchiseproduct','franchiseproduct.product_id',
					'=','product.id')->
				where('franchiseproduct.id',$request->f_id)->
				select('product.*')->
				first();

			$is_exist = DB::table('locationproductprice')->
				where([
					"location_id"		 		=> $request->location_id,
					"franchiseproduct_id"		=> $request->f_id,
					"franchisee_merchant_id"	=> $user_data->company_id()
				])->first();


			$update_array = [];

			Log::debug('locationPriceUpdate: request='.
				json_encode($request->all()));
		
			switch($request->field) {
				case "price":
					if (!empty($is_exist)) {
						if ($is_exist->price == $request->data) {
							abort(404);
						}
					}
					$update_array['price'] = (float)$request->data;
					$msg = ucfirst($request->field)." updated";
					break;		
				case "active":
					$update_array['active'] = empty($is_exist->active)  ?
						1:!$is_exist->active;

					if ($update_array['active'] == true) {
						$msg = "All location price has been activated";
					} else {
						$msg = "All location price has been deactivated";
					}
					break;
			}
			
			$update_array['franchisee_merchant_id'] = $user_data->company_id();
			$update_array["updated_at"]				= date("Y-m-d H:i:s");

			if (!empty($is_exist)) {
				DB::table('locationproductprice')->
					where('id', $is_exist->id)->
					update($update_array);

			} else {
				$update_array["location_id"]		= $request->location_id;
				$update_array["franchiseproduct_id"]= $request->f_id;
				$update_array["created_at"] 		= date("Y-m-d H:i:s");

				DB::table('locationproductprice')->
					insert($update_array);
			}

			$response = [];
			$response['output'] = view('layouts.dialog',compact('msg'))->render();

			if ($product_details->ptype == 'oilgas') 
				//	$this->generateFuelGradeStr($request->location_id);

				/*
				$response['fuelGrade'] 	= json_encode( 
					$this->generateFuelGradeStr($request->location_id),
					JSON_NUMERIC_CHECK
				);
				*/

			//Log::debug('response='.json_encode($response['fuelGrade']));

			return response()->json($response);

		} catch (Exception $e) {
			Log::error([
				"error"	=> $e->getmessage(),
				"file"	=> $e->getfile(),
				"line"	=> $e->getline()
			]);
			abort(404);
		}
	}	


	public function locationPriceToggleAll(Request $request) {
		try {
		
			$user_data	 	= new UserData();
			$franchiseid 	= $request->franchiseid;
			$locationid		= $request->locationid;
			$all_btn_state	= $request->all_btn_state;
			$date		 	= $request->date;
			
			$data = DB::table('franchiseproduct')->
				leftjoin('franchisemerchant',
					'franchisemerchant.franchise_id','=','franchiseproduct.franchise_id')->
				join('product','franchiseproduct.product_id', '=',
					'product.id')->
				join('franchisemerchantloc','franchisemerchantloc.franchisemerchant_id','=',
					'franchisemerchant.id')->
				where([
					'franchisemerchant.franchisee_merchant_id' => $user_data->company_id(),
					'franchiseproduct.active' => 1,
					'franchiseproduct.franchise_id' => $franchiseid,
					'franchisemerchantloc.location_id' => $locationid
				])->
				whereNull('franchiseproduct.deleted_at')->
				select('product.systemid','product.id as product_id', 'product.name', 'product.thumbnail_1',
						'franchiseproduct.upper_price','franchiseproduct.lower_price',
							'franchiseproduct.id as f_pid','franchisemerchantloc.location_id')->
				get();

				$data->map(function($z) use ($user_data, $all_btn_state ) {
					
						$condition = [
								"franchiseproduct_id"	=> $z->f_pid,
								"franchisee_merchant_id"=> $user_data->company_id(),
								"location_id"			=> $z->location_id
							];

					$locationproductprice_data = DB::table('locationproductprice')->
						where($condition)->first();

					if (!empty($locationproductprice_data)) {
						DB::table('locationproductprice')->
							where($condition)->update(['active' => !$all_btn_state]);
					} else {
						$condition['created_at'] = date('Y-m-d H:i:s');
						$condition['updated_at'] = date('Y-m-d H:i:s');
						$condition['active'] = !$all_btn_state;
						DB::table('locationproductprice')->
							insert($condition);
					}

				});

				if (!$all_btn_state == true) {
					$msg = "All location price has been activated";
				} else {
					$msg = "All location price has been deactivated";
				}
				
				return view("layouts.dialog",compact('msg'));

		} catch (\Exception $e) {
			\Log::info([
				"error"	=> $e->getmessage(),
				"file"	=> $e->getfile(),
				"line"	=> $e->getline()
			]);
			abort(404);
		}
	}

	public function locationPrice_stockLevel($product_id, $location_id) {
		try {
				$promo_product_sale_qty = 0;
				
				$location = location::find($location_id);
				$user_data = new UserData();

				if (empty(request()->date)) {
					$date = date('Y-m-d 23:59:59',strtotime("-1day"));
				} else {
					$date = date('Y-m-d 23:59:59',strtotime(request()->date));
				}

				$final_qty = 0;
				$product_Sales_qty_data = opos_receiptproduct::
				leftjoin('opos_itemdetails', 'opos_itemdetails.receiptproduct_id', '=', 'opos_receiptproduct.id')
					->leftjoin('opos_receipt', 'opos_receipt.id', '=', 'opos_receiptproduct.receipt_id')
					->leftjoin('opos_receiptdetails', 'opos_receipt.id', '=', 'opos_receiptdetails.receipt_id')
					->leftjoin('opos_locationterminal', 'opos_receipt.terminal_id', '=', 'opos_locationterminal.terminal_id')
					->leftjoin('location', 'location.id', '=', 'opos_locationterminal.location_id')
					->leftjoin('staff','staff.user_id','=','opos_receipt.staff_user_id')
					->where('staff.company_id',$user_data->company_id())
					->where('opos_receiptproduct.product_id', $product_id)
					->where('opos_receiptdetails.void', '!=', 1)
					->where('location.id', '=', $location_id)
					->whereDate('opos_receiptproduct.created_at','<',$date)
					->sum('opos_receiptproduct.quantity');

				$stock_qty =  StockReport::join('stockreportproduct','stockreportproduct.stockreport_id',
						'=','stockreport.id')->
					leftjoin('stockreportmerchant', 'stockreportmerchant.stockreport_id','=','stockreport.id')->
					where('stockreportmerchant.franchisee_merchant_id',$user_data->company_id())->
					where([
						'stockreport.location_id' 		=>	$location_id,
						'stockreportproduct.product_id' => $product_id
					])->
					whereNotIn('stockreport.type', ['transfer'])->
					where('stockreport.status', 'confirmed')->
					whereDate('stockreport.created_at','<',$date)->
					sum('stockreportproduct.quantity');
				
						
				$stockreport_qty = StockReport::join('stockreportproduct','stockreportproduct.stockreport_id',
						'=','stockreport.id')->
					leftjoin('stockreportmerchant', 'stockreportmerchant.stockreport_id','=','stockreport.id')->
					where('stockreportmerchant.franchisee_merchant_id',$user_data->company_id())->
					where('stockreportproduct.product_id', $product_id)->
					where('stockreport.dest_location_id', $location_id)->
					where('stockreport.type', 'transfer')->
					where('stockreport.status', 'confirmed')->
					whereDate('stockreport.created_at','<',$date)->
					sum('stockreportproduct.received');
				
				$stockreportminus_qty = StockReport::join('stockreportproduct','stockreportproduct.stockreport_id',
						'=','stockreport.id')->
					leftjoin('stockreportmerchant', 'stockreportmerchant.stockreport_id','=','stockreport.id')->
					where('stockreportmerchant.franchisee_merchant_id',$user_data->company_id())->
					where('stockreportproduct.product_id', $product_id)->
					where('stockreport.location_id', $location_id)->
					where('stockreport.type', 'transfer')->
					where('stockreport.status', 'confirmed')->
					whereDate('stockreport.created_at','<',$date)->
					sum('stockreportproduct.quantity');

				$refund_c = opos_refund::join("opos_receiptproduct", 'opos_receiptproduct.id',
						'=', 'opos_refund.receiptproduct_id')
					->leftjoin('opos_receipt', 'opos_receipt.id', '=', 'opos_receiptproduct.receipt_id')
					->leftjoin('opos_locationterminal', 'opos_receipt.terminal_id', '=', 'opos_locationterminal.terminal_id')
					->leftjoin('location', 'location.id', '=', 'opos_locationterminal.location_id')
					->leftjoin('staff','staff.user_id','=','opos_refund.confirmed_user_id')
					->where('staff.company_id', $user_data->company_id())
					->where('opos_receiptproduct.product_id', $product_id)
					->where('location.id', $location_id)
					->where('opos_refund.refund_type', 'C')
					->whereDate('opos_refund.created_at','<',$date)
					->count();
				
				$refund_dx = opos_refund::join("opos_receiptproduct", 'opos_receiptproduct.id',
						'=', 'opos_refund.receiptproduct_id')
					->leftjoin('opos_receipt', 'opos_receipt.id', '=', 'opos_receiptproduct.receipt_id')
					->leftjoin('opos_locationterminal', 'opos_receipt.terminal_id', '=', 'opos_locationterminal.terminal_id')
					->leftjoin('location', 'location.id', '=', 'opos_locationterminal.location_id')
					->where('opos_receiptproduct.product_id', $product_id)
					->where('location.id', $location_id)
					->where('opos_refund.refund_type', 'Dx')
					->whereDate('opos_refund.created_at','<',$date)
					->count();


					$voucherQty = voucher::join('voucherproduct', 'voucherproduct.product_id', '=', 'prd_voucher.product_id')
						->where('voucherproduct.location_id', $location_id)
					->where('voucherproduct.product_id', $product_id)
					->whereIn('prd_voucher.type', ['qty'])
					->whereDate('voucherproduct.created_at','<',$date)
					->sum('voucherproduct.vquantity');

				$wastage = opos_wastageproduct::where('product_id', $product_id)->
					where('location_id', $location_id)->	
					leftjoin('opos_wastage','opos_wastage.id','=','opos_wastageproduct.wastage_id')->
					leftjoin('staff','staff.user_id','=','opos_wastage.staff_user_id')->
					where('staff.company_id', $user_data->company_id())->
					whereDate('opos_wastage.created_at','<',$date)->
					sum('wastage_qty');

				$redeemed = DB::table('product_pts_redemption')->
					where('location_id', $location_id)->
					where('product_id', $product_id)->
					whereDate('product_pts_redemption.created_at','<',$date)->
					sum('quantity');
				
				$final_qty = $stock_qty + $stockreport_qty - $stockreportminus_qty - $product_Sales_qty_data + $refund_c - $wastage - $promo_product_sale_qty 					-  								- $redeemed - $voucherQty - $refund_dx;
				return $final_qty;
		
		}  catch (\exception $e) {
			\Log::info([
				"error"	=> $e->getmessage(),
				"file"	=> $e->getfile(),
				"line"	=> $e->getline()
			]);
			abort(404);
		}
	}

	//for fuel grading
}

