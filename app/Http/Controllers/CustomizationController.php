<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Log;
use Matrix\Exception;
use Yajra\DataTables\DataTables;
use \App\Classes\SystemID;
use \App\Classes\UserData;
use \App\Models\merchantproduct;
use \App\Models\product;
use \App\Models\prd_proservices;
use \App\Models\restaurant;
use DB;
class CustomizationController extends Controller
{
    public function index()
    {
        $this->user_data = new UserData();
        $model           = new prd_proservices();
		
		// $this->user_data = new UserData();
        // $model           = new restaurant();

        // $ids  = merchantproduct::where('merchant_id', $this->user_data->company_id())->pluck('product_id');
        // $ids  = product::where('ptype', 'services')->whereIn('id', $ids)->pluck('id');
        // $data = $model->whereIn('product_id', $ids)->orderBy('created_at', 'asc')->latest()->get();
		$query = "        
		SELECT 
			prd.id as id,
			prd.product_id,
			prd.price as price,
			p.systemid as systemid,
			p.name as name,
			p.thumbnail_1 as thumbnail_1
		FROM prd_proservice as prd
		JOIN product p ON prd.product_id = p.id 
		JOIN merchantproduct mp ON p.id  = mp.product_id 
		WHERE mp.merchant_id = ". $this->user_data->company_id(). "
		AND p.ptype = 'customization'
		ORDER BY 
		 prd.created_at 
		DESC";
        $data = DB::select(DB::raw($query));

        return Datatables::of($data)
            ->addIndexColumn()
            ->addColumn('cuz_productid', function ($data) {
                return '<p class="os linkcolor" data-field="cuz_productid" style="margin: 0;text-align: center;">' . (!empty($data->systemid)? $data->systemid : '000000000000') . '</p>';
            })
            ->addColumn('cuz_productname', function ($data) {
                
                if (!empty($data->thumbnail_1)) {
                    
                    $img_src = '/images/product/' .
                        $data->product_id . '/thumb/' .
                        $data->thumbnail_1;
                    
                    $img = "<img src='$img_src' data-field='inven_pro_name' style=' width: 25px;height: 25px;display: inline-block;margin-right: 8px;object-fit:contain;'>";
                    
                }  else {
                    $img = null;
                } 
                
                return $img.'<p class="os-linkcolor" data-field="cuz_productname" onclick="details(' . $data->systemid . ')" style="cursor: pointer; margin: 0;display: inline-block">' . (!empty($data->name) ? $data->name : 'Product Name') . '</p>';
            })
            ->addColumn('cuz_price', function ($data) {

                return '<p class="os-linkcolor priceOutput" data-target="#customizationPriceModal" data-toggle="modal" data-field="cuz_price" data-product_id="' . $data->product_id . '" style="cursor:pointer;margin: 0; text-align: right;">'.(!empty($data->price) ? number_format($data->price/100,2,'.',','): '0.00').'</p>';
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

			
				$model           = new prd_proservices();
                $customization = $model::where('id', $id)->first();
				
                return view('customization.customization-modals',
					compact(['id', 'fieldName','customization']));
            } catch (\Illuminate\Database\QueryException $ex) {
			$response = (new ApiMessageController())->queryexception($ex);
		}
    }

    public function showCustomizationView() 
    {
        return view('customization.customization');
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
            $product->ptype    = 'customization';
            $product->save();

            $prd_proservices = new prd_proservices();
            $prd_proservices->product_id = $product->id;
            $prd_proservices->save();
			
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
            $data = view('customization.model', compact(
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
			
			$customization = prd_proservices::find($id);
			$is_exist = merchantproduct::where([
				'product_id' => $customization->product_id,
				'merchant_id' => $this->user_data->company_id()
			])->first();
			
			if (!$is_exist) {
				throw new Exception("Error Processing Request", 1);
			}
			$is_exist->delete();
			$product_id = $customization->product_id;
			product::find($product_id)->delete();
			$customization->delete();
			
			$msg = "Service deleted successfully";
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
			
			$prd_proservices = prd_proservices::find($product_id);
			
			if (!$prd_proservices) {
				throw new Exception("product_not_founds", 1);
			}
			
			if ($request->price) {				
				if ($prd_proservices->price != $request->price) {
					$prd_proservices->price = $request->price;
					$changed = true;
					$msg = "Price updated Successfully";
				}
			}
			
			if ($changed == true) {
				$prd_proservices->save();
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
				$msg = $e->getMessage();
			}
			
			//$msg = $e;
			$response = view('layouts.dialog', compact('msg'));
		}
		return $response;
	}
	
	
}
