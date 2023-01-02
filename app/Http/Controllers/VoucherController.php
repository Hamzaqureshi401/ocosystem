<?php

namespace App\Http\Controllers;

use App\Models\locationterminal;
use App\Models\terminal;
use App\Models\voucherlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;
use \App\Classes\SystemID;
use \App\Models\product;
use \App\Models\voucher;
use Matrix\Exception;
use \App\Classes\UserData;
use \App\Models\merchantproduct;
use \App\Models\usersrole;
use \App\Models\role;
use \Illuminate\Support\Facades\Auth;
use App\Http\Requests\Inventory\Barcode\CreateBarcodeFromRangeValidator;
use Illuminate\Support\Carbon;
use App\Http\Functions;
use App\Models\SettingBarcodeMatrix;
use App\User;
use Milon\Barcode\DNS1D;
use Milon\Barcode\DNS2D;
use \App\Models\productcolor;
use \App\Models\prd_inventory;
use \App\Models\Merchant;
use \App\Models\opos_brancheod;
use \App\Models\opos_eoddetails;
use \App\Models\opos_itemdetails;
use \App\Models\opos_itemdetailsremarks;
use \App\Models\opos_receipt;
use \App\Models\opos_receiptdetails;
use \App\Models\opos_receiptproduct;
use \App\Models\opos_receiptproductspecial;
use \App\Models\locationproduct;
use \App\Models\merchantlocation;
use \App\Models\location;
use \App\Models\opos_locationterminal;
use \App\Models\opos_terminalproduct;
use \App\Models\opos_refund;
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
use \App\Models\voucherlistqty;
use Log;
use DB;


class VoucherController extends Controller
{
     protected $user_data;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('CheckRole:prod');
        $this->check_voucher_expire();

    }

    public function index()
    {
        $this->user_data = new UserData();
        $model = new voucher();

        $ids  = merchantproduct::where('merchant_id',
			$this->user_data->company_id())->pluck('product_id');
	
	$franchise_p_id = DB::table('franchiseproduct')->
			leftjoin('franchisemerchant',
				'franchisemerchant.franchise_id','=','franchiseproduct.franchise_id')->
			where([
				'franchisemerchant.franchisee_merchant_id' => $this->user_data->company_id(),
				'franchiseproduct.active' => 1
			])->
			whereNull('franchiseproduct.deleted_at')->
			get();
			
	$franchise_p_id->map(function($z) use ($ids) {
		$ids->push($z->product_id);
	});

        $ids  = product::where('ptype', 'voucher')->
			whereIn('id', $ids)->
			pluck('id');

        $data = $model->
			whereIn('product_id', $ids)->
            orderBy('created_at', 'desc')->latest()->get();
            
        return Datatables::of($data)
            ->addIndexColumn()
            ->addColumn('voucher_pro_id', function ($voucherList) {
                return '<p class="os-linkcolor" data-field="voucher_pro_id" style="cursor: pointer; margin: 0; text-align: center;"><a class="os-linkcolor" href="/landing/show-voucherproduct-view/'.$voucherList->product_name->systemid.'" target="_blank" style="text-decoration: none;">' . $voucherList->product_name->systemid . '</p>';
            })
            ->addColumn('voucher_pro_name', function ($voucherList) {
                if (!empty($voucherList->product_name->thumbnail_1)) {
                    $img_src = '/images/product/' . $voucherList->product_name->id . '/thumb/' . $voucherList->product_name->thumbnail_1;
                    $img     = "<img src='$img_src' data-field='voucher_pro_name' style=' width: 25px;height: 25px;display: inline-block;margin-right: 8px;object-fit:contain;'/>";
                } else {
                    $img = null;
                }

                return $img . '<p class="os-linkcolor" data-field="voucher_pro_name" style="cursor: pointer; margin: 0;display:inline-block">' . (!empty($voucherList->product_name->name) ? $voucherList->product_name->name : 'Product Name') . '</p>';
            })
            ->addColumn('voucher_sub', function ($voucherList) {

                return '<p data-field="voucher_sub" style="margin: 0;">' . (!empty($voucherList->product_category->name) ? $voucherList->product_category->name : '--') . '</p>';

            })
            ->addColumn('voucher_price', function ($voucherList) {
                return '<p class="os-linkcolor priceOutput" data-field="voucher_price" style="cursor: pointer; margin: 0;text-align: right;" >'.(!empty($voucherList->price) ? number_format(($voucherList->price/100),2): '0.00').'</p>';
            })
            ->addColumn('voucher_qty', function ($voucherList) {

                return '<p class="os-linkcolor qtyOutput" data-target="#voucherQtyModal" data-toggle="modal"  data-field="voucher_qty" style="cursor: pointer; margin: 0;">'.(!empty($voucherList->package_qty) ? $voucherList->package_qty : '0' ) .'</p>';

            })
            ->addColumn('voucher_cash_unit', function ($voucherList) {
                if($voucherList->type == 'cash'){
                    return '<p class="os-linkcolor unitOutput" data-target="#voucherUnitModal" data-toggle="modal" data-field="voucher_unit" style="cursor: pointer; margin: 0;">'.(!empty($voucherList->qty_unit) ? $voucherList->qty_unit : '0' ) .'</p>';
                } else {
                    return '-';
                }
            })
            ->addColumn('voucher_per_unit', function ($voucherList) {
                if($voucherList->type == 'pct'){
                    return '<p class="os-linkcolor unitOutput" data-target="#voucherUnitModal" data-toggle="modal" data-field="voucher_unit" style="cursor: pointer; margin: 0;">'.(!empty($voucherList->qty_unit) ? $voucherList->qty_unit : '0' ) .'</p>';
                } else {
                    return '-';
                }
            })
            ->addColumn('voucher_unit', function ($voucherList) {
                if($voucherList->type == 'qty'){
                    return '<p class="os-linkcolor unitOutput" data-target="#voucherUnitModal" data-toggle="modal" data-field="voucher_unit" style="cursor: pointer; margin: 0;">'.(!empty($voucherList->qty_unit) ? $voucherList->qty_unit : '0' ) .'</p>';
                } else {
                    return '-';
                }
            })
            ->addColumn('voucher_expiry', function ($voucherList) {
                $ex_date = (!empty($voucherList->expiry) ? date("dMy",strtotime($voucherList->expiry)) : '--' );
                $ex_date1 = "'".$ex_date."'";

                $today = new Carbon;
                if($ex_date == "--" || $voucherList->expiry >= $today){
                    return '<p class="os-linkcolor" data-field="voucher_expiry" style="cursor: pointer; margin: 0;" onclick=" show_dialog2('.$voucherList->id .','.$ex_date1.') ">'.$ex_date.'</p>';
                }

                return '<p data-field="voucher_expiry" disabled="disabled" style="margin: 0;" >'.$ex_date.'</p>';

            })
            ->addColumn('deleted', function ($voucherList) {
                $check_voucher = $this->check_voucher($voucherList->product_name->id);
                if($check_voucher == true ){
					return '<div><img class="" src="/images/redcrab_50x50.png"
						style="width:25px;height:25px;cursor:not-allowed;
						filter:grayscale(100%) brightness(200%)"/>
						</div>';

                } else {
					return '<div data-field="deleted"
						data-id-'.$voucherList->product_name->id.'
						class="remove">
						<img class="" src="/images/redcrab_50x50.png"
						style="width:25px;height:25px;cursor:pointer"/>
						</div>';
                }
            })
            ->escapeColumns([])
            ->make(true);
    }

    public function check_voucher($voucher_id)
    {
        $total = 0;
		//dd($voucher_id);
		$voucher = voucher::where('product_id', $voucher_id)->first();
		if($voucher->type == 'pct'){
			$voucherlist = voucherlist::join('prd_voucher','prd_voucher.id','=','voucherlist.voucher_id')
            ->where('prd_voucher.product_id', $voucher_id)
            ->where('voucherlist.qty_left','=',0)
            ->get();
		} else {
			$voucherlist = voucherlist::join('prd_voucher','prd_voucher.id','=','voucherlist.voucher_id')
            ->where('prd_voucher.product_id', $voucher_id)
            ->where('voucherlist.status','!=','pending')
            ->get();
		}
        
		$total = count($voucherlist);
        if($total > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function edit($id)
    {
        //
    }

    public function store(Request $request)
    {
        //Create a new product here
        try {
            $this->user_data = new UserData();
            $merchantproduct = new merchantproduct();
            $SystemID = new SystemID('product');
            $product  = new product();
            $vtype = $request->vtype;
            $product->systemid = $SystemID;
            $product->ptype    = 'voucher';
            $product->save();

            $voucher             = new voucher();
            $voucher->product_id = $product->id;
            $voucher->type = (!empty($vtype)) ? $vtype : 'qty' ;
            if($vtype == 'cash' || $vtype == 'pct'){
                $voucher->package_qty = 1;
            }
            $voucher->save();

            $merchantproduct->product_id = $product->id;
            $merchantproduct->merchant_id = $this->user_data->company_id();
            $merchantproduct->save();

            $msg = "Voucher added successfully";
            return view('layouts.dialog', compact('msg'));

        } catch (\Exception $e) {
            $msg = "Some error occured";
            return view('layouts.dialog', compact('msg'));
        }

    }

    public function showEditModal(Request $request)
    {
        try {
            $allInputs = $request->all();
            $id        = $request->get('id');
            $fieldName = $request->get('field_name');

            $validation = Validator::make($allInputs, [
                'id'         => 'required',
                'field_name' => 'required',
            ]);

            if ($validation->fails()) {
                $response = (new ApiMessageController())->
                    validatemessage($validation->errors()->first());

            } else {

                $voucher = voucher::where('id', $id)->first();
				$check_voucher = $this->check_voucher($voucher ->product_id);
				if($check_voucher == true ){
					return response()->json([
						'success' => 0
					]);					
				} else {
					return view('voucher.voucher-modals', compact(['id', 'fieldName', 'voucher']));
				}
            }

        } catch (\Illuminate\Database\QueryException $ex) {
            $response = (new ApiMessageController())->queryexception($ex);
        }
    }

    public function update(Request $request)
    {

        // $findVoucher = $request->get('voucher_id');
        // $checkActiveVoucher = voucher::where('id', $findVoucher)->
        //     whereNotNull('price')->
        //     whereNotNull('package_qty')->
        //     whereNotNull('qty_unit')->
        //     whereNotNull('expiry')->
        //     get();
        // if(count($checkActiveVoucher) > 0){
        //     return "is active";
        //     $vProductID = $checkActiveVoucher->product_id;
        //     $vgetMerchantID = merchantproduct::where("product_id" , $vProductID)->first();
        //     $vgetLocationID = merchant::find($vgetMerchantID->merchant_id);
        //     $vLocationID = $vgetLocationID->supplier_default_location_id;

        //     $type = "";
        //     if($checkActiveVoucher->type == "qty"){
        //         $type = "vquantity";
        //     }else if($checkActiveVoucher->type == "cash"){
        //         $type = "vcash";
        //     }else if($checkActiveVoucher->type == "pct"){
        //         $type = "vpercent";
        //     }

        //     $checkVoucherPresent = voucherproduct::where("voucher_id",$findVoucher)->where("location_id" , $vLocationID)->where("product_id" , $vProductID)->where($type , 1)->get();

        //     if(! (count($checkVoucherPresent) > 0 )){
        //         $voucherAdd = new voucherproduct();
        //         $voucherAdd->voucher_id = $findVoucher;
        //         $
        //     }

        //     //check if not present and add it
        //     //if not present do nothing
        // }
        // else{
        //     //check if present and delete it
        //     //if not present do nothinf
        // }

        try {
            $allInputs = $request->all();
            $voucher_id       = $request->get('voucher_id');
            $changed = false;
            Log::debug('Voucher ID'.$voucher_id);
            Log::debug('Date'.date('Y-m-d',strtotime($request->date)));
            $validation = Validator::make($allInputs, [
                'voucher_id' => 'required',
                'quantity'   => 'numeric',
                'unit'       => 'numeric'
            ]);
            $msg = '';
            if ($validation->fails()) {
                throw new Exception("product_not_found", 1);
            }

			$voucher = voucher::find($voucher_id);
			log::debug('voucher'.json_encode($voucher));

            if (empty($voucher)) {
                throw new Exception("product_not_found", 1);
            }

            if ($request->has('price')) {
                if ($voucher->price != $request->price) {
                    $voucher->price = (int) str_replace('.','',str_replace(',','',number_format($request->price,2)));
                    $changed = true;
                    $msg = "Price updated";
                }
            }

            if ($request->has('quantity')) {
                if (!is_numeric($request->quantity)) {
                    throw new Exception("invalid_quantity", 1);
                }

                if($voucher->type=='cash' || $voucher->type=='pct'){
                    if($voucher->package_qty == 1){
                        $msg = "Cannot change the quantity";

                    } else {
                        $voucher->package_qty = 1;
                        $changed = true;
                        $msg = "Changed the quantity";
                    }

                } else {
                    if ($voucher->package_qty != $request->quantity) {
                        $voucher->package_qty = (int) $request->quantity;
                        $changed = true;
                        $msg = "Quantity updated";
                    }
                }
            }

            if ($request->has('unit')) {
                if(is_null($voucher->package_qty)) {
                    $msg = "Package quantity not set";

                } else if(is_null($voucher->expiry)) {
                    $msg = "Please enter the expiry date first";

                }else{
                    //commented this out because qty limit should not be updated
                    //   if ($voucher->qty_unit != $request->unit)
                    log::debug('voucher_last'.json_encode($voucher));

                    if (is_null($voucher->qty_unit) ||
						$voucher->qty_unit == 0 ||
						is_null($voucher->product_name->name) ||
						is_null($voucher->subcategory_id)) {

                        for($i = 0; $i < (int) $request->unit; $i++){
                            $voucherList = new voucherlist();
                            $voucherList->voucher_id  = $voucher->id;
                            $voucherList->systemid = new SystemID('voucherlist');
                            $voucherList->qty_left = $voucher->package_qty;
                            $voucherList->staff_user_id = Auth::user()->id;

                            if($voucher->type == 'pct') {
                                $voucherList->status = 'active';

                            } else {
                                $voucherList->status = 'pending';
                            }

                            $voucherList->save();
                        }
                        $voucher->qty_unit = (int) $request->unit;
                        $changed = true;
                        $msg = "Unit updated";

                    } else {
                        $msg = "Cannot update, voucher has been fully defined";
                    }
                }   
            }

            if ($request->has('date')) {
                $date = date('Y-m-d',strtotime($request->date));
                $voucher->expiry = $date;
                $changed = true;
                $msg = "Date updated";
            }

            if ($changed == true) {
                log::debug('voucher_last0'.json_encode($voucher));
                $voucher->save();
				$response = view('layouts.dialog', compact('msg'));

            } else {
            	if(!empty($msg)) {
					$response = view('layouts.dialog', compact('msg'));
            	} else {
					$response= '';
            	}
            }

        }  catch (\Exception $e) {
            if ($e->getMessage() == 'product_not_found') {
                $msg = "Voucher not found";
            } else if ($e->getMessage() == 'invalid_cost') {
                $msg = "Invalid cost";
            }else if ($e->getMessage() == 'invalid_quantity') {
                $msg = "Invalid Quantity";
            }else if ($e->getMessage() == 'invalid_unit') {
                $msg = "Invalid Unit";
            } else {
                //$msg = "Some error occured";
                $msg = $e->getMessage();
            }

            // $msg = $e;
            $response = view('layouts.dialog', compact('msg'));

            Log::error(
                "Error @ " . $e->getLine() . " file " . $e->getFile() .
                ":" . $e->getMessage()
            );
        }
        return $response;
    }


    public function destroy($id)
    {

        try {
            $this->user_data = new UserData();
            $voucher    = voucher::find($id);
            $product_id = $voucher->product_id;
            $is_exist = merchantproduct::where(['product_id'=>$product_id,
				'merchant_id'=>$this->user_data->company_id()])->first();

            if (!$is_exist) {
                throw new Exception("Error Processing Request", 1);
            }

            $is_exist->delete();
            product::find($product_id)->delete();
            $voucher->delete();

            $msg = "Voucher deleted successfully";
            return view('layouts.dialog', compact('msg'));
        } catch (\Illuminate\Database\QueryException $ex) {
            $msg = "Some error occured";

            return view('layouts.dialog', compact('msg'));
        }
    }

    public function showVoucherView()
    {
        return view('voucher.voucher');
    }


    public function showVoucherProductView($systemid)
    {
		Log::debug('showVoucherProductView: '.$systemid);

        $id = Auth::user()->id;
        $user_roles = usersrole::where('user_id',$id)->get();

        $is_king =  \App\Models\Company::where('owner_user_id',
			Auth::user()->id)->first();

        $product = product::join('prd_voucher',
			'prd_voucher.product_id','=','product.id')->
			where('systemid', $systemid)->first();

		Log::debug('showVoucherProductView: '.json_encode($product));

        $this->user_data = new UserData();
        $merchant_id = $this->user_data->company_id();

        $merchant_product = DB::table('merchantproduct')->
        select('id')->
        where('merchant_id', '=', $merchant_id)->
        where('product_id', '=', $product->product_id)->
        first();

        $product_active = 0;
        $voucherproduct = voucherproduct::
			where('voucher_id',$product->product_id)->
			groupBy('product_id')->get();

        Log::debug('voucherproduct'.json_encode($voucherproduct));

        $barcode_sku = productbarcode::
			where('product_id',$product->product_id)->first();

        if(empty($barcode_sku)){
            $product_barcode = new productbarcode();
            $product_barcode->merchantproduct_id = $merchant_product->id;
            $product_barcode->product_id = $product->product_id;
            // $product_barcode->barcode = 1;
            $product_barcode->save();
        }

        $barcode_data = productbarcode::
			where('product_id',$product->product_id)->first();

        $barcode = DNS1D::getBarcodePNG(trim($systemid), "C128");

        if(count($voucherproduct) > 0) {
            $product_active = count($voucherproduct);
        }

        if ($is_king != null) {
            $is_king = true;
        } else {
            $is_king  = false;
        }

        return view('voucher.voucherproduct',
			compact(
				'user_roles',
				'is_king',
				'product',
				'systemid',
				'product_active',
				'barcode_sku',
				'barcode',
				'barcode_data'
			)
		);
    }


    public function update_barcode_promo(Request $request)
    {
        try {
            $id = Auth::user()->id;

            $barcode_id = $request->get('barcode_id');
            $promo = $request->get('promo');
            $productbarcode = productbarcode::where('id', $barcode_id)->first();
            $productbarcode->barcode = $promo;
            $productbarcode->save();

            $msg = "Promocode updated successfully";
            $data = view('layouts.dialog', compact('msg'));
        } catch (\Exception $e) {

            {
                $msg = "Error occured while Saving remarks";
            }

            Log::error(
                "Error @ " . $e->getLine() . " file " . $e->getFile() .
                ":" . $e->getMessage()
            );

            $data = view('layouts.dialog', compact('msg'));
        }
        return $data;
    }


    public function update_barcode_name(Request $request)
    {
        try {
            $id = Auth::user()->id;

            $barcodeid = $request->get('barcodeid');
            $barcode_name = $request->get('barcode_name');
            // log::debug();
            $productbarcode = productbarcode::where('id', $barcodeid)->first();
            $productbarcode->name = $barcode_name;
            $productbarcode->save();

            $msg = "Barcode name updated successfully";
            $data = view('layouts.dialog', compact('msg'));
        } catch (\Exception $e) {

            {
                $msg = "Error occured while saving barcode name";
            }

            Log::error(
                "Error @ " . $e->getLine() . " file " . $e->getFile() .
                ":" . $e->getMessage()
            );

            $data = view('layouts.dialog', compact('msg'));
        }
        return $data;
    }

    public function VoucherProductList($systemid)
    {
        $id = Auth::user()->id;
        $user_roles = usersrole::where('user_id',$id)->get();
        $this->user_data = new UserData();

        $is_king =  \App\Models\Company::where('owner_user_id',Auth::user()->id)->first();
        $voucher = product::join('prd_voucher','prd_voucher.product_id','=','product.id')->where('systemid', $systemid)->first();
        log::debug('voucher'.json_encode($voucher));

        $ids = merchantproduct::where('merchant_id',
        $this->user_data->company_id())->pluck('product_id');

        $inventory_data = product::where('ptype', 'inventory')->whereNotNull('name')->whereIn('id', $ids)->get();
        $product_active = 0;
        foreach ($inventory_data as $key => $value) {
            log::debug('product'.$value->id);
            log::debug('voucher'.$voucher->product_id);
            $active = 0;
            $voucherproduct = voucherproduct::where('product_id',$value->id)->where('voucher_id',$voucher->product_id)->get();
            log::debug('voucherproduct'.json_encode($voucherproduct));
            if(count($voucherproduct) > 0) {
                $active = 1;
                $product_active = 1;
            }
            $inventory_data[$key]->active = $active;
        }
        // if($voucher->type == 'cash' || $voucher->type == 'pct'){
        //     $product_active = 0;
        // }
          if ($is_king != null) {
              $is_king = true;
          } else {
              $is_king  = false;
          }

          return view('voucher.voucherproductlist',compact('user_roles','is_king','voucher','systemid','inventory_data','product_active'));
    }

    public function voucher_product_active(Request $request)
    {
        try {
            $voucher = product::join('prd_voucher','prd_voucher.product_id',
				'=','product.id')->where('systemid', $request->voucher_id)->first();
                    // log::debug(json_encode($voucher));
            if($voucher->type == 'cash' || $voucher->type == 'pct'){
                $product_data = $request->product_data;
                $type = $request->type;
                $num = $request->num;
                    // $col = $type.'_val';
                $prd_voucher = voucher::where('product_id',$voucher->product_id)->first();
                if($prd_voucher->type == $type){
                    if($type == 'cash'){
                        $prd_voucher->cash_val = $num;
                    }
                    if($type == 'pct') {
                        $prd_voucher->pct_val = $num;
                    }
                    $prd_voucher->save();
                }

                // log::debug('product_data'.json_encode($product_data));
                foreach ($product_data as $key => $value) {
                    $voucher_product = voucherproduct::where('voucher_id',
						$voucher->product_id)->
						where('product_id',$value['product_id'])->get();

                    log::debug('voucher_product='.json_encode($voucher_product));

                    if(count($voucher_product) <= 0){
                        if($value['key'] == 0){ continue; }
                        if($value['key'] == 1){
                            $prd_pref = new voucherproduct();
                            $prd_pref->product_id = $value['product_id'];
                            $prd_pref->voucher_id = $voucher->product_id;
                            $prd_pref->save();
                        }
                    } else {
                        if($value['key'] == 1){ continue; }
                        if($value['key'] == 0){
                            voucherproduct::where('voucher_id',
								$voucher->product_id)->
								where('product_id', $value['product_id'])->
								delete();
                        }
                    }
                }
            } else {
                $key = $request->key;
                $prd_id = $request->prd_id;
                $voucher_product = voucherproduct::where('voucher_id', $voucher->product_id)->get();
                log::debug($voucher_product);
                if(count($voucher_product) <= 0){
                    $prd_pref = new voucherproduct();
                    $prd_pref->product_id = $prd_id;
                    $prd_pref->voucher_id = $voucher->product_id;
                    $prd_pref->save();
                }
                // log::debug("Product activated successfully");
            }
            return 'true';

        } catch (\Exception $e) {
            log::debug("Product activation error".json_encode($e));
            $msg = $e;//"Some error occured";
            return 'false';
            // view('layouts.dialog', compact('msg'));
        }
    }

    public function search($id){
        try{
            $voucher_id = $id;
            $voucherList = voucherlist::where('systemid',$voucher_id)->where('status','!=', 'pending')->first();


            $voucherListPendingCheck = voucherlist::where('systemid',$voucher_id)->where('status', 'pending')->first();

            $voucherListExpiredCheck = voucherlist::where('systemid',$voucher_id)->where('status', 'expired')->first();


            if (!$voucherList) {
             //   throw new Exception("voucher_not_found", 1);
                if(!is_null($voucherListPendingCheck)){
                    return response()->json([
                    'success' => 0,
                    'message' => 'Voucher still pending for activation'
                ]);
                }

                return response()->json([
                    'success' => 0,
                    'message' => 'Voucher Not Found'
                ]);
            } else {

                $voucher = voucher::where('id',$voucherList->voucher_id)->where('type','qty')->first();
                $voucherPerOrCash = voucher::where('id',$voucherList->voucher_id)->where('type', "!=",'qty')->first();

                if(!$voucher){

                    if($voucherPerOrCash){
                        return response()->json([
                            'success' => 0,
                            'message' => 'Please use discount receipt level modal to execute'
                        ]);
                    }
                    return response()->json([
                        'success' => 0,
                        'message' => 'Voucher Not Found'
                    ]);
                } else {

                    if(!is_null($voucherListExpiredCheck)){
                            return response()->json([
                            'success' => 0,
                            'message' => 'This voucher has been expired'
                        ]);
                    }

                    $qr_code = bar_code_generator($voucher_id);
                    return response()->json([
                        'success' => 1,
                        'message' => 'Voucher Found',
                        'systemid'=> $voucher_id,
                        'voucher_id'=> $voucherList->voucher_id,
                        'product_id'=> $voucher->product_id,
                        'product_name' => $voucher->product_name->name,
                        'package_qty' => $voucher->package_qty,
                        'left' => $voucherList->qty_left,
                        'status' => ucfirst($voucherList->status) == "Fully_consumed" ? "Completed" : ucfirst($voucherList->status),
                        'qty_used' => $voucher->package_qty - $voucherList->qty_left,
                        'thumbnail' => $voucher->product_name->thumbnail_1,
                        'qr_code' => $qr_code
                    ]);
                }
            }
        }catch (\Exception $e) {
            Log::debug($e->getMessage());
        }
    }

    public function redeem(Request $request){
		try{
			$voucherList_id = $request->get('id');
			$quantity =   $request->get('quantity');
			$terminal_id = $request->get('terminal_id');

			$allInputs = $request->all();
			$redeemed = false;

			$validation = Validator::make($allInputs, [
			   'id'         => 'required',
			   'quantity'   => 'numeric',
			   'terminal'    => 'numeric'
			]);
			
			$product_format = [];

			if ($validation->fails()) {
				throw new Exception("invalid_input", 1);
			}

			$voucherList = voucherlist::where('systemid',$voucherList_id)->
				where('status','!=','pending')->first();
                log::debug('voucherList'.json_encode($voucherList));

			if (!$voucherList) {
			   throw new Exception("voucher_not_found", 1);
			}

            if ($voucherList->status == 'fully_consumed') {
                throw new Exception("fully_consumed", 1);
            }
            if ($voucherList->status == 'expired') {
                throw new Exception("expired_voucher", 1);
            }

           if ($request->has('quantity')) {

               if (!is_numeric($request->quantity))
               {
                   throw new Exception("invalid_quantity", 1);
               }

               if (date(now()) > date($voucherList->voucher->expiry))
               {
                   throw new Exception("expired_voucher", 1);
               }

				Log::debug('Quantity'.$quantity);
				Log::debug('voucherList->qty_left'.$voucherList->qty_left);
				Log::debug('voucherList->id'.$voucherList->id);

				if ($voucherList->qty_left >= $quantity) {
                   $new_quantity = $voucherList->qty_left - $quantity;
                   $voucherList->qty_left = (int) $new_quantity;
                   if($new_quantity == 0) {
                       $voucherList->status = 'fully_consumed';
                   }
                   //Sort out the location
                   $terminal = terminal::where('systemid',$terminal_id)->value('id');
                   log::debug('terminal'.$terminal);
                   if (!$terminal) {
                       throw new Exception("terminal_not_found", 1);
                   }
                   $location_id = locationterminal::where('terminal_id',$terminal)->value('location_id');

                    $voucher_data=voucher::where('id',$voucherList->voucher_id)->first();
                    $voucherlistqty = new voucherlistqty();
                    $voucherlistqty->voucherlist_id = $voucherList->id;
                    $voucherlistqty->quantity = $quantity;
                    $voucher_product = array();
                    if($voucher_data->type != 'cash' && $voucher_data->type != 'pct'){
                        log::debug('$voucher_data'.json_encode($voucher_data));
                        $voucher_product = voucherproduct::where('voucher_id', $voucher_data->product_id)->first();
                        log::debug('voucher_product'.json_encode($voucher_product));
                        if($voucher_product) {
							$product_id = $voucher_product->product_id;
							$locationqty = app('App\Http\Controllers\InventoryController')->location_productqty($product_id, $location_id);
							$myproduct = product::where('id', $product_id)->first();
							//dd($locationqty);
							if($locationqty < $quantity){
									$item['row_index'] = 0;
									$item['id'] = $myproduct->id;
									$item['ptype'] = $myproduct->ptype;
									$item['type'] = $myproduct->ptype;
									$item['qty'] = 1;
									$item['price'] = 0;
									$item['amount'] = 0;
									$item['mock_price'] = 0;
									$item['discount'] = 0;
									$item['discount_pck'] = 0;
									$item['mock_sst'] = 0;
									$item['mock_sc'] = 0;
									$item['mock_discount'] = 0;
									$item['sc'] = 0;
									$item['sst'] = 0;
									$item['assoc_row_id'] = 0;
									$item['tot_number'] = 0;	
									array_push($product_format,$item);
								throw new Exception("insufficient_qty", $product_id);
							}
                            $prd_pref = new voucherproduct();
                            $prd_pref->vquantity = $quantity;
                            $prd_pref->product_id = $voucher_product->product_id;
                            $prd_pref->voucher_id = $voucher_product->voucher_id;
                            $prd_pref->location_id = $location_id;
                        }
                    }
					log::debug('location_id'.$location_id);
					$voucherList->location_id = $location_id;
					$voucherlistqty->location_id = $location_id;
					$redeemed = true;
					$msg = "Redeemed successfully";

				} else {
				   throw new Exception("exceed_limit", 1);
				}
			}

			if ($redeemed == true) {
                log::debug(json_encode($voucherList));
                if($voucher_product){
                    $prd_pref->save();
                }
                if($voucherlistqty){
                    $voucherlistqty->save();
                }
			   $voucherList->save();
			   $response = view('layouts.purpledialog', compact('msg'));

			} else {
			   $response  = null;
			}

		} catch (\Exception $e) {
           Log::debug($e->getMessage());

           if ($e->getMessage() == 'invalid_input') {
               $msg = "Please enter redemption quantity";
           } else if ($e->getMessage() == 'voucher_not_found') {
               $msg = "Voucher not found";
           }else if ($e->getMessage() == 'invalid_quantity') {
               $msg = "Invalid Quantity";
           }else if ($e->getMessage() == 'expired_voucher') {
               $msg = "This voucher has been expired";
           }else if ($e->getMessage() == 'terminal_not_found') {
               $msg = "Terminal not found";
           }else if ($e->getMessage() == 'fully_consumed') {
               $msg = "Voucher fully consumed";
           }else if ($e->getMessage() == 'exceed_limit') {
			   $msg = "Qty limit exceeded current limit, please try again";
           }else if ($e->getMessage() == 'insufficient_qty') {
			   $msg = "Quantity to be redeemed is larger than available product";
		   } else {
               $msg = "Some error occured";
           }
           $data["msg"] = $msg;
           $data["color"] = "rgba(0,0,255,0.5)";
           $response = view('layouts.dialog', $data);
		}

        return $response;
    }

    public function check_voucher_type(Request $request)
    {
        try{
            $promo_code = $request->promo_code;
            // $voucher_id = $id;
            $voucherList      = voucherlist::where('systemid',$promo_code)->where('status','active')->first();
			$now = \Carbon\Carbon::now()->format('Y-m-d');
            //level1a
            if (!$voucherList) {

                $barcode = productbarcode::select('prd_voucher.*')->join('prd_voucher','productbarcode.product_id','=','prd_voucher.product_id')->where('productbarcode.barcode',$promo_code)->where('prd_voucher.type','!=','qty')->first();

                $CheckBarcodeQty = productbarcode::select('prd_voucher.*')->join('prd_voucher','productbarcode.product_id','=','prd_voucher.product_id')->where('productbarcode.barcode',$promo_code)->where('prd_voucher.type','qty')->first();

                //log::debug('voucherList'.json_encode($barcode));
                //level2a
                if(!$barcode) {
                    $voucherList3      = voucherlist::where('systemid',$promo_code)->first();
                    //level3a
                    if(!$voucherList3) {
						Log::debug('voucherList3='.json_encode($voucherList3));

                        $msg = 'Invalid voucher..';
                        return view('layouts.purpledialog', compact('msg'));
                    }
                    //level3a
                    else {
                        //level 4a
                        if($voucherList3->status == 'pending') {
                            $msg = 'Voucher has not been bought';
                            return view('layouts.purpledialog', compact('msg'));
                        }
                        //level 4a
                        else if($voucherList3->status == 'expired') {
                            $msg = 'This voucher has been expired';
                            return view('layouts.purpledialog', compact('msg'));
                        }
                        //level 4a
                        else {
                            $msg = 'This voucher has been redeemed';
                            return view('layouts.purpledialog', compact('msg'));
                        }
                    }

                //level2b
                } else {
                    $voucherList2 = voucherlist::where('voucher_id',$barcode->id)->where('status','active')->first();
                    //log::debug('voucherListw'.json_encode($voucherList2));
                    //level3b
                    if(!$voucherList2) {
                        $voucherList4 = voucherlist::where('systemid',$barcode->id)->first();
                        //level4b
                        if(!$voucherList4) {
							Log::debug('voucherList4='.json_encode($voucherList4));
                            $msg = 'Invalid voucher..';
                            return view('layouts.purpledialog', compact('msg'));
                        }
                        //level4b
                        else {
                            //level5b
                            if($voucherList4->status == 'pending') {
                                $msg = 'Voucher has not been bought';
                                return view('layouts.purpledialog', compact('msg'));
                            }
                            //level5b
                            else if($voucherList4->status == 'expired') {
                                $msg = 'This voucher has been expired';
                                return view('layouts.purpledialog', compact('msg'));
                             }
                            //level5b
                            else {
                                $msg = 'This voucher has been redeemed';
                                return view('layouts.purpledialog', compact('msg'));
                            }
                        }
                    }
                    //level3b
                    else {
                        $voucher = voucher::where('id',$barcode->id)->where('type','!=','qty')->first();
                        //level4b
                        if(!$voucher){
							Log::debug('voucher='.json_encode($voucher));
                            $msg = 'Invalid voucher..';
                            return view('layouts.purpledialog', compact('msg'));
                        }
                        //level4b
                        else {
                            $voucherproduct = voucherproduct::where('voucher_id',$voucher->product_id)->groupBy('product_id')->pluck('product_id');
                            return response()->json([
                                'voucher_type' => $voucher->type,
                                'cash_val' => $voucher->cash_val,
                                'pct_val' => $voucher->pct_val,
                                'voucherproduct' => $voucherproduct,
                            ]);
                        }
                    }
                }
                //level2c
				Log::debug('voucherList='.json_encode($voucherList));
				$msg = 'Invalid voucher..';
				return view('layouts.purpledialog', compact('msg'));
            }
            //level1b
            else {
				Log::debug("VOUCHER");
                $voucher = voucher::where('id',$voucherList->voucher_id)->where('type','!=','qty')->first();

                $voucherCheckQty = voucher::where('id',$voucherList->voucher_id)->where('type','qty')->first();
                //level2b
				Log::debug("VOUCHER1 " .$voucherList);
				Log::debug("Date " .$now);
                if(!$voucher){
                        if($voucherCheckQty){
                            $msg = 'Please use qty redemption modal to execute';
                            return view('layouts.purpledialog', compact('msg'));
                        }
                        $msg = 'Invalid voucher..';
                        return view('layouts.purpledialog', compact('msg'));
                }
				if(!is_null($voucher)){
					if($voucher->expiry < $now) {
						Log::debug("HOLAAAAA");
						//Ldd("HOLA");
						voucherlist::where('voucher_id',$voucherList->voucher_id)->where('status', '!=','fully_consumed')->
							update(['status' => 'expired']);  
						$msg = 'This voucher has been expired';
						return view('layouts.purpledialog', compact('msg'));
							  
					}
				}
				
				if(!is_null($voucherCheckQty)){
					if($voucherCheckQty->expiry < $now) {
						Log::debug("HOLAAAAA");
						//Ldd("HOLA");
						voucherlist::where('voucher_id',$voucherList->voucher_id)->where('status', '!=','fully_consumed')->
							update(['status' => 'expired']);  
						$msg = 'This voucher has been expired';
						return view('layouts.purpledialog', compact('msg'));
							  
					}
				}				
                //level2b
                else {
                    $voucherproduct = voucherproduct::where('voucher_id',$voucher->product_id)->groupBy('product_id')->pluck('product_id');
                    return response()->json([
                        'voucher_type' => $voucher->type,
                        'cash_val' => $voucher->cash_val,
                        'pct_val' => $voucher->pct_val,
                        'voucherproduct' => $voucherproduct,
                    ]);
                }
            }
        }catch (\Exception $e) {
            Log::debug($e->getMessage());
        }

    }

    public function check_voucher_used($promo_code)
    {
        try{
            // check if voucher is SYSTEMID
            $voucherList      = voucherlist::where('systemid',$promo_code)->where('status','active')->first();
            if($voucherList) {
                return $voucherList;
            } else {
                $barcode = productbarcode::select('prd_voucher.*')->join('prd_voucher','productbarcode.product_id','=','prd_voucher.product_id')->where('productbarcode.barcode',$promo_code)->where('prd_voucher.type','!=','qty')->first();
                //log::debug('voucherList'.json_encode($barcode));
                if($barcode) {
                    $voucherList2 = voucherlist::where('voucher_id',$barcode->id)->where('status','active')->first();
                    if($voucherList2) {
                        return $voucherList2;
                    }
                }
                return false;
            }
        }catch (\Exception $e) {
            Log::debug($e->getMessage());
        }

    }

    public function check_voucher_expire()
    {
        try{
            $now = Carbon::now();
            $voucherlist = voucherlist::where('status', '!=', 'expired')->get();
            foreach ($voucherlist as $v) {
                if (($now >= $v->voucher->expiry) && ($v->status != 'fully_consumed')) {
                    $v->status = 'expired';
                    $v->update();
                }
            }

        }catch (\Exception $e) {
            Log::debug($e->getMessage());
        }

    }
}
