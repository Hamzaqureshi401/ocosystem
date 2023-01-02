<?php

namespace App\Http\Controllers;

use App\Models\ECBuyer;
use App\Models\ECMerchant;
use App\Models\Staff;
use App\User;
use \App\Models\usersrole;
use App\Models\Company;
use \App\Models\ec_ecommercemgmt;
use \App\Models\Merchant;
use \App\Models\Currency;
use \App\Classes\UserData;
use \Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use \App\Classes\SystemID;
use DB;
use App\Classes\ECAPI;
use Log;

class IndustryEcommerceController extends Controller
{
	public function index(){
		return view('industry.ecommerce.ecommerce'); 
	}


	public function getDatatable() {
		/*
		$user_id = Auth::user()->id;
		
        $is_king =  Company::where('owner_user_id', Auth::user()->id)->get();
        if(count($is_king) > 0){
        	$merchant =  Merchant::where('company_id', $is_king[0]->id)->first();
        	$merchant_id = $merchant->id;
        }else{
        	$staff = Staff::where('user_id', $user_id)->first();
        	$merchant_id = $staff->company_id;
        }
		 */
		$this->user_data = new UserData();
		$merchant_id = $this->user_data->company_id();
        $model           = new ec_ecommercemgmt();
		$query = "        
		SELECT 
			ec.id as id,
			ec.systemid,
			ec.platform,
			ec.url,
			ec.id as api,
			ec.status
		FROM ec_ecommercemgmt as ec
		WHERE merchant_id = ".$merchant_id."
		AND ec.status = 'online'
		ORDER BY 
		 ec.created_at 
		DESC";

        $data = DB::select(DB::raw($query));
		
        $query2 = '
            SELECT
                DISTINCT(p.id), mp.merchant_id, p.systemid, p.name, p.ptype, p.photo_1, p.thumbnail_1
            FROM
                merchantproduct mp,
                product p
            WHERE
                mp.merchant_id = :merchant_id
                AND mp.product_id = p.id
				AND (p.ptype = \'inventory\' OR p.ptype = \'services\')
				AND p.name != \'\'
				AND p.photo_1 != \'\'
				AND p.prdcategory_id != \'\'
				AND p.prdcategory_id != 0
            ORDER BY p.ptype ASC
        ';
        $productdata =  DB::select($query2, ['merchant_id' => $merchant_id]);
		//dd($products);
	//	$productdata = DB::select(DB::raw($query2));	
		$parray = array();
		 foreach ($productdata as $prd) {
			 array_push($parray, $prd->systemid);
		 }
		$appKey = env("APP_KEY", "0");
	//	dd($parray);
		$reqdata = [
		   'appkey' => $appKey,
		   'parray' => $parray,
		 ];	

		 $counter = 0;
        foreach ($data as &$value) {
			//$value->productsqty = 0;
            try{
                
				$endPoint = $value->url . '/api/checkproducts';          
				$appKey = env("APP_KEY", "0");
				$Ecapi = new ECAPI($appKey, $endPoint);
				$rs = $Ecapi->getrequest($reqdata);
				if(!empty($rs['response']) && !is_null($rs['response'])){
					if($rs['response']->status == 'Unauthorized'){
						Log::debug('Unauthorized: ' . $appKey);
						 $value->status = "Offline";	
						 $value->productsqty = 0;						 
					} else {
						Log::debug('OK: ' . $appKey);
					//	dd($rs);
						$value->status = "Online";
						$value->productsqty = $rs['response']->data;
						$value->merchantqty = $rs['response']->merchant_data;
				//		$response[$t]['success'] = $platform->platform . ": Successfully Connected";
					}					
				} else {
					Log::debug('Not Connected: ' . $endPoint);
					$value->status = "Offline";
					$value->productsqty = 0;
					$value->merchantqty = 0;
				}
            } catch (\Exception $e) {
				Log::debug('Exception: ' . $e->getMessage());
                $value->status = "Offline";
				$value->productsqty = 0;
				$value->merchantqty = 0;
            }
			
			/*$merchantsconnected = DB::table('ec_ecommercemgmt')->where('status','online')->where('systemid',$value->systemid )->count();
			$value->merchantqty = $merchantsconnected;
			if($value->productsqty == 0){
				unset($data[$counter]);
			}*/
			$counter++;
        }
	//	dd($data);
		return Datatables::of($data)
            ->addIndexColumn()
            ->addColumn('systemid', function ($data) {
            	return $data->systemid;
            })
            ->addColumn('platform_name', function ($data) {
                return '<a title="'.$data->platform.'" href="javascript:void(0);" data-toggle="modal" data-target="#platform_name_modal" data-platform_id="'.$data->id.'" class="platform_name" data-platform_name="'.$data->platform.'">'.(($data->platform!='')?$data->platform:'Platform Name').'</a>';
            })
            ->addColumn('url', function ($data) {
            	return '<a href="'.$data->url.'" target="_blank">'.$data->url.'</a>';
            })
            ->addColumn('api', function ($data) {
            	return '';	// Squidster: temporarily API is blank
            	//return $data->api;
            })
            ->addColumn('merchant', function ($data) {
            	return '';	// Squidster: temporarily API is blank
            })
            ->addColumn('product', function ($data) {
				return '<a href="javascript:void(0)" onClick="openPlatformproducts('.$data->id.')"
					data-platform_id="'.$data->id.'"
					style="display:flex;align-items:center;justify-content:center"
					class="btn-qty-ecomm">'.$data->productsqty.'</a>';
            })
			->addColumn('merchant', function ($data) {
				 return '<a href="javascript:void(0)" onClick="openPlatformmerchant('.$data->systemid.','.$data->id.')"
                		data-platform_id="'.$data->id.'"
	                    style="display:flex;align-items:center;justify-content:center"
	                    class="btn-qty-ecomm">'.$data->merchantqty.'</a>';
            })
            ->addColumn('price', function ($data) {
            	return '';	// Squidster: temporarily product is blank
            	//return $data->api;
            })
            ->addColumn('qty', function ($data) {
            	return  $data->productsqty;		// Squidster: temporarily product is blank
            	//return $data->api;
            })
			->addColumn('status', function ($data) {
				return ucfirst($data->status);
            })
            ->addColumn('bluecrab', function ($data) {

                return '<a href="'.$data->url.'" 
					target="_blank" data-field="bluecrab"
					data-platform_id="'.$data->id.'"
					class="text-center ec_mgmt_bluecrab">
					<img src="/images/bluecrab_50x50.png"
						style="width:25px;height:25px;cursor:pointer"
					</img></a>';
            })
             ->addColumn('deleted', function ($data) {
				 if($data->status == 'Offline'){
                return '<a href="javascript:void(0);" data-field="deleted"
                		data-platform_id="'.$data->id.'"
	                     style="background-color:red;
	                    border-radius:5px;margin:auto;
	                    width:25px;height:25px;
	                    display:block;cursor: pointer;"
	                     class="text-danger remove">
	                    <i class="fas fa-times text-white"
	                    style="color:white;opacity:1.0;
	                     padding:5px 7px;
	                    -webkit-text-stroke: 1px red;"></i>
                	</a>';
				 } else {
					 return '<a href="javascript:void(0);" 
	                     style="background-color:#ddd;
	                    border-radius:5px;margin:auto;
	                    width:25px;height:25px;
	                    display:block;cursor: not-allowed;"
	                     class="text-danger" disabled>
	                    <i class="fas fa-times text-white"
	                    style="color:white;opacity:1.0;
	                     padding:5px 7px;
	                    -webkit-text-stroke: 1px #ddd;"></i>
                	</a>'; 
				 }
             })
            ->escapeColumns([])
            ->make(true);
    }
	
    public function platform_merchants(Request $request){
		$platform = $request->platform;
		$user_id = Auth::user()->id;
		
        $is_king =  Company::where('owner_user_id', Auth::user()->id)->get();
        if(count($is_king) > 0){
        	$merchant =  Merchant::where('company_id', $is_king[0]->id)->first();
        	$merchant_id = $merchant->id;
        }else{
        	$staff = Staff::where('user_id', $user_id)->first();
        	$merchant_id = $staff->company_id;
        }

		$appKey = env("APP_KEY", "0");
	//	dd($parray);
		$reqdata = [
		   'appkey' => $appKey
		 ];	
		// dd($parray);
		$productdata = array();
		$platform_econ = DB::table('ec_ecommercemgmt')->
			where('id', $platform)->first();

	//	dd($platform);
		$data = [];
		$appKey = env("APP_KEY", "0");
		 try{		 
			$endPoint = $platform_econ->url . '/api/getmerchants';          
			$Ecapi = new ECAPI($appKey, $endPoint);
			$rs = $Ecapi->getrequest($reqdata);
			//dd($rs);
			if(!empty($rs['response']) && !is_null($rs['response'])){
				if($rs['response']->status == 'Unauthorized'){				
				} else {
			//		dd($rs);
					$data = $rs['response']->data;
				}					
			} 				
		 } catch (\Exception $e){
		//	dd($e->getMessage());
			//$response[$t]['success'] = $platform->platform . ": Offline";
		 }	

       

		return Datatables::of($data)
            ->addIndexColumn()
            ->addColumn('systemid', function ($data) {
            	return $data->systemid;
            })
            ->addColumn('merchant_name', function ($data) {
				return $data->name;
            })
            ->escapeColumns([])
            ->make(true);		
	}
    public function platform_products(Request $request)
    {
		$platform = $request->platform;
		$user_id = Auth::user()->id;
		
        $is_king =  Company::where('owner_user_id', Auth::user()->id)->get();
        if(count($is_king) > 0){
        	$merchant =  Merchant::where('company_id', $is_king[0]->id)->first();
        	$merchant_id = $merchant->id;
        }else{
        	$staff = Staff::where('user_id', $user_id)->first();
        	$merchant_id = $staff->company_id;
        }

		$this->user_data = new UserData();
        $model           = new ec_ecommercemgmt();
		$query = "        
		SELECT 
			ec.id as id,
			ec.systemid,
			ec.platform,
			ec.url,
			ec.id as api,
			ec.status
		FROM ec_ecommercemgmt as ec
		WHERE merchant_id = ".$merchant_id."
		ORDER BY 
		 ec.created_at 
		DESC";

        $data = DB::select(DB::raw($query));
		
        $query2 = '
            SELECT
                DISTINCT(p.id), mp.merchant_id, p.systemid, p.name, p.ptype, p.photo_1, p.thumbnail_1
            FROM
                merchantproduct mp,
                product p
            WHERE
                mp.merchant_id = :merchant_id
                AND mp.product_id = p.id
				AND (p.ptype = \'inventory\' OR p.ptype = \'services\')
				AND p.name != \'\'
				AND p.photo_1 != \'\'
				AND p.prdcategory_id != \'\'
				AND p.prdcategory_id != 0
            ORDER BY p.ptype ASC
        ';
        $productdata =  DB::select($query2, ['merchant_id' => $merchant_id]);
		//dd($products);
	//	$productdata = DB::select(DB::raw($query2));	
		$parray = array();
		 foreach ($productdata as $prd) {
			 array_push($parray, $prd->systemid);
		 }
		$appKey = env("APP_KEY", "0");
	//	dd($parray);
		$reqdata = [
		   'appkey' => $appKey,
		   'parray' => $parray,
		 ];	
		// dd($parray);
		$productdata = array();
		$platform_econ = DB::table('ec_ecommercemgmt')->
			where('id', $platform)->first();

	//	dd($platform);
		$appKey = env("APP_KEY", "0");
		 try{		 
			$endPoint = $platform_econ->url . '/api/getproducts';          
			$Ecapi = new ECAPI($appKey, $endPoint);
			$rs = $Ecapi->getrequest($reqdata);
			
			if(!empty($rs['response']) && !is_null($rs['response'])){
				if($rs['response']->status == 'Unauthorized'){				
				} else {
			//		dd($rs);
					$productdata = $rs['response']->data;
				}					
			} 				
		 } catch (\Exception $e){
			// dd($e->getMessage());
			//$response[$t]['success'] = $platform->platform . ": Offline";
		 }	
		//dd($productdata);
		return Datatables::of($productdata)
            ->addIndexColumn()
            ->addColumn('systemid', function ($data) {
            	return $data->systemid;
            })
            ->addColumn('product_name', function ($data) {
				$currprd = DB::table('product')->where('systemid',$data->systemid)->first();
				if (!empty($data->thumbnail_1)) {
                    $img_src = '/images/product/' . $currprd->id . '/thumb/' . $data->thumbnail_1;
                    $img     = "<img src='$img_src' data-field='product_name' style=' width: 25px;height: 25px;display: inline-block;margin-right: 8px;object-fit:contain;'>";
                } else {
                    $img = null;
                }
                return $img . '<p class="os- linkcolor" data-field="restaurantnservices_pro_name" style="margin: 0;display: inline-block;">' . (!empty($data->name) ? $data->name : 'Product Name') . '</p>';
            })->addColumn('status', function ($data) {
				if($data->api_status == 'active'){
					$html = '<button type="button" style="width:70px" class="btn btn-default btn-ecomm-list-active" styel="color:#34dabb; border:1px solid #34dabb;font-weight:bold;">Active</button>';
				} else {
					$html = '<button type="button" style="width:70px" class="btn btn-default btn-ecomm-list">Active</button>';
				}
                return $html;
            })
            ->escapeColumns([])
            ->make(true);		
	}	
	
    public function openquantity(Request $request)
    {
		$platform = $request->platform;
        return view('industry.ecommerce.platform_products');
    }	
	
    public function destroy($id){
    	try {
			$this->user_data = new UserData();
			$model           = new ec_ecommercemgmt();
			$ec_ecommercemgmt = $model::where('id',$id)->update(['status'=>'offline']);
			//$ec_ecommercemgmt->delete();

			$msg = "Platform deleted successfully";
            return view('layouts.dialog', compact('msg'));
			
		} catch (\Exception $e) {
			$msg = $e;// "Some error occured";
			return view('layouts.dialog', compact('msg'));
		}
    }
    public function update(Request $request){
    	//Create a new product here
        try {
        	$this->user_data = new UserData();

        	$model           = new ec_ecommercemgmt();
			$ec_ecommercemgmt = $model::find($request->platform_id);
        	$ec_ecommercemgmt->platform = $request->platform_name;
            $ec_ecommercemgmt->save();

            $msg = "Platform update successfully";
            return view('layouts.dialog', compact('msg'));

        } catch (\Exception $e) {
            $msg = $e->getMessage();
            return view('layouts.dialog', compact('msg'));
        }
    }
    public function store(Request $request)
    {
        //Create a new product here
        try {
        	$user_id = Auth::user()->id;
		
	        $is_king =  Company::where('owner_user_id', Auth::user()->id)->get();
	        if(count($is_king) > 0){
	        	$merchant =  Merchant::where('company_id', $is_king[0]->id)->first();
	        	$merchant_id = $merchant->id;
	        }else{
	        	$staff = Staff::where('user_id', $user_id)->first();
	        	$merchant_id = $staff->company_id;
	        }
	        
        	$this->user_data = new UserData();
        	$systemid = new SystemID('platform');
        	$ec_ecommercemgmt         = new ec_ecommercemgmt();
        	$ec_ecommercemgmt->systemid = $systemid;
        	$ec_ecommercemgmt->merchant_id = $merchant_id;
        	$ec_ecommercemgmt->platform = '';
            $ec_ecommercemgmt->url    = '';
            $ec_ecommercemgmt->status    = 'online';
            $ec_ecommercemgmt->save();

            $msg = "Platform added successfully";
            return view('layouts.dialog', compact('msg'));

        } catch (\Exception $e) {
            $msg = $e->getMessage();
            return view('layouts.dialog', compact('msg'));
        }
    }
    public function testSite($id){
        $user_id = Auth::user()->id;
        $is_king =  Company::where('owner_user_id', Auth::user()->id)->get();
        $currency_code = '';
        $currency =  Currency::where('id', $is_king[0]->currency_id)->first();
        if($currency){
            $currency_code = $currency->code;
        }
        return view('industry.ecommerce.products', compact('currency_code'));
    }
    public function cart(){
        $user_id = Auth::user()->id;
        
        $currency_code = '';
        $is_king =  Company::where('owner_user_id', Auth::user()->id)->get();
        if(count($is_king) > 0){
            $merchant =  Merchant::where('company_id', $is_king[0]->id)->first();
            $currency =  Currency::where('id', $is_king[0]->currency_id)->first();
            if($currency){
                $currency_code = $currency->code;
            }
        }else{
            $staff = Staff::where('user_id', $user_id)->first();
            $company =  Company::where('owner_user_id', $staff->company_id)->first();
            $currency =  Currency::where('id', $company->currency_id)->first();
            if($currency){
                $currency_code = $currency->code;
            }
        }
        
        return view('industry.ecommerce.cart', compact('currency_code'));
    }

    public function padmin(request $request, ec_ecommercemgmt $admin_id)
    {
        // $user_id = Auth::user()->id;
		// $user_roles = usersrole::where('user_id', $user_id)->get();
        // $is_king =  Company::where('owner_user_id', Auth::user()->id)->get();
        $user = Auth::user();
        $merchant =  Merchant::where('id', $admin_id->merchant_id)->first();
        // $merchant_id = $merchant->id;
        $merchant_details = DB::table('merchantprd_category as mct')
        ->join('prdcategory as pct', 'mct.category_id', '=', 'pct.id')
        ->join('prd_subcategory as psct', 'pct.id', '=', 'psct.category_id')
        ->get();
     
        $merchant_products = DB::table('merchantproduct as mpd')
        ->where('mpd.id', '=', $admin_id->merchant_id)
        ->leftJoin('prd_ecommerce as epd', 'mpd.product_id', '=', 'epd.id')
        ->get();
        
        // dd($merchant_details);
        return view('industry.ecommerce.ec_platform_admin', compact('user', 'merchant_details'));
    }

    public function crmadmin(){
        return view('industry.ecommerce.crm.crm_landingpage');
    }

    public function PersonalAllDetails($id){
        $user_id = Auth::user()->id;
        $user_roles = usersrole::where('user_id',$user_id)->get();
        $is_king =  Company::where('owner_user_id',Auth::user()->id)->get();
        return view('industry.ecommerce.crm.personal_all_details',compact('user_roles','is_king'));
    }

    public function Personal($id){
        $user_id = Auth::user()->id;
        $user_roles = usersrole::where('user_id',$user_id)->get();
        $is_king =  Company::where('owner_user_id',Auth::user()->id)->get();
        return view('industry.ecommerce.crm.personal',compact('user_roles','is_king'));
    }

    public function CompanyAllDetails($id){
        $user_id = Auth::user()->id;
        $user_roles = usersrole::where('user_id',$user_id)->get();
        $is_king =  Company::where('owner_user_id',Auth::user()->id)->get();
        return view('industry.ecommerce.crm.company_all_details',compact('user_roles','is_king'));
    }

    public function Company($id){
        $user_id = Auth::user()->id;
        $user_roles = usersrole::where('user_id',$user_id)->get();
        $is_king =  Company::where('owner_user_id',Auth::user()->id)->get();
        return view('industry.ecommerce.crm.company',compact('user_roles','is_king'));
    }


    // public function showModal(Request $request)
    // {
    //     try {
    //         $allInputs = $request->all();
    //         $id        = $request->get('id');
    //         $fieldName = $request->get('field_name');

    //         $validation = Validator::make($allInputs, [
    //             'business_reg'    =>    'required',
    //             'director_name'   =>    'required',
    //             'number_employer' =>    'required',

    //             'NRIC'            =>    'required',
    //             'dob'             =>    'required',
    //             'mobile'          =>    'required'
    //         ]);

    //         if ($validation->fails()) {
    //             $response = (new ApiMessageController())->
    //                 validatemessage($validation->errors()->first());

    //         } else {

    //             $membership = membership::where('id', $id)->first();
    //             return view('membership.membership-modals', compact(['id', 'fieldName', 'membership']));
    //         }

    //     } catch (\Illuminate\Database\QueryException $ex) {
    //         $response = (new ApiMessageController())->queryexception($ex);
    //     }
    // }




    public function showBuyer(request $request){

        $ec_id = $request->route('ec_id');

        $user_id = Auth::user()->id;
        $user_roles = usersrole::where('user_id',$user_id)->get();
        $is_king =  Company::where('owner_user_id',Auth::user()->id)->get();
        return view('industry.ecommerce.buyer.ec_buyer', compact('user_roles', 'is_king', 'ec_id'));
    }

    public function showBuyerDatatable( request $request )
	{
        $ec_id = $request->route('ec_id');        
        $ec_ecommercemgmt = ec_ecommercemgmt::find($ec_id);
        
        $data = [];
        if (!empty($ec_ecommercemgmt))
        {
            try{
                $appKey = env("APP_KEY", "0");
                return $ec_ecommercemgmt->url. "/api/crm-buyer-datatabe";
                $ECAPI = new ECAPI($appKey, $ec_ecommercemgmt->url. "/api/crm-buyer-datatabe");
                $params = [ ];
                $rs = $ECAPI->call( "GET", $params);

                if(isset($rs["response"]) && $rs["response"]->status == 'success' )
                {
                    $data = [];
                }
                else{
                    $data = [];
                }
            } catch (\Exception $e) {
                $data = [];
            }
        }

        return $rs;

        $data = ECBuyer::all();

		return Datatables::of($data)
			->addIndexColumn()
			->addColumn('receipt_id', function ($cmrList) {
				return  '<p class=" text-left m-0" data-field="receipt_id" >' .$cmrList->receipt_id. '</p>';
        })
			->addColumn('status', function ($cmrList) {
				return '<p class="text-right m-0" data-field="status" style="width: 150px" >'.$cmrList->status.'</p>';
			})

			->addColumn('delivery', function ($cmrList) {

				return '<p data-field="delivery" disabled="disabled" class="text-center m-0" "  style="width: 150px">'.$cmrList->delivery.'</p>';

			})
			->escapeColumns([])
			->make(true);
    }
    
    public function showCompany(){
        //        $user_id = Auth::user()->id;
        //        $user_roles = usersrole::where('user_id',$user_id)->get();
        //        $is_king =  Company::where('owner_user_id',Auth::user()->id)->get();
                return view('industry.ecommerce.merchant.ec_merchant');
            }

	public function showCompanyDatatable()
	{
		$user = Auth::user();


        $data = ECMerchant::where('user_id', $user->id)->get();



		return Datatables::of($data)
			->addIndexColumn()
			->addColumn('order_id', function ($cmrList) {
				return '<p class="text-center" data-field="order_id" style="vertical-align: middle; cursor: pointer; margin: 0;">'. $cmrList['order_id'] . '</p>';
			})
			->addColumn('name', function ($cmrList) {
				return  '<p class=" text-left m-0" data-field="name" >' . ucfirst(($cmrList->user->name) ). '</p>';
        })
			->addColumn('amount', function ($cmrList) {
				return '<p class="text-right m-0" data-field="amount" style="width: 150px" >'.ucfirst((!empty($cmrList['amount']) ? number_format($cmrList['amount']): '0.00' )).'</p>';
			})

			->addColumn('status', function ($cmrList) {

				return '<p data-field="status" disabled="disabled" class="text-center m-0" "  style="width: 150px">'.$cmrList->status.'</p>';

			})
            ->addColumn('execution', function ($cmrList) {
               return '<p 
                            data-field="execution"
                      
                            class="m-0 text-center remove "
                            
                        style="width: 150px">
                        <button   class="btn btn-gray text-center">Execution </button>
                        </p>';
			})
			->escapeColumns([])
			->make(true);
	}


}
