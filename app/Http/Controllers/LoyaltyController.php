<?php

namespace App\Http\Controllers;

use Carbon;
use DB;

use App\Models\prd_brand;
use App\Models\prd_inventory;
use App\Models\restaurant;
use App\Models\OgFuel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Matrix\Exception;
use App\Models\Company;
use \App\Classes\UserData;
use App\Models\opos_loyaltyptslog;
use App\Models\opos_loyaltyproductredemption;

class LoyaltyController extends Controller
{
    // inventory loyalty
    public function stored(Request $request)
    {
        try{
            $allInputs = $request->all();
            $prd_inventory_id = $request->get('prd_inventory_id');
            $changed = false;
            
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

            if ($request->has('loyalty')) {
                if ($prd_inventory->loyalty != $request->loyalty) {
                    $prd_inventory->loyalty = $request->loyalty;
                    $changed = true;
                    $msg = "Loyalty updated";
                }
            }


            $products	=	DB::table('product')->
				join('merchantproduct','merchantproduct.product_id','product.id')->
				where('product.id', $prd_inventory->product_id)->
				select('product.systemid')->
				first();

               



            if ($changed == true) {
                $prd_inventory->save();
                $response = view('layouts.dialog', compact('msg'));
            } else {
                $response = null;
            }
            
            $franchiseSync = new FranchiseeOceaniaSync();
            $franchiseSync->syncProductToOceania($products->systemid);

        } catch (\Exception $e) {
            if ($e->getMessage() == 'product_not_found') {
                $msg = "Product not found";
            } else if ($e->getMessage() == 'invalid_loyalty') {
                $msg = "Invalid loyalty";
            } else {
                $msg = "Some error occured";
            }

            //$msg = $e;
            $response = view('layouts.dialog', compact('msg'));
        }
        return $response;
    }


    // Restaurant loyalty
    public function storing(Request $request)
    {
        try {
            $allInputs = $request->all();
            $restaurant_id = $request->get('restaurant_id');
            $changed = false;

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

            if ($request->has('loyalty')) {
                if ($restaurant->loyalty != $request->loyalty) {
                    $restaurant->loyalty = $request->loyalty;
                    $changed = true;
                    $msg = "Loyalty updated";
                }
            }

			if ($changed == true) {
				$restaurant->save();
				$response = view('layouts.dialog', compact('msg'));
            } else {
				$response = null;
			}

        }  catch (\Exception $e) {
            if ($e->getMessage() == 'product_not_found') {
                $msg = "Product not found";
            } else if ($e->getMessage() == 'invalid_loyalty') {
                $msg = "Invalid loyalty";

            } else {
                $msg = "Error @ ".$e->getLine()." file ".$e->getFile()." ".
                    $e->getMessage();
                Log::error($msg);
            }
            $response = view('layouts.dialog', compact('msg'));
        }
        return $response;
    }


    public static function getPoints($product_id)
    {
        $prd_inventory = prd_inventory::where("product_id",$product_id)->get();
        $restaurant = restaurant::where("product_id",$product_id)->get();
        $oil_gas = OgFuel::where("product_id",$product_id)->get();
        if (count($restaurant)) {
            return $restaurant[0]->loyalty;

        } elseif (count($prd_inventory)) {
            return $prd_inventory[0]->loyalty;
        }
        elseif (count($oil_gas)) {
            return $oil_gas[0]->loyalty;
        }

    }
    
    public static function getPointsP($products)
    {
        $totalpoints = 0;
        foreach($products as $product){
            $prd_inventory = prd_inventory::where("product_id",$product->product_id)->get();
            $restaurant = restaurant::where("product_id",$product->product_id)->get();
            $oil_gas = OgFuel::where("product_id",$product->product_id)->get();
            if (count($restaurant)) {
                $totalpoints += ($restaurant[0]->loyalty * $product->quantity);
            } elseif (count($prd_inventory)) {
                $totalpoints += ($prd_inventory[0]->loyalty * $product->quantity);
            }
            elseif (count($oil_gas)) {
                $totalpoints += ($oil_gas[0]->loyalty * $product->quantity) ;
            }
        }
        return $totalpoints;
    }

    

   
        public function save_unconfirmed_data(Request $request)
    {
        // dd($request);
        $systemid = new SystemID('opos_loyaltyptslog');
        $opos_loyaltyptslog = new opos_loyaltyptslog();
        $opos_loyaltyptslog->systemid =$systemid;
        $opos_loyaltyptslog->creator_user_id = Auth::user()->id;
        $opos_loyaltyptslog->type = 'transfer';
        $opos_loyaltyptslog->location_id = $request->location_id;
        $opos_loyaltyptslog->dest_location_id = $request->dest_location_id;
 //       dd($opos_loyaltyptslog);
        $opos_loyaltyptslog->save();

        foreach ($request->products as $product)
        {
            $stockreportproduct = new stockreportproduct();
            $stockreportproduct->product_id = $product['id'];
            $stockreportproduct->quantity = $product['qty'];
            $stockreportproduct->stockreport_id = $stockreport->id;
            $stockreportproduct->save();
        }

        return view('data.trackingconfirm', ["id", $stockreportproduct->id]);

    }

        public static function pointExpiryPeriod(){
            $users = DB::table('opos_loyaltyproductredemption')
            ->select('point_expiry_period')
            ->groupBy('product_id')
            ->first();
            if(!empty($users)){
                return $users->point_expiry_period;
            }else{
                return '1y';
            }
            
        }

       public function updateProductRedemptionPoint(Request $request){
        $loyalty_point = $request->get('new_loyalty_value');
        $product_id = $request->get('product_id');

        if($loyalty_point != ''):
                $get_product = DB::table('opos_loyaltyproductredemption')->where('product_id', $product_id)->get();
                if(count($get_product) == 0){
                    $update = DB::table('opos_loyaltyproductredemption')->insert([
                        'product_id' => $product_id,
                        'redemption_lpts' => $loyalty_point,
                        'created_at' => date('Y-m-d h:i:s'),
                        'updated_at' => date('Y-m-d h:i:s')
                    ]);
                }else{
                    if($loyalty_point >= 0){
                      //  $status = 'inactive';
                    }else{
                     //   $status = 'active';
                    }
                    $update = DB::table('opos_loyaltyproductredemption')->where('product_id', $product_id)->update([
                        'redemption_lpts' => $loyalty_point,'updated_at' => date('Y-m-d h:i:s')
                        ]);
                }
				$getActiveStatus = app('App\Http\Controllers\LoyaltyController')->getActiveStatus($product_id);
				$getRedemptionPoints = app('App\Http\Controllers\LoyaltyController')->getRedemptionPoints($product_id);                        
				$getValidity = app('App\Http\Controllers\LoyaltyController')->getValidity($product_id);
				return response()->json([
					'getActiveStatus' 	=> $getActiveStatus,
					'getRedemptionPoints' 	=> $getRedemptionPoints,
					'getValidity' 	=> $getValidity,
					'blank' => false
				]);
            else:
				return response()->json([
					'getActiveStatus' 	=> $getActiveStatus,
					'getRedemptionPoints' 	=> $getRedemptionPoints,
					'getValidity' 	=> $getValidity,
					'blank' => true
				]);
            endif;

        
    }

    public function updatePeriodExpiry(Request $request){
        $update = DB::table('opos_loyaltyproductredemption')->update(['point_expiry_period' => $request->get('period')]);
        if($update){
            return 'success';
        }else{
            'failed';
        }
    }

    public function updateValidity(Request $request){
        $str = $request->get('date');
        $timestamp = strtotime($str);
        $validity = date('Y-m-d h:i:s A', $timestamp);

        $product_id = $request->get('product_id');
         $get_product = DB::table('opos_loyaltyproductredemption')->where('product_id', $product_id)->get();
        if(empty($get_product)){
            $update = DB::table('opos_loyaltyproductredemption')->insert([
                'product_id' => $product_id,
                'redemption_lpts' => 0,
                'validity' => $validity,
                 'created_at' => date('Y-m-d h:i:s'),
                 'updated_at' => date('Y-m-d h:i:s')
            ]);
        }else{
            $update = DB::table('opos_loyaltyproductredemption')
            ->where('product_id', $product_id)
            ->update(['validity' => $validity, 'updated_at' => date('Y-m-d h:i:s')]);
        }
		$getActiveStatus = app('App\Http\Controllers\LoyaltyController')->getActiveStatus($product_id);
		$getRedemptionPoints = app('App\Http\Controllers\LoyaltyController')->getRedemptionPoints($product_id);                        
		$getValidity = app('App\Http\Controllers\LoyaltyController')->getValidity($product_id);
		return response()->json([
			'getActiveStatus' 	=> $getActiveStatus,
			'getRedemptionPoints' 	=> $getRedemptionPoints,
			'getValidity' 	=> $getValidity
		]);
        
    }

     public function updateActive(Request $request){
        $active_status = $request->get('active_status');
        $product_id = $request->get('product_id');
        
        $get_product = DB::table('opos_loyaltyproductredemption')->where('product_id', $product_id)->get();

        if(empty($get_product)){
            // $update = DB::table('opos_loyaltyproductredemption')->insert([
            //     'product_id' => $product_id,
            //     'redemption_lpts' => 0,
            //     'status' => $active_status,
            //     'created_at' => date('Y-m-d h:i:s'),
            //     'updated_at' => date('Y-m-d h:i:s')

            // ]);
        }else{
            if($active_status == 'active'){
                    $update = DB::table('opos_loyaltyproductredemption')->where('product_id', $product_id)->update([
                        'status' => 'inactive',
                         'updated_at' => date('Y-m-d h:i:s'),
                        ]);
                }else{
                    $update = DB::table('opos_loyaltyproductredemption')->where('product_id', $product_id)->update([
                        'status' => 'active',
                         'updated_at' => date('Y-m-d h:i:s')
                        ]);   
                }
        }

        if($update){
            return 'Saved';
        }else{
            return 'Failed';
        }  

     }   

    

     public static function getRedemptionPoints($product_id)
    {
        $opos_loyaltyproductredemption = DB::table('opos_loyaltyproductredemption')->where("product_id",$product_id)->get();

      if (count($opos_loyaltyproductredemption) && !empty($opos_loyaltyproductredemption)) {
          if($opos_loyaltyproductredemption[0]->redemption_lpts == null){
              $opos_loyaltyproductredemption[0]->redemption_lpts = 0;
			  DB::table('opos_loyaltyproductredemption')->where("product_id",$product_id)->update(['status' => 'inactive']);
          }
			  if($opos_loyaltyproductredemption[0]->redemption_lpts == 0){
				  DB::table('opos_loyaltyproductredemption')->where("product_id",$product_id)->update(['status' => 'inactive']);
			  }
            return $opos_loyaltyproductredemption[0]->redemption_lpts;
        }else{
            return 0;
        }

    }

     public static function getActiveStatus($product_id)
    {
        $opos_loyaltyproductredemption = DB::table('opos_loyaltyproductredemption')->where("product_id",$product_id)->get();

      if (count($opos_loyaltyproductredemption) && !empty($opos_loyaltyproductredemption)) {
          if($opos_loyaltyproductredemption[0]->status == null){
              $opos_loyaltyproductredemption[0]->status = 'inactive';
          }
            return $opos_loyaltyproductredemption[0]->status;
        }else{
            return 'inactive';
        }

    }

    public static function quantityzero($product_id){
		 $opos_loyaltyproductredemption = DB::table('opos_loyaltyproductredemption')->where("product_id",$product_id)->get();
		 if(!is_null($opos_loyaltyproductredemption)){
			 if(sizeof($opos_loyaltyproductredemption) > 0){
				 if($opos_loyaltyproductredemption[0]->validity == null){
					 
				 } else {
					 DB::table('opos_loyaltyproductredemption')->where("product_id",$product_id)->update(['status' => 'inactive']);
				 }
			 }
		 }
		 return true;
	}
	
    public static function getValidity($product_id){
         $opos_loyaltyproductredemption = DB::table('opos_loyaltyproductredemption')->where("product_id",$product_id)->get();

      if (count($opos_loyaltyproductredemption) && !empty($opos_loyaltyproductredemption)) {
          if($opos_loyaltyproductredemption[0]->validity == null){
              $opos_loyaltyproductredemption[0]->validity = '--';
			  DB::table('opos_loyaltyproductredemption')->where("product_id",$product_id)->update(['status' => 'inactive']);
          }else{
			  $date1 = date("Y-m-d");
			  $today = strtotime($date1);
			  $expiration_date = strtotime($opos_loyaltyproductredemption[0]->validity);
				if ($expiration_date < $today) {
				  DB::table('opos_loyaltyproductredemption')->where("product_id",$product_id)->update(['status' => 'inactive']);
				}
              $opos_loyaltyproductredemption[0]->validity  = date("dMy", strtotime($opos_loyaltyproductredemption[0]->validity));
          }
            return $opos_loyaltyproductredemption[0]->validity;
        }else{
            return '--';
        }
    }

    public static function getValidityDate($product_id){
          $opos_loyaltyproductredemption = DB::table('opos_loyaltyproductredemption')->where("product_id",$product_id)->get();

      if (count($opos_loyaltyproductredemption) && !empty($opos_loyaltyproductredemption)) {
          if($opos_loyaltyproductredemption[0]->validity == null){
              $opos_loyaltyproductredemption[0]->validity = '--';
			  DB::table('opos_loyaltyproductredemption')->where("product_id",$product_id)->update(['status' => 'inactive']);
          }else{
              $opos_loyaltyproductredemption[0]->validity  = date("d-m-Y", strtotime($opos_loyaltyproductredemption[0]->validity));
          }
            return $opos_loyaltyproductredemption[0]->validity;
        }else{
            return '--';
        }
    }

    public static function getValigetExpirationDate($product_id){
          $opos_loyaltyproductredemption = DB::table('opos_loyaltyproductredemption')->where("product_id",$product_id)->get();

      if (count($opos_loyaltyproductredemption) && !empty($opos_loyaltyproductredemption)) {
          if($opos_loyaltyproductredemption[0]->validity == null){
              $opos_loyaltyproductredemption[0]->validity = '--';
          }else{
              $opos_loyaltyproductredemption[0]->validity  = date("Y-m-d", strtotime($opos_loyaltyproductredemption[0]->validity));
          }
            $today = date("Y-m-d");
            if ($today > $opos_loyaltyproductredemption[0]->validity) {
                return true;
            }
            else {
                return false;
            }
        }else{
            return true;
        }
    }

}
