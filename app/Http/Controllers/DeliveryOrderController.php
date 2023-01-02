<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Auth;
use App\Models\usersrole;
use App\Models\PurchaseOrder;
use App\Models\DeliveryOrder;
use DB;
use Milon\Barcode\DNS1D;
use Milon\Barcode\DNS2D;
use \App\Classes\SystemID;
use \App\Classes\UserData;
use PDF;
class DeliveryOrderController extends Controller
{
	


	public function index()
	{
		return DataTables::of(array('',''))
			->addIndexColumn()
			->addColumn('do_no',function($staffList){
			return '<p data-field="do_no" style="margin:0;"> 1</p>';
			})
			->addColumn('do_id',function($staffList){
			return '<a data-field="do_id" href="/deliveryorder" target="_blank" class="text-center">19062821030100000001</a>';
			})
			->addColumn('do_source',function(){
			return '<p data-field="do_source" style="margin:0;" class="text-center">GATOR</p>';
			})
			->addColumn('do_date',function(){
			return '<p data-field="do_date" style="margin:0;" class="text-center">28Jun19 21:13:40</p>';
			})
			->addColumn('do_status',function(){
			return '<p data-field="do_status" style="margin:0;" class="text-center">Completed</p>';
			})
			->escapeColumns([])
			->make(true);

	}


	public function showIssuedlist()
	{

		$user_data  = new UserData();
		$invoiceIssued = DB::table('deliveryorder')->where(
				'deliveryorder.issuer_merchant_id',$user_data->company_id())->
				join('company','company.id','deliveryorder.receiver_merchant_id')->
				orderBy('deliveryorder.created_at', 'desc')->
				select('deliveryorder.*','company.name')->
				get();

		$invoiceIssued = $invoiceIssued->filter(function($f) {
			return !empty(DB::table('deliveryorderproduct')->
				where('deliveryorder_id', $f->id)->first());
		});

		return view('deliveryorder.deliveryorder_issued_list', compact('invoiceIssued'));
	}
	public function showReceivedlist()
	{
		$user_data  = new UserData();
		$invoiceRev = DB::table('deliveryorder')->where(
				'deliveryorder.receiver_merchant_id'  ,$user_data->company_id())->
				join('company','company.id','deliveryorder.issuer_merchant_id')->
				orderBy('deliveryorder.created_at', 'desc')->
				select('deliveryorder.*','company.name')->
				get();
		
		$invoiceRev = $invoiceRev->filter(function($f) {
			return !empty(DB::table('deliveryorderproduct')->
				where('deliveryorder_id', $f->id)->first());
		});

		return view('deliveryorder.deliveryorder_received_list', compact('invoiceRev'));
	}

    // method for delievery order template
    public function doindex()
    {
    	return view('deliveryorder.deliveryorder');
    }
	
    // method for delievery order template
    public function doComplete(Request $request)
	{
		
		$checkerData = $request->checkerData;
		foreach($checkerData as $cData) {
			DB::table('deliveryorderproduct')->
				where([
					"deliveryorder_id"	=> $request->do_id,
					"product_id"		=> $cData['product_id']
				])->
				update([
					"checker"	=>	$cData['val']
				]);
		}

		DB::table('deliveryorder')->
			where('id',$request->do_id)->
			update([
				'status'=>'completed', 
				"completed_by_user_id"=>Auth::User()->id,
				'completed_at'=> date("Y-m-d H:i:s")
			]);

		return response()->json(['msg' => 'OK']);
	}

    public function doView($docType , $deliveryorder_id)
    {
		$user_data  = new UserData();

		$DO_record = DB::table('deliveryorder')->
			where('systemid', $deliveryorder_id)->
			first();

		$product_detail = DB::table('deliveryorderproduct')->
			where('deliveryorder_id', $DO_record->id)->
			get();
	
		$user = DB::table('users')->
			join('staff','staff.user_id','=','users.id')->
			where('users.id', Auth::User()->id )->
			select('users.*','staff.systemid')->
			first();
	
		$Totalprice = $product_detail->reduce(function($a, $record) {
			return $a + $record->quantity * $record->price;
		});

		$date = date("dMy", strtotime($DO_record->created_at));

		$delivery_to_location = DB::table('location')->
			where('id', $DO_record->receiver_location_id)->
			first();
			
		switch($docType) {
			case 'invoice':
				$invoice = DB::table('invoice')->
					where('deliveryorder_id', $DO_record->id)->
					first();

				$product_detail = DB::table('invoiceproduct')->
					where('invoice_id', $invoice->id)->
					get();
				
				$user = DB::table('users')->
					join('staff','staff.user_id','=','users.id')->
					where('users.id', $invoice->staff_user_id)->
					select('users.*','staff.systemid')->
					first();
	
				$Totalprice = $product_detail->reduce(function($a, $record) {
					return $a + $record->quantity * $record->price;
				});

				$date = date("dMy", strtotime($invoice->created_at));
				break;
			case 'salesorder':
				break;
		}

		$mgLink = DB::table('mglink_do')->
			where('deliveryorder_id', $DO_record->id)->
			first();

		$code = DNS1D::getBarcodePNG(trim($DO_record->systemid), "C128");
		$qr = DNS2D::getBarcodePNG($DO_record->systemid, "QRCODE");

		$currency_code = DB::table('currency')->
			find($mgLink->currency_id)->
			code ?? 'MYR';

		$invoice_details = DB::table('invoice')->
			where('deliveryorder_id', $DO_record->id)->first();
		
		$complete_user_details = DB::table('users')->
			join('staff','staff.user_id','users.id')->
			where('users.id', $DO_record->completed_by_user_id)->
			select('users.name', 'staff.systemid')->
			first();

		$is_own = $DO_record->issuer_merchant_id == $user_data->company_id();
		return view('deliveryorder.deliveryorder',compact('product_detail',
			'DO_record','user', 'mgLink','currency_code','delivery_to_location',
			'invoice_details','complete_user_details', 'is_own',
			'code','qr', 'date','Totalprice'));
    }

    public function downloadPdf($docType , $deliveryorder_id)
    {
        $user_data  = new UserData();

        $DO_record = DB::table('deliveryorder')->
        where('systemid', $deliveryorder_id)->
        first();

        $product_detail = DB::table('deliveryorderproduct')->
        where('deliveryorder_id', $DO_record->id)->
        get();

        $user = DB::table('users')->
        join('staff','staff.user_id','=','users.id')->
        where('users.id', Auth::User()->id )->
        select('users.*','staff.systemid')->
        first();

        $Totalprice = $product_detail->reduce(function($a, $record) {
            return $a + $record->quantity * $record->price;
        });

        $date = date("dMy", strtotime($DO_record->created_at));

        $delivery_to_location = DB::table('location')->
        where('id', $DO_record->receiver_location_id)->
        first();

        switch($docType) {
            case 'invoice':
                $invoice = DB::table('invoice')->
                where('deliveryorder_id', $DO_record->id)->
                first();

                $product_detail = DB::table('invoiceproduct')->
                where('invoice_id', $invoice->id)->
                get();

                $user = DB::table('users')->
                join('staff','staff.user_id','=','users.id')->
                where('users.id', $invoice->staff_user_id)->
                select('users.*','staff.systemid')->
                first();

                $Totalprice = $product_detail->reduce(function($a, $record) {
                    return $a + $record->quantity * $record->price;
                });

                $date = date("dMy", strtotime($invoice->created_at));
                break;
            case 'salesorder':
                break;
        }

        $mgLink = DB::table('mglink_do')->
        where('deliveryorder_id', $DO_record->id)->
        first();

        $code = DNS1D::getBarcodePNG(trim($DO_record->systemid), "C128");
        $qr = DNS2D::getBarcodePNG($DO_record->systemid, "QRCODE");

        $currency_code = DB::table('currency')->
            find($mgLink->currency_id)->
            code ?? 'MYR';

        $invoice_details = DB::table('invoice')->
        where('deliveryorder_id', $DO_record->id)->first();

        $complete_user_details = DB::table('users')->
        join('staff','staff.user_id','users.id')->
        where('users.id', $DO_record->completed_by_user_id)->
        select('users.name', 'staff.systemid')->
        first();

        $is_own = $DO_record->issuer_merchant_id == $user_data->company_id();

        $pdf = PDF::setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true])->loadView('deliveryorder.deliveryorder_pdf',compact('product_detail',
            'DO_record','user', 'mgLink','currency_code','delivery_to_location',
            'invoice_details','complete_user_details', 'is_own',
            'code','qr', 'date','Totalprice'));

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
       // return $pdf->stream();
        return $pdf->download('deliveryorder.pdf');
    }
}
