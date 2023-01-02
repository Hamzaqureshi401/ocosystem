<?php

namespace App\Http\Controllers;

use App\Models\combine;
use App\Models\openBill;
use App\Models\openBillProduct;
use App\Models\opos_itemdetails;
use App\Models\opos_receipt;
use App\Models\opos_receiptdetails;
use App\Models\oposFtype;
use App\Models\platopenbillproductspecial;
use App\Models\productpreference;
use App\Models\reserve;
use App\Models\skipTable;
use App\Models\skipTableProduct;
use App\Models\skipTableProductSpecial;
use App\Models\splitTable;
use App\Models\opos_receiptproduct;
use App\Models\StockReport;
use App\Models\OgFuel;
use App\Models\OgFuelMovement;
use App\Models\stockreportproduct;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Matrix\Exception;
use phpDocumentor\Reflection\Types\Null_;
use Yajra\DataTables\DataTables;
use \App\Classes\SystemID;
use \App\Classes\UserData;
use \App\Models\location;
use \App\Models\locationterminal;
use \App\Models\membership;
use \App\Models\merchantlocation;
use \App\Models\merchantproduct;
use \App\Models\prd_inventory;
use \App\Models\product;
use \App\Models\restaurant;
use \App\Models\terminal;
use \App\Models\usersrole;
use \App\Models\voucher;
use \App\Models\warranty;
use \Illuminate\Support\Facades\Auth;
use App\Models\Company;
use App\Http\Controllers\OposComponentController;
use App\Models\prdcategory;
use App\Models\prd_subcategory;
use \App\Models\prd_special;
use \App\Models\productspecial;
use \App\Models\opos_btype;
use \App\Models\opos_terminalproduct;
use \App\Models\opos_refund;
use \App\Models\opos_damagerefund;
use \App\Models\locationproduct;
use \App\Models\opos_locationterminal;
use Log;
use DB;
use App\Http\Controllers\OposMemberController;
class RefundController extends Controller
{
    // 
      public function opossumRefund($id){
        
        $user_id = Auth::user()->id;
        $user_roles = usersrole::where('user_id', $user_id)->get();
        $is_king =  Company::where('owner_user_id', Auth::user()->id)->get();
        $receipt_id = opos_receipt::where('id',$id)->get()->first();
        
        $terminal_id = DB::table('opos_receipt')
       ->join('opos_terminal', 'opos_receipt.terminal_id', '=', 'opos_terminal.id')
       ->where('opos_receipt.id',$id)
       ->select('opos_terminal.systemid')
       ->get()->first();

       $branch_name = DB::table('opos_receipt')
       ->join('opos_locationterminal','opos_receipt.terminal_id','=','opos_locationterminal.terminal_id')
       ->join('location','opos_locationterminal.location_id','=','location.id')
       ->where('opos_receipt.id',$id)
       ->select('location.id','location.name','location.branch')
       ->get()->first();       

       $staff_name = DB::table('opos_receipt')
       ->join('users','opos_receipt.staff_user_id','=','users.id')       
       ->where('opos_receipt.id',$id)
       ->select('users.name')
       ->get()->first();       

       $staff_id = DB::table('opos_receipt')
       ->join('staff','opos_receipt.staff_user_id','=','staff.user_id')       
       ->where('opos_receipt.id',$id)
       ->select('staff.systemid')
       ->get()->first();

	Log::debug('BEFORE:'.round(microtime(true) * 1000).' ms');
	Log::debug('receipt_id= '.$id);

		/*
	   $results = DB::select( DB::raw("SELECT * FROM some_table WHERE some_col = :somevariable"),
	   array('somevariable' => $someVariable,));
	   */

		/* Squidster: Switched to raw query for stability & speed as
		 * Eloquent is not stable; Tinker and Controller has different
		 * query speed with no change in Eloquent code */
		$query = "SELECT
			rp.id as receiptproduct_id,
			p.*,
			rp.*,
			id.amount,
			rd.item_amount,
			r.staff_user_id,
			s.systemid as user_number
		FROM
			product p,
			opos_receiptproduct rp,
			opos_itemdetails id,
			opos_receiptdetails rd,
			opos_receipt r,
			staff s
		WHERE
			rp.product_id = p.id 
			AND id.receiptproduct_id = rp.id
			AND rd.receipt_id = rp.receipt_id
			AND r.id = rd.receipt_id
			AND s.user_id = r.staff_user_id
			AND rp.receipt_id = :my_receipt_id";

	$opos_receiptproduct = DB::select($query, ['my_receipt_id' => $id]);
	
/*
        $opos_receiptproduct = DB::table('opos_receiptproduct')
        ->select('opos_receiptproduct.id as receiptproduct_id','product.*','opos_receiptproduct.*', 'opos_itemdetails.amount', 'opos_receiptdetails.item_amount','opos_receipt.staff_user_id','staff.systemid as user_number')
        ->join('product','opos_receiptproduct.product_id','=','product.id')
        ->join('opos_itemdetails','opos_receiptproduct.id','=','opos_itemdetails.receiptproduct_id')
        ->join('opos_receiptdetails','opos_receiptproduct.receipt_id','=','opos_receiptdetails.receipt_id')
        ->join('opos_receipt','opos_receiptdetails.receipt_id','opos_receipt.id')
        ->join('staff','opos_receipt.staff_user_id','staff.user_id')
        ->where('opos_receiptproduct.receipt_id',$id)
        ->get();
		*/

		Log::debug('AFTER: '.round(microtime(true) * 1000). ' ms');
		Log::debug('opos_receiptproduct='.json_encode($opos_receiptproduct));

/*/
        //handling promo
        $promoProducts = DB::table('opos_receiptproduct')
            ->selectRaw('opos_receiptproduct.id as receiptproduct_id, opos_receiptproduct.*, product.*,   opos_receiptproduct.quantity * opos_promoproduct.quantity as quantity')
            ->join('opos_promoproduct', 'opos_promoproduct.promo_id', '=', 'opos_receiptproduct.promo_id')
            ->join('product','opos_promoproduct.product_id','=','product.id')
            ->whereNotNull('opos_receiptproduct.promo_id')
            ->where('receipt_id', $id)->get();


        $indexCounter = count($opos_receiptproduct);
        foreach ($promoProducts as $promoProduct) {
            $opos_receiptproduct[$indexCounter] = $promoProduct;
            $indexCounter++;
        }
        

	foreach ($opos_receiptproduct as $key => $receiptProduct) {
		if (!is_null($receiptProduct->promo_id) && $opos_receiptproduct[$key]->product_id != 0) {
			$product = product::find($receiptProduct->product_id);
			$opos_receiptproduct[$key]->name = $product->name;
		} else if (!is_null($receiptProduct->promo_id) && $opos_receiptproduct[$key]->product_id == 0) {
				$opos_receiptproduct[$key]->name $receiptProduct[$key]->
		}
	}

*/        
       $opos_refund = DB::table('opos_refund')
        ->leftjoin('opos_receiptproduct','opos_receiptproduct.id','=','opos_refund.receiptproduct_id')
        ->where('receipt_id',$id)
        ->get();
        
	$quantity = DB::table('opos_receiptproduct')
        ->select('opos_receiptproduct.id as receiptproduct_id','product.*','opos_receiptproduct.*')
        ->join('product','opos_receiptproduct.product_id','=','product.id')
        ->where('receipt_id',$id)->get();

        // ->sum('quantity');
        $All_refund =(count($opos_refund) >0) ? True : false;
        // dd($All_refund);
	
		$refund_data= array();
        foreach ($opos_refund as $key => $value) {
            $refund_data[$value->receiptproduct_id][$value->item_no] = $value->refund_type;
            $refund_data[$value->receiptproduct_id]['amount'] = $value->refunded_amt;
            $refund_data[$value->receiptproduct_id]['user_id'] = $value->confirmed_user_id;
		}

		foreach ($opos_receiptproduct as $key => $value) {
			$opos_refund = DB::table('opos_refund')
				->leftjoin('opos_receiptproduct','opos_receiptproduct.id','=','opos_refund.receiptproduct_id')
				->where('opos_receiptproduct.id',$value->receiptproduct_id)
				->first();

			$value->refunded_amt = $opos_refund->refunded_amt ?? '0.00';
			$value->user_id = $opos_refund->confirmed_user_id ?? false;

			$opos_receiptproduct[$key] = $value;
		}
		
	$opos_receiptproduct = array_filter ( $opos_receiptproduct, function($f) {
		return empty($f->promo_id);
	}); 
	
	$status = array('active', 'rejected', 'approved', 'pending');
        $refund_type = array('X', 'C', 'Cx', 'D', 'Dx' ,'Cp');
        return view('opossum.opossum_refund', compact(
			'user_roles', 'is_king', 'receipt_id', 'terminal_id',
			'branch_name','staff_name','staff_id', 'opos_receiptproduct',
			'status','refund_type','refund_data','All_refund'));
    }


    public function addProductLedger($location_id , $product_id , $quantity){
        $stock_system = DB::select("select nextval(stockreport_seq) as index_stock");
        $stock_system_id = $stock_system[0]->index_stock;
        $stock_system_id = sprintf("%010s", $stock_system_id);
        $stock_system_id = '111' . $stock_system_id;

        $current_day = date('Y-m-d');

        /*added by Udemezue  for selecting the actual value of ogfuel_id*/
        $og_fuel = Ogfuel::where("product_id", $product_id)->first();
        $og_fuel_id = $og_fuel ? $og_fuel->id : '';
            
        $og_fuelManage = OgFuelMovement::where([
			['ogfuel_id' , $og_fuel_id],
			['location_id' , $location_id]])->
			whereBetween('updated_at', [date($current_day. ' 00:00:00'),
				date($current_day.' 23:59:59')])->
			orderBy('updated_at','DESC')->get()->first();

        if($og_fuelManage){
            $liter = $og_fuelManage->sales - $quantity;
            $og_fuelManage->sales = $liter;  //atif
            $og_fuelManage->book =
				($og_fuelManage->cforward - $liter ) + $og_fuelManage->receipt;
            $og_fuelManage->update();
		}
		$stock = new StockReport();
		$stock->creator_user_id = Auth::user()->id;
		$stock->type = 'refundcp';  /// atif type
		$stock->systemid = $stock_system_id;
		$stock->quantity = $quantity;
		$stock->product_id = $product_id;
		$stock->status = 'confirmed';
		$stock->location_id = $location_id;
		$stock->save();
		/* saving stockreport  &&  stockreportproduct */
		$stockreportproduct = new stockreportproduct();
		$stockreportproduct->quantity = $quantity;
		$stockreportproduct->stockreport_id = $stock->id;
		$stockreportproduct->product_id = $product_id;
		$stockreportproduct->status = 'unchecked';
		$stockreportproduct->save();
        return 1;
    }

    public function quickrefund(Request $request){
        if($request->systemid){
            $systemid = $request->systemid;
            $opos_receipt = opos_receipt::where('systemid' ,$systemid)->first();
            if($opos_receipt){
                $opos_locationterminal = opos_locationterminal::where('terminal_id' ,$opos_receipt->terminal_id)->first();
                if($opos_locationterminal){
                    $opos_receiptproduct = opos_receiptproduct::where('receipt_id',$opos_receipt->id)->first();
                    if($opos_receiptproduct){
                        $prd_ogfuel = \DB::table('prd_ogfuel')->where('product_id',$opos_receiptproduct->product_id)->first()->id;
                        $price = \DB::table('og_fuelprice')->where('ogfuel_id', $prd_ogfuel)->first()->price;
                        $location_id = $opos_locationterminal->location_id;
                        $amount      = explode('MYR',$request->amount);
                        if($amount){
                            $amount = $amount[1];
                            preg_match_all('!\d+!', $amount, $amount);
                            $amount =$amount[0][0].'.'.$amount[0][1];
                        }
                        $product_id  = $opos_receiptproduct->product_id;
                        $liter       = $amount/number_format($price/100, 2);

                        $this->addProductLedger($location_id, $product_id, $liter);

                        $new_opos_refund = new opos_refund(); // new added by atif
						$new_opos_refund->systemid = (new Systemid('refund'))->__toString();
                        $new_opos_refund->receiptproduct_id = $opos_receiptproduct->id;
                        $new_opos_refund->refund_type = "Cp";
                        $new_opos_refund->confirmed_user_id = Auth::id();
                        $new_opos_refund->refunded_amt = $amount; 
                        $new_opos_refund->status = "confirmed";
                        $new_opos_refund->item_no = 1;  ////pendiung
                        $new_opos_refund->save();
                        return 1;
                    }
                }
            }
        }
    }
    
    public function updateRefund(Request $request)
    {   
        try{
           // dd($request);
            $receipt_id="";
			$user_data = new UserData();
            $id = Auth::user()->id;
			$table_data = $request->get('table_data');
            foreach ($table_data as $key => $value) {
                // if($value['qty'] <= 0) { continue; }
                $product_string = $value['product_id'];

                $actual_refund_liter = (isset($value['actual_quantity']))?$value['actual_quantity'] : 0;

                $str_explode = explode("_", $product_string, 2);
                $product_id  = $str_explode[0];
                $item_no  = $str_explode[1];
                $receipt_id = $value['receipt_id'];
                $refund_type = $value['type'];
                $amount = (isset($value['amount']))?$value['amount'] : 0;
                $terminal_id = opos_receipt::
					where('id',$receipt_id)->
					pluck('terminal_id')->first();

                $location_id = opos_locationterminal::
					where('terminal_id',$terminal_id)->
					pluck('location_id')->first();

                $receiptproduct = opos_receiptproduct::
					where('receipt_id',$receipt_id)-> 
					where('product_id',$product_id)->first();

                $opos_refund = opos_refund::
					where('receiptproduct_id',$receiptproduct->id)->
					where('item_no',$item_no)->
					first();

                if( !isset($value['amount']) ||
					$amount != "undefined" &&
					$amount != "0.00"){

                    $check1 = opos_refund::
						where('receiptproduct_id',$receiptproduct->id)->
						where('item_no',$item_no)->
						where('refund_type',$refund_type)->
						first();
					
					if(!$check1){
                        if(!$opos_refund) {
                          // $opos_refund = new opos_refund();
                          $opos_damagerefund = new opos_damagerefund();

                        } else {
                            $opos_damagerefund = opos_damagerefund::
								where('refund_id',$opos_refund->id)->
								first(); 

                            if(!$opos_damagerefund){
                                $opos_damagerefund = new opos_damagerefund();
                            }
                        }

        				Log::debug('RF refund_type='.$refund_type);

                        if($refund_type == "Cp"){
                            $this->addProductLedger($location_id, $product_id,
        						$actual_refund_liter);
                        }

                        $new_opos_refund = new opos_refund();
						$new_opos_refund->systemid = (new Systemid('refund'))->__toString();
						$new_opos_refund->receiptproduct_id = $receiptproduct->id;
                        $new_opos_refund->refund_type = $refund_type;
                        $new_opos_refund->confirmed_user_id = Auth::id();
                        $new_opos_refund->refunded_amt = $amount;
                        // $new_opos_refund->refunded_amt = $actual_refund_liter;
                        $new_opos_refund->status = "confirmed";
                        $new_opos_refund->item_no = $item_no;
                        $new_opos_refund->save();

                        if($refund_type == 'Cx' || $refund_type == 'Dx') {
						//  $opos_damagerefund->systemid = (new SystemID('wastage'))->__toString();
						  $opos_damagerefund->refund_id = $new_opos_refund->id;
                          $opos_damagerefund->damage_qty = 1;
                          $opos_damagerefund->save();
                        }
                        // check if product type is inventory
                        $ptype = product::
							where('id',$product_id)->
							where('ptype','inventory')->first();
						
						if(!$ptype) {
                        //  continue;
						}

							if ($refund_type == 'refund') {
								$loyaltyptslog= DB::table('opos_loyaltyptslog')->
											where('receipt_id',$receipt_id)->
											first();

									if (!empty($loyaltyptslog)) {

										$member_data = DB::table("opos_member")->
											find($loyaltyptslog->member_id);

										$refund_pts  = (( $loyaltyptslog->lpts / $receiptproduct->quantity ) * $actual_refund_liter );
										
										DB::table("opos_member")->
											where("id",$loyaltyptslog->member_id)->
											update([
												"loyaltypts" => $member_data->loyaltypts - $refund_pts
											]);
										
										$system_id	 = new SystemID('loyaltypts');
										$OposMember  = new OposMemberController();

										$OposMember->saveTransactionLogs(
											$system_id, 
											$user_data->user->id, 
											$loyaltyptslog->member_id, ($refund_pts * -1), 
											$loyaltyptslog->source_merchant_id, 
											$user_data->company_id(), 
											$loyaltyptslog->redeemed_merchant_id,
											$loyaltyptslog->receipt_id, 'refunded'	
											);

									}
							}

                        // update product quntity if type inventory
                        if ($refund_type == 'C' || $refund_type == 'Cx' || $refund_type == 'Dx' ) {
                          
                            $terminal_id = opos_receipt::
								where('id',$receipt_id)->
								pluck('terminal_id')->first();

                            $location_id = opos_locationterminal::
								where('terminal_id',$terminal_id)->
								pluck('location_id')->first();

                            if($location_id) {
                              // stock IN
							$locationproduct = locationproduct::
								where('location_id','=',$location_id)->
								where('product_id','=',$product_id)->
								orderby('id','desc')->first();
                            
                            $product = new locationproduct();
                            $product->product_id  = $product_id;
                            $product->location_id = $location_id;
                           
                            // if($refund_type == 2 || $refund_type == 5){
                            if($refund_type == 'C' || $refund_type == 'Dx'){
                                if($locationproduct) {
                                    $curr_qty = $locationproduct->quantity; 
                                    $curr_qty += 1;
                                } else {
                                    $curr_qty = 1;
                                }
                                $product->quantity = $curr_qty;
                            }

                            if($refund_type == 'Dx'){

                                if($locationproduct) {
                                    $damaged_qty = $locationproduct->damaged_quantity; 
                                    $damaged_qty += 1;
                                } else {
                                    $damaged_qty = 1;
                                }
                                $product->damaged_quantity = $damaged_qty;
                            }
                            
                            if($refund_type == 'C' || $refund_type == 'Cx'){
                              $loyaltypts = DB::table('opos_loyaltyptslog')->where('receipt_id',$receipt_id)->first();
                             
                                if(!is_null($loyaltypts)){
									$member_pts = DB::table('opos_member')->
										where('id',$loyaltypts->member_id)->
										first();

									$get_product = DB::table('prd_inventory')->
										where('product_id', $product->product_id)->
										first();
                                  //  dd($product);
                                    if(!is_null($get_product)){
                                        $new_points = $member_pts->loyaltypts - $get_product->loyalty;
                                        $receipt_points = $loyaltypts->lpts - $get_product->loyalty;
                                        //   dd($new_points);
										$update_member_lpt = DB::table('opos_member')->
											where('id',$loyaltypts->member_id)->
											update(['loyaltypts' => $new_points]);

										$loyaltypts = DB::table('opos_loyaltyptslog')->
											where('receipt_id',$receipt_id)->
											update(['lpts' => $receipt_points, 'status' => 'expired']);
                                    }
 
                                }                               
                            }

                            $product->save();
							DB::table('opos_loyaltyptslog')->
								where('receipt_id',$receipt_id)->
								update([
									"deleted_at" => date("Y-m-d H:i:s")
								]);
                        }
					}
                    }///validaion check atif
                }
           }

        $opos_refund = DB::table('opos_refund')
        ->leftjoin('opos_receiptproduct','opos_receiptproduct.id','=','opos_refund.receiptproduct_id')
        ->where('receipt_id',$receipt_id)
        ->get();

        $quantity = DB::table('opos_receiptproduct')
        ->select('opos_receiptproduct.id as receiptproduct_id','product.*','opos_receiptproduct.*')
        ->join('product','opos_receiptproduct.product_id','=','product.id')
        ->where('receipt_id',$receipt_id)->get();
        $flag =(count($opos_refund) == count($quantity) ) ? 1 : 0;

       /* $loyaltypts = DB::table('opos_loyaltyptslog')->where('receipt_id',$receipt_id)->first();
        if(!is_null($loyaltypts)){
            $member_pts = DB::table('opos_member')->where('id',$loyaltypts->member_id)->first();
            $new_points = $member_pts->loyaltypts - $loyaltypts->lpts;
         //   dd($new_points);
            $update_member_lpt = DB::table('opos_member')->where('id',$loyaltypts->member_id)->update(['loyaltypts' => $new_points]);
            $loyaltypts = DB::table('opos_loyaltyptslog')->where('receipt_id',$receipt_id)->update(['lpts' => 0, 'status' => 'expired']);
        }*/

            return $flag;
            $msg = "Refund performed successfully";
            $data = view('layouts.dialog', compact('msg'));

        } catch (\Exception $e) {

			$msg = "Error @ " . $e->getLine() . " file " . $e->getFile() .
                ":" . $e->getMessage();

            Log::error(
                "Error @ " . $e->getLine() . " file " . $e->getFile() .
                ":" . $e->getMessage()
            );

            $data = view('layouts.dialog', compact('msg'));
        }
        return $data;
    }

    public function getRefundData(Request $request){

        $selector = $request->get('pID');
        $product_id = explode('_', $selector)[2];
        $receiptproduct_id = explode('^', intval($request->get('rID')))[0];


        $data = opos_receiptproduct::where('opos_receiptproduct.id',$receiptproduct_id)
            ->select('opos_receiptproduct.id as receiptproduct_id','product.*','opos_receiptproduct.*', 'opos_itemdetails.amount', 'opos_receiptdetails.item_amount')
            ->join('product','opos_receiptproduct.product_id','=','product.id')
            ->join('opos_itemdetails','opos_receiptproduct.id','=','opos_itemdetails.receiptproduct_id')
            ->join('opos_receiptdetails','opos_receiptproduct.receipt_id','=','opos_receiptdetails.receipt_id')
            ->first();//->only('local_price','quantity','amount');
        $data->item_amount = number_format($data->item_amount/100, 2);
        $data->amount = number_format($data->amount/100, 2);
        $data->price = number_format($data->price/100, 2);
        $data->refunded = number_format($data->amount - $data->item_amount);
//        $data->perUnit =  number_format($data->amount/$data->price, 2);
        $opos_refund = opos_refund::where('receiptproduct_id' , $receiptproduct_id)->first();
        if($opos_refund){
             $data->refund_amount = number_format($opos_refund->refunded_amt  ,2);
             $data->liter  = number_format($opos_refund->refunded_amt / $data->price ,2); 
        }

        //atif
        return view('opos_component.refund_edit', compact('data'));
    }

    public function updateRefundEdit(Request $request){

        return '';//view('layouts.dialog', compact('msg'));
        try{
            $refundAmount = $request->filled('refundAmount') ? floatval($request->get('refundAmount')) : 0.00;
            $refundQuantity = $request->filled('refundQuantity') ? $request->get('refundQuantity') : 0.00;
            $receiptProductID =  $request->get('receiptProductID');
			$receiptProduct = opos_receiptproduct::find($receiptProductID);
				
			$receipt_id = $receiptProduct->receipt_id;
			
			$opos_receiptdetails = opos_receiptdetails::where('receipt_id', $receipt_id)->first();
			
			$user_data = new UserData();

			if($refundAmount > $opos_receiptdetails->item_amount){
                $msg = "Cannot refund more than price.";
                return view('layouts.dialog', compact('msg'));
            }

            //Update amount
            /*$newTotal = $opos_receiptdetails->item_amount - $refundAmount*100;

            $opos_receiptdetails->update(['item_amount' => $newTotal]);*/

            $receiptProduct = opos_receiptproduct::find($receiptProductID);

            $opos_refund = new opos_refund;
			$opos_refund->systemid = (new Systemid('refund'))->__toString();
            $opos_refund->receiptproduct_id = $receiptProductID;
            $opos_refund->item_no = $refundQuantity;
            $opos_refund->refund_type = 'C';
			$opos_refund->status = 'approved';
			$opos_refund->refunded_amt = $refundAmount;
            $opos_refund->save();

            $location  = locationproduct::where('product_id', $receiptProduct->product_id)->first()->location;

            $stock_system = DB::select("select nextval(stockreport_seq) as index_stock");
            $stock_system_id = $stock_system[0]->index_stock;
            $stock_system_id = sprintf("%010s", $stock_system_id);
            $stock_system_id = '111' . $stock_system_id;


            $stock = new StockReport();
            $stock->creator_user_id = auth()->id();
            $stock->type = 6; //'refunded'
            $stock->systemid = $stock_system_id;
           // $stock->quantity = $refundQuantity; //add - if reduced
            //$stock->product_id = $receiptProduct->product_id;
            $stock->status = 6; //'refunded'
            $stock->location_id = $location->id;
            $stock->save();

            $stockreportproduct = new stockreportproduct();
            $stockreportproduct->quantity = $refundQuantity; //add - if reduced
            $stockreportproduct->stockreport_id = $stock->id;
            $stockreportproduct->product_id = $receiptProduct->product_id;
            $stock->status = 'checked';
            $stockreportproduct->save();

			$loyaltyptslog= DB::table('opos_loyaltyptslog')->
				where('receipt_id',$receipt_id)->
				first();

			if (!empty($loyaltyptslog)) {

				$member_data = DB::table("opos_member")->
					find($loyaltyptslog->member_id);

				$refund_pts  = (( $loyaltyptslog->lpts / $receiptProduct->quantity ) * $refundQuantity );
				
				DB::table("opos_member")->
					where("id",$loyaltyptslog->member_id)->
					update([
						"loyaltypts" => $member_data->loyaltypts - $refund_pts
					]);
				
				$system_id	 = new SystemID('loyaltypts');
				$OposMember  = new OposMemberController();

				$OposMember->saveTransactionLogs(
					$system_id, 
					$user_data->user->id, 
					$loyaltyptslog->member_id, $refund_pts, 
					$loyaltyptslog->source_merchant_id, 
					$loyaltyptslog->rewarded_merchant_id, 
					$loyaltyptslog->redeemed_merchant_id,
					$loyaltyptslog->receipt_id, 'refunded'	
					);

			}

            $msg = "Refund performed succesfully";
            return view('layouts.dialog', compact('msg'));

        }catch (\Exception $e){
            $msg = "Something went wrong";
			\Log::info($e);
            return view('layouts.dialog', compact('msg'));
        }
    }
}
