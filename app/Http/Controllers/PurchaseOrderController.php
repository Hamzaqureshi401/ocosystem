<?php

namespace App\Http\Controllers;

use Dompdf\Dompdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\usersrole;
use App\Models\PurchaseOrder;
use App\Models\DeliveryOrder;
use App\Models\Invoice;
use App\Models\MerchantCreditLimit;
use DB;
use Illuminate\Support\Facades\Log;
use Milon\Barcode\DNS1D;
use Milon\Barcode\DNS2D;
use \App\Classes\SystemID;
use \App\Classes\UserData;
use PDF;
class PurchaseOrderController extends Controller
{
    //
    public function showPurchaseOrderView($id, $approve = null, $pdf = false)
    {
		$user_data  = new UserData();
		//dd($id);
		$purchaseorder = DB::table('purchaseorder')
							->join('merchantpurchaseorder', 'merchantpurchaseorder.purchaseorder_id','=','purchaseorder.id')
							->join('merchant', 'merchantpurchaseorder.merchant_id','=','merchant.company_id')
							->join('company', 'company.id','=','merchant.company_id')
							->where('purchaseorder.id', $id)
							->select('purchaseorder.*','company.name','company.office_address','company.id as company_id','company.corporate_logo')
							->first();
							
		$issuer_merchant = DB::table('merchant')->join('company', 'company.id','=','merchant.company_id')->where('merchant.company_id',
			$purchaseorder->issuer_merchant_id)->join('users','users.id','=','company.owner_user_id')
			->select('users.name as username','company.*','merchant.company_id')->first();

		$code = DNS1D::getBarcodePNG(trim($purchaseorder->systemid), "C128");
		$qr = DNS2D::getBarcodePNG($purchaseorder->systemid, "QRCODE");
		
		$poproducts = DB::table('purchaseorderproduct')->
			where('purchaseorderproduct.purchaseorder_id', $id)->
			select('purchaseorderproduct.quantity', 'purchaseorderproduct.purchase_price',
				'purchaseorderproduct.purchaseorder_id','purchaseorderproduct.product_id',
				'purchaseorderproduct.product_name as name','purchaseorderproduct.product_thumbnail as thumbnail_1',
				'purchaseorderproduct.product_systemid as systemid')->
				get();

		$total = $poproducts->reduce(function($ac, $rec) {
			return $ac + ($rec->purchase_price * $rec->quantity);});
		$mgLink = DB::table('mglink_po')->
			where('purchaseorder_id', $id)->
			latest()->
			first();
			
		$currency_code = DB::table('currency')->
			find($mgLink->currency_id)->
			code ?? 'MYR';

		$is_do_inv_not_issued = empty(DB::table('purchaseorderdeliveryorder')->
			where('purchaseorder_id', $purchaseorder->id)->
			first());

		$is_issuer_side = $issuer_merchant->company_id == $user_data->company_id();

		$purchaseorder_void = DB::table('users')->join('staff','users.id','staff.user_id')->
			select('users.*','staff.systemid')->where('users.id',$purchaseorder->void_user_id)->
			first();
	
		$compact_data  =	 compact('issuer_merchant', 'purchaseorder', 'code', 'purchaseorder_void',
			'currency_code','qr','poproducts', 'approve',
		   	'mgLink','total', 'is_do_inv_not_issued', 'is_issuer_side');
			
		$view =  view('purchaseorder.purchaseorder',$compact_data);
		return $pdf ? $compact_data : $view;
    }

	public function pdfPurchaseOrder(Request $request) {
		try {

			$data = $this->showPurchaseOrderView($request->order_id, null, true);	
			$htmlView = view('purchaseorder.purchaseorderpdf', $data);
			$options = new \Dompdf\Options();
			$options->setIsHtml5ParserEnabled(true);
			$dompdf = new Dompdf($options);
			$dompdf->loadHtml($htmlView);

			// (Optional) Setup the paper size and orientation
			$dompdf->setPaper('A4', 'landscape');

			// Render the HTML as PDF
			$dompdf->render();

			// Output the generated PDF to Browser
			$dompdf->stream();
		} catch (\Exception $e) {
			Log::info([
				"Error"	=>	$e->getMessage(),
				"File"	=>	$e->getFile(),
				"Line"	=>	$e->getLine()
			]);
			abort(404);
		}
	
	}

    public function pdfPurchaseOrderDownload($id, $approve = null, $pdf = false) {
        try {
            $user_data  = new UserData();
            //dd($id);
            $purchaseorder = DB::table('purchaseorder')
                ->join('merchantpurchaseorder', 'merchantpurchaseorder.purchaseorder_id','=','purchaseorder.id')
                ->join('merchant', 'merchantpurchaseorder.merchant_id','=','merchant.company_id')
                ->join('company', 'company.id','=','merchant.company_id')
                ->where('purchaseorder.id', $id)
                ->select('purchaseorder.*','company.name','company.office_address','company.id as company_id','company.corporate_logo')
                ->first();

            $issuer_merchant = DB::table('merchant')->join('company', 'company.id','=','merchant.company_id')->where('merchant.company_id',
                $purchaseorder->issuer_merchant_id)->join('users','users.id','=','company.owner_user_id')
                ->select('users.name as username','company.*','merchant.company_id')->first();

            $code = DNS1D::getBarcodePNG(trim($purchaseorder->systemid), "C128");
            $qr = DNS2D::getBarcodePNG($purchaseorder->systemid, "QRCODE");

            $poproducts = DB::table('purchaseorderproduct')->
            where('purchaseorderproduct.purchaseorder_id', $id)->
            select('purchaseorderproduct.quantity', 'purchaseorderproduct.purchase_price',
                'purchaseorderproduct.purchaseorder_id','purchaseorderproduct.product_id',
                'purchaseorderproduct.product_name as name','purchaseorderproduct.product_thumbnail as thumbnail_1',
                'purchaseorderproduct.product_systemid as systemid')->
            get();

            $total = $poproducts->reduce(function($ac, $rec) {
                return $ac + ($rec->purchase_price * $rec->quantity);});
            $mgLink = DB::table('mglink_po')->
            where('purchaseorder_id', $id)->
            latest()->
            first();

            $currency_code = DB::table('currency')->
                find($mgLink->currency_id)->
                code ?? 'MYR';

            $is_do_inv_not_issued = empty(DB::table('purchaseorderdeliveryorder')->
            where('purchaseorder_id', $purchaseorder->id)->
            first());

            $is_issuer_side = $issuer_merchant->company_id == $user_data->company_id();

            $purchaseorder_void = DB::table('users')->join('staff','users.id','staff.user_id')->
            select('users.*','staff.systemid')->where('users.id',$purchaseorder->void_user_id)->
            first();

            $pdf = PDF::loadView("purchaseorder.purchaseorder_pdf",compact('issuer_merchant', 'purchaseorder', 'code',
                'currency_code','qr','poproducts', 'approve', 'mgLink','total','purchaseorder_void'));


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
            return $pdf->download('purchaseorder.pdf');


        } catch (\Exception $e) {
            
            Log::info([
                "Error"	=>	$e->getMessage(),
                "File"	=>	$e->getFile(),
                "Line"	=>	$e->getLine()
            ]);
            abort(404);
        }

    }

    public function approvePo(Request $request)
	{
		$user_data = new UserData();

		PurchaseOrder::where('id', $request->po_id)
          ->update(['status' => 'confirmed']);
		  
		$purchaseorder =   PurchaseOrder::where('purchaseorder.id', $request->po_id)
							->join('merchantpurchaseorder', 'merchantpurchaseorder.purchaseorder_id','=','purchaseorder.id')
							->select('purchaseorder.*','merchantpurchaseorder.merchant_id as receiver_merchant_id')
							->first();
		 
		$systemid = new SystemID('deliveryorder');
        $DeliveryOrder = new DeliveryOrder();
		$DeliveryOrder->systemid =$systemid->__toString();
        $DeliveryOrder->deliveryman_user_id = 0;
        $DeliveryOrder->issuer_merchant_id = $purchaseorder->issuer_merchant_id;
        $DeliveryOrder->issuer_location_id = $purchaseorder->issuer_location_id;
        $DeliveryOrder->receiver_merchant_id = $purchaseorder->receiver_merchant_id;
        $DeliveryOrder->status = 'pending';
        $DeliveryOrder->purchaseorder_id = $purchaseorder->id;
		$DeliveryOrder->save();
		$systemid = $systemid->__toString();  
		
		$poproducts = DB::table('purchaseorderproduct')->
			join('product','product.id','=','purchaseorderproduct.product_id')
		   ->where('purchaseorderproduct.purchaseorder_id', $request->po_id)
		   ->select('purchaseorderproduct.*','product.id as product_id',
					'product.name as name','product.thumbnail_1','product.systemid')
		   ->get();
													   
		$Invoice = new Invoice();
		$inv_systemid = new SystemID('invoice');
		$Invoice->systemid =$inv_systemid->__toString();
        $Invoice->deliveryorder_id = $DeliveryOrder->id;
        $Invoice->supplier_merchant_id = $purchaseorder->issuer_merchant_id;
        $Invoice->dealer_merchant_id = $purchaseorder->receiver_merchant_id;
        $Invoice->staff_user_id = 0;
		$Invoice->save();
		
		$merchantglobal = DB::table('merchantglobal')->
			where('merchant_id', $user_data->company_id())->
			first();

		$company = DB::table('company')->find($user_data->company_id());

		$mgLink = [];
		$mgLink['deliveryorder_id']	= $DeliveryOrder->id;
		$mgLink['do_footer']		= $merchantglobal->do_footer;
		$mgLink['created_at']		= date("Y-m-d H:i:s");
		$mgLink['updated_at']		= date("Y-m-d H:i:s");

		if ($merchantglobal->do_has_logo == 1 ) {
			$mgLink['do_headerlogo']	= $company->corporate_logo;
		}

		DB::table('mglink_do')->insert($mgLink);

		foreach($poproducts as $pproduct){
			DB::table('deliveryorderproduct')->insert([
				'deliveryorder_id' => $DeliveryOrder->id,
				'product_id' => $pproduct->product_id,
				'quantity' => $pproduct->quantity,
				'remark' => '',
				'status' => 'pending',
				'created_at' => date('Y-m-d H:i:s'),
				'updated_at' => date('Y-m-d H:i:s'),
			]);
			
			DB::table('invoiceproduct')->insert([
				'invoice_id' => $Invoice->id,
				'product_id' => $pproduct->product_id,
				'quantity' => $pproduct->quantity,
				'created_at' => date('Y-m-d H:i:s'),
				'updated_at' => date('Y-m-d H:i:s'),
			]);
		}
		
		
		return response()->json(['msg' => 'Succcess', 'status' => 'true'
								, 'systemid' => $systemid, 'do_id' => $DeliveryOrder->id]);
	}
	
    public function showPurchaseOrderIssuedView()
    {
		$user_data = new UserData();
        
		/*	
		$my_company_detail =  \App\Models\Company::where('id', $user_data->company_id())->first();
		$issuer_merchant_id = DB::table('merchant')->where('company_id',
			$my_company_detail->id)->first()->id;
		*/

		$purchaseorders = DB::table('purchaseorder')
							->join('merchantpurchaseorder', 'merchantpurchaseorder.purchaseorder_id','=','purchaseorder.id')
							->join('merchant', 'merchantpurchaseorder.merchant_id','=','merchant.company_id')
							->join('company', 'company.id','=','merchant.company_id')
							->where('issuer_merchant_id', $user_data->company_id())
							->select('purchaseorder.id','purchaseorder.systemid','purchaseorder.status','company.name')
							->orderBy('purchaseorder.created_at', 'desc')
							->get();
		
		foreach($purchaseorders as $po){
			$sum = DB::table('purchaseorderproduct')->
				where('purchaseorder_id',$po->id)->
				sum(\DB::raw('purchase_price * quantity'));
			$po->amount = $sum;
		}

		return view('purchaseorder.purchaseorder_issued_list',
			compact('purchaseorders'));
    }

    public function showPurchaseOrderRecievedView()
    {

		 //$my_company_detail =  \App\Models\Company::where('owner_user_id',Auth::user()->id)->first();
		$user_data = new UserData();
		$merchant_id = $user_data->company_id();
			
		$purchaseorders = DB::table('purchaseorder')
							->join('merchantpurchaseorder', 'merchantpurchaseorder.purchaseorder_id','=','purchaseorder.id')
							->join('merchant', 'purchaseorder.issuer_merchant_id','=','merchant.company_id')
							->join('company', 'company.id','=','merchant.company_id')
							->where('merchantpurchaseorder.merchant_id', $merchant_id)
							->select('purchaseorder.id','purchaseorder.systemid','purchaseorder.status','company.name')
							->orderBy('purchaseorder.created_at', 'desc')
							->get();
		foreach($purchaseorders as $po){
			$sum = DB::table('purchaseorderproduct')->
				where('purchaseorder_id',$po->id)->
				sum(\DB::raw('purchase_price * quantity'));
			$po->amount = $sum;
		}						
    	return view('purchaseorder.purchaseorder_received_list',compact('purchaseorders'));
    }

	public function showPurchaseOrderDOInvoice(Request $request) {
		try {
		
			$purchaseorder_systemid 	= $request->purchaseorder_systemid;

			$purchaseOrder				= DB::table('purchaseorder')->
				where('systemid', $purchaseorder_systemid)->first();

			$purchaseOrderItem 			=  DB::table('purchaseorderproduct')->
				where('purchaseorder_id', $purchaseOrder->id)->get();

			$supplier_merchant = DB::table('merchantpurchaseorder')->
				where('purchaseorder_id', $purchaseOrder->id)->first();
		
			$supplier_merchant 	=	DB::table('company')->where([
				'id'	=>	$supplier_merchant->merchant_id
			])->first();

			$dealer_merchant 	=	DB::table('company')->where([
				'id'	=>	$purchaseOrder->issuer_merchant_id
			])->first();

			$merchantglobal = DB::table('merchantglobal')->
				where('merchant_id', $dealer_merchant->id)->
				first();

			if ($purchaseOrder->is_void == 1)
				return redirect()->back()->with(
					["msg" => "This is a void Purchase Order."]);


			if (!empty(DB::table('purchaseorderdeliveryorder')->where('purchaseorder_id', $purchaseOrder->id)->first()))
				return redirect()->
				back()->with(["msg" => "Delivery order and invoice has been already issued."]);
			/////////////////////////////
			//creating delivery Records
			
			$merchantlinkrelation_sup = DB::table('merchantlinkrelation')->
				join('merchantlink', 'merchantlink.id','=','merchantlinkrelation.merchantlink_id')->
				where([
					'merchantlink.initiator_user_id' 	=> $dealer_merchant->owner_user_id,
					'merchantlink.responder_user_id'	=> $supplier_merchant->owner_user_id,
					'merchantlinkrelation.company_id'	=>	$supplier_merchant->id,
					'merchantlinkrelation.ptype'	 	=> 'dealer'
				])->
				orWhere([
					'merchantlink.initiator_user_id' => $supplier_merchant->owner_user_id,
					'merchantlink.responder_user_id' => $dealer_merchant->owner_user_id,
					'merchantlinkrelation.company_id'=>$supplier_merchant->id,
					'merchantlinkrelation.ptype'	 => 'dealer'
				])->
				first();
			
			$merchantlinkrelation_dealer = DB::table('merchantlinkrelation')->
				join('merchantlink', 'merchantlink.id','=','merchantlinkrelation.merchantlink_id')->
				where([
					'merchantlink.initiator_user_id' => $dealer_merchant->owner_user_id,
					'merchantlink.responder_user_id' => $supplier_merchant->owner_user_id,
					'merchantlinkrelation.company_id'=> $dealer_merchant->id,
					'merchantlinkrelation.ptype'	 => 'supplier'
				])->
				orWhere([
					'merchantlink.initiator_user_id' => $supplier_merchant->owner_user_id,
					'merchantlink.responder_user_id' => $dealer_merchant->owner_user_id,
					'merchantlinkrelation.company_id'=> $dealer_merchant->id,
					'merchantlinkrelation.ptype'	 => 'supplier'
				])->
				first();

			$systemid_deliveryorder = new SystemID('deliveryorder');

			$deliveryorder_id = DB::table('deliveryorder')->insertGetId([
				"systemid"				=>	$systemid_deliveryorder,
				"issuer_merchant_id"	=>	$dealer_merchant->id,
				"issuer_location_id"	=>	$merchantlinkrelation_dealer->default_location_id ?? 0,
				"receiver_merchant_id"	=>	$supplier_merchant->id,
				"receiver_location_id"	=>	$merchantlinkrelation_sup->default_location_id ?? 0,
				"created_at"			=>	date("Y-m-d H:i:s"),	
				"updated_at"			=>	date("Y-m-d H:i:s")
			]);

			$purchaseorderdeliveryorder_id = DB::table('purchaseorderdeliveryorder')->
				insertGetId([
					"purchaseorder_id"	=>	$purchaseOrder->id,
					"deliveryorder_id"	=>	$deliveryorder_id,
					"created_at"		=>	date("Y-m-d H:i:s"),	
					"updated_at"		=>	date("Y-m-d H:i:s")
				]);

			//Inserting invoice records
			$systemid_invoice = new SystemID('invoice');
			$invoice_id = DB::table('invoice')->insertGetId([
				'systemid'				=>	$systemid_invoice,
				'dealer_merchant_id'	=>	$dealer_merchant->id,
				'supplier_merchant_id'	=>	$supplier_merchant->id,
				'deliveryorder_id'		=>	$deliveryorder_id,
				'staff_user_id'			=>	Auth::User()->id,
				"created_at"			=>	date("Y-m-d H:i:s"),	
				"updated_at"			=>	date("Y-m-d H:i:s")
			]);

			
			$DO_products 		= collect();
			$invoice_products 	= collect();

			$purchaseOrderItem->map(function($m) use ($deliveryorder_id, $invoice_id, $DO_products, $invoice_products) {

				//invoice insertion data
				$invoice_array = collect($m)->toArray();
				$invoice_array["created_at"]	=	date("Y-m-d H:i:s");
				$invoice_array["updated_at"]	=	date("Y-m-d H:i:s");
				$invoice_array['invoice_id'] 	= 	$invoice_id;
				$invoice_array['price']			= 	$invoice_array['purchase_price'];
				unset($invoice_array['purchaseorder_id']);
				unset($invoice_array['id']);
				unset($invoice_array['purchase_price']);
				$invoice_products->push($invoice_array);

				//do insertion data
				$do_array = collect($m)->toArray();
				$do_array["created_at"]			=	date("Y-m-d H:i:s");
				$do_array["updated_at"]			=	date("Y-m-d H:i:s");
				$do_array['deliveryorder_id']	=	$deliveryorder_id;
				$do_array['checker']			=	0;
				$do_array['status']				=	'pending';
				$do_array['remark']				=	'';
				$do_array['price']				= 	$do_array['purchase_price'];
				unset($do_array['purchaseorder_id']);
				unset($do_array['purchase_price']);
				unset($do_array['id']);
				$DO_products->push($do_array);
			});

			$Totalprice = $invoice_products->reduce(function($a, $b){
				return ($b['price'] * $b['quantity']) + $a;
			});

			$merchant_credit_limit = MerchantCreditLimit::where([
                'dealer_merchant_id'=> $dealer_merchant->id,
                'supplier_merchant_id'=> $supplier_merchant->id
			])->increment('avail_credit_limit',  $Totalprice);


			DB::table('deliveryorderproduct')->insert($DO_products->toArray());
			DB::table('invoiceproduct')->insert($invoice_products->toArray());	
			$this->mgLink('do', 'deliveryorder_id', $deliveryorder_id, $supplier_merchant, $dealer_merchant, $merchantglobal);
			$this->mgLink('inv', 'invoice_id', $invoice_id, $supplier_merchant, $dealer_merchant, $merchantglobal);

			return redirect()->back();

		} catch (\Exception $e) {
			\Log::error([                      
				 "Error" => $e->getMessage(),   
				 "File"  => $e->getFile(),      
				 "Line"  => $e->getLine()       
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

			$mgLink[$fk]	= $fk_value;
			$footer_slug = $slug."_footer";
			$mgLink[$slug."_footer"]	= $merchantglobal->so_footer ?? '';
			DB::table("mglink_".$slug)->insert($mgLink);
	}


}
