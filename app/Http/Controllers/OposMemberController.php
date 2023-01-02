<?php

namespace App\Http\Controllers;

use App\Classes\UserData;
use App\Models\Company;
use App\Models\Merchant;
use App\Models\opos_loyaltyptslog;
use App\Models\usersrole;
use App\Models\opos_loyaltyproductredemption;
use App\Models\opos_member;
use \App\Models\locationterminal;
use \App\Models\locationproduct;
use \App\Models\terminal;
use \App\Models\FranchiseMerchantLocTerm;
use App\Classes\SystemID;
use App\Models\opos_receipt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator as Validator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use Matrix\Exception;
use Illuminate\Http\Request;

class OposMemberController extends Controller
{
    protected $user_data;
    public function saveMember(Request $request){
	    
	$user_id = Auth::user()->id;
	
	$user_data = new UserData();

        $staff = DB::table('staff')->select('company_id')->where('user_id',$user_id)->first();
	
	$allData=$request->all();
	
	$validation = Validator::make($allData, [
                'member_name'  => 'required',
                'member_nric'  => 'required',
                'member_mobileno'  => 'required',
                'member_c_mobileno' => 'required',
        ]);
	$terminal = DB::table('opos_terminal')->
		where('systemid',	$request->terminal_id)->
		first();

	 $is_franchise = FranchiseMerchantLocTerm::select('company.id','company.systemid')->
		join('franchisemerchantloc', 'franchisemerchantloc.id', '=',
			'franchisemerchantlocterm.franchisemerchantloc_id')->
		join('franchisemerchant','franchisemerchant.id','=',
			'franchisemerchantloc.franchisemerchant_id')->	
		join('franchise','franchise.id','=', 'franchisemerchant.franchise_id')->
		join('merchant', 'franchise.owner_merchant_id', '=','merchant.id')->	
		join('company', 'merchant.company_id', '=','company.id')->			
		where(
			['franchisemerchantlocterm.terminal_id' => $terminal->id ],
		)->
		first();
	
	if (!empty($is_franchise)) {
		$merchant_id = $is_franchise->id;
	} else {
		$merchant_id = $user_data->company_id();
	}

	$selectNric = DB::table('opos_member')->
		where('nric',$allData['member_nric'])->
		where('merchant_id', $merchant_id)->
		first();
	$msg = 'Nric already exist';


	$selectMobile = DB::table('opos_member')->
			where('mobile',$allData['member_mobileno'])->
			where('merchant_id', $merchant_id)->
			first();
        $msg = 'Mobile Number already exist';
            if ($validation->fails()) {
                 return (new ApiMessageController())->validatemessage($allData);
            }else if ($selectNric!=null){
                return (new ApiMessageController())->uniqueresponse("Nric already exist");
            }else if($selectMobile != null){
                return (new ApiMessageController())->uniqueresponse("Mobile No exist");
            }
            else{
       
                $sysid=new SystemID('member');
                $opos_member=new opos_member();
                $opos_member->nric=$allData['member_nric'];
                $opos_member->systemid=''.$sysid->__toString();
                $opos_member->merchant_id= $merchant_id;//$staff->company_id;;
                $opos_member->loyaltypts=0;
                $opos_member->membershipmts=0;
                $opos_member->name=$allData['member_name'];
                $opos_member->mobile=$allData['member_mobileno'];
                $opos_member->terminal_id=$allData['terminal_id'];
                $opos_member->location_id=0;
                if($opos_member->save()){
                     return (new ApiMessageController())->saveresponse("Saved");
                }else{
                    return (new ApiMessageController())->failedresponse("There is some issues please try again.");
                }
            }
    }

    public function getPointRedemption($id){
		$user_id = Auth::user()->id;
		$user = DB::table('users')->where('id',$user_id)->first();
		$user_roles = usersrole::where('user_id',$user_id)->get();
        $is_king =  Company::where('owner_user_id',Auth::user()->id)->first();
        $company = DB::table('company')->where('owner_user_id',$user_id)->get();
	    $redemptions = DB::table('product_pts_redemption')
        ->select('product_pts_redemption.*','product.name','product.thumbnail_1', 'product.id as prod_id', 'product.systemid as prdsystemid', 
		'opos_member.name as member_name', 'location.branch as location', 'users.name as name_user_staff',
		'location.systemid as locationid', 'staff.systemid as staffid')
        ->join('product', 'product_pts_redemption.product_id','=','product.id')
        ->join('opos_member', 'product_pts_redemption.member_id','=','opos_member.id')
        ->leftjoin('users', 'product_pts_redemption.staff_user_id','=','users.id')
        ->leftjoin('staff', 'staff.user_id','=','users.id')
        ->leftjoin('location', 'location.id', '=', 'product_pts_redemption.location_id')
        ->where('product_pts_redemption.systemid', $id)
        ->get();
        // dd($redemptions,$user );
        
        $redemptionDate = "";
        $redemptionID = "";
        $redemptionLocation = "";
        $nameUser = "";

        if(!empty($redemptions) && count($redemptions) > 0 ) {
            $redemptionDate = date('dMy H:i:s',
				strtotime($redemptions[0]->created_at)) ;
            $redemptionID = $redemptions[0]->systemid;
            $redemptionLocation = $redemptions[0]->location;
            $redemptionLocationId = $redemptions[0]->locationid;
            $nameUser =  $redemptions[0]->name_user_staff;
            $nameUserId =  $redemptions[0]->staffid;

        } else {
            $redemptions = [];
        }

		return view('retail_membership.product_redemption_detail',
			compact('user_roles','is_king', 'redemptions', 'user',
			'redemptionDate', 'redemptionID', 'redemptionLocation',
			'nameUser', 'redemptionLocationId', 'nameUserId' ));
	}

	
    public function PointRedemption($member_id, $location_id){
        $user_id = Auth::user()->id;
		$user_roles = usersrole::where('user_id',$user_id)->get();
        $is_king =  Company::where('owner_user_id',Auth::user()->id)->first();
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

        $validity = date('Y-m-d');
		//dd($validity);
        $data = DB::table('opos_loyaltyproductredemption')
        ->select('opos_loyaltyproductredemption.id',
        'opos_loyaltyproductredemption.product_id',
        'opos_loyaltyproductredemption.redemption_lpts',
        'opos_loyaltyproductredemption.status',
//        'locationproduct.quantity',
        'merchantproduct.merchant_id',
        'product.thumbnail_1',
        'product.systemid',
        'product.id as product_id',
        'product.name'
        )
        ->where('opos_loyaltyproductredemption.status', 'active')
        ->where('opos_loyaltyproductredemption.redemption_lpts', '>', 0)
        ->where('opos_loyaltyproductredemption.validity', '>=', $validity)
        ->where('merchantproduct.merchant_id', $merchant_id)
    //    ->where('locationproduct.quantity', '>', 0)
        ->join('product', 'opos_loyaltyproductredemption.product_id', '=', 'product.id')
     //   ->join('locationproduct', 'product.id','=','locationproduct.product_id')
        ->join('merchantproduct','product.id','=','merchantproduct.product_id')
        ->groupBy(['product.id'])
        ->get();
      //  dd($data);
        $yy = 0;
        foreach($data as $id => $dd){
            $dd->quantity = app('App\Http\Controllers\InventoryController')->location_productqty($dd->product_id, $location_id);
       //     dd($location_id);
           /* if($dd->quantity == 0){
               unset($data[$id]);
            }*/
           // $yy++;
        }

        $get_expired = DB::table('opos_loyaltyptslog')->
			where('member_id', $member_id)->
			where('expiry', '<=', date('Y-m-d H:i:s'))->
			where('status', '!=', 'expired')->get();

        if(!empty($get_expired) || count($get_expired) >= 1){
            foreach($get_expired as $expired){

                $update_status = DB::table('opos_loyaltyptslog')->
					where('id',$expired->id)->
					update(['status' => 'expired']);

                $get_member_lpt = DB::table('opos_member')->
					where('id', $expired->member_id)->
					first();

                if($get_member_lpt->loyaltypts > 0 ||
					$expired->lpts < $get_member_lpt->loyaltypts){

                    $new_point =
						($get_member_lpt->loyaltypts) - ($expired->lpts);

					$update_member_lpt = DB::table('opos_member')->
						where('id',$id)->
						update(['loyaltypts' => $new_point]);
                }
            }
        }

        $check = DB::table('opos_member')
            ->where('id', $member_id)
            ->first();
    
		return view('opossum.petrol_station.product_redemption.product_redemption',
			compact('user_roles','is_king','location_id'))->
			with('data', $data)->
			with('user', $check);
    }


    public function validateMember(Request $request){
        $user_id = Auth::user()->id;
		$user_roles = usersrole::where('user_id',$user_id)->get();
        $is_king =  Company::where('owner_user_id',Auth::user()->id)->first();
        $nric = $request->nric;
        $mobile = $request->mobile;
        $location_id = $request->location_id;
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

        $get_member = DB::table('opos_member')
        ->where('nric',$nric)
        ->where('mobile', $mobile)
        ->where('merchant_id', $merchant_id)
        ->first();
        $yy = 0;

        if(is_null($get_member)){
			return response()->json(false);
		} else {
            return response()->json(['member_id' => $get_member->id, 'location_id' => $location_id]);
        }
          //  return back();
    }

public static function getLatestLocationQuantity($location_id, $product_id){
    $latestQuantity = DB::table('locationproduct')
    ->where('location_id', $location_id)
    ->where('product_id', $product_id)
    ->orderBy('created_at', 'desc')->first();
}

public function searchMember(Request $request, opos_member $nric)
    {
        $allRequest = $request->all();
        $nricNew = $nric->newQuery();
        $validation = Validator::make($allRequest, [
            'nric'  => 'required',
            'points'  => 'required',
            'company_id'  => 'required',
	    'terminal_id' => 'required'
        ]);
        if ($validation->fails()) {
            return (new ApiMessageController())->validatemessage($allRequest);
        }
	
	$terminal_id = $request->terminal_id;
	$merchant_id = DB::table('opos_locationterminal')->
		join('merchantlocation','merchantlocation.location_id',
			'=','opos_locationterminal.location_id')->
		where('opos_locationterminal.terminal_id',$terminal_id)->
		first()->merchant_id;
	
	if ($request->has('nric'))
        {
            $nricNew = $nric->where('nric', $request->input('nric'))->where('company.id', $merchant_id)
                            ->join('merchant', 'opos_member.merchant_id','=','merchant.id')
                            ->join('company', 'company.id','=','merchant.company_id')
                            ->select('opos_member.*');
        }
        return $nricNew->first();
    }
    public function saveLoyalty(Request $request)
    {
        $this->user_data = new UserData();

        $ids = Merchant::join('company','company.id', '=', 'merchant.company_id')
            ->where('merchant.id',$this->user_data->company_id())->pluck('merchant.id');

        $nric = $request->nricing;
        $loyalty = $request->points;
        $staff_id = $request->staff_id;
        $member_id = $request->member_id;
        $receipt_id = $request->receipt_id;

        $dbPoints = DB::table('opos_member')->
			where('nric', $nric)->
			where('loyaltypts', '>=',0)->
			get()->toArray();

        if ($dbPoints != null) {

            $save = DB::table('opos_member')->where('nric', $nric)
            ->update(['loyaltypts' => DB::raw('loyaltypts + '.$loyalty)]);
            if ($save)
            {
                $system_id = new SystemID('loyaltypts');
                $this->saveTransactionLogs($system_id->__toString(), $staff_id, $member_id,$loyalty,$ids[0],$ids[0],0,$receipt_id);
                return "Successfully";
            }
        }
    }


	public function saveMemberMtsPoints(Request $request){
        $allInputs=$request->all();
               $validation = Validator::make($allInputs, [
                'mobile'  => 'required',
                'mts'  => 'required',
                'nric'  => 'required'
            ]);     
            if ($validation->fails()) {
                 return (new ApiMessageController())->validatemessage($allInputs);
            }else{
                $opos_member=opos_member::where("nric",$allInputs['nric'])->where("mobile",$allInputs['mobile'])->first();
                if(!empty($opos_member)){
                    $opos_member->membershipmts=intval($allInputs['mts'])+$opos_member->membershipmts;
                    if($opos_member->save()){
                         return (new ApiMessageController())->saveresponse("Saved successfully");
                    }else{
                        return (new ApiMessageController())->failedresponse("There is some issues pleas try again."); 
                    }
                }else{
                    return (new ApiMessageController())->failedresponse("Please Register Member first");
                }
            } 
    }

    public static function saveTransactionLogs(
        $systemid, 
        $staff_user_id, 
        $member_id, $lpts, 
        $source_merchant_id, 
        $rewarded_merchant_id, 
        $redeemed_merchant_id,
        $receipt_id, $status = 'earned')
    {
        //calculate-expiry

        $get_expiry = DB::table('opos_loyaltyproductredemption')
            ->select('point_expiry_period')
            ->groupBy('product_id')
            ->first();

		switch($get_expiry->point_expiry_period ?? null){
			case '1m':
				$expiry = date('Y-m-d H:i:s', strtotime('+1 month'));
				break;
			case '1y':
				$expiry = date('Y-m-d H:i:s', strtotime('+1 year'));
				break;
			case '2y':
				$expiry = date('Y-m-d H:i:s', strtotime('+2 years'));
				break;
			default:
				$expiry = date('Y-m-d H:i:s', strtotime('+1 year'));
		}

		$saveLogs = new opos_loyaltyptslog();
		$saveLogs->systemid = $systemid;
		$saveLogs->staff_user_id = $staff_user_id;
		$saveLogs->member_id = $member_id;
		$saveLogs->lpts = $lpts;
		$saveLogs->receipt_id = $receipt_id;
		$saveLogs->source_merchant_id = $source_merchant_id;
		$saveLogs->rewarded_merchant_id = $rewarded_merchant_id;
		$saveLogs->redeemed_merchant_id = $redeemed_merchant_id;
		$saveLogs->status = $status;
		$saveLogs->expiry = $expiry;
		$saveLogs->save();
    }


    public static function checkReceiptId($receipt_id)
    {
        $getReceiptId = opos_loyaltyptslog::where('receipt_id',$receipt_id)->get();
        if (count($getReceiptId)) {
            return $getReceiptId;
        }
        return;
    }


	/*
    public static function saveMemberTransactionLogs(
		$systemid, $staff_user_id, $member_id, $lpts,
		$source_merchant_id, $rewarded_merchant_id, $redeemed_merchant_id)
    {

        $saveLogs = new opos_loyaltyptslog();
        $saveLogs->systemid = $systemid;
        $saveLogs->staff_user_id = $staff_user_id;
        $saveLogs->member_id = $member_id;
        $saveLogs->lpts = $lpts;
        $saveLogs->source_merchant_id = $source_merchant_id;
        $saveLogs->rewarded_merchant_id = $rewarded_merchant_id;
        $saveLogs->redeemed_merchant_id = $redeemed_merchant_id;
        $saveLogs->save();
    }
	*/
}
