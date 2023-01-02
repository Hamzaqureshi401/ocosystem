<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Matrix\Exception;

use \Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;

use App\User;
use \App\Models\Staff;
use \App\Classes\SystemID;
use \App\Models\usersrole;
use \App\Models\role;
use \App\Classes\UserData;
use \App\Models\merchantlocation;
use \App\Models\location;
use \App\Models\locationproduct;
use \App\Models\Stocktakemgmt;
use App\StockReport;
use \App\Models\product;
use App\Models\stockreportproduct;
use Illuminate\Support\Facades\Validator;
use DB;
use Log;


class StocktakeController extends Controller
{
    public function showInventoryStockTakeList($id)
	{
		$stocktake_systemid = $id;
		$stocktake = Stocktakemgmt::orderBy("id", "desc")->
			where('systemid', $stocktake_systemid)->first();
		$stocktake_location = location::
			where('id', $stocktake->location_id)->first();
		$creator_name = User::where('id',$stocktake->creator)->value('name');
		$creator_systemId = Staff::where('user_id',$stocktake->creator)->value('systemid');

		$id = Auth::user()->id;
		$user_roles = usersrole::where('user_id', $id)->get();		
		$is_king = \App\Models\Company::where('owner_user_id',
			Auth::user()->id)->first();

		$this->user_data = new UserData();

		$ids = merchantlocation::where('merchant_id',
			$this->user_data->company_id())->pluck('location_id');

		$location = location::where([['branch', '!=', 'null']])->
			whereIn('id', $ids)->latest()->get();

		if ($is_king != null) {
			$is_king = true;
		} else {
			$is_king = false;
		}

		return view('inventory.inventorystocktake_list',
			compact('user_roles', 'is_king', 'location',
				'stocktake_location', 'stocktake',
				'stocktake_systemid','id','creator_name','creator_systemId'));
	}


	public function showInventoryStockTakeMultichecker($stockTakeId)
	{
		$id = Auth::user()->id;
		$user_roles = usersrole::where('user_id', $id)->get();
		$is_king = \App\Models\Company::where('owner_user_id',
			Auth::user()->id)->first();

		$productSystemID = Input::get('productID');
		$product = product::where('systemid', $productSystemID)->first();

		$stocktakeID = $stockTakeId;

		$stocktake = Stocktakemgmt::where('systemid', $stocktakeID)->first();
		
		$creator_info = Stocktakemgmt::select('users.name','staff.systemid')->
			join('users','stocktakemgmt.creator','=','users.id')->
			join('staff','staff.user_id','=','users.id')->
			where('stocktakemgmt.systemid', $stocktakeID)->
			first();

		Log::debug('creator_info='.json_encode($creator_info));

		$location_info = location::where('id', $stocktake->location_id)->first();
		$user_info = User::select('users.name','staff.systemid')->
			join('staff','staff.user_id','=','users.id')->
			where('users.id', $id)->
			first();

		return view('inventory.inventorystocktake_multichecker',
			compact('user_roles', 'is_king','stocktakeID','creator_info','user_info','location_info','product'));
	}

	public function showInventoryStockTakePage($id)
	{
		$stocktake_systemid = $id;
		$stocktake = Stocktakemgmt::where('systemid', $stocktake_systemid)->first();
		$stocktake_location = location::where('id', $stocktake->location_id)->first();

		$id = Auth::user()->id;
		$user_roles = usersrole::where('user_id', $id)->get();
		$user_name = User::where('id', $id)->value('name');
		$user_staff_systemId = Staff::where('user_id', $id)->value('systemid');
		
		$is_king = \App\Models\Company::where('owner_user_id',
			Auth::user()->id)->first();
		
		$this->user_data = new UserData();
		$modal = "newLocationDialog";
		$ids = merchantlocation::where('merchant_id', $this->user_data->company_id())->pluck('location_id');
		$location = location::where([['branch', '!=', 'null']])->whereIn('id', $ids)->latest()->get();
		
		if ($is_king != null) {
			$is_king = true;
		} else {
			$is_king = false;
		}
		return view('inventory.inventorystocktake_page',
			compact('user_roles', 'is_king', 'location', 'stocktake_location', 'stocktake', 'stocktake_systemid','user_name','user_staff_systemId'));
	}


	public function showInventoryStockTake($type)
	{
		$id = Auth::user()->id;
		$user_roles = usersrole::where('user_id', $id)->get();
		
		$is_king = \App\Models\Company::where('owner_user_id',
			Auth::user()->id)->first();
		
		$this->user_data = new UserData();
		$modal = "newLocationDialog";
		$ids = merchantlocation::where('merchant_id', $this->user_data->company_id())->pluck('location_id');
		$location = location::where([['branch', '!=', 'null']])->whereIn('id', $ids)->latest()->get();
		
		if ($is_king != null) {
			$is_king = true;
		} else {
			$is_king = false;
		}

		return view('inventory.inventorystocktake_mgmt',
			compact('user_roles', 'is_king', 'location', 'type'));
	}


	public function getInventoryStokTake(Request $request)
    {	
        $this->user_data = new UserData();
		$company_id = $this->user_data->company_id();
        $id = Auth::user()->id;
		$is_king = \App\Models\Company::where('owner_user_id',
			Auth::user()->id)->first();

		Log::debug('getInventoryStokTake()');
		Log::debug('is_king='.$is_king);

		if ($is_king) {
			Log::debug('This is the owner!');
			Log::debug('locID='.$request->locID);
			//This is Owner
			if (empty($request->locID)) {
				// Location is NOT selected
				$data = [];

			} else {
				$sql = "
					SELECT 
						stm.id,
						stm.systemid,
						stm.stocktake_name,
						stm.status,
						stm.creator,
						stm.product,
						stm.created_at,
						u.name,
						sf.systemid AS staff_systemid
					FROM 
						stocktakemgmt AS stm
					JOIN users AS u ON u.id = stm.creator
					JOIN staff AS sf ON sf.user_id = u.id
					JOIN company AS c ON c.id = sf.company_id
					WHERE
						c.id = '".$company_id."' AND
						stm.product = '".$request->product_type."' AND
						stm.location_id = '".$request->locID."' AND
						stm.product IS NOT NULL AND
						stm.deleted_at IS NULL
					ORDER BY stm.created_at DESC
				";
				$data = DB::select($sql);
			}
		} else {
			//This is Checker
			Log::debug('This is the Checker!');
			Log::debug('locID='.$request->locID);
			if (empty($request->locID)) {
				// Location is NOT selected
				$data = [];

			} else {
				$slug = 'prod';
				$sql = "
					SELECT
						stm.id,
						stm.systemid,
						stm.stocktake_name,
						stm.status,
						stm.product,
						stm.creator,
						stm.created_at,
						u.name,
						sf.systemid AS staff_systemid
					FROM stocktakemgmt AS stm
					JOIN users AS u ON u.id = stm.creator 
					JOIN staff AS sf ON sf.user_id = u.id 
					JOIN company AS c ON c.id = sf.company_id
					JOIN usersrole AS ur ON c.id = ur.company_id
					JOIN role AS r ON r.id = ur.role_id
					WHERE
						r.slug = '$slug' AND
						ur.user_id = $id AND
						stm.product = '".$request->product_type."' AND
						stm.location_id = '".$request->locID."' AND
						stm.product IS NOT NULL AND
						stm.deleted_at IS NULL
					ORDER BY stm.created_at DESC;
				";
				$data = DB::select($sql);

			} 
		}

        return Datatables::of($data)
            ->addIndexColumn()
            ->addColumn('stocktake_id', function ($memberList) {
                return '<p class="os-linkcolor" data-field="stocktake_id" style="cursor: pointer; margin: 0; text-align: center;"><a  href="/inventorystocktake_list/'.$memberList->systemid.'" target="_blank" style="text-decoration: none;">' . $memberList->systemid . '</a></p>';
            })
            ->addColumn('stocktake_name', function ($memberList) use ($id) {
            	if ($id == $memberList->creator) {
            		return '<p class="os-linkcolor" data-field="stocktake_name" style="cursor: pointer; margin: 0;display:inline-block" onclick="details(' . $memberList->systemid . ')">' . (!empty($memberList->stocktake_name) ? $memberList->stocktake_name : 'Stock Take List') . '</p>';
            	} else {
            		return '<p data-field="stocktake_name" style="margin: 0;display:inline-block">' . (!empty($memberList->stocktake_name) ? $memberList->stocktake_name : 'Stock Take List') . '</p>';
            	}
        	
            })
            ->addColumn('stocktake_creator', function ($memberList) {

                return '<p class="buyOutput" data-field="stocktake_creator" style="margin: 0; ">'. $memberList->staff_systemid.'&nbsp;&nbsp;'.(!empty($memberList->name) ? $memberList->name: 'NULL').'</p>';
            })
            ->addColumn('stocktake_status', function ($memberList) {

                return '<p class="buyOutput" data-field="stocktake_status" style="margin: 0; ">'.(!empty($memberList->status) ? ucfirst($memberList->status): '&mdash;').'</p>';

            })
            ->addColumn('stocktake_pro', function ($memberList) {

                return '<p class="buyOutput text-center" data-field="stocktake_pro" style="margin: 0; ">'.(($memberList->product == 'inventory') ? 'Inventory': 'Raw&nbsp;Material').'</p>';

            })
            ->addColumn('stocktake_user', function ($memberList) {
            	$checker = StockReport::select('receiver_user_id')
            		->join('stockreportproduct','stockreportproduct.stockreport_id','=','stockreport.id')
            		->where('stocktakemgmt_id', $memberList->id)
            		->where('stockreportproduct.received','!=',0)
            		->pluck('receiver_user_id')
            		->all();
            	$count_checker = count(array_unique($checker));

                return '<p class="os-linkcolor getOutput" data-field="stocktake_user" onclick="showCheckerModal('.$memberList->id.')" style="cursor: pointer; margin: 0; "> '.$count_checker.'</p>';

            })
            ->addColumn('deleted', function ($memberList) use ($id) {

            	if (strtolower($memberList->status !== "completed")) {
					return '<div data-field="deleted"
						onclick="removeStocktakeManagementModel('.$memberList->id.')"
						class="remove">
						<img src="/images/redcrab_50x50.png"
						style="width:25px;height:25px;cursor:pointer"/>
						</div>';

            	} else {
					return '<div><img class="" src="/images/redcrab_50x50.png"
						style="width:25px;height:25px;cursor:not-allowed;
						filter:grayscale(100%) brightness(200%)"/>
						</div>';
            	}
            })
            ->escapeColumns([])
            ->make(true);
    }


    public function checkerNameModal(Request $request)
    {
        
        try {
            $checker = StockReport::select('receiver_user_id')->where('stocktakemgmt_id', $request->stocktakemgmt_id)->pluck('receiver_user_id')->all();
            $ids = array_unique($checker);
            $checker_list = [];
            foreach ($ids as $key => $id) {
            	$checker = User::select('users.name','staff.systemid')->
            		join('staff','staff.user_id','=','users.id')->
            		where('users.id', $id)->
            		first();
            	$checker_list[] = $checker;
            }
    		$fieldName = 'checker_modal';
            return view('inventory.inventory-modals', compact(['checker_list', 'fieldName']));
            

        } catch (\Illuminate\Database\QueryException $ex) {
            $response = (new ApiMessageController())->queryexception($ex);
        }
    }



    public function destroyStm(Request $request)
    {
        
        try {
            $allInputs = $request->all();
            $id        = $request->get('id');
            $fieldName = 'stocktakemgmt_delete';
            $stocktakemgmt = stocktakemgmt::where('id', $id)->first();
            return view('inventory.inventory-modals', compact(['id', 'fieldName', 'stocktakemgmt']));
            

        } catch (\Illuminate\Database\QueryException $ex) {
            $response = (new ApiMessageController())->queryexception($ex);
        }
    }


    public function destoryStocktakeManagement($id)
    {
        try {
            $this->user_data = new UserData();
            $stocktakemgmt = stocktakemgmt::find($id);

            if(strtolower($stocktakemgmt->status) !== "completed"){
            	$stocktakemgmt->delete();
	            $msg = "Stocktake deleted successfully";
            }
            else{
	            $msg = "Stocktake already completed";
            }

            return view('layouts.dialog', compact('msg'));

        } catch (\Illuminate\Database\QueryException $ex) {
            //$msg = "Some error occured";
            $msg = $ex->getMessage();

            return view('layouts.dialog', compact('msg'));
        }
    }

    public function getInventoryStokTakeList(Request $request)
    {
    	$id = Auth::user()->id;
		$user_roles = usersrole::where('user_id', $id)->get();		
		$is_king = \App\Models\Company::where('owner_user_id', $id)->first();

        $this->user_data = new UserData();
        $model = new stocktakemgmt();
        $location_id = location::where('systemid',
			$request->locationSystemId)->value('id');

       $stocktake_systemid = $request->stocktake_systemid;
        $stockTake = stocktakemgmt::where('systemid',
			$stocktake_systemid)->first();

		$product_type = $stockTake->product;

	//	dd($is_king);
        if ($is_king) {
        	//this is owner
	        if ($product_type == 'rawmaterial') {
		        $data = DB::select(
					"SELECT 
						p.id, p.name, p.systemid, p.created_at, p.thumbnail_1, l.location_id
					FROM prd_rawmaterial AS pr
					JOIN product AS p ON pr.product_id = p.id
					JOIN merchantproduct AS mp ON mp.product_id = p.id
					JOIN merchant AS m ON mp.merchant_id = m.id
					JOIN merchantlocation AS l ON l.merchant_id = m.id
					WHERE 
						(p.name IS NOT NULL AND p.name <> '') AND
						(p.prdcategory_id IS NOT NULL AND p.prdcategory_id <> 0) AND
						(p.prdsubcategory_id IS NOT NULL AND p.prdsubcategory_id <> 0)AND
						(pr.price IS NOT NULL AND pr.price <> 0) AND
						m.company_id = '".$is_king->id."' AND
						l.location_id = '".$location_id."'
					GROUP BY id
					ORDER BY created_at DESC
					"
				);
	        } elseif ($product_type == 'inventory') {
	        	$data = DB::select(
					"SELECT 
						p.id, p.name, p.systemid, p.created_at, p.thumbnail_1, l.location_id
					FROM prd_inventory AS pi
					JOIN product AS p ON pi.product_id = p.id
					JOIN merchantproduct AS mp ON mp.product_id = p.id
					JOIN merchant AS m ON mp.merchant_id = m.id
					JOIN merchantlocation AS l ON l.merchant_id = m.id
					WHERE 
						(p.name IS NOT NULL AND p.name <> '') AND
						(p.prdcategory_id IS NOT NULL AND p.prdcategory_id <> 0) AND
						(p.prdsubcategory_id IS NOT NULL AND p.prdsubcategory_id <> 0)AND
						(pi.price IS NOT NULL AND pi.price <> 0) AND
						m.company_id =  '".$is_king->id."' AND
						l.location_id = '".$location_id."'
					GROUP BY id
					ORDER BY created_at DESC
					"
				);
	        }
        } else {
        	//this is checker
        	if ($product_type == 'rawmaterial') {
		        $data = DB::select(
					"SELECT 
						p.id, p.name, p.systemid, p.created_at, p.thumbnail_1, l.location_id
					FROM prd_rawmaterial AS pr
					JOIN product AS p ON pr.product_id = p.id
					JOIN merchantproduct AS mp ON mp.product_id = p.id
					JOIN merchant AS m ON mp.merchant_id = m.id
					JOIN merchantlocation AS l ON l.merchant_id = m.id
					JOIN usersrole AS ur ON m.company_id = ur.company_id
					WHERE 
						(p.name IS NOT NULL AND p.name <> '') AND
						(p.prdcategory_id IS NOT NULL AND p.prdcategory_id <> 0) AND
						(p.prdsubcategory_id IS NOT NULL AND p.prdsubcategory_id <> 0)AND
						(pr.price IS NOT NULL AND pr.price <> 0) AND
						ur.user_id = '".$id."' AND
						l.location_id = '".$location_id."'
					GROUP BY id
					ORDER BY created_at DESC
					"
				);
	        } elseif ($product_type == 'inventory') {
	        	$data = DB::select(
					"SELECT 
						p.id, p.name, p.systemid, p.created_at, p.thumbnail_1, l.location_id
					FROM prd_inventory AS pi
					JOIN product AS p ON pi.product_id = p.id
					JOIN merchantproduct AS mp ON mp.product_id = p.id
					JOIN merchant AS m ON mp.merchant_id = m.id
					JOIN merchantlocation AS l ON l.merchant_id = m.id
					JOIN usersrole AS ur ON m.company_id = ur.company_id
					WHERE 
						(p.name IS NOT NULL AND p.name <> '') AND
						(p.prdcategory_id IS NOT NULL AND p.prdcategory_id <> 0) AND
						(p.prdsubcategory_id IS NOT NULL AND p.prdsubcategory_id <> 0)AND
						(pi.price IS NOT NULL AND pi.price <> 0) AND
						ur.user_id = '".$id."' AND
						l.location_id = '".$location_id."'
					GROUP BY id
					ORDER BY created_at DESC
					"
				);
	        }
        }
		
		foreach($data as $prd){
			$st_Info = $this->stockTake_additionalInfo($prd->id, $stockTake->id) ;
			$prd->quantity = $st_Info->quantity ?? app('App\Http\Controllers\InventoryController')->location_productqty($prd->id, $prd->location_id);
//			$prd->mcheck - $st_Info->multichecker ?? 0;
			//$prd->quantity = app('App\Http\Controllers\InventoryController')->location_productqty($prd->id, $prd->location_id); 
		}
        return Datatables::of($data)
            ->addIndexColumn()
            ->addColumn('poduct_id', function ($memberList) {
                return '<p class="getOutput" data-field="poduct_id" style="margin: 0; text-align: center;">' . $memberList->systemid . '</p>';
            })
            ->addColumn('poduct_name', function ($memberList) {
            	if (!empty($memberList->thumbnail_1)) {
                    $img_src = '/images/product/' . $memberList->id . '/thumb/' . $memberList->thumbnail_1;
                    $img = "<img id='img-product-thumb-' src='$img_src' data-field='og_product_name' style=' width: 25px;height: 25px;display: inline-block;margin-right: 8px;object-fit:contain;'/>";
                } else {
                    $img = "";
                }

                return $img . '<p class="getOutput" data-field="poduct_name" style="margin: 0;display:inline-block" >'. (!empty($memberList->name) ? $memberList->name : 'Product Name') . '</p>';
            })
            ->addColumn('poduct_qty', function ($memberList) {

                return '<p class="buyOutput" data-field="poduct_qty" style="margin: 0; "> '. (!empty($memberList->quantity) ? $memberList->quantity : 0) .'</p>';

            })
            ->addColumn('poduct_multi', function ($memberList) use ($stocktake_systemid,$stockTake) {
				$mulichecker = stockreportproduct::select('stockreportproduct.received','stockreportproduct.correction')->
					join('stockreport','stockreport.id','=','stockreportproduct.stockreport_id')->
					where('stockreport.stocktakemgmt_id',$stockTake->id)->
					where('stockreportproduct.product_id',$memberList->id)->
					get();
            	$sum = 0;
            	$sum_correction = 0;
            	foreach ($mulichecker as $key => $checker) {
            		$sum += $checker->received;
            		$sum_correction += $checker->correction;
            	}
            	$mulichecker_sum = $sum + $sum_correction;
            	// dd($sum_correction);
				
				return '<p class="os-linkcolor getOutput" data-field="poduct_multi" style="cursor: pointer; margin: 0;"><a  href="/inventorystocktake_multichecker/'.$stocktake_systemid.'?productID='.$memberList->systemid.'" target="_blank" style="text-decoration: none;">'.$mulichecker_sum.'</a></p>';

            })
            ->addColumn('poduct_difference', function ($memberList) use ($stockTake) {
				$mulichecker = stockreportproduct::select('stockreportproduct.received','stockreportproduct.correction')->
					join('stockreport','stockreport.id','=','stockreportproduct.stockreport_id')->
					where('stockreport.stocktakemgmt_id',$stockTake->id)->
					where('stockreportproduct.product_id',$memberList->id)->
					get();

            	$sum = 0;
            	$sum_correction = 0;
            	foreach ($mulichecker as $key => $checker) {
            		$sum += $checker->received;
            		$sum_correction += $checker->correction;
				}

            	$mulichecker_sum = $sum + $sum_correction;

                return '<p class="getOutput" data-field="poduct_difference" style="margin: 0;"> '. (!empty($memberList->quantity) ? $mulichecker_sum -  $memberList->quantity  : $mulichecker_sum) .'</p>';

            })
            
            ->escapeColumns([])
            ->make(true);
    }


    public function getInventoryStokTakePage(Request $request)
    {
    	$id = Auth::user()->id;
		$user_roles = usersrole::where('user_id', $id)->get();
		
		$is_king = \App\Models\Company::where('owner_user_id',
			Auth::user()->id)->first();
        $this->user_data = new UserData();
		$company_id = $this->user_data->company_id();
        $model = new stocktakemgmt();
        $location_id = location::where('systemid', $request->locationSystemId)->value('id');
		
		$stocktake_systemid = $request->stocktake_systemid;


		$stockTake = stocktakemgmt::where('systemid',
			$stocktake_systemid)->first();

		$product_type = $stockTake->product;


        if ($is_king) {
        	//this is owner
	        if ($product_type == 'rawmaterial') {
		        $data = DB::select(
					"SELECT 
						p.id, p.name, p.systemid, p.created_at, p.thumbnail_1, l.location_id
					FROM prd_rawmaterial AS pr
					JOIN product AS p ON pr.product_id = p.id
					JOIN merchantproduct AS mp ON mp.product_id = p.id
					JOIN merchant AS m ON mp.merchant_id = m.company_id
					JOIN merchantlocation AS l ON l.merchant_id = m.company_id
					WHERE 
						(p.name IS NOT NULL AND p.name <> '') AND
						(p.prdcategory_id IS NOT NULL AND p.prdcategory_id <> 0) AND
						(p.prdsubcategory_id IS NOT NULL AND p.prdsubcategory_id <> 0)AND
						(pr.price IS NOT NULL AND pr.price <> 0) AND
						m.company_id = '".$company_id."' AND
						l.location_id = '".$location_id."'
					GROUP BY id
					ORDER BY created_at DESC
					"
				);
	        } elseif ($product_type == 'inventory') {
	        	$data = DB::select(
					"SELECT 
						p.id, p.name, p.systemid, p.created_at, p.thumbnail_1, l.location_id
					FROM prd_inventory AS pi
					JOIN product AS p ON pi.product_id = p.id
					JOIN merchantproduct AS mp ON mp.product_id = p.id
					JOIN merchant AS m ON mp.merchant_id = m.company_id
					JOIN merchantlocation AS l ON l.merchant_id = m.company_id
					WHERE 
						(p.name IS NOT NULL AND p.name <> '') AND
						(p.prdcategory_id IS NOT NULL AND p.prdcategory_id <> 0) AND
						(p.prdsubcategory_id IS NOT NULL AND p.prdsubcategory_id <> 0)AND
						(pi.price IS NOT NULL AND pi.price <> 0) AND
						m.company_id =  '".$company_id."' AND
						l.location_id = '".$location_id."'
					GROUP BY id
					ORDER BY created_at DESC
					"
				);
	        }
        } else {
        	//this is checker
        	if ($product_type == 'rawmaterial') {
		        $data = DB::select(
					"SELECT 
						p.id, p.name, p.systemid, p.created_at, p.thumbnail_1, l.location_id
					FROM prd_rawmaterial AS pr
					JOIN product AS p ON pr.product_id = p.id
					JOIN merchantproduct AS mp ON mp.product_id = p.id
					JOIN merchant AS m ON mp.merchant_id = m.id
					JOIN merchantlocation AS l ON l.merchant_id = m.id
					JOIN usersrole AS ur ON m.company_id = ur.company_id
					WHERE 
						(p.name IS NOT NULL AND p.name <> '') AND
						(p.prdcategory_id IS NOT NULL AND p.prdcategory_id <> 0) AND
						(p.prdsubcategory_id IS NOT NULL AND p.prdsubcategory_id <> 0)AND
						(pr.price IS NOT NULL AND pr.price <> 0) AND
						ur.user_id = '".$id."' AND
						l.location_id = '".$location_id."'
					GROUP BY id
					ORDER BY created_at DESC
					"
				);
	        } elseif ($product_type == 'inventory') {
	        	$data = DB::select(
					"SELECT 
						p.id, p.name, p.systemid, p.created_at, p.thumbnail_1, l.location_id
					FROM prd_inventory AS pi
					JOIN product AS p ON pi.product_id = p.id
					JOIN merchantproduct AS mp ON mp.product_id = p.id
					JOIN merchant AS m ON mp.merchant_id = m.id
					JOIN merchantlocation AS l ON l.merchant_id = m.id
					JOIN usersrole AS ur ON m.company_id = ur.company_id
					WHERE 
						(p.name IS NOT NULL AND p.name <> '') AND
						(p.prdcategory_id IS NOT NULL AND p.prdcategory_id <> 0) AND
						(p.prdsubcategory_id IS NOT NULL AND p.prdsubcategory_id <> 0)AND
						(pi.price IS NOT NULL AND pi.price <> 0) AND
						ur.user_id = '".$id."' AND
						l.location_id = '".$location_id."'
					GROUP BY id
					ORDER BY created_at DESC
					"
				);
	        }
        }
		if ($product_type == 'rawmaterial') {
			foreach($data as $prd){
				$prd->quantity = app('App\Http\Controllers\RawMaterialController')->location_productqty($prd->id, $prd->location_id); 
			}			
		} else {
			foreach($data as $prd){
				$prd->quantity = app('App\Http\Controllers\InventoryController')->location_productqty($prd->id, $prd->location_id); 
			}
		}

        return Datatables::of($data)
            ->addIndexColumn()
            ->addColumn('poduct_id', function ($memberList) {
                return '<p class="getOutput" data-field="poduct_id" style="margin: 0; text-align: center;">' . $memberList->systemid . '</p>';
            })
            ->addColumn('poduct_name', function ($memberList) {
            	if (!empty($memberList->thumbnail_1)) {
                    $img_src = '/images/product/' . $memberList->id . '/thumb/' . $memberList->thumbnail_1;
                    $img = "<img id='img-product-thumb-' src='$img_src' data-field='og_product_name' style=' width: 25px;height: 25px;display: inline-block;margin-right: 8px;object-fit:contain;'/>";
                } else {
                    $img = "";
                }

                return $img . '<p class="getOutput" data-field="poduct_name" style="margin: 0;display:inline-block" >'. (!empty($memberList->name) ? $memberList->name : 'Product Name') . '</p>';
            })
            ->addColumn('poduct_correction', function ($memberList) use ($stockTake) {
				$mulichecker = stockreportproduct::select('stockreportproduct.received','stockreportproduct.correction')->
					join('stockreport','stockreport.id','=','stockreportproduct.stockreport_id')->
					where('stockreport.stocktakemgmt_id',$stockTake->id)->
					where('stockreportproduct.product_id',$memberList->id)->
					get();

            	$sum = 0;
            	foreach ($mulichecker as $key => $checker) {
            		$sum += $checker->received;
            	}
                return '<div class="value-button increase" id="increase_correction_' . $memberList->id . '" onclick="increaseValueCorrection(' . $memberList->id . ')" value="Increase Value" style="margin-top:-25px;"><ion-icon class="ion-ios-plus-outline" style="font-size: 24px;margin-right:10px;"></ion-icon>
                    </div><input type="number" data-prd="'.$memberList->id.'" id="number_correction_' . $memberList->id . '"  class="number product_correction" value="0"  min="-' . $sum . '" max="0" required onblur="check_min(' . $memberList->id . ')">
                    <div class="value-button decrease" id="decrease_correction_' . $memberList->id . '" onclick="decreaseValueCorrection(' . $memberList->id . ')" value="Decrease Value" style="margin-top:-25px;"><ion-icon class="ion-ios-minus-outline" style="font-size: 24px;"></ion-icon>
                    </div>';

            })
            ->addColumn('poduct_qty', function ($memberList) {

                return '<p class="buyOutput" id="qty_' . $memberList->id . '" data-field="poduct_qty" style="margin: 0; "> '. (!empty($memberList->quantity) ? $memberList->quantity : 0) .'</p>';

            })
            ->addColumn('poduct_checker', function ($memberList) {
            	$mulichecker = 0;
                return '<div class="value-button increase" id="increase_' . $memberList->id . '" onclick="increaseValue(' . $memberList->id . ')" value="Increase Value" style="margin-top:-25px;"><ion-icon class="ion-ios-plus-outline" style="font-size: 24px;margin-right:10px;"></ion-icon>
                    </div><input type="number" data-prd="'.$memberList->id.'" id="number_' . $memberList->id . '"  class="number product_qty" value="0"  min="0" max="' . $memberList->quantity . '" required onblur="check_max(' . $memberList->id . ')">
                    <div class="value-button decrease" id="decrease_' . $memberList->id . '" onclick="decreaseValue(' . $memberList->id . ')" value="Decrease Value" style="margin-top:-25px;"><ion-icon class="ion-ios-minus-outline" style="font-size: 24px;"></ion-icon>
                    </div>';

            })
            ->addColumn('poduct_difference', function ($memberList) {
				$mulichecker = 0;
                return '<p class="getOutput" id="checker_' . $memberList->id . '" data-field="poduct_difference" style="margin: 0;"> '. (!empty($memberList->quantity) ?  						$mulichecker  - $memberList->quantity : 0) .'</p>';

            })
            
            ->escapeColumns([])
            ->make(true);
    }

    public function stocktakeStore(Request $request)
    {
        //Create a new Stock Take management here
        try {
        	$validation = Validator::make($request->all(), [
                'location_id' => 'required',
                'product_type' => 'required',
            ]);
            $this->user_data = new UserData();
            $user_id = Auth::user()->id;
            $SystemID        = new SystemID('stocktake');
            $locationID = $request->location_id;
            $productType = $request->product_type;
            $stocktake         = new Stocktakemgmt();

            $stocktake->systemid = $SystemID;
            $stocktake->location_id = $locationID;
            $stocktake->product = $productType;
            $stocktake->creator = $user_id;
            $stocktake->status = 'Pending';
            $stocktake->stocktake_name    = '';
            $stocktake->save();

            $msg = "Stock Take added successfully";
            return view('layouts.dialog', compact('msg'));

        } catch (\Exception $e) {
            //$msg = "Some error occured";
            $msg = $e->getMessage();
            return view('layouts.dialog', compact('msg'));
        }
    }


    function showEditModal(Request $request){
        try {
            $validation = Validator::make($request->all(), [
                'stocktake_id' => 'required',
            ]);

            if ($validation->fails()) {
                throw new \Exception("validation_error", 19);
            }

            $stocktake_details = Stocktakemgmt::where('systemid',
                $request->stocktake_id)->first();

	        if (!$stocktake_details) {
	            throw new \Exception('stocktake_not_found', 25);
	        }
	        return  response()->json(['name' =>$stocktake_details->stocktake_name, 'status' => 'true']);

            
        } catch (\Exception $e) {
//            return $e->getMessage();
            if ($e->getMessage() == 'validation_error') {
                return '';

            } else if ($e->getMessage() == 'product_not_found') {
                return response()->json([
					'message' =>"Error occured while opening dialog, invalid product selected",
					'status' => 'false']);

            } else {
                return response()->json([
					'message' =>$e->getMessage(),
					'status' => 'false']);
            }
        }
    }

    function updateStockManagementDetail(Request $request){
            
        try {

            $allInputs = $request->all();
            $systemid       = $request->get('systemid');
            $changed = false;

            $validation = Validator::make($allInputs, [
                'systemid'         => 'required',
            ]);

            if ($validation->fails()) {
                throw new Exception("stocktake_not_found", 1);
            }

             $stocktakemgmt = Stocktakemgmt::where('systemid', $systemid)->first();

             if (!$stocktakemgmt) {
                throw new Exception("stocktakemgmt_not_found", 1);
            }

            if ($request->has('stocktakemgmt_name')) {
				$stocktakemgmt->stocktake_name = $request->stocktakemgmt_name;
				$changed = true;
				$msg = "Stock Take Name updated";
            }

            if ($changed == true) {
                $stocktakemgmt->save();
                $response = response()->json(['msg' => $msg,
					'status' => 'true']);
            } else {
                $response = response()->json([
					'msg' =>'Stock Take Name not found', 'status' => 'false']);
            }

        }  catch (\Exception $e) {
            if ($e->getMessage() == 'product_not_found') {
                $msg = "Product not found";
            } else if ($e->getMessage() == 'invalid_cost') {
                $msg = "Invalid cost";
            } else {
                $msg = $e->getMessage();
            }
            $response = response()->json(['msg' =>$msg, 'status' => 'false']);
        }

        return $response;
    }

    //stock take page report function
    public function stocktakePageReport(Request $request)
    {
    	try {
        	$validation = Validator::make($request->all(), [
                'location_id' => 'required',
                'product_type' => 'required',
            ]);
	        $this->user_data = new UserData();
	        $user_id = Auth::user()->id;

	        $SystemID        = new SystemID('stockreport');
	    	

	        $creator_user_id = $request->stocktake['creator'];
	        $receiver_user_id = Staff::where('systemid',$request->receiver_systemid)->value('user_id');
	        $stocktakemgmt_id = $request->stocktake['id'];
	        $status = 'completed';
	        $type = 'stocktake';
	        $location_id = $request->stocktake['location_id'];
	        // $current = date('dMy H:i:s');
	        $current = date('Y-m-d H:i:s');
	    	
	    	$totalQty = 0;
	    	$totalCorrection = 0;
	    	foreach ($request->received_prod as $product) {
	    		$totalQty += $product['qty'];
	    		$totalCorrection += $product['correction'];
	    	}

	    	if($totalQty > 0 || $totalCorrection < 0){

		    	$StockReport         = new StockReport();
		    	$StockReport->systemid = $SystemID;
		    	$StockReport->creator_user_id = $creator_user_id;
		    	$StockReport->receiver_user_id = $receiver_user_id;
		    	$StockReport->stocktakemgmt_id = $stocktakemgmt_id;
		    	$StockReport->status = $status;
		    	$StockReport->type = $type;
		    	$StockReport->location_id = $location_id;
		    	$StockReport->received_tstamp = $current;
		    	$StockReport->save();

	    		if ($totalQty > 0) {
			    	$stockreport_id = $StockReport->id;
			        foreach ($request->received_prod as $product) {
			        	$prd_id = $product['id'];
			        	$received = $product['qty'];
			        	$correction = $product['correction'];
			        	$loc_prod = DB::select("
			        		SELECT MAX(id) id 
			        		FROM locationproduct 
			        		WHERE location_id = $location_id AND product_id = $prd_id
			        		GROUP BY product_id, location_id");
			        	if ($received > 0) {
				        	if(count($loc_prod)){
				        		$qty = locationproduct::where('id', $loc_prod[0]->id)->value('quantity');
				        	} else {
				        		$qty = 0;
				        	}
				        	$lost = $qty - $received;
				        	$prd_image_url = product::where('id',$prd_id)->value('thumbnail_1');

				        	$StockReportPrd         = new stockreportproduct();
				        	$StockReportPrd->product_id = $prd_id;
				        	$StockReportPrd->stockreport_id = $stockreport_id;
				        	$StockReportPrd->quantity = $qty;
				        	$StockReportPrd->correction = $correction;
				        	$StockReportPrd->received = $received;
				        	$StockReportPrd->status = 'checked';
				        	$StockReportPrd->lost = $lost;
				        	$StockReportPrd->image = $prd_image_url;
				        	$StockReportPrd->save();
			        	}
			        }
	    			$msg = "Checker's column qty added successfully";
	    		} else {
	    			$stockreport_id = $StockReport->id;
			        foreach ($request->received_prod as $product) {
			        	$prd_id = $product['id'];
			        	$received = $product['qty'];
			        	$correction = $product['correction'];
			        	$loc_prod = DB::select("
			        		SELECT MAX(id) id 
			        		FROM locationproduct 
			        		WHERE location_id = $location_id AND product_id = $prd_id
			        		GROUP BY product_id, location_id");

			        	if(count($loc_prod)){
			        		$qty = locationproduct::where('id', $loc_prod[0]->id)->value('quantity');
			        	} else {
			        		$qty = 0;
			        	}
			        	$lost = $qty - $received;
			        	$prd_image_url = product::where('id',$prd_id)->value('thumbnail_1');

			        	$StockReportPrd         = new stockreportproduct();
			        	$StockReportPrd->product_id = $prd_id;
			        	$StockReportPrd->stockreport_id = $stockreport_id;
			        	$StockReportPrd->quantity = $qty;
			        	$StockReportPrd->correction = $correction;
			        	$StockReportPrd->received = $received;
			        	$StockReportPrd->status = 'checked';
			        	$StockReportPrd->lost = $lost;
			        	$StockReportPrd->image = $prd_image_url;
			        	$StockReportPrd->save();
			        }
	    			$msg = "Correction updated successfully";
	    		}
	    	} else {
	    		$msg = "Checker is zero";
	    	}
            return view('layouts.dialog', compact('msg'));

        } catch (\Exception $e) {
            //$msg = "Some error occured";
			$msg = $e->getMessage();
            return view('layouts.dialog', compact('msg'));
        }
        // return view('layouts.dialog', compact('msg'));
    }


    public function getStockMultichecker(Request $request)
    {	
        $this->user_data = new UserData();

        $id = Auth::user()->id;
		$is_king = \App\Models\Company::where('owner_user_id',
			Auth::user()->id)->first();

   //  	$data = stockreportproduct::select(
   //  		'staff.systemid',
   //  		'users.name',
   //  		'stockreport.received_tstamp',
   //  		'stockreportproduct.received')->
   //  		join('stockreport','stockreport.id','=','stockreportproduct.stockreport_id')->
   //  		join('product','product.id','=','stockreportproduct.product_id')->
   //  		join('users','users.id','=','stockreport.receiver_user_id')->
   //  		join('staff','staff.user_id','=','users.id')->
   //  		where('product.systemid',$request->product_id)->
   //  		orderBy('stockreport.received_tstamp','DESC')->
			// get();

		$data = DB::select(
			"SELECT
				s.systemid as user_id,
				checker.name,
				DATE_FORMAT(srp.created_at, '%d%b%y %T') as created_at,
				srp.received,
				srp.correction
			FROM
				users checker,
				users owner,
				staff s,
				stockreport sr,
				stockreportproduct srp,
				product p,
				stocktakemgmt sm,
				location l,
				company c
			WHERE
				s.user_id = checker.id
				AND s.company_id = c.id
				AND c.owner_user_id = owner.id
				AND srp.stockreport_id = sr.id
				AND srp.product_id = p.id
				AND sr.stocktakemgmt_id = sm.id
				AND sr.receiver_user_id = checker.id
				AND sm.location_id = l.id
				AND p.systemid = '".$request->product_id."' 
				AND sm.systemid = '".$request->stocktake_systemid."'
			ORDER BY
				srp.created_at DESC"
		);
		// AND owner.id = '".$id."'  

        return Datatables::of($data)
            ->addIndexColumn()
            ->addColumn('user_id', function ($memberList) {
                return '<p data-field="user_id" style="cursor: pointer; margin: 0; text-align: center;">'.$memberList->user_id.'</p>';
            })
            ->addColumn('user_name', function ($memberList) {
            	
        		return '<p data-field="user_name" style="cursor: pointer; margin: 0;display:inline-block">' . (!empty($memberList->name) ? $memberList->name : 'Stock Take List') . '</p>';
            })
            ->addColumn('date', function ($memberList) {

                return '<p class="buyOutput" data-field="date" style="margin: 0; ">'. $memberList->created_at.'</p>';
            })
            ->addColumn('qty', function ($memberList) {

                return '<p class="buyOutput" data-field="qty" style="margin: 0; ">'.($memberList->received + $memberList->correction).'</p>';

            })
            
            ->escapeColumns([])
            ->make(true);
    }

    public function stocktakeListConfrim(Request $request) {
    	try {
			
			$validation = Validator::make($request->all(), [
                'stocktakemgmt_id' => 'required',
            ]);
	    	
	    	$id = Auth::user()->id;
			
			$is_king = \App\Models\Company::where('owner_user_id',
				Auth::user()->id)->first();

			$get_stocktakemgmt = Stocktakemgmt::where('systemid',
				$request->stocktakemgmt_id)->first();

			$stock_report = DB::table('stockreport')->
				join('stockreportproduct','stockreportproduct.stockreport_id','=','stockreport.id')->
				where('stockreport.stocktakemgmt_id', $get_stocktakemgmt->id)->get();

			$product_ids = $stock_report->pluck('product_id')->unique();

			$insert_query = collect();	
			$product_ids->map(function($z) use ($get_stocktakemgmt, $stock_report, $insert_query) {
				$quantity = app('App\Http\Controllers\InventoryController')->location_productqty($z, $get_stocktakemgmt->location_id);			
				 
				 $stock_report_product = $stock_report->where('product_id',	$z);
				 $sum = $stock_report_product->sum('received');
				 $sum_correction = $stock_report_product->sum('correction');
				 $mchecker = $sum + $sum_correction;

				 $insert_query->push( [
				 	"stocktakemgmt_id"  => $get_stocktakemgmt->id,
					"quantity"			=> $quantity,
					"product_id"		=> $z,
					"multichecker"		=> $mchecker,
					"created_at"		=> date("Y-m-d H:i:s"),
					"updated_at"		=> date("Y-m-d H:i:s")
				 ]);
			});
			if ($insert_query->count() > 0)
				DB::table('stocktakemgmtqty')->insert($insert_query->toArray());

			$get_stocktakemgmt->status = 'completed';
			$get_stocktakemgmt->save();

		} catch (\Exception $e) {
            //$msg = "Some error occured";
			$msg = $e->getMessage();
            return view('layouts.dialog', compact('msg'));
        }

    }

	public function stockTake_additionalInfo($product_id, $stockmgmt_id) {
		try {
				$qty = DB::table('stocktakemgmtqty')->
							where('stocktakemgmt_id', $stockmgmt_id)->
							where('product_id', $product_id)->
							first();
				return !empty($qty) ? $qty:null;
		} catch (\Exception $e) {
			\Log::info([
				"Error"		=> $e->getMessage(),
				"File"		=> $e->getFile(),
				"Line No" 	=> $e->getLine()
			]);
		}
	}
}
