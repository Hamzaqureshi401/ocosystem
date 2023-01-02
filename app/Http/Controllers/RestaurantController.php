<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Log;
use Matrix\Exception;
use Yajra\DataTables\DataTables;
use \App\Classes\SystemID;
use \App\Classes\UserData;
use \App\Models\merchantproduct;
use \App\Models\product;
use \App\Models\restaurant;
use \App\Http\Controllers\ProductController;
use \App\Models\prdcategory;
use App\Models\prd_subcategory;
use \App\Models\productspecial;

class RestaurantController extends Controller
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
        $model           = new restaurant();

        $ids  = merchantproduct::where('merchant_id', $this->user_data->company_id())->pluck('product_id');
 	
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
   

	$ids  = product::where('ptype', 'services')
        // ->whereNotNull('name')
        ->whereIn('id', $ids)->pluck('id');
        $data = $model->whereIn('product_id', $ids)->orderBy('created_at', 'asc')->latest()->get();

       // $data = $model->latest()->get();

	$data->map(function ($z) use ($franchise_p_id) {
			$franchise_product = $franchise_p_id->firstWhere('product_id',$z->product_id);

			if (!empty($franchise_product)) {
				$z->franchise_product  	= true;
			}
		});


        return Datatables::of($data)
            ->addIndexColumn()
            ->addColumn('restaurantnservices_pro_id', function ($memberList) {
		    return '<p class="os linkcolor" data-field="restaurantnservices_pro_id"
			    style="margin: 0;text-align: center;">' . $memberList->product_name->systemid . '</p>';
            })
            ->addColumn('restaurantnservices_pro_name', function ($memberList) {
                if (!empty($memberList->product_name->thumbnail_1)) {
			$img_src = '/images/product/' . $memberList->product_name->id . 
				'/thumb/' . $memberList->product_name->thumbnail_1;
			$img     = "<img src='$img_src' data-field='restaurantnservices_pro_name'
			   	 style=' width: 25px;height: 25px;display: inline-block;margin-right: 8px;object-fit:contain;'>";
                } else {
                    $img = null;
                }
		
		return  !empty($memberList->franchise_product) ? $img.$memberList->product_name->name:
				$img . '<p class="os-linkcolor" data-field="inven_pro_name" 
				style="cursor: pointer; margin: 0;display:inline-block" onclick="details(' . 
				$memberList->product_name->systemid . ')">' .
				 (!empty($memberList->product_name->name) ? $memberList->product_name->name : 
					'Product Name') . '</p>';

            })
            ->addColumn('restaurantnservices_sub_2', function ($memberList) {

                if (!empty($memberList->product_id)) {

                    $subcat_id = product::where('id',$memberList->product_id)->pluck('prdsubcategory_id')->first();
                    
                    $prd_subcategory = prd_subcategory::find($subcat_id);
                }
                return '<p data-field="restaurantnservices_sub" style="margin: 0;">'.(empty($prd_subcategory->name) ? '':$prd_subcategory->name).'</p>';
            })
            ->addColumn('restaurantnservices_price', function ($memberList) {

		    return !empty($memberList->franchise_product) ? '<p style="text-align:right;margin:0">'.
				number_format(($memberList->price / 100), 2).'</p>': 
			    '<p class="os-linkcolor priceOutput"  data-field="restaurantnservices_price" 
			    style="cursor:pointer;margin: 0; text-align: right;">'.(!empty($memberList->price) ? number_format(($memberList->price/100),2): 
				'0.00').'</p>';

            })
            ->addColumn('restaurantnservices_cogs', function ($memberList) {

                return '<p class="os-linkcolor cogsOutput" data-field="restaurantnservices_cogs" style="cursor:pointer; margin: 0; text-align: right;">0.00</p>';

            })
            ->addColumn('restaurantnservices_special', function ($memberList) {
                $product_special = productspecial::where('product_id',$memberList->product_id)->get()->count();

                return '<p class="os-linkcolor" data-field="restaurantnservices_special" style="cursor: pointer; margin: 0;">'.$product_special.'</p>';

            })
            ->addColumn('restaurantnservices_loyalti', function ($memberList) {
				
				return !empty($memberList->franchise_product) ?
					'<p style="text-align:center;margin: 0;">'.(!empty($memberList->loyalty) ?
			   			$memberList->loyalty : '0').'</p>':
					'<p class="os-linkcolor loyaltyOutput" data-field="restaurantnservices_loyalti" 
					style="cursor: pointer; margin: 0; text-align: center;" 
					data-target="#inventoryLoyaltyModal" data-toggle="modal">
					'.(!empty($memberList->loyalty) ? $memberList->loyalty : '0').'</p>';
            })
            ->addColumn('deleted', function ($memberList) {
		if ( !empty($memberList->franchise_product) ) {
				return '';
			}


				return '<div data-field="deleted"
					data-target="#showMsgModal" data-toggle="modal"
					class="remove">
					<img class="" src="/images/redcrab_50x50.png"
					style="width:25px;height:25px;cursor:pointer"/>
					</div>';
            })

            ->escapeColumns([])
            ->make(true);
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
			$company = DB::table('company')->where('id', $this->user_data->company_id())->
				first();
            $merchantproduct = new merchantproduct();
            $SystemID        = new SystemID('product');
            $product         = new product();

            $product->systemid = $SystemID;
            $product->ptype    = "services";
            $product->save();

            $restaurant             = new restaurant();
            $restaurant->product_id = $product->id;

			if (!empty($company->loyalty_pgm))
				$restaurant->loyalty = $company->loyalty_pgm;

            $restaurant->save();

            $merchantproduct->product_id  = $product->id;
            $merchantproduct->merchant_id = $this->user_data->company_id();
            $merchantproduct->save();

            $msg = "Product added successfully";
            return view('layouts.dialog', compact('msg'));

        } catch (\Exception $e) {
			$msg = "Error @ ".$e->getLine()." file ".$e->getFile()." ".
				$e->getMessage();
			Log::error($msg);

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

                if ($fieldName == "restaurantnservices_special") {

                    $product_controller = new ProductController();
                    $special = $product_controller->product_special($request);
                    return $special;

                } else {
                $restaurant = restaurant::where('id', $id)->first();

                return view('restaurant.restaurant-modals',
					compact(['id', 'fieldName', 'restaurant']));
                }
            }

        } catch (\Illuminate\Database\QueryException $ex) {
            $response = (new ApiMessageController())->queryexception($ex);
        }
    }

    public function destroy($id)
    {

        try {
            $this->user_data = new UserData();
            $restaurant      = restaurant::find($id);
            $product_id      = $restaurant->product_id;
            $is_exist        = merchantproduct::where(['product_id' => $product_id, 'merchant_id' => $this->user_data->company_id()])->first();

            if (!$is_exist) {
                throw new Exception("Error Processing Request", 1);
            }

            $is_exist->delete();
            product::find($product_id)->delete();
            $restaurant->delete();

            $msg = "Product deleted successfully";
            return view('layouts.dialog', compact('msg'));
        } catch (\Illuminate\Database\QueryException $e) {
			$msg = "Error @ ".$e->getLine()." file ".$e->getFile()." ".
				$e->getMessage();
			Log::error($msg);

            return view('layouts.dialog', compact('msg'));
        }
    }

    public function update(Request $request)
    {
        try {
            $allInputs = $request->all();
            $restaurant_id = $request->get('restaurant_id');
            $changed = false;
			$msg = $response = null;

            $validation = Validator::make($allInputs, [
                'restaurant_id' => 'required',
            ]);

            if ($validation->fails()) {
                throw new Exception("product_not_found", 1);
            }

			$restaurant = restaurant::find($restaurant_id);

			if (!$restaurant) {
                throw new Exception("product_not_found", 1);
            }

			Log::debug('request->price='.$request->price);

            if ($request->has('price')) {
			if ($restaurant->price != (int) str_replace('.','',
				$request->price)) {

				$restaurant->price = (int) str_replace('.', '',
					$request->price);

				$changed = true;
				$msg = "Price updated successfully";
				}
            }

            if ($changed) {
				$restaurant->save();
				$response = view('layouts.dialog', compact('msg'));
            }

        }  catch (\Exception $e) {
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


    public function showRestaurantView()
    {
        return view('restaurant.restaurant');
    }
}
