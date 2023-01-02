<?php

namespace App\Http\Controllers;

use Log;

use Illuminate\Http\Request;
use App\Models\usersrole;
use App\Models\role;
use App\Models\Company;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use App\Models\MerchantLink;

use \App\Classes\SystemID;

use App\Models\SettingBarcodeMatrix;
use \App\Models\product;
use \App\Models\productcolor;
use \App\Models\prd_inventory;
use \App\Models\Merchant;
use App\Models\Oneway;
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
use \App\Models\ExtStockReport;
use \App\Models\stockreportremarks;
use \App\Models\opos_damagerefund;
use \App\Models\Staff;
use \App\Models\opos_wastage;
use \App\Models\opos_wastageproduct;
use \App\Models\productbarcode;
use \App\Models\warehouse;
use \App\Models\rack;
use \App\Models\rackproduct;
use \App\Models\ext_rackproduct;
use \App\Models\stockreportproduct;
use \App\Models\stockreportproductrack;
use \App\Models\ext_stockreportproduct;
use \App\Models\ext_stockreportproductrack;
use \App\Models\productbarcodelocation;
use Milon\Barcode\DNS1D;
use Milon\Barcode\DNS2D;
use URL;

use \App\Models\warranty;

use \App\Classes\UserData;

class WarehouseController extends Controller
{
	public function showWarehouseStockIn($location_id)
	{
		$id = Auth::user()->id;
		$user_roles = usersrole::where('user_id', $id)->get();
		
		$is_king = \App\Models\Company::where('owner_user_id',
			Auth::user()->id)->first();
		
		$userData = new UserData();
		$modal = "newLocationDialog";
		$ids = merchantlocation::where('merchant_id', $userData->company_id())->pluck('location_id');
		$location = location::where('id', $location_id)->first();
		
		if ($is_king != null) {
			$is_king = true;
		} else {
			$is_king = false;
		}
		
		$userId = app('App\Http\Controllers\DataController')->getCompanyUserId();
		
        $responderIds = MerchantLink::where('initiator_user_id', $userId)->
			pluck('responder_user_id')->toArray();

        $initiatorIds = MerchantLink::where('responder_user_id', $userId)->
			pluck('initiator_user_id')->toArray();

        $merchantUserIds = array_merge($responderIds, $initiatorIds);

        $first = Company::selectRaw(
			"company.id as company_id,
			company.name as company_name,
			company.business_reg_no as business_reg_no,
			company.systemid as company_system_id,
			company.owner_user_id,
			merchant.id as merchant_id,
			initiators.status as initiator_status,
			responders.status as responder_status,
			initiators.id as initiator_id,
			responders.id as responder_id,
			null as oneway_id,
			null as oneway_status
			"
        )->join('merchant', 'merchant.company_id', '=', 'company.id')
		->leftJoin('merchantlink as initiators', function($leftJoin)use($userId)
        {
            $leftJoin->on('company.owner_user_id', '=', 'initiators.responder_user_id');
            $leftJoin->on(DB::raw('initiators.initiator_user_id'), DB::raw('='),DB::raw("'".$userId."'"));
        })
		->leftJoin('merchantlink as responders', function($leftJoin)use($userId)
        {
            $leftJoin->on('company.owner_user_id', '=', 'responders.initiator_user_id');
            $leftJoin->on(DB::raw('responders.responder_user_id'), DB::raw('='),DB::raw("'".$userId."'"));
        })->whereIn('company.owner_user_id', $merchantUserIds);
		
		$oneWayData = Oneway::selectRaw(
			"id as company_id,
			company_name,
			business_reg_no as business_reg_no,
			null as company_system_id,
			null as owner_user_id,
			self_merchant_id as merchant_id,
			null as initiator_status,
			null as responder_status,
			null as initiator_id,
			null as responder_id,
			id as oneway_id,
			status as oneway_status
			"
        )->where('self_merchant_id', $userData->company_id());
		
		$externals = $first->union($oneWayData)->get();		
		
		return view('warehouse.warehousestockin',
			compact('user_roles', 'is_king', 'location', 'externals', 'location_id'));
	}	
	
	public function selectRack(Request $request)
	{
		
		try {
			$product_id = $request->get('product_id');
			$location_id = $request->get('location_id');
			$company_id = $request->get('external_id');
			$type = $request->get('type');
			$product_type = $request->get('product_type');
			$fieldName = 'select_rack';
			$warehouse = warehouse::where('location_id', $location_id)->first();
			Log::debug('warehouse=' . json_encode($warehouse));
			if ($warehouse) {
				$rack_list = rack::where('warehouse_id', $warehouse->id)->get();
				// $final_qty = $this->location_productqty($value->id, $location_id);
				
				$rack_product_qty = 0;
				$rack_id = '';
				$rack = array();
				$total_qty = 0;
				foreach ($rack_list as $rk_key => $rk_value) {
					if ($type == "OUT") {					
						$rack_product_count_ext = ext_stockreportproductrack::
						join('ext_stockreportproduct', 'ext_stockreportproductrack.ext_stockreportproduct_id', '=', 'ext_stockreportproduct.id')->
						join('ext_stockreport', 'ext_stockreportproduct.ext_stockreport_id', '=', 'ext_stockreport.id')
							->where('ext_stockreportproduct.product_id', $product_id)
							->where('ext_stockreportproductrack.rack_id', $rk_value->id)
							->where('ext_stockreport.company_id', '!=' ,$company_id)
							->whereNotNull('ext_stockreport.company_id')
							->count();	
				
						if ($rack_product_count_ext > 0) {
							Log::debug('UNSET=' . $rk_value);
							$rack[$rk_key] = $rk_value;
							$total_qty += $rack_product_count_ext;
						} else {
						//	Log::debug('UNSET=' . $rk_value->id);
						//	Log::debug('UNSET=' . $rack[$rk_key]);
							//unset($rack[$rk_key]);
						}						
					} else 	{	
						Log::debug('ELSE=');
						$rack_product_count = stockreportproductrack::
						join('stockreportproduct', 'stockreportproductrack.stockreportproduct_id', '=', 'stockreportproduct.id')
						//	->where('stockreportproduct.product_id', $product_id)
							->where('stockreportproductrack.rack_id', $rk_value->id)
							->count();
							
						$rack_product_count_ext = ext_stockreportproductrack::
						join('ext_stockreportproduct', 'ext_stockreportproductrack.ext_stockreportproduct_id', '=', 'ext_stockreportproduct.id')
						//	->where('ext_stockreportproduct.product_id', $product_id)
							->where('ext_stockreportproductrack.rack_id', $rk_value->id)
							->where('ext_stockreportproduct.company_id', '!=' ,$company_id)
							->whereNotNull('ext_stockreportproduct.company_id')
							->count();	
								
						if (($rack_product_count + $rack_product_count_ext) > 0) {
							unset($rack[$rk_key]);
						} else {
							$rack[$rk_key] = $rk_value;
							$total_qty += $rack_product_count;
						}
					}
				}
			}
		//	Log::debug('RACK=' . $rack);
			return view('inventory.inventory-modals',
				compact(['product_id', 'fieldName', 'rack']));
			
		} catch (\Illuminate\Database\QueryException $ex) {
			Log::debug('warehouse=' . json_encode($ex->getMessage()));
			$response = (new ApiMessageController())->queryexception($ex);
		}
	}	
	
	public function showWarehouseStockOut($location_id)
	{
		$id = Auth::user()->id;
		$user_roles = usersrole::where('user_id', $id)->get();
		
		$is_king = \App\Models\Company::where('owner_user_id',
			Auth::user()->id)->first();
		
		$userData = new UserData();
		$modal = "newLocationDialog";
		$ids = merchantlocation::where('merchant_id', $userData->company_id())->pluck('location_id');
		$location = location::where('id', $location_id)->first();
		
		if ($is_king != null) {
			$is_king = true;
		} else {
			$is_king = false;
		}
		
		$userId = app('App\Http\Controllers\DataController')->getCompanyUserId();
		
        $responderIds = MerchantLink::where('initiator_user_id', $userId)->
			pluck('responder_user_id')->toArray();

        $initiatorIds = MerchantLink::where('responder_user_id', $userId)->
			pluck('initiator_user_id')->toArray();

        $merchantUserIds = array_merge($responderIds, $initiatorIds);

        $first = Company::selectRaw(
			"company.id as company_id,
			company.name as company_name,
			company.business_reg_no as business_reg_no,
			company.systemid as company_system_id,
			company.owner_user_id,
			merchant.id as merchant_id,
			initiators.status as initiator_status,
			responders.status as responder_status,
			initiators.id as initiator_id,
			responders.id as responder_id,
			null as oneway_id,
			null as oneway_status
			"
        )->join('merchant', 'merchant.company_id', '=', 'company.id')
		->leftJoin('merchantlink as initiators', function($leftJoin)use($userId)
        {
            $leftJoin->on('company.owner_user_id', '=', 'initiators.responder_user_id');
            $leftJoin->on(DB::raw('initiators.initiator_user_id'), DB::raw('='),DB::raw("'".$userId."'"));
        })
		->leftJoin('merchantlink as responders', function($leftJoin)use($userId)
        {
            $leftJoin->on('company.owner_user_id', '=', 'responders.initiator_user_id');
            $leftJoin->on(DB::raw('responders.responder_user_id'), DB::raw('='),DB::raw("'".$userId."'"));
        })->whereIn('company.owner_user_id', $merchantUserIds);
		
		$oneWayData = Oneway::selectRaw(
			"id as company_id,
			company_name,
			business_reg_no as business_reg_no,
			null as company_system_id,
			null as owner_user_id,
			self_merchant_id as merchant_id,
			null as initiator_status,
			null as responder_status,
			null as initiator_id,
			null as responder_id,
			id as oneway_id,
			status as oneway_status
			"
        )->where('self_merchant_id', $userData->company_id());
		
		$externals = $first->union($oneWayData)->get();		
		
		return view('warehouse.warehousestockout', compact(
			'user_roles', 'is_king', 'location',
			'externals', 'location_id'
		));
	}		
	
	public function get_external_product(Request $request)
	{
		$this->user_data = new UserData();
		$modal = "newLocationDialog";
		$ids = merchantproduct::where('merchant_id',
			$this->user_data->company_id())->pluck('product_id');
	
		$external_id = $request->get('id');
		$location_id = $request->get('location_id');
		$type = $request->get('type');
		$source = $request->get('source');
		$opos_product = array();
		$final_product = array();
		$index = 0;
		
		if ($external_id) {

				$ptype = ($request->get('product_type')) ?
					$request->get('product_type') : 'inventory';

				$opos_product = product::where('ptype', $ptype)->
					whereNotNull('name')->
					whereNotNull('prdcategory_id')->
					whereIn('id', $ids)->get();
			
				$rack_data = $request->get('rack_data');
				$warehouse = warehouse::where('location_id', $location_id)->
					first();

				Log::debug('warehouse=' . json_encode($warehouse));
				
				if ($warehouse) {
					$rack_list = rack::where('warehouse_id',
						$warehouse->id)->get();
				}

				foreach ($opos_product as $key => $value) {
					$final_qty = $this->extlocation_productqty($value->id, $location_id, $external_id);
					if ($warehouse) {
						$rack_product_qty = 0;
						$rack_id = '';
						$rack = array();
						$total_qty = 0;
						$valid_rack = array();
						foreach ($rack_list as $rk_key => $rk_value) {
							$rack_product_count = stockreportproductrack::
							join('stockreportproduct', 'stockreportproductrack.stockreportproduct_id', '=', 'stockreportproduct.id')
							//	->where('stockreportproduct.product_id', $product_id)
								->where('stockreportproductrack.rack_id', $rk_value->id)
								->count();
								
							$rack_product_count_ext = ext_stockreportproductrack::
							join('ext_stockreportproduct', 'ext_stockreportproductrack.ext_stockreportproduct_id', '=', 'ext_stockreportproduct.id')
							//	->where('ext_stockreportproduct.product_id', $product_id)
								->where('ext_stockreportproductrack.rack_id', $rk_value->id)
								->where('ext_stockreportproduct.company_id', '!=' ,$external_id)
								->whereNotNull('ext_stockreportproduct.company_id')
								->count();	
							if ($type == "OUT") {
								if ($rack_product_count <= 0) {
									continue;
								}
								$valid_rack[] = $rk_value->id;
							}
							if (($rack_product_count + $rack_product_count_ext) > 0) {
								unset($rack[$rk_key]);
							} else {
								$rack[$rk_key] = $rk_value;
								$total_qty += $rack_product_count;
							}
						//	Log::debug('total_qty=' . json_encode($total_qty));
						}

						if ($type == 'OUT') {

						}
						if ($rack_data) {
							foreach ($rack_data as $rd_key => $rd_value) {
								if ($rd_value['product_id'] == $value->id) {
									$rack_id = $rd_value['rack_id'];
								}
							}
						}
						if ($rack_id) {
							$total_qty = ext_stockreportproductrack::
							join('ext_stockreportproduct', 'ext_stockreportproductrack.ext_stockreportproduct_id', '=', 'ext_stockreportproduct.id')
								->where('ext_stockreportproduct.product_id', $value->id)
								->where('ext_stockreportproductrack.rack_id', $rack_id)
								->sum('ext_stockreportproduct.quantity');
						}
						$rack_no = rack::where('id', $rack_id)->value('rack_no');
						$final_product[$index] = $value;
						$final_product[$index]->warehouse = 1;
						if ($type == 'OUT') {
							$final_product[$index]->existing_qty = $final_qty;
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
		} 
		
			Log::debug('opos_product=' . json_encode($opos_product));
			return Datatables::of($opos_product)->
			addIndexColumn()->
			addColumn('inven_pro_id', function ($memberList) {
				return $memberList->systemid;
			})->
			addColumn('inven_pro_name', function ($memberList) {
				return '<img src="' . asset('images/product/' . $memberList->id . '/thumb/' . $memberList->thumbnail_1) . '" style="height:40px;width:40px;object-fit:contain;margin-right:8px;">' . $memberList->name;
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
				// Squidster: protect count() from null
				if (!empty($memberList) and !empty($memberList->rack)) {
					if (count($memberList->rack) <= 0) {
						return '-';
					}
				}
				return '<div style="cursor: pointer; color: blue;"
                        class="rack_list" id="' . $memberList->product_id . '" onclick="open_rack(' . $memberList->product_id . ',' . $memberList->first_product . ')">' . (($memberList->rack_no) ? $memberList->rack_no : "-") . '</div>';
			})->
			addColumn('inven_pro_existing_qty', function ($memberList) {
				return '<p>' . $memberList->existing_qty . '</p>';
			})->
			addColumn('inven_pro_qty', function ($memberList) {
				if ($memberList->type == "OUT") {
					return '<div class="value-button increase" id="increase_' . $memberList->id . '" onclick="increaseValue(' . $memberList->id . ')" value="Increase Value" style="margin-top:-25px;"><ion-icon class="ion-ios-plus-outline" style="font-size: 24px;margin-right:10px;"></ion-icon>
                    </div><input type="number" onkeyup="check_value(' . $memberList->id . ')" id="number_' . $memberList->id . '"  class="number product_qty" value="0" min="0" max="' . $memberList->quantity . '" required onblur="check_max(' . $memberList->product_id . ')"  required>
                    <div class="value-button decrease" id="decrease_' . $memberList->id . '" onclick="decreaseValue(' . $memberList->id . ')" value="Decrease Value" style="margin-top:-25px;"><ion-icon class="ion-ios-minus-outline" style="font-size: 24px;"></ion-icon>
                    </div>';
				} else {
					return '<div class="value-button increase" id="increase_' . $memberList->id . '" onclick="increaseValue(' . $memberList->id . ')" value="Increase Value" style="margin-top:-25px;"><ion-icon class="ion-ios-plus-outline" style="font-size: 24px;margin-right:10px;"></ion-icon>
                    </div><input type="number" onkeyup="check_value(' . $memberList->id . ')" id="number_' . $memberList->id . '"  class="number product_qty" value="0" min="0"  required>
                    <div class="value-button decrease" id="decrease_' . $memberList->id . '" onclick="decreaseValue(' . $memberList->id . ')" value="Decrease Value" style="margin-top:-25px;"><ion-icon class="ion-ios-minus-outline" style="font-size: 24px;"></ion-icon>
                    </div>';
				}
			})->
			escapeColumns([])->
			make(true);		
	}	

	public function extlocation_productqty($product_id, $location_id, $external_id)
	{
		$final_qty = 0;
		$stockreport_qty = DB::table('ext_stockreport')->join('ext_stockreportproduct','ext_stockreportproduct.ext_stockreport_id','=','ext_stockreport.id')->
										where('ext_stockreportproduct.product_id', $product_id)->where('ext_stockreport.location_id', $location_id)
										->whereIn('ext_stockreport.type', ['stockin','stockout'])->where('ext_stockreport.company_id', $external_id)->sum('ext_stockreportproduct.quantity');
		$final_qty = $stockreport_qty;
		return $final_qty;
	}	
	
	public function updateProductQuantitystock(Request $request)
	{
		
		try {
			$id = Auth::user()->id;
			
			$table_data = $request->get('table_data');
			$stock_type = $request->get('stock_type');
			$total_qty = 0;
			$stock_system = DB::select("select nextval(ext_stockreport_seq) as index_stock");
			$stock_system_id = $stock_system[0]->index_stock;
			$stock_system_id = sprintf("%010s", $stock_system_id);
			$stock_system_id = '144' . $stock_system_id;


			log::debug('qty value:' . json_encode($table_data));
			foreach ($table_data as $key => $value) {
	            foreach ($table_data as $k => $v) {
	                if($value["product_id"] == $v["product_id"] && $key != $k && $value["location_id"] == $v["location_id"] && $value["external_id"] == $v["external_id"] ){
	                    unset($table_data[$key]);
	                    continue;
	                }
	            }
	        }

		
			foreach ($table_data as $key => $value) {
				
				Log::debug('value='.json_encode($value));

				// Don't add new record if qty <= 0
				if ($value['qty'] <= 0) continue;
				$product_details = product::where('id',
					$value['product_id'])->first();
				$stock = new ExtStockReport();
				$stock->creator_user_id = Auth::user()->id;

				//('voided', 'transfer', 'stockin', 'stockout', 'stocktake')
				$stock->type = ($stock_type == 'IN') ? 3 : 4;
				$stock->systemid = $stock_system_id;
				$reportquantity = ($stock_type == 'IN') ?
					$value['qty'] : '-' . $value['qty'];

				//$stock->product_id = $product_details->id;
				$stock->status = 'confirmed';
				$stock->location_id = $value['location_id'];
				$stock->company_id = $value['external_id'];
				$stock->save();
				$total_qty += $value['qty'];
				DB::table('ext_stockreportproduct')->insert([
					'product_id' => $product_details->id,
					'ext_stockreport_id' => $stock->id,
					'quantity' => $reportquantity,
					'received' => $reportquantity,
					'status' => 'checked',
					'lost' => 0,
					'damaged' => 0,
					'remark' => '',
					'image' => '',
					'created_at' => date("Y-m-d H:i:s"),
					'updated_at' => date("Y-m-d H:i:s"),
				]);
			}
		
			if ($total_qty > 0) {
				if ($stock_type == "IN") {
					$msg = "Stock In performed succesfully";
				} else {
					$msg = "Stock Out performed succesfully";
				}
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
	
	public function updateRackProductQuantitystock(Request $request)
	{
		
		try {
			$id = Auth::user()->id;
			
			$table_data = $request->get('table_data');
			$stock_type = $request->get('stock_type');
			
			$total_qty = 0;
			$stock_system = DB::select("select nextval(ext_stockreport_seq) as index_stock");
			$stock_system_id = $stock_system[0]->index_stock;
			$stock_system_id = sprintf("%010s", $stock_system_id);
			$stock_system_id = '111' . $stock_system_id;
			
			foreach ($table_data as $key => $value) {
				if ($value['qty'] <= 0) {
					continue;
				}
				
				$rackproduct = ext_rackproduct::where('rack_id', '=', $value['rack_id'])->where('product_id', '=', $value['product_id'])->orderby('id', 'desc')->first();
				if ($rackproduct) {
					$curr_qty = $rackproduct->quantity;
					if ($stock_type == "IN") {
						$curr_qty += $value['qty'];
					} else {
						$curr_qty -= $value['qty'];
					}
				} else {
					$curr_qty = $value['qty'];
				}
				DB::table('rack')->where('id',$value['rack_id'])->update(['type' => 'ext']);
				log::debug('curr_qty' . $curr_qty);
				log::debug('value:' . json_encode($value));
				$product = new ext_rackproduct();
				$product->product_id = $value['product_id'];
				$product->rack_id = $value['rack_id'];
				$product->quantity = $curr_qty;
				$product->save();
				
				$stockreport = new ExtStockReport();
				$stockreport->type = ($stock_type == 'IN') ? 3 : 4; //('voided', 'transfer', 'stockin', 'stockout', 'stocktake')
				$stockreport->creator_user_id = Auth::user()->id;
				$stockreport->systemid = $stock_system_id;
				$reportquantity = ($stock_type == 'IN') ?
					$value['qty'] : '-' . $value['qty'];
			//	$stockreport->product_id = $value['product_id'];
				$stockreport->status = 'confirmed';
				$stockreport->location_id = $value['location_id'];
				$stockreport->company_id = $value['external_id'];
				$stockreport->save();
				
				$total_qty += $value['qty'];
				
				$ext_stockreportproduct = new ext_stockreportproduct();
				$ext_stockreportproduct->quantity = ($stock_type == 'IN') ? $value['qty'] : '-' . $value['qty'];
				$ext_stockreportproduct->received = ($stock_type == 'IN') ? $value['qty'] : '-' . $value['qty'];
				$ext_stockreportproduct->ext_stockreport_id = $stockreport->id;
				$ext_stockreportproduct->status = 'checked';
				$ext_stockreportproduct->company_id = $value['external_id'];
				$ext_stockreportproduct->lost = 0;
				$ext_stockreportproduct->damaged = 0;
				$ext_stockreportproduct->remark = '';
				$ext_stockreportproduct->image = '';
				$ext_stockreportproduct->product_id = $value['product_id'];
				$ext_stockreportproduct->save();
				
				$stockreportproductrack = new ext_stockreportproductrack();
				$stockreportproductrack->ext_stockreportproduct_id = $ext_stockreportproduct->id;
				$stockreportproductrack->rack_id = $value['rack_id'];
				$stockreportproductrack->save();
				$total_qty += $value['qty'];
			}
			
			if ($total_qty > 0) {
				if ($stock_type == "IN") {
					$msg = "Stock In performed succesfully";
				} else {
					$msg = "Stock Out performed succesfully";
				}
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
	
    public function showWarehouseMain(){

        $id = Auth::user()->id;
        $user_roles = usersrole::where('user_id', $id)->get();

        $is_king = \App\Models\Company::where('owner_user_id',
            Auth::user()->id)->first();

        $this->user_data = new UserData();
        $ids = merchantlocation::where('merchant_id',
			$this->user_data->company_id())->
			pluck('location_id');

        $location = location::where([['branch', '!=', 'null']])->
			where('warehouse',1)->
			whereIn('id', $ids)->latest()->get();

      return view('warehouse.warehouse',
            compact('user_roles', 'is_king','location'));
    }

    public function get_location_rack(Request $request)
    {
          $location_id = $request->get('location_id');
          $rack = array();
          if($location_id) {
              $location = location::where('id',$location_id)->first(); 
              $warehouse = warehouse::where('location_id',$location_id)->first();
              Log::debug('$warehouse='.json_encode($warehouse));

              if($warehouse) {
                $rack = rack::where('warehouse_id',$warehouse->id)->get();
				//dd($rack);
                Log::debug('$rack='.json_encode($rack));
                foreach ($rack as $key => $value) {
				  if($rack[$key]->type == 'own'){
					  $rack_product = StockReport::
						  //select('stockreportproduct.id')
						join('stockreportproduct','stockreport.id','=',
							  'stockreportproduct.stockreport_id')
						->join('stockreportproductrack','stockreportproductrack.stockreportproduct_id',
								  '=','stockreportproduct.id')
						->where('stockreportproductrack.rack_id',$value->id)
						->where('stockreport.location_id',$location_id)
						->where('stockreport.type','stockin')
						->groupBy('stockreportproduct.barcode')
						->get();
				  	  $rack_product->map(function ($value) use ($location_id) {
						$rack_product_qty = StockReport::
							join('stockreportproduct','stockreport.id','=',
								'stockreportproduct.stockreport_id')
							->join('stockreportproductrack',
								'stockreportproductrack.stockreportproduct_id',
								'=','stockreportproduct.id')
							->where('stockreportproduct.barcode',$value->barcode)
							->where('stockreportproductrack.rack_id',$value->rack_id)
							->where('stockreport.location_id',$location_id)  
							->sum('stockreportproduct.quantity');
							$value->quantity = $rack_product_qty;  
					});
					  
				 	$rack_product = $rack_product->filter(function($z) {
						return $z->quantity > 0;
					});

					$rack[$key]->quantity = count($rack_product);  
                			$rack[$key]->company = '';
				  } else {
					$rack_product = ExtStockReport::select('ext_stockreport.company_id')
						->join('ext_stockreportproduct','ext_stockreport.id','=',
							'ext_stockreportproduct.ext_stockreport_id')
						->join('ext_stockreportproductrack',
								'ext_stockreportproductrack.ext_stockreportproduct_id',
								'=','ext_stockreportproduct.id')
						 ->where('ext_stockreportproductrack.rack_id',$value->id)
						 ->where('ext_stockreport.location_id',$location_id)
						 ->groupBy('ext_stockreport.company_id')
						 ->get();
					
                    			$rack[$key]->quantity = count($rack_product);
					 $rack[$key]->company = '';
					if(count($rack_product) > 0){
						$company_id = $rack_product[0]->company_id;
						if(!is_null($company_id)){
							$rackcompany = DB::table('company')->where('id',$company_id)->first();
							if(!is_null($rackcompany)){
								 $rack[$key]->company = $rackcompany->name;
							}
						}
						
					}					
				  }
                }
              }
	  }
	  if (!empty($rack)) {
		if (!empty($rack[0]->rack_no)) {
	  		$rack = $rack->sortBy('rack_no');
	  	}

	  }
	  return Datatables::of($rack)->
                addIndexColumn()->
                addColumn('inven_pro_id', function ($memberList) {
                    return $memberList->systemid;
                })->
                addColumn('inven_pro_rack_no', function ($memberList) {
                    return (($memberList->rack_no) ? $memberList->rack_no : '-');
                    // return "<a title='".$memberList->rack_no."' href='#' data-toggle='modal' data-target='#war_rack_no' data-item_id='".$memberList->id."' data-rack_no='".$memberList->rack_no."' onclick='rack_update(this)'>". (($memberList->rack_no) ? $memberList->rack_no : '-') ."</a>";                    
                })->
                addColumn('inven_pro_desc', function ($memberList) {
                   return "<a title='".$memberList->description."' href='#' data-toggle='modal' data-target='#rack_desc' data-rackid='".$memberList->id."' data-item_desc='".$memberList->description."' onclick='rack_update(this)'>". (($memberList->description) ? $memberList->description : 'Description') ."</a>";
                })->
                addColumn('inven_pro_allocated', function ($memberList) {
                    return '<a href=""data-toggle="modal" data-target="#allocatedProducts" class="retail-voucher-list os-linkcolor" style="text-decoration:none;" onclick="rack_product_list('.$memberList->id.','.$memberList->rack_no.',\''.$memberList->company.'\',\''.$memberList->type.'\')">'.$memberList->quantity.'</a>';
                })->
                addColumn('inven_pro_remark', function ($memberList) {
                               return "<a title='".$memberList->remarks."' href='#' data-toggle='modal' data-target='#rack_remark' data-rack_id='".$memberList->id."' data-item_remark='".$memberList->remarks."' onclick='rack_update(this)'>". (($memberList->remarks) ? $memberList->remarks : 'Remarks') ."</a>";
                })->
                escapeColumns([])->
                make(true);

    }

    public function externalcompanies(Request $request)
	{
		$userId = app('App\Http\Controllers\DataController')->getCompanyUserId();
		$userData = new UserData();

        $responderIds = MerchantLink::where('initiator_user_id', $userId)->
			pluck('responder_user_id')->toArray();

        $initiatorIds = MerchantLink::where('responder_user_id', $userId)->
			pluck('initiator_user_id')->toArray();

        $merchantUserIds = array_merge($responderIds, $initiatorIds);

        $first = Company::selectRaw(
			"company.id as company_id,
			company.name as company_name,
			company.business_reg_no as business_reg_no,
			company.systemid as company_system_id,
			company.owner_user_id,
			merchant.id as merchant_id,
			initiators.status as initiator_status,
			responders.status as responder_status,
			initiators.id as initiator_id,
			responders.id as responder_id,
			null as oneway_id,
			null as oneway_status
			"
        )->join('merchant', 'merchant.company_id', '=', 'company.id')
		->leftJoin('merchantlink as initiators', function($leftJoin)use($userId)
        {
            $leftJoin->on('company.owner_user_id', '=', 'initiators.responder_user_id');
            $leftJoin->on(DB::raw('initiators.initiator_user_id'), DB::raw('='),DB::raw("'".$userId."'"));
        })
		->leftJoin('merchantlink as responders', function($leftJoin)use($userId)
        {
            $leftJoin->on('company.owner_user_id', '=', 'responders.initiator_user_id');
            $leftJoin->on(DB::raw('responders.responder_user_id'), DB::raw('='),DB::raw("'".$userId."'"));
        })->whereIn('company.owner_user_id', $merchantUserIds);
		
		$oneWayData = Oneway::selectRaw(
			"id as company_id,
			company_name,
			business_reg_no as business_reg_no,
			null as company_system_id,
			null as owner_user_id,
			self_merchant_id as merchant_id,
			null as initiator_status,
			null as responder_status,
			null as initiator_id,
			null as responder_id,
			id as oneway_id,
			status as oneway_status
			"
        )->where('self_merchant_id', $userData->company_id());
		
		$externals = $first->union($oneWayData)->get();
	//	dd($externals);

		return Datatables::of($externals)->
			addIndexColumn()->
			addColumn('merchant_id', function ($company) {
				$id = "--";
				if(!is_null($company->company_system_id)){
					$id = $company->company_system_id;
				}
				return $id;
			})->
			addColumn('business_reg_no', function ($company) {
				return $company->business_reg_no;
			})->
			addColumn('merchant_name', function ($company) {
				return $company->company_name;
			})->
			addColumn('merchant_product', function ($company) {
				return 0;
			})->
			addColumn('merchant_active', function ($company) {
				$html = "";
				if(!is_null($company->initiator_status)){
					if($company->initiator_status == 'active'){
						$html = '<button type="button" id="mlink'.$company->initiator_id.'" onclick="changeMlinkStatus('.$company->initiator_id.', \''.$company->initiator_status.'\')" style="width:75px" class="btn btn-default btn-warehouse-list-active">Active</button>';
					} else {
						$html = '<button type="button" id="mlink'.$company->initiator_id.'" onclick="changeMlinkStatus('.$company->initiator_id.', \''.$company->initiator_status.'\')" style="width:75px" class="btn btn-default btn-warehouse-list">Active</button>';
					}
				}
				if(!is_null($company->responder_status)){
					if($company->responder_status == 'active'){
						$html = '<button type="button" id="mlink'.$company->responder_id.'" style="width:75px" onclick="changeMlinkStatus('.$company->responder_id.', \''.$company->responder_status.'\')" class="btn btn-default btn-warehouse-list-active">Active</button>';
					} else {
						$html = '<button type="button" id="mlink'.$company->responder_id.'" style="width:75px" onclick="changeMlinkStatus('.$company->responder_id.', \''.$company->responder_status.'\')" class="btn btn-default btn-warehouse-list">Active</button>';
					}						
				}
				if(!is_null($company->oneway_status)){
					if($company->oneway_status == 'active'){
						$html = '<button type="button" id="oneway'.$company->oneway_id.'" style="width:75px" onclick="changeOnewayStatus('.$company->oneway_id.', \''.$company->oneway_status.'\')" class="btn btn-default btn-warehouse-list-active">Active</button>';
					} else {
						$html = '<button type="button" id="oneway'.$company->oneway_id.'" style="width:75px" onclick="changeOnewayStatus('.$company->oneway_id.', \''.$company->oneway_status.'\')" class="btn btn-default btn-warehouse-list">Active</button>';
					}
				}
				return $html;
			})->
			escapeColumns([])->
			make(true);		
	}


    public function rack_product_list(Request $request)
    {
      $rack_product =array();
      $rack_id = $request->get('rack_id');
      $location_id = $request->get('location_id');
      $company = $request->get('company');
      $type = $request->get('type');
      if($rack_id) {
		  if($type == 'ext'){
			  $rack_product = ExtStockReport::
					join('ext_stockreportproduct','ext_stockreport.id','=','ext_stockreportproduct.ext_stockreport_id')
					->join('ext_stockreportproductrack','ext_stockreportproductrack.ext_stockreportproduct_id','=','ext_stockreportproduct.id')
					->join('rack','rack.id','=','ext_stockreportproductrack.rack_id')
					->join('product','product.id','=','ext_stockreportproduct.product_id')
					->where('ext_stockreportproductrack.rack_id',$rack_id)
					->where('ext_stockreport.location_id',$location_id)
					->groupBy('ext_stockreportproduct.product_id')
					->select('ext_stockreport.*', 'ext_stockreportproduct.*', 'ext_stockreportproductrack.*', 'rack.*',
					'product.name','product.systemid as prsystemid','product.prdcategory_id as prdcategory_id', 'product.thumbnail_1')
					->get();
			foreach ($rack_product as $key => $value) {
				$rack_product_qty = ExtStockReport::
					join('ext_stockreportproduct','ext_stockreport.id','=','ext_stockreportproduct.ext_stockreport_id')
				  ->join('ext_stockreportproductrack','ext_stockreportproductrack.ext_stockreportproduct_id','=','ext_stockreportproduct.id')
				  ->where('ext_stockreportproduct.product_id',$value->product_id)
				  ->where('ext_stockreportproductrack.rack_id',$rack_id)
				  ->where('ext_stockreport.location_id',$location_id)  
				  ->sum('ext_stockreportproduct.quantity');
					$rack_product[$key]->quantity = $rack_product_qty;  
			}
			  
		  } else {

				 $rack_product = StockReport::
					join('stockreportproduct','stockreport.id','=','stockreportproduct.stockreport_id')
					->join('stockreportproductrack','stockreportproductrack.stockreportproduct_id','=','stockreportproduct.id')
					->join('rack','rack.id','=','stockreportproductrack.rack_id')
					->join('product','product.id','=','stockreportproduct.product_id')
					->where('stockreportproductrack.rack_id',$rack_id)
					->where('stockreport.location_id',$location_id)
					->where('stockreport.type','stockin')
					->groupBy('stockreportproduct.barcode')
					->select('stockreport.*', 'stockreportproduct.*', 'stockreportproductrack.*', 'rack.*',
						'product.name','product.systemid as prsystemid',
						'product.prdcategory_id as prdcategory_id', 'product.thumbnail_1')
					->get();
				 $rack_product = $rack_product->unique('barcode');
				 
			foreach ($rack_product as $key => $value) {
				$rack_product_qty = StockReport::
					join('stockreportproduct','stockreport.id','=','stockreportproduct.stockreport_id')
					->join('stockreportproductrack','stockreportproductrack.stockreportproduct_id','=','stockreportproduct.id')
					->where('stockreportproduct.barcode',$value->barcode)
					->where('stockreportproductrack.rack_id',$rack_id)
					->where('stockreport.location_id',$location_id)  
					->sum('stockreportproduct.quantity');
				$rack_product[$key]->quantity = $rack_product_qty;  
			}
			$rack_product = $rack_product->filter(function($z) {
				return $z->quantity > 0;
			});
		 }
      }

      return Datatables::of($rack_product)->
			addIndexColumn()->
			addColumn('inven_pro_barcode', function ($memberList) {
				$product = product::where('systemid',
					$memberList->prsystemid)->first();

				$barcode = DNS1D::getBarcodePNG(trim($memberList->prsystemid),
					"C128");

				return (!is_null($barcode)) ? $memberList->barcode : '';
			})->
			addColumn('inven_pro_name', function ($memberList) {
				return '<img src="'.asset('images/product/'.
					$memberList->product_id.'/thumb/'.
					$memberList->thumbnail_1).
					'" style="height:40px;width:40px;object-fit:contain;margin-right:8px;">'.
					$memberList->name;
			})->
			addColumn('inven_pro_ownership', function ($memberList) use($type, $company) {
				$comp = 'Own';
				if($type == 'ext'){
					$comp = $company;
				}
				return $comp;
			 })->
			addColumn('inven_pro_qty', function ($memberList) use($type) {
				return '<a href=""data-toggle="modal" data-target="#qtyallocatedProducts"
					class="retail-voucher-list os-linkcolor" style="text-decoration:none;"
					 onclick="rack_product_ledger('.
					 $memberList->rack_id.','.$memberList->product_id.
					','.$memberList->rack_no.',\''.
					$memberList->type.'\''.
					','."'$memberList->barcode'".
					')">'.$memberList->quantity.'</a>';
			})->
			escapeColumns([])->
			make(true);
    }


    public function rack_product_ledger(Request $request)
    {
        $rack_product =array();
        $rack_id = $request->get('rack_id');
        $product_id = $request->get('product_id');
        $location_id = $request->get('location_id');
        $type = $request->get('type');
		$barcode = $request->get('barcode');
        if($rack_id) {
			if($type == 'ext'){
				$rack_product = ext_stockreportproduct::select('ext_stockreport.systemid','product.id as product_id','rack.id as rack_id','rack.rack_no','ext_stockreportproduct.quantity','ext_stockreport.type','ext_stockreportproduct.created_at as lastupdate')
                  ->join('ext_stockreportproductrack','ext_stockreportproductrack.ext_stockreportproduct_id','=','ext_stockreportproduct.id')
                  ->join('rack','rack.id','=','ext_stockreportproductrack.rack_id')
                  ->join('product','product.id','=','ext_stockreportproduct.product_id')
                  ->join('ext_stockreport','ext_stockreportproduct.ext_stockreport_id','=','ext_stockreport.id')
                  ->where('ext_stockreportproductrack.rack_id',$rack_id)
                  ->where('ext_stockreportproduct.product_id',$product_id)
                  ->where('ext_stockreport.location_id',$location_id)
                  ->get();				
			} else {
				$rack_product = stockreportproduct::select('stockreport.systemid',
					'product.id as product_id','rack.id as rack_id','rack.rack_no','stockreportproduct.quantity',
					'stockreport.type','stockreportproduct.created_at as lastupdate')
                  ->join('stockreportproductrack','stockreportproductrack.stockreportproduct_id','=','stockreportproduct.id')
                  ->join('rack','rack.id','=','stockreportproductrack.rack_id')
                  ->join('product','product.id','=','stockreportproduct.product_id')
                  ->join('stockreport','stockreportproduct.stockreport_id','=','stockreport.id')
                  ->where('stockreportproductrack.rack_id',$rack_id)
				  ->where('stockreportproduct.product_id',$product_id)
				  ->where('stockreportproduct.barcode',$barcode) 
                  ->where('stockreport.location_id',$location_id)
                  ->get();
			}
        }

      return Datatables::of($rack_product)->
                addIndexColumn()->
                addColumn('inven_report_id', function ($memberList) {
                    return $memberList->systemid;
                })->
                addColumn('inven_report_type', function ($memberList) {
                  if($memberList->type == 'stockin'){
                    return 'Stock In';
                  } else if($memberList->type == 'stockout') {
                    return 'Stock Out';
                  } else {
                    return $memberList->type;
                  }
                })->
                addColumn('inven_report_lastupdate', function ($memberList) {
                    return date('dMY',strtotime($memberList->lastupdate));
                })->
                addColumn('inven_report_qty', function ($memberList) {
                    return $memberList->quantity;
                })->
                escapeColumns([])->
                make(true);
    }

    public function addRack(Request $request)
    {   
        try {
          $location_id = $request->get('location_id');
          if($location_id) {
              $location = location::where('id',$location_id)->first(); 
              $warehouse = warehouse::where('location_id',$location_id)->first();
              if(!$warehouse) {
                $warehouse = new warehouse();
              }
              $warehouse->location_id = $location_id;
              $warehouse->name = $location->branch;
              $warehouse->save();
              
              $rack_system = DB::select("select nextval(rack_seq) as index_rack");
              $rack_system_id = $rack_system[0]->index_rack;
              $rack_system_id = sprintf("%010s",$rack_system_id);
              $rack_system_id = '113'.$rack_system_id;

              $rack_no = rack::where('warehouse_id',$warehouse->id)->count();


              $rack = new rack();
              $rack->systemid = $rack_system_id; 
              $rack->rack_no = $rack_no + 1; 
              $rack->warehouse_id = $warehouse->id; 
              $rack->save(); 
          }
          $msg = "Rack added successfully";
          return view('layouts.dialog', compact('msg'));

        } catch (\Exception $e) {
            $msg = $e;//"Some error occured";
            return view('layouts.dialog', compact('msg'));
        }
    }


    public function removeRack(Request $request)
    {   
        try {
          $location_id = $request->get('location_id');
          if($location_id) {
              $warehouse = warehouse::where('location_id',$location_id)->first();
              $rack = rack::where('warehouse_id',$warehouse->id)->orderby('id','DESC')->first();
	      
	      if (empty($rack)) {
		      throw new \Exception("No rack available to delete");
	      }

              $rack_check = stockreportproductrack::
                  join('stockreportproduct','stockreportproductrack.stockreportproduct_id','=','stockreportproduct.id')
                  ->where('stockreportproductrack.rack_id',$rack->id)
                  ->count();
              $barcode_rack = productbarcodelocation::where('rack_id',$rack->id)->count();
              $total_rack = $rack_check + $barcode_rack;
              log::debug('total_rack'.$total_rack);
              if($total_rack <= 0) {
                rack::where('id', $rack->id)->delete();
                $msg = "Rack removed successfully";
              } else {
                $msg = "Rack cannot be removed.";
              }
          }

          return view('layouts.dialog', compact('msg'));

        } catch (\Exception $e) {
            $msg = $e->getMessage();//"Some error occured";
            return view('layouts.dialog', compact('msg'));
        }
    }

    public function updateOnewayStatus(Request $request){
		try{
			$oneway_id = $request->get('oneway_id');
			$oneway_status = $request->get('oneway_status');
			$newstatus = 'active';
			if($oneway_status == 'active'){
				$newstatus = 'inactive';
			}
			Oneway::where('id',$oneway_id)->update(['status'=>$newstatus]);
			$msg = "Oneway saved successfully";
		} catch (\Exception $e) {
            $msg = "Oneway occured while Saving Remark";
        }
		return $msg;		
	}
	
    public function updateMlinkStatus(Request $request){
		try{
			$mlink_id = $request->get('mlink_id');
			$mlink_status = $request->get('mlink_status');
			$newstatus = 'active';
			if($mlink_status == 'active'){
				$newstatus = 'inactive';
			}
			MerchantLink::where('id',$mlink_id)->update(['status'=>$newstatus]);
			$msg = "Mlink saved successfully";
		} catch (\Exception $e) {
            $msg = "Mlink occured while Saving Remark";
        }
		return $msg;
	}
	
    public function updateRemark(Request $request)
    {
        try{
            $id = Auth::user()->id;

            $rack_id = $request->get('rack_id');
            $item_remark = $request->get('item_remark');
            $rack = rack::where('id',$rack_id)->first();
            $rack->remarks = $item_remark;                
            $rack->save();

            $msg = "Remark saved successfully";
            $data = view('layouts.dialog', compact('msg'));
        } catch (\Exception $e) {
            {
                $msg = "Error occured while Saving Remark";
            }
            Log::error(
                "Error @ " . $e->getLine() . " file " . $e->getFile() .
                ":" . $e->getMessage()
            );
            $data = view('layouts.dialog', compact('msg'));
        }
        return $data;
    }

    public function updateRackNo(Request $request)
    {
        try{
            $id = Auth::user()->id;

            $rack_id = $request->get('item_id');
            $rack_no = $request->get('rack_no');
            $rack = rack::where('id',$rack_id)->first();
            $rack->rack_no = $rack_no;                
            $rack->save();

            $msg = "Rack Number saved successfully";
            $data = view('layouts.dialog', compact('msg'));
        } catch (\Exception $e) {
            {
                $msg = "Error occured while Saving Rack Number";
            }
            Log::error(
                "Error @ " . $e->getLine() . " file " . $e->getFile() .
                ":" . $e->getMessage()
            );
            $data = view('layouts.dialog', compact('msg'));
        }
        return $data;
    }

    public function updateRackDesc(Request $request)
    {
        try{
            $id = Auth::user()->id;

            $rack_id = $request->get('rack_id');
            $item_desc = $request->get('item_desc');
            $rack = rack::where('id',$rack_id)->first();
            $rack->description = $item_desc;                
            $rack->save();

            $msg = "Description saved successfully";
            $data = view('layouts.dialog', compact('msg'));
        } catch (\Exception $e) {
            {
                $msg = "Error occured while Saving description";
            }
            Log::error(
                "Error @ " . $e->getLine() . " file " . $e->getFile() .
                ":" . $e->getMessage()
            );
            $data = view('layouts.dialog', compact('msg'));
        }
        return $data;
    }

   	public function showtallyChartView() {
       return view('warehouse.tallychart');
   	}

    public function showpickUpControlView() {
    	return view('warehouse.pickupcontrol');
    }

    public function showdeliveryControlView() {
         return view('warehouse.deliverycontrol');
    }

    public function showdeliveryManView() {
		$user_data  = new UserData();

		$userApprovedDate = DB::table('company')->find($user_data->company_id());
		$userApprovedDate = !empty($userApprovedDate) ? date("Y-m-d", strtotime($userApprovedDate->approved_at)) :
			date("Y-m-d");

		$months = [];
		for($x = 1; $x  <=12; $x++ ) {
			$date = "2020-$x-$x";
			$months[] = date('M',strtotime($date));
		}

        return view('warehouse.deliveryman', compact('months', 'userApprovedDate'));
   }
  
	public function showdeliveryManTable(Request $request) {

		try {
		
			$user_data = new UserData();

			$deliverman = DB::table('users')->
				select("users.*","staff.systemid")->
				join('staff','staff.user_id','=','users.id')->
				join('usersfunction','usersfunction.user_id','=','users.id')->
				join('function','function.id','=','usersfunction.function_id')->
				where([
					'staff.company_id'	=> $user_data->company_id(),
					'function.slug'		=> 'dlv'
				])->
				get();

			$deliverman->map(function($d_man) use ($request) {
				$task = DB::table('deliveryorder')->
						where('deliveryorder.deliveryman_user_id', $d_man->id);

					if (!empty($request->date)) {
						$year = date("Y", strtotime($request->date));
						$month = date("m", strtotime($request->date));
						$task = $task->whereMonth('created_at', $month)->
							whereYear('created_at', $year)->get();
					} else {
						$task = $task->get();
					}

				$task = $task->filter(function($f) {
					return !empty(DB::table('deliveryorderproduct')->
						where('deliveryorder_id', $f->id)->first());
				});

				$d_man->assigned = $task->count();
				$d_man->completed = $task->where('status','completed')->count();
				$d_man->outstanding = $d_man->assigned - $d_man->completed;
			});
		

			return Datatables::of($deliverman)->
				addIndexColumn()->

				addColumn('systemid',function($data) {
					return $data->systemid;
				})->

				addColumn('name',function($data) {
					return $data->name;
				})->


				addColumn('numberPlate',function($data) {
					return "JK097474";
				})->
				
				
				addColumn('assigned',function($data) {
					return <<<EOD
					<a href="javascript:void(0)" onclick="assigned_modal($data->id)" 
						style="text-decoration:none;" class="btn-link">$data->assigned
					</a>
EOD;
				})-> 


				addColumn('completed',function($data) {
					return $data->completed;
				})-> 


				addColumn('outstanding',function($data) {
					return <<<EOD
					<a href="javascript:void(0)" onclick="outstanding_modal($data->id)"
						style="text-decoration:none;" class="btn-link">$data->outstanding
					</a>
EOD;
				})-> 

				addColumn('cog',function($data) {
					return <<<EOD
						<a href="javascript:void(0)" class="btn-link">
							<i class="fa fa-cog"></i>
						</a>
EOD;
				})->

				addColumn('docs',function($data) {
					return <<<EOD
					<img style="width:25px;height:25px;cursor:pointer"
						src="/images/pinkcrab_50x50.png">
EOD;
				})->

				escapeColumns([])->
				make(true);

		} catch (\Exception $e) {
	
			\Log::info([
				"Error" => $e->getMessage(),
				"Line" 	=> $e->getLine(),
				"File" 	=> $e->getFile()
			]);
			
			abort(404);
		}


	}

	public function deliverymanAssignedTask(Request $request) {
		try {

			$dman_id = $request->dman_id;

			$task = DB::table('deliveryorder')->
				where('deliveryorder.deliveryman_user_id', $dman_id);

			if (!empty($request->type)) {
				if ($request->type == 'outstanding')
					$task = $task->whereNotIn('status',['completed']);	
				elseif ($request->type == 'completed')
					$task = $task->whereIn('status',['completed']);	
			}

			if (!empty($request->date)) {
				$year = date("Y", strtotime($request->date));
				$month = date("m", strtotime($request->date));
				$task = $task->whereMonth('created_at', $month)->
					whereYear('created_at', $year)->get();
			} else {
				$task = $task->get();
			}
			
			$task = $task->filter(function($f) {
				return !empty(DB::table('deliveryorderproduct')->
					where('deliveryorder_id', $f->id)->first());
			});

			return Datatables::of($task)->
				addIndexColumn()->
				addColumn('document',function($data) {
					$url = route('delieveryorderbyid.index', ['do', $data->systemid]);
					return <<< EOD
					<span class="os-linkcolor" style="cursor:pointer;"
						onclick="openNewTabURL('$url')">$data->systemid</span>
EOD;
				})->
				addColumn('date',function($data) {
					return date("dMY", strtotime($data->created_at));
				})->
				addColumn('from',function($data) {
					return DB::table('location')->find($data->issuer_location_id)->branch ?? '';
				})->
				addColumn('to',function($data) {
					return DB::table('location')->find($data->receiver_location_id)->branch ?? '';
				})->
				addColumn('status',function($data) {
					return ucfirst($data->status);
				})->
				addColumn('verified_by',function($data) {
					return DB::table('users')->find($data->completed_by_user_id)->name ?? '';
				})->
				escapeColumns([])->
				make(true);

		} catch (\Exception $e) {
	
			\Log::info([
				"Error" => $e->getMessage(),
				"Line" 	=> $e->getLine(),
				"File" 	=> $e->getFile()
			]);
			
			abort(404);
		}

	}

	public function showWarehouseBarcode($id)
	{
		$system_id = $id;
		$product = product::where('systemid', $system_id)->first();
		$product_id = $product->id;
		$product_qty = app('App\Http\Controllers\InventoryController')->check_quantity($product->id);
		$barcodematrix = SettingBarcodeMatrix::where('category_id', $product->prdcategory_id)->first();
		
		$barcode_sku = productbarcode::where('product_id', $product->id)->first();
		
		$barcode = DNS1D::getBarcodePNG(trim($system_id), "C128");
		
		$id = Auth::user()->id;
		$user_roles = usersrole::where('user_id', $id)->get();
		
		$is_king = \App\Models\Company::where('owner_user_id',
			Auth::user()->id)->first();
		
		$showbuttons = false;
		
		if ($is_king != null) {
			$is_king = true;
		} else {
			$is_king = false;
		}
		
		return view('inventory.inventorybarcode',
			compact('user_roles', 'is_king', 'barcode', 'product_id', 'system_id', 'product', 'barcodematrix', 'product_qty', 'barcode_sku', 'showbuttons'));
	}   
}
