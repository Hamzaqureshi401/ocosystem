<?php

namespace App\Http\Controllers;

use App\Classes\UserData;
use App\Models\Merchant;
use App\Models\merchantproduct;
use App\Models\opos_loyaltyproductredemption;
use App\Models\opos_loyaltyptslog;
use App\Models\prd_inventory;
use App\Models\product;
use App\Models\restaurant;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator as Validator;
use App\Models\usersrole;
use App\Models\role;
use Yajra\DataTables\DataTables;
use App\Models\Company;
use App\Models\opos_member;
use App\Models\locationproduct;
use App\Models\opos_receipt;

class RetailMembershipController extends Controller
{
	protected $user_data;

	public function index()
	{
		$status = "active";
/*	 
	$user_id = Auth::user()->id;
       //code to fetch merchantID for the membership **b@ttl3
	 
	$company = DB::table('company')->where('owner_user_id',$user_id)->get();
            if(!empty($company)){
				$merchant = DB::table('merchant')
				->where('company_id', $company[0]->id)
				->get();
                $merchant_id = $merchant[0]->id;
                $company_id = $company[0]->id;
            }else{
				$staff = DB::table('staff')
				->join('merchant','staff.company_id','=','merchant.id')
				->where('staff.user_id',$user_id)
				->select('merchant.id','merchant.company_id')
				->get();
				$merchant_id = $staff[0]->id;
	    }
 */
	$user_data = new UserData();
	$merchant_id = $user_data->company_id();
        //get members where pts is bigger than zero
        $members_pts_bigger_than_zero=opos_member::where("merchant_id", "=", $merchant_id)->where("loyaltypts", ">", 0)->orderBy("updated_at", "desc")->get();

        //get members where pts is zero
        $members_pts_not_bigger_than_zero=opos_member::where("merchant_id", "=", $merchant_id)->where("loyaltypts", "<=", 0)->orderBy("updated_at", "asc")->get();

        //merge the two results
        $mebers = $members_pts_bigger_than_zero->merge($members_pts_not_bigger_than_zero);
		return DataTables::of($mebers)
			->addIndexColumn()

			->addColumn('retail_cust_id',function($member){
			return '<p data-field="retail_cust_id" style="margin:0;"  class="text-center">'.$member->systemid.'</p>';
			})
			->addColumn('retail_cust_name',function($member){
			return '<p data-field="retail_cust_name" style="margin:0;" >'.$member->name.'</p>';
			})
			->addColumn('retail_status',function($member){
			return '<p data-field="retail_status" style="margin:0;" class="text-center">'.ucfirst($member->status).'</p>';
			})
			->addColumn('retail_nric',function($member){
				return '<p data-field="retail_nric" style="margin:0;" class="text-center">'.$member->nric.'</p>';
			})
			->addColumn('retail_pts',function($member){
			return '<a href="/retailmembership-loyalty-ledger-view?id='.$member->id.'" style="margin:0; text-decoration: none; text-align:center;" target="_blank" data-field="retail_point" class="text-center" style="text-decoration:none;">'.$member->loyaltypts.'</a>';
			})
 			->addColumn('retail_mts',function($member){
			return '<a href="/retailmembership-point-ledger-view?id='.$member->id.'" style="margin:0; text-decoration: none; text-align:center;" target="_blank" data-field="retail_point" class="text-center" style="text-decoration:none;">'.$member->membershipmts.'</a>';
			})
  			->addColumn('retail_crt',function($member){
			return '<a href="/retailmembership-wallet-ledger-view?id='.$member->id.'" style="margin:0; text-decoration: none;" target="_blank" data-field="retail_point" class="text-center" style="text-decoration:none;" align="center">0</a>';
			})
    		
			->escapeColumns([])
			->make(true);
	}


    function showRetailMembershipView(){
		return view('retail_membership.retail_membership');
	}


    public function showRetailMembershippointledgerView(){
    	$user_id = Auth::user()->id;
    	$user_roles = usersrole::where('user_id',$user_id)->get();
    	$is_king =  Company::where('owner_user_id',Auth::user()->id)->
			get();
    	return view('retail_membership.membership_point_ledger',
			compact('user_roles','is_king'));
    }


    public function showRetailMembershiployaltyledgerView(){
    	$user_id = Auth::user()->id;
    	$user_roles = usersrole::where('user_id',$user_id)->get();
    	$is_king =  Company::where('owner_user_id',Auth::user()->id)->
			get();
    	return view('retail_membership.membership_loyalty_ledger',
			compact('user_roles','is_king'));
    }
 

    public function showRetailMembershipwalletledgerView(){
    	$user_id = Auth::user()->id;
    	$user_roles = usersrole::where('user_id',$user_id)->get();
    	$is_king =  Company::where('owner_user_id',Auth::user()->id)->
			get();
    	return view('retail_membership.membership_wallet_ledger',
			compact('user_roles','is_king'));
	}


	public function showProductRedemptionView(){

        $this->user_data = new UserData();
		
        $company = DB::table('company')->find($this->user_data->company_id());
	
		if(!empty($company)){
			$merchant = DB::table('merchant')
			->where('company_id', $company->id)
			->first();
			$merchant_id = $merchant->id;
			$company_id = $company->id;
		}
		
		/*else{
			$staff = DB::table('staff')
			->join('merchant','staff.company_id','=','merchant.id')
			->where('staff.user_id',$user_id)
			->select('merchant.id','merchant.company_id')
			->get();
			$merchant_id = $staff[0]->id;
		}*/
			
			
			
       
		/*
        $ids  = merchantproduct::where('merchant_id',
			$this->user_data->company_id())->
			pluck('product_id')->toArray();

        $productRedemptions = DB::table('product')->whereIn('id',$ids)->
			where(function($q) {
                $q->where('ptype','services')->
				orWhere('ptype','inventory')->
				orWhere('ptype','voucher')->
				orWhere('ptype','membership');
            })->orderBy('created_at', 'asc')->latest()->get();
        */
        
        $data = DB::table('product')
        ->select(
            'product.id',
            'product.name',
            'product.systemid',
            'product.ptype',
            'product.thumbnail_1',
            'merchantproduct.merchant_id',
			'locationproduct.quantity',
			'locationproduct.location_id',
			'locationproduct.created_at'
			)
		->leftJoin(
			'locationproduct',
			'product.id','=',
			'locationproduct.product_id'
			)			
        ->leftJoin(
            'merchantproduct',
            'product.id','=',
            'merchantproduct.product_id'
            )
	   ->where('merchantproduct.merchant_id', $merchant_id)
	//    ->orderBy('locationproduct.created_at', 'desc')
		->groupBy('product.id')
		->get();
	//	dd($data);

		$new_data = [];

        foreach($data as $key => $val){
            if($val->ptype == 'inventory' || $val->ptype == 'services'){
			/*	$get_latest_location_quantity = DB::table('locationproduct')
				->where('product_id', $val->id)
				->where('location_id', $val->location_id)
				->orderBy('created_at', 'desc')
				->first();*/
			//	dd($val->location_id);
				if(!empty($val->name))
				{
					if($val->ptype == 'services')
					{
						$type = 'Restaurant & Services';
						$T_quantity = '-';
					}
					else{
						$type = $val->ptype;
						$T_quantity = app('App\Http\Controllers\InventoryController')->check_quantity($val->id);
					}

					$new_data[$key]['id'] = $val->id;
					$new_data[$key]['name'] = $val->name;
					$new_data[$key]['systemid'] = $val->systemid;
					$new_data[$key]['type'] = $type;
					$new_data[$key]['T_quantity'] = $T_quantity;
					if($T_quantity == 0){
						$T_quantity = app('App\Http\Controllers\LoyaltyController')->quantityzero($val->id);
					}
					$new_data[$key]['thumbnail_1'] = $val->thumbnail_1;
					$new_data[$key]['location_id'] = $val->location_id;
					$new_data[$key]['merchant_id'] = $val->merchant_id;
					$new_data[$key]['created_at'] = $val->created_at;
				}
            }
        }

		  $data = $new_data;
		
			$count = 0;	
			$productRedemption = 0;
		return view('retail_membership.product_redemption',
			compact('data', 'count', 'productRedemption'));
    }


    public function saveValidity(Request $request) {
		$validity = null;
        $this->user_data = new UserData();
        $ids  = merchantproduct::where('merchant_id',
			$this->user_data->company_id())->pluck('product_id')->toArray();

        $productRedemptions = DB::table('product')->whereIn('id',$ids)
            ->where(function($q) {
                $q->where('ptype','services')
                    ->orWhere('ptype','inventory')
                    ->orWhere('ptype','voucher')->orWhere('ptype','membership');
            })
            ->orderBy('created_at', 'asc')->latest()->get();

        $time = strtotime(now());

		/*
		if ($request->selected == "1 Month") {
			$validity = '+1 month';
			$createdAt = DB::table('opos_loyaltyptslog')->select('created_at','id')->get();
			foreach ($createdAt as $item) {
				$newTime = strtotime($item->created_at);
				$id = $item->id;
				$expiry = DB::table('opos_loyaltyptslog')->where('id',$id)->update([
					'expiry' => date('Y-m-d H:i:s', strtotime('+1 month', $newTime))
				]);
			}

		} else if ($request->selected == "1 Year") {
			$validity = '+1 year';
			$createdAt = DB::table('opos_loyaltyptslog')->select('created_at','id')->get();
			foreach ($createdAt as $item) {
				$newTime = strtotime($item->created_at);
				$id = $item->id;
				$expiry = DB::table('opos_loyaltyptslog')->where('id',$id)->update([
					'expiry' => date('Y-m-d H:i:s', strtotime('+1 year', $newTime))
				]);
			}

		} else if ($request->selected == "2 Year") {
			$validity = '+2 year';
			$createdAt = DB::table('opos_loyaltyptslog')->select('created_at','id')->get();
			foreach ($createdAt as $item) {
				$newTime = strtotime($item->created_at);
				$id = $item->id;
				$expiry = DB::table('opos_loyaltyptslog')->where('id',$id)->update([
					'expiry' => date('Y-m-d H:i:s', strtotime('+2 year', $newTime))
				]);
			}
		}
		*/

		switch ($request->selected) {
			case "1 Month";
				$validity = '+1 month';
				break;
			case "1 Year";
				$validity = '+1 year';
				break;
			case "2 Year";
				$validity = '+2 year';
				break;
			default:
				$validity = '+1 year';
		}

		$createdAt = DB::table('opos_loyaltyptslog')->select('created_at','id')->get();
		foreach ($createdAt as $item) {
			$newTime = strtotime($item->created_at);
			$id = $item->id;
			$expiry = DB::table('opos_loyaltyptslog')->where('id',$id)->update([
				'expiry' => date('Y-m-d H:i:s', strtotime($validity, $newTime))
			]);
		}
    }


	public function checkNric(Request $request){
		$validator = Validator::make($request->all(), [ 
			'nric' => 'required',
		]);
		if ($validator->fails()) {
			$response['response'] = $validator->messages();
		}else{
			$opos_member=opos_member::where("nric", "=", $request->input('nric'))->get()->first();
			 $response['points'] = !empty($opos_member)?$opos_member->loyaltypts:'Not exist';
		}

		return $response;
	}

    public function saveTermsCondition(Request $request)
    {
        $this->user_data = new UserData();
        $ids = Merchant::join('company','company.id', '=', 'merchant.company_id')
            ->where('merchant.id',$this->user_data->company_id())->pluck('merchant.id');

        $validation =$request->validate([
            'terms' => 'required',
        ]);

            $updateTerms = DB::table('merchant')->whereIn('id', $ids)
                ->update([
                    'term_condition' => $request->terms
                ]);
            if ($updateTerms) {
                return $updateTerms;
            }
    }

    public function indexforledger(){
		
		$id = $_REQUEST['id'];
        $data = opos_loyaltyptslog::where('member_id',$id)->get();
        

    	return DataTables::of($data)
			->addIndexColumn()
			->addColumn('retail_transaction_id',function($systemid){
			return '<p data-field="retail_transaction_id" style="margin:0; text-decoration: none; cursor: pointer;"  class="text-center os-linkcolor">'.$systemid->systemid.'</p>';
			})
			->addColumn('retail_pts',function($points){
				$sm_pts = $points->lpts;
				if ($points->type == 'out'){
					$sm_pts = '-'.$points->lpts;
				}
				return '<p data-field="retail_pts" style="margin:0;" >'.$sm_pts.'</p>';
			})
            
			->addColumn('retail_source',function($data){
			return '<p data-field="retail_source" style="margin:0;" class="text-center">'.$this->getMerchantName($data->source_merchant_id).'</p>';
			})
			->addColumn('retail_bought',function($data){
			return '<p data-field="retail_bought" style="margin:0;" class="text-center">'.$this->getMerchantName($data->rewarded_merchant_id).'</p>';
			})
			->addColumn('retail_consumed',function($data){
			return '<p data-field="retail_consumed" style="margin:0;" class="text-center">'.$this->getMerchantName($data->redeemed_merchant_id).'</p>';
			})

			->escapeColumns([])
			->make(true);
    }

    public function indexforwallet(){
        
        $id = $_REQUEST['id'];
        $data = opos_loyaltyptslog::where('member_id',$id)->get();

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('retail_transaction_id',function($systemid){
            return '<p data-field="retail_transaction_id" style="margin:0; text-decoration: none; cursor: pointer;"  class="text-center os-linkcolor">'.$systemid->systemid.'</p>';
            })
            ->addColumn('retail_pts',function($points){
                $sm_pts = $points->lpts;
                if ($points->type == 'out'){
                    $sm_pts = '-'.$points->lpts;
                }
                return '<p data-field="retail_pts" style="margin:0;" >'.$sm_pts.'</p>';
            })
            ->addColumn('retail_in_out',function($expiry){
                return '<p data-field="retail_in_out" style="margin:0;"  class="text-center">'.date('dMy', strtotime($expiry->expiry)).'</p>';
            })
            
            ->addColumn('retail_source',function($data){
            return '<p data-field="retail_source" style="margin:0;" class="text-center">'.$this->getMerchantName($data->source_merchant_id).'</p>';
            })
            ->addColumn('retail_bought',function($data){
            return '<p data-field="retail_bought" style="margin:0;" class="text-center">'.$this->getMerchantName($data->rewarded_merchant_id).'</p>';
            })
            ->addColumn('retail_consumed',function($data){
            return '<p data-field="retail_consumed" style="margin:0;" class="text-center">'.$this->getMerchantName($data->redeemed_merchant_id).'</p>';
            })

            ->escapeColumns([])
            ->make(true);
    }

	function sortcreated($a, $b) {
		return strcmp($b->created_at, $a->created_at);
	}

	public function indexforloyalty(){
        $id = $_REQUEST['id'];
         $get_expired = DB::table('opos_loyaltyptslog')->where('member_id', $id)
						->where('expiry', '<=', date('Y-m-d H:i:s'))->where('status', '!=', 'expired')
						->get();
		
        if(!empty($get_expired) || count($get_expired) >= 1){
            foreach($get_expired as $expired){
                $update_status = DB::table('opos_loyaltyptslog')->where('id',$expired->id)->update(['status' => 'expired']);
                $get_member_lpt = DB::table('opos_member')->where('id', $expired->member_id)->first();
                if($get_member_lpt->loyaltypts > 0 || $expired->lpts < $get_member_lpt->loyaltypts){
                      $new_point = ($get_member_lpt->loyaltypts) - ($expired->lpts);
                        $update_member_lpt = DB::table('opos_member')->where('id',$id)->update(['loyaltypts' => $new_point]);
                }
            }
        }


        $loyaltypts = DB::table('opos_loyaltyptslog')->select('systemid', 'lpts as points', 'expiry', 'status'
														, 'source_merchant_id', 'receipt_id', 'type',
														'rewarded_merchant_id', 'redeemed_merchant_id', 'created_at')
												->orderBy('created_at', 'DESC')->where('member_id',$id)->get();
		$redeemed = DB::table('product_pts_redemption')->selectRaw('id, sum(total_pts_redeemed) as points, systemid,
																	created_at')
														->where('member_id',$id)->groupBy('systemid')->get();
		
	//	$data = $data->union($redeemed)->orderBy('expiry', 'DESC')->get();
		$data = array();
		$indexdata = 0;
		foreach($loyaltypts as $key => $value){
			$data[$indexdata] = $value;
			$indexdata++;
		}
	//	dd($redeemed);
		foreach($redeemed as $key => $value){
			$data[$indexdata] = $value;
			$data[$indexdata]->systemid = $value->systemid;
			$data[$indexdata]->points = $value->points;
			$data[$indexdata]->expiry = $value->created_at;
			$data[$indexdata]->status = "redeemed";
			$data[$indexdata]->receipt_id = $value->id;
			$data[$indexdata]->type = "out";
			$data[$indexdata]->rewarded_merchant_id = 0;
			$data[$indexdata]->redeemed_merchant_id = 0;
			$data[$indexdata]->source_merchant_id = 0;
			$indexdata++;
		}
	//	dd($data);
		usort($data, array($this, "sortcreated"));


        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('retail_transaction_id',function($systemid){
                //code to fetch receiptID for the transaction ID in the loyalty ledger **b@ttl3
				if($systemid->status != "redeemed"){
					$receipt_id = $systemid->receipt_id;
					$receipt_details = opos_receipt::where('id',$receipt_id)->get();

					return '<p data-field="retail_transaction_id" style="margin:0; text-decoration: none; cursor: pointer;"  data-receipt-id="'.$receipt_details[0]->systemid.'" class="text-center os-linkcolor">'.$systemid->systemid.'</p>';
				} else {
					return '<a href="' . url('opossum') . '/getpointredemption/' .
						$systemid->systemid .
						'" target="_blank" style="text-decoration:none; cursor: pointer;">' .
						$systemid->systemid .'</a>';
				}
			})
            ->addColumn('retail_pts',function($points){
                $sm_pts = $points->points;
                if ($points->type == 'out'){
                    $sm_pts = '-'.$points->points;
                }
                return '<p data-field="retail_pts" style="margin:0;" >'.$sm_pts.'</p>';
            })
            ->addColumn('retail_expiry',function($expiry){
                return '<p data-field="retail_expiry" style="margin:0;"  class="text-center">'.date('dMy', strtotime($expiry->expiry)).'</p>';
            })
            ->addColumn('retail_status',function($status){
                if($status->status == null || $status->status == '' || empty($status->status)){
                    $status->status = 'earned';
                }
                return '<p data-field="retail_status" style="margin:0;"  class="text-center">'.ucfirst($status->status).'</p>'; })
            ->addColumn('retail_source',function($data){
				if($data->source_merchant_id > 0){
					return '<p data-field="retail_source" style="margin:0;" class="text-center">'.$this->getMerchantName($data->source_merchant_id).'</p>';
				} else {
					return '<p data-field="retail_source" style="margin:0;" class="text-center"></p>';
				}
            })
            ->addColumn('retail_bought',function($data){
				if($data->rewarded_merchant_id > 0){	
					return '<p data-field="retail_bought" style="margin:0;" class="text-center">'.$this->getMerchantName($data->rewarded_merchant_id).'</p>';
				}else {
					return '<p data-field="retail_bought" style="margin:0;" class="text-center"></p>';
				}
            })
            ->addColumn('retail_consumed',function($data){
				if($data->redeemed_merchant_id > 0){
					return '<p data-field="retail_consumed" style="margin:0;" class="text-center">'.$this->getMerchantName($data->redeemed_merchant_id).'</p>';
				} else {
					return '<p data-field="retail_consumed" style="margin:0;" class="text-center"></p>';
				}
            })

            ->escapeColumns([])
            ->make(true);
	}

	function build_sorter($key) {
		return function ($a, $b) use ($key) {
			return strnatcmp($a[$key], $b[$key]);
		};
	}



	public static function getMerchantName($merchant_id)
	{
		if ($merchant_id == 0){
			return "-";

		} else {
			$merchant = Merchant::where('id', $merchant_id)->get();
			$company = Company::where('id', $merchant[0]['company_id'])->get();
			return $company[0]['name'];
		}
	}
}
