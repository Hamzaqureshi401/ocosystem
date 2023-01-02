<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Matrix\Exception;
use \App\Classes\thumb;
use \App\Http\Controllers\ProductCategoriesController;
use \App\Models\product;
use \Log;
use App\Models\voucher;
use \App\Models\restaurant;
use \App\Models\merchantproduct;

use \App\Classes\UserData;
use \App\Models\prd_special;
use \App\Models\productspecial;
use \App\Http\Controllers\APIFranchiseController;
use Illuminate\Support\Facades\Log as FacadesLog;

class ProductController extends Controller
{
    //
    public function dialog(Request $request)
    {

        try {
            $validation = Validator::make($request->all(), [
                'product_id' => 'required',
            ]);

            if ($validation->fails()) {
                throw new \Exception("validation_error", 19);
            }

	    $UserData = new UserData();

            Log::debug('product_id=' . $request->product_id);

            $product_details = product::where('systemid',
                $request->product_id)->first();

            if (!$product_details) {
                throw new \Exception('product_not_found', 25);
            }

            $productsetting = new ProductCategoriesController();

            if (!empty($product_details->prdcategory_id)) {

                $product_product =
                    $productsetting->fetchData('product',false);

				Log::debug('product_product='.json_encode($productsetting));

                $selected_product =
                    $product_product->where('category_id',
                        $product_details->prdcategory_id)->first();

				//Log::debug('selected_product='.json_encode($selected_product));

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

				if (!empty($product_details->prdprdcategory_id) && !empty($selected_product)) {
					$selected_product->prdprdcategory_id =
						$product_details->prdprdcategory_id;
				}

				Log::debug('product_details='.json_encode($product_details));
				Log::debug('product_details->brand_id='.$product_details->brand_id);

				Log::debug('selected_product='.json_encode($selected_product));

				if (!empty($product_details->brand_id)  && !empty($selected_product)) {
					$selected_product->id = $product_details->brand_id;
				}

            } else {

                $product_product = null;
                $product_subcategory = null;
                $selected_product = null;
            }

            $product_brand = $productsetting->fetchData('brand',false);
            $product_category = $productsetting->fetchData('category',false);

	    $allowed_attribs = DB::table('bmatrixattrib')->
		    join('bmatrixattribitem','bmatrixattrib.id', '=',  'bmatrixattribitem.bmatrixattrib_id')->
		    whereNull('bmatrixattrib.deleted_at')->
		    whereNull('bmatrixattribitem.deleted_at')->
		    pluck('bmatrix_id')->toArray();
	    
	    $allowed_attribs = array_unique($allowed_attribs);
		
	    $allowed_colors = DB::table('bmatrixcolor')->
		    where([['color_id','!=','0']])->
		    whereNull('deleted_at')->
		    pluck('bmatrix_id')->toArray();

	    $allowed_colors = array_unique($allowed_colors);

	    $merged_ids  = array_unique(array_merge($allowed_attribs,$allowed_colors));

	    $matrix_ids = DB::table('bmatrix')->
		    where('merchant_id',$UserData->company_id())->
		    whereIn('bmatrix.id',$merged_ids)->
			join('bmatrixbarcode', 'bmatrixbarcode.bmatrix_id', '=', 'bmatrix.id')->
		    whereNull('bmatrix.deleted_at')->
		    pluck('bmatrix.subcategory_id')->toArray();
            $model = 'edit';
            $data = view('product.model', compact(
                'product_details',
                'model',
                'product_category',
                'product_brand',
                'product_subcategory',
                'product_product',
				'selected_product',
				'matrix_ids'
            ));

        } catch (\Exception $e) {
//            return $e->getMessage();
            if ($e->getMessage() == 'validation_error') {
                return '';

            } else if ($e->getMessage() == 'product_not_found') {
                $msg = "Error occured while opening dialog, Invalid product selected";
            } else {
                $msg = 'Error: '.$e->getMessage();
            }

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

            $validation = Validator::make($request->all(), [
                'systemid' => 'required',
            ]);

            Log::debug('Request: '.json_encode($request->file()));

            if ($validation->fails()) {
                throw new \Exception("validation_error", 19);
            }

            $systemid = $request->systemid;
            $product_details = product::where('systemid', $systemid)->first();
	    
			if (!$product_details) {
                throw new \Exception('product_not_found', 25);
            }

            $changed = false;

            if ($request->has('product_name')) {
                if ($product_details->name != $request->product_name) {
                    $product_details->name = $request->product_name;
                    $changed = true;
                }
            }

            if ($request->has('category')) {
                if ($product_details->prdcategory_id != $request->category) {
                    $product_details->prdcategory_id = $request->category;
                    $changed = true;
                }
            }


            if ($request->has('subcategory')) {
                if ($product_details->prdsubcategory_id != $request->subcategory) {
                    $product_details->prdsubcategory_id = $request->subcategory;
                    $changed = true;
                }

                if ($product_details->ptype == 'voucher') {
                    $voucher = voucher::where('product_id', $product_details->id)->first();
                    if($voucher->subcategory_id != $request->subcategory){
                        $voucher->subcategory_id = $request->subcategory;

                        $voucher->save();

                        $changed = true;
                    }
                }
			}

            if ($request->has('prdcategory')) {
                if ($product_details->prdprdcategory_id != $request->prdcategory) {
                    $product_details->prdprdcategory_id = $request->prdcategory;
                    $changed = true;
                }
            }


            if ($request->has('prdbrand')) {
                if ($product_details->brand_id != $request->prdbrand) {
                    $product_details->brand_id = $request->prdbrand;
                    $changed = true;
                }
            }

            if ($request->has('description')) {
                if ($product_details->description != $request->description) {
                    $product_details->description = $request->description;
                    $changed = true;
                }
            }
			
			Log::info([
				'has_category'		=> 	$request->has('category'),
				'has_subcategory'	=>	$request->has('subcategory'),
				'category_id'		=>	$request->category ?? '-',
				'subcategory_id'	=>	$request->subcategory ?? '-',
				'prdcategory'		=>  $request->prdcategory ?? '-',
				'brand'				=>  $request->prdbrand ?? '-',
				'is_changed'		=>  $changed
			]);

            if ($changed == true || true) {
                $product_details->save();

                //Send Details to Ocosystem about product

                /*
                    Check last black record of product price
                    Get product information and details
                    Get ip address
                    push to oceania
                */

				/*
                $product = DB::table('og_fuelprice')
                ->join('prd_ogfuel', 'prd_ogfuel.id', '=', 'og_fuelprice.ogfuel_id' )
                ->join('product', 'product.id','=' ,'prd_ogfuel.product_id')
                ->where('product.systemid', $systemid)
                ->orderBy('start', 'DESC')
                ->select('og_fuelprice.*')->first();

				$ipLocations = null;
                if (!empty($product)) {
                    $ipLocations = 	DB::table('product')
                        ->leftjoin('franchiseproduct', 'franchiseproduct.product_id', '=',  'product.id')
                        ->leftjoin('franchisemerchant', 'franchisemerchant.franchise_id', '=',  'franchiseproduct.franchise_id')
                        ->leftjoin('locationipaddr', 'locationipaddr.company_id', '=',  'franchisemerchant.franchisee_merchant_id')
                        ->leftjoin('location', 'locationipaddr.location_id', '=',  'location.id')
                        ->leftjoin('franchisemerchantloc', 'franchisemerchantloc.location_id', '=',  'location.id')
                        ->leftjoin('franchisemerchantloc as fml', 'franchisemerchant.id', '=',  'fml.franchisemerchant_id')
                        ->where('product.systemid', $systemid)
                        ->select('locationipaddr.ipaddr','locationipaddr.location_id', 'location.branch', 'locationipaddr.company_id')
                        ->groupBy('location.id')
                        ->get();
                }

                $endpoint = '/interface/update/product';
                $call = new APIFranchiseController($endpoint);
                
				if (!empty($ipLocations)) {
					foreach($ipLocations as $location){
						//Make a call
						$payload = array(
							'product_id'=>$product_details->id,
							'prdcategory_id'=>$product_details->prdcategory_id,
							'prdsubcategory_id'=>$product_details->prdsubcategory_id,
							'ptype'=>$product_details->ptype,
							'location_id'=>$location->location_id,
							'brand_id'=>$product_details->brand_id,
							'company_id'=>$location->company_id,
							'photo_1'=>env('APP_URL').'/images/product/'.
								$product_details->id.'/'.$product_details->photo_1,
							'username'=> $request->user()->name,
							'product_name'=>$product_details->name,
							'product_systemid'=>$product_details->systemid,
							'userid'=>$request->user()->id,
							'time'=>$product->updated_at
						);
					  $payload = json_encode($payload);
						$response = $call->sendToOceania(
							$location->ipaddr, $payload);

						Log::debug('response='.json_encode($response));
					}
					Log::debug('payload='.$payload);
					Log::debug('product='.json_encode($product));
					Log::debug('product_details='.json_encode($product_details));
					Log::debug('ipLocation='.json_encode($ipLocations));
				}
				 */
			
                $franchiseSync = new FranchiseeOceaniaSync();
                $franchiseSync->syncProductToOceania($systemid);
				$msg = "Product information updated successfully";
				$data = view('layouts.dialog', compact('msg'));

            } else {
                $data = '';
            }

        } catch (\Exception $e) {

            if ($e->getMessage() == 'validation_error') {
                return '';
            }
            if ($e->getMessage() == 'product_not_found') {
                $msg = "Error occured while uploading, Invalid product selected";
            } else {
                //$msg = "Error occured while updating product";
                $msg = $e->getMessage();
            }

            Log::debug(
                "Error @ " . $e->getLine() . " file " . $e->getFile() .
                ":" . $e->getMessage()
            );

            $data = view('layouts.dialog', compact('msg'));
        }
        return $data;
    }



    public function productSavePicture(Request $request)
    {
        try {
            $validation = Validator::make($request->all(), [
                'product_id' => 'required',
            ]);

            if ($validation->fails()) {
                throw new \Exception("validation_error", 19);
            }

            $product_details = product::where('systemid', $request->product_id)->first();

            if (!$product_details) {
                throw new \Exception('product_not_found', 25);
            }

            if ($request->hasfile('file')) {
                $file = $request->file('file');
                $extension = $file->getClientOriginalExtension(); // getting image extension
                $company_id = Auth::user()->staff->company_id;

                if (!in_array($extension, array(
                    'jpg', 'JPG', 'png', 'PNG', 'jpeg', 'JPEG', 'gif', 'GIF', 'bmp', 'BMP', 'tiff', 'TIFF'))) {
                    return abort(403);
                }

                $filename = ('p' . sprintf("%010d", $product_details->id)) . '-m' . sprintf("%010d", $company_id) . rand(1000, 9999) . '.' . $extension;

                $product_id = $product_details->id;

                $this->check_location("/images/product/$product_id/");
                $file->move(public_path() . ("/images/product/$product_id/"), $filename);

                $this->check_location("/images/product/$product_id/thumb/");
                $thumb = new thumb();

                $dest = public_path() . "/images/product/$product_id/thumb/thumb_" . $filename;
                $thumb->createThumbnail(
                    public_path() . "/images/product/$product_id/" . $filename,
                    $dest,
                    200);
                
                $systemid = $request->product_id;
                /*    
                //Changing Image on Local
                $product = DB::table('og_fuelprice')
                ->join('prd_ogfuel', 'prd_ogfuel.id', '=', 'og_fuelprice.ogfuel_id' )
                ->join('product', 'product.id','=' ,'prd_ogfuel.product_id')
                ->where('product.id', $product_details->id)
                ->orderBy('start', 'DESC')
                ->select('og_fuelprice.*')->first();
                    Log::debug('SystemID: '.$product_details->id);
               

				$ipLocations = null;
                if (!empty($product->start)) {
                    $ipLocations = 	DB::table('product')
                        ->leftjoin('franchiseproduct', 'franchiseproduct.product_id', '=',  'product.id')
                        ->leftjoin('franchisemerchant', 'franchisemerchant.franchise_id', '=',  'franchiseproduct.franchise_id')
                        ->leftjoin('locationipaddr', 'locationipaddr.company_id', '=',  'franchisemerchant.franchisee_merchant_id')
                        ->leftjoin('location', 'locationipaddr.location_id', '=',  'location.id')
                        ->leftjoin('franchisemerchantloc', 'franchisemerchantloc.location_id', '=',  'location.id')
                        ->leftjoin('franchisemerchantloc as fml', 'franchisemerchant.id', '=',  'fml.franchisemerchant_id')
                        ->where('product.id', $product_details->id)
                        ->select('locationipaddr.ipaddr','locationipaddr.location_id', 'location.branch', 'locationipaddr.company_id')
                        ->groupBy('location.id')
                        ->get();
                }
                Log::debug('SystemID: '.$systemid);
                Log::debug('SystemID: '.json_encode($ipLocations));
                Log::debug('SystemID: '.json_encode($product));

                $endpoint = '/interface/update/product';
                $call = new APIFranchiseController($endpoint);
                
				*/
                /*
                $thumb  = <file>
                $this->check_location( "/images/product/$product_id/thumb/" );
                $file->move(public_path().("/images/product/$product_id/thumb/"), $thumb);

                 */

                $product_details->photo_1 = $filename;
                $product_details->thumbnail_1 = 'thumb_' . $filename;
                $product_details->save();
				/*
                if (!empty($ipLocations)) {
					foreach($ipLocations as $location){
						//Make a call
						$payload = array(
							'product_id'=>$product_details->id,
							'prdcategory_id'=>$product_details->prdcategory_id,
							'prdsubcategory_id'=>$product_details->prdsubcategory_id,
							'ptype'=>$product_details->ptype,
							'location_id'=>$location->location_id,
							'brand_id'=>$product_details->brand_id,
							'company_id'=>$location->company_id,
							'photo_1'=>env('APP_URL').'/images/product/'.
								$product_details->id.'/'.$product_details->photo_1,
							'username'=> $request->user()->name,
							'product_name'=>$product_details->name,
							'product_systemid'=>$product_details->systemid,
							'userid'=>$request->user()->id,
							'time'=>$product->updated_at
						);
					  $payload = json_encode($payload);
						$response = $call->sendToOceania(
							$location->ipaddr, $payload);

						Log::debug('response='.json_encode($response));
					}
					Log::debug('payload='.$payload);
					Log::debug('product='.json_encode($product));
					Log::debug('product_details='.json_encode($product_details));
					Log::debug('ipLocation='.json_encode($ipLocations));
				}
				 */

                $franchiseSync = new FranchiseeOceaniaSync();
                $franchiseSync->syncProductToOceania($systemid);

                $return_arr = array(
					"name" => $filename,
					"size" => 000,
					"src" => "/products/$product_id/$filename"
				);

                return response()->json($return_arr);

            } else {
                return abort(403);
            }

        } catch (\Exception $e) {

            if ($e->getMessage() == 'validation_error') {
                return '';
            }
            if ($e->getMessage() == 'product_not_found') {
                $msg = "Error occured while uploading, Invalid product selected";
            }
            {
                $msg = "Error occured while uploading picture";
            }

            Log::error(
                "Error @ " . $e->getLine() . " file " . $e->getFile() .
                ":" . $e->getMessage()
            );

            $data = view('layouts.dialog', compact('msg'));
        }
        return $data;
    }

    public function delPicture(Request $request)
    {

        try {
            $validation = Validator::make($request->all(), [
                'systemid' => 'required',
            ]);

            if ($validation->fails()) {
                throw new \Exception("validation_error", 19);
            }

            $product_details = product::where('systemid', $request->systemid)->first();

            if (!$product_details) {
                throw new \Exception('product_not_found', 25);
            }

            $product_details->photo_1 = null;
            $product_details->thumbnail_1 = null;
            $product_details->save();
            $return = response()->json(array("deleted" => "True"));

        } catch (\Exception $e) {
            $return = response()->json(array("deleted" => "False"));
        }

        return $return;

    }

    public function productImage($product_id, $filename)
    {

        $is_exist = product::where(["id" => $product_id, "photo_1" => $filename])->first();
        if ($is_exist) {

            $headers = array('Content-Type: application/octet-stream', "Content-Disposition: attachment; filename=$filename");
            $location = "/images/product/$product_id/$filename";
            if (!file_exists(public_path() . $location)) {
                return abort(500);
            }

            $response = \Response::file(public_path() . ($location), $headers);

            ob_end_clean();

            return $response;

        } else {
            return abort(404);
        }
    }

    public function productImageThumb($product_id, $filename)
    {

        $is_exist = product::where(["id" => $product_id, "thumbnail_1" => $filename])->first();
        if ($is_exist) {

            $headers = array('Content-Type: application/octet-stream', "Content-Disposition: attachment; filename=$filename");
            $location = "/images/product/$product_id/thumb/$filename";
            if (!file_exists(public_path() . $location)) {
                return abort(500);
            }

            $response = \Response::file(public_path() . ($location), $headers);

            ob_end_clean();

            return $response;

        } else {
            return abort(404);
        }

    }

    public function product_special(Request $request)
    {

        try {

            $product_id = $request->product_id;
            $special_ids = productspecial::where('product_id', $product_id)->pluck('special_id');
            $prd_special = prd_special::whereIn('id', $special_ids)->get();
            return view('prd_special.prd_special_modal', compact('product_id', 'prd_special'));

        } catch (\Exception $e) {

            if ($e->getMessage() == 'validation_error') {
                return '';
            }
            if ($e->getMessage() == 'product_not_found') {
                $msg = "Error occured while uploading, Invalid product selected";
            }
            {
                $msg = "Error occured while updating product";
            }

            Log::error(
                "Error @ " . $e->getLine() . " file " . $e->getFile() .
                ":" . $e->getMessage()
            );

            $data = view('layouts.dialog', compact('msg'));
        }
    }

    public function store_product_special(Request $request)
    {
        try {

            $validation = Validator::make($request->all(), [
                'button_name' => 'required',
                'button_price' => 'required',
                'product_id' => 'required'
            ]);

            if ($validation->fails()) {
                throw new \Exception("validation_error", 19);
            }

            $product_is_exist = product::find($request->product_id);

            if (!$product_is_exist) {
                throw new Exception("product_not_found", 1);

            }


            $prd_special = new  prd_special();
            $productspecial = new productspecial();

            $prd_special->name = $request->button_name;
            $prd_special->price = (int)str_replace('.', '', number_format($request->button_price, 2));
            $prd_special->save();

            $productspecial->special_id = $prd_special->id;
            $productspecial->product_id = $request->product_id;
            $productspecial->save();

            $data = response()->json(
                ['status' => 'Saved', 'special_id' => $prd_special->id]);


        } catch (\Exception $e) {

            if ($e->getMessage() == 'validation_error') {
                return '';
            }
            if ($e->getMessage() == 'product_not_found') {
                $msg = "Error occured while storing, Invalid product selected";
            }
            {
                $msg = "Error occured while updating product";
            }

            Log::error(
                "Error @ " . $e->getLine() . " file " . $e->getFile() .
                ":" . $e->getMessage()
            );

            $data = view('layouts.dialog', compact('msg'));
        }

        return $data;
    }

    public function delete_product_special(Request $request)
    {
        try {

            $validation = Validator::make($request->all(), [
                'id' => 'required',
            ]);
            $user_data = new UserData();

            $prd_special = prd_special::find($request->id);

            $productspecial = productspecial::where('special_id', $prd_special->id)->first();


            $merchant_product = merchantproduct::where(
                ['merchant_id' => $user_data->merchant_id(), 'product_id' => $productspecial->product_id])->first();

            if ($validation->fails()) {
                throw new \Exception("validation_error", 19);
            }


            if (!$prd_special || !$productspecial || !$merchant_product) {
                throw new Exception("product_not_found", 1);
            }

            $prd_special->delete();
            $productspecial->delete();

            $data = response()->json(["Delete" => "successfully"]);

        } catch (\Exception $e) {

            if ($e->getMessage() == 'validation_error') {
                return '';
            } else if ($e->getMessage() == 'product_not_found') {
                $msg = "Error occured while deleting, Invalid special selected";
            } else {
                $msg = '';
            }

            Log::error(
                "Error @ " . $e->getLine() . " file " . $e->getFile() .
                ":" . $e->getMessage() . " | " . $msg
            );

            $data = view('layouts.dialog', compact('msg'));
        }

        return $data;
    }


    public function check_location($location)
    {
        $location = array_filter(explode('/', $location));
        $path = public_path();

        foreach ($location as $key) {
            $path .= "/$key";

			Log::debug('check_location(): $path='.$path);

            if (is_dir($path) != true) {
                mkdir($path, 0775, true);
            }
        }
    }
}
