<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Matrix\Exception;
use Yajra\DataTables\DataTables;
use \App\Classes\SystemID;
use \App\Classes\UserData;
use \App\Models\merchantproduct;
use \App\Models\product;
use \App\Models\rawmaterial;
use \Illuminate\Support\Facades\Auth;
use \App\Models\usersrole;
use \App\Models\role;

use \App\Models\productcolor;
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
use \App\Models\SettingBarcodeMatrix;
use Milon\Barcode\DNS1D;

use \App\Models\warranty;
use Log;


class RawMaterialController extends Controller
{
    protected $user_data;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('CheckRole:prod');
    }

    public function index()
    {

        $this->user_data = new UserData();
        $model           = new rawmaterial();

        $ids  = merchantproduct::where('merchant_id', $this->user_data->company_id())->pluck('product_id');
        $ids  = product::where('ptype', 'rawmaterial')->whereIn('id', $ids)->pluck('id');
        $data = $model->whereIn('product_id', $ids)->orderBy('created_at', 'asc')->latest()->get();


        foreach ($data as $key => $value) {
			$final_qty = $this->check_quantity($value->product_id);
			$data[$key]['quantity'] = $final_qty;
			$data[$key]['transaction']= $this->check_transaction($value->product_id);
        }
        return Datatables::of($data)
            ->addIndexColumn()
            ->addColumn('rawmaterial_pro_id', function ($memberList) {
                return '<p  data-field="rawmaterial_pro_id" style="cursor: pointer; margin: 0;text-align: center;"><a class="os-linkcolor" href="/landing/rawmaterialbarcode/' . $memberList->product_name->systemid . '" target="_blank" style="text-decoration: none;">' . $memberList->product_name->systemid . '</a></p>';
            })
            ->addColumn('rawmaterial_pro_name', function ($memberList) {
                if (!empty($memberList->product_name->thumbnail_1)) {
                    $img_src = '/images/product/' . $memberList->product_name->id . '/thumb/' . $memberList->product_name->thumbnail_1;
                    $img     = "<img src='$img_src' data-field='rawmaterial_pro_name' style=' width: 25px;height: 25px;display: inline-block;margin-right: 8px;object-fit:contain;'>";
                } else {
                    $img = null;
                }
                return $img . '<p class="os-linkcolor" data-field="rawmaterial_pro_name" style="cursor: pointer; margin: 0;display:inline-block">' . (!empty($memberList->product_name->name) ? $memberList->product_name->name : 'Product Name') . '</p>';
            })
            ->addColumn('rawmaterial_qty', function ($memberList) {
                return '<p class="os-linkcolor qtyOutput" data-field="rawmaterial_qty"  style="cursor: pointer; margin: 0; text-align: center;"> <a style="text-decoration:none;" href="' . url('rawmaterial_quantity/'.$memberList->product_name->systemid) . '" target="_blank">' . (!empty($memberList->quantity) ? ($memberList->quantity) : '0') . '</a></p>';
            })
            ->addColumn('rawmaterial_price', function ($memberList) {

                return '<p class="os-linkcolor priceOutput" data-field="rawmaterial_price" style="cursor: pointer; margin: 0; text-align: right;">' . (!empty($memberList->price) ? number_format(($memberList->price / 100), 2) : '0.00') . '</p>';
            })
            ->addColumn('deleted', function ($memberList) {
				if ($memberList->transaction == 'True') {
					return '<div><img class="" src="/images/redcrab_50x50.png"
						style="width:25px;height:25px;cursor:not-allowed;
						filter:grayscale(100%) brightness(200%)"/>
						</div>';

				} else {
					return '<div data-field="deleted" class="remove">
						<img class="" src="/images/redcrab_50x50.png"
						style="width:25px;height:25px;cursor:pointer"/>
						</div>';
				}
            })
            ->escapeColumns([])
            ->make(true);
    }

    public function check_transaction($product_id)
    {
        $sales_count = opos_receiptproduct::where('product_id', $product_id)
            ->leftjoin('opos_itemdetails','opos_itemdetails.receiptproduct_id','=','opos_receiptproduct.id')
            ->leftjoin('opos_receipt','opos_receipt.id','=','opos_receiptproduct.receipt_id')
            ->leftjoin('opos_receiptdetails','opos_receipt.id','=','opos_receiptdetails.receipt_id')
            ->count();
		$stock_count = stockreport::
			join('stockreportproduct','stockreportproduct.stockreport_id','=','stockreport.id')->
			where('stockreportproduct.product_id', $product_id)->
			count();

        $wastage = opos_wastageproduct::where('product_id',$product_id)->count();
        $total = $sales_count + $stock_count + $wastage;
        if($total > 0) {
            return true;
        } else {
            return false;
        }
    }


    public function check_quantity($product_id)
    {
        $final_qty = 0;
        $sales_qty = opos_receiptproduct::where('product_id', $product_id)->
			leftjoin('opos_itemdetails','opos_itemdetails.receiptproduct_id',
				'=','opos_receiptproduct.id')->
			leftjoin('opos_receipt','opos_receipt.id','=',
				'opos_receiptproduct.receipt_id')->
			leftjoin('opos_receiptdetails','opos_receipt.id','=',
				'opos_receiptdetails.receipt_id')->
			leftjoin('opos_locationterminal', 'opos_receipt.terminal_id',
				'=', 'opos_locationterminal.terminal_id')->
			leftjoin('location', 'location.id', '=',
				'opos_locationterminal.location_id')->
			where('opos_receiptdetails.void','!=',1)->
			sum('opos_receiptproduct.quantity');

	$stock_qty = stockreport::join('stockreportproduct','stockreportproduct.stockreport_id','=','stockreport.id')->
		where('stockreportproduct.product_id', $product_id)->
		sum('stockreportproduct.quantity');
	
	$stockreport_qty = StockReport::join('stockreportproduct','stockreportproduct.stockreport_id','=','stockreport.id')->
		where('stockreportproduct.product_id', $product_id)->
		where('stockreport.type', 'transfer')->
		where('stockreport.status', 'confirmed')->
		sum('stockreportproduct.received');
		
	$stockreportminus_qty = StockReport::join('stockreportproduct','stockreportproduct.stockreport_id','=','stockreport.id')->
		where('stockreportproduct.product_id', $product_id)->
		where('stockreport.type', 'transfer')->
		where('stockreport.status', 'confirmed')->
		sum('stockreportproduct.quantity');	
		
	$refund_c = opos_refund::join("opos_receiptproduct",'opos_receiptproduct.id','=','opos_refund.receiptproduct_id')->
		where('opos_receiptproduct.product_id',$product_id)->
		where('refund_type','C')->
		count();

	$refund_dx = opos_refund::join("opos_receiptproduct",'opos_receiptproduct.id','=','opos_refund.receiptproduct_id')->
		where('opos_receiptproduct.product_id',$product_id)->
		where('refund_type','Dx')->
		count();
	
	$wastage = opos_wastageproduct::where('product_id',$product_id)->
		sum('wastage_qty');
	
	$final_qty = $stock_qty + $stockreport_qty - $stockreportminus_qty - $sales_qty + $refund_c - $refund_dx - $wastage; 
        return $final_qty;
    }

    public function location_productqty($product_id, $location_id)
    {
        $final_qty = 0;
         $product_Sales_qty_data = opos_receiptproduct::
                leftjoin('opos_itemdetails','opos_itemdetails.receiptproduct_id','=','opos_receiptproduct.id')
                ->leftjoin('opos_receipt','opos_receipt.id','=','opos_receiptproduct.receipt_id')
                ->leftjoin('opos_receiptdetails','opos_receipt.id','=','opos_receiptdetails.receipt_id')
                ->leftjoin('opos_locationterminal', 'opos_receipt.terminal_id', '=', 'opos_locationterminal.terminal_id')
                ->leftjoin('location', 'location.id', '=', 'opos_locationterminal.location_id')
                ->where('opos_receiptproduct.product_id', $product_id)
                ->where('opos_receiptdetails.void','!=',1)
                ->where('location.id','=',$location_id)
                ->sum('opos_receiptproduct.quantity');

	$stock_qty = stockreport::
		join('stockreportproduct','stockreportproduct.stockreport_id','=','stockreport.id')->
		where('stockreportproduct.product_id', $product_id)->where('location_id', $location_id)->sum('stockreportproduct.quantity');
				
		$stockreport_qty = StockReport::join('stockreportproduct','stockreportproduct.stockreport_id','=','stockreport.id')->
			where('stockreportproduct.product_id', $product_id)->where('stockreport.dest_location_id', $location_id)
			->where('stockreport.type', 'transfer')->where('stockreport.status', 'confirmed')->sum('stockreportproduct.received');
		
		$stockreportminus_qty = StockReport::join('stockreportproduct','stockreportproduct.stockreport_id','=','stockreport.id')->
			where('stockreportproduct.product_id', $product_id)->where('stockreport.location_id', $location_id)
			->where('stockreport.type', 'transfer')->where('stockreport.status', 'confirmed')->sum('stockreportproduct.quantity');				

                $refund_c = opos_refund::
                    join("opos_receiptproduct",'opos_receiptproduct.id','=','opos_refund.receiptproduct_id')
                    ->leftjoin('opos_receipt','opos_receipt.id','=','opos_receiptproduct.receipt_id')
                    ->leftjoin('opos_locationterminal', 'opos_receipt.terminal_id', '=', 'opos_locationterminal.terminal_id')
                    ->leftjoin('location', 'location.id', '=', 'opos_locationterminal.location_id')
                    ->where('opos_receiptproduct.product_id',$product_id)
                    ->where('location.id',$location_id)
                    ->where('opos_refund.refund_type','C')
                    ->count();

                $refund_dx = opos_refund::
                    join("opos_receiptproduct",'opos_receiptproduct.id','=','opos_refund.receiptproduct_id')
                    ->leftjoin('opos_receipt','opos_receipt.id','=','opos_receiptproduct.receipt_id')
                    ->leftjoin('opos_locationterminal', 'opos_receipt.terminal_id', '=', 'opos_locationterminal.terminal_id')
                    ->leftjoin('location', 'location.id', '=', 'opos_locationterminal.location_id')
                    ->where('opos_receiptproduct.product_id',$product_id)
                    ->where('location.id',$location_id)
                    ->where('opos_refund.refund_type','Dx')
                    ->count();
                $wastage = opos_wastageproduct::where('product_id',$product_id)->where('location_id',$location_id)->sum('wastage_qty');
                $final_qty = $stock_qty + $stockreport_qty - $stockreportminus_qty - $product_Sales_qty_data + $refund_c - $refund_dx-$wastage;
            return $final_qty;
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
            $SystemID        = new SystemID('product');
            $product         = new product();
            $rawmaterial     = new rawmaterial();

            $product->systemid = $SystemID;
            $product->ptype    = 'rawmaterial';
            $product->save();

            $rawmaterial->product_id = $product->id;
            $rawmaterial->save();

            $merchantproduct->product_id  = $product->id;
            $merchantproduct->merchant_id = $this->user_data->company_id();
            $merchantproduct->save();

            $msg = "Product added successfully";
            return view('layouts.dialog', compact('msg'));
        } catch (\Exception $e) {
            $msg = "$e";
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
                $rawmaterial = rawmaterial::where('id', $id)->first();
                return view('rawmaterial.rawmaterial-modals', compact([
					'id', 'fieldName', 'rawmaterial'
				]));
            }

        } catch (\Illuminate\Database\QueryException $ex) {
            $response = (new ApiMessageController())->queryexception($ex);
        }
    }


    public function update(Request $request)
    {
        try {
            $allInputs = $request->all();
            $rawmaterial_id = $request->get('rawmaterial_id');
            $changed = false;
			$msg = $response = null;

            $validation = Validator::make($allInputs, [
                'rawmaterial_id' => 'required',
            ]);

            if ($validation->fails()) {
                throw new Exception("product_not_found", 1);
            }

            $rawmaterial = rawmaterial::find($rawmaterial_id);

            if (!$rawmaterial) {
                throw new Exception("product_not_found", 1);
            }

			Log::debug('request->price='.$request->price);
			Log::debug('rawmaterial->price='.$rawmaterial->price);

            if ($request->has('price')) {
                if ($rawmaterial->price != (int) str_replace('.','',
					$request->price)) {
                    $rawmaterial->price = (int) str_replace('.', '',
						$request->price);
                    $changed = true;
                    $msg = "Price updated successfully";
                }
            }

            if ($changed) {
                $rawmaterial->save();
                $response = view('layouts.dialog', compact('msg'));
            }

        } catch (\Exception $e) {
            if ($e->getMessage() == 'product_not_found') {
                $msg = "Product not found";
            } else if ($e->getMessage() == 'invalid_cost') {
                $msg = "Invalid cost";
            } else {
				$msg = "Error @ ".$e->getLine()." file ".$e->getFile()." ".
					$e->getMessage();
				Log::error($msg);
            }

            $response = view('layouts.dialog', compact('msg'));
        }

        return $response;
    }


    public function destroy($id)
    {
        try {
            $this->user_data = new UserData();
            $rawmaterial     = rawmaterial::find($id);
            $product_id      = $rawmaterial->product_id;
            $is_exist        = merchantproduct::where(
                ['product_id' => $product_id, 'merchant_id' => $this->user_data->company_id()]
            )->first();

            if (!$is_exist) {
                throw new Exception("Error Processing Request", 1);
            }

            product::find($product_id)->delete();
            $rawmaterial->delete();

            $msg = "Product deleted successfully";
            return view('layouts.dialog', compact('msg'));
        } catch (\Illuminate\Database\QueryException $ex) {
            $msg = "Some error occured";

            return view('layouts.dialog', compact('msg'));
        }
    }

    public function showRawmaterialView()
    {
        return view('rawmaterial.rawmaterial');
    }


    public function showRawMaterialQuantity($id)
    {
        $user_id = Auth::user()->id;
        $user_roles = usersrole::where('user_id', $user_id)->get();

        $is_king =  \App\Models\Company::where('owner_user_id', Auth::user()->id)->first();

        $model = new rawmaterial();
        $product = product::where('systemid', $id)->first();
        $product_data = $model->where('product_id', $product->id)->first();

        // Product Meta data (reciept, location, document no., etc.)
        $opos_product = opos_receiptproduct::
        select(
			'opos_receipt.systemid as document_no',
			'opos_receiptproduct.receipt_id',
			'opos_itemdetails.id as item_detail_id',
			'opos_itemdetails.receiptproduct_id',
			'opos_receiptproduct.quantity',
			'opos_itemdetails.created_at as last_update',
			'location.branch as location',
			'location.id as locationid',
			'opos_receiptdetails.void')
        ->leftjoin('opos_itemdetails','opos_itemdetails.receiptproduct_id','=','opos_receiptproduct.id')
        ->leftjoin('opos_receipt','opos_receipt.id','=','opos_receiptproduct.receipt_id')
        ->leftjoin('opos_receiptdetails','opos_receipt.id','=','opos_receiptdetails.receipt_id')
        ->leftjoin('opos_locationterminal', 'opos_receipt.terminal_id', '=', 'opos_locationterminal.terminal_id')
        ->leftjoin('location', 'location.id', '=', 'opos_locationterminal.location_id')
        ->where('opos_receiptproduct.product_id', $product->id)
        ->orderby('opos_itemdetails.id','DESC')
        ->get();
        $this->user_data = new UserData();

	$refund = opos_refund::select('opos_receipt.systemid as document_no',
		'opos_receiptproduct.receipt_id','opos_receiptproduct.quantity',
		'opos_refund.refund_type','opos_itemdetails.id as item_detail_id',
		'opos_itemdetails.receiptproduct_id','opos_refund.created_at as last_update',
		'location.branch as location','location.id as locationid','opos_receiptdetails.void')
        ->join("opos_receiptproduct",'opos_receiptproduct.id','=','opos_refund.receiptproduct_id')
        ->leftjoin('opos_itemdetails','opos_itemdetails.receiptproduct_id','=','opos_receiptproduct.id')
        ->leftjoin('opos_receipt','opos_receipt.id','=','opos_receiptproduct.receipt_id')
        ->leftjoin('opos_receiptdetails','opos_receipt.id','=','opos_receiptdetails.receipt_id')
        ->leftjoin('opos_locationterminal', 'opos_receipt.terminal_id', '=', 'opos_locationterminal.terminal_id')
        ->leftjoin('location', 'location.id', '=', 'opos_locationterminal.location_id')
        ->whereIn('opos_refund.refund_type',array('C','Dx'))
        ->where('opos_receiptproduct.product_id',$product->id)->get();

        $item_count = count($opos_product);
        foreach ($refund as $key => $value) {
            $refund_type = $value->refund_type;
            if($refund_type == "C" || $refund_type == "Dx"){
                if($refund_type == 'C') {
                    $opos_product[$item_count] = $value;
                    $opos_product[$item_count]->sales_type = 'Refund C';
                    $opos_product[$item_count]->quantity = 1;
                    $item_count++;
                } 

                if($refund_type == "Dx") {
                    $opos_product[$item_count] = $value;
                    $opos_product[$item_count]->sales_type = 'Refund Dx';
                    $opos_product[$item_count]->quantity = -1;
                    $item_count++;
                }
            }
        }

        $wastage = opos_wastageproduct::
		select('product.systemid as productsys_id','product.id as product_id','product.thumbnail_1',
			'product.name','opos_wastage.systemid as document_no','opos_wastageproduct.wastage_qty as quantity',
			'opos_wastageproduct.created_at as last_update','location.branch as location','location.id as locationid')
        ->join('opos_wastage','opos_wastage.id','=','opos_wastageproduct.wastage_id')
        ->join('location','location.id','=','opos_wastageproduct.location_id')
        ->join("product",'opos_wastageproduct.product_id','=','product.id')
        ->where('opos_wastageproduct.product_id',$product->id)
        -> get();
        $item_count = count($opos_product);
        foreach ($wastage as $key => $value) {
            $opos_product[$item_count] = $value;
            $opos_product[$item_count]->wastage = 1;
            $opos_product[$item_count]->sales_type = "Wastage & Damage";
            $opos_product[$item_count]->quantity = 0 - $value->quantity;
            $item_count++;
        }

	$StockReport = StockReport::select('stockreport.systemid as document_no','stockreport.id as stockreport_id',
		'stockreportproduct.quantity','stockreport.type as refund_type','stockreport.created_at as last_update',
		'location.branch as location','location.id as locationid')
	->join('stockreportproduct','stockreportproduct.stockreport_id','=','stockreport.id')
	->leftjoin('location', 'location.id', '=', 'stockreport.location_id')->
		where([['stockreport.type','!=','transfer']])
        ->where('stockreportproduct.product_id',$product->id)->get();

        $item_count = count($opos_product);
        foreach ($StockReport as $key => $value) {
            $opos_product[$item_count] = $value;
            if($value->refund_type == 'stockin') {
                $sales = 'Stock In';
            } else if($value->refund_type == 'stockout') {
                $sales = 'Stock Out';
                $opos_product[$item_count]->quantity = $value->quantity;

            } else {
                $sales = $value->refund_type; 
            }
            $opos_product[$item_count]->sales_type = $sales;
            $opos_product[$item_count]->item_detail_id = $value->stockreport_id;
            $opos_product[$item_count]->stock = 1;
            $item_count++;
        }


	$StockReportTRout = StockReport::select('stockreport.systemid as document_no', 
		'stockreport.id as stockreport_id', 'stockreportproduct.quantity', 
		'stockreport.type as refund_type', 'stockreport.created_at as last_update', 
		'location.branch as location', 'location.id as locationid')
			->join('location', 'location.id', '=', 'stockreport.location_id')
			->join('stockreportproduct', 'stockreport.id', '=', 'stockreportproduct.stockreport_id')
			->where('stockreportproduct.product_id', $product->id)->where(['stockreport.status'=>'confirmed','stockreport.type'=>'transfer'])->distinct()->get();
		
			$item_count = count($opos_product);
			foreach ($StockReportTRout as $key => $value) {
				$opos_product[$item_count] = $value;
				$sales = 'Transfer Report';
				$opos_product[$item_count]->sales_type = $sales;
				$opos_product[$item_count]->item_detail_id = $value->stockreport_id;
				$opos_product[$item_count]->quantity = $value->quantity * -1;
				$opos_product[$item_count]->stock = 3;
				$item_count++;
			}		
			
		$StockReportTRin = StockReport::select('stockreport.systemid as document_no', 'stockreport.id as stockreport_id', 'stockreportproduct.quantity'
											, 'stockreportproduct.received', 
											'stockreport.type as refund_type', 'stockreport.created_at as last_update', 'location.branch as location', 
											'location.id as locationid')
			->join('location', 'location.id', '=', 'stockreport.dest_location_id')
			->join('stockreportproduct', 'stockreport.id', '=', 'stockreportproduct.stockreport_id')
			->where('stockreportproduct.product_id', $product->id)->where(['stockreport.status'=>'confirmed','stockreport.type'=>'transfer'])->distinct()->get();
			$item_count = count($opos_product);
			foreach ($StockReportTRin as $key => $value) {
				$opos_product[$item_count] = $value;
				$sales = 'Transfer Report';
				$opos_product[$item_count]->sales_type = $sales;
				$opos_product[$item_count]->item_detail_id = $value->stockreport_id;
				$opos_product[$item_count]->quantity = $value->received;
				$opos_product[$item_count]->stock = 3;
				$item_count++;
			}
        $merchant_id = Merchant::where('id', $this->user_data->company_id())->pluck('id')->first();
        $location_sales_qty = array();
        // Latest item remarks 
        foreach ($opos_product as $key => $value) {
            if($value->stock) {
                $product_remark = stockreportremarks::orderby('created_at','DESC')
                ->where('stockreport_id', $value->stockreport_id)
                ->where('user_id', Auth::user()->id)->first();
                if($product_remark) {
                    $opos_product[$key]->item_remarks = $product_remark->remarks ;
                }
            } else {
                $item_id = $value->item_detail_id;
                $product_remark = opos_itemdetailsremarks::orderby('created_at','DESC')
                                ->where('itemdetails_id', $item_id)
                                ->where('user_id', Auth::user()->id)->first();
                if($product_remark) {
                    $opos_product[$key]->item_remarks = /*(strlen($product_remark->remarks) > 60 ) ? substr($product_remark->remarks,0,60)."..." : */$product_remark->remarks ;
                }
                if($value->refund_type || $value->wastage) { continue; }
                if($value->void ==1) {
                    $opos_product[$key]->sales_type = "Void Sales";
                    $opos_product[$key]->quantity = 0 ;
                } else {
                    $opos_product[$key]->sales_type = "Cash Sales";
                    $opos_product[$key]->quantity = 0 - $value->quantity;
                }
            }
        }

        // opos_product sort by Lastupdate (db_table.created_at) Desc
        $opos_product = $opos_product->sortBy('last_update',SORT_REGULAR,true);

        // Product Location Stock data
        $location_data = location::
        join('merchantlocation','merchantlocation.location_id','=','location.id')->where('merchant_id',$merchant_id)->whereNotNull('location.branch')->get();
        foreach ($location_data as $key => $value) {
            $final_qty = $this->location_productqty($product->id,$value->location_id);
            $location_data[$key]['quantity'] = $final_qty;
        }

        if ($is_king != null) {
            $is_king = true;
        } else {
            $is_king  = false;
        }

	$opos_product = $opos_product->groupBy('document_no');
	$opos_product = $opos_product->map(function($f) {
		if (get_class($f) == "Illuminate\Database\Eloquent\Collection") {
			$qty = $f->sum('quantity');
			$f = $f[0];
			$f->quantity = $qty;
		}
		//	dd(get_class($f));
		return $f;
	});
        return view('product.product_rawmaterialqty', compact('user_roles', 'is_king', 'product','product_data','opos_product','location_data'));
    }


    public function showRawmaterialBarcode($id)
    {
        $system_id = $id;

        $product = product::where('systemid', $system_id)->first();
        $product_id = $product->id;
        $product_qty = $this->check_quantity($product->id);
	  	$barcodematrix = null;//SettingBarcodeMatrix::where('category_id', $product->prdcategory_id)->first();
        
        $barcode_sku = productbarcode::where('product_id',$product->id)->first();

        $barcode = DNS1D::getBarcodePNG(trim($system_id), "C128");

        $id = Auth::user()->id;
        $user_roles = usersrole::where('user_id', $id)->get();

        $is_king =  \App\Models\Company::where('owner_user_id', Auth::user()->id)->first();

        if ($is_king != null) {
            $is_king = true;
        } else {
            $is_king  = false;
        }

		$showbuttons = true;

        return view('inventory.inventorybarcode', compact(
			'user_roles','system_id', 'is_king','product',
			'product_qty','barcodematrix','barcode_sku',
			'barcode','product_id','showbuttons'));
    }

    public function showRMStockIn()
    {
        $id = Auth::user()->id;
        $user_roles = usersrole::where('user_id', $id)->get();

        $is_king =  \App\Models\Company::where('owner_user_id', Auth::user()->id)->first();

        $this->user_data = new UserData();
        $modal           = "newLocationDialog";
        $ids             = merchantlocation::where('merchant_id', $this->user_data->company_id())->pluck('location_id');
        $location        = location::where([['branch', '!=', 'null']])->whereIn('id', $ids)->latest()->get();

        if ($is_king != null) {
            $is_king = true;
        } else {
            $is_king  = false;
        }
        // $products = \App\Models\product::get();
        return view('rawmaterial.rawmaterialstockin', compact('user_roles', 'is_king','location'));
    }

    public function showRMStockOut()
    {
        $id = Auth::user()->id;
        $user_roles = usersrole::where('user_id', $id)->get();

        $is_king =  \App\Models\Company::where('owner_user_id', Auth::user()->id)->first();

        $this->user_data = new UserData();
        $modal           = "newLocationDialog";
        $ids             = merchantlocation::where('merchant_id', $this->user_data->company_id())->pluck('location_id');
        $location        = location::where([['branch', '!=', 'null']])->whereIn('id', $ids)->latest()->get();

        if ($is_king != null) {
            $is_king = true;
        } else {
            $is_king  = false;
        }
        return view('rawmaterial.rawmaterialstockout', compact('user_roles', 'is_king','location'));
    }
}
?>
