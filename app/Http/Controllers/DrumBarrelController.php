<?php

namespace App\Http\Controllers;
use Log;

use Illuminate\Http\Request;
use App\Models\usersrole;
use App\Models\role;
use App\Models\Company;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Matrix\Exception;
use Yajra\DataTables\DataTables;
use \App\Classes\SystemID;
use \App\Classes\UserData;
use \App\Models\merchantproduct;
use \App\Models\product;
use \App\Models\prd_drumbarrel;
use \App\Models\restaurant;
use \App\Models\opos_receipt;
use \App\Models\opos_receiptdetails;
use \App\Models\opos_receiptproduct;
use \App\Models\opos_brancheod;
use \App\Models\rackproduct;
use \App\Models\stockreportproduct;
use \App\Models\stockreportproductrack;
use \App\Models\productbarcodelocation;
use \App\Models\voucherproduct;
use \App\Models\voucherlist;
use \App\Models\voucher;
use \App\Models\opos_refund;
use \App\Models\StockReport;
use \App\Models\opos_wastage;
use \App\Models\opos_wastageproduct;
//use App\Models\SettingBarcodeMatrix;
use \App\Models\productbarcode;
use Milon\Barcode\DNS1D;
use Milon\Barcode\DNS2D;
use DB;

class DrumBarrelController extends Controller
{
	public function DrumBarrelView() {
	   return view('drumbarrel.prd_drumbarrel');
	}
	
	public function index() {
		
		$this->user_data = new UserData();
        $model           = new prd_drumbarrel();
		
		$query = "        
		SELECT 
			prd.id as id,
			prd.product_id,
			prd.price as price,
			prd.deposit as deposit,
			p.systemid as systemid,
			p.name as name,
			p.thumbnail_1 as thumbnail_1
		FROM prd_drumbarrel as prd
		JOIN product p ON prd.product_id = p.id 
		JOIN merchantproduct mp ON p.id  = mp.product_id 
		WHERE mp.merchant_id = ". $this->user_data->company_id(). "
		AND p.ptype = 'drum'		
		ORDER BY 
		 prd.created_at 
		DESC";
        $data = DB::select(DB::raw($query));
		return Datatables::of($data)
            ->addIndexColumn()
            ->addColumn('drum_productid', function ($data) {
                return '<a class="os-linkcolor" href="/landing/drumbarrel/' . $data->systemid . '" target="_blank" target="_blank" style="cursor: pointer; margin: 0;text-align: center;text-decoration:none">' . (!empty($data->systemid)? $data->systemid : '000000000000') . '</a>';
            })
            ->addColumn('drum_productname', function ($data) {
                if (!empty($data->thumbnail_1)) {
                    $img_src = asset('images/product/' . $data->product_id . '/thumb/' . $data->thumbnail_1);
                    $img     = "<img src='$img_src' data-field='restaurantnservices_pro_name' style='width: 25px;height: 25px;display: inline-block;margin-right: 8px;object-fit:contain;'>";
                } else {
                    $img = null;
                }
                return $img . '<p class="os-linkcolor" data-field="drum_productname" onclick="details(' . $data->systemid . ')" style="cursor: pointer; margin: 0;display: inline-block">' . (!empty($data->name) ? $data->name : 'Product Name') . '</p>';
            })
            ->addColumn('drum_price', function ($data) {

                return '<p class="os-linkcolor priceOutput" data-target="#drumbarrelPriceModal" data-toggle="modal" data-field="drum_price" data-product_id="' . $data->product_id . '" style="cursor:pointer;margin: 0; text-align: right;">'.(!empty($data->price) ? number_format($data->price/100,2,'.',','): '0.00').'</p>';

            })
			->addColumn('drum_deposit', function ($data) {

                return '<p class="os-linkcolor depositOutput" data-target="#drumbarrelDepositModal" data-toggle="modal" data-field="drum_deposit" data-product_id="' . $data->product_id . '"  style="cursor:pointer;margin: 0; text-align: right;">'.(!empty($data->deposit) ? number_format($data->deposit/100,2,'.',','): '0.00').'</p>';

            })
            ->addColumn('deleted', function ($data) {
				return '<div data-field="deleted" class="remove">
					<img class="" src="/images/redcrab_50x50.png"
					style="width:25px;height:25px;cursor:pointer"/>
					</div>';
            })
            ->escapeColumns([])
            ->make(true);
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

			$model           = new prd_drumbarrel();
            $drumbarrel = $model::where('id', $id)->first();
				
            return view('drumbarrel.drumbarrel-modals', compact(['id', 'fieldName','drumbarrel']));
            } catch (\Illuminate\Database\QueryException $ex) {
			$response = (new ApiMessageController())->queryexception($ex);
		}
    }
	
	public function showDrumBarrelProduct($systemid)
	{
        $id = Auth::user()->id;
        $user_roles = usersrole::where('user_id',$id)->get();

        $is_king =  \App\Models\Company::where('owner_user_id',Auth::user()->id)->first();

        if ($is_king != null) {
            $is_king = true;
        } else {
            $is_king  = false;
        }
		$this->user_data = new UserData();
        $model           = new prd_drumbarrel();
		
		$system_id = $systemid;
		$product = product::where('systemid', $system_id)->first();
		$product_id = $product->id;
		$product_qty = $this->check_quantity($product->id);

		//$barcodematrix = SettingBarcodeMatrix::where('category_id', $product->prdcategory_id)->first();
		$barcodematrix = null;

		$barcode_sku = productbarcode::where('product_id', $product->id)->first();
		
		$barcode = DNS1D::getBarcodePNG(trim($system_id), "C128");

		return view('drumbarrel.drumbarrelbarcode', compact(
			'user_roles','is_king','system_id','product',
			'barcodematrix','barcode_sku','barcode','product_qty'
		));
	}
	
	public function showBarcodeTable(Request $request)
	{
		$product_id = $request->id;
		$product = product::where('id', $product_id)->first();
		$product_qty = $this->check_quantity($product->id);
		//$barcodematrix = SettingBarcodeMatrix::where('category_id', $product->prdcategory_id)->first();
		$barcodematrix = null;
		
		$this->user_data = new UserData();
		$merchant_id = $this->user_data->company_id();

        $merchant_product = DB::table('merchantproduct')->
            select('id')->
            where('merchant_id','=',$merchant_id)->
            where('product_id' ,'=',$product_id)->
            first();

		$barcode_sku = productbarcode::where('product_id', $product->id)->where('merchantproduct_id',$merchant_product->id)->first();
		
		$barcode = DNS1D::getBarcodePNG(trim($product->systemid), "C128");
		
		$id = Auth::user()->id;
		$user_roles = usersrole::where('user_id', $id)->get();
		
		$is_king = \App\Models\Company::where('owner_user_id',
			Auth::user()->id)->first();
		
		if ($is_king != null) {
			$is_king = true;
		} else {
			$is_king = false;
		}
		
		$columns = array(
			0 => 'no',
			1 => 'barcode',
			2 => 'qr_code',
			3 => 'color',
			4 => 'matrix',
			5 => 'notes',
			6 => 'qty',
			7 => 'options',
			7 => 'actions'
		);
		$limit = $request->length;
		$start = $request->start;
		$search = $_REQUEST['search']['value'];
		
		$totalRecords = productbarcode::where('product_id', $product->id)->
			where('merchantproduct_id',$merchant_product->id)->
			where([['barcode', 'LIKE', "%" . $search . "%"]])->count();

		$totalFiltered = $totalRecords;
		$totalRecords += 1;
		$totalFiltered += 1;
		
		$barcodes_data = array();
		$count = $start + 1;
		
		$qr = DNS2D::getBarcodePNG($product->systemid, "QRCODE");
		$sku = 'SKU';
		if (!empty($product->sku))
			$sku = $product->sku;
		array_push(
			
			$barcodes_data, array(
				"no" => $count,
				"barcode" => $product->name ."<br><img src='data:image/png;base64," . $barcode . "' height='60px' width='200px' style='margin-top:0'/><br>" . $product->systemid,
				"qr_code" => "<p id='barcodesku_" . $product_id . "' style='margin-bottom: 0px;'><a href='#' data-is_main='1' class='sku' data-barcode_id='" . $product_id . "'>" . $sku . "</a></p><img src='data:image/png;base64," . $qr . "' height='70px' width='70px' />",
				"color" => "",
				"matrix" => "Colour Black Size XL XXL L M XXXl",
				"notes" => "<strong>Warranty No.325324234<br>Invoice
                        No.
                        5343533</strong>",
				"qty" => $product_qty,
				"options" => "<a href='#' class='btn btn-success btn-log bg-web sellerbutton'style='padding-top: 25px;'>Print</a>",
				"actions" => ""
			)
		);
		
		$barcodes = productbarcode::where('product_id', $product->id)->
			where('merchantproduct_id',$merchant_product->id)->
			where([['barcode', 'LIKE', "%" . $search . "%"]])->
			skip($start)->
			take($limit)->
			orderBy('id', 'desc')->
			get();
		
		foreach ($barcodes as $barcode) {
            $sku = 'SKU';
            $name = 'Barcode Name';
            $notes = $barcode->notes;
            if ($barcode->expirydate != '0000-00-00' &&
				$barcode->expirydate != '1970-01-01' &&
				!is_null($barcode->expirydate))
                $notes = "Expiry Date : <strong>" .
					date("dMy", strtotime($barcode->expirydate)) . "</strong>";
            if (!empty($barcode->sku))
                $sku = $barcode->sku;
            if (!empty($barcode->name))
                $name = $barcode->name;
            $count++;
            $code = DNS1D::getBarcodePNG(trim($barcode->barcode), "C128");
            $qr = DNS2D::getBarcodePNG($barcode->barcode, "QRCODE");
            $final_qty = productbarcodelocation::
            where('productbarcode_id', $barcode->id)
                ->sum('quantity');

            $barcodes_data[0]['qty'] -= $final_qty;
            $check_barcode = productbarcodelocation::
                where('productbarcode_id', $barcode->id)->get();

			/*
			$sku = 'SKU';
			$notes = $barcode->notes;
			if ($barcode->expirydate != '0000-00-00' && $barcode->expirydate != '1970-01-01' && !is_null($barcode->expirydate))
				$notes = "Expiry Date : <strong>" . date("dMy", strtotime($barcode->expirydate)) . "</strong>";
			if (!empty($barcode->sku))
				$sku = $barcode->sku;
			$count++;
			$code = DNS1D::getBarcodePNG(trim($barcode->barcode), "C128");
			$qr = DNS2D::getBarcodePNG($barcode->barcode, "QRCODE");
			$final_qty = productbarcodelocation::
			where('productbarcode_id', $barcode->id)
				->sum('quantity');
			
			$barcodes_data[0]['qty'] -= $final_qty;
			$check_barcode = productbarcodelocation::
			where('productbarcode_id', $barcode->id)
				->get();
			*/

			if (count($check_barcode) > 0) {
				/*
				$actions = '<p style="background-color:#ddd;
                    border-radius:5px;margin:auto;
                    width:25px;height:25px;
                    display:block;cursor:not-allowed;margin-top:29px">
                    <i class="fas fa-times text-white" style="color:white;opacity:1.0;
                    padding-left:7px;padding-top:4px;
                    -webkit-text-stroke: 1px #ccc;"></i></p>';
				*/
				$actions = '<div><img src="/images/redcrab_50x50.png"
                    style="width:25px;height:25px;cursor:not-allowed;
                    filter:grayscale(100%) brightness(200%)"/>
                    </div>';

			} else {
				/*
				$actions = '<input type="hidden"  value="' . $barcode->id . '"/><p style="background-color:red;
                    border-radius:5px;margin:auto;
                    width:25px;height:25px;
                    display:block;cursor: pointer;margin-top:29px" class="text-danger remove-barcode">
                    <i class="fas fa-times text-white" style="color:white;opacity:1.0;
                    padding-left:7px;padding-top:4px;
                    -webkit-text-stroke: 1px red;"></i></p>';
				*/
				$actions = '<input type="hidden"  value="'.$barcode->id.
                    '"/><div class="remove-barcode mb-0"
                    style="">
                    <img class="" src="/images/redcrab_50x50.png"
                    style="width:25px;height:25px;cursor:pointer"/>
                    </div>';
			}
			
			
			array_push(
				$barcodes_data, array(
					"no" => $count,
					"barcode" => "<p id='barcodename_" . $barcode->id. "' style='margin-bottom: 0px; margin-top : 10px;'><a href='#!' data-is_main='0' class='name' data-barcode_id='" . $barcode->id . "'>" .  $name . "</a></p>"."<img src='data:image/png;base64," . $code . "' height='60px' width='200px' style='margin-top:0'/><br>" . $barcode->barcode,
					"qr_code" => "<p id='barcodesku_" . $barcode->id . "' style='margin-bottom: 0px;'><a href='#' data-is_main='0' class='sku' data-barcode_id='" . $barcode->id . "'>" . $sku . "</a></p><img src='data:image/png;base64," . $qr . "' height='70px' width='70px' />",
					"color" => "",
					"matrix" => "",
					"notes" => $notes,
					"qty" => $final_qty,
					"options" => "<a href='#' class='btn btn-success btn-log bg-web sellerbutton'style='padding-top: 25px;'>Print</a>",
					"actions" => $actions
				)
			);
		}
		
		echo json_encode(array(
			"draw" => intval($_REQUEST['draw']),
			"recordsTotal" => $totalRecords,
			"recordsFiltered" => $totalFiltered,
			"data" => $barcodes_data
		));
	}
	
  
	public function check_quantity($product_id)
	{
		$final_qty = 0;


        //$promo_product_sale_qty = $this->getPromoProductSaleQty($product_id);

        // Product Meta data (reciept, location, document no., etc.)
        $sales_qty = opos_receiptproduct::
        select('opos_receipt.systemid as document_no', 'opos_receiptproduct.receipt_id', 'opos_itemdetails.id as item_detail_id', 'opos_itemdetails.receiptproduct_id', 'opos_receiptproduct.quantity', 'opos_itemdetails.created_at as last_update', 'location.branch as location', 'location.id as locationid', 'opos_receiptdetails.void')
            ->leftjoin('opos_itemdetails', 'opos_itemdetails.receiptproduct_id', '=', 'opos_receiptproduct.id')
            ->leftjoin('opos_receipt', 'opos_receipt.id', '=', 'opos_receiptproduct.receipt_id')
            ->leftjoin('opos_receiptdetails', 'opos_receipt.id', '=', 'opos_receiptdetails.receipt_id')
            ->leftjoin('opos_locationterminal', 'opos_receipt.terminal_id', '=', 'opos_locationterminal.terminal_id')
            ->leftjoin('location', 'location.id', '=', 'opos_locationterminal.location_id')
            ->where('opos_receiptproduct.product_id', $product_id)
            ->where('opos_receiptdetails.void', '!=', 1)
            ->sum('opos_receiptproduct.quantity');

        /*
        $sales_qty = opos_receiptproduct::where('product_id', $product_id)
            ->leftjoin('opos_itemdetails', 'opos_itemdetails.receiptproduct_id', '=', 'opos_receiptproduct.id')
			->leftjoin('opos_receipt', 'opos_receipt.id', '=', 'opos_receiptproduct.receipt_id')
			->leftjoin('opos_receiptdetails', 'opos_receipt.id', '=', 'opos_receiptdetails.receipt_id')
			->leftjoin('opos_locationterminal', 'opos_receipt.terminal_id', '=', 'opos_locationterminal.terminal_id')
			->leftjoin('location', 'location.id', '=', 'opos_locationterminal.location_id')
			->where('opos_receiptdetails.void', '!=', 1)
			->sum('opos_receiptproduct.quantity');
        */

        $voucherQty = voucher::join('voucherproduct', 'voucherproduct.product_id', '=', 'prd_voucher.product_id')
            ->where('voucherproduct.product_id', $product_id)
            ->whereIn('prd_voucher.type', ['qty'])
            ->sum('voucherproduct.vquantity');


        $stock_qty = StockReport::where('product_id', $product_id)->sum('quantity');
        $refund_c = opos_refund::join("opos_receiptproduct", 'opos_receiptproduct.id', '=', 'opos_refund.receiptproduct_id')->where('opos_receiptproduct.product_id', $product_id)->where('refund_type', 'C')->count();
        $refund_dx = opos_refund::join("opos_receiptproduct", 'opos_receiptproduct.id', '=', 'opos_refund.receiptproduct_id')->where('opos_receiptproduct.product_id', $product_id)->where('refund_type', 'Dx')->count();
        $wastage = opos_wastageproduct::where('product_id', $product_id)->sum('wastage_qty');
        $final_qty = $stock_qty - $sales_qty + $refund_c - $refund_dx - $wastage - $voucherQty;
		return $final_qty;
	}

  public function showDrumBarrelDistribution()
  {

        $id = Auth::user()->id;
        $user_roles = usersrole::where('user_id',$id)->get();

        $is_king =  \App\Models\Company::where('owner_user_id',Auth::user()->id)->first();

        if ($is_king != null) {
            $is_king = true;
        } else {
            $is_king  = false;
        }

        return view('drumbarrel.drumbarrelmgmt',compact('user_roles','is_king'));
  }

public function showDrumBarrelDistributionProduct()
{

        $id = Auth::user()->id;
        $user_roles = usersrole::where('user_id',$id)->get();

        $is_king =  \App\Models\Company::where('owner_user_id',Auth::user()->id)->first();

        if ($is_king != null) {
            $is_king = true;
        } else {
            $is_king  = false;
        }

        return view('drumbarrel.drumbarrel_distribution_product',compact('user_roles','is_king'));
}
public function showDrumBarrelDistributionQty()
{

        $id = Auth::user()->id;
        $user_roles = usersrole::where('user_id',$id)->get();

        $is_king =  \App\Models\Company::where('owner_user_id',Auth::user()->id)->first();

        if ($is_king != null) {
            $is_king = true;
        } else {
            $is_king  = false;
        }

        return view('drumbarrel.drumbarrel_distribution_qty',compact('user_roles','is_king'));
}

public function store(Request $request)
    {
        //Create a new product here
        try {
            $this->user_data = new UserData();
            $merchantproduct = new merchantproduct();
            $SystemID        = new SystemID('product');
            $product         = new product();

            $product->systemid = $SystemID;
            $product->ptype    = 'drum';
            $product->save();

            $prd_drumbarrel = new prd_drumbarrel();
            $prd_drumbarrel->product_id = $product->id;
            $prd_drumbarrel->save();
			
            $merchantproduct->product_id  = $product->id;
            $merchantproduct->merchant_id = $this->user_data->company_id();
            $merchantproduct->save();

            $msg = "Product added successfully";
            return view('layouts.dialog', compact('msg'));

        } catch (\Exception $e) {
            $msg = $e->getMessage();
            return view('layouts.dialog', compact('msg'));
        }
    }
	
	public function dialog(Request $request)
    {
        try {
            $validation = Validator::make($request->all(), [
                'product_id' => 'required',
            ]);

            if ($validation->fails()) {
                throw new \Exception("validation_error", 19);
            }

            Log::debug('product_id=' . $request->product_id);

            $product_details = product::where('systemid',
                $request->product_id)->first();

            if (!$product_details) {
                throw new \Exception('product_not_found', 25);
            }

            $productsetting = new ProductCategoriesController();

            if (!empty($product_details->prdcategory_id)) {

                $product_product =
                    $productsetting->fetchData('product');

                $selected_product =
                    $product_product->where('category_id',
                        $product_details->prdcategory_id)->first();

                $product_product =
                    $product_product->where('subcategory_id',
                        $product_details->prdsubcategory_id)->all();

                $product_subcategory =
                    $productsetting->fetchData('subcategory');

                $product_subcategory =
                    $product_subcategory->where('category_id',
                        $product_details->prdcategory_id)->all();

				if (!empty($product_details->prdsubcategory_id) &&
					!empty($selected_product->subcategory_id)) {
					$selected_product->subcategory_id =
						$product_details->prdsubcategory_id;
				}

				if (!empty($product_details->prdprdcategory_id)) {
					$selected_product->prdprdcategory_id =
						$product_details->prdprdcategory_id;
				}

				if (!empty($product_details->brand_id)) {
					$selected_product->id = $product_details->brand_id;
				}

            } else {

                $product_product = null;
                $product_subcategory = null;
                $selected_product = null;
            }

            $product_brand = $productsetting->fetchData('brand');
            $product_category = $productsetting->fetchData('category');
            $model = 'edit';
            $data = view('product.model', compact(
                'product_details',
                'model',
                'product_category',
                'product_brand',
                'product_subcategory',
                'product_product',
                'selected_product'
            ));

        } catch (\Exception $e) {
//            return $e->getMessage();
            if ($e->getMessage() == 'validation_error') {
                return '';

            } else if ($e->getMessage() == 'product_not_found') {
                $msg = "Error occured while opening dialog, Invalid product selected";
            } else {
                $msg = "Error occured while opening dialog";
            }

            Log::error(
                "Error @ " . $e->getLine() . " file " . $e->getFile() .
                ":" . $e->getMessage()
            );

            $data = view('layouts.dialog', compact('msg'));
        }
        return $data;
    }
	
	public function destroy($id)
	{
		
		try {
			$this->user_data = new UserData();
			
			$drumbarrel = prd_drumbarrel::find($id);
			
			$is_exist = merchantproduct::where([
				'product_id' => $drumbarrel->product_id,
				'merchant_id' => $this->user_data->company_id()
			])->first();
			
			if (!$is_exist) {
				throw new Exception("Error Processing Request", 1);
			}
			$is_exist->delete();
			$product_id = $drumbarrel->product_id;
			product::find($product_id)->delete();
			$drumbarrel->delete();
			
			$msg = "Product deleted successfully";
            return view('layouts.dialog', compact('msg'));
			
		} catch (\Exception $e) {
			$msg = $e;// "Some error occured";
			return view('layouts.dialog', compact('msg'));
		}
	}
	
	
	public function updatePrice(Request $request)
	{
		try {
			$allInputs = $request->all();
			
			$product_id = $request->product_id;
			$changed = false;
			
			$validation = Validator::make($allInputs, [
				'product_id' => 'required',
				'price' => 'required',
			]);
			
			if ($validation->fails()) {
				throw new Exception("product_not_found", 1);
			}
			
			$prd_drumbarrel = prd_drumbarrel::find($product_id);
			
			if (!$prd_drumbarrel) {
				throw new Exception("product_not_found", 1);
			}
			
			if ($request->price) {				
				if ($prd_drumbarrel->price != $request->price) {
					$prd_drumbarrel->price = $request->price;
					$changed = true;
					$msg = "Price updated successfully";
				}
			}
			
			if ($changed == true) {
				$prd_drumbarrel->save();
				$response = view('layouts.dialog', compact('msg'));
			} else {
				$response = null;
			}
		} catch (\Exception $e) {
			if ($e->getMessage() == 'product_not_found') {
				$msg = "Product not found";
			} else if ($e->getMessage() == 'invalid_cost') {
				$msg = "Invalid cost";
			} else {
				$msg = 'Product Not Found';
			}
			
			//$msg = $e;
			$response = view('layouts.dialog', compact('msg'));
		}
		return $response;
	}
	
	public function updateDeposit(Request $request)
	{
		try {
			$allInputs = $request->all();
			
			$product_id = $request->product_id;
			$changed = false;
			
			$validation = Validator::make($allInputs, [
				'product_id' => 'required',
				'deposit' => 'required',
			]);
			
			if ($validation->fails()) {
				throw new Exception("product_not_found", 1);
			}
			
			$prd_drumbarrel = prd_drumbarrel::find($product_id);
			
			if (!$prd_drumbarrel) {
				throw new Exception("product_not_found", 1);
			}
			
			if ($request->deposit) {				
				if ($prd_drumbarrel->deposit != $request->deposit) {
					$prd_drumbarrel->deposit = $request->deposit;					
					$changed = true;
					$msg = "Deposit updated Successfully";
				}
			}
			
			if ($changed == true) {
				$prd_drumbarrel->save();
				$response = view('layouts.dialog', compact('msg'));
			} else {
				$response = null;
			}
		} catch (\Exception $e) {
			if ($e->getMessage() == 'product_not_found') {
				$msg = "Product not found";
			} else if ($e->getMessage() == 'invalid_cost') {
				$msg = "Invalid cost";
			} else {
				$msg = 'Product Not Found';
			}
			
			$response = view('layouts.dialog', compact('msg'));
		}
		return $response;
	}	
	
}
