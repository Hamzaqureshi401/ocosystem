<?php

namespace App\Http\Controllers;

use Log;
use App\User;
use App\Models\Company;
use App\Models\product;
use App\Models\Merchant;
use Illuminate\Support\Facades\URL;
use App\Models\Staff;
use \App\Models\usersrole;
use App\Models\restaurant;
use App\Models\prd_commerce;
use Illuminate\Http\Request;
use App\Models\prd_inventory;
use App\Models\merchantproduct;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use \Illuminate\Support\Facades\Auth;
use App\Models\prd_ecommerceinventory;
use App\Models\prd_ecommercerestaurant;
use \App\Classes\ECAPI;
use \App\Classes\UserData;

class ECommerceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index() {
       /* 
        $user_id = Auth::user()->id;
        // $user_roles = usersrole::where('user_id', $user_id)->get();
        // $is_king =  Company::where('owner_user_id', Auth::user()->id)->get();
        
        $is_company_owner = Company::where('owner_user_id', $user_id)->first();

        if($is_company_owner) {
            $merchant = Merchant::where('id', $is_company_owner->id)->first();
            // insert merchant products into prd_ecommerce table
          //  $this->insert_products($merchant);
            // get merchant products
            $products = $this->get_products($merchant);
			
        } else {
            $staff = Staff::where('user_id', $user_id)->first();
        	$merchant_id = $staff->company_id;
        }
		*/

		$user_data = new UserData();
        $merchant = Merchant::where('company_id', $user_data->company_id())->first();
		$products = $this->get_products($merchant);

        return Datatables::of($products)
            ->addIndexColumn()
            ->addColumn('ecommerce_pro_id', function ($product) {
                return '<p class="os linkcolor" data-field="ecommerce_pro_id" style="margin: 0;text-align: center;">' . $product->systemid . '</p>';
            })

            ->addColumn('ecommerce_pro_name', function ($product) {
                $img_src = '/images/product/' . $product->id . '/thumb/' . $product->thumbnail_1;
                $img = "<img src='$img_src' data-field='ecommerce_pro_name' class='thum_".$product->id."' style=' width: 25px;height: 25px;display: inline-block;margin-right: 8px;object-fit:contain;'>";
                return '<span class="ecommerce_pro_name" data="'.$img_src.'">' .$img. '<p class="os-linkcolor" data-field="ecommerce_pro_name" style="cursor: pointer; margin: 0;display: inline-block">' .$product->name .'</p> </span>';
            })
            
            ->addColumn('ecommerce_qty', function ($product) {
                if($product->ptype === 'inventory') {
                    $qty = $product->int_qty * -1;
                    $class_name = '';
					$lc = "os-linkcolor";
					$cp = "cursor:pointer";

                } else {
                    $qty = ' - ';
                    $class_name = '';
					$lc = "";
					$cp = "";
                }
                return '<a href="'. route('ecommerce.product.ledger', ['productid'=>$product->id]).'" target="_blank" class="'.$class_name.' '.$lc.'"  data-field="ecommerce_qty" style="'.$cp.';text-decoration:none;margin: 0; text-align: center;">'.$qty.'</a>';
            })

            ->addColumn('ecommerce_ptype', function ($product) {
				switch($product->ptype) {
					case 'ecommerce':
						$type = "E-Commerce";
						break;
					case 'drum':
						$type = "Drum & Barrel";
						break;
					case 'services':
						$type = "Restaurant & Services";
						break;
					default: 
						$type = ucfirst($product->ptype);
				}

				/*
				Log::debug('product->ptype = '.$product->ptype);
				Log::debug('type = '.$type);
				*/

                return '<p class=""  data-field="ecommerce_ptype" style="margin: 0; text-align: center;">'.$type.'</p>';
            })
            ->addColumn('bluecrab', function ($product) {

				return '<div data-field="bluecrab" id="bluecrab"
					data-platform_id="bluecrab" class="bluecrab">
					<img src="/images/bluecrab_50x50.png"
						style="width:25px;height:25px;cursor:pointer"
					</img></div>';
            })
            
            ->escapeColumns([])
            ->make(true);
    }

    public function upsert_product_platform(Request $request) {	
		$user_id = Auth::user()->id;
		
        $is_king =  Company::where('owner_user_id', Auth::user()->id)->get();
        if(count($is_king) > 0){
        	$merchant =  Merchant::where('company_id', $is_king[0]->id)->first();
        	$merchant_id = $merchant->id;
        }else{
        	$staff = Staff::where('user_id', $user_id)->first();
        	$merchant_id = $staff->company_id;
        }		
	
		$product = product::where('id',$request->product_id)->first();
		$product_image = '';
		$product_thumb = '';
		try{
			$path_image = "images/product/" . $product->id . "/" . $product->photo_1;
			$type = pathinfo($path_image, PATHINFO_EXTENSION);
			$data = file_get_contents($path_image);
			$product_image = 'data:image/' . $type . ';base64,' . base64_encode($data);
			$path_thumb = "images/product/" . $product->id . "/thumb/" . $product->thumbnail_1;
			$type_thumb = pathinfo($path_thumb, PATHINFO_EXTENSION);
			$datathumb = file_get_contents($path_thumb);
			$product_thumb = 'data:image/' . $type_thumb . ';base64,' . base64_encode($datathumb);
		} catch (\Exception $e){
		//	dd($e->getMessage());
		}
		//dd($product_thumb);
		$prd_category = DB::table('prd_category')->where('id',$product->prdcategory_id)->first();
		$prd_subcategory = DB::table('prd_subcategory')->where('id',$product->prdprdcategory_id)->first();
		$prdcategory = DB::table('prdcategory')->where('id',$product->prdcategory_id)->first();
		$prd_inventory = DB::table('prd_inventory')->where('product_id',$product->id)->first();
		$prd_restaurant = DB::table('prd_restaurant')->where('product_id',$product->id)->first();
		$merchantproduct = DB::table('merchantproduct')->where('product_id',$product->id)->where('merchant_id',$merchant_id)->first();
		$merchant = DB::table('merchant')->join('company', 'company.id','=','merchant.company_id')->where('merchant.id',$merchant_id)->first();
		$appKey = env("APP_KEY", "0");
		$data = [
		   'appkey' => $appKey,
		   'product_image' => $product_image,
		   'product_thumb' => $product_thumb,
		   'product' => $product,
		   'prd_category' => $prd_category,
		   'prd_subcategory' => $prd_subcategory,
		   'prdcategory' => $prdcategory,
		   'prd_inventory' => $prd_inventory,
		   'prd_restaurant' => $prd_restaurant,
		   'merchantproduct' => $merchantproduct,
		   'merchant' => $merchant,
		 ];
		 $platforms = [];
		 if(!empty($request->platforms)){
			$platforms = $request->platforms;
		 }
		 
		 $noplatforms = [];
		 if(!empty($request->noplatforms)){
			$noplatforms = $request->noplatforms;
		 }
		 $error = "";
		 $response = array();
		 for($t = 0; $t < sizeof($platforms); $t++){
			 $platform = DB::table('ec_ecommercemgmt')->where('id', $platforms[$t])->first();
			 if(!is_null($platform)){
				 //Log::debug('PLATFORM ='.json_encode($platform));
				 try{
					 
					$endPoint = $platform->url . '/api/updateproduct';          
					$appKey = env("APP_KEY", "0");
					$Ecapi = new ECAPI($appKey, $endPoint);
					$rs = $Ecapi->postrequest($data);

					Log::debug('rs='.json_encode($rs));

					if(!empty($rs['response']) && !is_null($rs['response'])){
						if($rs['response']->status == 'Unauthorized'){
							$response[$t]['success'] = "Unauthorized";
						} else {
							$response[$t]['success'] = $platform->platform . ": Successfully Connected";
						}					
					} else {
						$response[$t]['success'] = $platform->platform . ": Offline";
						 $error .= $platform->platform . ": Offline";

					}				
				 } catch (\Exception $e){
					$response[$t]['success'] = $platform->platform . ": Offline";
				 }
			 }

		 }
		 for($t = 0; $t < sizeof($noplatforms); $t++){
			 $platform = DB::table('ec_ecommercemgmt')->where('id', $noplatforms[$t])->first();
			 if(!is_null($platform)){
				 try{
					$endPoint = $platform->url . '/api/updateproductselect';          
					$appKey = env("APP_KEY", "0");
					$Ecapi = new ECAPI($appKey, $endPoint);
					$rs = $Ecapi->postrequest($data);

					Log::debug('rs='.json_encode($rs));

					if(!empty($rs['response']) && !is_null($rs['response'])){
						if($rs['response']->status == 'Unauthorized'){
							$response[$t]['success'] = "Unauthorized";
						} else {
							$response[$t]['success'] = $platform->platform . ": Successfully Connected";
						}					
					} else {
						$response[$t]['success'] = $platform->platform . ": Offline";
						 $error .= $platform->platform . ": Offline";

					}				
				 } catch (\Exception $e){
					$response[$t]['success'] = $platform->platform . ": Offline";
				 }
			 }			 
		 }
		//dd($response);
	/*	$endPoint = 'http://localhost:8080/api/updateproduct';          
		$appKey = env("APP_KEY", "0");
		$Ecapi = new ECAPI($appKey, $endPoint);
		$configuration_test = $Ecapi->update_product($data);
		dd($configuration_test);*/
		//$response = response()->json(array("response" => $configuration_test));
		return response()->json($response);
	}
	
    public function insert_products($merchant) {

		Log::debug('insert_products: merchant='.json_encode($merchant));

        $tables = ['prd_inventory', 'prd_restaurant'];

        foreach($tables as $table) {
            $query = '
            INSERT INTO prd_ecommerce (product_id, created_at, updated_at)
            SELECT
                p.id as pid, 
                now(), now()
            FROM
                product p,
                '.$table.' pi,
                merchantproduct mp
            WHERE
                pi.product_id = p.id
                AND mp.product_id = p.id
                AND mp.merchant_id = :merchant_id
                AND p.thumbnail_1 is not null
                AND (pi.price is not null AND pi.price != 0)
                
            ON DUPLICATE KEY UPDATE 
                product_id = p.id
            ';
            DB::insert($query, ['merchant_id' => $merchant->id]);
        }

    }

    public function get_products($merchant) {
        // added product id for product idenfication
        $query = '
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
        $products =  DB::select($query, ['merchant_id' => $merchant->id]);
		foreach($products as $prd){
			$prd->int_qty = 0;
		}		
		$parray = array();
		 foreach ($products as $prd) {
			 array_push($parray, $prd->systemid);
		 }		
		
		$appKey = env("APP_KEY", "0");
		
		$query = "        
		SELECT 
			ec.id as id,
			ec.systemid,
			ec.platform,
			ec.url,
			ec.id as api,
			ec.status
		FROM ec_ecommercemgmt as ec
		WHERE merchant_id = ".$merchant->id."
		ORDER BY 
		 ec.created_at 
		DESC";
        $platforms = DB::select(DB::raw($query));	
		$reqdata = [
		   'appkey' => $appKey,
		   'parray' => $parray,
		 ];			
		foreach($platforms as $platform){
			//$platform->qty = 0;
			 try{		 
				$endPoint = $platform->url . '/api/checkproductsqty';          
				$Ecapi = new ECAPI($appKey, $endPoint);
				$rs = $Ecapi->getrequest($reqdata);
				
				if(!empty($rs['response']) && !is_null($rs['response'])){
					
					if($rs['response']->status == 'Unauthorized'){				
					} else {
						$resarr = $rs['response']->data;
						
						foreach($products as $prd){
							$sysid = $prd->systemid;
						//	dd($sysid);
						//	dd($resarr);
							if(isset($resarr->{$sysid})){
							
								if (!is_null($resarr->{$sysid})) {
									$prd->int_qty += $resarr->{$sysid};
								}
							}
						}
					}					
				} 				
			 } catch (\Exception $e){
				// dd($e->getMessage());
				$response[$t]['success'] = $platform->platform . ": Offline";
			 }			
		}		
        return collect($products);
    }

    public function eCommerce()
    {
    	$user_id = Auth::user()->id;
        $user_roles = usersrole::where('user_id', $user_id)->get();
        $is_king =  Company::where('owner_user_id', Auth::user()->id)->get();

    	return view('ecommerce.ecommerce', compact('user_roles', 'is_king'));
    }

    public function connected_platforms(request $request) 
    {
        $prd_id = $request->id;
		
		$user_id = Auth::user()->id;
		
        $is_king =  Company::where('owner_user_id', Auth::user()->id)->get();
        if(count($is_king) > 0){
        	$merchant =  Merchant::where('company_id', $is_king[0]->id)->first();
        	$merchant_id = $merchant->id;
        }else{
        	$staff = Staff::where('user_id', $user_id)->first();
        	$merchant_id = $staff->company_id;
        }		
		
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
		$product = DB::table('product')->where('id', $prd_id)->first();
        $platforms = DB::select(DB::raw($query));
		$appKey = env("APP_KEY", "0");
		$data = [
		   'appkey' => $appKey,
		   'product_id' => $product->systemid,
		 ];		
		foreach($platforms as $platform){
			$platform->exists = false;
			$platform->qty = 0;
			 try{		 
				$endPoint = $platform->url . '/api/checkproduct';          
				$appKey = env("APP_KEY", "0");
				$Ecapi = new ECAPI($appKey, $endPoint);
				$rs = $Ecapi->getrequest($data);
				if(!empty($rs['response']) && !is_null($rs['response'])){
					if($rs['response']->status == 'Unauthorized'){				
					} else {
				//		dd($rs);
						$platform->qty = $rs['response']->qty;
						if($rs['response']->data == 'exists'){
							$platform->exists = true;
						}
					}					
				} 				
			 } catch (\Exception $e){
				//$response[$t]['success'] = $platform->platform . ": Offline";
			 }			
		}
	//	dd($platforms);
        return view('ecommerce.ecommerce-platforms', compact('prd_id', 'platforms'));
    }

    public function remark(request $request){
		$appKey = env("APP_KEY", "0");
		$data = [
		   'appkey' => $appKey,
		   'receipt_id' => $request->receipt_id,
		   'receipt_remark' => $request->receipt_remark,
		 ];	

		 try{		 
			
			$endPoint = $request->url . '/api/saveremarks';          
			$appKey = env("APP_KEY", "0");
			$Ecapi = new ECAPI($appKey, $endPoint);
			$rs = $Ecapi->postrequest($data);
			//dd($request->url);
			if(!empty($rs['response']) && !is_null($rs['response'])){
				if($rs['response']->status == 'Unauthorized'){	
					$msg = "Error occured while Saving remarks";
					$dataret = view('layouts.dialog', compact('msg'));		
					return $dataret;
				} else {
				//	dd($rs);
					$msg = "Receipt remarks saved successfully";
					$dataret = view('layouts.dialog', compact('msg'));	
					return $dataret;
				}	
								
			} 	
			
		 } catch (\Exception $e){
			 dd($e->getMessage());
			$msg = "Error occured while Saving remarks";
			$dataret = view('layouts.dialog', compact('msg'));	
		//	 dd($e);
			//$response[$t]['success'] = $platform->platform . ": Offline";
			return $dataret;
		 }	
				 
	}
    public function receipt(request $request){
		$appKey = env("APP_KEY", "0");
		$data = [
		   'appkey' => $appKey,
		   'receipt_id' => $request->receipt_id,
		 ];	
		 $products = null;
		 $merchant = null;											
		 $receipt = null;
			
			 try{		 
				$endPoint = $request->url . '/api/getreceipts';          
				$appKey = env("APP_KEY", "0");
				$Ecapi = new ECAPI($appKey, $endPoint);
				$rs = $Ecapi->getrequest($data);
			//	dd($rs);
				if(!empty($rs['response']) && !is_null($rs['response'])){
					if($rs['response']->status == 'Unauthorized'){				
					} else {
					//	dd($rs);
						$products = $rs['response']->data;
						$merchant = $rs['response']->merchant;
						$receipt = $rs['response']->receipt;
						$platform_name = $rs['response']->platform_name;
						$platform_id = $rs['response']->platform_id;
					}					
				} 				
			 } catch (\Exception $e){
			//	 dd($e);
				//$response[$t]['success'] = $platform->platform . ": Offline";
			 }

			return view('ecommerce.receipt', compact(
                'products',
                'merchant',
                'receipt',
                'platform_id',
                'platform_name',
            ));			 
	}
	
    public function product_ledger(request $request)
    {
        $product = DB::table('product')->where('id', $request->productid)->first();
 		$user_id = Auth::user()->id;
		
        $is_king =  Company::where('owner_user_id', Auth::user()->id)->get();
        if(count($is_king) > 0){
        	$merchant =  Merchant::where('company_id', $is_king[0]->id)->first();
        	$merchant_id = $merchant->id;
        }else{
        	$staff = Staff::where('user_id', $user_id)->first();
        	$merchant_id = $staff->company_id;
        }		
		
        $user_roles = usersrole::where('user_id', $user_id)->get();	
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
        $platforms = DB::select(DB::raw($query));
		$appKey = env("APP_KEY", "0");
		$data = [
		   'appkey' => $appKey,
		   'product_id' => $product->systemid,
		 ];	
	//	$product->int_qty = 0;
		$defdata = [];
		$ii = 0;
		foreach($platforms as $platform){
			$platform->exists = false;
			$platform->qty = 0;
			$platform->product = $product;
			 try{		 
				$endPoint = $platform->url . '/api/checkproduct';          
				$appKey = env("APP_KEY", "0");
				$Ecapi = new ECAPI($appKey, $endPoint);
				$rs = $Ecapi->getrequest($data);
				if(!empty($rs['response']) && !is_null($rs['response'])){
					if($rs['response']->status == 'Unauthorized'){				
					} else {
					//	dd($rs);
						//$defdata[$ii] = $rs['response']->qty;
						if(!is_null($rs['response']->qty)){
							$receipts = $rs['response']->qty;
							foreach($receipts as $receipt){
								$defdata[$ii] = $receipt;
								$defdata[$ii]->platform = $platform;
								$ii++;
							}
						}
						if($rs['response']->data == 'exists'){
							$platform->exists = true;
						}
						
					}					
				} 				
			 } catch (\Exception $e){
				//$response[$t]['success'] = $platform->platform . ": Offline";
			 }			
		} 
//dd($defdata);		
        return view('ecommerce.ecommerce-prod-ledger', compact('defdata','platforms', 'product','user_roles','is_king'));
    }
}
