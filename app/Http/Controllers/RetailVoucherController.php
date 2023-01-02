<?php

namespace App\Http\Controllers;
use \App\Models\usersrole;
use \App\Models\role;
use App\Models\voucher;
use App\Models\voucherlist;
use \Illuminate\Support\Facades\Auth;
use \App\Classes\UserData;
use Illuminate\Http\Request;
use \App\Models\merchantproduct;
use \App\Models\product;
use Log;
use \App\Models\voucherlistqty;
use \App\Models\voucherproduct;
use App\Models\OposPvoucher;
use App\Models\Merchant;

class RetailVoucherController extends Controller {

    public function __construct() {
        $this->middleware('auth');
    }

    public function index() {
		
        $this->user_data = new UserData();
        $model = new voucher();
		
        $ids  = merchantproduct::where('merchant_id',
			$this->user_data->company_id())->
			pluck('product_id');

        $ids  = product::where('ptype', 'voucher')->
			whereNotNull('name')->
			whereNotNull('photo_1')->
			whereIn('id', $ids)->
			pluck('id');

        $vouchers = $model->whereIn('product_id', $ids)->
		     whereNotNull('price')->
		    whereNotNull('package_qty')->
		    whereNotNull('qty_unit')->
            whereNotNull('expiry')->
            whereNotNull('subcategory_id')->
            where('subcategory_id', '!=', 0)->
			orderBy('created_at', 'desc')->
			latest()->
            get();
	//	dd($vouchers);
        $opospVoucher = OposPvoucher::with('user')->
			orderBy('created_at', 'desc')->
			whereHas('user', function($query){
				$query->whereHas('staff', function($q) {
                $q->where('company_id', Auth::user()->staff->company_id);
            });
        })->get();

		Log::debug('vouchers = '.json_encode($vouchers));

        foreach ($vouchers as $key => $value) {

            $this->voucherValidateData($value->product_name, $value->product_category);

            $active = 0;

			Log::debug('key='.$key.', product_id='.$value->product_id);

            $voucherproduct = voucherproduct::where('voucher_id',
				$value->product_id)->get();

            if(count($voucherproduct) > 0) {
                $active = 1;
            }
            $vouchers[$key]->active = $active;
        }

        foreach ($opospVoucher as $key => $value) {
            $active = 0;

			$p = $value['platform'];

			if ($p == 'aliexpress') {
				$value['platform'] = 'AliExpress';
			} else {
				$value['platform'] = ucfirst($p);
			}

			//Log::debug('key='.$key.', p='.$p.', value='.json_encode($value));

            $opospVoucher[$key]->active = $active;

			Log::debug('opospVoucher['.$key.']='.
				json_encode($opospVoucher[$key]));
        }
        
        // $merged = $vouchers->merge($opospVoucher);
        // $vouchers = $merged->all();
        // dd($vouchers);
        
		$user_id = Auth::user()->id;

		$user_roles = usersrole::where('user_id',$user_id)->get();		
		$is_king =  \App\Models\Company::where('owner_user_id',Auth::user()->id)->first();
		
        return view('retail_voucher.retail_voucher',
			compact('vouchers', 'opospVoucher', 'user_roles', 'is_king'));
    }

    public function voucherValidateData($product_name, $product_category)
    {
        if ( !is_null($product_name) || !is_null($product_category) ) {
            if (!is_null($product_name->name) && !is_null($product_name->photo_1) && !is_null($product_category->category_id) ) {
                return true;
            } else {
                return false;
            }
        }

        return false;
    }
    
    public function voucherList($id) {
        $user_id = Auth::user()->id;
	$user_data = new UserData();
	$user_data->exit_merchant();

	$user_roles = usersrole::where('user_id',$user_id)->get();

	$is_king =  \App\Models\Company::where('owner_user_id',Auth::user()->id)->first();

    // $vouchers = voucher::where('id',$id)->get()
    $product = voucher::where('prd_voucher.id',$id)->
		join('product','product.id','=','prd_voucher.product_id')->first();

    log::debug('product'.json_encode($product));
    $now = \Carbon\Carbon::now()->format('Y-m-d');
	
    if($product->expiry < $now) {
		//if*(
        voucherlist::where('voucher_id',$id)->where('status', '!=','fully_consumed')->
			update(['status' => 'expired']);        
    }
    $vouchers = voucherlist::where('voucher_id',$id)->get();

	if ($is_king != null) {
		$is_king = true;	
	} else {
		$is_king  = false;
	}

	if (!$user_data->company_id()) {
		abort(404);
	}

        return view('retail_voucher.retail_voucher_list', compact('user_roles','is_king','vouchers','product'));
    }

    public function voucherLedgerList($id){
        try{
            $voucherList      =  voucherlist::select('voucherlistqty.created_at as last_update','location.*','voucherlistqty.*','voucherlist.*','staff.*')->join('voucherlistqty','voucherlistqty.voucherlist_id','=','voucherlist.id')->join('staff','staff.user_id','=','voucherlist.staff_user_id')->leftjoin('location','location.id','=','voucherlistqty.location_id')->where('voucherlist.id',$id)->get();
                $response = view('retail_voucher.voucher_ledger', compact('voucherList'));

        } catch (\Exception $e) {
            $response = null;
        }
        return $response;
    }
	
	public function guide() {
		
        $this->user_data = new UserData();
        $guides = [];
		$user_id = Auth::user()->id;
		$user_roles = usersrole::where('user_id',$user_id)->get();		
		$is_king =  \App\Models\Company::where('owner_user_id',Auth::user()->id)->first();
		
        return view('retail_voucher.retail_voucher_guide',
			compact('guides', 'user_roles', 'is_king'));
    }
}