<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use \App\Classes\UserData;
use \App\Models\Company;
use \App\Models\merchantbrand;
use \App\Models\merchantprd_category;
use \App\Models\prdcategory;
use \App\Models\prd_brand;
use \App\Models\prd_category;
use \App\Models\prd_subcategory;
use \App\Models\product;
use \Exception;
use \Log;

class ProductCategoriesController extends Controller
{
    protected $user_data;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('CheckRole:stg');
        $this->middleware('CheckRole:onlyuser');

    }

    public function fetchData($type, $fp = true)
    {
        $this->user_data = new UserData();
        $company_id = $this->user_data->company_id();
	
	$franchise_p_id = DB::table('franchiseproduct')->
		leftjoin('franchisemerchant',
		    'franchisemerchant.franchise_id','=','franchiseproduct.franchise_id')->
		leftjoin('product','product.id','=','franchiseproduct.product_id')->
		where([
			'franchisemerchant.franchisee_merchant_id' => $this->user_data->company_id(),
			'franchiseproduct.active' => 1
		])->
		whereNull('franchiseproduct.deleted_at')->
		select('product.prdcategory_id','product.prdsubcategory_id','product.brand_id')->
		get();


        if ($type == 'subcategory') {
            $category_ids = merchantprd_category::where('merchant_id', $company_id)->pluck('category_id');
	    
		 	if ($fp) {
	    		$category_ids_fr = $franchise_p_id->unique('prdcategory_id')->pluck('prdcategory_id');
	  			$category_ids = $category_ids->merge($category_ids_fr)->unique();
			}

            $data = prd_subcategory::whereIn('category_id', $category_ids)->get();
        } else if ($type == 'category') {
		
			$category_ids = merchantprd_category::where('merchant_id', $company_id)->pluck('category_id');
		
			if ($fp) {
		    	$category_ids_fr = $franchise_p_id->unique('prdcategory_id')->pluck('prdcategory_id');
	   			$category_ids = $category_ids->merge($category_ids_fr)->unique();
			 }

	 		$data = prd_category::whereIn('id', $category_ids)->latest()->get();
        } else if ($type == 'product') {
			
			$category_ids = merchantprd_category::where('merchant_id', $company_id)->pluck('category_id');

		 	if ($fp) {
	  			$category_ids_fr = $franchise_p_id->unique('prdcategory_id')->pluck('prdcategory_id');
	   			$category_ids = $category_ids->merge($category_ids_fr)->unique();
			}

            $data = prdcategory::whereIn('category_id', $category_ids)->get();
        } else if ($type == 'brand') {
            $brand_id = merchantbrand::where('merchant_id', $company_id)->pluck('brand_id');
			
			if ($fp) {
	  			$brand_ids_fr = $franchise_p_id->unique('brand_id')->pluck('brand_id');
	   			$brand_id = $brand_id->merge($brand_ids_fr)->unique();
			}

            $data = prd_brand::whereIn('id', $brand_id)->latest()->get();
        } else {
            $data = null;
        }
        return $data;
    }

    public function showModel(Request $request)
    {
        $this->user_data = new UserData();
        $model = 'getdata';
        $company_id = $this->user_data->company_id();

        $validation = Validator::make($request->all(), [
            'addType' => 'required',
        ]);

        $selectcategory = $request->selectcategory;
        $selectsubcategory = $request->selectsubcategory;

        if ($validation->fails()) {
            exit();
        }

        $type = $request->addType;

        $this_company = Company::findOrFail($company_id);
        if ($type == 'deleted') {
            $deleteType = $request->deleteType;
            $id = $request->id;
            $responce = view('productsetting.delete', compact('id', 'deleteType'));
        } else {
            $responce = view('productsetting.models',
                compact(['type', 'model', 'this_company', 'selectcategory', 'selectsubcategory']));
        }
        return $responce;
    }

    public function addSubcategory(Request $request)
    {
        try {
            $this->user_data = new UserData();
            $validation = Validator::make($request->all(), [
                'subcategoryname' => 'required',
                "categoryid" => 'required',
            ]);

            if ($validation->fails()) {
                throw new \Exception("validation_error", 53);
            }

			$category_ids = merchantprd_category::where([
				'category_id' => $request->categoryid,
				'merchant_id' => $this->user_data->company_id()
				])->
				first();

			if (empty($category_ids)) {
                throw new \Exception("forbidden", 53);
			}

            //more code here
            $subcategory = new prd_subcategory();
            $subcategory->name = $request->subcategoryname;
            $subcategory->category_id = $request->categoryid;
            $subcategory->save();
            $msg = "SubCategory successfully saved.";

        } catch (\Exception $e) {
            if ($e->getMessage() == 'validation_error') {
                return '';
			} else if ($e->getMessage() == 'forbidden') {
				$msg = "Action not allowed";
			} else {
                $msg = "Error occured while storing SubCategory";
            }

            Log::error(
                "Error @ " . $e->getLine() . " file " . $e->getFile() .
                ":" . $e->getMessage()
            );
        }

        $responce = view('layouts.dialog', compact('msg'));
        return $responce;
    }

    public function addcategory(Request $request)
    {
        try {
            $this->user_data = new UserData();
            $validation = Validator::make($request->all(), [
                'categoryname' => 'required',
            ]);

            if ($validation->fails()) {
                throw new \Exception("validation_error", 100);
            }

            //more code here
            $category = new prd_category();
            $merchantprd_category = new merchantprd_category();
            $category->name = $request->categoryname;
            $category->save();

            $merchantprd_category->merchant_id = $this->user_data->company_id();
            $merchantprd_category->category_id = $category->id;
            $merchantprd_category->save();

            $msg = "Category successfully saved.";

        } catch (\Exception $e) {
            if ($e->getMessage() == 'validation_error') {
                return '';
            } else if ($e->getMessage() == 'subcategory_not_exist') {
                $msg = "Error: Cannot store category, given subcategory not found";
            } else {
                $msg = "Error occured while storing category";
            }

            Log::error(
                "Error @ " . $e->getLine() . " file " . $e->getFile() .
                ":" . $e->getMessage()
            );
        }

        $responce = view('layouts.dialog', compact('msg'));
        return $responce;
    }

    public function addProduct(Request $request)
    {

        try {

            $this->user_data = new UserData();
            $validation = Validator::make($request->all(), [
                'productname' => 'required',
                "categoryid" => 'required',
                "subcategoryid" => 'required',
            ]);

            if ($validation->fails()) {
                throw new \Exception("validation_error", 100);
            }

            $is_category_exist = prd_category::find($request->categoryid);
            $is_subcategory_exist = prd_subcategory::find($request->subcategoryid);

            if (!$is_category_exist) {
                throw new Exception("category_not_exist", 105);

            }

            if (!$is_subcategory_exist) {
                throw new Exception("subcategory_not_exist", 106);

            }
	
			$category_ids = merchantprd_category::where([
				'category_id' => $request->categoryid,
				'merchant_id' => $this->user_data->company_id()
				])->
				first();

			if (empty($category_ids)) {
                throw new \Exception("forbidden", 53);
			}

            //more code here
            $product = new prdcategory();
            $product->name = $request->productname;
            $product->category_id = $request->categoryid;
            $product->subcategory_id = $request->subcategoryid;
            $product->save();
            $msg = "Product successfully saved.";

        } catch (\Exception $e) {
            if ($e->getMessage() == 'validation_error') {
                return '';
            } else if ($e->getMessage() == 'category_not_exist') {
                $msg = "Error: Cannot store product, given category not found";
            } else if ($e->getMessage() == 'subcategory_not_exist') {
                $msg = "Error: Cannot store product, given subcategory not found";
			} else if ($e->getMessage() == 'forbidden') {
				$msg = "Action not allowed on this category";
			} else {
                $msg = "Error occured while storing product";
            }

            Log::error(
                "Error @ " . $e->getLine() . " file " . $e->getFile() .
                ":" . $e->getMessage() . "| $msg"
            );
        }

        $responce = view('layouts.dialog', compact('msg'));
        return $responce;
    }

    public function addbrand(Request $request)
    {

        try {
            $this->user_data = new UserData();
            $validation = Validator::make($request->all(), [
                'brandname' => 'required',
            ]);

            if ($validation->fails()) {
                throw new \Exception("validation_error", 100);
            }

            $prd_brand = new prd_brand();
            $merchantbrand = new merchantbrand();

            $prd_brand->name = $request->brandname;
            $prd_brand->save();

            $merchantbrand->brand_id = $prd_brand->id;
            $merchantbrand->merchant_id = $this->user_data->company_id();
            $merchantbrand->save();

            $msg = "Brand successfully saved.";

        } catch (\Exception $e) {
            if ($e->getMessage() == 'validation_error') {
                return '';
            } else {
                $msg = "Error occured while storing brand";
            }

            Log::error(
                "Error @ " . $e->getLine() . " file " . $e->getFile() .
                ":" . $e->getMessage() . "| $msg"
            );
        }

        return view('layouts.dialog', compact('msg'));
    }

    public function get_dropDown($option, $key)
    {
        try {
            $this->user_data = new UserData();
            if ($key == '' || $option == '') {
                throw new \Exception("validation_error", 100);
            }
			
			$franchise_p_id = DB::table('franchiseproduct')->
				leftjoin('franchisemerchant',
		    		'franchisemerchant.franchise_id','=','franchiseproduct.franchise_id')->
				leftjoin('product','product.id','=','franchiseproduct.product_id')->
				where([
					'franchisemerchant.franchisee_merchant_id' => $this->user_data->company_id(),
					'franchiseproduct.active' => 1
				])->
				whereNull('franchiseproduct.deleted_at')->
				select('product.prdcategory_id','product.prdsubcategory_id')->
				get();


            if ($option == 'subcat') {
                $category_ids = merchantprd_category::where('merchant_id', $this->user_data->company_id())->pluck('category_id');
				
				$category_ids_fr = $franchise_p_id->unique('prdcategory_id')->pluck('prdcategory_id');
	   			$category_ids = $category_ids->merge($category_ids_fr)->unique();

                $data = prd_subcategory::where('category_id', $key)->whereIn('category_id', $category_ids)->get(['id', 'name','is_matrix'])->toArray();
            } else if ($option == 'product') {
				$category_ids = merchantprd_category::where('merchant_id', $this->user_data->company_id())->pluck('category_id');

			    $category_ids_fr = $franchise_p_id->unique('prdcategory_id')->pluck('prdcategory_id');
		   		$category_ids = $category_ids->merge($category_ids_fr)->unique();

                $data = prdcategory::where('subcategory_id', $key)->whereIn('category_id', $category_ids)->get(['id', 'name'])->toArray();
            } else if ($option == 'cat') {
				$category_ids = merchantprd_category::where('merchant_id', $this->user_data->company_id())->pluck('category_id');

		    	$category_ids_fr = $franchise_p_id->unique('prdcategory_id')->pluck('prdcategory_id');
	   			$category_ids = $category_ids->merge($category_ids_fr)->unique();

                $data = prd_category::whereIn('id', $category_ids)->latest()->get(['id', 'name'])->toArray();
            } else if ($option == 'brand') {
                $brand_id = merchantbrand::where('merchant_id', $this->user_data->company_id())->pluck('brand_id');
                $data = prd_brand::whereIn('id', $brand_id)->latest()->get(['id', 'name'])->toArray();
            }

            $return = response()->json($data);

        } catch (\Exception $e) {
            if ($e->getMessage() == 'validation_error') {
                $msg = "Some error occured while validating data.";
            } else if ($e->getMessage() == 'category_not_exist') {
                $msg = "Error: Cannot store product, given Category not found";
            } else {
                $msg = $e; //"Error occured while populating data";
            }

            Log::error(
                "Error @ " . $e->getLine() . " file " . $e->getFile() .
                ":" . $e->getMessage()
            );

            $return = view('layouts.dialog', compact('msg'));
        }
        return $return;
    }

    public function delete(Request $request)
    {
        try {

            $this->user_data = new UserData();
            $validation = Validator::make($request->all(), [
                'deleteType' => 'required',
                'id' => 'required',
            ]);

            if ($validation->fails()) {
                throw new \Exception("validation_error", 100);
            }

            $id = $request->id;

            switch ($request->deleteType) {
                case 'category':

                    $merchantprd_category = merchantprd_category::where(
                        ['category_id' => $id, 'merchant_id' =>
							$this->user_data->company_id()
						])->first();

                    if (!$merchantprd_category) {
                        throw new Exception("This is a franchise product category, which has been assigned to a franchisee. It is not allowed to be deleted.", 1);

                    }

                    $category = prd_category::find($id);

                    if (!$category) {
                        throw new Exception("Category record not found", 1);
                    }

                    $product_ids = prdcategory::where('category_id', $id)->pluck('id');
                    $is_used = product::whereIn('prdcategory_id', $product_ids)->first();

                    if ($is_used) {
                        $msg = "Category in use, cannot delete";
                        break;
                    }

                    $subcategory = prd_subcategory::where('category_id', $id)->delete();
                    $product = prdcategory::where('category_id', $id)->delete();
                    $category->delete();

                    $msg = "Category deleted sucessfully";
                    break;

                case 'subcategory':
                    $subcategory = prd_subcategory::find($id);

                    if (!$subcategory) {
                        throw new Exception("Subcategory not found.", 1);

                    }

                    $product_ids = prdcategory::where('subcategory_id', $id)->pluck('id');
                    $is_used = product::whereIn('prdcategory_id', $product_ids)->first();

                    if ($is_used) {
                        $msg = "Category in use, cannot delete";
                        break;
                    }

                    $merchantprd_category = merchantprd_category::where(
                        ['category_id' => $subcategory->category_id, 'merchant_id' => $this->user_data->company_id()])->first();

                    if (!$merchantprd_category) {
                        throw new Exception("Category record not found", 1);

                    }

                    $subcategory->delete();
                    $product = prdcategory::where('subcategory_id', $id)->delete();

                    $msg = "SubCategory deleted successfully.";
                    break;
                case 'product':
                    $product = prdcategory::find($id);


                    $is_used = product::where('prdcategory_id', $id)->first();

                    if ($is_used) {
                        $msg = "Category in use, cannot delete";
                        break;
                    }

                    $merchantprd_category = merchantprd_category::where(
                        ['category_id' => $product->category_id, 'merchant_id' => $this->user_data->company_id()])->first();

                    if (!$merchantprd_category) {
                        throw new Exception("Product record not found", 1);
                    }

                    $product->delete();
                    $msg = "Product deleted successfully.";
                    break;
                case 'brand':

                    $prd_brand = prd_brand::find($id);
                    $merchantbrand = merchantbrand::where(['brand_id' => $prd_brand->id, 'merchant_id' => $this->user_data->company_id()])->first();

                    if (!$merchantbrand) {
                        throw new Exception("Brand record not found", 1);
                    }

                    $is_used = product::where('brand_id', $id)->first();

                    if ($is_used) {
                        $msg = "Category in use, cannot delete";
                        break;
                    }

                    $prd_brand->delete();
                    $merchantbrand->delete();
                    $msg = "Brand deleted successfully.";
                    break;
                default:
                    $msg = "Some error occured";
                    break;
            }

        } catch (\Exception $e) {
            if ($e->getMessage() == 'validation_error') {
                $msg = "Some error occured while validating data.";
            } else {
                $msg = $e->getMessage();// "Error occured while populating data";
            }

            Log::error(
                "Error @ " . $e->getLine() . " file " . $e->getFile() .
                ":" . $e->getMessage()
            );
        }
        return view('layouts.dialog', compact('msg'));
    }
}
