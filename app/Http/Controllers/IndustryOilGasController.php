<?php

namespace App\Http\Controllers;

use \App\Http\Controllers\OposPetrolStationPumpController;
use App\Models\OgFuelMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Matrix\Exception;
use Yajra\DataTables\DataTables;
use \App\Classes\SystemID;
use \App\Classes\UserData;
use \App\Models\merchantproduct;
use \App\Models\product;
use \App\Models\oilgas;
use \App\Models\prd_inventory;
use App\Models\OgFuel;
use App\Models\OgTank;
use App\Models\OgFuelPrice;
use App\Models\usersrole;
use App\Models\role;
use App\Models\productcolor;
use \App\Models\StockReport;
use \App\Models\rackproduct;
use \App\Models\stockreportproduct;
use \App\Models\stockreportproductrack;
use \App\Models\opos_wastageproduct;
use \App\Models\opos_receiptproduct;
use \App\Models\Company;
use \App\Models\location;
use Illuminate\Support\Facades\Auth;
use \App\Models\locationproduct;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use \App\Models\FranchiseMerchantLocTerm;
use Log;
use \App\Http\Controllers\AnalyticsController;
use \App\Http\Controllers\InventoryController;
use \App\Http\Controllers\APIFranchiseController;
use \App\Models\Staff;
use \App\User;
use \App\Models\globaldata;


class IndustryOilGasController extends Controller
{

    public function __construct()
	{
		$this->middleware('auth');
    }

    public function index()
    {
        $this->user_data = new UserData();

        $model = new OgFuel();

        $ids = merchantproduct::where('merchant_id',
			$this->user_data->company_id())->
            pluck('product_id');
	
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
	
        $data = $model->whereIn('prd_ogfuel.product_id', $ids)->
			leftjoin('productcolor','productcolor.product_id','=','prd_ogfuel.product_id')->
			leftjoin('color','color.id','=','productcolor.color_id')->
			select('prd_ogfuel.*','color.hex_code as color')
            ->orderBy('created_at', 'asc')->get();
		
		foreach ($data as $key => $value) {
			$data[$key]['transaction'] = $this->check_transaction($value->product_id);
            $data[$key]['price'] = $this->get_execute_price($value->id);
            $data[$key]['total'] = $this->getTotal($value->product_id);
            $data[$key]['cash_sale'] = $this->getCashSale($value->product_id);
		}
	
		$data->map(function ($z) use ($franchise_p_id) {
			$franchise_product = $franchise_p_id->
				firstWhere('product_id',$z->product_id);

			if (!empty($franchise_product)) {
				$z->franchise_product  	= true;
			}
		});

		$inventoryController = new InventoryController();

        return Datatables::of($data)
            ->addIndexColumn()
            ->addColumn('og_product_id', function ($oilgaslist) {
				return '<p data-field="og_product_id" style="margin: 0; text-align: center;"'.
					'class="doc_id" url="' .route("inventory.showstockreport",$oilgaslist->product_name->systemid) .
					'">' . $oilgaslist->product_name->systemid .'</p>';
            })
            ->addColumn('og_product_name', function ($oilgaslist) {
                if (!empty($oilgaslist->product_name->thumbnail_1)) {
                    $img_src = '/images/product/' . $oilgaslist->product_name->id . '/thumb/' . $oilgaslist->product_name->thumbnail_1;
					$img     = "<img src='$img_src' data-field='og_product_name' style=' width: 25px;height: 25px;
					display: inline-block;margin-right: 8px;object-fit:contain;'/>";
                } else {
                    $img = "";
                }
	
				return  !empty($oilgaslist->franchise_product) ? $img.$oilgaslist->product_name->name:
					$img . '<p class="os-linkcolor" data-field="inven_pro_name" 
					style="cursor: pointer; margin: 0;display:inline-block" onclick="details(' . 
					$oilgaslist->product_name->systemid . ')">' .
					(!empty($oilgaslist->product_name->name) ? $oilgaslist->product_name->name : 
					'Product Name') . '</p>';
			})
			->addColumn('og_litre', function ($oilgaslist) use ($inventoryController) {
				$qty = $inventoryController->check_quantity($oilgaslist->product_name->id);
                return '<p class="buyOutput" style="text-align: center;margin: 0;"  data-field="og_litre">
					<a href="/industry/oil-gas/product-ledger/' . $oilgaslist->product_id . 
					'" target="_blank"  style="text-decoration:none;text-align: right;">'
               			.number_format($qty,2).
                    '</a></p>';
					//(!empty($oilgaslist->total) ?
                      //  number_format(($oilgaslist->total - $oilgaslist->cash_sale),2) :
            })
            ->addColumn('og_price', function ($oilgaslist) {
				return !empty($oilgaslist->franchise_product) ? '<p style="text-align:right;margin:0">'.
					number_format(($oilgaslist->price / 100), 2).'</p>': 
				'<p class="os-linkcolor getOutput" 
				    style="text-align: right;margin: 0;" data-field="og_price">
					<a href="/industry/oil-gas/fuel-prices/' . 

				$oilgaslist->id . '" target="_blank" style="text-decoration:none;text-align:
					right;">'.(!empty($oilgaslist->price) ?
					number_format(($oilgaslist->price/100),2): '0.00').'</a></p>';
            })

           ->addColumn('og_price_wholesale', function ($oilgaslist) {

					$price = DB::table('prd_ogfuel')->
						where('product_id', $oilgaslist->product_id)->
						first();

				return !empty($oilgaslist->franchise_product) ? '<p style="text-align:right;margin:0">'.
					number_format(($price->wholesale_price / 100), 2).'</p>':  '<p style="text-align:right;margin:0;cursor:pointer;"
						class="os-linkcolor" onclick="wholesalepriceModal(\''.$oilgaslist->product_id.'\', \''.number_format(($price->wholesale_price / 100), 2).'\')">'.
					number_format(($price->wholesale_price / 100), 2).'</p>';          
		   
		   })

            ->addColumn('og_loyalty', function ($oilgaslist) {
                if($oilgaslist->loyalty == '' || $oilgaslist->loyalty == null){
                    $oilgaslist->loyalty = 0;
                }

				if (!empty($oilgaslist->franchise_product)) {
					return $oilgaslist->loyalty;
				}
                return '<p class="os-linkcolor getOutput" style="cursor: pointer; margin: 0; text-align: center;" data-toggle="modal"  data-target="#loyaltyUpdateModal'.$oilgaslist->id.'" style="text-align: right;margin: 0;" data-field="og_loyalty">' . $oilgaslist->loyalty . '</p>'.
                //modal pop up for the loyalty definition form
                '<div class="modal fade" id="loyaltyUpdateModal'.$oilgaslist->id.'" onblur="update_loyalty('.$oilgaslist->product_id.','.$oilgaslist->id.','.$oilgaslist->loyalty.')" tabindex="-1" role="dialog" aria-labelledby="LoyaltyUpdate" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-sm">
                <div class="modal-content">
                    <!-- Modal body -->
                    <div class="modal-body">
                        <input tabindex="-1" style="text-align: center; " id="fuel_product_loyalty'.$oilgaslist->id.'" min="0" type="number" class="pl-1 form-control" value="'.$oilgaslist->loyalty.'"  style="width: 100%; border: 1px solid #ddd;">
                    </div>
                </div>
                </div>
            </div>';
            })
			->addColumn('og_color', function($oilgaslist) {
				$color = $oilgaslist->color ?? "#fff";
				$html =  <<<EOD
				<div style="padding:1.2em;
					background:$color;
					border:1px solid #ccc;cursor:pointer" 
					onclick="select_colorModal($oilgaslist->product_id)">
				</div>
EOD;
				return $html;
			})
			->addColumn('deleted', function ($oilgaslist) {

			if ( !empty($oilgaslist->franchise_product) ) {
				return '';
			}
				if ($oilgaslist->transaction == 'True') {
					// Disabled redcrab
					return '<div><img class="" src="/images/redcrab_50x50.png"
						style="width:25px;height:25px;cursor:not-allowed;
						filter:grayscale(100%) brightness(200%)"/>
						</div>';

				} else {
					// Regular redcrab
					return '<div data-field="deleted" class="remove">
						<img src="/images/redcrab_50x50.png"
						style="width:25px;height:25px;cursor:pointer"/>
						</div>';
				}
			})->
			escapeColumns([])->
			make(true);
    }

	public	function fuelWholesalePriceUpdate(Request $request) {
		try {
		
			$validation = Validator::make($request->all(), [
				"product_id"	=>	'required',
				'price'			=>	'required'
			]);

			if ($validation->fails())
				throw new \Exception("validation_failed");

			DB::table('prd_ogfuel')->
				where('product_id', $request->product_id)->
				update(['wholesale_price'	=>	$request->price]);

			$msg = "Wholesale price updated";
			return view('layouts/dialog', compact('msg'));
		} catch (\Exception $e) {
			\Log::info([
				"Error"	=>	$e->getMessage(),
				"File"	=>	$e->getFile(),
				'Line'	=>	$e->getLine()
			]);
			abort(404);
		}
	}

	public function ogProductUpdateColor(Request $request) {
		try {
	
			$allInputs = $request->all();
			$validation = Validator::make($allInputs, [
				'ogproduct_id' => 'required',
				'color' => 'required',
			]);
			
			if ($validation->fails()) {
				throw new Exception("Validation Error", 1);
			}
			
			$findProductColor = DB::table('productcolor')->
				where('product_id', $request->ogproduct_id)->
				first();
			
			if (!empty($findProductColor)) {
				DB::table('color')->
					where('id', $findProductColor->color_id)->
					update(['hex_code' => $request->color]);
			} else {
				$init_color = DB::table('color')->insertGetId([
					'name'			=> 'oilProduct',
					'description' 	=> '',
					'rgb_code'		=> '',
					'hex_code'		=> $request->color,
					'created_at'	=> date('Y-m-d H:i:s'),
					'updated_at' 	=> date('Y-m-d H:i:s')
				]);

				DB::table('productcolor')->insert([
					'product_id'	=> $request->ogproduct_id,
					"color_id"		=> $init_color,
					'created_at'	=> date('Y-m-d H:i:s'),
					'updated_at' 	=> date('Y-m-d H:i:s')
				]);
			}

			$msg = "Colour updated";
			return view('layouts/dialog', compact('msg'));
		} catch (\Exception $e) {
			\Log::info([
				"Error"	=> 	$e->getMessage(),
				"File"	=>	$e->getFile(),
				"Line"	=>	$e->getLine()
			]);
			abort(404);
		}
	}

    public function updateLoyalty(Request $request){
        // print_r($request->all());
        // echo $request['product_id'];
        $product_id = $request['product_id'];
        // echo $product_id;
        $new_loyalty_value = $request['new_loyalty_value'];
        // echo $new_loyalty_value;
        $update = Ogfuel::where('product_id', $product_id)->update(['loyalty' => $new_loyalty_value]);
        if($update){
            return 200;
        }else{
            return 'failed';
        }
    }

    public function getTotal($product_id) {
        return stockreportproduct::where('product_id', $product_id)->sum('quantity');
        /* $stockreports = stockreportproduct::where('product_id', $product_id)->get();
         $total = 0;
         foreach ($stockreports as $key => $value) {
             $total = $total + $value->quantity;
         }
         return $total;*/
    }

    protected function getCashSale($product_id){
       return opos_receiptproduct::where('product_id', $product_id)->sum('quantity');
    }

	public function showTankMonitoring(Request $request) {
		try {
		
        	$this->user_data = new UserData();
        	$merchant_id = $this->user_data->company_id();
		
		
			$is_franchise = FranchiseMerchantLocTerm::select('company.name',
					'franchisemerchantloc.location_id','company.systemid','company.id')->
				join('franchisemerchantloc', 'franchisemerchantloc.id', '=',
					'franchisemerchantlocterm.franchisemerchantloc_id')->
				join('franchisemerchant','franchisemerchant.id','=',
					'franchisemerchantloc.franchisemerchant_id')->	
				join('franchise','franchise.id','=', 'franchisemerchant.franchise_id')->
				join('company', 'franchise.owner_merchant_id', '=','company.id')->			
				where(
					["franchisemerchant.franchisee_merchant_id" => $merchant_id]
				)->get();
			
			$f_locations = $is_franchise->pluck('location_id')->unique();
			
			$locations = DB::table('merchantlocation')->
				where('merchant_id',$merchant_id)->
				whereNull('deleted_at')->
				get()->pluck('location_id');
			
			$locations = $locations->merge($f_locations);

			$model = OgTank:://leftjoin('staff','og_tank.user_id','=','staff.user_id')->
				leftjoin('company','company.id','=','og_tank.franchise_merchant_id')->
				leftjoin('location','location.id','=','og_tank.location_id')->
				leftjoin('productcolor','productcolor.product_id','=','og_tank.product_id')->
				leftjoin('color','color.id','=','productcolor.color_id')->
				where([
					['og_tank.tank_no', '!=',0],
					['og_tank.height', '!=',0],
				//	['og_tank.franchise_merchant_id', '=', $merchant_id]
				])->
				whereIn('og_tank.location_id',$locations)->
				whereNull('og_tank.deleted_at')->
				whereNotNull('og_tank.product_id')->
				orderBy('created_at','desc')->
				select('og_tank.*','company.id as company_id', 'company.name as owner_company_name','location.id as location_id',
					'company.systemid as owner_systemid','location.systemid as loc_systemid', 'color.hex_code as color')->
				get();

				$model = $model->filter(function ($z) use  ($merchant_id, $is_franchise) {
				$location_info = $is_franchise->where('location_id',$z->location_id)->first();
				
				if (!empty($location_info)) {
					//own location
					if ($merchant_id == $location_info->id) {
						return true;
					}

					//if not own location
					if ($merchant_id == $z->company_id) { 
						return true;
					}

					//other records
					return false;
				}
					return true;
				});


            return Datatables::of($model)
                ->addIndexColumn()
				->addColumn('tank_no', function ($data) {
					return $data->tank_no;
				})->
				addColumn('status', function ($data) {
					return 'Status';
				})->
				addColumn('product', function ($data) {
					if (!empty($data->product_name->thumbnail_1)) {
                        $img_src = '/images/product/' . $data->product_name->id . '/thumb/' . $data->product_name->thumbnail_1;
                        $prod_img_id = $data->product_name->id;
						$img = "<img id='img-product-thumb-' src='$img_src' data-field='og_product_name' 
							style=' width: 25px;height: 25px;display: inline-block
							;margin-right: 8px;float:left;object-fit:contain;'/>";
                    } 
					$img = $img ?? '';
					$img_src = $img_src ?? '';
					$product_name = $data->product_name->name ?? "Product Name";
					$product_systemid = $data->product_name->systemid ?? "Product SystemId";
					$max_cap = $data->max_capacity ?? 0;

					return  <<<EOD
						$img <p style="text-align: left;margin-bottom: 0px;cursor:pointer" 
							class="os-linkcolor widgetClick" 
							onclick="update_product_widget('$product_name', '$product_systemid','$img_src','$max_cap')" >$product_name</p>
EOD;

				})->
				addColumn('js',function($data) {
				
					if (!empty($data->product_name->thumbnail_1)) {
                        $img_src = '/images/product/' . $data->product_name->id . '/thumb/' . $data->product_name->thumbnail_1;
					}
					$img_src = $img_src ?? '';
					$product_name = $data->product_name->name ?? "Product Name";
					$product_systemid = $data->product_name->systemid ?? "Product SystemId";
					$max_cap = $data->max_capacity ?? 0;

					return "$product_name;$product_systemid;$img_src;$max_cap";
				})->
				addColumn('tank_filling', function ($data) {
					return 0;
				})->
				addColumn('product_mm', function ($data) {
					return 0;
				})->
				addColumn('water_mm', function ($data) {
					return 0;
				})->
				addColumn('temperature', function ($data) {
					return 0;	
				})->
				addColumn('product_l', function ($data) {
					return 0;
				})->
				addColumn('water_l', function ($data) {
					return 0;
				})->
				addColumn('ullage_l', function ($data) {
					return 0;
				})->
				addColumn('tc', function ($data) {
					return 0;
				})->
				setRowClass(function ($data) {
					return "clickme";
				})->
                escapeColumns([])->
                make(true);

		} catch (\Exception $e) {
			\Log::info([
				"Error"	=>	$e->getMessage(),
				"Line"	=>	$e->getLine(),
				"File"	=>	$e->getFile()
			]);
			abort(404);
		}	
	}


    public function showTankManagement(Request $request)
    {
        $this->user_data = new UserData();

        $merchant_id = $this->user_data->company_id();
		$location_ID = $request->locID;

		if(!empty($request->locID)) {	
			$is_franchise = FranchiseMerchantLocTerm::select('company.name','company.systemid','company.id')->
				join('franchisemerchantloc', 'franchisemerchantloc.id', '=',
					'franchisemerchantlocterm.franchisemerchantloc_id')->
				join('franchisemerchant','franchisemerchant.id','=',
					'franchisemerchantloc.franchisemerchant_id')->	
				join('franchise','franchise.id','=', 'franchisemerchant.franchise_id')->
				join('company', 'franchise.owner_merchant_id', '=','company.id')->			
				where(
					['franchisemerchantloc.location_id' => $location_ID],
					["franchisemerchant.franchisee_merchant_id" => $this->user_data->company_id()]
				)->
				first();
		
			$model = OgTank::
				//leftjoin('staff','og_tank.user_id','=','staff.user_id')->
					leftjoin('company','company.id','=','og_tank.franchise_merchant_id')->
					leftjoin('location','location.id','=','og_tank.location_id')->
					where(['og_tank.location_id' => $location_ID])->
					leftjoin('productcolor','productcolor.product_id','=','og_tank.product_id')->
					leftjoin('color','color.id','=','productcolor.color_id')->
					whereNull('og_tank.deleted_at')->
					orderBy('created_at','desc')->
					//whereIn('og_tank.location_id',$locations)->
					select('og_tank.*','company.id as company_id','company.name as owner_company_name',
						'company.systemid as owner_systemid',
						'location.systemid as loc_systemid', 'color.hex_code as color')->
					get();

				
				if ($request->franchise_view == 'true') {
				
					if ($this->user_data->company_id() != $is_franchise->id) {
						$model = $model->whereIn('company_id', [$this->user_data->company_id()]);
					}

			} else {
				$model = $model->where('company_id', $this->user_data->company_id());
			}

		} else {
		
			$is_franchise = FranchiseMerchantLocTerm::select('company.name',
					'franchisemerchantloc.location_id','company.systemid','company.id')->
				join('franchisemerchantloc', 'franchisemerchantloc.id', '=',
					'franchisemerchantlocterm.franchisemerchantloc_id')->
				join('franchisemerchant','franchisemerchant.id','=',
					'franchisemerchantloc.franchisemerchant_id')->	
				join('franchise','franchise.id','=', 'franchisemerchant.franchise_id')->
				join('company', 'franchise.owner_merchant_id', '=','company.id')->			
				where(
					["franchisemerchant.franchisee_merchant_id" => $this->user_data->company_id()]
				)->get();
			
			$f_locations = $is_franchise->pluck('location_id')->unique();
			
			$locations = DB::table('merchantlocation')->
				where('merchant_id',$merchant_id)->
				whereNull('deleted_at')->
				get()->pluck('location_id');
			
			$locations = $locations->merge($f_locations);

			$model = OgTank::
				//leftjoin('staff','og_tank.user_id','=','staff.user_id')->
				leftjoin('company','company.id','=','og_tank.franchise_merchant_id')->
				leftjoin('location','location.id','=','og_tank.location_id')->
				leftjoin('productcolor','productcolor.product_id','=','og_tank.product_id')->
				leftjoin('color','color.id','=','productcolor.color_id')->
			//	where('company.id', $merchant_id)->
				whereIn('og_tank.location_id',$locations)->
				whereNull('og_tank.deleted_at')->
				orderBy('created_at','desc')->
				select('og_tank.*','company.id as company_id', 'company.name as owner_company_name','location.id as location_id',
					'company.systemid as owner_systemid','location.systemid as loc_systemid', 'color.hex_code as color')->
				get();

			$model = $model->filter(function ($z) use  ($merchant_id, $is_franchise) {
				$location_info = $is_franchise->where('location_id',$z->location_id)->first();
				
				if (!empty($location_info)) {
					//own location
					if ($merchant_id == $location_info->id) {
						return true;
					}

					//if not own location
					if ($merchant_id == $z->company_id) { //|| $z->company_id == $location_info->id ) {
						return true;
					}

					//other records
					return false;
				}
				
				return true;
			});
		}	


		$model = $model->filter( function($f) use ($request, $merchant_id) {

			if (!$request->has('t_id'))	{
				if ($f->tank_no == 0 || 
					empty($f->product_id) || $f->height == 0) {
						return false;
					}	
			}

			if ($request->franchise_view == 'false') {
					
				if ($f->type == 'direct') {

					if ($merchant_id == $f->company_id) { 
						return true;
					}
				}

			} else {
			
				if ($f->type != 'direct') {
						return true;
				}
			}

		});
		

		$model->map(function ($f) use ($merchant_id, $request) {
			
			$is_franchise = FranchiseMerchantLocTerm::select('franchise.name as fname','franchise.systemid as fsystemid',
					'company.name','company.systemid','company.id')->
				join('franchisemerchantloc', 'franchisemerchantloc.id', '=',
					'franchisemerchantlocterm.franchisemerchantloc_id')->
				join('franchisemerchant','franchisemerchant.id','=',
					'franchisemerchantloc.franchisemerchant_id')->	
				join('franchise','franchise.id','=', 'franchisemerchant.franchise_id')->
				join('company', 'franchise.owner_merchant_id', '=','company.id')->			
				where(
					[
						'franchisemerchantloc.location_id' => $f->location_id,
				//		"franchisemerchant.franchisee_merchant_id" => $this->user_data->company_id()
					]
				)->
				first();
			
			if ($f->type != 'direct') {
				
				$f->fr = $f->type;
				$f->fr_detail = $is_franchise;

			} else {
				$f->fr = $f->type;
			}

			if (!empty($request->t_id)) {
					$f->read_only = false;	
			} else {
					$f->read_only = true;	
			}


		});
		
		return Datatables::of($model)
			->addIndexColumn()
			->addColumn('tank_no', function ($tanklist)  use ($is_franchise) {
				if ($tanklist->read_only == true) {
					return $tanklist->tank_no;
				}
				
				return '<p class="os-linkcolor" id="tank_no_' . $tanklist->id . '" onclick="storeTankID(' . 
					$tanklist->id . ',' . $tanklist->tank_no . ')" style="cursor: pointer; margin: 0; 
				text-align: center;">' . $tanklist->tank_no . '</p>';
			})
			->addColumn('tank_id', function ($tanklist) {
				if ($tanklist->read_only == true) {
					return $tanklist->systemid;
				}
				return '<p style=" margin: 0; text-align: center;">' . $tanklist->systemid . '</p>';
			})
			->addColumn('product', function ($tanklist) use ($is_franchise) {

				if (!empty($tanklist->product_name->thumbnail_1)) {
					$img_src = '/images/product/' . $tanklist->product_name->id . '/thumb/' . $tanklist->product_name->thumbnail_1;
					$prod_img_id = $tanklist->product_name->id;
					//class='img-product-thumb-$prod_img_id'
					$img = "<img id='img-product-thumb-' src='$img_src' data-field='og_product_name' 
						style=' width: 25px;height: 25px;display: inline-block
						;margin-right: 8px;float:left;object-fit:contain;'/>";
				} else {
					$img = "";
				}

				return $img .( $tanklist->read_only == true ?'<p style="text-align: left;margin-bottom: 0px;">' . 
				(	$tanklist->product_name->name ?? "Product Name")."</p>": 
					
				'<p class="os-linkcolor" id="tank_product_' . $tanklist->id . '" style="cursor: pointer;  
				text-align: left;margin-bottom: 0px;" onclick="showProducts(' . $tanklist->id . ')"  >' .
					(!empty($tanklist->product_id) ? $tanklist->product_name->name : 'Product Name') . '</p>');
				
			})
			->addColumn('height', function ($tanklist) use ($is_franchise) {
				
				if ($tanklist->read_only == true) {
					return  (!empty($tanklist->height) ? number_format($tanklist->height) : '0');
				}

				return '<p id="tank_height_' . $tanklist->id . '" class="os-linkcolor" data-field="' . 
					$tanklist->id . '"  onclick="showogFuelHeightModel(' . $tanklist->id . ',' . $tanklist->systemid . 
					')"  style="cursor: pointer; margin: 0;float:right; text-align:center;" >' . (!empty($tanklist->height) ? 
					number_format($tanklist->height) : '0') . '</p>';
			})
			->addColumn('atg', function ($tanklist) {
				$color = $tanklist->color ?? '#fff';
				$width = 0;
				$max = !empty($tanklist->max_capacity) ? $tanklist->max_capacity:0;
				
				return <<<EOD
					<div id="masterBackBar">
					<div id="myBar" style="background-color:$color;width:$width%;">
					</div>
					<div id="myProgress">
						<span id="c1">0/$max</span>&nbsp;ℓ
					</div>
				</div>
EOD;
			})->
			addColumn('segment', function ($tanklist) use ($merchant_id) {
				//		return $tanklist->fr;
				
				if (!empty($tanklist->fr_detail)) {
					$name		= $tanklist->fr_detail->name;
					$fname		= $tanklist->fr_detail->fname;

					$systemid	= $tanklist->fr_detail->systemid;
					$fsystemid	= $tanklist->fr_detail->fsystemid;
					$type = ucwords($tanklist->type);
					return <<< EOD
						<span class="os-linkcolor" 
							style="cursor:pointer;"
							onclick="franchise_popup('$name', '$systemid','$fname', '$fsystemid','$tanklist->owner_company_name','$tanklist->owner_systemid')">$type</span>
EOD;
				
				} else {
					return 'Direct';
				}
			})
			->addColumn('deleted', function ($tanklist) use ($is_franchise) {
			$redCrab = asset('images/redcrab_50x50.png');
			
			if ($tanklist->read_only == true) {
				return '';
			} 

			return <<< EOD
				<img src="$redCrab" data-field="deleted" id="$tanklist->systemid" class="remove" style="width:25px;height:25px;cursor:pointer"/>
EOD;

			})->
			escapeColumns([])->
			make(true);
    }


    public function showTank_AccordingLocation_Disabled(Request $request)
    {
        $location_ID = $request->location_id;

        $model = OgTank::where(['location_id' => $location_ID])->get();
        return Datatables::of($model)
            ->addIndexColumn()
            ->addColumn('tank_no', function ($tanklist) {
                return '<p class="os-linkcolor" id="tank_no_' . $tanklist->id . '" onclick="storeTankID(' . $tanklist->id . ',' . $tanklist->tank_no . ')" style="cursor: pointer; margin: 0; text-align: center;">' . $tanklist->tank_no . '</p>';
            })
            ->addColumn('tank_id', function ($tanklist) {
                return '<p style=" margin: 0; text-align: center;">' . $tanklist->systemid . '</p>';
            })
            ->addColumn('product', function ($tanklist) {

                if (!empty($tanklist->product_name->thumbnail_1)) {
                    $img_src = '/images/product/' . $tanklist->product_name->id . '/thumb/' . $tanklist->product_name->thumbnail_1;
                    $prod_img_id = $tanklist->product_name->id;
                    //class='img-product-thumb-$prod_img_id'
                    $img = "<img id='img-product-thumb-' src='$img_src' data-field='og_product_name' style=' width: 25px;height: 25px;display: inline-block;margin-right: 8px;float:left;object-fit:contain;'/>";
                } else {
                    $img = "";
                }
//                return $img . '<p class="os-linkcolor" data-field="og_product_name" style="cursor: pointer; margin: 0;float:left;display:inline-block;" onclick="details(' . $tanklist->product_name . ')">' . (!empty($tanklist->product_name->name) ? $tanklist->product_name->name : 'Product Name') . '</p>';
//
//				if (!empty($tanklist->product_name)) {
//					$product_name = $tanklist->product_name->name;
//				} else {
//					$product_name = "Product Name";
//				}

                return $img . '<p class="os-linkcolor" id="tank_product_' . $tanklist->id . '" style="cursor: pointer;  text-align: left;margin-bottom: 0px;" onclick="showProducts(' . $tanklist->id . ')"  >' . (!empty($tanklist->product_id) ? $tanklist->product_name->name : 'Product Name') . '</p>';
            })
            ->addColumn('height', function ($tanklist) {
                return '<p id="tank_height_' . $tanklist->id . '" class="os-linkcolor" data-field="' . $tanklist->id . '"  onclick="showogFuelHeightModel(' . $tanklist->id . ',' . $tanklist->systemid . ')"  style="cursor: pointer; margin: 0;float:right; text-align:center;" >' . (!empty($tanklist->height) ? number_format($tanklist->height) : '0') . '</p>';

            })
            ->addColumn('deleted', function ($tanklist) {
                return '<p data-field="deleted"
                        id="' . $tanklist->systemid . '"
						style="background-color:red;
						border-radius:5px;margin:auto;
						width:25px;height:25px;
						display:block;cursor: pointer;"
						class="text-danger remove">
						<i class="fas fa-times text-white"
						style="color:white;opacity:1.0;
						padding-top:4px;
						-webkit-text-stroke: 1px red;"></i></p>';
            })->

            escapeColumns([])->
            make(true);
    }

    public function showTank_AccordingLocation(Request $request)
    {
        $location_ID = $request->locID;

        if (!empty($request->locID)) {

            $model = OgTank::where(['location_id' => $location_ID])->get();
            return Datatables::of($model)
                ->addIndexColumn()
                ->addColumn('tank_no', function ($tanklist) {
                    return '<p class="os-linkcolor" id="tank_no_' . $tanklist->id . '" onclick="storeTankID(' . $tanklist->id . ',' . $tanklist->tank_no . ')" style="cursor: pointer; margin: 0; text-align: center;">' . $tanklist->tank_no . '</p>';
                })
                ->addColumn('tank_id', function ($tanklist) {
                    return '<p style=" margin: 0; text-align: center;">' . $tanklist->systemid . '</p>';
                })
                ->addColumn('product', function ($tanklist) {

                    if (!empty($tanklist->product_name->thumbnail_1)) {
                        $img_src = '/images/product/' . $tanklist->product_name->id . '/thumb/' . $tanklist->product_name->thumbnail_1;
                        $prod_img_id = $tanklist->product_name->id;
                        //class='img-product-thumb-$prod_img_id'
                        $img = "<img id='img-product-thumb-' src='$img_src' data-field='og_product_name' style=' width: 25px;height: 25px;display: inline-block;margin-right: 8px;float:left;object-fit:contain;'/>";
                    } else {
                        $img = "";
                    }
                    //                return $img . '<p class="os-linkcolor" data-field="og_product_name" style="cursor: pointer; margin: 0;float:left;display:inline-block;" onclick="details(' . $tanklist->product_name . ')">' . (!empty($tanklist->product_name->name) ? $tanklist->product_name->name : 'Product Name') . '</p>';
                    //
                    //				if (!empty($tanklist->product_name)) {
                    //					$product_name = $tanklist->product_name->name;
                    //				} else {
                    //					$product_name = "Product Name";
                    //				}

                    return $img . '<p class="os-linkcolor" id="tank_product_' . $tanklist->id . '" style="cursor: pointer;  text-align: left;margin-bottom: 0px;" onclick="showProducts(' . $tanklist->id . ')"  >' . (!empty($tanklist->product_id) ? $tanklist->product_name->name : 'Product Name') . '</p>';
                })
                ->addColumn('height', function ($tanklist) {
                    return '<p id="tank_height_' . $tanklist->id . '" class="os-linkcolor" data-field="' . $tanklist->id . '"  onclick="showogFuelHeightModel(' . $tanklist->id . ',' . $tanklist->systemid . ')"  style="cursor: pointer; margin: 0;float:right; text-align:center;" >' . (!empty($tanklist->height) ? number_format($tanklist->height) : '0') . '</p>';

                })
                ->addColumn('deleted', function ($tanklist) {
                    return '<p data-field="deleted"
                        id="' . $tanklist->systemid . '"
						style="background-color:red;
						border-radius:5px;margin:auto;
						width:25px;height:25px;
						display:block;cursor: pointer;"
						class="text-danger remove">
						<i class="fas fa-times text-white"
						style="color:white;opacity:1.0;
						padding-top:4px;
						-webkit-text-stroke: 1px red;"></i></p>';
                })->

                escapeColumns([])->
                make(true);

        }else{

            $model = OgTank::all();
            return Datatables::of($model)
                ->addIndexColumn()
                ->addColumn('tank_no', function ($tanklist) {
                    return '<p class="os-linkcolor" id="tank_no_' . $tanklist->id . '" onclick="storeTankID(' . $tanklist->id . ',' . $tanklist->tank_no . ')" style="cursor: pointer; margin: 0; text-align: center;">' . $tanklist->tank_no . '</p>';
                })
                ->addColumn('tank_id', function ($tanklist) {
                    return '<p style=" margin: 0; text-align: center;">' . $tanklist->systemid . '</p>';
                })
                ->addColumn('product', function ($tanklist) {

                    if (!empty($tanklist->product_name->thumbnail_1)) {
                        $img_src = '/images/product/' . $tanklist->product_name->id . '/thumb/' . $tanklist->product_name->thumbnail_1;
                        $prod_img_id = $tanklist->product_name->id;
                        //class='img-product-thumb-$prod_img_id'
                        $img = "<img id='img-product-thumb-' src='$img_src' data-field='og_product_name' style=' width: 25px;height: 25px;display: inline-block;margin-right: 8px;float:left;object-fit:contain;'/>";
                    } else {
                        $img = "";
                    }
                    //                return $img . '<p class="os-linkcolor" data-field="og_product_name" style="cursor: pointer; margin: 0;float:left;display:inline-block;" onclick="details(' . $tanklist->product_name . ')">' . (!empty($tanklist->product_name->name) ? $tanklist->product_name->name : 'Product Name') . '</p>';
                    //
                    //				if (!empty($tanklist->product_name)) {
                    //					$product_name = $tanklist->product_name->name;
                    //				} else {
                    //					$product_name = "Product Name";
                    //				}

                    return $img . '<p class="os-linkcolor" id="tank_product_' . $tanklist->id . '" style="cursor: pointer;  text-align: left;margin-bottom: 0px;" onclick="showProducts(' . $tanklist->id . ')"  >' . (!empty($tanklist->product_id) ? $tanklist->product_name->name : 'Product Name') . '</p>';
                })
                ->addColumn('height', function ($tanklist) {
                    return '<p id="tank_height_' . $tanklist->id . '" class="os-linkcolor" data-field="' . $tanklist->id . '"  onclick="showogFuelHeightModel(' . $tanklist->id . ',' . $tanklist->systemid . ')"  style="cursor: pointer; margin: 0;float:right; text-align:center;" >' . (!empty($tanklist->height) ? number_format($tanklist->height) : '0') . '</p>';

                })
                ->addColumn('deleted', function ($tanklist) {
                    return '<p data-field="deleted"
                        id="' . $tanklist->systemid . '"
						style="background-color:red;
						border-radius:5px;margin:auto;
						width:25px;height:25px;
						display:block;cursor: pointer;"
						class="text-danger remove">
						<i class="fas fa-times text-white"
						style="color:white;opacity:1.0;
						padding-top:4px;
						-webkit-text-stroke: 1px red;"></i></p>';
                })->

                escapeColumns([])->
                make(true);
        }
    }


    public function storeTank(Request $request)
    {
        try {
            $SystemID 	= new SystemID('og_tank');
            $og_tank 	= new OgTank();
			$user_data	= new UserData();

            if (empty($request->location_id)) {
                $msg = "Please select a location";
                return view('layouts.dialog', compact('msg'));
            }

            $og_tank->tank_no = '0';
            $og_tank->location_id = $request->location_id;
            $og_tank->systemid = $SystemID->__toString();
			$og_tank->height = '0';
			$og_tank->max_capacity = $request->tankSize;
            $og_tank->user_id = Auth::User()->id;
			$og_tank->franchise_merchant_id = $user_data->company_id();
			
			if (!empty($request->t_id)) {
				$location = DB::table('location')->
					join('opos_locationterminal','opos_locationterminal.location_id','=','location.id')->
					join('opos_terminal','opos_terminal.id','=','opos_locationterminal.terminal_id')->
					join('merchantlocation','merchantlocation.location_id','=','opos_locationterminal.location_id')->
					where('opos_terminal.systemid',$request->t_id)->
					select("location.*",'merchantlocation.merchant_id')->
					first();
				
				if (!empty($location)) {
						$og_tank->type = 'franchise';
				}
			}

            $og_tank->save();

            $msg = "Tank added successfully";
            return view('layouts.dialog', compact('msg'));

        } catch (\Exception $e) {
            $msg = $e->getMessage();
            return view('layouts.dialog', compact('msg'));
        }
    }



    public function updateTankHeight(Request $request)
    {
        try {
            $this->user_data = new UserData();
            $og_tank_id   = $request->get('TankID');
	    $height       =  (int)str_replace(',', '',str_replace('.', '',
			number_format ( $request->get('ogTankHeight') )));
                                
            $ogTankHeight = OgTank::where('systemid',$og_tank_id)->first();
            
            $ogTankHeight->height = $height;
            $ogTankHeight->save();
            $msg = "Height updated successfully";
            return view('layouts.dialog', compact('msg'));

        } catch (\Exception $e) {
            $msg = $e->getMessage();
            return view('layouts.dialog', compact('msg'));
        }
    }


    public function showTankProducts(Request $request)
    {
        //$og_fuels= OgFuel::all()->take($request->length);

		$query = "
		SELECT
			latest.id,
			latest.name,
			og_fuelprice.ogfuel_id as No,
			og_fuelprice.price
		FROM (
		SELECT
			p.id,
			p.name,
			max(fp.created_at) as fp_created_at
		FROM
			product p,
			prd_ogfuel pof,
			merchantproduct mp,
			company c,
			og_fuelprice fp
		WHERE
			pof.product_id = p.id 
			AND mp.product_id = p.id
			AND mp.merchant_id = c.id
			AND fp.ogfuel_id = pof.id
			AND p.name is not null
			AND fp.price is not null
			AND c.owner_user_id = ".Auth::user()->id."
		GROUP BY
			p.id
		) as latest
		INNER JOIN
			og_fuelprice
		ON
			og_fuelprice.created_at = latest.fp_created_at
		ORDER BY
			og_fuelprice.ogfuel_id
		LIMIT 8
		";

		if (!empty($request->t_id)) {
			
			$this->user_data = new UserData();

			$fmerchant_id = DB::table('merchantlocation')->select('merchantlocation.merchant_id')->
				join('opos_locationterminal','opos_locationterminal.location_id','=','merchantlocation.location_id')->
				join('opos_terminal','opos_terminal.id','=','opos_locationterminal.terminal_id')->
				where('opos_terminal.systemid',$request->t_id)->first()->merchant_id ?? '';
			
			$is_franchise_terminal = FranchiseMerchantLocTerm::select('company.name','company.systemid')->
				join('franchisemerchantloc', 'franchisemerchantloc.id', '=',
					'franchisemerchantlocterm.franchisemerchantloc_id')->
				join('franchisemerchant','franchisemerchant.id','=',
					'franchisemerchantloc.franchisemerchant_id')->	
				join('franchise','franchise.id','=', 'franchisemerchant.franchise_id')->
				join('company', 'franchise.owner_merchant_id', '=','company.id')->			
				join('opos_terminal','opos_terminal.id','=', 'franchisemerchantlocterm.terminal_id')->
				where(
					[	
						'opos_terminal.systemid' 					=> $request->t_id,
						"franchisemerchant.franchisee_merchant_id"	=>	$this->user_data->company_id()
					]

				)->
				first();
		}

		$og_fuels = $this->getOgFuelQualifiedProducts();
		
		$output="";
        foreach ($og_fuels as  $og_fuel) {
			if (!empty($request->t_id)) {
				if (!empty($is_franchise_terminal)) {
				
					if ($og_fuel->access_type != 'franchise')
						continue;
			
					if ($og_fuel->franchise_m_id != $fmerchant_id) 
						continue;
				} else {
				
					if ($og_fuel->access_type != 'direct') 
						continue;
				}

			} else {
				if ($og_fuel->access_type != 'direct')
					continue;
			}

			$output.='<button class="btn btn-success bg-enter btn-log sellerbuttonwide 
				ps-function-btn pump_credit_card_product"  style="width: 129px !important;" 
				onclick="add_product_pump('.$og_fuel->id.','.$request->tank_id.')" > <span>'. 
				$og_fuel->name .' </span> </button>';
        }

        $response = [
            'og_fuels' => $og_fuels,
            'output' => $output,
        ];
        return response()->json($response);
    }

	public function color_guide() {
		$og_fuels = $this->getOgFuelQualifiedProducts();
//	dd($og_fuels);
		return view('industry.oil_gas.color_guide',compact('og_fuels'))->render();
	}

    public function saveTankProducts(Request $request){
        try{
            $product_id=$request->product_id;
            $tank_id=$request->tank_id;

            $tank=OgTank::find($tank_id);
            $tank->product_id=$product_id;
            $tank->save();
            $msg = 'Product updated successfully';
            return view('layouts.dialog', compact('msg'));
//            return view('layouts.dialog')->with(['msg'=>$msg]);

        }catch (\Exception $e) {
            $msg = $e->getMessage();
            return view('layouts.dialog', compact('msg'));
//            return view('layouts.dialog')->with(['msg'=>$msg]);
        }
    }


    public function getTankProducts(Request $request){
        try{
            $tank_id=$request->tank_id;
            $product_name=OgTank::find($tank_id)->product_name()->first()->name;
            $product_id = OgTank::find($tank_id)->product_name()->first()->id;
            $product_image_thumbnail = OgTank::find($tank_id)->product_name()->first()->thumbnail_1;

            return array('0' => $product_name, '1' => $product_id, '2' => $product_image_thumbnail);

        }catch (\Exception $e) {
            $msg = $e->getMessage();
//            return view('layouts.dialog', compact('msg'));
            return view('layouts.dialog')->with(['msg'=>$msg]);
        }
    }


    public function saveTankNo(Request $request){
        try {
            $tank_id=$request->tank_id;
            $tank_no=$request->tank_no;

            $tank=OgTank::find($tank_id);
            $tank->tank_no=$tank_no;
            $tank->save();
            $msg = 'Tank No. updated successfully';
            return view('layouts.dialog', compact('msg'));

        } catch (\Exception $e) {
            $msg = $e->getMessage();
            return view('layouts.dialog', compact('msg'));
        }
    }
    

	public function check_transaction($product_id)
	{

		$sales_count = opos_receiptproduct::where('product_id', $product_id)
			->leftjoin('opos_itemdetails', 'opos_itemdetails.receiptproduct_id', '=', 'opos_receiptproduct.id')
			->leftjoin('opos_receipt', 'opos_receipt.id', '=', 'opos_receiptproduct.receipt_id')
			->leftjoin('opos_receiptdetails', 'opos_receipt.id', '=', 'opos_receiptdetails.receipt_id')
            ->count();
            
		$stock_count = StockReport::join('stockreportproduct','stockreportproduct.stockreport_id','=','stockreport.id')->
			where('stockreportproduct.product_id', $product_id)->count();
		$wastage = opos_wastageproduct::where('product_id', $product_id)->count();
        $total = $sales_count + $stock_count + $wastage;
        //dd($total);
		if ($total > 0) {
			return true;
		} else {
			return false;
		}
    } 

	//Don't change this, used in franchise products
    	// parameters: ogFuel_id, return: Price
	public function get_execute_price($id)
	{
		$ogFuelPrice = OgFuelPrice::where('ogfuel_id', $id)
						->where('price', '!=', null)
                        ->where('start', '!=', null)
                        ->whereDate('start', '<=', Carbon::now())
						->orderBy('id', 'DESC')
						->first();
					
		if(!empty($ogFuelPrice)){
			$price = $ogFuelPrice->price;
		}else{
			$price = '0.00';
		}
		
		return $price;
    }
	
    	
    public function showOgFuelProducts(Request $request,$t_id = null)
	{
		$user_data = new \App\Classes\UserData();
		$company_id= $user_data->company_id();

			/*DB::table('users')
        // ->select('staff.company_id')
        ->join('staff','users.id','staff.user_id')
        ->where('staff.user_id' , Auth::user()->id )->first()->company_id;
		  */
		$query = "
		SELECT
			latest.id,
            latest.name,
            latest.thumbnail_1,
            latest.systemid,
			og_fuelprice.ogfuel_id as No,
			og_fuelprice.price,
            og_fuelprice.start
		FROM (
		SELECT
			p.id,
            p.name,
            p.thumbnail_1,
            p.systemid,
			max(fp.created_at) as fp_created_at
		FROM
			product p,
			prd_ogfuel pof,
			merchantproduct mp,
			company c,
			og_fuelprice fp
		WHERE
			pof.product_id = p.id 
			AND mp.product_id = p.id
			AND mp.merchant_id = c.id
			AND fp.ogfuel_id = pof.id
			AND p.name is not null
            AND p.prdcategory_id  > 0
            AND p.prdsubcategory_id  > 0
            AND p.prdprdcategory_id > 0
            AND p.photo_1 is not null
			AND fp.price is not null
            AND fp.start <= '".Carbon::now()."'   
			AND c.id = ".$company_id."
		GROUP BY
			p.id
		) as latest
		INNER JOIN
			og_fuelprice
		ON
			og_fuelprice.created_at = latest.fp_created_at
		ORDER BY
			og_fuelprice.ogfuel_id
		LIMIT 8
		";

		$products = DB::select(DB::raw($query));
		
		if (!empty($t_id)) {
			$location = location::leftjoin('opos_locationterminal',
					'opos_locationterminal.location_id','=','location.id')->
				where('opos_locationterminal.terminal_id',$t_id)->
				select("location.*")->
				first();
			
			$is_franchise = \App\Models\FranchiseMerchantLocTerm::select('company.name','company.systemid')->
				join('franchisemerchantloc', 'franchisemerchantloc.id', '=',
					'franchisemerchantlocterm.franchisemerchantloc_id')->
				join('franchisemerchant','franchisemerchant.id','=',
					'franchisemerchantloc.franchisemerchant_id')->
				join('franchise','franchise.id','=', 'franchisemerchant.franchise_id')->
			//	join('merchant', 'franchise.owner_merchant_id', '=','merchant.id')->
				join('company', 'franchise.owner_merchant_id', '=','company.id')->
				where(
					['franchisemerchantlocterm.terminal_id' => $t_id],
					["franchisemerchantlocterm.franchisemerchant_id" => $company_id]
				)->
				first();
			
			if (!empty($is_franchise)) {
				$franchise_p_id = DB::table('franchiseproduct')->
					leftjoin('franchisemerchant',
						'franchisemerchant.franchise_id','=','franchiseproduct.franchise_id')->
					leftjoin('franchisemerchantloc',
						'franchisemerchantloc.franchisemerchant_id','=','franchisemerchant.id')->
					leftjoin('product','product.id','=','franchiseproduct.product_id')->
					where([
						'franchisemerchant.franchisee_merchant_id' => $company_id,
						'franchisemerchantloc.location_id' => $location->id,
						'franchiseproduct.active' => 1
					])->
					whereNull('franchiseproduct.deleted_at')->
					where('ptype','oilgas')->
					select('product.*')->
					get();
				
				$query = "
				SELECT distinct	
					latest.id,
					latest.name,
					latest.thumbnail_1,
					latest.systemid,
					og_fuelprice.ogfuel_id as No,
					og_fuelprice.price,
					og_fuelprice.start
				FROM (
				SELECT
					p.id,
					p.name,
					p.thumbnail_1,
					p.systemid,
					max(fp.created_at) as fp_created_at
				FROM
					product p,
					prd_ogfuel pof,
					merchantproduct mp,
					company c,
					og_fuelprice fp
				WHERE
					pof.product_id = p.id 
					AND mp.product_id = p.id
					AND mp.merchant_id = c.id
					AND fp.ogfuel_id = pof.id
					AND p.name is not null
					AND p.prdcategory_id  > 0
					AND p.prdsubcategory_id  > 0
					AND p.prdprdcategory_id > 0
					AND p.photo_1 is not null
					AND fp.price is not null
					AND fp.start <= '".Carbon::now()."'  
					AND p.id In (".implode(',',$franchise_p_id->pluck('id')->toArray()) .") 
				GROUP BY
					p.id
				) as latest
				INNER JOIN
					og_fuelprice
				ON
					og_fuelprice.created_at = latest.fp_created_at
				ORDER BY
					og_fuelprice.ogfuel_id
				LIMIT 8
				";
				if (!$franchise_p_id->isEmpty())
					$products = DB::select(DB::raw($query));
				else
					$products = collect();
			}
		}

		if (!empty($products_)) {	
			$products = array_merge($products_,$products); 
		}

		$output="";
        foreach ($products as  $product) {
			$output .='<button class="btn btn-success bg-enter btn-log 
				sellerbuttonwide ps-function-btn pump_credit_card_product" 
				href_fuel_prod_name="'.$product->name.'" href_fuel_prod_id="'.
				$product->id.'" href_fuel_prod_thumbnail="'.$product->thumbnail_1.
				'" href_fuel_prod_systemid="'.$product->systemid.'" style="width: 129px !important;"
				> <span>'. $product->name .' </span> </button>';
        }


        $totalRecords = count($products);

        $response = [
            'data' => $products,
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalRecords,
            'output'   => $output
        ];
        return response()->json($response);
    }

    public function getOgFuelQualifiedProducts($company_id = null) {

		if ($company_id == null) {
        	$this->user_data = new UserData();
			$company_id		 = $this->user_data->company_id();
		}

        $products_chunck=array();
        $filter = array();
		$merchant_product_ids = merchantproduct::where('merchant_id', $company_id)->	
            pluck('product_id');
	
	
		$franchise_p_id = DB::table('franchiseproduct')->
			leftjoin('franchisemerchant',
				'franchisemerchant.franchise_id','=','franchiseproduct.franchise_id')->
			where([
				'franchisemerchant.franchisee_merchant_id' => $company_id,
				'franchiseproduct.active' => 1
			])->
			whereNull('franchiseproduct.deleted_at')->
			get();
	
		
		$ids_f = $franchise_p_id->pluck('product_id');
		
		$merchant_product_ids = $merchant_product_ids->
			merge($ids_f)->unique();

        $ids = OgFuel::whereIn('product_id',$merchant_product_ids)->
			pluck('product_id');

        $products = product::
			 select('product.*','og_fuelprice.start','og_fuelprice.price',
			 	'og_fuelprice.ogfuel_id as og_f_id','color.hex_code as color')
			->join('prd_ogfuel','product.id','prd_ogfuel.product_id')
			->join('og_fuelprice','prd_ogfuel.id','og_fuelprice.ogfuel_id')
			->leftjoin('productcolor','productcolor.product_id','=','prd_ogfuel.product_id')
			->leftjoin('color','color.id','=','productcolor.color_id')
			->whereIn('product.id',$ids)
			->where([
				['product.name', '<>', null] ,
				['product.prdcategory_id', '>', 0],
			//	['product.prdsubcategory_id', '>', 0],
			//	['product.prdprdcategory_id', '>', 0],
				['product.photo_1','!=',null]
			])
			//->whereDate('og_fuelprice.start', '<=', Carbon::now())
			->orderBy('og_fuelprice.created_at', 'DESC')
			->get();

		$franchise_mp = merchantproduct::whereIn('product_id',$ids)->get();
		$products->map(function($z) use ($ids_f, $franchise_mp) {
			if ($ids_f->contains($z->id)) {
				$z->access_type = "franchise";
				$z->franchise_m_id = $franchise_mp->
					where('product_id',$z->id)->
					first()->merchant_id ?? '';

			} else {
				$z->access_type = "direct";
			}
		
		});
        
        foreach($products as $product){
            if(!in_array($product->og_f_id, $filter)){
                $products_chunck[] = $product;
                $filter[] = $product->og_f_id;
            }
        }
        return $products_chunck;
    }

    public function showOgFuelQualifiedProducts(Request $request) {
        $products = $this->getOgFuelQualifiedProducts();
        $output="";
        foreach ($products as  $product) {
            $output.='<button class="btn btn-success bg-enter btn-log sellerbuttonwide ps-function-btn pump_credit_card_product" href_fuel_prod_name="'
				.$product->name.'" href_fuel_prod_id="'.$product->id.'" href_fuel_prod_thumbnail="'.$product->thumbnail_1.'" href_fuel_prod_systemid="'
				.$product->systemid.'" style="width: 129px !important;"> <span>'. $product->name .' </span> </button>';
        }


        $totalRecords = count($products);

        $response = [
            'data' => $products,
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalRecords,
            'output'   => $output
        ];
        return response()->json($response);
    }


    public function saveProduct(Request $request) {
        //Create a new product here
        try {
            $this->user_data = new UserData();
            $SystemID = new SystemID('product');

            $product = new product();
            $product->systemid = $SystemID;
            $product->ptype = 'inventory';
            $product->save();

            $inventory = new prd_inventory();
            $inventory->product_id = $product->id;
            $inventory->save();

            $merchantproduct = new merchantproduct();
            $merchantproduct->product_id = $product->id;
            $merchantproduct->merchant_id = $this->user_data->company_id();
            $merchantproduct->save();

            $ogFuel = new OgFuel();
            $ogFuel->product_id = $product->id;
            $ogFuel->save();

            $msg = "Product added successfully";
            return view('layouts.dialog', compact('msg'));

        } catch (\Exception $e) {
            $msg = $e->getMessage();
            return view('layouts.dialog', compact('msg'));
        }
    }
    

    function showIndustryView(){
        return view('industry.oil_gas.og_oilgas');
    }


    function showFuelPriceView($id) {
		$oilgas = OgFuel::where('id', $id)->first();
        return view('industry.oil_gas.og_fuelprice', compact(['id', 'oilgas']));
    }

   
	function showFuelLocalPriceView($location_id) {
		$location = Location::find($location_id);
		return view('industry.oil_gas.og_fuel_localprice', compact('location'));
    }

	function showFuelLocalPriceDatatable(Request $request) {
		$fuelRecord = collect($this->getOgFuelQualifiedProducts());
		$user_data = new UserData();
		$fuelRecord->map(function ($f) use ($request, $user_data) {
			$f->localfuelprice = DB::table('og_localfuelprice')->
				where([
					'ogfuel_id' 	=> $f->og_f_id, 
					'location_id' 	=> $request->location_id,
					'company_id'	=> $user_data->company_id()
				])->
				first();
		});


		return Datatables::of($fuelRecord)->
			addIndexColumn()->
			addColumn('product_name', function ($data) {
				$img_src = '/images/product/' .
				$data->id . '/thumb/' .
				$data->thumbnail_1;
			
				$img = "<img src='$img_src' data-field='inven_pro_name' style=' width: 25px;
				height: 25px;display: inline-block;margin-right: 8px;object-fit:contain;'>";

				return $img.$data->name;
			})->
			addColumn('start', function ($data) {
				if (!empty($data->localfuelprice)) {
					$date = date("dMY", strtotime($data->localfuelprice->start));
					$date_inp = date("Y-m-d", strtotime($data->localfuelprice->start));
					if ($data->localfuelprice->start == '0000-00-00 00:00:00') {
						$date = 'Date';
						$date_inp = date("Y-m-d");
					}
				} else
					$date = "Date";

				$date_inp = $date_inp ?? date("Y-m-d");
				$fk_id = $data->og_f_id;
	
				$html =  <<<EOD
				<span class="os-linkcolor" onclick="dateDialog('$date_inp', '$fk_id')" style="cursor:pointer">
					$date</span>
EOD;
				return $html;
			})->
			addColumn('price', function ($data) {
				$price = number_format(($data->localfuelprice->price ?? 0) /100,2); 
				$fk_id = $data->og_f_id;
				$html =  <<<EOD
				<span class="os-linkcolor" onclick="price_set_modal('$price', '$fk_id')" style="cursor:pointer">
					$price</span>
EOD;
				return $html;
			})->
			addColumn('user', function ($data) {
				if (!empty($data->localfuelprice))
					$user = DB::table('users')->find($data->localfuelprice->user_id)->name;
				else
					$user = "";
				return $user;
			})->
			addColumn('user_date', function($data) {
				return !empty($data->localfuelprice->created_at) ?
					date("dMY", strtotime($data->localfuelprice->created_at)):'';
			})->
			escapeColumns([])->
			make(true);
	}

	function showFuelLocalPriceUpdate(Request $request) {
		try {

			$user_data = new UserData();
			$validation = Validator::make($request->all(), [
				"field"	=>	"required",
				"value"	=>	"required",
				"id"	=>	"required"
			]);

			if ($validation->fails())
				throw new \Exception("Validation failed");

			$is_exist = DB::table('og_localfuelprice')->
				where([
					"ogfuel_id"		=> $request->id,
					"location_id"	=> $request->location_id,
					"company_id"	=> $user_data->company_id()
				])->first();

			$array = [];
			$array['updated_at'] = date('Y-m-d H:i:s');
			$array["company_id"] = $user_data->company_id();

			switch($request->field) {
				case 'start':
					$array['start'] = date("Y-m-d 00:00:00", strtotime($request->value));
					break;
				case 'price':
					$array['price'] = $request->value;
					break;
				default:
					throw new \Exception("Invalid data type");
					break;
			}

			if (!empty($is_exist)) {
				DB::table('og_localfuelprice')->
					where([
						"ogfuel_id"	=> $request->id,
						"location_id"	=> $request->location_id,
						"company_id"	=> $user_data->company_id()
				])->update($array);
			} else {
				$array['created_at'] 	= date('Y-m-d H:i:s');
				$array["ogfuel_id"]		= $request->id;
				$array['user_id']		= Auth::User()->id;
				$array["location_id"]	= $request->location_id;
				DB::table('og_localfuelprice')->insert($array);
			}
			
			return response()->json(["status" => true]);

		} catch (\Exception $e) {
			\Log::info([
				"Error"	=>	$e->getMessage(),
				"File"	=>	$e->getFile(),
				"Line"	=>	$e->getLine()
			]);
			abort(404);
		}
	}

	function fuelLocalPriceDatatable(Request $request) {
		try {

			$user_data = new UserData();
			$pump = DB::table('local_pump')->
				join('local_controller','local_controller.id','=','local_pump.controller_id')->
				where([
					"local_controller.location_id"	=>	$request->location_id,
					"local_controller.company_id"	=>	$user_data->company_id()
				])->
				select("local_pump.*")->
				get();

			return Datatables::of($pump)->
				addIndexColumn()->
				addColumn('pump_no', function ($data) {
					return <<<EOD
					<span class="os-linkcolor" style='cursor:pointer;'
					onclick="openDetailModal($data->id)">$data->pump_no</span>
EOD;
				})->
				escapeColumns([])->
				make(true);
		} catch (\Exception $e) {
			\Log::info([
				"Error" =>  $e->getMessage(),
				"File"  =>  $e->getFile(),
				"Line"  =>  $e->getLine()
			]);
			abort(404);
		}
	}

	function fuelLocalPriceModal(Request $request) {
		try {

			$pump = DB::table('local_pump')->
				/*leftjoin('local_pts2_protocol','local_pts2_protocol.id','=',
					'local_pump.local_pts2_protocol_id')->
				leftjoin('local_pts2_baudrate','local_pts2_baudrate.index','=',
					'local_pump.baudrate')->
					leftjoin()*/
				where('local_pump.id', $request->pump_id)->
				first();

			$local_pts2_protocol = DB::table('local_pts2_protocol')->
				where('protocol_no', $pump->pts2_protocol_id)->
				first();

			$baud = DB::table('local_pts2_baudrate')->
				where('index', $pump->baudrate)->
				first();

			$nozzleData = DB::table('local_pumpnozzle')->
				join('prd_ogfuel','prd_ogfuel.id',
					'=','local_pumpnozzle.ogfuel_id')->
				leftjoin('product','product.id','=','prd_ogfuel.product_id')->
				where('local_pumpnozzle.pump_id', $pump->id)->
				select('local_pumpnozzle.nozzle_no',
					'local_pumpnozzle.ogfuel_id','product.name as fuel_name')->
				get();

			$nozzleData->map(function($f) {
				$f->price = number_format(
					$this->getPrice($f->ogfuel_id, request()->location_id), 2);
			});

			return view('industry.oil_gas.local_pumpconfig_modal', compact('pump', 
				'local_pts2_protocol', 'baud', 'nozzleData'));
		
		}  catch (\Exception $e) {
			\Log::info([
				"Error" =>  $e->getMessage(),
				"File"  =>  $e->getFile(),
				"Line"  =>  $e->getLine()
			]);
			abort(404);
		}
	}

	/*
	 * PUSH FUEL PRICE TO HARDWARE
	 */
	public function fuelLocalPricePush(Request $request) {
		try {
			$this->push_price2controller($request->location_id);
			$msg = "Pushed successfully to hardware";
            return view('layouts.dialog', compact('msg'));
		} catch (\Exception $e) {
			\Log::info([
				"Error" =>  $e->getMessage(),
				"File"  =>  $e->getFile(),
				"Line"  =>  $e->getLine()
			]);
			abort(404);
		}
	}
	public function push_price2controller($location_id) {
		$user_data = new UserData();
		$forecourtController = new ForecourtController();
		$fuelData = $nozzelDataFIndex = $forecourtController->getFuelData();
		$index = 1;
		foreach($fuelData as $key => $val) {
			$fuelData[$key]['id'] = (int)$index;
			$fuelData[$key]['price'] =
				(float) number_format($this->
					getPrice($val['ogfuel_id'], $location_id), 2);
			$nozzelDataFIndex[$key] = $fuelData[$key];
			unset($fuelData[$key]['ogfuel_id']);
			$index++;
		}

		$oposPetrolStationPumpController = new OposPetrolStationPumpController();
		
		$og_controller = DB::table('local_controller')->
			where('location_id', $location_id)->
			where('company_id', $user_data->company_id())->
			select('ipaddress', 'public_ipaddress', 'id')->get();

		$og_controller->map(function ($hardware) use
			($oposPetrolStationPumpController, $fuelData, $nozzelDataFIndex) {
			if (env('PTS_MODE') == 'local')
				$ipAddress = $hardware->ipaddress;
			else
				$ipAddress = $hardware->public_ipaddress;
			
			$pumpConfig = $this->generateNozzleConfg(
				$hardware->id,
				$nozzelDataFIndex
			);

			foreach($pumpConfig as $config) {	
				$oposPetrolStationPumpController->pumpSetPrices(
					$config['pump_no'],
					$config['prices'],
					$ipAddress);
			}
		});


		return $fuelData;
	} 


	public function generateNozzleConfg($controller_id, $nozzelDataFIndex) {
		$user_data =  new UserData();	
		$nozzleFuelData = DB::table('local_pumpnozzle')->
			join('local_pump','local_pump.id','=','local_pumpnozzle.pump_id')->
			join('local_controller','local_controller.id','=','local_pump.controller_id')->
			//join('prd_ogfuel','prd_ogfuel.id','=','og_pumpnozzle.ogfuel_id')->
			where([
				'local_controller.id'			=>	$controller_id,
				'local_controller.company_id'	=>	$user_data->company_id()
			])->
			whereNull('local_pumpnozzle.deleted_at')->
			whereNull('local_pump.deleted_at')->
			whereNull('local_controller.deleted_at')->
			select('local_pumpnozzle.ogfuel_id','local_pump.pump_no',
				'local_pumpnozzle.nozzle_no', "local_pump.id as pump_id")->
			get();

		$nozzleFuelDataByPump = $nozzleFuelData->groupBy('pump_no');

		$output = [];
		$pumpIndex = 1;
		$nozzelDataFIndex = collect($nozzelDataFIndex);
		
		foreach ($nozzleFuelDataByPump as $byPump) {
			$pumpNozzleFData = [];
			$nozzleFormated = [];
			foreach($byPump as $nozzle) {
				$data = $nozzelDataFIndex->where('ogfuel_id', $nozzle->ogfuel_id)->first();
				if (!empty($data)) {
					$pumpNozzleFData[$nozzle->nozzle_no] = $data['price'];
				}
			}

			for($i = 1; $i <= 6; $i++)
				$nozzleFormated[] = $pumpNozzleFData[$i] ?? 0.00;

			$pumpData = ["pump_no"	=> $byPump[0]->pump_no, "prices"=> $nozzleFormated];
			$output[] = $pumpData; //nozzle data is output
		}
		
		return $output;
	}

	function getPrice($ogfuel_id, $location_id) {
		
		$user_data = new UserData();
		$ogFuelPrice =	ogFuelPrice::where('ogfuel_id', $ogfuel_id)->
			whereDate('start' , '<=',\Carbon\Carbon::today())->
			orderBy('start', 'desc')->
			first();

		$localPrice = DB::table('og_localfuelprice')->where('ogfuel_id',$ogfuel_id)->
			whereDate('start' , '<=',\Carbon\Carbon::today())->where('location_id', $location_id)->
			where('company_id', $user_data->company_id())->
			first();

		if (!empty($localPrice)) 
			return $localPrice->price / 100 ?? 0;	
		else if (!empty($ogFuelPrice)) 
			return $ogFuelPrice->price / 100 ?? 0;
		else
			return 0;
	}

	/*
	*/

    function showProductLedgerView($id,Request $request) {
            //atif
        $product = DB::table('product')
            ->join('prd_ogfuel' , 'product.id' , '=' , 'prd_ogfuel.product_id')
            ->select("product.*")
            ->where(['product.id' => $id])->first();

        if($request->day) {
            $stockreportproducts = $this->getStockReportProducts($id, $request, true , false);
            $opos_product = $this->getOposreceiptproducts($id, $request,true , false);
        } else {
            $stockreportproducts = $this->getStockReportProducts($id, $request , false , false);
            $opos_product = $this->getOposreceiptproducts($id, $request, false , false);
        }

		$data = $opos_product->merge($stockreportproducts)->map(function ($item){
            $item->table_name = class_basename($item);
            return $item;
        })->sortByDesc('updated_at');

   		return view('industry.oil_gas.og_productledger',
			compact('id', 'product','opos_product','stockreportproducts', 'data'));
    }

    function showProductLedgerViewSale($id , $location_id , Request $request) {
        $product = DB::table('product')
            ->join('prd_ogfuel' , 'product.id' , '=' , 'prd_ogfuel.product_id')
            ->select("product.*")
            ->where(['product.id' => $id])->first();
        if($request->day) {
            $stockreportproducts = $this->getStockReportProducts($id, $request, true , $location_id);
            $opos_product = $this->getOposreceiptproducts($id, $request ,true , $location_id);
		
		}else{
            $stockreportproducts = $this->getStockReportProducts($id, $request , false , $location_id);
            $opos_product = $this->getOposreceiptproducts($id, $request ,false , $location_id);
        }
	
	$data = $opos_product->merge($stockreportproducts)->map(function ($item){
            $item->table_name = class_basename($item);
	    
	    $item->remarks  = DB::table('opos_receiptremarks')->
		    where('receipt_id',$item->receipt_id)->
		    whereNull('deleted_at')->
		    orderBy('created_at','desc')->
		    first()->remarks ?? null;

            return $item;
	})->sortByDesc('updated_at');
	
	return view('industry.oil_gas.og_productledger_sale',
            compact('id', 'product','opos_product','stockreportproducts', 'data'));
    }

    function showProductLedgerViewReceipt($id , $location_id , Request $request) {
        $product = DB::table('product')
            ->join('prd_ogfuel' , 'product.id' , '=' , 'prd_ogfuel.product_id')
            ->select("product.*")
            ->where(['product.id' => $id])->first();
            
        if($request->day) {
            $stockreportproducts = $this->getStockReportProducts($id, $request, true , $location_id);
            $opos_product = $this->getOposreceiptproducts($id, $request ,true ,$location_id);
        }else{
            $stockreportproducts = $this->getStockReportProducts($id, $request , false , $location_id);
            $opos_product = $this->getOposreceiptproducts($id, $request ,false ,$location_id);
        }
        $data = $stockreportproducts->merge($stockreportproducts)->map(function ($item){
            $item->table_name = class_basename($item);
            return $item;
        })->sortByDesc('updated_at');
        
        return view('industry.oil_gas.og_productledger_receipt',
            compact('id', 'product','opos_product','stockreportproducts', 'data'));
    }

    public function stockinproduct(Request $request){
        $location_ID = $request->id;
        
        $this->user_data = new UserData();
	
        $ids = merchantproduct::where('merchant_id',
            $this->user_data->company_id())->	
            pluck('product_id');

		$franchiseLocations = DB::table('franchisemerchant')->
			join('franchisemerchantloc','franchisemerchantloc.franchisemerchant_id','=','franchisemerchant.id')->
			where([
				'franchisemerchant.franchisee_merchant_id' => $this->user_data->company_id()
			])->
			whereNull('franchisemerchant.deleted_at')->
			whereNull('franchisemerchantloc.deleted_at')->
			pluck('location_id');
		
		$franchise_p_id = DB::table('franchiseproduct')->
				leftjoin('franchisemerchant',
					'franchisemerchant.franchise_id','=','franchiseproduct.franchise_id')->
				leftjoin('franchisemerchantloc','franchisemerchant.id',
					'=','franchisemerchantloc.franchisemerchant_id')->
				where([
					'franchisemerchant.franchisee_merchant_id' => $this->user_data->company_id(),
					'franchiseproduct.active' => 1
				])->
				whereNull('franchiseproduct.deleted_at')->
				select("franchiseproduct.*","franchisemerchantloc.location_id")->
				get();

		$f_ids = $franchise_p_id->pluck('product_id');

		if (!empty($location_ID)) {
			$franchise_p_id = $franchise_p_id->where('location_id', $location_ID);
			$f_loc =  $franchiseLocations->toArray();
			if (in_array($location_ID, $f_loc)) {
				$ids = $f_ids->toArray();
			}
		} else {
			$ids = array_merge($ids->toArray(),$f_ids->toArray());
		}


        $filtered_ids = array();
        foreach ($ids as $id) {
            if (product::where('name','<>',null)->find($id)) {
                $filtered_ids[] = $id;
            }
        }
        
		$data = array();
            
        $model = new OgFuel();

        $data = $model->whereIn('product_id', $filtered_ids)->
            with('product_name', 'og_fuel_price')->
            orderBy('created_at', 'asc')->
            latest()->get();	

		return Datatables::of($data)->addIndexColumn()->
			addColumn('inven_pro_id', function ($memberList) {
				return $memberList->product_name->systemid;
			})->
			addColumn('inven_pro_name', function ($oilgaslist) {
				if (!empty($oilgaslist->product_name->thumbnail_1)) {
					$img_src = '/images/product/' . $oilgaslist->product_name->id . '/thumb/' . $oilgaslist->product_name->thumbnail_1;
					$img     = "<img src='$img_src' data-field='og_product_name' style=' width: 25px;height: 25px;display: inline-block;margin-right: 8px;object-fit:contain;'/>";
				} else {
					$img = "";
				}
				$txt = $img . '<p class="os-linkcolor" data-field="og_product_name" 
				style="cursor: pointer; margin: 0;display:inline-block" 
				onclick="details(' . $oilgaslist->product_name->systemid . ')">';
				$txt .= (!empty($oilgaslist->product_name->name)) ? $oilgaslist->product_name->name .' '  : 'Product Name';
				//$txt .= ($oilgaslist->og_fuel_price) ? ' (' . number_format($oilgaslist->og_fuel_price->price,  2) .')' : '---' . '</p>';
				return $txt;
			})->
			addColumn('inven_pro_colour', function ($memberList) {
			$product_color = productcolor::join('color', 'productcolor.color_id', '=', 'color.id')
				->where('productcolor.product_id', $memberList->product_id)->first();
			if ($product_color) {
				return $product_color->name;
			}
			return "-";
			})->
			addColumn('inven_pro_matrix', function ($memberList) {
				return '-';
			})->
			addColumn('inven_pro_rack', function ($memberList) {
			// if (count($memberList->rack) <= 0) {
			// 	return '-';
			// }
			return '<div style="cursor: pointer;"
				class="rack_list" id="' . $memberList->product_id . '" onclick="open_rack(' . $memberList->product_id . ',' . $memberList->first_product . ')">' . (($memberList->rack_no) ? $memberList->rack_no : "-") . '</div>';
			})->
			addColumn('inven_pro_existing_qty', function ($memberList) use ($location_ID) {
		//		$lp = locationproduct::where(['location_id'=>$location_ID,'product_id'=>$memberList->product_id, 
		//			"franchisee_merchant_id"=>$this->user_data->company_id()])->get()->first();
				
				$lp = (float) number_format( app('App\Http\Controllers\InventoryController')->
					location_productqty($memberList->product_id,$location_ID),2);

			 if($lp)
				 return  '<label value="'.$lp.'" id="existing_qty_'.$memberList->product_id.'">'.$lp.'</label>';
				 else return '<label value="' . 0 . '" id="existing_qty_' . $memberList->product_id . '">0.00</label>';
			/*
				if($memberList->existing_qty == null)
					return 0;
				else
				return $memberList->system_id;
			*/
			})->
			addColumn('inven_pro_qty', function ($memberList) {
				return '<div class="value-button increase" id="increase_' . $memberList->product_id . '" onclick="increaseValue(' . $memberList->product_id . ')" value="Increase Value" style="margin-top:-25px;"><ion-icon class="ion-ios-plus-outline" style="font-size: 24px;margin-right:10px;"></ion-icon>
					</div><input type="number" id="number_' . $memberList->product_id . '"  class="number product_qty" value="0.00" step="0.01" min="0" max="' . $memberList->quantity . '" required onblur="check_max(' . $memberList->product_id . ')">
					<div class="value-button decrease" id="decrease_' . $memberList->product_id . '" onclick="decreaseValue(' . $memberList->product_id . ')" value="Decrease Value" style="margin-top:-25px;"><ion-icon class="ion-ios-minus-outline" style="font-size: 24px;"></ion-icon>
					</div>';
			})->
			addColumn('inven_bluecrab', function ($memberList) {
				return '<p data-field="bluecrab"
						style="padding-top:1.4px;display:block;cursor: pointer;"
						class=" btn-primary bg-bluecrab"
						data-toggle="modal"><a class="os-linkcolor" href="barcodeinventoryout/' . $memberList->product_name->systemid . '/' . $memberList->product_name->location_id . '" target="_blank" style="color:#fff;text-decoration: none;">O</a></p>';
			})->
		escapeColumns([])->
		make(true);
    }


    public function stockoutproduct(Request $request){
        
        $locationID = $request->id;
		$this->user_data = new UserData();
        $data = array();
		$invenotyCon = new InventoryController();
	
        $ids = merchantproduct::where('merchant_id',
            $this->user_data->company_id())->	
            pluck('product_id');
		
		$franchiseLocations = DB::table('franchisemerchant')->
			join('franchisemerchantloc','franchisemerchantloc.franchisemerchant_id','=','franchisemerchant.id')->
			where([
				'franchisemerchant.franchisee_merchant_id' => $this->user_data->company_id()
			])->
			whereNull('franchisemerchant.deleted_at')->
			whereNull('franchisemerchantloc.deleted_at')->
			pluck('location_id');
		
		$franchise_p_id = DB::table('franchiseproduct')->
				leftjoin('franchisemerchant',
					'franchisemerchant.franchise_id','=','franchiseproduct.franchise_id')->
				leftjoin('franchisemerchantloc','franchisemerchant.id',
					'=','franchisemerchantloc.franchisemerchant_id')->
				where([
					'franchisemerchant.franchisee_merchant_id' => $this->user_data->company_id(),
					'franchiseproduct.active' => 1
				])->
				whereNull('franchiseproduct.deleted_at')->
				select("franchiseproduct.*","franchisemerchantloc.location_id")->
				get();

		$f_ids = $franchise_p_id->pluck('product_id');

		if (!empty($location_ID)) {
			$franchise_p_id = $franchise_p_id->where('location_id', $location_ID);
			$f_loc =  $franchiseLocations->toArray();
			if (in_array($location_ID, $f_loc)) {
				$ids = $f_ids->toArray();
			}
		} else {
			$ids = array_merge($ids->toArray(),$f_ids->toArray());
		}

            
        $filtered_ids = array();
        foreach ($ids as $id) {
            if (product::where('name','<>','')->find($id)) {
                $filtered_ids[] = $id;
            }
        }

        if ($locationID) {
			$nonEmptyQty = locationproduct::where(['location_id' => $locationID],[
				'franchisee_merchant_id' => $this->user_data->company_id()])//,['quatity','>',0])
			->whereIn('product_id', $filtered_ids)
			->pluck('product_id');
            //dd($nonEmptyQty);
            $model = new OgFuel();

            $data = $model->whereIn('product_id', $nonEmptyQty)->
                with('product_name', 'og_fuel_price')->
                orderBy('created_at', 'asc')->
                latest()->get();
			
			$data = $data->filter(function($memberList) use ($locationID) {
				
				/*$lp = locationproduct::where(['location_id'=>$locationID,
					'product_id'=>$memberList->product_id,
					"franchisee_merchant_id"=>$this->user_data->company_id()
				])->get()->first();*/
				$lp = (float) number_format( app('App\Http\Controllers\InventoryController')->
					location_productqty($memberList->product_id,$locationID),2);

				return $lp > 0;
			});

        }
		return Datatables::of($data)->addIndexColumn()->
			addColumn('inven_pro_id', function ($memberList) {
				return $memberList->product_name->systemid;
				//return '<p data-field="og_product_id" style="cursor: pointer; margin: 0; text-align: center;"'. 'class="os-linkcolor doc_id" url="' .route("inventory.showstockreport",$memberList->product_name->systemid) .'">' . $memberList->product_name->systemid .'</p>';
			})->
			addColumn('inven_pro_name', function ($oilgaslist) {
				if (!empty($oilgaslist->product_name->thumbnail_1)) {
					$img_src = '/images/product/' . $oilgaslist->product_name->id . '/thumb/' . $oilgaslist->product_name->thumbnail_1;
					$img     = "<img src='$img_src' data-field='og_product_name' style=' width: 25px;height: 25px;display: inline-block;margin-right: 8px;object-fit:contain;'/>";
				} else {
					$img = "";
				}
				$txt = $img . '<p class="os-linkcolor" data-field="og_product_name" 
				style="cursor: pointer; margin: 0;display:inline-block" 
				onclick="details(' . $oilgaslist->product_name->systemid . ')">';
				$txt .= (!empty($oilgaslist->product_name->name)) ? $oilgaslist->product_name->name .' '  : 'Product Name';
				//$txt .= ($oilgaslist->og_fuel_price) ? ' (' . number_format($oilgaslist->og_fuel_price->price,  2) .')' : '---' . '</p>';
				return $txt;
			})->
			addColumn('inven_pro_colour', function ($memberList) {
			$product_color = productcolor::join('color', 'productcolor.color_id', '=', 'color.id')
				->where('productcolor.product_id', $memberList->product_id)->first();
			if ($product_color) {
				return $product_color->name;
			}
			return "-";
			})->
			addColumn('inven_pro_matrix', function ($memberList) {
				return '-';
			})->
			addColumn('inven_pro_rack', function ($memberList) {
			// if (count($memberList->rack) <= 0) {
			// 	return '-';
			// }
			return '<div style="cursor: pointer;"
					class="rack_list" id="' . $memberList->product_id . '" onclick="open_rack(' . $memberList->product_id . ',' . $memberList->first_product . ')">' . (($memberList->rack_no) ? $memberList->rack_no : "-") . '</div>';
		})->
			addColumn('inven_pro_existing_qty', function ($memberList)  use ($locationID)  {
				
				/*$lp = locationproduct::where(['location_id'=>$locationID,
					'product_id'=>$memberList->product_id,
					"franchisee_merchant_id"=>$this->user_data->company_id()
				])->get()->first();*/

				$lp = (float) number_format( app('App\Http\Controllers\InventoryController')->
					location_productqty($memberList->product_id,$locationID),2);
				
				if($lp)
				 return '<label value="' . $lp . '" id="existing_qty_' . $memberList->product_id . '">'.$lp.'</label>';
				else
				 return '<label value="' . 0 . '" id="existing_qty_' . $memberList->product_id . '">0.00</label>';
			})->
			addColumn('inven_pro_qty', function ($memberList) use ($locationID) {
				$lp = locationproduct::where(['location_id'=>$locationID,'product_id'=>$memberList->product_id])->get()->first();
				
				if($lp)
				return '<div class="value-button increase" id="increase_' . $memberList->product_id . '" onclick="increaseValue(' . $memberList->product_id . ')" value="Increase Value" style="margin-top:-25px;"><ion-icon class="ion-ios-plus-outline" style="font-size: 24px;margin-right:10px;"></ion-icon>
					</div><input type="number" id="number_' . $memberList->product_id . '"  class="number product_qty" value="0.00" step="0.01"  min="0" max="' . $memberList->quantity . '" required onblur="check_max(' . $memberList->product_id . ')">
					<div class="value-button decrease" id="decrease_' . $memberList->product_id . '" onclick="decreaseValue(' . $memberList->product_id . ')" value="Decrease Value" style="margin-top:-25px;"><ion-icon class="ion-ios-minus-outline" style="font-size: 24px;"></ion-icon>
					</div>';
				else return '<div class="value-button increase" id="increaseddd_' . $memberList->product_id . '" onclick="increaseValue(' . $memberList->product_id . ')" value="Increase Value" style="margin-top:-25px;"><ion-icon class="ion-ios-plus-outline" style="font-size: 24px;margin-right:10px;"></ion-icon>
				</div><input type="number" id="number_' . $memberList->product_id . '"  class="number product_qty" value="0"  min="0" max="' . $memberList->quantity . '" required onblur="check_max(' . $memberList->product_id . ')"' . '"onchange="update_qty(' . $memberList->product_id . ')" disabled>
				<div class="value-button decrease" id="dddecrease_' . $memberList->product_id . '" onclick="decreaseValue(' . $memberList->product_id . ')" value="Decrease Value" style="margin-top:-25px;"><ion-icon class="ion-ios-minus-outline" style="font-size: 24px;"></ion-icon>
				</div>';
			})->
			addColumn('inven_bluecrab', function ($memberList) {
				return '<p data-field="bluecrab"
						style="padding-top:1.4px;display:block;cursor: pointer;"
						class=" btn-primary bg-bluecrab"
						data-toggle="modal"><a class="os-linkcolor" href="barcodeinventoryout/' . $memberList->product_name->systemid . '/' . $memberList->product_name->location_id . '" target="_blank" style="color:#fff;text-decoration: none;">O</a></p>';
			})->
		escapeColumns([])->
		make(true);

    }

	function showProductStockInView() {
		$id = Auth::user()->id;
        $user_roles = usersrole::where('user_id',$id)->get();

        $is_king =  \App\Models\Company::where('owner_user_id',
			Auth::user()->id)->first();

		$analyticsController = new AnalyticsController();
		$branch_location = [];
		$get_location = $analyticsController->get_location();
		foreach ($get_location as $key => $val) {
			$$key = $val;
			$location_id = array_column($branch_location, 'id');
			foreach($val as $location) {
				if (!in_array($location->id,$location_id)){
					$branch_location = array_merge($branch_location, [$location]);
				}
			}
		}

		$location = $branch_location;
        $data = array();
		$this->user_data = new UserData();
		$model = new OgFuel();
	
		$ids = merchantproduct::where('merchant_id',
			$this->user_data->company_id())->	
			pluck('product_id');
	
		/*
			$ids = product::where('ptype', 'oilgas')->
			whereIn('id', $ids)->pluck('id');
		*/
	
		$data = $model->whereIn('product_id', $ids)->
			orderBy('created_at', 'asc')->
			latest()->get();

        return view('industry.oil_gas.og_productstockin', [
			'user_roles'=>$user_roles,
			'is_king'=>$is_king,
			'location'=>$location
		]);
    }
 

    function showProductStockOutView() {
//        $product = OgFuel::where('id', $id)->first();
        $id = Auth::user()->id;
        $user_roles = usersrole::where('user_id',$id)->get();
        $is_king =  \App\Models\Company::where('owner_user_id',
			Auth::user()->id)->first();
   
		$analyticsController = new AnalyticsController();
		$branch_location = [];
		$get_location = $analyticsController->get_location();
		foreach ($get_location as $key => $val) {
			$$key = $val;
			$location_id = array_column($branch_location, 'id');
			foreach($val as $location) {
				if (!in_array($location->id,$location_id)){
					$branch_location = array_merge($branch_location, [$location]);
				}
			}
		}

		$location = $branch_location;

		return view('industry.oil_gas.og_productstockout', [
			'location'=>$location,
			'user_roles'=>$user_roles,
			'is_king'=>$is_king
		]);
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
            $product->ptype    = 'oilgas';
            $product->save();

            $oilgas             = new OgFuel();
            $oilgas->product_id = $product->id;
            $oilgas->save();

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
    

    public function showEditModal(Request $request)
    {
      
        try {
            $allInputs = $request->all();
            $id        = $request->get('id');
            $fieldName = $request->get('field_name');
           // dd($allInputs);

            $validation = Validator::make($allInputs, [
                'id'         => 'required',
                'field_name' => 'required',
            ]);

            if ($validation->fails()) {
                $response = (new ApiMessageController())->
                    validatemessage($validation->errors()->first());

            } else {

                $oilgas = OgFuel::where('id', $id)->first();
                // dd($oilgas);
                return view('industry.oil_gas.og_oilgas-modals', compact(['id', 'fieldName', 'oilgas']));
                // dd($request);
            }

        } catch (\Illuminate\Database\QueryException $ex) {
            $response = (new ApiMessageController())->queryexception($ex);
        }
    }


    public function update(Request $request)
    {
        try {
            $allInputs = $request->all();
            $og_product_id       = $request->get('og_product_id');
            $changed = false;

            $validation = Validator::make($allInputs, [
                'og_product_id'         => 'required',
            ]);

            if ($validation->fails()) {
                throw new Exception("product_not_found", 1);
            }

             $oilgas = OgFuel::find($og_product_id);

             if (!$oilgas) {
                throw new Exception("product_not_found", 1);
            }

            if ($request->has('litre')) {

                if ($oilgas->litre != (int) str_replace('.','',$request->litre)) {
                    $oilgas->litre = (int) str_replace('.','',$request->litre);
                    $changed = true;
                    $msg = "Litre updated";
                }
            }

            if ($changed == true) {
                $oilgas->save();
                $response = view('layouts.dialog', compact('msg'));
            } else {
                $response  = null;
            }

        }  catch (\Exception $e) {
			$msg = $e->getMessage();
            if ($msg == 'product_not_found') {
                $msg = "Product not found";
            } else if ($msg == 'invalid_cost') {
                $msg = "Invalid cost";
            } 

            // $msg = $e;
            $response = view('layouts.dialog', compact('msg'));
        }

        return $response;
    }

    public function destroy($id)
    {
        try {
            $this->user_data = new UserData();
            $oilgas          = OgFuel::find($id);
			
            $product_id      = $oilgas->product_id;

            $is_exist = merchantproduct::where([
				'product_id' => $product_id,
				'merchant_id' => $this->user_data->company_id()
			])->first();

            if (!$is_exist) {
                throw new Exception("Error Processing Request", 1);
            }

            $is_exist->delete();
            product::find($product_id)->delete();
            $oilgas->delete();

            $msg = "Product deleted successfully";
            return view('layouts.dialog', compact('msg'));

        } catch (\Illuminate\Database\QueryException $e) {
            $msg = $e->getMessage();

            return view('layouts.dialog', compact('msg'));
        }
    }
	
	public function showFuelPrices(Request $request)
    {
		$this->user_data = new UserData();
        $model = new OgFuelPrice();
		$og_fuel_id       = $request->get('ogFuelId');
		
        $data = $model->where('ogfuel_id', $og_fuel_id)->
			orderBy('created_at', 'desc')->
            latest()->get();
		
		$checkFuel = $model->where('ogfuel_id', $og_fuel_id)->
			orderBy('id', 'desc')->
            first();
			
		$enableButton = true;
		if(!empty($checkFuel->start) && !empty($checkFuel->price)){
			$enableButton = false;
		}
		foreach ($data as $key => $value) {
			$data[$key]['username'] = $value->user_name->name;
			$data[$key]['enableButton'] = $enableButton;
		}
		$myJSON = json_encode($data); 
		return $myJSON;	
	}

    public function storeFuel(Request $request){
        try {
            $og_fuelManage = new OgFuelMovement();

            if (empty($request->fuel_prod_id)) {
                $msg = "Please select a fuel";
                return view('layouts.dialog', compact('msg'));
                return false;
            }

            if (empty($request->location_id)) {
                $msg = "Please select a location";
                return view('layouts.dialog', compact('msg'));
                return false;
            }

            $og_fuelManage->location_id = $request->location_id;
            $og_fuelManage->fuel_prod_id = $request->fuel_prod_id;
            $og_fuelManage->date = date('Y-m-d H:i:s');
            $og_fuelManage->cforward = $request->cforward;
            $og_fuelManage->sales = "";
            $og_fuelManage->receipt = "";
            $og_fuelManage->book = "";
            $og_fuelManage->tank_dip = "";
            $og_fuelManage->daily_variance = "";
            $og_fuelManage->cumulative = "";
            $og_fuelManage->percentage = "";
            $og_fuelManage->save();

            $msg = "Fuel added successfully";
            return view('layouts.dialog', compact('msg'));

            //return $request->location_id."-".$request->fuel_prod_id."-".$request->cforward;

        } catch (\Exception $e) {
            $msg = $e->getMessage();
            return view('layouts.dialog', compact('msg'));
        }

    }    

    private function StockInCarryForward($location_id ,  $product_id , $cforward){

				$user_data 		= 	new UserData();
				$merchant_id	=	$user_data->company_id();

                $stock_system = DB::select("select nextval(stockreport_seq) as index_stock");
                $stock_system_id = $stock_system[0]->index_stock;
                $stock_system_id = sprintf("%010s", $stock_system_id);
                $stock_system_id = '111' . $stock_system_id;

                // $locationproduct = locationproduct::where(['location_id' => $location_id, 'product_id' => $product_id ])->orderby('id', 'desc')->first();
                // //dd($value['qty']);
                // if ($locationproduct) { // modify existing location product
                //     $curr_qty = $locationproduct->quantity;
                //     $curr_qty += $cforward;
                //     $locationproduct->quantity = $curr_qty;
                //     $locationproduct->save();
                // } else { // save new location productvalue['product_id']
                //     $product = new locationproduct();
                //     $product->product_id = $product_id;
                //     $product->location_id = $location_id;
                //     $product->quantity = $cforward;
                //     $product->save();
                // }
                // $og_fuel_mov = OgFuelMovement::where(['location_id'=>$location_id,'ogfuel_id'=> $product_id])->get()->first();
                // if($og_fuel_mov){ 
                //     $og_fuel_mov->receipt += $cforward;
                //     $og_fuel_mov->book = ($og_fuel_mov->cforward - $og_fuel_mov->sales) + $og_fuel_mov->receipt;
                //     $og_fuel_mov->date = now();
                //     $og_fuel_mov->save();
                // } else {
                //     OgFuelMovement::create([
                //         'location_id' => $location_id,
                //         'ogfuel_id' => $product_id,
                //         'receipt' => $cforward,
                //         'date'  => now()
                //     ]);
                // }
                /* saving stockreport  &&  stockreportproduct */

                $stock = new StockReport();
                $stock->creator_user_id = Auth::user()->id;
                $stock->type = 'cforward';
                $stock->systemid = $stock_system_id;
             //   $stock->quantity =  $cforward;
              //  $stock->product_id = $product_id;
                $stock->status = 'confirmed';
                $stock->location_id = $location_id;
                $stock->save();

                /* saving stockreport  &&  stockreportproduct */
                $stockreportproduct = new stockreportproduct();
                $stockreportproduct->quantity =$cforward;
                $stockreportproduct->stockreport_id = $stock->id;
                $stockreportproduct->product_id = $product_id;
                $stockreportproduct->status = 'confirmed';
				$stockreportproduct->save();
				
				DB::table('stockreportmerchant')->insert([
					"stockreport_id"			=>	$stock->id,
					'franchisee_merchant_id'	=>	$merchant_id,
					"created_at"				=>	date("Y-m-d H:i:s"),
					"updated_at"				=>  date("Y-m-d H:i:s")
				]);

                return 1;
                /* ------------------------------------- */
    }
    
    public function storeFuelMovement(Request $request){
        try {    
			// This is actually product_id
            if (empty($request->fuel_prod_id)) {
                $msg = "Please select a fuel";
                return view('layouts.dialog', compact('msg'));
                return false;
            }
            if (empty($request->location_id)) {
                $msg = "Please select a location";
                return view('layouts.dialog', compact('msg'));
                return false;
			}
			$user_data = new UserData();
            $current_day = date('Y-m-d');

            /*added by Udemezue  for selecting the actual value of ogfuel_id*/
            $og_fuel = Ogfuel::where("product_id",
				$request->fuel_prod_id)->first();

            $og_fuel_id = $og_fuel ? $og_fuel->id : '';

            // selecting this day's fuel movement
            $og_fuelManage = OgFuelMovement::where([
                    ['ogfuel_id' , $og_fuel_id],
					['location_id' , $request->location_id],
					['franchisee_merchant_id' , $user_data->company_id()]
                ])->whereBetween('updated_at', [
					date($current_day. ' 00:00:00'),
					date($current_day.' 23:59:59')
				])->orderBy('updated_at','DESC')->
				get()->first();

            if($og_fuelManage){
                $number='';
                $data = explode(',' , $request->cforward);
                for($i=0; $i<count($data); $i++){
                    $number=$number.''.$data[$i];
                }
                $cf_converted = (float)$number;
                $sales = $this->_getFuelSales($request->fuel_prod_id,
					Carbon::parse($og_fuelManage->date)->format('Y-m-d'));

                $receipt = $this->_getFuelReceipt($request->fuel_prod_id,
					Carbon::parse($og_fuelManage->date)->format('Y-m-d'));

                $book = $this->_getFuelBook($cf_converted, $sales , $receipt);

                if ($request->tank_dip) {
                    // Modify tank dip of selected fuel movementog_fuelManage
					$og_fuelManage->tank_dip = $request->tank_dip;
					$og_fuelManage->daily_variance =
						($og_fuelManage->tank_dip - $book);
					$og_fuelManage->book = $book;
					$og_fuelManage->save();
                } else {
                    // modify c/forward
                    $cf_converted = (float)$request->cforward * 1000;
                    $og_fuelManage->date = date('Y-m-d H:i:s');
                    $og_fuelManage->cforward = $cf_converted;
                    $og_fuelManage->sales = $sales; 
                    $og_fuelManage->receipt = $receipt;
                    $og_fuelManage->book = $book;
                    $og_fuelManage->save();
                }
                
                $variance =$this->_variance($og_fuelManage->ogfuel_id ,
					$og_fuelManage->location_id);
                $percentage =$this->_percentage($og_fuelManage->ogfuel_id,
					$og_fuelManage->location_id);
                $og_fuelManage->cumulative = $variance;
                $og_fuelManage->percentage = $percentage;
                $og_fuelManage->update();
            } else {
                $cf_converted = (float)$request->cforward;
                // create new fuel movement for the first time
                // $sales = $this->_getFuelSales($request->fuel_prod_id, date('Y-m-d'));
                // $receipt = $this->_getFuelReceipt($request->fuel_prod_id, date('Y-m-d'));
                // $book = $this->_getFuelBook($cf_converted, $sales , $receipt);
            ///  changes here
                // OgFuelMovement::create([
                //     'location_id'   => $request->location_id,
                //     'ogfuel_id'     => $request->fuel_prod_id,
                //     'date'          => date('Y-m-d H:i:s'),
                //     'cforward'      => $cf_converted,
                //     'sales'         => $sales,
                //     'receipt'       => $receipt,
                //     'book'          => $cf_converted,
                // ]);
                
                OgFuelMovement::create([
                    'location_id'   => $request->location_id,
                    'ogfuel_id'     => $og_fuel_id,
					'franchisee_merchant_id' => $user_data->company_id(),
                    'date'          => date('Y-m-d H:i:s'),
                    'cforward'      => $cf_converted,
                    'sales'         => 0.00,
                    'receipt'       => 0.00,
                    'book'          => $cf_converted,
                ]);

                /* 
                    This block was added by Udemezue.
                    what it actually does is to create a initial start stock
					in locationproduct
                */
                locationproduct::create([
                    'location_id'			 => $request->location_id,
                    'product_id'			 => $request->fuel_prod_id,
                    'quantity'				 => $request->cforward,
					'franchisee_merchant_id' => $user_data->company_id()
                ]);
            }
            
            if(isset($request->tank_dip) && $request->tank_dip == 0){
                $this->StockInCarryForward($request->location_id ,
					$request->fuel_prod_id , $request->cforward);
            }

            $msg = "Fuel added successfully";
            return view('layouts.dialog', compact('msg'));

            //return $request->location_id."-".$request->fuel_prod_id."-".$request->cforward;

        } catch (\Exception $e) {
            $msg = $e->getMessage();
            return view('layouts.dialog', compact('msg'));
        }
    }


    public function _variance($fuel_id , $location_id){
		$user_data 	= new UserData();
		$merchantId = $user_data->company_id();	

        $og_fuelManage = OgFuelMovement::where([
                            ['ogfuel_id' , $fuel_id],
							['location_id' , $location_id],
							['franchisee_merchant_id',$merchantId]
                        ])
                        ->orderBy('updated_at','desc')->get();
        $variance =0;
        foreach($og_fuelManage as $row){
            $variance = $row->daily_variance + $variance;
        }
        return $variance;
    }

    public function _percentage($fuel_id , $location_id){

		$user_data 	= new UserData();
		$merchantId = $user_data->company_id();	
		
		$og_fuelManage = OgFuelMovement::where([
			['ogfuel_id' , $fuel_id],
			['location_id' , $location_id],
			['franchisee_merchant_id',$merchantId]
		])->orderBy('updated_at','desc')->get();
        $book=0;
        foreach($og_fuelManage as $row){
            $book =$book + $row->book;
        }
        
        $end = end($og_fuelManage);
        $percentage = $book == 0 ? 0 : $end[0]->daily_variance/($book)*100;
        return $percentage;
    }


    function _getFuelSales($product_id , $date){
		$user_data 	= new UserData();
		$merchantId = $user_data->company_id();	
		
		$sold_products = opos_receiptproduct::select("opos_receiptproduct.*")
				->leftjoin('opos_receipt','opos_receipt.id','=','opos_receiptproduct.receipt_id')
				->leftjoin('staff','staff.user_id','=','opos_receipt.staff_user_id')
				->where('staff.company_id',$merchantId)
				->where([['opos_receiptproduct.product_id',$product_id]])
				->whereBetween('opos_receiptproduct.updated_at', [
					date($date. ' 00:00:00'), date($date.' 23:59:59')
				])
				->pluck('opos_receiptproduct.quantity');

        $sales = 0;
        foreach ($sold_products as $key => $value) {
            $sales += (float)$value;
        }
        return $sales;
    }

    
    function _getFuelReceipt($product_id , $date){
		$user_data 	= new UserData();
		$merchantId = $user_data->company_id();

        $loc_product = stockreportproduct::select('stockreportproduct.quantity')
			->join('stockreport' ,'stockreportproduct.stockreport_id','stockreport.id')
		    ->join('stockreportmerchant','stockreportmerchant.stockreport_id','=','stockreport.id')
			->where('stockreportmerchant.franchisee_merchant_id',$merchantId)
            ->where([['stockreportproduct.product_id',$product_id]])
            ->where('stockreport.type','!=','cforward')
            ->whereBetween('stockreportproduct.updated_at', [date($date. ' 00:00:00'), date($date.' 23:59:59')])->get();
        $receipt = 0;
        foreach ($loc_product as $value) {
            $receipt += (float)$value->quantity;
        }
        return $receipt;


    }

    function _getFuelBook($cforward, $sales , $receipt) {
        return (($cforward - $sales) + $receipt);
    }


    public function storeFuelPrices(Request $request)
    {
        //Create a new product here
        try {
            $this->user_data = new UserData();
			$og_fuel_id       = $request->get('ogFuelPriceId');
			
            $merchantproduct = new merchantproduct();
			//$SystemID        = new SystemID('product');

            $ogFuelPrice            = new OgFuelPrice();
            $ogFuelPrice->ogfuel_id = $og_fuel_id;
            $ogFuelPrice->user_id = Auth()->user()->id;
            $tomorrow = date("Y-m-d", strtotime("+ 1 day"));
            $tomorrow = date_create($tomorrow);
            $tomorrow = date_format($tomorrow, 'Y-m-d 00:00:01');
            $ogFuelPrice->start = $tomorrow;
            $ogFuelPrice->save();

            $msg = "Product added successfully";
            return view('layouts.dialog', compact('msg'));

        } catch (\Exception $e) {
            $msg = $e;
            return view('layouts.dialog', compact('msg'));
        }
    }
	
	public function updateFuelPrices(Request $request)
    {
        //Create a new product here
        try {
            $this->user_data = new UserData();
			$og_fuel_id       = $request->get('ogFuelPriceId');
            $price       = 		(int)str_replace(',', '',str_replace('.', '',number_format($request->get('ogFuelPrice'), 2)));
								
            $ogFuelPrice = OgFuelPrice::find($og_fuel_id);
			
			$ogFuelPrice->price = $price;
            $ogFuelPrice->save();
			$msg = "Price updated successfully";
            return view('layouts.dialog', compact('msg'));

        } catch (\Exception $e) {
            $msg = $e;
            return view('layouts.dialog', compact('msg'));
        }
    }
	
	public function updateStartDateFuelPrices(Request $request)
    {
		Log::debug('updateStartDateFuelPrices()');

        //Create a new price here
        try {
			$this->user_data = new UserData();
			
			//Get Company ID
			$og_fuel_id = $request->get('ogFuelPriceId');
            $startDate  = $request->get('startDate');
            $ogFuelPrice = OgFuelPrice::find($og_fuel_id);
			
			$date = date_create($startDate);
			$date = date_format($date, 'Y-m-d H:i:s');
			$ogFuelPrice->start =  $date;
			$ogFuelPrice->save();

			$setdate = date('Y-m-d', strtotime($date)); 
        	$now = \Carbon\Carbon::now()->toDateString();
     
			
			$date = DB::table('og_fuelprice')->where('id',$og_fuel_id)
				->select('updated_at')
				->first();
			
		
				$product = DB::table('prd_ogfuel')->
				join('product', 'product.id', 'prd_ogfuel.product_id')
				->where('prd_ogfuel.id', $ogFuelPrice->ogfuel_id)
				->select('product.*')->first();
			
			Log::debug('product='.json_encode($product));

			$ipLocation = 	DB::table('product')
				->leftjoin('franchiseproduct', 'franchiseproduct.product_id', '=',  'product.id')
				->leftjoin('franchisemerchant', 'franchisemerchant.franchise_id', '=',  'franchiseproduct.franchise_id')
				->leftjoin('locationipaddr', 'locationipaddr.company_id', '=',  'franchisemerchant.franchisee_merchant_id')
				->leftjoin('location', 'locationipaddr.location_id', '=',  'location.id')
				->leftjoin('franchisemerchantloc', 'franchisemerchantloc.location_id', '=',  'location.id')
				->leftjoin('franchisemerchantloc as fml', 'franchisemerchant.id', '=',  'fml.franchisemerchant_id')
				->where('product.id', $product->id )
				->select('locationipaddr.ipaddr','locationipaddr.location_id', 'location.branch','locationipaddr.tsystem')
				->groupBy('location.id')
				->get();
			Log::debug('ipLocation='.json_encode($ipLocation));
				
			if($ipLocation){
				$endpoint = '/interface/update/product/price';
				$call = new APIFranchiseController($endpoint);

				foreach($ipLocation as $location){
					Log::debug('xlocation='.json_encode($location));
					Log::debug('xipaddr='.$location->ipaddr);
					if (empty($location->ipaddr)) {
						continue;
					}
					//Make a call
					 
					$payload = array('price'=>$ogFuelPrice->price,
						'product_id'=>$product->id,
						'prdcategory_id'=>$product->prdcategory_id,
						'prdsubcategory_id'=>$product->prdsubcategory_id,
						'ptype'=>$product->ptype,
						'location_id'=>$location->location_id,
						'brand_id'=>$product->brand_id,
						'company_id'=>$this->user_data->company_id(),
						'username'=> $request->user()->name,
						'product_name'=>$product->name,
						'product_systemid'=>$product->systemid,
						'userid'=>$request->user()->id,
						'time'=>$date->updated_at
					);

					$payload = json_encode($payload);
					
					Log::debug($payload);
					Log::debug(json_encode($location));
					$select_web_string = DB::table('locationipaddr')->
						where('ipaddr', $location->ipaddr)->
						pluck('tsystem')->
						first();
					Log::debug($select_web_string);
					Log::debug($setdate);
					Log::debug($now);
					if ($setdate == $now){
						$response = $call->sendToOceania($select_web_string,
							$payload);
					}
				}
			}

			$msg = "Start updated successfully";
		
        } catch (\Exception $e) {
            $msg = $e;
			Log::error($msg);
        }

		return view('layouts.dialog', compact('msg'));
    }


    public function showFuelManagement_tables(Request $request){

		$location_ID = $request->locID;
		$user_data  = new UserData();
		$merchantId = $user_data->company_id();
		$min_date = $request->min_date;
        //$ogfuel_id = $request->ogfuel_id;

        /*added by Udemezue  for selecting the actual value of ogfuel_id*/
		$og_fuel = Ogfuel::where("product_id", $request->ogfuel_id)->
			first();
        $og_fuel_id = $og_fuel ? $og_fuel->id : '';
        
        if(!empty($location_ID) && !empty($og_fuel_id)){
			
			$model = OgFuelMovement::where([
				['location_id',$location_ID],
				['ogfuel_id',$og_fuel_id],
				['franchisee_merchant_id',$merchantId]
			])->
			orderBy('date','desc')->get();
		
			$model = $model->filter(function($f) use ($min_date) {
				if (strtotime($f->date) >=	strtotime("$min_date-01 00:00:00") && 
						strtotime($f->date) <=  strtotime("$min_date-31 23:59:59"))  {
						return true;
					}

					return false;
			});

			$latest =  $model->first();
		   
			if($latest){
                $latest->daily_variance = ($latest->tank_dip - $latest->book);
                $latest->cumulative = $this->_variance($latest->ogfuel_id, $latest->location_id);
                $latest->percentage = $this->_percentage($latest->ogfuel_id, $latest->location_id);
                $latest->update();
            }

            return Datatables::of($model)
                ->addIndexColumn()
                ->addColumn('date', function ($fuel_list) {
                    return '<p  id="fuel_date" style="margin: 0; text-align: center;">' . Carbon::parse($fuel_list->date)->format('dMy H:i:s') . '</p>';
                })
                ->addColumn('cforward', function ($fuel_list) {
                    return '<p  id="fuel_cforward" style="margin: 0; text-align: right;" value="'.number_format($fuel_list->cforward,2).'">' . number_format($fuel_list->cforward,2).'</p>';
                })
                ->addColumn('sales', function ($fuel_list) {
                    $og_fuel = OgFuel::where("id", $fuel_list->ogfuel_id)->first();
					return '<p id="fuel_receipt" class="os-linkcolor fuel_receipt" data-field="' .( 
						(!empty($fuel_list->sales) && $fuel_list->sales == 0 ) ? $fuel_list->sales : "" ). 
						'" url="' .route("get_industry_oil_gas_product_ledger_index_view_sales",[
							'oilgas_product_id' => $og_fuel->product_id, 'location_id' => $fuel_list->location_id
						]) . '" day="' .Carbon::parse($fuel_list->date)->format('Y-m-d') . 
						'" style="cursor: pointer; margin: 0;float:right; text-align:center;" >'. 
						number_format($fuel_list->sales,2) .'</p>';
                })
                ->addColumn('receipt', function ($fuel_list) {
					$og_fuel = OgFuel::where("id", $fuel_list->ogfuel_id)->first();
					$id = $og_fuel->product_id;
					$location_id = $fuel_list->location_id;
					$receiptDate = collect();
					$receiptDate->day = (Carbon::parse($fuel_list->date)->format('Y-m-d'));
					$stockreportproducts = $this->getStockReportProducts($id, $receiptDate , true , $location_id);
					$opos_product = $this->getOposreceiptproducts($id, $receiptDate , true ,$location_id);
					$data = $stockreportproducts->merge($stockreportproducts)->map(function ($item){
						$item->table_name = class_basename($item);
						return $item;
					})->sortByDesc('updated_at');

					$quantity= 0;
					foreach($data as $value) {
		  				$filtertypes =['Voided','Transfer','Stocktake','cforward','Refundcp'];
						if($value->table_name == 'stockreportproduct' && !in_array($value->stock_report->type, $filtertypes)){
								$quantity += $value->quantity ? $value->quantity: 0;
						}
					}

					return '<p id="fuel_receipt" class="os-linkcolor fuel_receipt" data-field="' .( (
						!empty($fuel_list->receipt) && $fuel_list->receipt == 0 ) ? $fuel_list->receipt : "" ). 
						'" url="' .route("get_industry_oil_gas_product_ledger_index_view_receipt",[
							'oilgas_product_id' => $og_fuel->product_id , 'location_id' => $fuel_list->location_id
						]) . '" day="' .Carbon::parse($fuel_list->date)->format('Y-m-d') .
					'" style="cursor: pointer;margin: 0;float:right; text-align:center;" >'.
						number_format($quantity,2) .'</p>';
                })
                ->addColumn('book', function ($fuel_list) {
                    return '<p id="fuel_book" data-field="' .( (!empty($fuel_list->book) && $fuel_list->book == 0 ) ? $fuel_list->book : "" ). '"  style="margin: 0;float:right; text-align:center;" >'.number_format($fuel_list->book,2) .'</p>';
                })
                ->addColumn('tank_dip', function ($fuel_list) {
                    return '<p id="fuel_tankDIp" data-field="' .( (!empty($fuel_list->tank_dip) && $fuel_list->tank_dip == 0 ) ? $fuel_list->tank_dip : "" ). '" style="margin: 0;float:right; text-align:center;" >'. number_format($fuel_list->tank_dip,2).'</p>';
                })
                ->addColumn('daily_variance', function ($fuel_list) {
                    return '<p id="fuel_dailyVariance" data-field="' .( (!empty($fuel_list->daily_variance) && $fuel_list->daily_variance == 0 ) ? $fuel_list->daily_variance : "" ). '"  style="margin: 0;float:right; text-align:center;" >'. number_format($fuel_list->daily_variance,2) .'</p>';
                })
                ->addColumn('cumulative', function ($fuel_list) {
                    return '<p id="fuel_cumulative" data-field="' .( (!empty($fuel_list->cumulative) && $fuel_list->cumulative == 0 ) ? $fuel_list->cumulative : "" ). '"  style="margin:0;float:right; text-align:center;" >'.number_format($fuel_list->cumulative,2).'</p>';
                })
                ->addColumn('percentage', function ($fuel_list) {
                    return '<p id="fuel_cumulative" data-field="' .( (!empty($fuel_list->percentage) && $fuel_list->percentage == 0 ) ? $fuel_list->percentage : "" ). '"  style="margin: 0;float:right; text-align:center;" >'.number_format($fuel_list->percentage,2) .'</p>';
                })
                ->escapeColumns([])->
                make(true);
        }/*else{
            $model = OgFuelMovement::all();
            return Datatables::of($model)
                ->addIndexColumn()
                ->addColumn('date', function ($fuel_list) {
                    return '<p class="os-linkcolor" id="fuel_date" style="cursor: pointer; margin: 0; text-align: center;">' . Carbon::parse($fuel_list->date)->format('dMy H:i:s') . '</p>';
                })
                ->addColumn('cforward', function ($fuel_list) {
                    return '<p  id="fuel_cforward" style="margin: 0; text-align: right;">' .number_format($fuel_list->cforward,2) . '</p>';
                })
                ->addColumn('sales', function ($fuel_list) {
                    return '<p id="fuel_receipt" class="os-linkcolor" data-field="' .( (!empty($fuel_list->sales) && $fuel_list->sales == 0 ) ? $fuel_list->sales : "" ). '" style="cursor: pointer; margin: 0;float:right; text-align:center;" >'. number_format($fuel_list->sales,2) .'</p>';
                })
                ->addColumn('receipt', function ($fuel_list) {
                    return '<p id="fuel_receipt" class="os-linkcolor fuel_receipt" data-field="' .( (!empty($fuel_list->receipt) && $fuel_list->receipt == 0 ) ? $fuel_list->receipt : "" ). '" url="' .route("get_industry_oil_gas_product_ledger_index_view",$fuel_list->ogfuel_id) . '" day="' .Carbon::parse($fuel_list->date)->format('Y-m-d') . '" style="cursor: pointer; margin: 0;float:right; text-align:center;" >'.number_format($fuel_list->receipt,2).'</p>';
                })
                ->addColumn('book', function ($fuel_list) {
                    return '<p id="fuel_book" class="os-linkcolor" data-field="' .( (!empty($fuel_list->book) && $fuel_list->book == 0 ) ? $fuel_list->book : "" ). '"  style="cursor: pointer; margin: 0;float:right; text-align:center;" >'.number_format($fuel_list->book,2) .'</p>';
                })
                ->addColumn('tank_dip', function ($fuel_list) {
                    return '<p id="fuel_tankDIp" class="os-linkcolor" data-field="' .( (!empty($fuel_list->tank_dip) && $fuel_list->tank_dip == 0 ) ? $fuel_list->tank_dip : "" ). '" style="cursor: pointer; margin: 0;float:right; text-align:center;" >'. number_format($fuel_list->tank_dip,2).'</p>';
                })
                ->addColumn('daily_variance', function ($fuel_list) {
                    return '<p id="fuel_dailyVariance" class="os-linkcolor" data-field="' .( (!empty($fuel_list->daily_variance) && $fuel_list->daily_variance == 0 ) ? $fuel_list->daily_variance : "" ). '"  style="cursor: pointer; margin: 0;float:right; text-align:center;" >'. number_format($fuel_list->daily_variance,2) .'</p>';
                })
                ->addColumn('cumulative', function ($fuel_list) {
                    return '<p id="fuel_cumulative" class="os-linkcolor" data-field="' .( (!empty($fuel_list->cumulative) && $fuel_list->cumulative == 0 ) ? $fuel_list->cumulative : "" ). '"  style="cursor: pointer; margin: 0;float:right; text-align:center;" >'.number_format($fuel_list->cumulative,2).'</p>';
                })
                ->addColumn('percentage', function ($fuel_list) {
                    return '<p id="fuel_cumulative" class="os-linkcolor" data-field="' .( (!empty($fuel_list->percentage) && $fuel_list->percentage == 0 ) ? $fuel_list->percentage : "" ). '"  style="cursor: pointer; margin: 0;float:right; text-align:center;" >'.number_format($fuel_list->percentage,2) .'</p>';
                })
                ->escapeColumns([])->
                make(true);
        
        }*/
        return Datatables::of(array())->make(true);
    }


    public function showFuelManagement(){
		$user_data = new UserData();

		$date= Company::select(DB::raw('EXTRACT(MONTH FROM created_at) as \'month\', EXTRACT(YEAR FROM created_at) as \'year\''))->
			whereid($user_data->company_id())->
			get()->
			first();

        return view('industry.oil_gas.og_fuel-management',compact(['date']));
	}

	public function showDippingModel(Request $request) {
		try {
			$user_name		= new UserData();
			$merchant_id	= $user_name->company_id();
			$fuel_id 		= $request->fuel_id;
			$location_id	= $request->location_id;

			$product_detail = DB::table('prd_ogfuel')->
				join('product','product.id','=','prd_ogfuel.product_id')->
				select("prd_ogfuel.id as ogfuel_id","product.name")->
				where('product.id', $fuel_id)->
				first();

			$fuel_moment = OgFuelMovement::select("og_fuelmovement.*")->
				where([
          			['og_fuelmovement.location_id','=' , $location_id],
          			['og_fuelmovement.franchisee_merchant_id','=' , $merchant_id]
        		])->
				where('og_fuelmovement.ogfuel_id', $product_detail->ogfuel_id)->
				whereNull('og_fuelmovement.tank_dip')->
				orderBy('og_fuelmovement.updated_at','desc')->
				get();
			
			$fuel_moment->map(function($z) use ($product_detail) {
				$z->fuel_name  = $product_detail->name ?? 'Fuel Name';
			});
			
			return view('opossum.petrol_station.dipping_modal', compact('fuel_moment'));	

		} catch (\Exception $e) {
			\Log::info([
				"Error" => $e->getMessage(),
				"Line" 	=> $e->getLine(),
				"File" 	=> $e->getFile()
			]);
			abort(404);
		}
	}

	public function customLocation(Request $request) {
		try {
			$user_data = new UserData();
			$company_id = $user_data->company_id();

			if (!empty($request->loc_for_product)) {
			
				$loc_for_product = $request->loc_for_product;
				
				$is_own = DB::table('merchantproduct')->
					where([
						"product_id" 	=> $loc_for_product,
						"merchant_id" 	=> $company_id
					])->
					first();
			
				\Log::info([
					"PRODUCT ID"	=> $loc_for_product,
					"is own"		=> !empty($is_own)
				]);

			}

			if (empty($request->loc_for_product) || !empty($is_own)) {

				 $own_loc_ =  DB::select('SELECT
						l.branch,
						l.created_at,
						l.warehouse,
						l.foodcourt,
						l.id,
						c.name
					FROM
						company c,
						location l,
						merchantlocation ml
					WHERE
						c.id = '.$company_id.'
						AND ml.merchant_id = c.id
						AND ml.location_id = l.id
						AND l.branch is NOT NULL
						AND l.deleted_at is NULL;
				 ');

				 \Log::info([
					 "Result Loc" => $own_loc_
				 ]);

				 \Log::info('SELECT
						l.branch,
						l.created_at,
						l.warehouse,
						l.foodcourt,
						l.id,
						c.name
					FROM
						company c,
						location l,
						merchantlocation ml
					WHERE
						c.id = '.$company_id.'
						AND ml.merchant_id = c.id
						AND ml.location_id = l.id
						AND l.branch is NOT NULL
						AND l.deleted_at is NULL;
				 ');

				 return $own_loc_;
			}

			$franchiseLocations = DB::table('franchisemerchant')->
				join('franchisemerchantloc','franchisemerchantloc.franchisemerchant_id','=','franchisemerchant.id')->
				join('franchiseproduct','franchiseproduct.franchise_id','=','franchisemerchant.franchise_id')->
				where([
					'franchisemerchant.franchisee_merchant_id' 	=> $user_data->company_id(),
					'franchiseproduct.product_id'			 	=> $loc_for_product
					
				])->
				whereNull('franchisemerchant.deleted_at')->
				whereNull('franchisemerchantloc.deleted_at')->
				pluck('location_id')->toArray();
			

			$franchiseLocations = implode(",",$franchiseLocations);

			$location =   DB::select('SELECT
						l.branch,
						l.created_at,
						l.warehouse,
						l.foodcourt,
						l.id,
						c.name
					FROM
						company c,
						location l,
						merchantlocation ml
					WHERE
						l.id in ('.$franchiseLocations.')
						AND ml.merchant_id = c.id
						AND ml.location_id = l.id
						AND l.branch is NOT NULL
						AND l.deleted_at is NULL;
					');
				\Log::info([
					"Found Location"	=> $location
				]);
			return $location;

		} catch (\Exception $e) {
			\Log::info([
				"Error" => $e->getMessage(),
				"Line" 	=> $e->getLine(),
				"File" 	=> $e->getFile()
			]);
			abort(404);
		}
	}

	public function showOgProduct(){
		$color = [
				'#003380', '#008066', '#7a0099', '#990073', '#99004d',
				'#008000', '#6a6a48', '#804000', '#990000','#cccc00'		
				];
        return view('industry.oil_gas.og_product', compact("color") );
    }

	public function showOgProductRetail() {
		$color = [
				'#003380', '#008066', '#7a0099', '#990073', '#99004d',
				'#008000', '#6a6a48', '#804000', '#990000','#cccc00'		
				];
        return view('industry.oil_gas.og_product_retail', compact("color") );
	}
    
	public function showOgProductWholesale() {
		$color = [
				'#003380', '#008066', '#7a0099', '#990073', '#99004d',
				'#008000', '#6a6a48', '#804000', '#990000','#cccc00'		
			];
        return view('industry.oil_gas.og_product_wholesale', compact("color") );
	}

    public function showEditModalFuelPrice(Request $request)
    {
        
        try {
            $allInputs = $request->all();
            $id        = $request->get('id');
            $fieldName = $request->get('field_name');
            $oilgas = OgFuelPrice::where('id', $id)->first();
            return view('industry.oil_gas.og_fuelprice-modals', compact(['id', 'fieldName', 'oilgas']));
            

        } catch (\Illuminate\Database\QueryException $ex) {
            $response = (new ApiMessageController())->queryexception($ex);
        }
    }
    
    
    public function destroyFuelprice($id)
    {
        try {
            $this->user_data = new UserData();
            $oilgas          = OgFuelPrice::find($id);
            $oilgas->delete();

            $msg = "Fuel price deleted successfully";
            return view('layouts.dialog', compact('msg'));

        } catch (\Illuminate\Database\QueryException $ex) {
            $msg = "Some error occured";

            return view('layouts.dialog', compact('msg'));
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function destroyTank(Request $request)
    {
        try {
            $this->user_data = new UserData();
            $og_tank_id       = $request->get('TankID');

            $ogTankHeight = OgTank::where('systemid',$og_tank_id)->first();
		
			if (!empty($ogTankHeight)) {
            	$ogTankHeight->deleted_at = Carbon::now();
            	$ogTankHeight->save();
			}

            $msg = "Tank deleted successfully";
            return view('layouts.dialog', compact('msg'));
//            return view('layouts.dialog')->with(['msg'=>$msg]);

        } catch (\Exception $e) {
            $msg = $e;
            return view('layouts.dialog', compact('msg'));
//            return view('layouts.dialog')->with(['msg'=>$msg]);
        }
    }


    public function updateProductQuantitystock(Request $request)
	{
	
		try {
			$user_data = new UserData();
			$id = Auth::user()->id;
            $table_data = $request->get('table_data');
			$stock_type = $request->get('stock_type');
            $total_qty = 0;
            
            $stock_system = DB::select("select nextval(stockreport_seq) as index_stock");
			$stock_system_id = $stock_system[0]->index_stock;
			$stock_system_id = sprintf("%010s", $stock_system_id);
			$stock_system_id = '111' . $stock_system_id;
			/**/
			foreach ($table_data as $key => $value) {
				if ($value['qty'] <= 0) {
					continue;
				}
				$locationproduct = locationproduct::where([
					'location_id' 				=> $value['location_id'], 
					'franchisee_merchant_id'	=> $user_data->company_id(),
					'product_id'				=> $value['product_id']
				])->orderby('id', 'desc')->first();
                //dd($value['qty']);
                if ($locationproduct) { // modify existing location product
					$curr_qty = $locationproduct->quantity;
					if ($stock_type == "IN") {
                        $curr_qty += $value['qty'];
					} else {
						$curr_qty -= $value['qty'];
                    }
                    $locationproduct->quantity = $curr_qty;
                    $locationproduct->save();
				} else { // save new location product
                    
                    $product = new locationproduct();
                    $product->product_id = $value['product_id'];
                    $product->location_id = $value['location_id'];
                    $product->quantity = $value['qty'];
					$product->franchisee_merchant_id	= $user_data->company_id();
                    $product->save();
                }

                /*added by Udemezue  for selecting the actual value of ogfuel_id*/
                $og_fuel = Ogfuel::where("product_id", $value['product_id'])->first();
                $og_fuel_id = $og_fuel ? $og_fuel->id : '';

                /*$og_fuel_mov = OgFuelMovement::where(['location_id'=>$value['location_id'],'ogfuel_id'=>$og_fuel_id])->get()->first();*/

                $current_day = date('Y-m-d');

				$og_fuel_mov = OgFuelMovement::where([
					'location_id'	=>	$value['location_id'],
					'ogfuel_id'=>$og_fuel_id])->
					whereBetween('updated_at', [
						date($current_day. ' 00:00:00'), date($current_day.' 23:59:59')])->
						orderBy('updated_at','DESC')->get()->first();

                if($og_fuel_mov){ 

                    if ($stock_type == "IN") {
                        $og_fuel_mov->receipt += $value['qty'];
					} else {
                        $og_fuel_mov->receipt -= $value['qty'];
                    }

                    $og_fuel_mov->book = ($og_fuel_mov->cforward - $og_fuel_mov->sales) + $og_fuel_mov->receipt;
                    $og_fuel_mov->daily_variance = $og_fuel_mov->tank_dip - $og_fuel_mov->book;
                    $og_fuel_mov->date = now();

                    /*added by Udemezue this file trigers variance and percentage when SISO happens*/
                    $variance =$this->_variance($og_fuel_mov->ogfuel_id , $og_fuel_mov->location_id);
                    $percentage =$this->_percentage($og_fuel_mov->ogfuel_id , $og_fuel_mov->location_id);

                    $og_fuel_mov->cumulative = $variance;
                    $og_fuel_mov->percentage = $percentage;
                    $og_fuel_mov->update();

                } else {
                    OgFuelMovement::create([
                        'location_id' => $value['location_id'],
                        'ogfuel_id' => $og_fuel_id,
                        'receipt' => $value['qty'],
                        'date'  => now()
                    ]);
                }
                /* saving stockreport  &&  stockreportproduct */

                $stock = new StockReport();
                $stock->creator_user_id = Auth::user()->id;
                $stock->type = ($stock_type == 'IN') ? 3 : 4; //('voided', 'transfer', 'stockin', 'stockout', 'stocktake')
                $stock->systemid = $stock_system_id;
                $stock->status = 'confirmed';
                $stock->location_id = $value['location_id'];
                $stock->save();

                /* saving stockreport  &&  stockreportproduct */
				$stockreportproduct = new stockreportproduct();
				$stockreportproduct->quantity = ($stock_type == 'IN') ? $value['qty'] : '-' . $value['qty'];
				$stockreportproduct->stockreport_id = $stock->id;
				$stockreportproduct->product_id = $value['product_id'];
				$stock->status = 'confirmed';
                $stockreportproduct->save();
				
				DB::table('stockreportmerchant')->insert([
					"stockreport_id" 			=> $stock->id,
					"franchisee_merchant_id"	=> $user_data->company_id(),
					"created_at"				=> date("Y-m-d H:i:s"),
					"updated_at"				=> date("Y-m-d H:i:s")
				]);	

                $total_qty += $value['qty'];
			}
			if ($total_qty > 0) {
				if ($stock_type == "IN") {
					$msg = "Stock In performed succesfully";
				} else {
					$msg = "Stock Out performed succesfully";
				}
			} else {
				$msg = "Please fill in product quantity";
			}
			$data = view('layouts.dialog', compact('msg'));
			
		} catch (\Exception $e) {
		dd($e);
			$msg = "Error occured while saving stock";
			
			Log::error(
				"Error @ " . $e->getLine() . " file " . $e->getFile() .
				":" . $e->getMessage()
			);
			
			$data = view('layouts.dialog', compact('msg'));
		}
		return $data;
    }

    public function updateLocationProduct(Request $request){
        try{
            $id =Auth::user()->id;

			$userData	=	new UserData();
			$stock_system = new SystemID('stockreport');
			$stock_system_id = $stock_system->__toString();

            $table_data = $request->get('table_data');
            $stock_type = $request->get('stock_type');

            $product_id = $table_data[0]['product_id'];
            $location_id = $table_data[0]['location_id'];
            $quantity = $table_data[0]['qty'];

            if ($quantity > 0) {
                $current_location_product = locationproduct::orderBy("id", "desc")->where('product_id', $product_id)->where('location_id', $location_id)->first();

                if($current_location_product){
                    $current_location_product->update([
                        'quantity' => $current_location_product->quantity + $quantity,
                    ]);
                }
                else{
                    locationproduct::create([
                        'product_id' => $product_id,
                        'location_id' => $location_id,
                        'quantity' => $quantity,
						'franchisee_merchant_id' => $userData->company_id()
                    ]);
                }

                /*added by Udemezue  for selecting the actual value of ogfuel_id*/
                $og_fuel = Ogfuel::where("product_id", $product_id)->first();
                $og_fuel_id = $og_fuel ? $og_fuel->id : '';
                
                /*$og_fuel_mov = OgFuelMovement::where(['location_id'=>$location_id,'ogfuel_id'=>$og_fuel_id])->get()->first();
*/
                $current_day = date('Y-m-d');
                $og_fuel_mov = OgFuelMovement::where(['location_id'=>$location_id,'ogfuel_id'=>$og_fuel_id])->whereBetween('updated_at', [date($current_day. ' 00:00:00'), date($current_day.' 23:59:59')])
                    ->orderBy('updated_at','DESC')->get()->first();


                if($og_fuel_mov){
                    $og_fuel_mov->book = ($og_fuel_mov->cforward - $og_fuel_mov->sales) + $og_fuel_mov->receipt;

                    $og_fuel_mov->daily_variance = $og_fuel_mov->tank_dip - $og_fuel_mov->book;

                    $og_fuel_mov->date = now();

                    /*added by Udemezue this file trigers variance and percentage when SISO happens*/
                    $variance =$this->_variance($og_fuel_mov->ogfuel_id , $og_fuel_mov->location_id);
                    $percentage =$this->_percentage($og_fuel_mov->ogfuel_id , $og_fuel_mov->location_id);
                    $og_fuel_mov->cumulative = $variance;
                    $og_fuel_mov->percentage = $percentage;

                    $og_fuel_mov->update();
                } /*else {
                    OgFuelMovement::create([
                        'location_id' => $location_id,
                        'ogfuel_id' => $og_fuel_id,
                        'receipt' => $quantity,
                        'date'  => now()
                    ]);
                }*/
                /* saving stockreport  &&  stockreportproduct */

                $stock = new StockReport();
                $stock->creator_user_id = Auth::user()->id;
                $stock->type = ($stock_type == 'IN') ? 3 : 4; //('voided', 'transfer', 'stockin', 'stockout', 'stocktake')
                $stock->systemid = $stock_system_id;
                //$stock->quantity = $quantity;
                //$stock->product_id = $product_id;
                $stock->status = 'confirmed';
                $stock->location_id = $location_id;
                $stock->save();

                /* saving stockreport  &&  stockreportproduct */
                $stockreportproduct = new stockreportproduct();
                $stockreportproduct->quantity = $quantity;
                $stockreportproduct->stockreport_id = $stock->id;
                $stockreportproduct->product_id = $product_id;
                $stock->status = 'confirmed';
                $stockreportproduct->save();
                /* ------------------------------------- */
                
                $msg = "Stock In performed succesfully";
            } else {
                $msg = "Please fill in product quantity";
            }
            $data = view('layouts.dialog', compact('msg'));
            
        } catch (\Exception $e) {
            $msg = "Error occured while saving stock";
			
			\Log::error(
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
			$stock_system = DB::select("select nextval(stockreport_seq) as index_stock");
			$stock_system_id = $stock_system[0]->index_stock;
			$stock_system_id = sprintf("%010s", $stock_system_id);
			$stock_system_id = '111' . $stock_system_id;
			
			foreach ($table_data as $key => $value) {
				if ($value['qty'] <= 0) {
					continue;
				}
				
				$rackproduct = rackproduct::where('rack_id', '=', $value['rack_id'])->where('product_id', '=', $value['product_id'])->orderby('id', 'desc')->first();
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
				log::debug('curr_qty' . $curr_qty);
				log::debug('value:' . json_encode($value));
				$product = new rackproduct();
				$product->product_id = $value['product_id'];
				$product->rack_id = $value['rack_id'];
				$product->quantity = $curr_qty;
				$product->save();
				
				$stockreport = new StockReport();
				$stockreport->type = ($stock_type == 'IN') ? 3 : 4; //('voided', 'transfer', 'stockin', 'stockout', 'stocktake')
				$stockreport->creator_user_id = Auth::user()->id;
				$stockreport->systemid = $stock_system_id;
				$stockreport->quantity = ($stock_type == 'IN') ? $value['qty'] : '-' . $value['qty'];
				$stockreport->product_id = $value['product_id'];
				$stockreport->status = 'confirmed';
				$stockreport->location_id = $value['location_id'];
				$stockreport->save();
				
				$stockreportproduct = new stockreportproduct();
				$stockreportproduct->quantity = ($stock_type == 'IN') ? $value['qty'] : '-' . $value['qty'];
				$stockreportproduct->stockreport_id = $stockreport->id;
				$stockreportproduct->product_id = $value['product_id'];
				$stockreportproduct->save();
				
				$stockreportproductrack = new stockreportproductrack();
				$stockreportproductrack->stockreportproduct_id = $stockreportproduct->id;
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
				$msg = "Please fill in product quantity";
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

    public function getTodayFuelMovement($location_id, $ogfuel_id) {

		$user_data = new UserData();
		$merchantId = $user_data->company_id();

        $date = date('Y-m-d');
        $ogfuel_movement = OgFuelMovement::where([
            ['ogfuel_id' ,'=', $ogfuel_id],
			['location_id','=' , $location_id],
			['franchisee_merchant_id','=',$merchantId]
        ])
        ->whereBetween('updated_at', [date($date. ' 00:00:00'), date($date.' 23:59:59')])
        ->get()->first();
        if($ogfuel_movement) return true;

        return false;
    }

    public function checkFuelMovementExist($location_id, $ogfuel_id) {

		$user_data = new UserData();
		$merchantId = $user_data->company_id();

        $date = date('Y-m-d');
        $ogfuel_movement = OgFuelMovement::where([
            ['ogfuel_id' ,'=', $ogfuel_id],
			['location_id','=' , $location_id],
			['franchisee_merchant_id','=',$merchantId]
        ])
        ->get()->first();
        if($ogfuel_movement) return true;

        return false;
    }

    public function checkFuelMovement(Request $request){

        /*added by Udemezue  for selecting the actual value of ogfuel_id*/
        $og_fuel = Ogfuel::where("product_id", $request->ogfuel_id)->first();
        $og_fuel_id = $og_fuel ? $og_fuel->id : '';
        
        //$exist = ['exist' => false];
        $btns_activation_status = ['start' => false , 'dipping' => false];
        if($request->ogfuel_id && $request->location_id){
            if($this->getTodayFuelMovement($request->location_id, $og_fuel_id)){
                $btns_activation_status['start'] = false ;
                $btns_activation_status['dipping'] = true ;
            } else {
                if($this->checkFuelMovementExist($request->location_id, $og_fuel_id)) {
                    $btns_activation_status['start'] = false ;
                    $btns_activation_status['dipping'] = true ;
                } else {
                    $btns_activation_status['start'] = true ;
                    $btns_activation_status['dipping'] = false ;
                }
            }
        }

        return json_encode($btns_activation_status);
    }
	
	protected function getStockReportProducts($id, $request, $byDay = false , $location_id){
		$user_data = new UserData();
		$stockreportproducts = stockreportproduct::join('stockreportmerchant',
			'stockreportmerchant.stockreport_id','=','stockreportproduct.stockreport_id')->
			with('stock_report.location',
            'stock_report.remark',
			'stock_report.product')->
			where('stockreportmerchant.franchisee_merchant_id',$user_data->company_id());

            if($location_id){
                $stockreportproducts = $stockreportproducts->whereHas('stock_report.location',function($query) use($location_id){
                    $query->where('location_id' , $location_id);
                });
            }
            $stockreportproducts = $stockreportproducts->where('stockreportproduct.product_id' , $id)
            ->orderby('stockreportproduct.id', 'DESC');

//        dd($stockreportproducts->get()[0]->stock_report);

        return $byDay ? $stockreportproducts->
        whereBetween('stockreportproduct.updated_at', [date($request->day. ' 00:00:00'), date($request->day.' 23:59:59')])
        ->get() : $stockreportproducts->get();
    }

    protected function getOposreceiptproducts($id, $request, $byDay = false , $location_id){
		$user_data = new UserData();
        $opos_product = opos_receiptproduct::
        SELECT (
            'opos_receipt.systemid as document_no',
           /* 'opos_receiptproduct.receipt_id',
            'opos_receiptproduct.promo_id'  ,
            'opos_receiptproduct.created_at as last_update',*/
            /*'opos_itemdetails.id as item_detail_id',
            'opos_itemdetails.receiptproduct_id',*/
            'location.branch as location',
            'location.id as locationid',
            'opos_receiptdetails.void',

//            'opos_receiptproduct.quantity as opos_receiptproduct_quantity',
            'opos_receipt.payment_type',
            'opos_receiptproduct.*',
            'opos_refund.refund_type',
            'opos_refund.refunded_amt',
            'og_pumplog.volume',
            // 'opos_receiptremarks.remarks'
        )
//            ->join('opos_itemdetails', 'opos_itemdetails.receiptproduct_id', '=', 'opos_receiptproduct.id')
            ->leftJoin('opos_receipt', 'opos_receipt.id', '=', 'opos_receiptproduct.receipt_id')
            ->leftJoin('opos_refund', 'opos_refund.receiptproduct_id', '=', 'opos_receiptproduct.id')
            ->leftJoin('og_pumplog', 'og_pumplog.pump', '=', 'opos_receipt.pump_no')
            ->join('opos_receiptdetails', 'opos_receipt.id', '=', 'opos_receiptdetails.receipt_id')
            ->join('opos_locationterminal', 'opos_receipt.terminal_id', '=', 'opos_locationterminal.terminal_id')
            ->join('location', 'location.id', '=', 'opos_locationterminal.location_id')
            // ->leftjoin('opos_receiptremarks','opos_receiptproduct.receipt_id', '=', 'opos_receiptremarks.receipt_id')
            ->where('opos_receiptproduct.product_id', $id)
            ->where('opos_receiptproduct.product_id', $id)
			->where('og_pumplog.merchant_id', $user_data->company_id());
            if($location_id){
                $opos_product->where('location.id', $location_id);
            }
            $opos_product = $opos_product->orderby('opos_receiptproduct.id', 'DESC');
        return 
        $byDay ? $opos_product->whereBetween('opos_receipt.updated_at', [date($request->day. ' 00:00:00'), date($request->day.' 23:59:59')])
            ->get() : $opos_product->get();
    }

	public function cadmin_og() {
		$analyticsController = new AnalyticsController();
		$branch_location = [];
		$get_location = $analyticsController->get_location();
		foreach ($get_location as $key => $val) {
			$$key = $val;
			$location_id = array_column($branch_location, 'id');
			foreach($val as $location) {
				if (!in_array($location->id,$location_id)){
					$branch_location = array_merge($branch_location, [$location]);
				}
			}
		}

		return view("industry.oil_gas.og_cadmin", compact('branch_location'));
	}

	public function cadmin_og_dtable(Request $request) {
		try {
		
			$user_data	= new UserData();
			$mycompany	= DB::table('company')->find($user_data->company_id());

			$data = collect();

			/*
			//processing invoice to display
			$invoiceDeliveryRecord = DB::table('invoice')->
				leftjoin('deliveryorder','deliveryorder.id','=','invoice.deliveryorder_id')->
				where('invoice.dealer_merchant_id', $user_data->company_id())->
				orWhere('invoice.supplier_merchant_id', $user_data->company_id())->
				select("invoice.systemid as invoice_systemid", "invoice.id as invoice_id",
					"invoice.created_at as date","deliveryorder.*")->
				get();

			$invoiceDeliveryRecord->map(function($f) use ($data) {
				$packet = collect();
				$packet->source_doc 	= $f->invoice_systemid;
				$packet->source_doc_url	= url("/invoice/issued/$f->invoice_id");
				$packet->source			= 'Invoice';
				$packet->date			= date("dMy", strtotime($f->date));
				$packet->delivery_id	= $f->systemid;	
				$packet->delivery_url	= url("/deliveryorder/invoice/$f->systemid");
				$packet->to				= '';
				$packet->delivery_man	= 'Deliveryman';
				$packet->deliveryman_id =  0;
				$packet->status			= 'Pending';
				$packet->created_at		= $f->date;
				$data->push($packet);
			});
			 */

			//processing SO to display
			$salesOrderRecord = DB::table('salesorder')->
				leftjoin('salesorderdeliveryorder','salesorderdeliveryorder.salesorder_id','salesorder.id')->
				leftjoin('deliveryorder','deliveryorder.id','=','salesorderdeliveryorder.deliveryorder_id')->
				where('salesorder.creator_user_id', $mycompany->owner_user_id)->
				select('salesorder.*', 
				'deliveryorder.issuer_location_id as creator_location_id',
				'deliveryorder.receiver_location_id',
				'deliveryorder.systemid as dsystemid', 'deliveryorder.id as DO_id',
				'deliveryorder.deliveryman_user_id'
				)->

				get();

			$salesOrderRecord = $salesOrderRecord->filter(function($f) {
				$product  = DB::table('product')->
					join('salesorderitem','salesorderitem.product_id','=','product.id')->
					where('salesorderitem.salesorder_id',$f->id)->
					whereIn('ptype', ['oilgas'])->
					first();
				return !empty($product);
			});

			$salesOrderRecord->map(function($f) use ($data) {
				$isDOIssued = DB::table('deliveryorderproduct')->
					where('deliveryorder_id', $f->DO_id)->first();
				$packet = collect();
				$packet->source_doc 	= $f->systemid;
				$packet->source_doc_url	= url("/salesorder/$f->systemid");
				$packet->source			= 'Sales Order';
				$packet->date			= date("dMy", strtotime($f->created_at));
				$packet->delivery_id	= !empty($isDOIssued) ? $f->dsystemid: ''; 
				$packet->delivery_url	= url("/deliveryorder/salesorder/$f->dsystemid");
				$packet->to				= DB::table('location')->
											find($f->receiver_location_id)->branch ?? '';
				$packet->delivery_man	= 'Deliveryman';
				$packet->deliveryman_id =  0;
				$packet->status			= 'Pending';
				$packet->created_at		= $f->created_at;
				$packet->from_location_id	= $f->receiver_location_id ?? 0;
				if ($f->is_void == 1)	
					$packet->status			= 'Void';
				elseif (!empty($isDOIssued))
					$packet->status			= 'Approved';
				else
					$packet->status			= 'Pending';
				$data->push($packet);
			});

			//processing PO to display
			$purchaseOrderRecord = DB::table('purchaseorder')->
				join('merchantpurchaseorder',
					'merchantpurchaseorder.purchaseorder_id','=','purchaseorder.id')->
				leftjoin('purchaseorderdeliveryorder','purchaseorderdeliveryorder.purchaseorder_id',
						'=','purchaseorder.id')->
				leftjoin('deliveryorder','deliveryorder.id','=','purchaseorderdeliveryorder.deliveryorder_id')->
				where('merchantpurchaseorder.merchant_id', $mycompany->id)->
				select('purchaseorder.*', 'deliveryorder.receiver_location_id',
					'deliveryorder.systemid as dsystemid', 'deliveryorder.id as DO_id',
					'deliveryorder.deliveryman_user_id'
				)->
				get();

			$purchaseOrderRecord = $purchaseOrderRecord->filter(function($f) {
	
				$product  = DB::table('product')->
					join('purchaseorderproduct','purchaseorderproduct.product_id','=','product.id')->
					where('purchaseorderproduct.purchaseorder_id',$f->id)->
					whereNotIn('ptype', ['oilgas'])->
					first();
				return !empty($product);
			});	

			$purchaseOrderRecord->map(function($f) use ($data) {
				$packet = collect();
				$packet->source_doc 		= $f->systemid;
				$packet->source_doc_url		= url("/purchaseorder/$f->id");
				$packet->source				= 'Purchase Order';
				$packet->date				= date("dMy", strtotime($f->created_at));
				$packet->delivery_id		= $f->dsystemid; 
				$packet->delivery_url		= url("/deliveryorder/purchaseorder/$f->dsystemid");
				$packet->to					= DB::table('location')->
												find($f->receiver_location_id)->branch ?? '';
				$packet->delivery_man		=  DB::table('users')->find($f->deliveryman_user_id)->name ?? 'Deliveryman';;
				$packet->deliveryman_id 	=  $f->deliveryman_user_id;
				$packet->status				= 'Pending';
				$packet->created_at			= $f->created_at;
				$packet->from_location_id	= $f->receiver_location_id;
				$packet->dsystemid	=	$f->dsystemid;

				if ($f->is_void  == 1)
					$packet->status			= 'Void';
				elseif (!empty($f->dsystemid))
					$packet->status			= 'Approved';
				else
					$packet->status			= 'Pending';
			
				$data->push($packet);
			});

			if ($request->has('location_id') && $request->location_id != 'all') {
				$data = $data->where('from_location_id', $request->location_id);
			}

			$data = $data->sortByDESC('created_at')->values();

			return Datatables::of($data)->
				addIndexColumn()->

				addColumn('source_doc', function ($data) {
					return <<<EOD
				<a href="javascript: openNewTabURL('$data->source_doc_url');"
					style="text-decoration:none"
					>$data->source_doc</a>
EOD;
				})->
				
				addColumn('storage', function ($data) {
					return '';
				})->
				
				addColumn('petrol', function ($data) {
					return '';
				})->

				addColumn('date', function ($data) {
					return $data->date;
				})->
				
				addColumn('delivery_id', function ($data) {
				
					return <<<EOD
					<a href="javascript: openNewTabURL('$data->delivery_url');"
						style="text-decoration:none">$data->delivery_id</a>
EOD;

				})->
				
				addColumn('to', function ($data) {
					return $data->to;
				})->
				
				addColumn('delivery_man', function ($data) {
					return <<<EOD
					<a href="javascript:deliveryman($data->deliveryman_id)"
						style="text-decoration:none">$data->delivery_man</a>
EOD;
				})->
				
				addColumn('status', function ($data) {
					return $data->status;
				})->
				
				addColumn('yellowcrab', function ($data) {
					return '';
				})->
				
				addColumn('bluecrab', function ($data) {
					return '';
				})->
				
				escapeColumns([])->
				make(true);


		} catch (\Exception $e) {
			\Log::error([
				"Error"	=>	$e->getMessage(),
				"File"	=>	$e->getFile(),
				"Line"	=>	$e->getLine()
			]);
			abort(404);
		}
	}


	public function vehicle_og() {

		$color_guide = $this->color_guide();
		return view("industry.oil_gas.og_vehicle",compact('color_guide'));
	}
	
	public function vehicle_mgmt_og() {
		return view("industry.oil_gas.og_vehiclemgmt_view");
	}

	public function og_deliveryman() {
		return view("industry.oil_gas.og_deliveryman");
	}

	public function og_deliverymantable() {
		
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

				addColumn('bluecrab',function($data) {
					return <<<EOD
						<img src="/images/bluecrab_25x25.png" class="mb-0 text-center" 
							style="width:25px;height:25px;cursor:pointer">
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
}
