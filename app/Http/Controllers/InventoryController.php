<?php

namespace App\Http\Controllers;

use App\Http\Requests\Inventory\Barcode\CreateBarcodeFromRangeValidator;
use App\Models\SettingBarcodeMatrix;
use App\Models\opos_receiptremarks;
use App\Models\opos_refundremarks;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;
use App\Models\inventorycost;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use App\Models\Wholesale;
use App\Http\Functions;
use Milon\Barcode\DNS1D;
use Milon\Barcode\DNS2D;
use Matrix\Exception;
use App\User;
use Log;

use Illuminate\Support\Facades\Validator;

use \App\Models\usersrole;
use \App\Models\role;
use \Illuminate\Support\Facades\Auth;

use \App\Classes\SystemID;

use \App\Models\product;
use \App\Models\productcolor;
use \App\Models\prd_inventory;
use \App\Models\Merchant;
use \App\Models\merchantproduct;
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
use \App\Models\wastagereportremarks;
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
use \App\Models\voucherlist;
use \App\Models\voucher;

use \App\Models\warranty;

use \App\Classes\UserData;
use App\Models\opos_promo_product;

use \App\Models\Stocktakemgmt;
use Illuminate\Support\Arr;


class InventoryController extends Controller
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

		/* Dipak: DON'T TOUCH THIS! YOU HAVE SCREWED UP THE CODE */
		//$model = prd_inventory:: whereNotNull('price');

		$model = new prd_inventory();
		$ids = merchantproduct::where('merchant_id',
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
        	
		/* Dipak: DON'T TOUCH THIS! YOU HAVE SCREWED UP THE CODE */
		//$ids = product::where('ptype', 'inventory')->whereNotNull('name')->whereNotNull('thumbnail_1')->whereNotNull('prdcategory_id')->whereNotNull('prdsubcategory_id')->whereNotNull('prdprdcategory_id')->whereIn('id', $ids)->pluck('id');

		$ids = product::where('ptype', 'inventory')->
			whereIn('id', $ids)->pluck('id');


		$data = $model->whereIn('product_id', $ids)->
		orderBy('created_at', 'asc')->latest()->get();
		
		// Product qty count
		$merchant_id = $this->user_data->company_id();
		$location_data = location::
		join('merchantlocation', 'merchantlocation.location_id', '=', 'location.id')->where('merchant_id', $merchant_id)->whereNotNull('location.branch')->get();
		
		foreach ($data as $key => $value) {
			$price = Wholesale::where('product_id', $value->product_id)->first();

			$check_quantity = $this->check_quantity($value->product_id);
			$data[$key]['quantity'] = $check_quantity;
			$data[$key]['price_two'] = $price ? $price->price : 0;
			$check_transaction = $this->check_transaction($value->product_id);
			$data[$key]['transaction'] = $this->check_transaction($value->product_id);
		}

		$wholesale_prices = new Wholesale();
        	$product_whole_sale_price_and_range = [];
		foreach ($ids as $key => $value) {
        	    $product_whole_sale_price_and_range[$value] = $wholesale_prices->where('product_id', $value)->get()->toArray();
        	}


		$data->map(function ($z) use ($franchise_p_id) {
			$franchise_product = $franchise_p_id->firstWhere('product_id',$z->product_id);

			if (!empty($franchise_product)) {
				$z->price 				= $franchise_product->recommended_price;
				$z->price_two			= 0;//$franchise_product->upper_price;	
				$z->min_price 			= $franchise_product->lower_price;
				$z->max_price 			= $franchise_product->upper_price;
				$z->retail_price 		= $franchise_product->recommended_price;
				$z->franchise_id		= $franchise_product->franchise_id;
				$z->franchise_product  	= true;
			}
		});


		return Datatables::of($data)->
		addIndexColumn()->
		addColumn('systemid', function ($memberList) {
			return $memberList->product_name->systemid;
		})->
		//showing the barcode
		addColumn('systemid_dsp', function ($memberList) {
			return '<p class="os-linkcolor qtyOutput" data-field="systemid_dsp"
				style="cursor: pointer; margin: 0; text-align: center;">
				<a class="os-linkcolor" href="/landing/inventorybarcode/' . 
				$memberList->product_name->systemid . 
				'" target="_blank" style="text-decoration: none;">' . 
				$memberList->product_name->systemid . '</a></p>';
		})->
		
		addColumn('inven_pro_name', function ($memberList) {
		    
			if (!empty($memberList->product_name->thumbnail_1)) {
		    
				$img_src = '/images/product/' .
					$memberList->product_name->id . '/thumb/' .
					$memberList->product_name->thumbnail_1;
				
				$img = "<img src='$img_src' data-field='inven_pro_name' style=' width: 25px;height: 25px;display: inline-block;margin-right: 8px;object-fit:contain;'>";
				
			}  else {
				$img = null;
			} 
			return  !empty($memberList->franchise_product) ? $img.$memberList->product_name->name:
				$img . '<p class="os-linkcolor" data-field="inven_pro_name" 
				style="cursor: pointer; margin: 0;display:inline-block" onclick="details(' . 
				$memberList->product_name->systemid . ')">' .
				 (!empty($memberList->product_name->name) ? $memberList->product_name->name : 
					'Product Name') . '</p>';
			
		})->
		
		addColumn('inven_price', function ($memberList) {
		    
			return !empty($memberList->franchise_product) ? '<p 
				data-field="product_cost_franchise"
				style="text-align:right;margin:0;cursor:pointer"  class="os-linkcolor" >'.
					(!empty($memberList->price) ?
					number_format(($memberList->price / 100), 2) : '0.00').
					'</p>':
				'<p class="os-linkcolor" data-field="inven_price" data-id="retail" 
				style="cursor: pointer; margin: 0; text-align: right;" >' . 
				(!empty($memberList->price) ? number_format(($memberList->price / 100), 2) : 
				'0.00') . '</p>';
		})->
		
		addColumn('inven_price_two', function ($memberList) {
				
			return !empty($memberList->franchise_product) ? '<p style="text-align:right;margin:0">'.
				number_format(($memberList->price_two / 100), 2).'</p>': 
				'<p class="os-linkcolor" data-field="inven_price" data-id="wholesale"
				style="cursor: pointer; margin: 0; text-align: right;" >' .
				 number_format(($memberList->price_two / 100), 2) . '</p>';
		})->
		
		addColumn('inven_qty', function ($memberList) {
			
			return 
				'<p class="os-linkcolor qtyOutput" data-field="inven_qty" 
				style="cursor: pointer; margin: 0; text-align: center;">
				<a class="os-linkcolor" href="/landing/show-inventoryqty-view/' . 
				$memberList->product_name->systemid . '" target="_blank" 
				style="text-decoration: none;">' . 
				(!empty($memberList->quantity) ? $memberList->quantity : '0') .
				 '</a></p>';
		})->
		addColumn('inven_tax', function ($memberList) {
			
			return '<p class="os-linkcolor qtyOutput" data-field="inven_tax" style="cursor: pointer; margin: 0; text-align: center;"><a class="os-linkcolor" href="/landing/show-inventoryqty-view/' . $memberList->product_name->systemid . '" target="_blank" style="text-decoration: none;">-</a></p>';
		})->
		addColumn('inven_cogs', function ($memberList) {
			return '<p class="os-linkcolor cogsOutput" data-target="#inventoryCogsModal" data-toggle="modal" data-field="inven_cogs" style="cursor: pointer; margin: 0; text-align: right;">' . (!empty($memberList->cogs) ? $memberList->cogs : '0.00') . '</p>';
		})->
		
		addColumn('inven_cost', function ($memberList) {
			return '<p class="os-linkcolor qtyOutput" data-field="inven_qty" style="cursor: pointer; margin: 0; text-align: center;"><a class="os-linkcolor" href="/landing/inventorycost/'.$memberList->product_name->id.'" target="_blank" style="text-decoration: none;">' . (!empty($memberList->cost) ? $memberList->cost : '0.00') . '</a></p>';
		})->
		
		addColumn('inven_pending', function ($memberList) {
			return '<p class="os-linkcolor" data-field="inven_pending" style="cursor: pointer; margin: 0; text-align: center;">' . (!empty($memberList->pending) ? $memberList->pending : '0') . '</p>';
		})->
		
		addColumn('inven_loyalty', function ($memberList) {
			return !empty($memberList->franchise_product) ?
				'<p style="text-align:center;margin: 0;">'.(!empty($memberList->loyalty) ?
			   		$memberList->loyalty : '0').'</p>':
				'<p class="os-linkcolor loyaltyOutput" data-field="inven_loyalty" 
					style="cursor: pointer; margin: 0; text-align: center;" 
					data-target="#inventoryLoyaltyModal" data-toggle="modal">
					'.(!empty($memberList->loyalty) ? $memberList->loyalty : '0').'</p>';
		})->
		
		addColumn('deleted', function ($memberList) {
			if ( !empty($memberList->franchise_product) ) {
				return '';
			}

			if ($memberList->transaction == 'True') {
				return '<div><img src="/images/redcrab_50x50.png"
                    style="width:25px;height:25px;cursor:not-allowed;
					filter:grayscale(100%) brightness(200%)"/>
					</div>';

			} else {
				return '<div data-field="deleted"
					data-target="#showMsgModal" data-toggle="modal"
					class="remove">
                    <img src="/images/redcrab_50x50.png"
                    style="width:25px;height:25px;cursor:pointer"/>
					</div>';
			}
		})->
		escapeColumns([])->
		make(true);
	}
	
	public function check_transaction($product_id)
	{
		$sales_count = opos_receiptproduct::where('product_id', $product_id)
			->leftjoin('opos_itemdetails', 'opos_itemdetails.receiptproduct_id', '=', 'opos_receiptproduct.id')
			->leftjoin('opos_receipt', 'opos_receipt.id', '=', 'opos_receiptproduct.receipt_id')
			->leftjoin('opos_receiptdetails', 'opos_receipt.id', '=', 'opos_receiptdetails.receipt_id')
			->count();
	//	$stock_count = StockReport::where('product_id', $product_id)->count();
			
		$stock_count = StockReport::join('stockreportproduct','stockreportproduct.stockreport_id','=','stockreport.id')->
						where('stockreportproduct.product_id', $product_id)->count();

		$wastage = opos_wastageproduct::where('product_id', $product_id)->count();
		$total = $sales_count + $stock_count + $wastage;
		if ($total > 0) {
			return true;
		} else {
			return false;
		}
		
	}

    /**
     * @param product $productId product id
     */
    private function getPromoProductSaleQty($productId){

        // promo product sale qty
        $receiptPromos = opos_receiptproduct::
        select('opos_receiptproduct.quantity', 'opos_receiptdetails.void', 'opos_receiptproduct.promo_id')
            ->leftjoin('opos_receipt', 'opos_receipt.id', '=', 'opos_receiptproduct.receipt_id')
            ->leftjoin('opos_receiptdetails', 'opos_receipt.id', '=', 'opos_receiptdetails.receipt_id')
            ->leftjoin('opos_locationterminal', 'opos_receipt.terminal_id', '=', 'opos_locationterminal.terminal_id')
            ->leftjoin('location', 'location.id', '=', 'opos_locationterminal.location_id')
            ->whereNotNull('opos_receiptproduct.promo_id')
            ->where('opos_receiptdetails.void', '!=', 1)
            ->get();

        $promo_product_sale_qty = 0;
        foreach ($receiptPromos as $receiptPromo) {
            $promoProducts = opos_promo_product::select('product_id', 'quantity')->where('promo_id', $receiptPromo->promo_id)->get();
            foreach($promoProducts as $promoProduct) {
                if ($promoProduct->product_id == $productId) {
                    $promo_product_sale_qty += (int) $receiptPromo->quantity * (int)$promoProduct->quantity;
                }
            }
        }

        return $promo_product_sale_qty;
	}
	
	//##################
	// Quantity function
	// Started
	// ###################

	// top level non location

    public function check_quantity($product_id)
	{
		
		$final_qty = 0;
		$user_data = new UserData();

        //$promo_product_sale_qty = $this->getPromoProductSaleQty($product_id);
	// Product Meta data (reciept, location, document no., etc.)

        $sales_qty = opos_receiptproduct::
			select('opos_receipt.systemid as document_no', 'opos_receiptproduct.receipt_id', 
			'opos_itemdetails.id as item_detail_id', 'opos_itemdetails.receiptproduct_id',
			'opos_receiptproduct.quantity', 'opos_itemdetails.created_at as last_update',
		   	'location.branch as location', 'location.id as locationid', 'opos_receiptdetails.void')
            ->leftjoin('opos_itemdetails', 'opos_itemdetails.receiptproduct_id', '=', 'opos_receiptproduct.id')
            ->leftjoin('opos_receipt', 'opos_receipt.id', '=', 'opos_receiptproduct.receipt_id')
            ->leftjoin('opos_receiptdetails', 'opos_receipt.id', '=', 'opos_receiptdetails.receipt_id')
            ->leftjoin('opos_locationterminal', 'opos_receipt.terminal_id', '=', 'opos_locationterminal.terminal_id')
            ->leftjoin('location', 'location.id', '=', 'opos_locationterminal.location_id')
			->leftjoin('staff','staff.user_id','=','opos_receipt.staff_user_id')
		  	->where('staff.company_id',$user_data->company_id())
            ->where('opos_receiptproduct.product_id', $product_id)
            ->where('opos_receiptdetails.void', '!=', 1)
            ->sum('opos_receiptproduct.quantity');
	
	/*
        	$sales_qty = opos_receiptproduct::where('product_id', $product_id)
            		->leftjoin('opos_itemdetails', 'opos_itemdetails.receiptproduct_id',
			       	'=', 'opos_receiptproduct.id')
			->leftjoin('opos_receipt', 'opos_receipt.id', '=', 'opos_receiptproduct.receipt_id')
			->leftjoin('opos_receiptdetails', 'opos_receipt.id','=', 'opos_receiptdetails.receipt_id')
			->leftjoin('opos_locationterminal', 'opos_receipt.terminal_id', '=', 'opos_locationterminal.terminal_id')
			->leftjoin('location', 'location.id', '=', 'opos_locationterminal.location_id')
			->where('opos_receiptdetails.void', '!=', 1)
			->sum('opos_receiptproduct.quantity');
        */

        $voucherQty = voucher::join('voucherproduct', 'voucherproduct.product_id', '=', 'prd_voucher.product_id')
            ->where('voucherproduct.product_id', $product_id)
            ->whereIn('prd_voucher.type', ['qty'])
            ->sum('voucherproduct.vquantity');

	
	$stock_qty =  StockReport::join('stockreportproduct','stockreportproduct.stockreport_id','=','stockreport.id')->
		leftjoin('stockreportmerchant', 'stockreportmerchant.stockreport_id','=','stockreport.id')->
		where('stockreportmerchant.franchisee_merchant_id',$user_data->company_id())->
		where('stockreportproduct.product_id', $product_id)->
		whereNotIn('stockreport.type', ['transfer'])->
		where('stockreport.status', 'confirmed')->
		sum('stockreportproduct.quantity');
	//StockReport::where('product_id', $product_id)->sum('quantity');
		
		
	$stockreport_qty = StockReport::join('stockreportproduct','stockreportproduct.stockreport_id',
			'=','stockreport.id')->
		leftjoin('stockreportmerchant', 'stockreportmerchant.stockreport_id','=','stockreport.id')->
		where('stockreportmerchant.franchisee_merchant_id',$user_data->company_id())->
		where('stockreportproduct.product_id', $product_id)->
		where('stockreport.type', 'transfer')->
		where('stockreport.status', 'confirmed')->
		sum('stockreportproduct.received');
		
	$stockreportminus_qty = StockReport::join('stockreportproduct','stockreportproduct.stockreport_id',
			'=','stockreport.id')->
		leftjoin('stockreportmerchant', 'stockreportmerchant.stockreport_id','=','stockreport.id')->
		where('stockreportmerchant.franchisee_merchant_id',$user_data->company_id())->
		where('stockreportproduct.product_id', $product_id)->
		where('stockreport.type', 'transfer')->
		where('stockreport.status', 'confirmed')->
		sum('stockreportproduct.quantity');			
		
	$refund_c = opos_refund::join("opos_receiptproduct", 'opos_receiptproduct.id', 
			'=', 'opos_refund.receiptproduct_id')->
		leftjoin('staff','staff.user_id','=','opos_refund.confirmed_user_id')->
		where('staff.company_id', $user_data->company_id())->
		where('opos_receiptproduct.product_id', $product_id)->
		where('refund_type', 'C')->
		count();
	
	$refund_dx = opos_refund::join("opos_receiptproduct", 'opos_receiptproduct.id', 
		'=', 'opos_refund.receiptproduct_id')->
		leftjoin('staff','staff.user_id','=','opos_refund.confirmed_user_id')->
		where('staff.company_id', $user_data->company_id())->
		where('opos_receiptproduct.product_id', $product_id)->
		where('refund_type', 'Dx')->
		count();
		
		$wastage = opos_wastageproduct::where('opos_wastageproduct.product_id', $product_id)->
			leftjoin('opos_wastage','opos_wastage.id','=','opos_wastageproduct.wastage_id')->
			leftjoin('staff','staff.user_id','=','opos_wastage.staff_user_id')->
			where('staff.company_id', $user_data->company_id())->
			sum('opos_wastageproduct.wastage_qty');
	
	$redeemed = DB::table('product_pts_redemption')->
		where('product_id', $product_id)->
		sum('quantity');
		
	$final_qty = $stock_qty + $stockreport_qty + $refund_c - $stockreportminus_qty - $sales_qty - $wastage - $voucherQty - $redeemed - $refund_dx;
		return $final_qty;

	}
	//top level location calculation
	public function location_productqty($product_id, $location_id)
	{

	    // promo product sale qty
	/*
	
		$receiptPromos = opos_receiptproduct::select('opos_receiptproduct.quantity', 
			'opos_receiptdetails.void', 'opos_receiptproduct.promo_id')
 			->leftjoin('opos_receipt', 'opos_receipt.id', '=', 'opos_receiptproduct.receipt_id')
            	 	->leftjoin('opos_receiptdetails', 'opos_receipt.id', '=', 'opos_receiptdetails.receipt_id')
            		->leftjoin('opos_locationterminal', 'opos_receipt.terminal_id', '=', 'opos_locationterminal.terminal_id')
           		->leftjoin('location', 'location.id', '=', 'opos_locationterminal.location_id')
         		->whereNotNull('opos_receiptproduct.promo_id')
            		->where('opos_receiptdetails.void', '!=', 1)
      			->where('location.id', '=', $location_id)
         		   ->get();
	*/

        $promo_product_sale_qty = 0;
	/*  
        foreach ($receiptPromos as $receiptPromo) {
            $promoProducts = opos_promo_product::select('product_id', 'quantity')->where('promo_id', $receiptPromo->promo_id)->get();
            foreach($promoProducts as $promoProduct) {
                if ($promoProduct->product_id == $product_id) {
                    $promo_product_sale_qty += (int) $receiptPromo->quantity * (int)$promoProduct->quantity;
                }
            }
        }
        */
		$location = location::find($location_id);
		$user_data = new UserData();

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
			sum('stockreportproduct.quantity');
		
				
		$stockreport_qty = StockReport::join('stockreportproduct','stockreportproduct.stockreport_id',
				'=','stockreport.id')->
			leftjoin('stockreportmerchant', 'stockreportmerchant.stockreport_id','=','stockreport.id')->
			where('stockreportmerchant.franchisee_merchant_id',$user_data->company_id())->
			where('stockreportproduct.product_id', $product_id)->
			where('stockreport.dest_location_id', $location_id)->
			where('stockreport.type', 'transfer')->
			where('stockreport.status', 'confirmed')->
			sum('stockreportproduct.received');
		
		$stockreportminus_qty = StockReport::join('stockreportproduct','stockreportproduct.stockreport_id',
				'=','stockreport.id')->
			leftjoin('stockreportmerchant', 'stockreportmerchant.stockreport_id','=','stockreport.id')->
			where('stockreportmerchant.franchisee_merchant_id',$user_data->company_id())->
			where('stockreportproduct.product_id', $product_id)->
			where('stockreport.location_id', $location_id)->
			where('stockreport.type', 'transfer')->
			where('stockreport.status', 'confirmed')->
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
			->count();
		
		$refund_dx = opos_refund::join("opos_receiptproduct", 'opos_receiptproduct.id',
		   		'=', 'opos_refund.receiptproduct_id')
			->leftjoin('opos_receipt', 'opos_receipt.id', '=', 'opos_receiptproduct.receipt_id')
			->leftjoin('opos_locationterminal', 'opos_receipt.terminal_id', '=', 'opos_locationterminal.terminal_id')
			->leftjoin('location', 'location.id', '=', 'opos_locationterminal.location_id')
			->where('opos_receiptproduct.product_id', $product_id)
			->where('location.id', $location_id)
			->where('opos_refund.refund_type', 'Dx')
			->count();


        	$voucherQty = voucher::join('voucherproduct', 'voucherproduct.product_id', '=', 'prd_voucher.product_id')
        	    ->where('voucherproduct.location_id', $location_id)
		    ->where('voucherproduct.product_id', $product_id)
		    ->whereIn('prd_voucher.type', ['qty'])
		    ->sum('voucherproduct.vquantity');

		$wastage = opos_wastageproduct::where('product_id', $product_id)->
			where('location_id', $location_id)->	
			leftjoin('opos_wastage','opos_wastage.id','=','opos_wastageproduct.wastage_id')->
			leftjoin('staff','staff.user_id','=','opos_wastage.staff_user_id')->
			where('staff.company_id', $user_data->company_id())->
			sum('wastage_qty');

		$redeemed = DB::table('product_pts_redemption')->
			where('location_id', $location_id)->
			where('product_id', $product_id)->
			sum('quantity');
		
		$final_qty = $stock_qty + $stockreport_qty - $stockreportminus_qty - $product_Sales_qty_data + $refund_c - $wastage - $promo_product_sale_qty 					-  								- $redeemed - $voucherQty - $refund_dx;
		return $final_qty;
	}

	//with respect to location calculation
	function location_barcode_qty($barcode_id, $barcode, $location_id, $is_matrix = false) {
		$user_data = new UserData();
		$location = location::find($location_id);
		
		$stock_qty =  StockReport::join('stockreportproduct','stockreportproduct.stockreport_id','=','stockreport.id')->
			leftjoin('stockreportmerchant', 'stockreportmerchant.stockreport_id','=','stockreport.id')->
			where([
				'stockreport.location_id' 						=> $location_id,
				'stockreportproduct.barcode'					=> $barcode,
				'stockreportmerchant.franchisee_merchant_id'	=> $user_data->company_id(),
			])->
			whereNotIn('stockreport.type', ['transfer'])->
			where('stockreport.status', 'confirmed')->
			sum('stockreportproduct.quantity');
		
		$stockreport_qty = StockReport::join('stockreportproduct','stockreportproduct.stockreport_id','=','stockreport.id')->
			leftjoin('stockreportmerchant', 'stockreportmerchant.stockreport_id','=','stockreport.id')->
			where('stockreportproduct.barcode', $barcode)->
			where('stockreport.dest_location_id', $location_id)->
			where('stockreportmerchant.franchisee_merchant_id',$user_data->company_id())->
			where('stockreport.type', 'transfer')->
			where('stockreport.status', 'confirmed')->
			sum('stockreportproduct.received');
		
		$stockreportminus_qty = StockReport::join('stockreportproduct','stockreportproduct.stockreport_id','=','stockreport.id')->
			leftjoin('stockreportmerchant', 'stockreportmerchant.stockreport_id','=','stockreport.id')->
			where('stockreportmerchant.franchisee_merchant_id',$user_data->company_id())->
			where('stockreportproduct.barcode', $barcode)->
			where('stockreport.location_id', $location_id)->
			where('stockreport.type', 'transfer')->
			where('stockreport.status', 'confirmed')->
			sum('stockreportproduct.quantity');

		$field_name = $is_matrix ? 'productbmatrixbarcodelocation_id':'productbarcodelocation_id';
		$field_name = 'opos_receiptproductbarcode.'.$field_name;
		
		$product_Sales_qty_data =  DB::table('opos_receiptproductbarcode')->
			select("opos_receiptproductbarcode.quantity as qty")->
			leftjoin('opos_receiptproduct','opos_receiptproduct.id', '=' , 
				'opos_receiptproductbarcode.receiptproduct_id')->
			leftjoin('opos_receipt', 'opos_receipt.id', '=', 'opos_receiptproduct.receipt_id')->
			leftjoin('opos_locationterminal', 'opos_receipt.terminal_id', '=', 'opos_locationterminal.terminal_id')->
			leftjoin('location', 'location.id', '=', 'opos_locationterminal.location_id')->
			leftjoin('opos_itemdetails',  'opos_itemdetails.receiptproduct_id', '=', 'opos_receiptproduct.id')->
			leftjoin('opos_receiptdetails','opos_receipt.id', '=','opos_receiptdetails.receipt_id')->
			leftjoin('staff','staff.user_id','=','opos_receipt.staff_user_id')->
		  	where('staff.company_id',$user_data->company_id())->
			where('opos_receiptdetails.void', '!=', 1)->
			where($field_name,$barcode_id)->
			where('location.id', '=', $location_id)->
			get()->sum('qty');

		$wastage_qty = DB::table('opos_wastageproductbarcode')->
			join('opos_wastageproduct', 'opos_wastageproduct.id','=','opos_wastageproductbarcode.wastageproduct_id')->
			leftjoin('opos_wastage','opos_wastage.id','=','opos_wastageproduct.wastage_id')->
			leftjoin('staff','staff.user_id','=','opos_wastage.staff_user_id')->
			where('staff.company_id', $user_data->company_id())->
			where([
				'opos_wastageproduct.location_id' 	=> $location_id,
				'opos_wastageproductbarcode.barcode'	=> $barcode
			])->
			whereNull('opos_wastageproductbarcode.deleted_at')->
			sum('opos_wastageproductbarcode.barcode_quantity');
		
		$refund_c = opos_refund::join("opos_receiptproduct", 'opos_receiptproduct.id',
		   		'=', 'opos_refund.receiptproduct_id')
			->leftjoin('opos_receipt', 'opos_receipt.id', '=', 'opos_receiptproduct.receipt_id')
			->leftjoin('opos_locationterminal', 'opos_receipt.terminal_id', '=', 'opos_locationterminal.terminal_id')
			->leftjoin('location', 'location.id', '=', 'opos_locationterminal.location_id')
			->leftjoin('staff','staff.user_id','=','opos_refund.confirmed_user_id')
			->where('staff.company_id', $user_data->company_id())
			->where('opos_receiptproduct.product_id', $barcode_id)
			->where('location.id', $location_id)
			->where('opos_refund.refund_type', 'C')
			->count();

		$refund_dx = opos_refund::join("opos_receiptproduct", 'opos_receiptproduct.id',
		   		'=', 'opos_refund.receiptproduct_id')
			->leftjoin('opos_receipt', 'opos_receipt.id', '=', 'opos_receiptproduct.receipt_id')
			->leftjoin('opos_locationterminal', 'opos_receipt.terminal_id', '=', 'opos_locationterminal.terminal_id')
			->leftjoin('location', 'location.id', '=', 'opos_locationterminal.location_id')
			->leftjoin('staff','staff.user_id','=','opos_refund.confirmed_user_id')
			->where('staff.company_id', $user_data->company_id())
			->where('opos_receiptproduct.product_id', $barcode_id)
			->where('location.id', $location_id)
			->where('opos_refund.refund_type', 'Dx')
			->count();

		$final_qty = $stock_qty + $stockreport_qty + $refund_c - $stockreportminus_qty - $product_Sales_qty_data - $wastage_qty - $refund_dx;
		return $final_qty;
	}

	//non location calculation	
	function barcode_qty_v2($barcode_id, $barcode, $is_matrix = false) {
		$user_data = new UserData();
		$stock_qty =  StockReport::join('stockreportproduct','stockreportproduct.stockreport_id','=','stockreport.id')->
			leftjoin('stockreportmerchant', 'stockreportmerchant.stockreport_id','=','stockreport.id')->
			where([
				'stockreportproduct.barcode' => $barcode
			])->
			where('stockreportmerchant.franchisee_merchant_id',$user_data->company_id())->
			whereNotIn('stockreport.type', ['transfer'])->
			where('stockreport.status', 'confirmed')->
			sum('stockreportproduct.quantity');
		
		$stockreport_qty = StockReport::join('stockreportproduct','stockreportproduct.stockreport_id','=','stockreport.id')->
			leftjoin('stockreportmerchant', 'stockreportmerchant.stockreport_id','=','stockreport.id')->
			where('stockreportmerchant.franchisee_merchant_id',$user_data->company_id())->
			where('stockreportproduct.barcode', $barcode)->
			where('stockreport.type', 'transfer')->
			where('stockreport.status', 'confirmed')->
			sum('stockreportproduct.received');
		$stockreportminus_qty = StockReport::join('stockreportproduct','stockreportproduct.stockreport_id','=','stockreport.id')->
			leftjoin('stockreportmerchant', 'stockreportmerchant.stockreport_id','=','stockreport.id')->
			where('stockreportmerchant.franchisee_merchant_id',$user_data->company_id())->
			where('stockreportproduct.barcode', $barcode)->
			where('stockreport.type', 'transfer')->
			where('stockreport.status', 'confirmed')->
			sum('stockreportproduct.quantity');
		$field_name = $is_matrix ? 'productbmatrixbarcodelocation_id':'productbarcodelocation_id';
		$field_name = 'opos_receiptproductbarcode.'.$field_name;
		
		$product_Sales_qty_data =  DB::table('opos_receiptproductbarcode')->
			select("opos_receiptproductbarcode.quantity as qty")->
			leftjoin('opos_receiptproduct','opos_receiptproduct.id', '=' , 
				'opos_receiptproductbarcode.receiptproduct_id')->
			leftjoin('opos_receipt', 'opos_receipt.id', '=', 'opos_receiptproduct.receipt_id')->
			leftjoin('opos_locationterminal', 'opos_receipt.terminal_id', '=', 'opos_locationterminal.terminal_id')->
			leftjoin('location', 'location.id', '=', 'opos_locationterminal.location_id')->
			leftjoin('opos_itemdetails',  'opos_itemdetails.receiptproduct_id', '=', 'opos_receiptproduct.id')->
			leftjoin('opos_receiptdetails','opos_receipt.id', '=','opos_receiptdetails.receipt_id')->
			leftjoin('staff','staff.user_id','=','opos_receipt.staff_user_id')->
		  	where('staff.company_id',$user_data->company_id())->
			where('opos_receiptdetails.void', '!=', 1)->
			where($field_name,$barcode_id)->
			get()->sum('qty');
	
		$wastage_qty = DB::table('opos_wastageproductbarcode')->
			where([
				'opos_wastageproductbarcode.barcode'	=> $barcode
			])->
			leftjoin('opos_wastageproduct','opos_wastageproduct.id',
				'=','opos_wastageproductbarcode.wastageproduct_id')->
			leftjoin('opos_wastage','opos_wastage.id','=','opos_wastageproduct.wastage_id')->
			leftjoin('staff','staff.user_id','=','opos_wastage.staff_user_id')->
			where('staff.company_id', $user_data->company_id())->
			whereNull('opos_wastageproductbarcode.deleted_at')->
			sum('opos_wastageproductbarcode.barcode_quantity');

		$refund_c = opos_refund::join("opos_receiptproduct", 'opos_receiptproduct.id',
		   		'=', 'opos_refund.receiptproduct_id')
			->leftjoin('opos_receipt', 'opos_receipt.id', '=', 'opos_receiptproduct.receipt_id')
			->leftjoin('opos_locationterminal', 'opos_receipt.terminal_id', '=', 'opos_locationterminal.terminal_id')
			->leftjoin('location', 'location.id', '=', 'opos_locationterminal.location_id')
			->leftjoin('staff','staff.user_id','=','opos_refund.confirmed_user_id')
			->where('staff.company_id', $user_data->company_id())
			->where('opos_receiptproduct.product_id', $barcode_id)
			->where('opos_refund.refund_type', 'C')
			->count();

		
		$refund_dx = opos_refund::join("opos_receiptproduct", 'opos_receiptproduct.id',
		   		'=', 'opos_refund.receiptproduct_id')
			->leftjoin('opos_receipt', 'opos_receipt.id', '=', 'opos_receiptproduct.receipt_id')
			->leftjoin('opos_locationterminal', 'opos_receipt.terminal_id', '=', 'opos_locationterminal.terminal_id')
			->leftjoin('location', 'location.id', '=', 'opos_locationterminal.location_id')
			->leftjoin('staff','staff.user_id','=','opos_refund.confirmed_user_id')
			->where('staff.company_id', $user_data->company_id())
			->where('opos_receiptproduct.product_id', $barcode_id)
			->where('opos_refund.refund_type', 'Dx')
			->count();


		$final_qty = $stock_qty + $stockreport_qty + $refund_c - $stockreportminus_qty - $product_Sales_qty_data - $wastage_qty - $refund_dx;
		return $final_qty;
	}

	//########################
	//	Qty function end
	//	#######################
	public function getInventoryQty()
	{
		return Datatables::of(array('',))->
		addIndexColumn()->
		addColumn('inventoryqtry_pro_id', function ($memberList) {
			return '<p class="os-linkcolor" data-field="inventoryqtry_pro_id" style="cursor: pointer; margin: 0; text-align: center;">24576553</p>';
		})->
		
		addColumn('inventoryqtry_type', function ($memberList) {
			return '<p data-field="inventoryqtry_type" style="cursor: pointer; margin: 0;">Dummy</p>';
		})->
		
		addColumn('inventoryqtry_lastup', function ($memberList) {
			return '<p data-field="inventoryqtry_lastup" style="cursor: pointer; margin: 0;" data-toggle="modal">24Jan19 23:46</p>';
		})->
		
		addColumn('inventoryqtry_location', function ($memberList) {
			return '<p data-field="inventoryqtry_location" style="cursor: pointer; margin: 0;" data-toggle="modal">LF-Kajang</p>';
		})->
		
		addColumn('inventoryqtry_qty', function ($memberList) {
			return '<p data-field="inventoryqtry_qty" style="cursor: pointer; margin: 0; text-align: center;">7</p>';
		})->
		
		escapeColumns([])->
		make(true);
	}
	
	
	public function edit($id)
	{
	
	}
	
	public function showEditModal(Request $request)
	{
		Log::debug('***** showEditModal() *****');

		try {
			$allInputs = $request->all();
			$id = $request->get('id');
			$fieldName = $request->get('field_name');
			
			
			$validation = Validator::make($allInputs, [
				'id' => 'required',
				'field_name' => 'required'
			]);
			
			if ($validation->fails()) {
				$response = (new ApiMessageController())->
					validatemessage($validation->errors()->first());

				Log::debug('response='. $response);
				
			} else {
				$inventory = prd_inventory::where('id', $id)->first();

                $wholesales = Wholesale::
                    where('product_id', $inventory->product_id)
                    ->get()
                    ->toArray();

				Log::debug('id='.$id);
				Log::debug('fieldName='.$fieldName);
				Log::debug('inventory='.json_encode($inventory));
				Log::debug('wholesales='.
					json_encode($wholesales));

				return view('inventory.inventory-modals',
					compact([
						'id',
						'fieldName',
						'inventory',
						'wholesales'
					]));
			}
			
		} catch (\Illuminate\Database\QueryException $ex) {
			Log::error($ex);
			$response = (new ApiMessageController())->queryexception($ex);
		}
	}
	
	
	public function store(Request $request)
	{
		//Create a new product here
		try {
			$this->user_data = new UserData();
			$company = DB::table('company')->where('id', $this->user_data->company_id())->
				first();
			$SystemID = new SystemID('product');
			$product = new product();
			$inventory = new prd_inventory();
			$merchantproduct = new merchantproduct();

			$product->systemid = $SystemID;
			$product->ptype = 'inventory';
			$product->save();
			
			$inventory->product_id = $product->id;
		
			if (!empty($company->loyalty_pgm))
				$inventory->loyalty = $company->loyalty_pgm;

			$inventory->save();
			
			$merchantproduct->product_id = $product->id;
			$merchantproduct->merchant_id = $this->user_data->company_id();
			$merchantproduct->save();
			
			
			$msg = "
Product added successfully";
			return view('layouts.dialog', compact('msg'));
			
		} catch (\Exception $e) {
			$msg = $e;//"Some error occured";
			return view('layouts.dialog', compact('msg'));
		}
	}
	
	public function update_remark(Request $request)
	{
		try {
			$id = Auth::user()->id;
			$item_id = $request->get('item_id');
			$item_remark = $request->get('item_remark') ? $request->get('item_remark') : '';
			$remark_type = $request->get('remark_type');

			Log::debug('remark_type='.$remark_type);


			// $opos_itemdetailsremarks = opos_itemdetailsremarks::where('user_id',$id)->where('itemdetails_id',$item_id)->orderby('id',"desc")-> first();

			if ($remark_type == 'stock') {
				//pranto code
				$count = stockreportremarks::where('stockreport_id','=',$item_id)->count();
				if ($count <=0) {
					$stockreportremarks = new stockreportremarks();
					$stockreportremarks->stockreport_id = $item_id;
					$stockreportremarks->user_id = $id;
					$stockreportremarks->remarks = $item_remark;
					$stockreportremarks->save();
				}else{
					stockreportremarks::where('stockreport_id', $item_id)->update(array(
					'remarks'=> $item_remark,
					'user_id' => $id
					));
				}
			} else if($remark_type == 'cash_sale_receipt'){
				opos_receiptremarks::create([
			        'receipt_id' => $item_id,
                    'user_id' => $id,
                    'remarks' => $item_remark
                ]);

            } else if($remark_type == 'refund'){

			    opos_refundremarks::create([
			        'refund_id' => $item_id,
                    'user_id' => $id,
                    'remarks' => $item_remark
                ]);

			} else if($remark_type == 'wastage'){
				$wastagereportremarks = new wastagereportremarks();
				$wastagereportremarks->wastage_id = $item_id;
				$wastagereportremarks->user_id = $id;
				$wastagereportremarks->remarks = $item_remark;
				$wastagereportremarks->save();
			} else {
				$opos_itemdetailsremarks = new opos_itemdetailsremarks();
				$opos_itemdetailsremarks->itemdetails_id = $item_id;
				$opos_itemdetailsremarks->user_id = $id;
				$opos_itemdetailsremarks->remarks = $item_remark;
				
				$opos_itemdetailsremarks->save();
			}
			$msg = "Item remarks saved successfully";
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
	
	public function update_barcode_sku(Request $request)
	{
		try {
			$id = Auth::user()->id;
			
			$barcode_id = $request->get('barcode_id');
			$sku = $request->get('sku');
			$is_main = $request->get('is_main');
			if ($is_main == 0) {
				$productbarcode = productbarcode::where('id', $barcode_id)->first();
				$productbarcode->sku = $sku;
				$productbarcode->save();
			} else {
				$productbarcode = product::where('id', $barcode_id)->first();
				$productbarcode->sku = $sku;
				$productbarcode->save();
			}
			$msg = "SKU saved successfully";
			$data = view('layouts.dialog', compact('msg'));

		} catch (\Exception $e) {
			$msg = "Error occured while Saving remarks";
			
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
			
			$barcode_id = $request->get('barcode_id');
			$name = $request->get('name');
			$is_main = $request->get('is_main');
			if ($is_main == 0) {
				$productbarcode = productbarcode::where('id', $barcode_id)->first();
				$productbarcode->name = $name;
				$productbarcode->save();
			} else {
				$productbarcode = product::where('id', $barcode_id)->first();
				$productbarcode->name = $name;
				$productbarcode->save();
			}
			$msg = "Barcode name saved successfully";
			$data = view('layouts.dialog', compact('msg'));

		} catch (\Exception $e) {
			$msg = "Error occured while Saving remarks";
			
			Log::error(
				"Error @ " . $e->getLine() . " file " . $e->getFile() .
				":" . $e->getMessage()
			);
			
			$data = view('layouts.dialog', compact('msg'));
		}
		return $data;
	}

	
	public function update(Request $request)
	{
		try {
			$allInputs = $request->all();
			$prd_inventory_id = $request->get('prd_inventory_id');
			$changed = false;

			$msg = NULL;
			
			$validation = Validator::make($allInputs, [
				'prd_inventory_id' => 'required',
			]);
			
			if ($validation->fails()) {
				throw new Exception("product_not_found", 1);
			}
			
			$prd_inventory = prd_inventory::find($prd_inventory_id);
			if (!$prd_inventory) {
				throw new Exception("product_not_found", 1);
			}
            $product_id_for_wholesale = $prd_inventory->product_id;

			if ($request->has('price')) {
				$new_price =	(int) ($request->price * 100);
				if ($prd_inventory->price != $new_price) {
					$prd_inventory->price = $new_price;
					$changed = true;
					$msg = "Price updated successfully";
				}
			}

			if ($changed == true) {
				$prd_inventory->save();

                // update whole sale
                if($request->checker == "wholesale"){
                	$result = $this->updateWholesale($product_id_for_wholesale, $request->all());
                	if($result != ''){
                		$msg = $result;
                	}
                }
			} else {

                // update whole sale
                if($request->checker == "wholesale"){
                	$result = $this->updateWholesale($product_id_for_wholesale, $request->all());
                	if($result != ''){
                		$msg = $result;
                	}
                }
			}

            if($msg == NULL){
            	$response = NULL;
            }
            else{
            	$response = view('layouts.dialog', compact('msg'));
            }
			
		} catch (\Exception $e) {
			if ($e->getMessage() == 'product_not_found') {
				$msg = "Product not found";
			} else if ($e->getMessage() == 'invalid_cost') {
				$msg = "Invalid cost";
			} else {
				$msg = $e->getMessage();
			}
			
			//$msg = $e;
			$response = view('layouts.dialog', compact('msg'));
		}
		return $response;
	}

	function updateWholesale($product_id, $data) {

		Log::debug('updateWholesale:'.json_encode($data));


        $wholesale_update = Wholesale::
			where(['product_id' => $product_id])->get();

			$changed = false;
			$last_position = count($data) - (count($wholesale_update) + 3);
			$msg = '';

        // insert
        for($i=1; $i< $last_position ; $i++){
            $wholsale_input_tu_key = 'wholsale_input_tu_'.$i;
            $wholsale_input_price_key = 'wholsale_input_price_'.$i;

            $wholsale_input_tu_key_previous = 'wholsale_input_tu_'. ($i-1);
            $wholsale_input_price_key_previous = 'wholsale_input_price_'. ($i-1);

            if($i == 1){
        		if($data[$wholsale_input_price_key] && $data[$wholsale_input_tu_key]){
        			$previous_wholesale = Wholesale::where("product_id", $product_id)->
						where("position", $i)->first();

        			if($previous_wholesale){
        				if($previous_wholesale->unit != $data[$wholsale_input_tu_key] ||
						   $previous_wholesale->price != ($data[$wholsale_input_price_key] * 100)){
        					Wholesale::updateOrCreate(["product_id" => $product_id, "position" => $i], [
			            		"product_id" => $product_id,
			            		"position" => $i,
			            		"funit" => $i,
			            		"unit" => (int) $data[$wholsale_input_tu_key],
			            		"price" => (int) ($data[$wholsale_input_price_key] * 100),
			            		"deleted_at" => NULL,
			            	]); $msg = "Price updated successfully";
        				}
        			} else{
        				Wholesale::updateOrCreate(["product_id" => $product_id, "position" => $i], [
		            		"product_id" => $product_id,
		            		"position" => $i,
		            		"funit" => $i,
		            		"unit" => (int) $data[$wholsale_input_tu_key],
		            		"price" => (int) ($data[$wholsale_input_price_key] * 100),
		            		"deleted_at" => NULL,
		            	]);
		            	$msg = "Price updated successfully";
        			}
	            }
            } else{
            	if($data[$wholsale_input_price_key] &&
				   $data[$wholsale_input_tu_key] &&
				   $data[$wholsale_input_tu_key_previous] &&
				   ($data[$wholsale_input_tu_key] > ($data[$wholsale_input_tu_key_previous] + 1)) &&
				   ($data[$wholsale_input_price_key] < $data[$wholsale_input_price_key_previous])){

        			$previous_wholesale = Wholesale::where("product_id", $product_id)->
						where("position", $i)->first();

        			if($previous_wholesale){
        				if($previous_wholesale->unit != $data[$wholsale_input_tu_key] ||
						   $previous_wholesale->price != ($data[$wholsale_input_price_key] * 100)){
	        				Wholesale::updateOrCreate(["product_id" => $product_id, "position" => $i], [
			            		"product_id" => $product_id,
			            		"position" => $i,
			            		"funit" => ((int) $data[$wholsale_input_tu_key_previous]) + 1,
			            		"unit" => (int) $data[$wholsale_input_tu_key],
			            		"price" => (int) ($data[$wholsale_input_price_key] * 100),
				            	"deleted_at" => NULL,
			            	]);
			            	$msg = "Price updated successfully";
        				}
        			} else {
        				Wholesale::updateOrCreate(["product_id" => $product_id, "position" => $i], [
		            		"product_id" => $product_id,
		            		"position" => $i,
		            		"funit" => ((int) $data[$wholsale_input_tu_key_previous]) + 1,
		            		"unit" => (int) $data[$wholsale_input_tu_key],
		            		"price" => (int) ($data[$wholsale_input_price_key] * 100),
			            	"deleted_at" => NULL,
		            	]);
		            	$msg = "Price updated successfully";
        			}
	            }
	            if($data[$wholsale_input_tu_key] && $data[$wholsale_input_price_key] && $data[$wholsale_input_price_key] > $data[$wholsale_input_price_key_previous]){
	            	$msg = "Price must be lower than previous tier";
	            }
	            /*if($data[$wholsale_input_tu_key] && $data[$wholsale_input_price_key] && $data[$wholsale_input_tu_key] < ($data[$wholsale_input_tu_key_previous] + 1)){
	            	$msg = "Input unit cannot be lower than the starting unit";
	            }*/
            }
        }
        return $msg;
     }
	

	public function destroy($id)
	{
		
		try {
			$this->user_data = new UserData();
			
			$inventory = prd_inventory::where("id", $id)->first();

			if(!$inventory){
				$msg = "Product not found, please refresh your browser";
			}
			else{
				$is_exist = merchantproduct::where([
					'product_id' => $inventory->product_id,
					'merchant_id' => $this->user_data->company_id()
				])->first();
				
				if (!$is_exist) {
					throw new Exception("Error Processing Request", 1);
				}
				$is_exist->delete();
				$product_id = $inventory->product_id;
				product::find($product_id)->delete();
				$inventory->delete();
				
				// \DB::table('client')->where('id', $id)->delete();
				$msg = "Product deleted successfully";
			}
			return view('layouts.dialog', compact('msg'));
			
		} catch (\Exception $e) {
			$msg = $e;// "Some error occured";
			return view('layouts.dialog', compact('msg'));
		}
	}
	
	
	public function showInventoryView()
	{
		return view('inventory.inventory');
	}
	
	
	public function showInventoryQtyView($systemid)
	{

		$user_data = new UserData();
		
		// Get product from system id
		$product = product::where('systemid', $systemid)->first();
		
		// Product inventory data
		$model = new prd_inventory();
		$product_data = $model->where('product_id', $product->id)->first();
		
		// Product Meta data (reciept, location, document no., etc.)
		$opos_product = opos_receiptproduct::
			select('opos_receipt.systemid as document_no', 'opos_receiptproduct.receipt_id', 
			'opos_receiptproduct.promo_id'  ,'opos_itemdetails.id as item_detail_id', 
			'opos_itemdetails.receiptproduct_id', 'opos_receiptproduct.quantity', 
			'opos_receiptproduct.created_at as last_update', 'location.branch as location', 
			'location.id as locationid', 'opos_receiptdetails.void')
			->leftjoin('opos_itemdetails', 'opos_itemdetails.receiptproduct_id', '=', 'opos_receiptproduct.id')
			->leftjoin('opos_receipt', 'opos_receipt.id', '=', 'opos_receiptproduct.receipt_id')
			->leftjoin('opos_receiptdetails', 'opos_receipt.id', '=', 'opos_receiptdetails.receipt_id')
			->leftjoin('opos_locationterminal', 'opos_receipt.terminal_id', '=', 'opos_locationterminal.terminal_id')
			->leftjoin('location', 'location.id', '=', 'opos_locationterminal.location_id')
			->leftjoin('staff','staff.user_id','=','opos_receipt.staff_user_id')
		  	->where('staff.company_id',$user_data->company_id())
			->where('opos_receiptproduct.product_id', $product->id)
			//->distinct()
			->orderby('opos_receiptproduct.id', 'DESC')
			->get();

	//	dd($opos_product);

		// Getting promo receipt
        /*
        $receiptPromos = opos_receiptproduct::
        select('opos_receipt.systemid as document_no', 'opos_receiptproduct.receipt_id', 'opos_itemdetails.id as item_detail_id', 'opos_itemdetails.receiptproduct_id', 'opos_receiptproduct.quantity', 'opos_itemdetails.created_at as last_update', 'location.branch as location', 'location.id as locationid', 'opos_receiptdetails.void', 'opos_receiptproduct.promo_id')
            ->leftjoin('opos_itemdetails', 'opos_itemdetails.receiptproduct_id', '=', 'opos_receiptproduct.id')
            ->leftjoin('opos_receipt', 'opos_receipt.id', '=', 'opos_receiptproduct.receipt_id')
            ->leftjoin('opos_receiptdetails', 'opos_receipt.id', '=', 'opos_receiptdetails.receipt_id')
            ->leftjoin('opos_locationterminal', 'opos_receipt.terminal_id', '=', 'opos_locationterminal.terminal_id')
            ->leftjoin('location', 'location.id', '=', 'opos_locationterminal.location_id')
            ->whereNotNull('opos_receiptproduct.promo_id')
            ->orderby('opos_itemdetails.id', 'DESC')
            ->get();



        $item_count = count($opos_product);
        foreach ($receiptPromos as $receiptPromo) {
            $promoProducts = opos_promo_product::select('product_id', 'quantity')->where('promo_id', $receiptPromo->promo_id)->get();
            foreach($promoProducts as $promoProduct) {
                if ($promoProduct->product_id == $product->id) {
                    $receiptPromo->quantity = (int) $receiptPromo->quantity * (int)$promoProduct->quantity;
                    $opos_product[$item_count] = $receiptPromo;
                    $item_count++;
                }
            }
        }
        */

        $this->user_data = new UserData();

		$refund = opos_refund::select('opos_receipt.systemid as document_no',
			'opos_receiptproduct.receipt_id', 
			'opos_receiptproduct.quantity', 'opos_refund.refund_type',
			'opos_itemdetails.id as item_detail_id', 'opos_itemdetails.receiptproduct_id', 
			'opos_refund.created_at as last_update', 'location.branch as location', 
			'location.id as locationid', 'opos_receiptdetails.void')
			->join("opos_receiptproduct", 'opos_receiptproduct.id', '=', 'opos_refund.receiptproduct_id')
			->leftjoin('opos_itemdetails', 'opos_itemdetails.receiptproduct_id', '=', 'opos_receiptproduct.id')
			->leftjoin('opos_receipt', 'opos_receipt.id', '=', 'opos_receiptproduct.receipt_id')
			->leftjoin('opos_receiptdetails', 'opos_receipt.id', '=', 'opos_receiptdetails.receipt_id')
			->leftjoin('opos_locationterminal', 'opos_receipt.terminal_id', '=', 'opos_locationterminal.terminal_id')
			->leftjoin('location', 'location.id', '=', 'opos_locationterminal.location_id')
			->leftjoin('staff','staff.user_id','=','opos_refund.confirmed_user_id')
			->where('staff.company_id', $this->user_data->company_id())
			->whereIn('opos_refund.refund_type', array('C', 'Dx'))
			->where('opos_receiptproduct.product_id', $product->id)->get();
	//	dd($refund );
        $item_count = count($opos_product);
		foreach ($refund as $key => $value) {
			$refund_type = $value->refund_type;
			if ($refund_type == "C" || $refund_type == "Dx") {
				// if($refund_type == "C" || $refund_type == "Cx" || $refund_type == "Dx"){
				// if($refund_type == 'C' || $refund_type == "Dx") {
				if ($refund_type == 'C') {
					$opos_product[$item_count] = $value;
					$opos_product[$item_count]->sales_type = 'Refund C';
					$opos_product[$item_count]->quantity = 1;
					$item_count++;
				}
				// if($refund_type == 'Cx' || $refund_type == "Dx") {
				if ($refund_type == "Dx") {
					$opos_product[$item_count] = $value;
					$opos_product[$item_count]->sales_type = 'Refund Dx';
					$opos_product[$item_count]->quantity = -1;
					$item_count++;
				}
			}
		}

        $wastage = opos_wastageproduct::select('product.systemid as productsys_id', 'product.id as product_id', 
			'product.thumbnail_1', 'product.name', 'opos_wastage.systemid as document_no',
			DB::raw('SUM(opos_wastageproduct.wastage_qty) as quantity'), 'opos_wastageproduct.created_at as last_update', 
			'location.branch as location', 'location.id as locationid')
		->join('opos_wastage', 'opos_wastage.id', '=', 'opos_wastageproduct.wastage_id')
		->join('location', 'location.id', '=', 'opos_wastageproduct.location_id')
		->join("product", 'opos_wastageproduct.product_id', '=', 'product.id')
		->leftjoin('staff','staff.user_id','=','opos_wastage.staff_user_id')
		->where('staff.company_id', $user_data->company_id())
		->where('opos_wastageproduct.product_id', $product->id)
		->groupBy('document_no')
		->get();

        $item_count = count($opos_product);
		foreach ($wastage as $key => $value) {
			$opos_product[$item_count] = $value;
			$opos_product[$item_count]->wastage = 1;
			$opos_product[$item_count]->sales_type = "Wastage & Damage";
			$opos_product[$item_count]->quantity = 0 - $value->quantity;
			$item_count++;
		}


		$voucher_data = voucherproduct::select('product.systemid as document_no',
			'voucherproduct.product_id as product_id','voucherproduct.created_at as last_update',
			'product.*','prd_voucher.*','voucherproduct.*','location.branch as location', 
			'location.id as locationid')
			->join('product','product.id','=','voucherproduct.voucher_id')
            ->join('prd_voucher','prd_voucher.product_id','=','product.id')
            // ->join('voucherlist','voucherlist.voucher_id', '=','prd_voucher.id')
            ->leftjoin('location','voucherproduct.location_id','=','location.id')
            ->where('voucherproduct.product_id',$product->id)
            ->whereIn('prd_voucher.type', ['qty'])
            ->where('voucherproduct.vquantity','>', 0)
            ->get();

        $item_count = count($opos_product);
        foreach ($voucher_data as $key => $value) {
            $opos_product[$item_count] = $value;
            $opos_product[$item_count]->voucher = 1;
            $opos_product[$item_count]->document_no = $value->systemid;
            $opos_product[$item_count]->sales_type = "Voucher";
            $opos_product[$item_count]->quantity = 0 - $value->vquantity;
            $item_count++;
        }


		$StockReport = StockReport::select('stockreport.systemid as document_no', 
			'stockreport.id as stockreport_id', 'stockreportproduct.quantity', 
			'stockreport.type as refund_type', 'stockreport.created_at as last_update', 
			'location.branch as location', 'location.id as locationid')
			->join('stockreportproduct','stockreportproduct.stockreport_id','=','stockreport.id')
			->leftjoin('location', 'location.id', '=', 'stockreport.location_id')
			->leftjoin('stockreportmerchant', 'stockreportmerchant.stockreport_id',
				'=','stockreport.id')
			->where('stockreportmerchant.franchisee_merchant_id',$this->user_data->company_id())
			->where('stockreportproduct.product_id', $product->id)
			->where('stockreport.status', 'confirmed')->distinct()->get();

		
		$item_count = count($opos_product);
		foreach ($StockReport as $key => $value) {
			$opos_product[$item_count] = $value;
			if ($value->refund_type == 'stockin') {
				$sales = 'Stock In';
			} else if ($value->refund_type == 'stockout') {
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
	//	dd($StockReport);
		$StockReportTRout = StockReport::select('stockreport.systemid as document_no', 
			'stockreport.id as stockreport_id', 'stockreportproduct.quantity',
			'stockreport.type as refund_type', 'stockreport.created_at as last_update', 
			'location.branch as location', 'location.id as locationid')->
			join('location', 'location.id', '=', 'stockreport.location_id')->
			join('stockreportproduct', 'stockreport.id', '=', 'stockreportproduct.stockreport_id')->
			leftjoin('stockreportmerchant', 'stockreportmerchant.stockreport_id','=','stockreport.id')->
			where('stockreportmerchant.franchisee_merchant_id',$this->user_data->company_id())->
			where('stockreportproduct.product_id', $product->id)->
			where('stockreport.type', 'transfer')->
			where('stockreport.status', 'confirmed')->
			distinct()->
			get();
		//dd($StockReportTRout);
		
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
			
			$StockReportTRin = StockReport::select('stockreport.systemid as document_no', 
					'stockreport.id as stockreport_id', 'stockreportproduct.quantity',
			   		'stockreportproduct.received', 'stockreport.type as refund_type', 
					'stockreport.created_at as last_update', 'location.branch as location', 
					'location.id as locationid')->
				join('location', 'location.id', '=', 'stockreport.dest_location_id')->
				join('stockreportproduct', 'stockreport.id', '=', 'stockreportproduct.stockreport_id')->
				leftjoin('stockreportmerchant', 'stockreportmerchant.stockreport_id','=','stockreport.id')->
				where('stockreportmerchant.franchisee_merchant_id',$this->user_data->company_id())->
				where('stockreportproduct.product_id', $product->id)->
				where('stockreport.type', 'transfer')->
				where('stockreport.status', 'confirmed')->
				distinct()->
				get();

		
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
	//	dd($opos_product);
		
		$loyaltyredemption = DB::table('product_pts_redemption')
						->select('product_pts_redemption.systemid as document_no', 
								'product_pts_redemption.id as redemption_id', 'product_pts_redemption.quantity', 
								'product_pts_redemption.created_at as last_update', 'product_pts_redemption.remarks as item_remarks',
								'location.branch as location', 'location.id as locationid')
			->leftjoin('location', 'location.id', '=', 'product_pts_redemption.location_id')
			->where('product_pts_redemption.product_id', $product->id)->distinct()->get();

		
		$item_count = count($opos_product);
		
		foreach ($loyaltyredemption as $key => $value) {
			$opos_product[$item_count] = $value;
			$opos_product[$item_count]->sales_type = "Loyalty";
			$opos_product[$item_count]->item_detail_id = $value->redemption_id;
			$opos_product[$item_count]->stock = false;
			$opos_product[$item_count]->wastage = false;
			$opos_product[$item_count]->refund_type = false;
			$opos_product[$item_count]->voucher = false;
			$opos_product[$item_count]->void = false;
			$opos_product[$item_count]->quantity = 0 - $value->quantity;
			$item_count++;
		}	
	
		
		$merchant_id = Merchant::where('id', $this->user_data->company_id())->pluck('id')->first();
		$location_sales_qty = array();
		// Latest item remarks
		$prev_id = 0;
		$prev_key = 0;
		foreach ($opos_product as $key => $value) {
			
			if ($value->stock) {
				$product_remark = stockreportremarks::orderby('created_at', 'DESC')
					->where('stockreport_id', $value->stockreport_id)
					->where('user_id', Auth::user()->id)->first();
				if ($product_remark) {
					$opos_product[$key]->item_remarks = $product_remark->remarks;
					//return $opos_product[$key]->item_remarks;
				}
			}
			if ($value->wastage) {
				
				$opos_wastage = new opos_wastage();
				$opos_wastage_id = $opos_wastage->where('systemid', $value->document_no)->first();
				$product_remark = wastagereportremarks::orderby('created_at', 'DESC')
					->where('wastage_id', $opos_wastage_id->id)
					->where('user_id', Auth::user()->id)->first();
					//return $opos_wastage_id->id;
					$opos_product[$key]->item_detail_id = $opos_wastage_id->id;
				if ($product_remark) {
					$opos_product[$key]->item_remarks = $product_remark->remarks;
				}
			} else {
				$item_id = $value->item_detail_id;
				$product_remark = opos_itemdetailsremarks::orderby('created_at', 'DESC')
					->where('itemdetails_id', $item_id)
					->where('user_id', Auth::user()->id)->first();
				if ($product_remark) {
					$opos_product[$key]->item_remarks = /*(strlen($product_remark->remarks) > 60 ) ? substr($product_remark->remarks,0,60)."..." : */
						$product_remark->remarks;
				}
				if ($value->refund_type || $value->wastage || $value->voucher ) {
					continue;
				}
				if (isset($value->promo_id)) {
                    $opos_product[$key]->sales_type = "Bundle Sales";
                    $opos_product[$key]->quantity = 0 - $value->quantity;
					if ($value->void == 1) {
                        $opos_product[$key]->sales_type = "Void Sales";
                        $opos_product[$key]->quantity = 0;
                    }

                } else {
                    if ($value->void == 1) {
                        $opos_product[$key]->sales_type = "Void Sales";
                        $opos_product[$key]->quantity = 0;
                    } else {
						if($opos_product[$key]->sales_type != "Loyalty"){
							$opos_product[$key]->sales_type = "Cash Sales";
							$opos_product[$key]->quantity = 0 - $value->quantity;
						}
                    }
                }

			}

		}
		
		// opos_product sort by Lastupdate (db_table.created_at) Desc
		$opos_product = $opos_product->sortBy('last_update', SORT_REGULAR, true);
	//	
		// Product Location Stock data
		$location_data = location::join('merchantlocation', 'merchantlocation.location_id', '=', 'location.id')->
			where('merchant_id', $this->user_data->company_id())->
			whereNotNull('location.branch')->
			get();
	
		$franchise_location = location::select('location.*',"location.id as location_id")->
			join('franchisemerchantloc','franchisemerchantloc.location_id','=','location.id')->
			join('franchisemerchant','franchisemerchant.id','=','franchisemerchantloc.franchisemerchant_id')->
			leftjoin('franchiseproduct','franchiseproduct.franchise_id','=','franchisemerchant.franchise_id')->
			where([
				"franchiseproduct.product_id"	 => $product->id,
				"franchiseproduct.active"		 => 1
			])->
			distinct()->
			get();
	
		$franchise_location->map(function($z) use ($location_data) {
			if (!$location_data->contains('systemid',$z->systemid)) {
				$location_data->prepend($z);
			}

		});

		foreach ($location_data as $key => $value) {
			$final_qty = $this->location_productqty($product->id, $value->location_id);
            $location_data[$key]['quantity'] = $final_qty;
		}
		//dd($opos_product);
		foreach ($opos_product as $key => $value) {
		//	dd($prev_id);
			if($value->document_no == $prev_id && $opos_product[$prev_key]->stock == 1){
				//if($value->sales_type != 'Stock In' && $value->sales_type != 'Stock Out'){
					$opos_product[$prev_key]->quantity = $opos_product[$prev_key]->quantity + $value->quantity;
				//}
				unset($opos_product[$key]);
			} else {
				$prev_key = $key;
				$prev_id = $value->document_no;
			}			
		}
		
		return view('inventory.inventoryqty', compact('product', 'product_data', 'opos_product', 'location_data'));
	}
	
	public function showwastagereport(Request $request)
	{

		/*$id = Auth::user()->id;
		$user_roles = usersrole::where('user_id', $id)->get();
		
		$is_king = \App\Models\Company::where('owner_user_id',
			Auth::user()->id)->first();
		Log::debug('is_king='.json_encode($is_king));

		$company_id = $is_king->id;
		Log::debug('company_id='.$company_id);

		$merchant_id = \App\Models\Merchant::where('company_id',
			$company_id)->value('id');
		/*/
		
		$user_data 	= new UserData();
		$merchant_id 	= $user_data->company_id();
		$report_id	 = $request->doc_id;		
	
		$p_id_fr = DB::table('franchiseproduct')->
			leftjoin('franchisemerchant',
				'franchisemerchant.franchise_id','=','franchiseproduct.franchise_id')->
			where([
				'franchisemerchant.franchisee_merchant_id' => $merchant_id,
				'franchiseproduct.active' => 1
			])->
			whereNull('franchiseproduct.deleted_at')->
			get();

		$p_id_mp = Merchantproduct::where('merchant_id',$merchant_id)->
			pluck('product_id');

		$productIds = array_merge( $p_id_fr->pluck('product_id')->toArray() ,$p_id_mp->toArray());

		$waste = product::
		//	 ->join("product", 'merchantproduct.product_id', '=', 'product.id')
			 join("opos_wastageproduct",'product.id' , '=','opos_wastageproduct.product_id' )
			 ->join('opos_wastage', 'opos_wastage.id', '=', 'opos_wastageproduct.wastage_id')
		 	 ->leftjoin('opos_wastageproductbarcode', 'opos_wastageproductbarcode.wastageproduct_id', '=' ,'opos_wastageproduct.wastage_id')
			 ->where('opos_wastage.systemid', $report_id)	
		 	 ->whereIn('product.id',$productIds)
		 	 ->leftjoin('location', 'location.id', '=', 'opos_wastageproduct.location_id')
			 ->select('product.*',  'opos_wastage.systemid as document_no', 
			 'opos_wastageproduct.wastage_qty as quantity', 'opos_wastageproduct.created_at as last_update',
			 'location.branch as location', 'location.id as locationid','opos_wastage.staff_user_id','opos_wastageproductbarcode.bmatrixbarcodejson as matrix',
			 'opos_wastageproductbarcode.barcode as barcode')
			 ->get();
		
		$waste  = $this->decodeMatrix($waste);
		//	 dd($wastage);
	
		$wastage = product::
			//->join("product", 'merchantproduct.product_id', '=', 'product.id')
			join("opos_receiptproduct", 'product.id', '=', 'opos_receiptproduct.product_id')
			->join("opos_refund", 'opos_receiptproduct.id', '=', 'opos_refund.receiptproduct_id')
			->join("opos_damagerefund", 'opos_refund.id' , '=', 'opos_damagerefund.refund_id')
			->join('locationproduct','product.id','=','locationproduct.product_id')
			->join('location','locationproduct.location_id','=','location.id')
			->leftjoin('opos_receipt', 'opos_receiptproduct.receipt_id', '=', 'opos_receipt.id')
			->where('opos_receipt.systemid', $report_id)
		 	->whereIn('product.id',$productIds)
			->select('product.*','opos_receipt.systemid as document_no',
			 'opos_damagerefund.damage_qty as quantity', 'opos_damagerefund.created_at as last_update',
			 'location.branch as location','location.id as locationid','opos_refund.confirmed_user_id as staff_user_id'	
		 	)
			->groupBy('location')
			->get();
		
		$item_count = count($wastage);
		foreach ($waste as $key => $value) {
			$wastage[$item_count] = $value;
			$wastage[$item_count]->type = 'wastage';
			$item_count++;
		}
		
		$wastage_data = collect();
		$record_1 = $wastage->first();
		$staff = User::where('users.id',$record_1->staff_user_id)->
			join('staff','staff.user_id','=','users.id')->
			first();
		$wastage_data->staff_name = $staff->name;
		$wastage_data->staff_id = $staff->systemid;
		$wastage_data->location = $record_1->location;
		$wastage_data->locationid =  $record_1->locationid;
		$wastage_data->last_update = $wastage->max('last_update');
		$wastage_data->created_at = $wastage->max('last_update');	
		/*	
			->select('product.*','users.name as staff_name', 'staff.systemid as staff_id',
				 'opos_receipt.systemid as document_no', 'opos_damagerefund.damage_qty as quantity',
				 'opos_damagerefund.created_at as last_update','location.branch as location','location.id as locationid')
				 ->first();
		 */	

		return view('inventory.inventorywastagereport',
			compact('wastage', 'wastage_data','report_id'));
	}
	
	public function showstockreport(Request $request)
	{
		
		$id = Auth::user()->id;
		$user_roles = usersrole::where('user_id', $id)->get();
		
		$stock_system_id = $request->doc_id;

/*
		$stockreport = StockReport::
			select('product.*', 'productbarcode.barcode','users.name as staff_name', 
			'staff.systemid as staff_id', 'stockreport.systemid as document_no', 
			'stockreport.id as stockreport_id', 'stockreportproduct.quantity', 
			'stockreport.type as refund_type', 'stockreport.created_at as last_update', 
			'location.branch as location', 'location.id as locationid')
			->join('location', 'location.id', '=', 'stockreport.location_id')
			->leftjoin('stockreportproduct','stockreportproduct.stockreport_id','=','stockreport.id')
			->join('product', 'product.id', '=', 'stockreportproduct.product_id')
			->join('users', 'users.id', '=', 'stockreport.creator_user_id')
			->join('staff', 'staff.user_id', '=', 'stockreport.creator_user_id')
			->leftjoin('productbarcode','product.id', '=', 'productbarcode.product_id')
			->where('stockreport.systemid', $stock_system_id)->groupBy('barcode')->get();
		
		if(sizeof($stockreport) == 0){
			$stockreport = StockReport::
				select('product.*', 'productbarcode.barcode','users.name as staff_name', 
				'staff.systemid as staff_id', 'stockreport.systemid as document_no', 
				'stockreport.id as stockreport_id', 'stockreportproduct.quantity', 
				'stockreport.type as refund_type', 'stockreport.created_at as last_update', 
				'location.branch as location', 'location.id as locationid')
				->leftjoin('location', 'location.id', '=', 'stockreport.location_id')
				->join('stockreportproduct', 'stockreportproduct.stockreport_id', '=', 'stockreport.id')
				->join('product', 'product.id', '=', 'stockreportproduct.product_id')
				->join('users', 'users.id', '=', 'stockreport.creator_user_id')
				->join('staff', 'staff.user_id', '=', 'stockreport.creator_user_id')
				->leftjoin('productbarcode','product.id', '=', 'productbarcode.product_id')
				->where('stockreport.systemid', $stock_system_id)->groupBy('barcode')->get();
		}
/*/
		

		$stockreport = StockReport::
			join('stockreportproduct', 'stockreportproduct.stockreport_id', '=', 'stockreport.id')->
			leftjoin('stockreportproductrack', 'stockreportproductrack.stockreportproduct_id', '=',
				'stockreportproduct.id')->
			leftjoin('rack','rack.id','=','stockreportproductrack.rack_id')->
			join('product', 'product.id', '=', 'stockreportproduct.product_id')->
			where('stockreport.systemid', $stock_system_id)->get();
		
		if (isset($stockreport[0]['bmatrixbarcodejson'])) {
	
			$stockreport->map(function($z){
				$z->matrix = $z->bmatrixbarcodejson;
			});
		
			$stockreport =	$this->decodeMatrix($stockreport->toArray());

			$stockreport =  collect( $stockreport )->
				map(function($z) { 
								return (object) $z;
				})->
				reject(function($z){
				})->all();
		}
		
		$stockreport_data = StockReport::
			select('users.name as staff_name', 'staff.systemid as staff_id', 
			'stockreport.systemid as document_no', 'stockreport.id as stockreport_id', 
			'stockreport.type as refund_type', 'stockreport.created_at as last_update', 
			'location.branch as location', 'location.id as locationid')
			->leftjoin('location', 'location.id', '=', 'stockreport.location_id')
			->join('users', 'users.id', '=', 'stockreport.creator_user_id')
			->join('staff', 'staff.user_id', '=', 'stockreport.creator_user_id')
			->where('stockreport.systemid', $stock_system_id)->first();
		
		$is_king = \App\Models\Company::where('owner_user_id',
			Auth::user()->id)->first();
		
		if ($is_king != null) {
			$is_king = true;
		} else 	{
			$is_king = false;
		}
		$isWarehouse = location::find($stockreport_data->locationid)->warehouse;

		return view('inventory.inventorystockreport',
			compact('user_roles', 'is_king', 'stockreport', 'stockreport_data','isWarehouse'));
	}


	public function showInventoryQtyDamage_opossum($branch) {
		if (empty($branch)) {
			abort(404);
		}

		return	$this->showInventoryQtyDamage($branch);
	}


	public function showInventoryQtyDamage($oposum = false)
	{
		$user_data  = new UserData();
		// This will fail on a staff account!!!
		// Replay: Use UserData()
		// **** Trying to get property 'id' of non-object ****

		$merchant_id = $user_data->company_id();
		
		if ($oposum != false) {
			$location__ = DB::table("opos_locationterminal")->
				join('location','location.id','=','opos_locationterminal.location_id')->
				where("terminal_id",$oposum)->
				first();
			$location_id = $location__->location_id;
		}
		
		$p_id_fr = DB::table('franchiseproduct')->
			leftjoin('franchisemerchant',
				'franchisemerchant.franchise_id','=','franchiseproduct.franchise_id')->
			where([
				'franchisemerchant.franchisee_merchant_id' => $merchant_id,
				'franchiseproduct.active' => 1
			])->
			whereNull('franchiseproduct.deleted_at')->
			get();

		$p_id_mp = Merchantproduct::where('merchant_id',$merchant_id)->
			pluck('product_id');

		$productIds = array_merge( $p_id_fr->pluck('product_id')->toArray() ,$p_id_mp->toArray());

		

		$damage = product::
				//->join("product", 'merchantproduct.product_id', '=', 'product.id')
				 join("opos_receiptproduct", 'product.id', '=', 'opos_receiptproduct.product_id')
				 ->join("opos_refund", 'opos_receiptproduct.id', '=', 'opos_refund.receiptproduct_id')
				 ->join("opos_damagerefund", 'opos_refund.id' , '=', 'opos_damagerefund.refund_id')
				 ->leftjoin('opos_receipt', 'opos_receiptproduct.receipt_id', '=', 'opos_receipt.id')
				 ->select('opos_refund.receiptproduct_id', 'product.systemid as productsys_id', 
				 	'product.id as product_id', 'product.thumbnail_1', 
					'opos_receiptproduct.name', 'opos_receipt.systemid as document_no', 
					'opos_receiptproduct.receipt_id', 'opos_damagerefund.damage_qty as quantity', 
					'opos_refund.refund_type', 'opos_damagerefund.created_at as last_update', 'opos_receipt.terminal_id')
				->leftjoin('staff','staff.user_id','=','opos_refund.confirmed_user_id')
				->where('staff.company_id', $user_data->company_id())
				->whereIn('product_id',$productIds)
				->get();
		
		if ($oposum != false) {
			$damage = $damage->where('terminal_id',$oposum);
		}

		$wastage = product::
			// ->join("product", 'merchantproduct.product_id', '=', 'product.id')
			 join("opos_wastageproduct",'product.id' , '=','opos_wastageproduct.product_id' )
			 ->join('opos_wastage', 'opos_wastage.id', '=', 'opos_wastageproduct.wastage_id')
			 ->select('product.systemid as productsys_id', 'product.id as product_id', 
			 'product.thumbnail_1', 'product.name', 'opos_wastage.systemid as document_no', 
			 DB::raw("SUM(opos_wastageproduct.wastage_qty) as quantity"),'opos_wastageproduct.location_id',
			 'opos_wastageproduct.created_at as last_update')->
			 leftjoin('staff','staff.user_id','=','opos_wastage.staff_user_id')->
			 where('staff.company_id', $user_data->company_id())
			->whereIn('product_id',$productIds)
			->groupBy('document_no')
			->get();
		
		if ($oposum != false) {
			$wastage = $wastage->where('location_id',$location_id);
			$oposum = $location__;
		}

		$item_count = count($damage);
		foreach ($wastage as $key => $value) {
			$damage[$item_count] = $value;
			$damage[$item_count]->type = 'wastage';
			$item_count++;
		}
		
		$damage = $damage->sortBy('last_update', SORT_REGULAR, true);
		
		return view('inventory.inventoryqtydamage',
			compact('damage','oposum'));
	}
	
	public function showInventoryQtyWastageForm_oposum($branch) {
		if (empty($branch)) {
			abort(404);
		}

		return $this->showInventoryQtyWastageForm($branch);

	}	
	
	public function showInventoryQtyWastageForm($location_id = false)
	{
		
		$this->user_data = new UserData();
	
		$ids = merchantproduct::where('merchant_id',
			$this->user_data->company_id())->pluck('product_id');
		
		$opos_product = product::whereIn('ptype', array('inventory', 'rawmaterial'))->whereNotNull('name')->get();

		$staff_data = Staff::join('users', 'users.id', '=', 'staff.user_id')->where('staff.user_id', Auth::user()->id)->first();

		$is_king = \App\Models\Company::where('owner_user_id',
			Auth::user()->id)->first();
		
		$modal = "newLocationDialog";
		$ids = merchantlocation::where('merchant_id', $this->user_data->company_id())->pluck('location_id');
		$location = location::where([['branch', '!=', 'null']])->whereIn('id', $ids)->latest()->get();
	
		$franchise_location = location::select('location.*')->
			join('franchisemerchantloc','franchisemerchantloc.location_id',
			'=','location.id')->
			join('franchisemerchant','franchisemerchant.id','=','franchisemerchantloc.franchisemerchant_id')->
			leftjoin('franchiseproduct','franchiseproduct.franchise_id','=','franchisemerchant.franchise_id')->
			where('franchisemerchant.franchisee_merchant_id',$this->user_data->company_id())->
			distinct()->
			get();
	
		$franchise_location->map(function($z) use ($location) {
			if (!$location->contains('systemid',$z->systemid)) {
				$location->prepend($z);
			}

		});

		if ($location_id != false) {
			$location = $location->where('id',$location_id);
			$location_id = $location->first(); 
		}

		foreach ($opos_product as $key => $value) {
			$final_qty = $this->check_quantity($value->id);
			$opos_product[$key]['quantity'] = $final_qty;
		}
		
		return view('inventory.inventoryqtywastageform',
			compact('opos_product', 'staff_data', 'location','location_id'));
	}
	
	public function showWastageForm(Request $request)
	{
		$this->user_data = new UserData();
		$location_id = $request->get('id');
	/*	$opos_product = array();
		$final_product = array();
		
		if ($location_id) {
			$opos_product = product::whereIn('ptype', array('inventory', 'rawmaterial'))->whereNotNull('name')->get();
			foreach ($opos_product as $key => $value) {
				
				$final_qty = $this->location_productqty($value->id, $location_id);
				if ($final_qty <= 0) {
					continue;
				}
				$final_product[$key] = $value;
				$final_product[$key]->quantity = $final_qty;
				$final_product[$key]->product_id = $value->id;
			}
			
			$opos_product = $final_product;
		}
		/*/
		$location_id 	= request()->get('id');
		$location 	= location::where('id', $location_id)->first();
		$product_data 	= $this->get_product_barcodes_out();
		
		if (!empty ($location)) {
			if ($location->warehouse == 1) {
				$product_data = $this->getAvailableRackData($product_data);
			} else {
				$product_data = $this->getAvailableNONRackData($product_data);
			}
			
			$product_data  = $this->decodeMatrix($product_data);
			$product_data =  collect( $product_data )->
				map(function($z) { 
					return (object) $z;
				})->
				reject(function($z){})->all();
		} else {
				$product_data = [];
		}
		
		return Datatables::of($product_data)->
		addIndexColumn()->
		addColumn('inven_pro_id', function ($memberList) {
			return $memberList->barcode;
		})->
		addColumn('inven_pro_name', function ($memberList) {
			return '<img src="' . asset('images/product/' . $memberList->org_product_id . '/thumb/' . 
				$memberList->thumbnail_1) . '" style="height:40px;width:40px;
			object-fit:contain;margin-right:8px;">' . $memberList->product_name;
		})->
		addColumn('inven_pro_existing_qty', function ($memberList) {
			return $memberList->quantity;
		})->
		addColumn('color', function ($memberList) {
			$color = $memberList->color ?? '-';	
			$html = <<< EOD
						<div style="padding:10px 20px; background:$color"></div>
			EOD;
			return $color != '-' ? $html : '-';
		})->
		addColumn('bmatrix_display',function ($memberList) {
			return $memberList->matrix_string ?? '-';
		})->
		addColumn('rack', function($memberList) {
			return $memberList->rack_no ?? '-';
		})->
		addColumn('inven_pro_qty', function ($memberList) {
			return '<div class="value-button increase" id="increase_' . $memberList->qty_id . 
				'" onclick="increaseValue(' . $memberList->qty_id . ')" 
				value="Increase Value" style="margin-top:-25px;"><ion-icon class="ion-ios-plus-outline" 
				style="font-size: 24px;margin-right:5px;"></ion-icon>
               			 </div><input type="number" id="number_' . $memberList->qty_id . '"  class="number product_qty"
				 value="0"  min="0" max="' . $memberList->quantity . '" 
				required onblur="check_max(' . $memberList->qty_id . ')"><div class="value-button decrease" 
				id="decrease_' . $memberList->qty_id . '" onclick="decreaseValue(' . $memberList->qty_id. ')" 
				value="Decrease Value" style="margin-top:-25px;"><ion-icon class="ion-ios-minus-outline" 
				style="font-size: 24px;margin-left:5px"></ion-icon></div>';
		})->
		escapeColumns([])->
		make(true);
	}
	
	public function updatewastage(Request $request)
	{
		try {
			$id = Auth::user()->id;
			
			$table_data = $request->get('table_data');
			$total_qty = 0;
			$wastage_system = DB::select("select nextval(wastage_seq) as index_damage");
			$wastage_system_id = $wastage_system[0]->index_damage;
			$wastage_system_id = sprintf("%010s", $wastage_system_id);
			$wastage_system_id = '112' . $wastage_system_id;
			
			foreach ($table_data as $key => $value) {
				if ($value['qty'] <= 0) {
					continue;
				}
				$opos_wastage = new opos_wastage();
				$opos_wastage->systemid = $wastage_system_id;
				$opos_wastage->staff_user_id = $id;
				$opos_wastage->save();
				
				
				$stock = new opos_wastageproduct();
				$stock->wastage_id = $opos_wastage->id;
				$stock->product_id = $value['product_id'];
				$stock->location_id = $value['location_id'];
				$stock->wastage_qty = $value['qty'];
				$stock->save();

				DB::table('opos_wastageproductbarcode')->insert([
					"wastageproduct_id"	=> $stock->id,
					"barcode"		=> $value['barcode'],
					"bmatrixbarcodejson"	=> $value['matrix'] ?? null,
					"barcode_quantity"	=> $value['qty'],
					"created_at"		=> date('Y-m-d H:i:s'),
					"updated_at"		=> date('Y-m-d H:i:s')
				]);

				$total_qty += $value['qty'];
			}
			if ($total_qty > 0) {
				$msg = "Wastage recorded succesfully";
			} else {
				$msg = "Please select product.";
			}
			$data = view('layouts.dialog', compact('msg'));
			
		} catch (\Exception $e) {
			$msg = "Error occured while saving stock";
			
			Log::error(
				"Error @ " . $e->getLine() . " file " . $e->getFile() .
				":" . $e->getMessage()
			);
			
			$data = view('layouts.dialog', compact('msg'));
		}
		return $data;
	}
	
	
	public function showInventoryQtyLocation()
	{
		$id = Auth::user()->id;
		$user_roles = usersrole::where('user_id', $id)->get();
		
		$is_king = \App\Models\Company::where('owner_user_id',
			Auth::user()->id)->first();
		
		if ($is_king != null) {
			$is_king = true;
		} else {
			$is_king = false;
		}
		return view('inventory.inventoryqtypdtlocation',
			compact('user_roles', 'is_king'));
	}
	
	
	public function barcodeIndex(Request $request)
	{
		$product = product::where('systemid', $request->system_id)->first();

		$barcodes = SettingBarcodeMatrix::where('category_id',
			$product->prdcategory_id)->
		where('subcategory_id', $product->prdsubcategory_id)->
		get();
		
		return Datatables::of($barcodes)->
		addColumn('bar', function ($barcodes) use ($request) {
			return '<img src="data:image/png;base64,' .
				DNS1D::getBarcodePNG(trim($request->system_id . '-' .
					$barcodes->barcode_numbers), "C128") .
				'" alt="barcode" width="200px" height="70px " ">' .
				'<br> <center>' . $request->system_id . '-' .
				$barcodes->barcode_numbers . '</center>';
		})->
		
		addColumn('color', function ($barcodes) use ($request) {
			return $barcodes->color_code;
		})->
		
		addColumn('sizes', function ($barcodes) use ($request) {
			if ($barcodes->color_id) {
				switch ($barcodes->color_id) {
					case 1:
						return $barcodes->size;
						break;
					case 2:
						$barcodes->size = "S";
						break;
					case 3:
						$barcodes->size = "M";
						break;
					case 4:
						$barcodes->size = "L";
						break;
					case 5:
						$barcodes->size = "XL";
						break;
					case 6:
						$barcodes->size = "XXL";
						break;
				}
			} else {
				return 'Without Color  ' . $barcodes->size;
			}
		})
			->rawColumns(['color'])
			->rawColumns(['sizes'])
			->rawColumns(['bar'])
			->make(true);
	}
	
	
	public function showInventoryBarcode($id)
	{
		$UserData = new UserData();
		$system_id = $id;
		$product = product::where('systemid', $system_id)->first();
		$product_id = $product->id;
		$product_qty = $this->check_quantity($product->id);
		$is_matrix_disable = !empty(
			DB::table('prdbmatrixbarcodegen')->
				where([
					'product_id'		=> $product_id,
					'bmbarcode_generated' 	=> 1
				])->
				whereNull('deleted_at')->
				first()
			);
			
		
		$is_matrix_disable_avalible  = 	empty(
				DB::table('bmatrix')->
					where([
						'bmatrix.subcategory_id'	=>	$product->prdsubcategory_id,
						"bmatrix.merchant_id"		=>	$UserData->company_id()
					])->
					join('bmatrixbarcode', 'bmatrixbarcode.bmatrix_id', '=', 'bmatrix.id')->
					first()
				);
		if (!$is_matrix_disable && $is_matrix_disable_avalible) {
			$is_matrix_disable = true;
		}	

		$barcode_sku = productbarcode::where('product_id', $product->id)->first();
		
		$barcode = DNS1D::getBarcodePNG(trim($system_id), "C128");
		
		$id = Auth::user()->id;
		$user_roles = usersrole::where('user_id', $id)->get();
		
		$is_king = \App\Models\Company::where('owner_user_id',
			Auth::user()->id)->first();
		
		if ($is_king != null) {
			$is_king = true;
		} else {
			$is_king = false;
		}
		
		$showbuttons = true;
		
		return view('inventory.inventorybarcode',
			compact('user_roles', 'is_king', 'barcode', 'product_id',
				'system_id', 'product', 'product_qty',
				'barcode_sku', 'showbuttons',
				'is_matrix_disable' 
			));
	}
	
	
	public function showBarcodeTable(Request $request)
	{
		$product_id = $request->id;
		$product = product::where('id', $product_id)->first();
		$product_qty = $this->check_quantity($product->id);
	//	$barcodematrix = SettingBarcodeMatrix::
	//		where('category_id', $product->prdcategory_id)->first();
		
		$this->user_data = new UserData();
		$merchant_id = $this->user_data->company_id();

       		 $merchant_product = DB::table('merchantproduct')->
			select('id')->
		    where('product_id' ,'=',$product_id)->
		    first();
		$barcode_sku = productbarcode::where('product_id', $product->id)->
			where('merchantproduct_id',$merchant_product->id)->
			first();
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
		
		$barcodes_data = array();
		$count = 0;
		
		$qr = DNS2D::getBarcodePNG($product->systemid, "QRCODE");
		$sku = 'SKU';
		if (!empty($product->sku))
			$sku = $product->sku;
		$is_default_exist = DB::table('productbarcode')->
			where([
				'barcode' => $product->systemid,
				"merchantproduct_id"	=> $merchant_product->id
			])->
			whereNull('deleted_at')->first();
		
		if (empty($is_default_exist)) {
			DB::table('productbarcode')->insert([
				"product_id" 		=>	$product->id,
				"merchantproduct_id"	=>	$merchant_product->id,
				"barcode"		=>	$product->systemid,
				"name"			=> 	$product->name
			]);
		}

		//sorting
		//here
		if ($request->has('start_date')) {
		    
		    $barcodes_superSet = productbarcode::select('productbarcode.*')-> 
				where('product_id', $product->id)->
				where('merchantproduct_id',$merchant_product->id)->	
				where([['barcode', 'LIKE', "%" . $search . "%"]])->
				orderBy('startdate', 'asc')->
				get();

		    $barcodes_superSet->map(function($z) {
			    $z->qty = $this->barcode_qty_v2($z->id, $z->barcode, $is_matrix = false);
		    });

		    $barcodes_1 = $barcodes_superSet->filter(function($z) {
			    return $z->qty > 0 && $z->startdate != null;
		    });

		    $barcodes_2 = $barcodes_superSet->filter(function($z) {
			    return $z->qty == 0 && $z->startdate != null;
		    });

		    $barcodes_3 = $barcodes_superSet->filter(function($z) {
			    return $z->startdate == null;
		    });
		
		    Log::info('+++++++++++++++++++');
		    Log::info([
		    	"Data"				=> "Unsorted",
				"Super SET"			=> json_encode($barcodes_superSet),
				"SET 1 (qty  > 0 & startdate)"	=> json_encode($barcodes_1),
				"SET 2 (qty == 0 & startdate)"	=> json_encode($barcodes_2),
				"SET 3 (not startdate)"		=> json_encode($barcodes_3),
		    ]);

		    $barcodes_1 = $barcodes_1->sortBy('startdate');
		    $barcodes_2 = $barcodes_2->sortBy('startdate');
		    $barcodes_3 = $barcodes_3->sortBy('created_at');
		    $barcodes = $barcodes_1->merge($barcodes_2->merge($barcodes_3));
		    
		    Log::info([
		    	"Data"				=> "Unsorted",
				"Super SET (Unsorted)"		=> json_encode($barcodes_superSet),
				"SET 1 (qty > 0 & startdate)"	=> json_encode($barcodes_1),
				"SET 2 (qty == 0 & startdate)"	=> json_encode($barcodes_2),
				"SET 3 (not startdate)"		=> json_encode($barcodes_3),
				"Super SET (Sorted)"		=> json_encode($barcodes),
		    ]);

		} else if($request->has('expiry_date')) {    
		    $barcodes_superSet = productbarcode::select('productbarcode.*')-> 
			where('product_id', $product->id)->
			where('merchantproduct_id',$merchant_product->id)->	
			where([['barcode', 'LIKE', "%" . $search . "%"]])->
			orderBy('expirydate', 'asc')->
			get();

		    $barcodes_superSet->map(function($z) {
			    $z->qty = $this->barcode_qty_v2($z->id, $z->barcode, $is_matrix = false);
		    });
		
		    $barcodes_1 = $barcodes_superSet->filter(function($z) {
			    return $z->qty > 0 && $z->expirydate != null;
		    });

		    $barcodes_2 = $barcodes_superSet->filter(function($z) {
			    return $z->qty == 0 && $z->expirydate != null;
		    });

		    $barcodes_3 = $barcodes_superSet->filter(function($z) {
			    return $z->expirydate == null;
		    });
		    Log::info("Hello"); 
		    Log::info([
		    	"Data"				=> "Unsorted (ExpiryDate)",
				"Super SET"			=> json_encode($barcodes_superSet),
				"SET 1 (qty > 0 & expirydate)"	=> json_encode($barcodes_1),
				"SET 2 (qty == 0 & expirydate)"	=> json_encode($barcodes_2),
				"SET 3 (not expirydate)"	=> json_encode($barcodes_3),
		    ]);

		    $barcodes_1 = $barcodes_1->sortBy('expirydate');
		    $barcodes_2 = $barcodes_2->sortBy('expirydate');
		    $barcodes_3 = $barcodes_3->sortBy('created_at');
		    $barcodes = $barcodes_1->merge($barcodes_2->merge($barcodes_3));
	    
		    Log::info([
		    	"Data"				=> "Sorted (ExpiryDate)",
				"Super SET (unsorted)"		=> json_encode($barcodes_superSet),
				"SET 1 (qty > 0 & expirydate)"	=> json_encode($barcodes_1),
				"SET 2 (qty == 0 & expirydate)"	=> json_encode($barcodes_2),
				"SET 3 (not expirydate)"	=> json_encode($barcodes_3),
				"Super SET (Sorted)"		=> json_encode($barcodes),
			]);
		} else {

		$barcodes = productbarcode::where('product_id', $product->id)->
			where('merchantproduct_id',$merchant_product->id)->
			where([['barcode', 'LIKE', "%" . $search . "%"]])->
			orderBy('created_at', 'asc')->
			get();
		}

		foreach ($barcodes as $barcode) {
			$sku = 'SKU';
			$name = 'Barcode Name';
			$notes = $barcode->notes;
			if ($barcode->startdate != '0000-00-00' &&
				$barcode->startdate != '1970-01-01' &&
				!is_null($barcode->startdate))

				$notes .= "Start Date: <b>" .
					date("dMy", strtotime($barcode->startdate)) .
					"</b><br>";

			if ($barcode->expirydate != '0000-00-00' &&
				$barcode->expirydate != '1970-01-01' &&
				!is_null($barcode->expirydate))

				$notes .= "Expiry Date: <b>" .
					date("dMy", strtotime($barcode->expirydate)) . "</b>";

			if (!empty($barcode->sku)) $sku = $barcode->sku;

			if (!empty($barcode->name)) $name = $barcode->name;

			$count++;
			
			$code = DNS1D::getBarcodePNG(trim($barcode->barcode), "C128");
			
			$qr = DNS2D::getBarcodePNG($barcode->barcode, "QRCODE");
			
			$final_qty = $this->barcode_qty_v2($barcode->id, $barcode->barcode, $is_matrix = false);
				
				//productbarcodelocation::
				//where('productbarcode_id', $barcode->id)->
				//sum('quantity');

			//if (isset($barcodes_data[0]))
			//	$barcodes_data[0]['qty'] += $final_qty;

			$check_barcode = productbarcodelocation::
				where('productbarcode_id', $barcode->id)->get();

			if (count($check_barcode) > 0) {
				$actions = '<div><img src="/images/redcrab_50x50.png"
					style="width:25px;height:25px;cursor:not-allowed;
					filter:grayscale(100%) brightness(200%)"/>
					</div>';

			} else {
				$actions = '<input type="hidden"  value="'.$barcode->id.
					'"/><div class="remove-barcode mb-0 align-items-center"
					style="">
					<img class="" src="/images/redcrab_50x50.png"
					style="width:25px;height:25px;cursor:pointer"/>
					</div>';
			}
			
			array_push(
				$barcodes_data, array(
					"no" => $count,
					"barcode" => "<p id='barcodename_" . $barcode->id. "' style='margin-bottom: 0px; margin-top : 10px;'><a href='#!' data-is_main='0' class='name' data-barcode_id='" . $barcode->id . "'>" .  $name . "</a></p>"."<img src='data:image/png;base64," . $code . "' height='60px' width='250px' style='margin-top:0'/><br>" . $barcode->barcode,
					/*
					"qr_code" => "<p id='barcodesku_" . $barcode->id . "' style='margin-bottom: 0px;'><a href='#' data-is_main='0' class='sku' data-barcode_id='" . $barcode->id . "'>" . $sku . "</a></p><img src='data:image/png;base64," . $qr . "' height='70px' width='70px' />",
					*/
					"qr_code" => "<img src='data:image/png;base64," . $qr . "' height='70px' width='70px' />",
					"color" => "",
					"matrix" => "",
					"notes" => $notes,
					"qty" => $final_qty,
					"options" => "<a href='#' class='btn btn-success btn-log bg-web sellerbutton'style='padding-top: 25px;margin-bottom:0'>Print</a>",
					"actions" => ($barcode->barcode == $product->systemid ? '':$actions)
				)
			);
		}

		//#################################################################
		//			Latest code starts from here
		//			@abrar_ajaz_wani
		//################################################################
		//is actulay coming from productbmatrixbarcode
		$bmatrixbarcode = DB::table('productbmatrixbarcode')->
			where('product_id', $product->id)->
			whereNull('deleted_at')->
			orderBy('id', 'desc')->
			get();

		foreach($bmatrixbarcode as $barcode) {
			Log::debug('BEFORE live_barcode='.$barcode->bmatrixbarcodejson);	

			$live_barcode = json_decode($barcode->bmatrixbarcodejson,true);

			Log::debug('AFTER  live_barcode='.$barcode->bmatrixbarcodejson);	

			//$back_toString = $barcode->bmatrixbarcodejsondd;
			
			$code = DNS1D::getBarcodePNG($barcode->bmatrixbarcode, "C128");
			$qr = DNS2D::getBarcodePNG($barcode->bmatrixbarcode, "QRCODE");

			
			$append['no']  =  count($barcodes_data) + 1;
			
			$append['barcode'] = <<< EOD
				$product->name
				<br/>
				<img  src="data:image/png;base64,$code" height="60px" width="250px" style="margin-top:0" / >
				<br/>
				$barcode->bmatrixbarcode
EOD;
			
			$append['qr_code'] = <<< EOD
				<br/>
				<img src='data:image/png;base64,$qr' height='70px' width='70px' />
EOD;
			
			$append['color'] = '';
			
			$append['notes'] = '';
			
			$append['qty'] = $this->barcode_qty_v2($barcode->id, $barcode->bmatrixbarcode, $is_matrix = true);
			       //	DB::table('productbmatrixbarcodelocation')->
				//	where('productbmatrixbarcode_id',$barcode->id)->
				//	whereNull('deleted_at')->get()->sum('quantity');

			$append['options'] = "<a href='#' class='btn btn-success btn-log bg-web sellerbutton'style='padding-top: 25px;margin-bottom:0'>Print</a>";
			
			if ($append['qty'] > 0) {
				$append['actions'] = '<div><img src="/images/redcrab_50x50.png"
					style="width:25px;height:25px;cursor:not-allowed;
					filter:grayscale(100%) brightness(200%)"/>
					</div>';

			} else {

				$append['actions'] = <<< EOD
					<div class="mb-0 align-items-center"
					style="">
							<img class="" src="/images/redcrab_50x50.png"
							onclick="delete_matrix_barcode($barcode->id)"
							style="width:25px;height:25px;cursor:pointer"/>
					</div>

EOD;
			}


			$string = '';

			if (!empty($live_barcode)) {

				foreach($live_barcode as $val) {
					foreach($val as $k => $v) {
						if ($k == 'bmatrix_id' || $k == 'systemid') {
							continue;
						}

						if ($k == 'color_id') {
							if ($v != 0) {
								$append['color'] = $v = DB::table('color')->
									find(DB::table('bmatrixcolor')->
									find($v)->color_id)->hex_code;

								$k = "Colour";
								$string 	=	ucfirst($k).":<b>$v</b>" .
								   	($string == '' ? '': '<b>, </b>').$string ;		
								$commas = true;
								continue;
							} else {
								continue;
							}
							
						} else {
							$v = DB::table('bmatrixattribitem')->
								where('id', $v)->
								first()->name;

							$k = DB::table('bmatrixattrib')->
								where('id', $k)->
								first()->name;
						}
						
						$string .=	($string == '' ? '': '<b>, </b>') .
							ucfirst($k).":<b>$v</b>";
						$commas = true;
					}
				}
			}

			$append['matrix'] = $string;
			array_push($barcodes_data, $append);
		}

		$barcodes_data_sliced = array_slice($barcodes_data,$start , $limit);	
		
		$totalRecords = count($barcodes_data);
		$totalFiltered = count($barcodes_data);
		echo json_encode(array(
			"draw" => intval($_REQUEST['draw']),
			"recordsTotal" => $totalRecords,
			"recordsFiltered" => $totalFiltered,
			"data" => $barcodes_data_sliced
		));
	}
	
	
	function deleteBarcode(Request $request)
	{
		$barcode_id = $request->barcode_id;
		productbarcode::where('id', $barcode_id)->delete();
		$msg = "Barcode deleted successfully";
		$html = view('layouts.dialog', compact('msg'))->render();
		return $html;
    }

	function update_barcode_oceania($product_id) {
		\Log::info("******** update_barcode_oceania() *******");
		$franchiseproduct_location	= DB::table('franchiseproduct')->
			join('franchisemerchant','franchisemerchant.franchise_id','franchiseproduct.franchise_id')->
			join('franchisemerchantloc','franchisemerchantloc.franchisemerchant_id','franchisemerchant.id')->
			where('franchiseproduct.product_id', $product_id)->
			select('franchisemerchantloc.*')->
			get()->pluck('location_id');

		$location_F_product_ids = $product_id;

		$productbmatrixbarcode = DB::table('productbmatrixbarcode')->
			join('product','product.id','productbmatrixbarcode.product_id')->
			where('product.id',$location_F_product_ids)->
			select("productbmatrixbarcode.*", 'product.systemid')->
			get();

		$productbarcode = DB::table('productbarcode')->
			join('product','product.id','productbarcode.product_id')->
			where('product.id',$location_F_product_ids)->
			select("productbarcode.*", 'product.systemid')->
			get();
		
		$prdbmatrixbarcodegen = DB::table('prdbmatrixbarcodegen')->
			join('product','product.id','prdbmatrixbarcodegen.product_id')->
			where('product.id',$location_F_product_ids)->
			select("prdbmatrixbarcodegen.*", 'product.systemid')->
			get();

		$new_request = [
			'productbmatrixbarcode' => $productbmatrixbarcode,
			'productbarcode' => $productbarcode,
			'prdbmatrixbarcodegen' => $prdbmatrixbarcodegen
		];

		$location_data = DB::table('locationipaddr')->
			whereIn('locationipaddr.location_id', $franchiseproduct_location)->
			get();

		foreach ($location_data as $l) {
			if (!empty($l->ipaddr))
				app('App\Http\Controllers\APIFcController')->init_send_data_via_api($l->ipaddr,$new_request);
		}

	}

    function saveFifo(Request $request){
        $system_id = trim($request->system_id);
        $product_id = trim($request->product_id);
        $barcode = trim($request->barcode);
        $start = trim($request->start);
        $expiry = trim($request->expiry);
        

        $this->user_data = new UserData();
        $merchant_id = $this->user_data->company_id();

        $merchant_product = DB::table('merchantproduct')->
            select('id')->
            where('merchant_id','=',$merchant_id)->
            where('product_id' ,'=',$product_id)->
            first();

     //   $barcode = $system_id." ".$day."".$month."".$year;
		$check = productbarcode::where([
			'product_id'			=>	$product_id,
			'barcode'				=>	$barcode,
			'merchantproduct_id'	=>	$merchant_product->id
		])
        ->get();
	    if(count($check) > 0 ){
			$msg = "Duplicate barcode not allowed.";
	        $html = view('layouts.dialog', compact('msg'))->render();
        } else {
			$product_barcode = new productbarcode();
			$product_barcode->merchantproduct_id = $merchant_product->id;
	        $product_barcode->product_id = $product_id;
	        $product_barcode->barcode = $barcode;
			if($start != ''){
				$start = new Carbon($start);
				$startdate = $start->isoFormat('YYYY-MM-DD');
				$product_barcode->startdate = $startdate;
			//	dd($start);
			}

			if($expiry != ''){
				$expiry = new Carbon($expiry);
				$expirydate = $expiry->isoFormat('YYYY-MM-DD');
				$product_barcode->expirydate = $expirydate;
			//	dd($start);
			}	       
	        $product_barcode->save();
	        $msg = "Saved FIFO barcode successfully";
	        $html = view('layouts.dialog', compact('msg'))->render();
        }
		$this->update_barcode_oceania($product_id);
        return $html;		
	}
	
    function saveExpiry(Request $request){
        $system_id = trim($request->system_id);
        $product_id = trim($request->product_id);
        $month = trim($request->month);
        $month += 1;
        if($month < 10)
            $month = '0'.$month;
        $day = trim($request->day);
        if($day < 10)
            $day = '0'.$day;
        $year = trim($request->year);
        $year = substr($year,2);

        $expiry_date = $year."-".$month."-".$day;

        $this->user_data = new UserData();
        $merchant_id = $this->user_data->company_id();

        $merchant_product = DB::table('merchantproduct')->
            select('id')->
            where('merchant_id','=',$merchant_id)->
            where('product_id' ,'=',$product_id)->
            first();

        $barcode = $system_id." ".$day."".$month."".$year;
        $check = productbarcode::where('merchantproduct_id',$merchant_product->id)
        ->where('product_id',$product_id)
        ->where('barcode',$barcode)
        ->get();
	    if(count($check) > 0 ){
			$msg = "Duplicate barcode not allowed.";
	        $html = view('layouts.dialog', compact('msg'))->render();
        } else {
	        $product_barcode = new productbarcode();
	        $product_barcode->merchantproduct_id = $merchant_product->id;
	        $product_barcode->product_id = $product_id;
	        $product_barcode->barcode = $barcode;
	        $product_barcode->expirydate = $expiry_date;
	        $product_barcode->save();
	        $msg = "Saved expiry date successfully";
	        $html = view('layouts.dialog', compact('msg'))->render();
        }

		$this->update_barcode_oceania($product_id);
        return $html;
    }

   
	/**
	 * Create Barcode from user input range
	 *
	 * @param Request $request
	 * @return \Illuminate\Http\JsonResponse
	 * @throws \Exception
	 */
	public function createBarcodeFromInputRange(Request $request)
	{
		$this->barcodeGeneratorValidator($request);
		
		$barcode_from 	= (int) $request->get('barcode_from');
		$barcode_to 	= (int) $request->get('barcode_to');
		$product_id 	= $request->get('product_id');
		$barcode_notes 	= $request->get('barcode_notes');
		$merchant_id 	= (new UserData())->company_id();

        $merchant_product = DB::table('merchantproduct')->
            select('id')->
            where('merchant_id','=',$merchant_id)->
            where('product_id' ,'=',$product_id)->
            first();

		if ($barcodes = $this->checkIfBarcodesExistWithRange($product_id, $merchant_product->id, $barcode_from, $barcode_to)) {
			$unique_barcodes = array_unique($barcodes);
			sort($unique_barcodes);
			if(count($unique_barcodes) > 10) {
				return response()->json([
					'status' 	=> 'success',
					'message' 	=> 'System detected clashing barcodes already in existence: <div class="text-left"><br/>'
						.implode('<br/>', array_slice($unique_barcodes, 0, 10)).'<br>Another '.(count($unique_barcodes)-10).' barcodes existed.</div>',
				]);
			} else {
				return response()->json([
					'status' 	=> 'success',
					'message' 	=> 'System detected clashing barcodes already in existence: <div class="text-left"><br/>'
						.implode('<br/>', array_slice($unique_barcodes, 0, 10)).'</div>',
				]);
			}
		}
		
		$this->createMultipleBarcodesWithRanges($barcode_from, $barcode_to, [
			'barcode_type' 			=> 'C128',
			'product_id' 			=> $product_id,
			'merchantproduct_id' 	=> $merchant_product->id,
			'notes' 	=> $barcode_notes
		]);
		
		$this->update_barcode_oceania($product_id);
		return response()->json([
			'status' 	=> 'success',
			'message' 	=> 'Barcode generated successfully',
		]);
	}
	
	
	/**
	 * Create Multiple Barcodes
	 *
	 * @param $barcode_from
	 * @param $barcode_to
	 * @param $otherAttributes array
	 */
	public function createMultipleBarcodesWithRanges($barcode_from, $barcode_to, $otherAttributes)
	{
		$now = Carbon::now()->toDateTimeString();
		$build_barcode_array = [];
		
		for ($i = $barcode_from; $i <= $barcode_to; $i++) {
			$build_barcode_array[] = array_merge(
				['barcode' => $i, 'created_at' => $now, 'updated_at' => $now],
				$otherAttributes
			);
		}
		
		productbarcode::insert($build_barcode_array);
	}
	
	/**
	 * Generate Barcode from ranges
	 *
	 * @param $product_id
	 * @param $merchant_id
	 * @param $barcode_from
	 * @param $barcode_to
	 * @return boolean
	 */
	private function checkIfBarcodesExistWithRange($product_id, $merchant_id, $barcode_from, $barcode_to)
	{
		$barcodes = productbarcode::where(DB::raw('CAST(barcode AS UNSIGNED)'), '>=', $barcode_from)
			->where(DB::raw('CAST(barcode AS UNSIGNED)'), '<=', $barcode_to)
			->where('product_id', $product_id)
			->where('merchantproduct_id', $merchant_id)
			->pluck('barcode')
			->toArray();
		
		return (count($barcodes) > 0) ? $barcodes : false;
	}
	
	/**
	 * Barcode Generator Validator
	 *
	 * @param $request
	 */
	private function barcodeGeneratorValidator($request)
	{
		$request->validate([
			'barcode_from' => 'bail|required|integer|min:1',
			'barcode_to' => ['required', 'integer', 'gt:barcode_from',
				function ($attribute, $value, $fail) use ($request) {
					if (($value - $request->get('barcode_from')) > 10000) {
						$fail('The range between barcode must not exceed 10,000.');
					}
				},
			],
		], [
			'barcode_from.required' => 'Barcode minimum range is required',
			'barcode_from.integer' => 'Barcode minimum range must be a number',
			'barcode_from.min' => 'Barcode minimum range must be a positive number greater than 0',
			'barcode_to.required' => 'Barcode maximum range is required',
			'barcode_to.integer' => 'Barcode maximum range must be a number',
			'barcode_to.gt' => 'Barcode maximum range must be greater than Barcode minimum range',
		]);
	}
	
	
	function saveBarcode(Request $request)
	{
		$barcodes = trim($request->barcodes);
		$barcodes = str_replace("\n", ";", $barcodes);
		$barcodes = str_replace(",", ";", $barcodes);
		$parts = explode(';', $barcodes);
		
		Log::debug('parts=' . json_encode($parts));
		
		$this->user_data = new UserData();
		$merchant_id = $this->user_data->company_id();
		$duplicate_barcodes = "";
		
		$merchant_product = DB::table('merchantproduct')->
		select('id')->
		where('merchant_id', '=', $merchant_id)->
		where('product_id', '=', $request->id)->
		first();
		
		Log::debug('merchant_product=' . json_encode($merchant_product));
		
		
		$is_duplicate = false;
		foreach ($parts as $part) {
			$part = trim($part);
			
			Log::debug('merchant_id=' . $merchant_id);
			Log::debug('product_id =' . $request->id);
			Log::debug('barcode    =' . $part);
			
			$count = DB::table('merchantproduct as mp')->
			join('productbarcode as pb', 'mp.id', '=',
				'pb.merchantproduct_id')->
			select('pb.barcode')->
			where('mp.merchant_id', '=', $merchant_id)->
			// where('mp.product_id', '=', $request->id)->
			where('pb.barcode', '=', $part)->
			count();
			
			Log::debug('count=' . json_encode($count));
			
			if (empty($count)) {
				$product_barcode = new productbarcode();
				$product_barcode->merchantproduct_id = $merchant_product->id;
				$product_barcode->product_id = $request->id;
				$product_barcode->barcode = $part;
				$product_barcode->save();
				
			} else {
				$is_duplicate = true;
				$duplicate_barcodes .= $part . "<br>";
			}
		}
		
		$this->update_barcode_oceania($request->id);

		if ($is_duplicate) {
			$msg = "Duplicated barcodes found:<br>" .
				$duplicate_barcodes;
			
			$html = view('layouts.dialog', compact('msg'))->render();
			return $html;
			
		} else {
			return 0;
		}
	}
	
	
	public function showInventoryCost($product_id)
	{
		$id = Auth::user()->id;
		$user_roles = usersrole::where('user_id', $id)->get();
		
		$is_king = \App\Models\Company::where('owner_user_id',
			Auth::user()->id)->first();
		
		if ($is_king != null) {
			$is_king = true;
		} else {
			$is_king = false;
		}

        $userId = Auth::user()->id;
        $merchant = Merchant::select('merchant.id as id')
            ->join('company', 'company.id', '=', 'merchant.company_id')
            ->where('company.owner_user_id', $userId)->first();



        $averageCost = inventorycost::selectRaw("format(sum((cost/100) * quantity)/sum(quantity),2) as  average_cost")
            ->join('inventorycostproduct', 'inventorycostproduct.inventorycost_id', '=', 'inventorycost.id')
            ->where('inventorycost.buyer_merchant_id', $merchant->id)->where('inventorycostproduct.product_id', $product_id)->first()->toArray();

        $quantity_left = $this->check_quantity($product_id);
        $product = product::find($product_id);

        return view('inventory.inventorycost',
			compact('user_roles', 'is_king', 'product_id', 'quantity_left', 'product', 'averageCost'));
	}

    public function productInventoryCost(Request $request, $product_id)
    {

        $buyerMerchantId = Auth::user()->id;
        $merchant = Merchant::select('merchant.id as id')
            ->join('company', 'company.id', '=', 'merchant.company_id')
            ->where('company.owner_user_id', $buyerMerchantId)->first();

        $query = inventorycost::selectRaw("inventorycost.id as inventory_cost_id,DATE_FORMAT(doc_date, '%d%b%y') as dated ,doc_no, Format(inventorycostproduct.cost / 100, 2) as cost,inventorycostproduct.quantity as quantity")
            ->join('inventorycostproduct', 'inventorycostproduct.inventorycost_id', '=', 'inventorycost.id');


        /*
        // name filter
        $search = $request->input('search');
        if (isset($search['value']) && $search['value'] != '')
            $query->where('title', 'like', '%'.$search['value'].'%');
        */

        /*
        // order by
        $order = $request->input('order');
        if (isset($order[0]['column']) && $order[0]['column'] != '') {
            if ($order[0]['column'] == 2) {
                $query->orderBy('title', $order[0]['dir']);
            }
            if ($order[0]['column'] == 4) {
                $query->orderBy('price', $order[0]['dir']);
            }
        }
        */

        $query->where('inventorycost.buyer_merchant_id', $merchant->id)->where('inventorycostproduct.product_id', $product_id);

        $query->orderBy('inventorycostproduct.id', 'desc');


        $totalRecords = $query->get()->count();

        // applying limit
        $productCostDetails = $query->skip($request->input('start'))->take($request->input('length'))->get();


        $counter = 0 + $request->input('start');

        foreach ($productCostDetails as $key => $productCost) {
            $productCostDetails[$key]['indexNumber'] = ++$counter;
        }

        $response = [
            'data' => $productCostDetails,
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalRecords
        ];
        return response()->json($response);
    }


	public function showInventoryAutoProcurement()
	{
		$this->user_data = new UserData();
		
		$ids = merchantlocation::where('merchant_id',
			$this->user_data->company_id())->pluck('location_id');
		
		$location = location::where([['branch', '!=', 'null']])->
			whereIn('id', $ids)->
			latest()->get();
	
		return view('inventory.inventoryautoprocurement', compact('location'));
	}

	
	public function showInventoryStockIn()
	{
		$this->user_data = new UserData();
	//	$modal = "newLocationDialog";
		
		$ids = merchantlocation::where('merchant_id', $this->user_data->company_id())->pluck('location_id');
		
		$franchiseLocations = DB::table('franchisemerchant')->
			join('franchisemerchantloc','franchisemerchantloc.franchisemerchant_id','=','franchisemerchant.id')->
			where([
				'franchisemerchant.franchisee_merchant_id' => $this->user_data->company_id()
			])->
			whereNull('franchisemerchant.deleted_at')->
			whereNull('franchisemerchantloc.deleted_at')->
			pluck('location_id');
		
		$ids = array_merge($ids->toArray(),$franchiseLocations->toArray());
		
		$location = location::where([['branch', '!=', 'null']])->
			whereIn('id', $ids)->
			latest()->get();
	
		return view('inventory.inventorystockin', compact('location'));
	}


	public function get_product_barcodes_out() {
		$this->user_data = new UserData();
		$ptype 	= request()->get('product_type') ?? 'inventory';
		$location_id = request()->id;

		$franchiseLocations = DB::table('franchisemerchant')->
			join('franchisemerchantloc','franchisemerchantloc.franchisemerchant_id','=','franchisemerchant.id')->
			where([
				'franchisemerchant.franchisee_merchant_id' => $this->user_data->company_id(),
				'franchisemerchantloc.location_id' 	=> $location_id,
			])->
			whereNull('franchisemerchant.deleted_at')->
			whereNull('franchisemerchantloc.deleted_at')->
			get();

		if ($franchiseLocations->isEmpty()) {
			$ids = merchantproduct::where('merchant_id',
			$this->user_data->company_id())->pluck('product_id');
		} else {
			
			$franchiseLocations = DB::table('franchisemerchant')->
				join('franchisemerchantloc','franchisemerchantloc.franchisemerchant_id','=','franchisemerchant.id')->
				join('franchiseproduct', 'franchiseproduct.franchise_id','=','franchisemerchant.franchise_id')->
				where([
					'franchisemerchant.franchisee_merchant_id' => $this->user_data->company_id(),
					'franchisemerchantloc.location_id' 	=> $location_id,
					'franchiseproduct.active'			=> 1
				])->
				whereNull('franchisemerchant.deleted_at')->
				whereNull('franchisemerchantloc.deleted_at')->
				get();
			$ids = $franchiseLocations->pluck('product_id');
		}

		$product = product::where('ptype',$ptype)
						->whereIn('id',$ids)
				   		->whereNotNull(['name','photo_1','prdcategory_id',
							'prdsubcategory_id','brand_id'])
						->get();
		$product_id = $product->pluck('id')->toArray();
		
		$regularBarcode = DB::table('productbarcode')->
				whereIn('product_id', $product_id)->
				whereNull('deleted_at')->
				get();

		$bmatrixbarcode = DB::table('productbmatrixbarcode')->
				whereIn('product_id',$product_id)->
				whereNull('deleted_at')->
				get();


		$combinedList = [];

		//processing product for regular barcodes
		
		foreach($regularBarcode as $rb) {
				
				$product_ = product::find($rb->product_id);
				
				$array = [
					"product_id" 		=> 	$rb->barcode,
					"org_product_id" 	=> 	$rb->product_id,
					"barcode_id"		=> 	$rb->id,
					"product_name" 		=>	$product_->name,
					"thumbnail_1"		=> 	$product_->thumbnail_1,
					"regular"		=>	true,
					"barcode"		=>	$rb->barcode
				];
				
				$combinedList[] = $array;
		}
		
		//processing product for bmatrix
		foreach($bmatrixbarcode as $bmbc) {
				$product_ = product::find($rb->product_id);
				$array = [
					"product_id" 		=> 	'-',
					"org_product_id" 	=> 	$bmbc->product_id,
					"barcode_id"		=> 	$bmbc->id,
					"product_name" 		=>	$product_->name,
					"thumbnail_1"		=> 	$product_->thumbnail_1,
					"matrix"			=>	$bmbc->bmatrixbarcodejson,
					"bmatrix"			=>	true,
					"barcode"			=>	$bmbc->bmatrixbarcode
				];
					
				$combinedList[] = $array;
		}
			return $combinedList;
	}
	public function getAvailableRackData($data) {
		$result = [];

		$location_id = request()->id;
		$warehouse = warehouse::where('location_id', $location_id)->first();

		$rack_id = rack::where('warehouse_id', $warehouse->id)->
					where('type','own')->
					orderby('id', 'asc')->
					get();
		
		for($x = 0; $x < count($data); $x++) {
			$z = $data[$x];	
			foreach($rack_id as $rack) {
				$rack_no = rack::where('id', $rack_id)->value('rack_no');
			
				if (isset($z['bmatrix'])) {
				
					$qty = DB::table('productbmatrixbarcodelocation')->
						where([
							'rack_id'					=> $rack->id, 
							'location_id'				=> $location_id,
							'productbmatrixbarcode_id'	=> $z['barcode_id']
						])->
					get()->
					sum('quantity');

				} else {
		
					$qty = DB::table('productbarcodelocation')->
						where([
							'rack_id'					=> $rack->id, 
							'location_id'				=> $location_id,
							'productbarcode_id'			=> $z['barcode_id']
						])->
					get()->
					sum('quantity');

				}

			if ($qty == 0) {
				continue;
			}

			$data[$x]['qty_id']  = rand(1,10000);
			$data[$x]['quantity'] = $qty;
			$data[$x]['rack'] = true;
			$data[$x]['first_product'] = 0;
			$data[$x]['rack_id'] = $rack->id;
			$data[$x]['rack_no'] = $rack->rack_no;
			$result[] = $data[$x];
			}
		}
			return $result;
	}


	private function getAvailableNONRackData($data) {
		$result = [];

		$location_id = request()->id;
		$warehouse = warehouse::where('location_id', $location_id)->first();
		
		for($x = 0; $x < count($data); $x++) {
			$z = $data[$x];	
			
				if (isset($z['bmatrix'])) {
					$qty =	$this->location_barcode_qty($z['barcode_id'], $z['barcode'], $location_id, $is_matrix = true);
				} else {

				$qty =	$this->location_barcode_qty($z['barcode_id'], $z['barcode'], $location_id, $is_matrix = false); 
			}

			if ($qty == 0) {
				continue;
			}

			$data[$x]['quantity'] = $qty;
			$data[$x]['qty_id']  = rand(1,10000);
			$data[$x]['rack'] = false;
			$data[$x]['first_product'] = 0;
			$result[] = $data[$x];
		}

			return $result;
	}

	private function StockOUTProducts() {
		try {
			
			$location_id 	= request()->get('id');
			$location 		= location::where('id', $location_id)->first();
			$product_data 	= $this->get_product_barcodes_out();

			if ($location->warehouse == 1) {
				$product_data = $this->getAvailableRackData($product_data);
			} else {
				$product_data = $this->getAvailableNONRackData($product_data);
			}
			$product_data  = $this->decodeMatrix($product_data);

			$product_data =  collect( $product_data )->
				map(function($z) { 
					return (object) $z;
				})->
				reject(function($z){
				})->all();
	
			return Datatables::of($product_data)->
			
			addIndexColumn()->
			addColumn('inven_pro_id', function ($memberList) {
				  return $memberList->barcode ?? '-';	
			})->
			addColumn('inven_pro_name', function ($memberList) {
				return '<img src="' . asset('images/product/' . $memberList->org_product_id . '/thumb/' . 
						$memberList->thumbnail_1) . '" style="height:40px;width:40px;object-fit:contain;
						margin-right:8px;">' . $memberList->product_name;
			})->
			addColumn('inven_pro_colour', function ($memberList) {
				
				//<div style="padding:10px 20px; background:$memberList->color"></div>
			if (!isset($memberList->color)) {
				return '-';
			}

			return <<< EOD
		<div style="padding:10px 20px; background:$memberList->color"></div>
EOD;
			})->
			addColumn('inven_pro_matrix', function ($memberList) {
				return $memberList->matrix_string ?? '-';
			})->
			addColumn('inven_pro_rack', function ($memberList) {
				return $memberList->rack_no ?? '-';
			})->
			addColumn('inven_pro_existing_qty', function ($memberList) {
				return $memberList->quantity;
			})->
			addColumn('inven_pro_qty', function ($memberList) {
				if(isset($memberList->systemid)){
				$id  = $memberList->qty_id ?? $memberList->product_id;
				
					return '<div class="value-button increase" id="increase_' . $id .
						'" onclick="increaseValue(' . $id . 
						')" value="Increase Value" style="margin-top:-25px;"><ion-icon class="ion-ios-plus-outline" 
						style="font-size: 24px;margin-right:10px;"></ion-icon>
						</div><input type="number" onkeyup="check_value(' . $id . 
						')" id="number_' . $id . '"  class="number product_qty"
						 value="0"  min="0" max="' . $memberList->quantity .
 						'" required onblur="check_max(' . $id. ')">
					<div class="value-button decrease" id="decrease_' . $id . '" onclick="decreaseValue(' .
					 $id . ')" value="Decrease Value" style="margin-top:-25px;"><ion-icon
					 class="ion-ios-minus-outline" style="font-size: 24px;"></ion-icon> </div>';
				}else{
				$id  = $memberList->qty_id ?? $memberList->barcode_id;
						return '<div class="value-button increase" id="increase_' . $id. 
							'" onclick="increaseValue(' . $id . ')" value="Increase Value"
							style="margin-top:-25px;"><ion-icon class="ion-ios-plus-outline"
							 style="font-size: 24px;margin-right:10px;"></ion-icon>
                    		</div><input type="number" onkeyup="check_value(' . 
							$id . ')" id="number_' . $id . 
							'"  class="number product_qty" value="0"  min="0" max="' . $memberList->quantity . 
							'" required onblur="check_max(' . $id. ')">
                    		<div class="value-button decrease" id="decrease_' . $id . 
							'" onclick="decreaseValue(' . $id . 
							')" value="Decrease Value" style="margin-top:-25px;"
							><ion-icon class="ion-ios-minus-outline" style="font-size: 24px;"></ion-icon></div>';
				}
				
			})->
			addColumn('inven_bluecrab', function ($memberList) {
				return '-';		
			})
			->
			escapeColumns([])->
			make(true);


		} catch (\Exception $e) {
			Log::info($e);
			abort(404);
		}
	}

	public function get_location_product(Request $request)
	{
		$this->user_data = new UserData();
		$modal = "newLocationDialog";
		$location_id = $request->get('id');

		$franchiseLocations = DB::table('franchisemerchant')->
			join('franchisemerchantloc','franchisemerchantloc.franchisemerchant_id','=','franchisemerchant.id')->
			where([
				'franchisemerchant.franchisee_merchant_id' => $this->user_data->company_id(),
				'franchisemerchantloc.location_id' 	=> $location_id,
			])->
			whereNull('franchisemerchant.deleted_at')->
			whereNull('franchisemerchantloc.deleted_at')->
			get();

		if ($franchiseLocations->isEmpty()) {
			$ids = merchantproduct::where('merchant_id', $this->user_data->company_id())->pluck('product_id');
		} else {
			
			$franchiseLocations = DB::table('franchisemerchant')->
				join('franchisemerchantloc','franchisemerchantloc.franchisemerchant_id','=','franchisemerchant.id')->
				join('franchiseproduct', 'franchiseproduct.franchise_id','=','franchisemerchant.franchise_id')->
				where([
					'franchisemerchant.franchisee_merchant_id' => $this->user_data->company_id(),
					'franchisemerchantloc.location_id' 	=> $location_id,
					'franchiseproduct.active'			=> 1
				])->
				whereNull('franchisemerchant.deleted_at')->
				whereNull('franchisemerchantloc.deleted_at')->
				get();
			$ids = $franchiseLocations->pluck('product_id');
		}
	
		$type = $request->get('type');
		$source = $request->get('source');
		$opos_product = array();
		$final_product = array();
		$rack = array();
		$page = $request->get('page');
		$index = 0;
		
		if ($type == 'OUT') {
			$ret = $this->StockOUTProducts(); 
			return $ret;
		}

		if ($location_id) {
			$location = location::where('id', $location_id)->first();
			
			if ($source) {
				$opos_product = product::whereIn('ptype',
					array('inventory', 'rawmaterial'))->
					whereNotNull('name')->
					whereNotNull('prdcategory_id')->
					whereIn('id', $ids)->
					get();

			} else {
				$ptype = ($request->get('product_type')) ?
					$request->get('product_type') : 'inventory';

				$opos_product = product::where('ptype',$ptype)->
					whereIn('id',$ids)->
					whereNotNull(['name','photo_1','prdcategory_id',
						'prdsubcategory_id','brand_id'])->get();
			}

			if ($location->warehouse == 1) {
				$rack_data = $request->get('rack_data');
				$warehouse = warehouse::where('location_id', $location_id)->
					first();

				Log::debug('warehouse=' . json_encode($warehouse));
				
				if ($warehouse) {
					$rack_list = rack::where('warehouse_id',
						$warehouse->id)->where('type','own')->get();
				}
				foreach ($opos_product as $key => $value) {
					// $final_qty = $this->location_productqty($value->id, $location_id);
					
					if ($warehouse) {
						$rack_product_qty = 0;
						
						$rack = array();
						$total_qty = 0;
						$valid_rack = array();
						foreach ($rack_list as $rk_key => $rk_value) {
								$rack_product_count = stockreportproductrack::
									join('stockreportproduct', 'stockreportproductrack.stockreportproduct_id', '=', 'stockreportproduct.id')
									->where('stockreportproduct.product_id', $value->id)
									->where('stockreportproductrack.rack_id', $rk_value->id)
									->sum('stockreportproduct.quantity');
							
							if ($type == "OUT") {
								if ($rack_product_count <= 0) {
									continue;
								}
								$valid_rack[] = $rk_value->id;
							}
							$rack[$rk_key] = $rk_value;
							$total_qty += $rack_product_count;
						}


						if ($type == 'OUT') {
							$rack_all = rack::where('warehouse_id',
								$warehouse->id)->
								whereIn('id', $valid_rack)->
								pluck('id');

						} else {
							$rack_all = rack::where('warehouse_id',
								$warehouse->id)->where('type','own')->
								pluck('id');
						}
						$rack_id = stockreportproductrack::
							join('stockreportproduct', 'stockreportproductrack.stockreportproduct_id', '=', 'stockreportproduct.id')
							->where('stockreportproduct.product_id', $value->id)
							->whereIn('stockreportproductrack.rack_id', $rack_all)
							->orderby('stockreportproductrack.id', 'desc')->value('rack_id');

						if (empty($rack_id)) {
							$rack_id = rack::where('warehouse_id',
								$warehouse->id)->where('type','own')->orderby('id', 'asc')->
								value('id');
						}

						$final_qty = $this->location_productqty(
							$value->id, $location_id);
						Log::debug('product_id=' . json_encode($value->id));
						Log::debug('final_qty=' . json_encode($final_qty));
						if ($type == "OUT") {
							if ($final_qty <= 0) {
								continue;
							}

							$barcodes = productbarcode::select('productbarcode.id as barcode_id', 'productbarcode.*', 'product.*')
								->join('product', 'product.id', '=', 'productbarcode.product_id')
								->where('product_id', $value->id)
								->orderBy('product.systemid', 'desc')->get();
						     Log::debug('stock out barcodes = '.json_encode($barcodes));

							$barcode_qty = 0;
							foreach ($barcodes as $b_key => $b_value) {
								$b_rack_product_qty = 0;
								
								$b_total_qty = 0;
								$b_valid_rack = array();
								foreach ($rack_list as $rk_key => $rk_value) {
									$rack_product_count = productbarcodelocation::
									where('productbarcode_id', $b_value->barcode_id)
										->where('location_id', $location_id)
										->where('rack_id', $rk_value->id)
										->sum('quantity');
									
									if ($rack_product_count <= 0) {
										continue;
									}
									$b_valid_rack[] = $rk_value->id;
									$rack[$rk_key] = $rk_value;
									$b_total_qty += $rack_product_count;
								}

								$b_rack_all = rack::where('warehouse_id',
									$warehouse->id)->
									whereIn('id', $b_valid_rack)->pluck('id');

								$b_rack_id = productbarcodelocation::
								where('productbarcode_id', $b_value->barcode_id)
									->where('location_id', $location_id)
									->whereIn('rack_id', $b_rack_all)
									->orderby('id', 'desc')
									->pluck('rack_id')->first();
								
								if (empty($b_rack_id)) {
									$b_rack_id = rack::where('warehouse_id',
										$warehouse->id)->where('type','own')->
										orderby('id', 'asc')->
										pluck('id')->first();
								}

								if ($b_total_qty <= 0) {
									continue;
								}

								$b_final_qty = $b_total_qty;
								if ($rack_data) {
									foreach ($rack_data as $rd_key => $rd_value) {
										if ($rd_value['product_id'] == $b_value->barcode_id) {
											$b_rack_id = $rd_value['rack_id'];
										}
									}
								}

								log::debug('b_rack_id' . $b_rack_id);
								if ($b_rack_id) {
									$b_total_qty = productbarcodelocation::
									where('productbarcode_id', $b_value->barcode_id)
										->where('rack_id', $b_rack_id)
										->where('location_id', $location_id)
										->sum('quantity');
								}
								log::debug('b_total_qty' . $b_total_qty);
								$b_rack_no = rack::where('id', $b_rack_id)->value('rack_no');
								
								$final_product[$index] = $b_value;
								$final_product[$index]->warehouse = 1;
								$final_product[$index]->existing_qty = $b_final_qty;
								$final_product[$index]->quantity = $b_total_qty;
								$final_product[$index]->product_id = $b_value->barcode_id;
								$final_product[$index]->systemid = $b_value->barcode;
								$final_product[$index]->first_product = 0;
								$final_product[$index]->rack = $rack;
								$final_product[$index]->rack_id = $b_rack_id;
								$final_product[$index]->rack_no = $b_rack_no;
								$final_product[$index]->location_id = $location_id;
								$barcode_qty += $b_final_qty;
								$index++;
							}
							
						}
						if ($rack_data) {
							foreach ($rack_data as $rd_key => $rd_value) {
								if ($rd_value['product_id'] == $value->id) {
									$rack_id = $rd_value['rack_id'];
								}
							}
						}
						if ($rack_id) {
							$total_qty = stockreportproductrack::
							join('stockreportproduct', 'stockreportproductrack.stockreportproduct_id', '=', 'stockreportproduct.id')
								->where('stockreportproduct.product_id', $value->id)
								->where('stockreportproductrack.rack_id', $rack_id)
								->sum('stockreportproduct.quantity');
						}
						$rack_no = rack::where('id', $rack_id)->value('rack_no');
						$final_product[$index] = $value;
						$final_product[$index]->warehouse = 1;
						if ($type == 'OUT') {
							$final_product[$index]->existing_qty = $final_qty - $barcode_qty;
						} else {
							$final_product[$index]->existing_qty = $final_qty;
						}
						$final_product[$index]->first_product = 1;
						$final_product[$index]->quantity = $total_qty;
						$final_product[$index]->product_id = $value->id;
						$final_product[$index]->rack = $rack;
						$final_product[$index]->rack_id = $rack_id;
						$final_product[$index]->rack_no = $rack_no;
						$final_product[$index]->location_id = $location_id;
						$index++;
						
					}
				}
				
			} else {
				foreach ($opos_product as $key => $value) {
					
					$final_qty = $this->location_productqty($value->id, $location_id);
					if ($type == "OUT") {
						// if ($final_qty <= 0) {
						// 	continue;
						// }
						$barcodes = productbarcode::select('productbarcode.id as barcode_id', 'productbarcode.*', 'product.*')
							->join('product', 'product.id', '=', 'productbarcode.product_id')
							->where('product_id', $value->id)
							->orderBy('productbarcode.id', 'desc')->get();
							
						$barcode_qty = 0;
						foreach ($barcodes as $b_key => $b_value) {
							
							
							$final_qty_barcode = productbarcodelocation::
								where('productbarcode_id', $b_value->barcode_id)
								->where('location_id', $location_id)
								->sum('quantity');
							
							if ($final_qty_barcode <= 0) {
								continue;
							}
							
							$final_product[$index] = $b_value;
							$final_product[$index]->existing_qty = $final_qty_barcode;
							$final_product[$index]->quantity = $final_qty_barcode;
							$final_product[$index]->rack = $rack;
							$final_product[$index]->product_id = $value->barcode_id;
							$final_product[$index]->systemid = $value->barcode;
							$final_product[$index]->first_product = 0;
							$final_product[$index]->location_id = $location_id;
							$barcode_qty += $final_qty_barcode;
							$index++;
						}
					}
					$final_product[$index] = $value;
					if ($type == "OUT") {
						$final_product[$index]->existing_qty = $final_qty - $barcode_qty;
				
					} else {
						$final_product[$index]->existing_qty = $final_qty;
					}
					$final_product[$index]->first_product = 1;
					$final_product[$index]->quantity = $final_qty;
					$final_product[$index]->product_id = $value->id;
					$final_product[$index]->rack = $rack;
					$final_product[$index]->location_id = $location_id;
					$index++;
				}
			}

			$i =0;
			foreach ($final_product as $key => $value) {
				if($value->systemid!=null){
					$opos_product[$i]=$value;
					$i++;
				}
			}

			$j= count($opos_product);
			foreach ($final_product as $key => $value) {
			
				if($value->systemid==null){
					$opos_product[$j]=$value;
					$j++;
				}
			}
		}

		if ($type == "OUT") {


		} else {
			return Datatables::of($opos_product)->
			addIndexColumn()->
			addColumn('inven_pro_id', function ($memberList) {
				return $memberList->systemid;
			})->
			addColumn('inven_pro_name', function ($memberList) {
				return '<img src="' . asset('images/product/' . $memberList->id .
					'/thumb/' . $memberList->thumbnail_1) .
				   	'" style="height:40px;width:40px;object-fit:contain;margin-right:8px;">' . 
					$memberList->name;
			})->
			addColumn('inven_pro_colour', function ($memberList) {
				$product_color = productcolor::join('color', 'productcolor.color_id', '=', 'color.id')
					->where('productcolor.product_id', $memberList->id)->first();
				if ($product_color) {
					
					return $product_color->name;
				}
				return "-";
			})->
			addColumn('inven_pro_matrix', function ($memberList) {
				return '-';
			})->
			addColumn('inven_pro_rack', function ($memberList) {
				if (!is_array($memberList->rack)) {
					return '-';
				}

				if (count($memberList->rack) <= 0) {
					return '-';
				}
				
				return '<div style="cursor: pointer; color: blue;"
						class="rack_list" id="' . $memberList->id . 
							'" onclick="open_rack(' . $memberList->id . ',)">' . 
							(($memberList->rack_no) ? $memberList->rack_no : "-") . '</div>';    
			})->
			addColumn('inven_pro_existing_qty', function ($memberList) {
				return $memberList->quantity?? $memberList->existing_qty;
			})->
			addColumn('inven_pro_qty', function ($memberList) {
				
				return '<div class="value-button increase" id="increase_' . $memberList->id . 
						'" onclick="increaseValue(' . $memberList->id .
						')" value="Increase Value" style="margin-top:-25px;">
						<ion-icon class="ion-ios-plus-outline" style="font-size: 24px;margin-right:10px;"></ion-icon>
						</div><input type="number" onkeyup="check_value(' . $memberList->id . ')" id="number_' . 
						$memberList->id . '"  class="number product_qty" value="0" min="0"  required>
                    	<div class="value-button decrease" id="decrease_' . $memberList->id . 
						'" onclick="decreaseValue(' . $memberList->id . ')" value="Decrease Value" 
						style="margin-top:-25px;"><ion-icon class="ion-ios-minus-outline" 
						style="font-size: 24px;"></ion-icon></div>';
			})->
			addColumn('inven_bluecrab', function ($memberList) use ($location_id) {
				$location = $memberList->location_id ?? $location_id;
				return '<p data-field="bluecrab"
                        style="padding-top:1.4px;display:block;cursor: pointer;"
						class=" btn-primary bg-bluecrab" data-toggle="modal" 
						><a class="os-linkcolor" href="/barcodeinventoryin/' . 
						$memberList->systemid . '/' . $location .
						 '" target="_blank" style="color:#fff;text-decoration: none;">O</a></p>';
			})->
			escapeColumns([])->
			make(true);
		}
	}


	public function get_autoprocurement(Request $request)
	{
		$autopc = [];

		return Datatables::of($autopc)->
			addIndexColumn()->
			addColumn('inven_pro_id', function ($list) {
				return '';
			})->
			addColumn('inven_pro_name', function ($list) {
				return 'Product';
			})->
			addColumn('inven_avg_daily_sales', function ($list) {
				return '';
			})->
			addColumn('inven_sus_supply', function ($list) {
				return '';
			})->
			addColumn('inven_active', function ($list) {
				return '';
			})->
			escapeColumns([])->
			make(true);
	}

	
	public function selectRack(Request $request)
	{
		
		try {
			$product_id = $request->get('product_id');
			$location_id = $request->get('location_id');
			$type = $request->get('type');
			$product_type = $request->get('product_type');
			$fieldName = 'select_rack';
			$warehouse = warehouse::where('location_id', $location_id)->first();
			Log::debug('warehouse=' . json_encode($warehouse));
			if ($warehouse) {
				$rack_list = rack::where('warehouse_id', $warehouse->id)->where('type','own')->get();
				// $final_qty = $this->location_productqty($value->id, $location_id);
				
				$rack_product_qty = 0;
				$rack_id = '';
				$rack = array();
				$total_qty = 0;
				foreach ($rack_list as $rk_key => $rk_value) {
					if ($product_type == 'barcode') {
						$rack_product_count = productbarcodelocation::
						where('productbarcode_id', $product_id)
							->where('rack_id', $rk_value->id)
							->where('location_id', $location_id)
							->sum('quantity');
					} else {
						
						$rack_product_count = stockreportproductrack::
						join('stockreportproduct', 'stockreportproductrack.stockreportproduct_id', '=', 'stockreportproduct.id')
							->where('stockreportproduct.product_id', $product_id)
							->where('stockreportproductrack.rack_id', $rk_value->id)
							->sum('stockreportproduct.quantity');
					}
					
					if ($type == "OUT") {
						if ($rack_product_count <= 0) {
							continue;
						}
					}
					$rack[$rk_key] = $rk_value;
					$total_qty += $rack_product_count;
				}
			}
			return view('inventory.inventory-modals',
				compact(['product_id', 'fieldName', 'rack']));
			
		} catch (\Illuminate\Database\QueryException $ex) {
			$response = (new ApiMessageController())->queryexception($ex);
		}
	}
	
	public function updateProductQuantitystock(Request $request)
	{
		
		try {
			$id = Auth::user()->id;
			
			$this->user_data = new UserData();
			$merchant_id = $this->user_data->company_id();

			$table_data = $request->get('table_data');
			$stock_type = $request->get('stock_type');
			$total_qty = 0;

			$stock_system = new SystemID('stockreport');
			$stock_system_id = $stock_system->__toString();
			
			foreach ($table_data as $key => $value) {		
				
				$stockproduct_barcode = null;
				$stockproduct_barcodejson = null; 

				if ($value['qty'] <= 0) continue;

				//	Comming barcode inventory in
				if (isset($value['mainpage'])) {	
					
						$product = product::find($value['product_id']);

						$merchant_product = DB::table('merchantproduct')->
							select('id')->
						//	where('merchant_id','=',$merchant_id)->
							where('product_id' ,'=',$product->id)->
		   					first();
		 
						$is_default_exist = DB::table('productbarcode')->
							where([
								'barcode' => $product->systemid,
							])->
							whereNull('deleted_at')->first();
						
						
						if (empty($is_default_exist)) {

							$primary_id = DB::table('productbarcode')->
								insertGetId([
									"product_id" => $product->id,
									"merchantproduct_id"=> $merchant_product->id,
									"barcode" => $product->systemid,
									"name" => $product->name
								]);
							$stockproduct_barcode = $product->systemid;

						} else {
							$primary_id = $is_default_exist->id;
							$stockproduct_barcode = $is_default_exist->barcode;
						}
						
						$product_details = $product;
						$is_matrix = false;			

				} else {
		
					if ($value['is_matrix'] == 'true') {
						//MATRIX BARCODE QUANTITY
						
						$barcode = DB::table('productbmatrixbarcode')->
							where('id',$value['barcode_id'])->
							whereNull('deleted_at')->first();
						
						$product_details = product::where('id', $barcode->product_id)
							->whereNotNull(['name','photo_1','prdcategory_id',
							'prdsubcategory_id','brand_id'])
							->first();

						$stockproduct_barcode = $barcode->bmatrixbarcode;
						$stockproduct_barcodejson = $barcode->bmatrixbarcodejson;
						$is_matrix = true;				

					} else {

						//for Regular Bar codes
						$barcode = productbarcode::where('id',$value['barcode_id'])
							->first();
					
						$product_details = product::where('id',
						$barcode->product_id)
				   		->whereNotNull(['name','photo_1','prdcategory_id',
					   		'prdsubcategory_id','brand_id'])
				   		->first();
						$stockproduct_barcode = $barcode->barcode;
						$is_matrix = false;				
					}
				}
				
				Log::info([
					"is_matrix_barcode"	=> isset($value['is_matrix']) ? $value['is_matrix']:false,
					"barcode_id"		=> $value['barcode_id'] ?? $primary_id,
					"barcode_found"		=> !empty($barcode),
					"product_id"		=> $barcode->product_id ?? false,
					"product_found"		=> !empty($product_details),
					'Rack ID'			=> isset($value['rack_id']) ? $value['rack_id']:false,
					"STOCK TYPE"		=> $stock_type,
				]);

			
				if ($is_matrix) {
					DB::table('productbmatrixbarcodelocation')->
						insert([
							"productbmatrixbarcode_id" 	=> $barcode->id,
							"location_id" => $value['location_id'],
							"rack_id" => isset($value['rack_id']) ?
								$value['rack_id']:0,
							"quantity" => ($stock_type == 'IN') ?
								$value['qty'] : '-' . $value['qty'],
							"created_at" => date('Y-m-d H:i:s'),
							"updated_at" => date('Y-m-d H:i:s')
						]);

				} else {
					$pbloc = new productbarcodelocation();
					$pbloc->productbarcode_id = $barcode->id ?? $primary_id ;
					$pbloc->location_id = $value['location_id'];
					$pbloc->rack_id = 	isset($value['rack_id']) ?
						$value['rack_id']:0;
					$pbloc->quantity = ($stock_type == 'IN') ?
						$value['qty'] : '-' . $value['qty'];
					$pbloc->save();
				}	

				$stock = new StockReport();
				$stock->creator_user_id = Auth::user()->id;
				$stock->type = ($stock_type == 'IN') ? 3 : 4;
				$stock->systemid = $stock_system_id;
				$stock->status = 'confirmed';
				$stock->location_id = $value['location_id'];
				$stock->save();
				
				DB::table('stockreportmerchant')->insert([
					"stockreport_id"			=>	$stock->id,
					"franchisee_merchant_id"	=>  $this->user_data->company_id(),
					"created_at"				=>  date("Y-m-d H:i:s"),
					"updated_at"				=>  date("Y-m-d H:i:s")
				]);

				$total_qty += $value['qty'];
				
				$stockreportproduct = new stockreportproduct();
				$stockreportproduct->quantity = ($stock_type == 'IN') ? 
					$value['qty'] : '-' . $value['qty'];

				$stockreportproduct->stockreport_id 	= $stock->id;
				$stockreportproduct->product_id 	= $product_details->id;
				$stockreportproduct->barcode		= $stockproduct_barcode;
				$stockreportproduct->bmatrixbarcodejson = $stockproduct_barcodejson;
				$stockreportproduct->save();


				//Extra Rack insertions
				if (isset($value['rack_id'])) {	
					$rackproduct = rackproduct::where('rack_id', '=', $value['rack_id'])->
						where('product_id', '=', $product_details->id)->
						orderby('id', 'desc')->
						first();

					if (!empty($rackproduct)) {
						$curr_qty = $rackproduct->quantity;

						if ($stock_type == "IN") {
							$curr_qty += $value['qty'];
						} else {
							$curr_qty -= $value['qty'];
						}

					} else {
						$curr_qty = $value['qty'];
					}

					$product = new rackproduct();
					$product->product_id = $value['product_id'];
					$product->rack_id = $value['rack_id'];
					$product->quantity = $curr_qty;
					$product->save();

					$stockreportproductrack = new stockreportproductrack();
					$stockreportproductrack->stockreportproduct_id = $stockreportproduct->id;
					$stockreportproductrack->rack_id = $value['rack_id'];
					$stockreportproductrack->save();
					$total_qty += $value['qty'];
				}
			}
		
			if ($total_qty > 0) {
				
				$msg = ($stock_type == "IN") ?  
					"Stock In performed succesfully":
					"Stock Out performed succesfully";

				Log::debug('msg='.$msg);


			} else {
				$msg = "Please select product";
			}

			$data = view('layouts.dialog', compact('msg'));
			
		} catch (\Exception $e) {
			$msg = "Error occured while saving stock";
			
			Log::error(
				"Error @ " . $e->getLine() . " file " . $e->getFile() .
				":" . $e->getMessage()
			);
			
			$data = view('layouts.dialog', compact('msg'));
		}
		return $data;
	}

	
	public function barcodeinventoryin($system_id, $location_id)
	{
		$UserData = new UserData();
		
		$product = product::where('systemid', $system_id)->first();
		$location = location::where('id', $location_id)->first();

		if (empty($product)) {
			
			Log::info([
				"Error"	=> "Records for found",
				"Is Product Empty"	=> empty($product),
				"Is Location Empty"	=> empty($location),
			]);

			abort(404);
		}

		$is_forbidden_location = empty(
			DB::table('merchantlocation')->
			where([
				"merchant_id"	=> $UserData->company_id(),
				"location_id"	=> $location->id
			])->
			whereNull('deleted_at')->
			first()
		);

		$is_franchise_location =  DB::table('franchisemerchant')->
			join('franchisemerchantloc','franchisemerchantloc.franchisemerchant_id','=','franchisemerchant.id')->
			where([
				'franchisemerchant.franchisee_merchant_id' => $UserData->company_id(),
				'franchisemerchantloc.location_id' 	=> $location->id,
			])->
			whereNull('franchisemerchant.deleted_at')->
			whereNull('franchisemerchantloc.deleted_at')->
			first();
		
		$is_forbidden_product = empty(
			merchantproduct::where([
				"merchant_id"	=> $UserData->company_id(),
				"product_id"	=> $product->id
			])->
			first()
		);
	
		$franchiseProduct = DB::table('franchisemerchant')->
				join('franchisemerchantloc','franchisemerchantloc.franchisemerchant_id','=','franchisemerchant.id')->
				join('franchiseproduct', 'franchiseproduct.franchise_id','=','franchisemerchant.franchise_id')->
				where([
					'franchisemerchant.franchisee_merchant_id' => $UserData->company_id(),
					'franchisemerchantloc.location_id' 	=> $location->id,
					'franchiseproduct.active'			=> 1,
					'franchiseproduct.product_id'		=> $product->id
				])->
				whereNull('franchisemerchant.deleted_at')->
				whereNull('franchisemerchantloc.deleted_at')->
				first();
		
			/*
			 * dd( ['FP' => $is_forbidden_product ,
			 *  'FrP' => empty($franchiseProduct)  ,'FL' => $is_forbidden_location,
			 *   'MRL' => empty($is_franchise_location)]);
			 */

		if (($is_forbidden_product && empty($franchiseProduct) ) || $is_forbidden_location && empty($is_franchise_location ) ) {

			Log::info([
				"Error"	=> "This page is forbidden",
				"Is Product Forbidden"	=> $is_forbidden_product,
				"Is Location Forbidden"	=> $is_forbidden_location
			]);
			abort(403);
		}

		return view('inventory.barcodeinventoryin',
			compact('product', 'system_id', 'location'));
		
	}
	
	public function get_product_barcodes_new() {
		try {
			//primary data fetch
			$product = product::where('systemid', request()->system_id)->first();
			$regularBarcode = DB::table('productbarcode')->
				where('product_id', $product->id)->
				whereNull('deleted_at')->
				orderBy('created_at', 'asc')->
				get();

			$bmatrixbarcode = DB::table('productbmatrixbarcode')->
				where('product_id',$product->id)->
				whereNull('deleted_at')->
				get();


			$location_id = request()->location_id;

			$combinedList = [];
			//processing product for regular barcodes
			foreach($regularBarcode as $rb) {
				$is_first  = $rb->barcode == $product->systemid ? 1:0;
				$array = [
					"product_id" 		=> 	$rb->barcode,
					"org_product_id" 	=> 	$product->id,
					"barcode_id"		=> 	$rb->id,
					"product_name" 		=>	$product->name,
					"thumbnail_1"		=> 	$product->thumbnail_1,
					"barcode"			=>	$rb->barcode,
					"regular"			=>	true,
					"first_product"		=> 	$is_first,
				];
				$combinedList[] = $array;
			}

			//processing product for bmatrix
			foreach($bmatrixbarcode as $bmbc) {
				preg_match_all('!\d+!', $bmbc->bmatrixbarcode, $stringBarcode);
				$array = [
					"product_id" 		=> 	'-',
					"org_product_id" 	=> 	$product->id,
					"barcode_id"		=> 	$bmbc->id,
					"product_name" 		=>	$product->name,
					"thumbnail_1"		=> 	$product->thumbnail_1,
					"matrix"			=>	$bmbc->bmatrixbarcodejson,
					"barcode"			=>	$bmbc->bmatrixbarcode,
					"bmatrix"			=>	true,
				];
					
				$combinedList[] = $array;

			}

			return $combinedList;
		} catch (\Exception $e) {
			Log::info($e);
			abort(404);
		}
	}
	
	public	function getRackData($data) { 
		
		$location_id = request()->location_id;
		$rack_data = request()->get('rack_data');
		$warehouse = warehouse::where('location_id', $location_id)->first();

		for($x = 0; $x < count($data); $x++) {
			 $z = $data[$x];
		
			if ($rack_data) {
				foreach ($rack_data as $rd_key => $rd_value) {	
					if ($rd_value['product_id'] == $z['barcode_id']) {
								$rack_id = $rd_value['rack_id'];
							}
						}
				}
				
			if (empty($rack_id)) {
				$rack_id = rack::where('warehouse_id', $warehouse->id)->
					where('type','own')->
					orderby('id', 'asc')->
					pluck('id')->
					first();
			}

			$rack_no = rack::where('id', $rack_id)->value('rack_no');
			
			if (isset($z['bmatrix'])) {
				$qty =	$this->location_barcode_qty($z['barcode_id'], $z['barcode'], $location_id, $is_matrix = true);
			} else {
				$qty =	$this->location_barcode_qty($z['barcode_id'], $z['barcode'], $location_id, $is_matrix = false);
			}

		
			$data[$x]['quantity'] = $qty;
			$data[$x]['rack'] = true;
			$data[$x]['first_product'] = 0;
			$data[$x]['rack_id'] = $rack_id;
			$data[$x]['rack_no'] = $rack_no;
		}

		return $data;

	}

	public function decodeMatrix($data) {
		for ($x = 0; $x < count($data); $x++) {
			
			if (!isset($data[$x]["matrix"])) {
				continue;
			}

			$live_barcode = json_decode($data[$x]["matrix"],true);
				$string = '';
				//string conversion
				foreach($live_barcode as $val) {
					foreach($val as $k => $v) {
						if ($k == 'bmatrix_id' || $k == 'systemid') {
							continue;
						}

						if ($k == 'color_id') {
							if ($v != 0) {
								$append = $v = DB::table('color')->
									find(DB::table('bmatrixcolor')->
									find($v)->color_id)->hex_code;

								$k = "Colour";
								$data[$x]['color'] = $append;
								$string =	ucfirst($k).":<b>$v</b>".
									($string == '' ? '': '<b>, </b>').$string;
								$commas = true;
								continue;
							} else {
								continue;
							}

						} else {
							$v = DB::table('bmatrixattribitem')->
								where('id', $v)->
								first()->name;

							$k = DB::table('bmatrixattrib')->
								where('id', $k)->
								first()->name;
						}

						$string .=	($string == '' ? '': '<b>, </b>') .
						ucfirst($k).":<b>$v</b>";
						$commas = true;
					}

					$data[$x]['matrix_string'] = $string;
				}
		}
		return $data;
	}
	
	public function getNonRackData($data) {
		
		$location_id = request()->location_id;

		for ($x = 0; $x < count($data); $x++) {
			
			$z = $data[$x];

			if (isset($z['bmatrix'])) {
				$qty =	$this->location_barcode_qty($z['barcode_id'], $z['barcode'], $location_id, $is_matrix = true);
			} else {
				$qty =	$this->location_barcode_qty($z['barcode_id'], $z['barcode'], $location_id, $is_matrix = false);
			}

			$data[$x]['quantity'] = $qty;
		}

		return $data;
	}

	public function get_product_barcodes(Request $request)
	{
		
		$location_id = request()->location_id;
		$location 		= location::where('id', $location_id)->first();
		$product_data 	= $this->get_product_barcodes_new();

		if ($location->warehouse == 1) {
			$product_data = $this->getRackData($product_data);
		} else {
			$product_data = $this->getNonRackData($product_data);
		}
		$product_data  = $this->decodeMatrix($product_data);

		$product_data =  collect( $product_data )->
				map(function($z) { 
								return (object) $z;
				})->
				reject(function($z){
				})->all();

		//dd($product_data);	
		return Datatables::of($product_data)->
		addIndexColumn()->
		addColumn('inven_pro_id', function ($memberList) {
			return $memberList->barcode ?? '-';
		})->
		addColumn('inven_pro_name', function ($memberList) {
			
			return '<img src="' . asset('images/product/' . $memberList->org_product_id . 
				'/thumb/' . $memberList->thumbnail_1)
				. '" style="height:40px;width:40px;object-fit:contain;margin-right:8px;">' 
				. $memberList->product_name;

		})->
		addColumn('inven_pro_colour', function ($memberList) {
		if(!isset($memberList->color)) {
			$product_color = productcolor::join(
				'color', 'productcolor.color_id', '=', 'color.id')->
				where(
					'productcolor.product_id', $memberList->product_id)->
					first();

			if ($product_color) {
				return $product_color->name;
			}

			return "-";
		} else {
			return <<< EOD
		<div style="padding:10px 20px; background:$memberList->color"></div>
EOD;
		}
		})->
		addColumn('inven_pro_matrix', function ($memberList) {
			return 	$memberList->matrix_string  ?? '-';
		})->
		addColumn('inven_pro_rack', function ($memberList) {
			
			if(!isset($memberList->rack)) {
				return '-';
			}	

			return '<div style="cursor: pointer; color: blue;"
					class="rack_list" id="' . $memberList->barcode_id .
					'" onclick="open_rack(' . $memberList->barcode_id . 
					',' . ($memberList->first_product ?? null). ')">' 
					. ( $memberList->rack_no ?? "-") . 
					'</div>';  

		})->
		
		addColumn('inven_pro_existing_qty', function ($memberList) {
			return $memberList->quantity ?? 0;
		})->
		addColumn('inven_pro_qty', function ($memberList) {
				$is_matrix = isset($memberList->matrix_string) ? 'true':'false';
				return '<div class="value-button increase" id="increase_' .
					$memberList->barcode_id . '" onclick="increaseValue(' . $memberList->barcode_id . 
					')" value="Increase Value" style="margin-top:-25px;"><ion-icon class="ion-ios-plus-outline"
				   	style="font-size: 24px;margin-right:10px;"></ion-icon>
					</div><input type="number" is-matrix="'.$is_matrix .'" id="number_' . $memberList->barcode_id . 
					'"  class="number product_qty" value="0" min="0"  required>
					<div class="value-button decrease" id="decrease_' . $memberList->barcode_id . 
					'" onclick="decreaseValue(' . $memberList->barcode_id . ')" value="Decrease Value" style="margin-top:-25px;">
					<ion-icon class="ion-ios-minus-outline" style="font-size: 24px;"></ion-icon>
					</div>';
		})->
		escapeColumns([])->
		make(true);
		
	}
	
	public function updateProductBarcodeQuantity(Request $request)
	{
		try {
			$id = Auth::user()->id;
			
			$this->user_data = new UserData();
			$merchant_id = $this->user_data->company_id();

			$table_data = $request->get('table_data');
			$warehouse = $request->get('warehouse');
			$stock_type = $request->get('stock_type');
			$system_id = $request->get('system_id');
			$total_qty = 0;

			// Squidster: Replaces old mechanism
			$stock_system = new SystemID('stockreport');
			$stock_system_id = $stock_system->__toString();
			foreach ($table_data as $key => $value) {

				$stockproduct_barcode = null;
				$stockproduct_barcodejson = null; 

				if ($value['qty'] <= 0) continue;
			

				if ($value['is_matrix'] == 'true') {
					//MATRIX BARCODE QUANTITY
				
					$barcode = DB::table('productbmatrixbarcode')->
						where('id',$value['barcode_id'])->
						whereNull('deleted_at')->first();
						
					$product_details = product::where('id', $barcode->product_id)
						   ->first();
				
					$stockproduct_barcode = $barcode->bmatrixbarcode;
					$stockproduct_barcodejson = $barcode->bmatrixbarcodejson; 
				} else {
					//for Regular Bar codes
					$barcode = productbarcode::where('id',
								$value['barcode_id'])->first();
				
					$product_details = product::where('id',
						$barcode->product_id)
				   		->first();

					$stockproduct_barcode = $barcode->barcode;
				}


				Log::info([
					"is_matrix_barcode"	=> $value['is_matrix'],
					"barcode_id"		=>	$value['barcode_id'],
					"barcode_found"		=>	!empty($barcode),
					"product_id"		=> 	$barcode->product_id ?? false,
					"product_found"		=> !empty($product_details),
					"is_ware house"		=>	($warehouse == 1),
					'rack_id'			=> ($warehouse == 1 ? $value['rack_id']:0),
				]);

				if (!empty($product_details)) {
						//product validation
					$merchant_product = DB::table('merchantproduct')->
						where('merchant_id','=',$merchant_id)->
						where('product_id' ,'=',$product_details->id)->
						first();
					$franchiseProduct = DB::table('franchisemerchant')->
						join('franchisemerchantloc','franchisemerchantloc.franchisemerchant_id','=','franchisemerchant.id')->
						join('franchiseproduct', 'franchiseproduct.franchise_id','=','franchisemerchant.franchise_id')->
						where([
							'franchisemerchant.franchisee_merchant_id'	=> $this->user_data->company_id(),
							'franchiseproduct.active'					=> 1,
							'franchiseproduct.product_id'				=> $product_details->id
						])->
						whereNull('franchisemerchant.deleted_at')->
						whereNull('franchisemerchantloc.deleted_at')->
						first();

					if (empty($merchant_product) && empty($franchiseProduct) ) {
						//throw new \Exception("Product is forbidden");
						Log::info([
							"Product ID"	=>	$product_details->id,
							"error"			=>	"Product is forbidden",
						]);
						
						continue;
					}

				} else {
					//throw new \Exception("Invalid product");
					continue;
				}

				if ($value['is_matrix'] == 'true') {
					
					DB::table('productbmatrixbarcodelocation')->
						insert([
							"productbmatrixbarcode_id" => $barcode->id,
							"location_id"	=>	$value['location_id'],
							"rack_id"	=> ($warehouse == 1 ? $value['rack_id']:0),
							"quantity"	=>	$stock_type == 'IN' ? $value['qty']:($value['qty']) * -1,
							"franchisee_merchant_id" => $this->user_data->company_id(),
							"created_at"	=> date('Y-m-d H:i:s'),
							"updated_at"	=> date('Y-m-d H:i:s')
						]);


				} else {
					
					$pbloc = new productbarcodelocation();
					$pbloc->productbarcode_id = $barcode->id;
					$pbloc->location_id = $value['location_id'];
					$pbloc->rack_id = $warehouse == 1 ? $value['rack_id']:0;
					$pbloc->quantity = ($stock_type == 'IN' ? $value['qty']:('-'.$value['qty']));	
					$pbloc->franchisee_merchant_id = $this->user_data->company_id();
					$pbloc->save();

				}


				$stock = new StockReport();
				$stock->creator_user_id = Auth::user()->id;

				$stock->type = ($stock_type == 'IN') ? 3 : 4;
				$stock->systemid = $stock_system_id;
				$stock->status = 'confirmed';
				$stock->location_id = $value['location_id'];
				$stock->save();
				$total_qty += $value['qty'];

				$stockreportproduct = new stockreportproduct();
				$stockreportproduct->quantity = ($stock_type == 'IN') ? $value['qty'] : '-' . $value['qty'];
				$stockreportproduct->stockreport_id = $stock->id;
				$stockreportproduct->product_id = $product_details->id;
				$stockreportproduct->barcode		= $stockproduct_barcode;
				$stockreportproduct->bmatrixbarcodejson = $stockproduct_barcodejson;
				$stockreportproduct->save();

				DB::table('stockreportmerchant')->insert([
					"stockreport_id" 			=> $stock->id,
					"franchisee_merchant_id"	=> $this->user_data->company_id(),
					"created_at"				=> date("Y-m-d H:i:s"),
					"updated_at"				=> date("Y-m-d H:i:s")
				]);	

				if ($warehouse == 1) {
					$stockreportproductrack = new stockreportproductrack();
					$stockreportproductrack->stockreportproduct_id = $stockreportproduct->id;
					$stockreportproductrack->rack_id = $value['rack_id'];
					$stockreportproductrack->save();
				}

				}

				if ($total_qty > 0) {
					$msg = ($stock_type == "IN") ? "Stock In performed succesfully":"Stock out performed succesfully";
				} else {
					$msg = "Please select product";
				}
				
				$data = view('layouts.dialog', compact('msg'));
			
		} catch (\Exception $e) {
			$msg = "Error occured while saving stock";
			Log::error(
				"Error @ " . $e->getLine() . " file " . $e->getFile() .
				":" . $e->getMessage()
			);
			
			$data = view('layouts.dialog', compact('msg'));
		}
		return $data;
	}
	
	public function showInventoryStockOut()
	{
		
		$this->user_data = new UserData();
	//	$modal = "newLocationDialog";
		$ids = merchantlocation::where('merchant_id', $this->user_data->company_id())->pluck('location_id');
	
		$franchiseLocations = DB::table('franchisemerchant')->
			join('franchisemerchantloc','franchisemerchantloc.franchisemerchant_id','=','franchisemerchant.id')->
			where([
				'franchisemerchant.franchisee_merchant_id' => $this->user_data->company_id()
			])->
			whereNull('franchisemerchant.deleted_at')->
			whereNull('franchisemerchantloc.deleted_at')->
			pluck('location_id');
		
		$ids = array_merge($ids->toArray(),$franchiseLocations->toArray());

		$location = location::where([['branch', '!=', 'null']])->whereIn('id', $ids)->latest()->get();
	
		return view('inventory.inventorystockout',
			compact('location'));
	}

	
}
