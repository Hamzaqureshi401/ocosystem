<?php

namespace App\Http\Controllers;

use App\Classes\SystemID;
use App\Classes\UserData;
use App\Models\Company;
use App\User;
use App\Models\InvoiceProduct;
use App\Models\Merchant;
use App\Models\MerchantCreditLimit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\usersrole;
use \App\Models\Invoice;
use \App\Models\product;


use Milon\Barcode\DNS1D;
use Milon\Barcode\DNS2D;
use PDF;
class InvoiceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function getCompanyUserId()
    {
        $userData = new UserData();
        $companyId = $userData->company_id();
        $company = Company::find($companyId);
        return $company->owner_user_id;
    }
    
    function showIssuedInvoiceView($invoice_id){
        $userData = new UserData();
        $invoice_id = (int) $invoice_id;
        $invoice = Invoice::where(['id'=> $invoice_id])->first();
	   
		$dealer_merchant_id = $invoice->dealer_merchant_id;
        $supplier_merchant_id = $invoice->supplier_merchant_id;
	
		$staff_user_id = $invoice->staff_user_id;
		$userDetails  = User::find($staff_user_id);
        $systemid = $invoice->systemid;

        // get credit limits
        $merchant_credit_limit = MerchantCreditLimit::where(
            [
                'dealer_merchant_id'=> $dealer_merchant_id,
                'supplier_merchant_id'=> $supplier_merchant_id
			])->first();

		//dd($merchant_credit_limit);
        // get invoice products
        $invoice_products = InvoiceProduct::where(['invoice_id'=> $invoice_id])->get();
		
		// ->join('product', 'invoiceproduct.product_id', '=', 'product.id')
        //  ->leftjoin('prd_warranty', 'invoiceproduct.product_id', '=', 'prd_warranty.product_id')

        // get total price of the invoice
        $total_price = 0;
        foreach ($invoice_products as $key => $product) {
            $product_qty_price = $product->quantity * $product->price;
            $total_price = $total_price + $product_qty_price;
        }
        $total_price_format = $total_price / 100;

		//mgLink
		$code = DNS1D::getBarcodePNG(trim($invoice->systemid), "C128");
		$qr = DNS2D::getBarcodePNG($invoice->systemid, "QRCODE");
	
		$mgLink = DB::table('mglink_inv')->
			where('invoice_id', $invoice->id)->
			first();
		
		$currency_code = DB::table('currency')->
			find($mgLink->currency_id)->
			code ?? 'MYR';
	

		$paymentDetails = DB::table('arpayment')->
			join('arpaymentinv','arpaymentinv.arpayment_id','=','arpayment.id')->
			leftjoin('bank','bank.id','=','arpayment.bank_id')->
			where('arpaymentinv.invoice_id',$invoice->id)->
			select("arpayment.*", "bank.company_name as bank_name")->
			whereNull('arpayment.deleted_at')->
			get();

		$totalPayment = $paymentDetails->reduce(function($a, $record) {
			return $a + $record->amount;
		});
		
		$deliveryorder_details = DB::table('deliveryorder')->find($invoice->deliveryorder_id);
		$salesorder_details = !empty($deliveryorder_details) ? DB::table('salesorderdeliveryorder')->
			join('salesorder','salesorder.id','salesorderdeliveryorder.salesorder_id')->
			where('salesorderdeliveryorder.deliveryorder_id',$invoice->deliveryorder_id)->
			select('salesorder.*')->
			first():null;

		$is_issuer_side = $invoice->supplier_merchant_id == $userData->company_id();

		$invoice_void	= DB::table('users')->join('staff','staff.user_id','users.id')->
			where('users.id', $invoice->void_user_id)->first();

        // will deal wholesale seperately. As have to handle ranges there.
        return view('invoice.invoice', compact(
            'invoice',
            'merchant_credit_limit',
            'invoice_products',
            'total_price_format','salesorder_details',
			'is_issuer_side','deliveryorder_details',
			'code','qr','mgLink', 'userDetails', 'invoice_void',
			'currency_code','paymentDetails', 'totalPayment'
        ));
    }

    function pdfInvoiceDownload($invoice_id){
        $userData = new UserData();
        $invoice_id = (int) $invoice_id;
        $invoice = Invoice::where(['id'=> $invoice_id])->first();

        $dealer_merchant_id = $invoice->dealer_merchant_id;
        $supplier_merchant_id = $invoice->supplier_merchant_id;

        $staff_user_id = $invoice->staff_user_id;
        $userDetails  = User::find($staff_user_id);
        $systemid = $invoice->systemid;

        // get credit limits
        $merchant_credit_limit = MerchantCreditLimit::where(
            [
                'dealer_merchant_id'=> $dealer_merchant_id,
                'supplier_merchant_id'=> $supplier_merchant_id
            ])->first();

        //dd($merchant_credit_limit);
        // get invoice products
        $invoice_products = InvoiceProduct::where(['invoice_id'=> $invoice_id])->get();

        // ->join('product', 'invoiceproduct.product_id', '=', 'product.id')
        //  ->leftjoin('prd_warranty', 'invoiceproduct.product_id', '=', 'prd_warranty.product_id')

        // get total price of the invoice
        $total_price = 0;
        foreach ($invoice_products as $key => $product) {
            $product_qty_price = $product->quantity * $product->price;
            $total_price = $total_price + $product_qty_price;
        }
        $total_price_format = $total_price / 100;

        //mgLink
        $code = DNS1D::getBarcodePNG(trim($invoice->systemid), "C128");
        $qr = DNS2D::getBarcodePNG($invoice->systemid, "QRCODE");

        $mgLink = DB::table('mglink_inv')->
        where('invoice_id', $invoice->id)->
        first();

        $currency_code = DB::table('currency')->
            find($mgLink->currency_id)->
            code ?? 'MYR';


        $paymentDetails = DB::table('arpayment')->
        join('arpaymentinv','arpaymentinv.arpayment_id','=','arpayment.id')->
        leftjoin('bank','bank.id','=','arpayment.bank_id')->
        where('arpaymentinv.invoice_id',$invoice->id)->
        select("arpayment.*", "bank.company_name as bank_name")->
        whereNull('arpayment.deleted_at')->
        get();

        $totalPayment = $paymentDetails->reduce(function($a, $record) {
            return $a + $record->amount;
        });

        $deliveryorder_details = DB::table('deliveryorder')->find($invoice->deliveryorder_id);
        $salesorder_details = !empty($deliveryorder_details) ? DB::table('salesorderdeliveryorder')->
        join('salesorder','salesorder.id','salesorderdeliveryorder.salesorder_id')->
        where('salesorderdeliveryorder.deliveryorder_id',$invoice->deliveryorder_id)->
        select('salesorder.*')->
        first():null;

        $is_issuer_side = $invoice->supplier_merchant_id == $userData->company_id();

        $invoice_void	= DB::table('users')->join('staff','staff.user_id','users.id')->
        where('users.id', $invoice->void_user_id)->first();

        // will deal wholesale seperately. As have to handle ranges there.

        $pdf = PDF::setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true])->loadView('invoice.invoice_pdf', compact(
            'invoice',
            'merchant_credit_limit',
            'invoice_products',
            'total_price_format','salesorder_details',
            'is_issuer_side','deliveryorder_details',
            'code','qr','mgLink', 'userDetails', 'invoice_void',
            'currency_code','paymentDetails', 'totalPayment'
        ));

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
        return $pdf->download('invoice.pdf');
    }

    function showReceivedInvoiceView(){
     return view('invoice.invoice');
    }
    function showInvoiceIssuedListView()
    {
		$user_data = new UserData();
		$invoice = DB::table('invoice')->
			join('invoiceproduct','invoiceproduct.invoice_id','=','invoice.id')->
			leftjoin('company', 'company.id','=','invoice.dealer_merchant_id')->
			where('invoice.supplier_merchant_id', $user_data->company_id())->
			select('invoice.*', 'company.name as company_name',
				DB::RAW("SUM(invoiceproduct.price * invoiceproduct.quantity) as price"))->
			groupBy('invoice.id')->
			orderBy('invoice.created_at', 'desc')->
			get();
    	return view('invoice.invoice_issued_list',compact('invoice'));
    }

    function showInvoiceReceivedListView()
    {
   		$user_data = new UserData();
		$invoice = DB::table('invoice')->
			join('invoiceproduct','invoiceproduct.invoice_id','=','invoice.id')->
			leftjoin('company', 'company.id','=','invoice.supplier_merchant_id')->
			where('invoice.dealer_merchant_id', $user_data->company_id())->
			select('invoice.*', 'company.name as company_name',
				DB::RAW("SUM(invoiceproduct.price * invoiceproduct.quantity) as price"))->
			groupBy('invoice.id')->
			orderBy('invoice.created_at', 'desc')->
			get();

    	return view('invoice.invoice_received_list',compact('invoice'));
    }

    public function createInvoice(Request $request) {
		
		$invoice = new Invoice();
        $a = new SystemID('invoice');
        $system_id = $a->__toString();

		$user_data = new UserData();
		$user_id = Auth::user()->id;

        $owner_user_id = $this->getCompanyUserId();
		
		$merchant = Merchant::select('merchant.id as id')->
			join('company', 'company.id', '=', 'merchant.company_id')->
			where('company.owner_user_id', $owner_user_id)->
			first();

        $supplier_merchant_id = $user_data->company_id();
        $dealer_merchant_id = (int) $request['merchantId'];

        // check available credit limit: merchant credit limit
        $mc_limit = MerchantCreditLimit::where('dealer_merchant_id',
		   	$dealer_merchant_id)->
			where('supplier_merchant_id', $supplier_merchant_id)->
			first();
		
		// total demanding credit
        $total_credit_demanding = (float) $request['totalCredit'] ?? 0;
        $credit_limit = $mc_limit['credit_limit'] ?? 0;
        $avail_credit_limit = $mc_limit['avail_credit_limit'] ?? 0;

		$allowed_credit_limit = $credit_limit - $avail_credit_limit;

        if ($allowed_credit_limit < $total_credit_demanding) {
			
			return response()->json([
				'msg' => 'Credit limit is insufficient',
				'status' => 'false',
				'allowed_credit_limit' => $allowed_credit_limit
			]);

        } else {
			
			// Update avail limit
            $now_total_availed = $avail_credit_limit + $total_credit_demanding;
            $now_total_availed_to_int = $now_total_availed;

			if (!empty($mc_limit)) {
				MerchantCreditLimit::where('id', $mc_limit['id'])->
					update([
						'avail_credit_limit' => $now_total_availed_to_int
					]);
			}
		}
		/*	
		// Populating Deliver Order
		$deliveryOrderSystemid = new SystemID('deliveryorder');

		$deliveryorder_id = DB::table('deliveryorder')->insertGetId([
			"systemid"				=>	$deliveryOrderSystemid,
			"purchaseorder_id"		=>	0,
			"deliveryman_user_id"	=>	0,
			"issuer_merchant_id"	=>	$supplier_merchant_id,
			"issuer_location_id"	=>	0,
			"receiver_merchant_id"	=>	$dealer_merchant_id,
			"receiver_location_id"	=>	0,
			"created_at"			=>	date("d-m-Y H:i:s"),
			"updated_at"			=>	date("d-m-Y H:i:s")
		]);*/

        // save invoice and get invoice id.
        $invoice->systemid = $system_id;
        $invoice->supplier_merchant_id = $supplier_merchant_id;
        $invoice->dealer_merchant_id = $dealer_merchant_id;
        $invoice->deliveryorder_id = 0;
        $invoice->staff_user_id = $user_id;
        $invoice->save();
        // saved invoice id
        $invoice_id = $invoice->id;

        // save invoice products and Qty
        $products = $request['products'];
        foreach ($products as $product) {

			$product_thumb = product::find($product['ProductID'])->thumbnail_1 ?? '';

            $invoiceProduct = new InvoiceProduct();
            $invoiceProduct->invoice_id = $invoice_id;
            $invoiceProduct->product_id = (int) $product['ProductID'];
			$invoiceProduct->quantity = (int) $product['Qty'];
			$invoiceProduct->product_name = $product['ProductName'];
			$invoiceProduct->product_systemid = $product['ProductSysID'];
			$invoiceProduct->product_thumbnail = $product_thumb;
			$invoiceProduct->price = (( (int) str_replace(',','', $product['Price'])) * 100);
            $invoiceProduct->save();
        }

		//MGLINK
		$merchantglobal = DB::table('merchantglobal')->
			where('merchant_id', $supplier_merchant_id)->
			first();

		$supplier_merchant = DB::table('company')->find($supplier_merchant_id);
		$dealer_merchant = DB::table('company')->find($dealer_merchant_id);

		$mgLink = [];
		$mgLink['invoice_id']				= $invoice_id;
		
		$mgLink['inv_footer']				= $merchantglobal->inv_footer ?? '';
		$mgLink['currency_id']				= $supplier_merchant->currency_id ?? 0;

		$mgLink['supplier_company_name']	= $supplier_merchant->name;
		$mgLink['supplier_business_reg_no'] = $supplier_merchant->business_reg_no ?? '';
		$mgLink['supplier_address'] 		= $supplier_merchant->office_address ?? '';

		$mgLink['dealer_company_name']		= $dealer_merchant->name;
		$mgLink['dealer_business_reg_no'] 	= $dealer_merchant->business_reg_no ?? '';
		$mgLink['dealer_address'] 			= $dealer_merchant->office_address ?? '';

		$mgLink['created_at']				= date("Y-m-d H:i:s");
		$mgLink['updated_at']				= date("Y-m-d H:i:s");

		if (!empty($merchantglobal->inv_has_logo)) {
			if ($merchantglobal->inv_has_logo == 1 ) {
				$mgLink['inv_headerlogo']	= $supplier_merchant->corporate_logo;
			}
		}

		DB::table('mglink_inv')->insert($mgLink);
		
		/*
		unset($mgLink['invoice_id']);
		unset($mgLink['inv_footer']);
		unset($mgLink['inv_headerlogo']);
		$mgLink['deliveryorder_id'] =	$deliveryorder_id; 
		$mgLink['do_footer'] = $merchantglobal->do_footer ?? '';
		if (!empty($merchantglobal->do_has_logo)) {
			if ($merchantglobal->do_has_logo == 1 ) {
				$mgLink['do_headerlogo']	= $supplier_merchant->corporate_logo;
			}
		}
		DB::table('mglink_do')->insert($mgLink);
		*/

        return response()
            ->json([
                'msg' => 'saved successfully',
                'status' => 'true',
                'invoice_id' => $invoice_id
            ]);
    }
}
