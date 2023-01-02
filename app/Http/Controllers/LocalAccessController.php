<?php

namespace App\Http\Controllers;

use Log;
use App\Models\FranchiseMerchant;
use App\Models\FranchiseProduct;
use App\Models\Merchant;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\IPConf;
use App\Models\Company;

class LocalAccessController extends Controller
{
    //
    public function licenceInterface(Request $request)
    {	
        try {
            $validation = Validator::make($request->all(), [
                'merchant_id'	=> 'required',
				'api_key'	 	=> 'required',	
                'licensekey'	=> 'required'
            ]);

            if ($validation->fails())
                throw new \Exception("Please fill information correctly.");
			
            $lic_key = $request->licensekey;

            $company_exist = DB::table('company')->where([
				'systemid' => $request->merchant_id,
			])->first();

			Log::debug('company_exist='.json_encode($company_exist));
			
            if (empty($company_exist))
				throw new \Exception("Company not found. Please recheck Merchant ID ".$company_exist);
			
            if ($company_exist->status != 'active')
                throw new \Exception("The franchisor and/or franchisee account is inactive. Please see the administrator.");
			
            $owner_user_data = DB::table('users')->
            join('staff', 'staff.user_id', 'users.id')->
            where('users.id', $company_exist->owner_user_id)->
            select("users.*", "staff.systemid")->first();

            // Ministation
            if (!empty($request->terminal_id)) {
                $terminal_data = DB::table('opos_terminal')->
                where('systemid', $request->terminal_id)->
                first();

                if (empty($terminal_data))
                    throw new Exception("Please fill information correctly.");

                $location_data = DB::table('location')->
                join('opos_locationterminal',
					'opos_locationterminal.location_id', 'location.id')->
                where('opos_locationterminal.terminal_id', $terminal_data->id)->
                select('location.*')->
                first();

                if (empty($location_data))
                    throw new Exception("Please fill information correctly.");

                DB::table('terminalipaddr')->insert([
                    "company_id" => $company_exist->id,
                    "location_id" => $location_data->id,
                    "terminal_id" => $terminal_data->id,
                    "ipaddr" => $request->LOCAL_IPADDR,
                    "tsystem" => $request->tsystem,		// CANNOT HARDCODE THIS!!
                    'created_at' => date("Y-m-d H:i:s"),
                    "updated_at" => date("Y-m-d H:i:s")
                ]);
			}

            // Oceania
            if (!empty($request->location_id)) 
            {
                $location_data = DB::table('location')->
                where('location.systemid', $request->location_id)->
                select('location.*')->
                first();

				Log::debug('location_id='.$request->location_id);

                if (!empty($location_data)) {
                    $isFAtive = DB::table('merchantlocation')->
                    join('company', 'company.id', 'merchantlocation.merchant_id')->
                    where([
                        'merchantlocation.location_id' => $location_data->id,
                        "company.status" => 'active'
                    ])->
                    select("company.*")->
                    first();

                    if (empty($isFAtive))
                        throw new \Exception("The franchisor and/or franchisee account is inactive. Please see the administrator.");

                    $terminal_data = DB::table('opos_terminal')->
                    join('opos_locationterminal', 'opos_locationterminal.terminal_id', 'opos_terminal.id')->
                    where('opos_locationterminal.location_id', $location_data->id)->
                    get();

                    DB::table('locationipaddr')->insert([
                        "company_id" => $company_exist->id,
                        "location_id" => $location_data->id,
                        "ipaddr" => $request->LOCAL_IPADDR,
                        "tsystem" => $request->tsystem,
                        'created_at' => date("Y-m-d H:i:s"),
                        "updated_at" => date("Y-m-d H:i:s")
                    ]);
                }
            }

            if (empty($location_data))
                throw new \Exception("Please fill information correctly.");

            $owner_user_data->status = 'active';

            $user_data = DB::table('users')->
				join('staff', 'staff.user_id', 'users.id')->
				join('userslocation', 'userslocation.user_id', 'users.id')->
				where([
					'staff.company_id' => $company_exist->id
				])->
				select("users.*", "staff.systemid")->
				get()->unique('systemid')->push($owner_user_data);

            $lic_locationkey = DB::table('lic_locationkey')->
            where('location_id', $location_data->id)->
            get();

            $lic_terminalkey = DB::table('lic_terminalkey')->
            join('opos_locationterminal',
                'opos_locationterminal.terminal_id', 'lic_terminalkey.terminal_id')->
            join('opos_terminal', 'opos_terminal.id', 'lic_terminalkey.terminal_id')->
            where('opos_locationterminal.location_id', $location_data->id)->
            select("lic_terminalkey.*", "opos_terminal.systemid")->
            get();

            //Fetch Product and Price
            //$merchantId = Merchant::where('company_id',$company_exist->id)->first()->id;
            $merchantId = $company_exist->id;
            $franchiseId = FranchiseMerchant::where('franchisee_merchant_id',$merchantId)->first()->id;
            $franchiseproducts = FranchiseProduct::with('productInformation')->where('franchise_id',$franchiseId)->get();

            $lic_keymatch = DB::table('lic_locationkey')->
            where([
                'license_key' => trim($lic_key),
                'location_id' => trim($location_data->id)
            ])->
            first();

            if (empty($lic_keymatch)) {
                throw new \Exception("Licence key not matched");
            }

			$check_if_f_valid = DB::table('franchise')->
				join('franchisemerchant','franchisemerchant.franchise_id','franchise.id')->
				join('franchisemerchantloc','franchisemerchantloc.franchisemerchant_id','franchisemerchant.id')->
				where([
					'franchise.owner_merchant_id' => $isFAtive->id,
					'franchisemerchant.franchisee_merchant_id' => $company_exist->id,
					'franchisemerchantloc.location_id'	=> $location_data->id
				])->first();

			if (empty($check_if_f_valid))
				throw new \Exception("Please fill information correctly.");

			$HW_Addr = $request->HW_Addr;
			$isExist = DB::table('serveraddr')->where([
				"location_id"	=>	$location_data->id,
				'ip_addr'		=>	request()->ip(),
				"hw_addr"		=>	$HW_Addr,
			])->first();

			if (empty($isExist)) {
				DB::table('serveraddr')->insert([
					"location_id"	=>	$location_data->id,
					'ip_addr'		=>	request()->ip(),
					"hw_addr"		=>	$HW_Addr,
					"created_at"	=>	now(),
					'updated_at'	=>	now()
				]);
			}

			// Registering ip address of oceania client in ocosystem ipconf
			$ipconfExist = DB::table('ipconf')->
				where('location_systemid', $request->location_id)->
				first();
		
			if (!$ipconfExist){
	        	$ipconf = new IPConf();

	        	$ipconf->public_ip = $request->PUBLIC_IPADDR ?? '0.0.0.0';
	        	$ipconf->local_ip = $request->LOCAL_IPADDR ?? '0.0.0.0';
	        	$ipconf->public_port = $request->PUBLIC_PORT ?? '80';
	        	$ipconf->local_port = $request->LOCAL_PORT ?? '80';
				$ipconf->location_systemid = $request->location_id ?? null;
	        	$ipconf->save();

        	}else{
        		$ipconf = IPConf::find($ipconfExist->id);

        		$ipconf->public_ip = $request->PUBLIC_IPADDR ?? '0.0.0.0';
	        	$ipconf->local_ip = $request->LOCAL_IPADDR ?? '0.0.0.0';
	        	$ipconf->public_port = $request->PUBLIC_PORT ?? '80';
	        	$ipconf->local_port = $request->LOCAL_PORT ?? '80';
				$ipconf->location_systemid = $request->location_id ?? null;
	        	$ipconf->save();
        	}

			// Registering ip address of oceania client in ocosystem ipconf
			$api_key = $request->api_key;
			$isExist = DB::table('oceania_apisecurity')->where([
				"app_key"			=> $api_key,
				"company_systemid"	=> $company_exist->systemid,
				"location_id" 		=> $location_data->id,
			])->first();

			if (empty($isExist)) {
				DB::table('oceania_apisecurity')->insert([
					"app_key"			=> $api_key,
					"company_systemid"	=> $company_exist->systemid,
					"location_id" 		=> $location_data->id,
					"created_at"	=>	now(),
					'updated_at'	=>	now()
				]);
			}
			
			//API PRODUCT PROCESS
			$location_F_product_ids = DB::table('franchiseproduct')->
				join('franchisemerchant','franchisemerchant.franchise_id','franchiseproduct.franchise_id')->
				join('franchisemerchantloc', 'franchisemerchantloc.franchisemerchant_id','franchisemerchant.id')->
				where([
					'franchisemerchant.franchisee_merchant_id'	=>	$isFAtive->id,
					'franchisemerchantloc.location_id'			=>	$location_data->id
				])->select('franchiseproduct.*')->
				get()->
				pluck('product_id')->unique();

			Log::debug('LFP location_F_product_ids='.$location_F_product_ids);
				
			$products = DB::table('product')->
				join('merchantproduct','merchantproduct.product_id','product.id')->
				where([
					'merchantproduct.merchant_id' => $isFAtive->id,
				])->
				orWhereIn('product.id',$location_F_product_ids)->
				whereIn('product.ptype', ['oilgas','inventory','services'])->
				select('product.*')->
				get()->filter(function($z) {
					return !empty($z->name) && !empty($z->photo_1);
				});


			$thumbnailData = app('App\Http\Controllers\APIFcController')->generateThumbnailContent($products);

		
			$locationPrice = DB::table('franchiseproduct')->
				join('franchisemerchant','franchisemerchant.franchise_id','franchiseproduct.franchise_id')->
				join('franchisemerchantloc', 'franchisemerchantloc.franchisemerchant_id','franchisemerchant.id')->
				join('product','product.id','franchiseproduct.product_id')->
				where([
					'franchisemerchant.franchisee_merchant_id'	=>	$company_exist->id,
					'franchisemerchantloc.location_id'			=>	$location_data->id
				])->
				whereIn('product.ptype', ['inventory'])->
				select("franchiseproduct.*","product.systemid")->
				get();

			$prd_category = DB::table('prd_category')->
				join('merchantprd_category','merchantprd_category.category_id','prd_category.id')->
				where("merchantprd_category.merchant_id", $isFAtive->id)->
				select("prd_category.*")->
				get();

			$prd_subcategory = DB::table('prd_subcategory')->
				join('prd_category','prd_subcategory.category_id','prd_category.id')->
				join('merchantprd_category','merchantprd_category.category_id','prd_category.id')->
				where("merchantprd_category.merchant_id", $isFAtive->id)->
				select("prd_subcategory.*",'prd_category.name as cat_name')->
				get();

			$prdBrand = DB::table('prd_brand')->
				join('merchantbrand','merchantbrand.brand_id','prd_brand.id')->
				where('merchantbrand.merchant_id',$isFAtive->id)->
				select('prd_brand.*')->
				get();

			$prd_inventory = DB::table('prd_inventory')->
				join('product','product.id','prd_inventory.product_id')->
				join('merchantproduct','merchantproduct.product_id','product.id')->
				where([
					'merchantproduct.merchant_id' => $isFAtive->id,
				])->
				orWhereIn('product.id',$location_F_product_ids)->
				select("prd_inventory.*","product.systemid")->
				get();

			$productbmatrixbarcode = DB::table('productbmatrixbarcode')->
				join('product','product.id','productbmatrixbarcode.product_id')->
				//join('productbmatrixbarcodelocation', 'productbmatrixbarcodelocation.productbmatrixbarcode_id',
				//	'productbmatrixbarcode.id')->
				//where('productbmatrixbarcodelocation.location_id', $location_data->id)->
				whereIn('product.id',$location_F_product_ids)->
				select("productbmatrixbarcode.*", 'product.systemid')->
				get();

			$productbarcode = DB::table('productbarcode')->
				join('product','product.id','productbarcode.product_id')->
				whereIn('product.id',$location_F_product_ids)->
				select("productbarcode.*", 'product.systemid')->
				get();
			
			$prdbmatrixbarcodegen = DB::table('prdbmatrixbarcodegen')->
				join('product','product.id','prdbmatrixbarcodegen.product_id')->
				whereIn('product.id',$location_F_product_ids)->
				select("prdbmatrixbarcodegen.*", 'product.systemid')->
				get();

			/* Add vehicle data here to be pushed down to Oceania;
			 * lg_vehiclemgmt
			 */
		
			Log::debug('isFAtive='.json_encode($isFAtive));

			$vehicle_data = DB::table('lg_vehiclemgmt')->
				join('lg_vehiclemgmtlocation',
					'lg_vehiclemgmtlocation.vehiclemgmt_id', 'lg_vehiclemgmt.id')->
				whereRaw('vehicle_license <> ""')->
				where("merchant_id", $company_exist->id)->
				get();		

			/* Add dealer data here to be pushed down to Oceania; 
			 * oneway:
			 * onewayrelation:
			 * onewaylocation:
			 * twoway:
			 * company: dealers' company record
			 * users: dealers' user record
			 * merchantlink: dealers link, can be at either
			 		initiator or responder
			 * merchantlinkrelation: relation from dealer's perspective */
			$oneway = DB::table('oneway')
				->where('self_merchant_id', $company_exist->id)
				->get();

			Log::debug('licenseInterface: oneway=-'.json_encode($oneway));

			$onewayrelation =  DB::table('oneway')
				->join('onewayrelation','onewayrelation.oneway_id','oneway.id')
				->select("onewayrelation.*")
				->where('oneway.self_merchant_id', $company_exist->id)
				->get();

			$onewaylocation =  DB::table('oneway')
				->join('onewaylocation','onewaylocation.oneway_id','oneway.id')
				->select("onewaylocation.*")
				->where('oneway.self_merchant_id', $company_exist->id)
				->get();

			$merchantLink = DB::table('merchantlink')
				->where('initiator_user_id', $company_exist->owner_user_id)
				->orWhere('responder_user_id' , $company_exist->owner_user_id)
				->get();

			 // $query = "SELECT 
		  //       c.id, c.systemid, c.name,c.owner_user_id,c.systemid , c.status
		  //       FROM
		  //       merchantlink m,
		  //       company c
		  //       WHERE
		  //       (m.initiator_user_id = c.owner_user_id AND
		  //       m.responder_user_id = $company_exist->owner_user_id)
		  //       OR
		  //       (m.initiator_user_id = $company_exist->owner_user_id AND
		  //       m.responder_user_id = c.owner_user_id)
		  //       OR
		  //       (m.initiator_user_id = c.owner_user_id AND c.owner_user_id = $company_exist->owner_user_id)
		  //       GROUP BY
		  //       c.id;";

		  //       $merchantLink = DB::select(DB::raw($query));


			// $merchantlinkrelation = DB::table('merchantlink')
			// 	->join('merchantlinkrelation',
			// 	'merchantlinkrelation.merchantlink_id','merchantlink.id')
			// 	->select("merchantlinkrelation.*")
			// 	->where('merchantlink.initiator_user_id', $company_exist->owner_user_id)
			// 	->orWhere('merchantlink.responder_user_id', $company_exist->owner_user_id)
			// 	->get();

			$query =DB::table(DB::raw("merchantlink m, company c, merchantlinkrelation mlr"))
				->select("c.*")
				->whereRaw ("(m.initiator_user_id = c.owner_user_id and m.responder_user_id = $company_exist->owner_user_id and mlr.ptype = 'dealer')")
				->orWhereRaw("(m.initiator_user_id = $company_exist->owner_user_id and m.responder_user_id = c.owner_user_id and mlr.ptype = 'dealer')")
				->orWhereRaw("(m.initiator_user_id = c.owner_user_id and c.owner_user_id = $company_exist->owner_user_id and mlr.ptype = 'dealer')")
				->groupBy("c.id");
			
			$twowaycompany = (clone $query)->get();

			
            $getcompanyid = DB::table('company')->whereIn('owner_user_id' , $twowaycompany->pluck('owner_user_id')->toArray())->pluck('id')->toArray();
            $merchantlinkrelation = DB::table('merchantlinkrelation')->whereIn('company_id' , $getcompanyid)->get();
    

			$company_users = (clone $query)->pluck('owner_user_id')->toArray();
			 $users = DB::table('staff')->join('users', 'users.id' , 'staff.user_id')->whereIn('users.id' , $company_users)->get();
			 $userAndAssociate = $user_data->merge($users);
			
            $return = [
                'company' => $company_exist,
                'terminal' => $terminal_data,
                'location' => $location_data,
                'users' => $userAndAssociate,
                'lic_locationkey' => $lic_locationkey,
                'lic_terminalkey' => $lic_terminalkey,
				'products' => $products,
				'locationPrice'		=> $locationPrice,
				'thumbnailData' => $thumbnailData,
				'prd_category' => $prd_category,
				'prd_subcategory' => $prd_subcategory,
				'prdBrand'	=> $prdBrand,
				'prd_inventory' => $prd_inventory,
				'vehicle_data'	=> $vehicle_data,
				'productbmatrixbarcode' => $productbmatrixbarcode,
				'productbarcode' => $productbarcode,
				'prdbmatrixbarcodegen' => $prdbmatrixbarcodegen,
				'oneway' => $oneway,
				'onewayrelation' => $onewayrelation,
				'onewaylocation' => $onewaylocation,
				'merchantLink' => $merchantLink,
				'merchantlinkrelation' => $merchantlinkrelation,
				'twowaycompany' => $twowaycompany,
				'ipconf' => $ipconf
            ];

		} catch (\Exception $e) {
            \Log::error([
                "Error" => $e->getMessage(),
                "File" => $e->getFile(),
                "Line" => $e->getLine()
            ]);

            $return = ["error" => $e->getMessage()];
        }

        return $return;
    }


    function licenceInterfaceTerminal(Request $request)
    {
        try {
            $validation = Validator::make($request->all(), [
                'terminal_id' => 'required',
				'api_key'	 	=> 'required',	
                'licensekey' => 'required'
            ]);

			Log::debug('licenceInterfaceTerminal()='.
				json_encode($request->all()));

            if ($validation->fails())
                throw new \Exception("Please fill information correctly.");

            $lic_key = $request->licensekey;

            $company_exist = DB::table('company')->
				where([
					'systemid' => $request->merchant_id,
				])->
				first();

            if (empty($company_exist))
                throw new \Exception("Company record corrupted");

            if ($company_exist->status != 'active')
                throw new \Exception("The franchisor and/or franchisee account is inactive. Please see the administrator.");

            $terminal_data = DB::table('opos_terminal')->
				where('systemid', $request->terminal_id)->
				first();

            if (empty($terminal_data))
                throw new \Exception("Please fill information correctly.");

			/*
			OBSELTE
			$HW_Addr = $request->HW_Addr;
			if (!empty($HW_Addr)) {
				DB::table('opos_terminal')->
					where('systemid', $request->terminal_id)->
					update([
						'hardware_addr' => $HW_Addr
					]);
			}
			 */

			$location_data = DB::table('location')->
                join('opos_locationterminal',
					'opos_locationterminal.location_id', 'location.id')->
                where('opos_locationterminal.terminal_id', $terminal_data->id)->
                select('location.*')->
                first();

			if (empty($location_data)) {
				Log::error('licenceInterfaceTerminal: location_data is NULL');
				Log::error('terminal_id='.json_encode($terminal_data));
				return;
			}

			$isFAtive = DB::table('merchantlocation')->
				join('company', 'company.id', 'merchantlocation.merchant_id')->
				where([
					'merchantlocation.location_id' => $location_data->id,
					"company.status" => 'active'
				])->
				select("company.*")->
				first();


			if (empty($isFAtive))
				throw new \Exception("The franchisor and/or franchisee account is inactive. Please see the administrator.");

			$this->verifyAPIKey($company_exist->systemid, $location_data->id,
				$request->api_key);

			$is_terminalipaddr = DB::table('terminalipaddr')->
				where([
					"company_id" => $company_exist->id,
					"location_id" => $location_data->id,
					"terminal_id" => $terminal_data->id,
					"ipaddr" => $request->LOCAL_IPADDR,
				])->first();

			if (empty($is_terminalipaddr)) {
				DB::table('terminalipaddr')->insert([
					"company_id" => $company_exist->id,
					"location_id" => $location_data->id,
					"terminal_id" => $terminal_data->id,
					"ipaddr" => $request->LOCAL_IPADDR,
					"tsystem" => $request->tsystem,		// CANNOT HARDCODE THIS!!
					'created_at' => date("Y-m-d H:i:s"),
					"updated_at" => date("Y-m-d H:i:s")
				]);
			}
		
			$terminalcount = DB::table('terminalcount')->where([
				'terminal_id' => $terminal_data->id,
			])->first();

            $return = [
                'terminal'		=> $terminal_data,
				'terminalcount'	=> $terminalcount
            ];

        } catch (\Exception $e) {
            \Log::error([
                "Error" => $e->getMessage(),
                "File" => $e->getFile(),
                "Line" => $e->getLine()
            ]);

            $return = ["error" => $e->getMessage()];
        }

        return $return;
    }


	function verifyAPIKey($company_systemid, $location_id, $api) {
		$verify = DB::table('oceania_apisecurity')->
			where([
				'app_key'				=> $api,
				'company_systemid'		=> $company_systemid,
				"location_id"			=> $location_id
			])->first();

		Log::info([
			'app_key'				=> $api,
			'company_systemid'		=> $company_systemid,
			"location_id"			=> $location_id
		]);

		if (empty($verify))
			throw new \Exception("Invalid API key");
	}
}
