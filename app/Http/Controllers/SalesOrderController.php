<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use Milon\Barcode\DNS1D;
use Milon\Barcode\DNS2D;

use App\Models\usersrole;
use App\Models\Salesorderitem;
use App\Models\Salesorder;
use App\Models\product;
use App\Models\Merchant;
use App\Models\Company;
use App\Models\MGlink_so;
use App\Models\Merchantglobal;
use App\Models\MerchantCreditLimit;

use App\Classes\SystemID;
use App\Classes\UserData;
use PDF;
class SalesOrderController extends Controller
{
    //
    public function showSalesOrderView($salesorder_systemid)
    {
		try{
			$user_data = new UserData();

            $salesorder = Salesorder::where('systemid',$salesorder_systemid)->first();


            if (empty($salesorder_systemid) || empty($salesorder)) {
                \Log::info("SaveSalesOrder: invalid saesorder_systemid $salesorder_systemid");
                abort(404);
            }

            $salesorderitem =  Salesorderitem::where("salesorder_id",$salesorder->id)->get();
            
            if (empty($salesorder_systemid)) {
                \Log::info("SaveSalesOrder: broken salesorderitem for salesorder_id:$salesorder->id");
                abort(404);
            }

            $company_dealer = Company::where('owner_user_id',$salesorder->dealer_user_id)->first();
            if (empty($company_dealer)) {
                \Log::info("SaveSalesOrder: broken company record for owner_user_id:$salesorder->dealer_user_id");
                abort(404);
            }

            $creator_user = \App\User::find($salesorder->creator_user_id);
            $creator_user_id_data = new UserData($creator_user);
            $company_creator = Company::find($creator_user_id_data->company_id());

            if (empty($company_creator)) {
                \Log::info("SaveSalesOrder: broken company record for owner_user_id:$salesorder->dealer_user_id");
                abort(404);
            }
            
            $salesorderitem->map(function($item){
                $item->total_price = $item->price * $item->quantity;
            });

            $grand_total = $salesorderitem->reduce(function($carry, $item) {
                return $carry + $item->total_price;
            });
            $barcode = DNS1D::getBarcodePNG(trim($salesorder->systemid), "C128");
            $qr = DNS2D::getBarcodePNG(trim($salesorder->systemid), "QRCODE");

		
			$mgLink = DB::table('mglink_so')->
				where('salesorder_id', $salesorder->id)->
				first();

			$currency_code = DB::table('currency')->
				find($mgLink->currency_id ?? 0)->
				code ?? 'MYR';
	
			$is_do_not_issued = empty(DB::table('deliveryorderproduct')->
				join('salesorderdeliveryorder','salesorderdeliveryorder.deliveryorder_id',
					'=','deliveryorderproduct.deliveryorder_id')->
					where('salesorderdeliveryorder.salesorder_id', $salesorder->id)->
					first());

			$delivery_to_location = DB::table('location')->
				find($salesorder->deliver_to_location_id);

			$salesorder_void = $salesorder->is_void == 1 ? DB::table('users')->
				join('staff','staff.user_id', 'users.id')->select("users.*", 'staff.systemid')->
				where('users.id', $salesorder->void_user_id)->first() : null;

			$is_issuer_side = $company_creator->id == $user_data->company_id(); 

            return view('salesorder.salesorder', compact(
				['salesorder','salesorderitem','company_dealer','company_creator','grand_total',
				'mgLink', 'creator_user','barcode','qr', 'currency_code','salesorder_void', 
				'is_do_not_issued','delivery_to_location', 'is_issuer_side']));
        } catch (\Exception $e) {
            \Log::error($e);
            abort(404);
        }
    }

    public function pdfSalesOrder($salesorder_systemid)
    {
        try{

            $salesorder = Salesorder::where('systemid',$salesorder_systemid)->first();


            if (empty($salesorder_systemid) || empty($salesorder)) {
                \Log::info("SaveSalesOrder: invalid saesorder_systemid $salesorder_systemid");
                abort(404);
            }

            $salesorderitem =  Salesorderitem::where("salesorder_id",$salesorder->id)->get();

            if (empty($salesorder_systemid)) {
                \Log::info("SaveSalesOrder: broken salesorderitem for salesorder_id:$salesorder->id");
                abort(404);
            }

            $company_dealer = Company::where('owner_user_id',$salesorder->dealer_user_id)->first();
            if (empty($company_dealer)) {
                \Log::info("SaveSalesOrder: broken company record for owner_user_id:$salesorder->dealer_user_id");
                abort(404);
            }

            $creator_user = \App\User::find($salesorder->creator_user_id);
            $creator_user_id_data = new UserData($creator_user);
            $company_creator = Company::find($creator_user_id_data->company_id());

            if (empty($company_creator)) {
                \Log::info("SaveSalesOrder: broken company record for owner_user_id:$salesorder->dealer_user_id");
                abort(404);
            }

            $salesorderitem->map(function($item){
                $item->total_price = $item->price * $item->quantity;
            });

            $grand_total = $salesorderitem->reduce(function($carry, $item) {
                return $carry + $item->total_price;
            });
            $barcode = DNS1D::getBarcodePNG(trim($salesorder->systemid), "C128");
            $qr = DNS2D::getBarcodePNG(trim($salesorder->systemid), "QRCODE");


            $mgLink = DB::table('mglink_so')->
            where('salesorder_id', $salesorder->id)->
            first();

            $currency_code = DB::table('currency')->
                find($mgLink->currency_id)->
                code ?? 'MYR';

            $is_do_not_issued = empty(DB::table('deliveryorderproduct')->
            join('salesorderdeliveryorder','salesorderdeliveryorder.deliveryorder_id',
                '=','deliveryorderproduct.deliveryorder_id')->
            where('salesorderdeliveryorder.salesorder_id', $salesorder->id)->
            first());
            $salesorder_void = $salesorder->is_void == 1 ? DB::table('users')->
            join('staff','staff.user_id', 'users.id')->select("users.*", 'staff.systemid')->
            where('users.id', $salesorder->void_user_id)->first() : null;


            $pdf = PDF::setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true])
                ->loadView("salesorder.salesorder_pdf",compact(
                    ['salesorder','salesorderitem','company_dealer','company_creator','grand_total',
                        'mgLink', 'creator_user','barcode','qr', 'currency_code', 'is_do_not_issued','salesorder_void']));

            $pdf->getDomPDF()->setBasePath(public_path().'/');
            $pdf->getDomPDF()->setHttpContext(
                stream_context_create([
                    'ssl' => [
                        'allow_self_signed'=> true,
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                    ]
                ])
            );
            $pdf->setPaper('A4', 'portrait');
            //return $pdf->stream();

            return $pdf->download('saleorder.pdf');
        } catch (\Exception $e) {
            \Log::error($e);
            abort(404);
        }
    }

    public function showSalesOrderIssuedView()
    {
		$order_id = 12;
		
		$user_data = new UserData();
        $company_creator = Company::find($user_data->company_id());

		$salesOrder = Salesorder::where('creator_user_id',$company_creator->owner_user_id)->
			join('salesorderitem', 'salesorderitem.salesorder_id', '=', 'salesorder.id')->
			select('salesorder.*', DB::RAW("SUM(salesorderitem.price * salesorderitem.quantity) as price") )->
			groupBy('salesorder.systemid')->
			orderBy('salesorder.created_at', 'desc')->
			get();

    	return view('salesorder.salesorder_issued_list',compact('order_id', 'salesOrder'));
    }

    public function saveSalesOrder(Request $request) {
        try {
            
            if(!$request->has('merchant_id')){
                \Log::info('merchant_id id');
                abort(404);
            }

            if(!$request->has('tableData')){
                \Log::info('tableData missing');
                abort(404);
            }

			if (empty($request->location_id))
				throw new \Exception("Please choose a 'Deliver To'");

			$user_data = new UserData();
            $systemid = new SystemID('salesorder');
            $do_systemid = new SystemID('deliveryorder');
            $systemid = $systemid->__toString();

            $merchant_id = $request->merchant_id;
			$location_id = $request->location_id;
            $merchant = Merchant::where('company_id',$merchant_id)->first();

            if (empty($merchant)) {
                \Log::info("SaveSalesOrder: invalid merchant id $merchant_id");
                abort(404);
            }

			$supplier_merchant = DB::table('company')->find($user_data->company_id());
            $company = Company::find($merchant->company_id);
            if (empty($company)) {
                \Log::info("SaveSalesOrder: invalid company id $merchant->company_id");
                abort(404);
            }
            
            $salesorder = new Salesorder();
            $salesorder->systemid = $systemid;
            $salesorder->dealer_user_id =  $company->owner_user_id;
            $salesorder->creator_user_id = $supplier_merchant->owner_user_id;
			$salesorder->deliver_to_location_id = $location_id;
            $salesorder->save();

			$merchantlinkrelation_sup = DB::table('merchantlinkrelation')->
				join('merchantlink', 'merchantlink.id','=','merchantlinkrelation.merchantlink_id')->
				where([
					'merchantlink.initiator_user_id' => $company->owner_user_id,
					'merchantlink.responder_user_id' => $supplier_merchant->owner_user_id,
					'merchantlinkrelation.company_id'=> $company->id,
				])->
				orWhere([
					'merchantlink.initiator_user_id' => $supplier_merchant->owner_user_id,
					'merchantlink.responder_user_id' => $company->owner_user_id,
					'merchantlinkrelation.company_id'=> $company->id,
				])->
				where('merchantlinkrelation.ptype','dealer')->
				first();
			
			$merchantlinkrelation_dealer = DB::table('merchantlinkrelation')->
				join('merchantlink', 'merchantlink.id','=','merchantlinkrelation.merchantlink_id')->
				where([
					'merchantlink.initiator_user_id' => $company->owner_user_id,
					'merchantlink.responder_user_id' => $supplier_merchant->owner_user_id,
					'merchantlinkrelation.company_id'=>	$supplier_merchant->id,
				])->
				orWhere([
					'merchantlink.initiator_user_id' => $supplier_merchant->owner_user_id,
					'merchantlinkrelation.company_id'=>	$supplier_merchant->id,
					'merchantlink.responder_user_id' => $company->owner_user_id,
				])->
				where('merchantlinkrelation.ptype','supplier')->
				first();

			$DO = DB::table('deliveryorder')->insertGetId([
				"systemid"			 	=>  $do_systemid,
				'issuer_merchant_id' 	=>  $user_data->company_id(),
				"issuer_location_id" 	=>	$merchantlinkrelation_sup->default_location_id ?? 0,
				"receiver_merchant_id"	=>  $company->id,
				"receiver_location_id"	=>	$location_id,
				"created_at"			=>	date("Y-m-d H:i:s"),	
				"updated_at"			=>	date("Y-m-d H:i:s")
			]);

			DB::table('salesorderdeliveryorder')->insert([
				"salesorder_id"			=>  $salesorder->id,
				"deliveryorder_id"		=>	$DO,
				"created_at"			=>	date("Y-m-d H:i:s"),	
				"updated_at"			=>	date("Y-m-d H:i:s")
			]);

            $tableData = $request->tableData;
			foreach($tableData as $key => $row) {

                $product = product::where('systemid',$row['ProductSysID'])->first();
                
                if (empty($product)) {
                    \Log::info("SaveSalesOrder: Product with system id ".$row['ProductSysID'] ."not found!");
                    continue;
                }
				
				$salesorderitem = new Salesorderitem();
                $salesorderitem->salesorder_id 			= $salesorder->id;
			  	$salesorderitem->product_name			= $row['ProductName'];
				$salesorderitem->product_systemid 		= $row['ProductSysID'];
				$salesorderitem->product_id 			= $row['ProductID'];
                $salesorderitem->price 					= (((int) str_replace(',','',$row['Price'])) * 100);
				$salesorderitem->product_thumbnail 		= $product->thumbnail_1;
                $salesorderitem->quantity 				= $row['Qty'];
                $salesorderitem->save();
            }

		//MGLINK
			$merchantglobal = DB::table('merchantglobal')->
				where('merchant_id', $user_data->company_id())->
				first();

			$supplier_merchant = DB::table('company')->find($user_data->company_id());
			$dealer_merchant = DB::table('company')->find($company->id);

			$mgLink = [];
			$mgLink['salesorder_id']			= $salesorder->id;
			
			$mgLink['so_footer']				= $merchantglobal->so_footer ?? '';
			$mgLink['currency_id']				= $supplier_merchant->currency_id ?? 0;

			$mgLink['supplier_company_name']	= $supplier_merchant->name;
			$mgLink['supplier_business_reg_no'] = $supplier_merchant->business_reg_no ?? '';
			$mgLink['supplier_address'] 		= $supplier_merchant->office_address ?? '';

			$mgLink['dealer_company_name']		= $dealer_merchant->name;
			$mgLink['dealer_business_reg_no'] 	= $dealer_merchant->business_reg_no ?? '';
			$mgLink['dealer_address'] 			= $dealer_merchant->office_address ?? '';

			$mgLink['created_at']				= date("Y-m-d H:i:s");
			$mgLink['updated_at']				= date("Y-m-d H:i:s");

			if (!empty($merchantglobal->so_has_logo)) {
				if ($merchantglobal->so_has_logo == 1 ) {
					$mgLink['so_headerlogo']	= $supplier_merchant->corporate_logo;
				}
			}

			DB::table('mglink_so')->insert($mgLink);


            return response()->json(["salesorder_systemid"=>$systemid]);

        } catch (\Execption $e) {
            \Log::error($e);

			return response()->json(["msg" => $e->getMessage()], 404);
        }
    }

	public function showSalesOrderInvoiceDO(Request $request) {
		try {
			$user_data 				= new UserData();
			$salesorder_systemid 	= $request->salesorder_systemid;

			$salesorder				= Salesorder::where('systemid', $salesorder_systemid)->
				first();

			$salesOrderItem			= Salesorderitem::where('salesorder_id', $salesorder->id)->
				get();

			$supplier_merchant 	=	DB::table('company')->where([
				'owner_user_id'	=>	$salesorder->creator_user_id
			])->first();

			$dealer_merchant 	=	DB::table('company')->where([
				'owner_user_id'	=>	$salesorder->dealer_user_id
			])->first();

			$merchantglobal = DB::table('merchantglobal')->
				where('merchant_id', $user_data->company_id())->
				first();

			$SO_DO	 =	 DB::table('salesorderdeliveryorder')->
				where('salesorder_id', $salesorder->id)->first();

			//is void
			if ($salesorder->is_void == 1)
				return redirect()->back()->with(["msg" => "This is an void sales order."]);

			//invoice
			if (!empty(DB::table('invoice')->where('deliveryorder_id', $SO_DO->deliveryorder_id)->first()))
				return redirect()->
				back()->with(["msg" => "Delivery order and invoice has been already issued."]);

			$systemid_invoice = new SystemID('invoice');

			$invoice_id = DB::table('invoice')->insertGetId([
				'systemid'				=>	$systemid_invoice,
				'dealer_merchant_id'	=>	$dealer_merchant->id,
				'supplier_merchant_id'	=>	$supplier_merchant->id,
				'deliveryorder_id'		=>	$SO_DO->deliveryorder_id,
				'staff_user_id'			=>	Auth::User()->id,
				"created_at"			=>	date("Y-m-d H:i:s"),	
				"updated_at"			=>	date("Y-m-d H:i:s")
			]);
	 
			$DO_products 		= collect();
			$invoice_products 	= collect();

			$salesOrderItem->map(function($m) use ($SO_DO, $invoice_id, $DO_products, $invoice_products) {

				//invoice insertion data
				$invoice_array = $m->toArray();
				unset($invoice_array['salesorder_id']);
				unset($invoice_array['id']);
				$invoice_array["created_at"]	=	date("Y-m-d H:i:s");
				$invoice_array["updated_at"]	=	date("Y-m-d H:i:s");
				$invoice_array['invoice_id'] 	= 	$invoice_id;
				$invoice_products->push($invoice_array);

				//do insertion data
				$do_array = $m->toArray();
				unset($do_array['salesorder_id']);
				unset($do_array['id']);
				$do_array["created_at"]			=	date("Y-m-d H:i:s");
				$do_array["updated_at"]			=	date("Y-m-d H:i:s");
				$do_array['deliveryorder_id']	=	$SO_DO->deliveryorder_id;
				$do_array['checker']			=	0;
				$do_array['status']				=	'pending';
				$do_array['remark']				=	'';
				$DO_products->push($do_array);
			});

			$Totalprice = $invoice_products->reduce(function($a, $b){
				return ($b['price'] * $b['quantity']) + $a;
			});

			$merchant_credit_limit = MerchantCreditLimit::where([
                'dealer_merchant_id'=> $dealer_merchant->id,
                'supplier_merchant_id'=> $supplier_merchant->id
			])->increment('avail_credit_limit',  $Totalprice);


			DB::table('deliveryorder')->where('id', $SO_DO->deliveryorder_id)->update([
				"created_at"	=>	date("Y-m-d H:i:s"),
				"updated_at"	=>	date("Y-m-d H:i:s")
			]);

			DB::table('deliveryorderproduct')->insert($DO_products->toArray());
			DB::table('invoiceproduct')->insert($invoice_products->toArray());	
			$this->mgLink('do', 'deliveryorder_id', $SO_DO->deliveryorder_id, $supplier_merchant, $dealer_merchant, $merchantglobal);
			$this->mgLink('inv', 'invoice_id', $invoice_id, $supplier_merchant, $dealer_merchant, $merchantglobal);
			
			return redirect()->back();

		} catch (\Execption $e) {
			\Log::error([
				"Error"	=> $e->getMessage(),
				"File"	=> $e->getFile(),
				"Line"	=> $e->getLine()
			]);
			abort(404);
		}
	
	}

	function mgLink($slug, $fk, $fk_value, $supplier_merchant, $dealer_merchant, $merchantglobal) {
		
			/*
			 * MgLink
			*/

			$mgLink = [];
			$mgLink['currency_id']				= $supplier_merchant->currency_id ?? 0;

			$mgLink['supplier_company_name']	= $supplier_merchant->name;
			$mgLink['supplier_business_reg_no'] = $supplier_merchant->business_reg_no ?? '';
			$mgLink['supplier_address'] 		= $supplier_merchant->office_address ?? '';

			$mgLink['dealer_company_name']		= $dealer_merchant->name;
			$mgLink['dealer_business_reg_no'] 	= $dealer_merchant->business_reg_no ?? '';
			$mgLink['dealer_address'] 			= $dealer_merchant->office_address ?? '';

			$mgLink['created_at']				= date("Y-m-d H:i:s");
			$mgLink['updated_at']				= date("Y-m-d H:i:s");

			$logo = $slug."_has_logo";
			if (!empty($merchantglobal->$logo)) {
				if ($merchantglobal->$logo == 1 ) {
					$mgLink[$slug.'_headerlogo']	= $supplier_merchant->corporate_logo;
				}
			}

			$footer_slug = $slug.'_footer';
			$mgLink[$fk]	= $fk_value;
			$mgLink[$slug."_footer"]	= $merchantglobal->$footer_slug ?? '';
			DB::table("mglink_".$slug)->insert($mgLink);
	}
}
