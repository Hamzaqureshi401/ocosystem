<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\usersrole;
use App\Models\Company;
use App\Models\Merchant;

use App\Models\Staff;

use App\Models\Creditnote;
use App\Models\Creditnoteitem;
use App\Models\product;
use App\Models\Currency;
use App\Models\MerchantCreditLimit;

use App\User;
use Log;
use DB;
use App\Classes\SystemID;
use App\Classes\UserData;
use Yajra\DataTables\DataTables;
use Milon\Barcode\DNS1D;
use Milon\Barcode\DNS2D;

use Carbon\Carbon;
use PDF;
class CreditNoteController extends Controller
{
    public function saveCreditNote(Request $request)
    {
		$userData_global = new UserData();
		$user_company_data = Company:: where('id', $userData_global->company_id())->first();

        $currency=  \App\Models\Currency::where('id',
			$user_company_data->currency_id)->first();

        $company_id = Merchant::where('company_id', $request->merchant_id)->
			value('company_id');

        $company_data = Company:: where('id', $company_id)->first();    
        $tableData =$request->tableData;
        $Totalprice=0;

        $system_id = new SystemID('creditnote');
        $systemid= $system_id-> __toString();
     
		$creditnote =new Creditnote();
		$creditnote->systemid = $systemid;// $value['ProductID'];
		$creditnote->creator_user_id =  $user_company_data->owner_user_id;
		$creditnote->dealer_user_id =   $company_data->owner_user_id;
		$creditnote->status =  'active';
        $creditnote->save();

        $code = DNS1D::getBarcodePNG(trim($systemid), "C128");
        $qr = DNS2D::getBarcodePNG($systemid, "QRCODE");

        foreach ($tableData as $product) {

			$product_thumb = product::find($product['ProductID'])->
				thumbnail_1 ?? '';

			$creditnoteitem =new Creditnoteitem();
            $creditnoteitem->creditnote_id	 = $creditnote->id;
            $creditnoteitem->product_id = (int) $product['ProductID'];
			$creditnoteitem->quantity = (int) $product['Qty'];
			$creditnoteitem->product_name = $product['ProductName'];
			$creditnoteitem->product_systemid = $product['ProductSysID'];
			$creditnoteitem->product_thumbnail = $product_thumb;
			$creditnoteitem->price = (((int) str_replace(',','', ($product['Price']))) * 100);
            $creditnoteitem->save();
            $Totalprice=$Totalprice + ( (int) str_replace(',','', $product['TQty']) * 100 );
        }

		//MGLINK
		$merchantglobal = DB::table('merchantglobal')->
			where('merchant_id', $userData_global->company_id())->
			first();

		$supplier_merchant = DB::table('company')->
			find($userData_global->company_id());

		$dealer_merchant = DB::table('company')->
			find($company_id);

		$merchant_credit_limit = MerchantCreditLimit::where([
			'dealer_merchant_id'=> $dealer_merchant->id,
			'supplier_merchant_id'=> $supplier_merchant->id
		])->decrement('avail_credit_limit',  $Totalprice);

		$mgLink = [];
		$mgLink['creditnote_id']			= $creditnote->id;
		
		$mgLink['cn_footer']				= $merchantglobal->cn_footer ?? '';
		$mgLink['currency_id']				= $supplier_merchant->currency_id ?? 0;

		$mgLink['supplier_company_name']	= $supplier_merchant->name;
		$mgLink['supplier_business_reg_no'] = $supplier_merchant->business_reg_no ?? '';
		$mgLink['supplier_address'] 		= $supplier_merchant->office_address ?? '';

		$mgLink['dealer_company_name']		= $dealer_merchant->name;
		$mgLink['dealer_business_reg_no'] 	= $dealer_merchant->business_reg_no ?? '';
		$mgLink['dealer_address'] 			= $dealer_merchant->office_address ?? '';

		$mgLink['created_at']				= date("Y-m-d H:i:s");
		$mgLink['updated_at']				= date("Y-m-d H:i:s");

		if (!empty($merchantglobal->cn_has_logo)) {
			if ($merchantglobal->cn_has_logo == 1 ) {
				$mgLink['cn_headerlogo']	= $supplier_merchant->corporate_logo;
			}
		}

		DB::table('mglink_cn')->insert($mgLink);
  
		$gen_date = date('dMy h:m:s');
		$response = [
			'dealer_data' => $company_data,
			'user_company' => $user_company_data,
			'tableData'=> $tableData,
			'genDate'=> $gen_date,
			'currency'=>$currency,
			'Totalprice'=> number_format((float)$Totalprice, 2, '.', ''),
			'barcode'=> $code,
			'qr'=> $qr,
			'systemid'=> $systemid,
			'creditNoteId'	=> $creditnote->id
		];
		return response()->json($response);
    }


    public function showCreditNoteView($creditnoteid)
    {
        $creditNotes = Creditnote::where('id',$creditnoteid)->first();
		
		$user_data= Staff::where('user_id', $creditNotes->creator_user_id)
               ->join('users', 'users.id', '=','staff.user_id')->first();
        $staff= Staff::where('user_id', $creditNotes->creator_user_id)->first();
        $is_king =  Company::where('owner_user_id',$creditNotes->creator_user_id)->first();

        $systemid =  $creditNotes->systemid;
        $code = DNS1D::getBarcodePNG(trim($systemid ), "C128");
        $qr = DNS2D::getBarcodePNG($systemid, "QRCODE");
        $product_detail = Creditnoteitem::where('creditnote_id',$creditnoteid)->get(); 
	 	$Totalprice = $product_detail->
			reduce(function($a, $b){
				return ($b->price * $b->quantity) + $a;
			});

		$mgLink = DB::table('mglink_cn')->
			where('creditnote_id', $creditnoteid)->
			first();

		$currency_code = DB::table('currency')->
			find($mgLink->currency_id)->
			code ?? 'MYR';
	
        $genDate = date('dMy h:m:s');
		$currency = '';
		$dealer_company = '';
		$creditnotes_void = DB::table('users')->join('staff','staff.user_id','users.id')->
			select('users.*', 'staff.systemid')->where('users.id',$creditNotes->void_user_id)->first();
        return view("creditnote.creditnote", compact(
			'is_king',
			'currency',
			'creditnoteid',
			'user_data',
			'dealer_company',
			'genDate',
			'staff',
			'Totalprice',
			'code',
			'creditNotes',
			'mgLink',
			'currency_code','creditnotes_void',
			'qr','product_detail','systemid'
		));
    }


  public function CreditNoteIssuedList(Request $request)
    {
        //creditnote Document ID	Date	Company Name	Amount(MYR)

		$user_data = new UserData();
        $id =  \App\Models\Company::find($user_data->company_id())->owner_user_id;
       
        $month= $request->month;
        $start_yr= $request->year;
         
        $type = $request->type;
        if($type=='Issue'){
            $creditNotes = Creditnote::where('creator_user_id', $id)->
                 whereMonth('created_at',$month)
                ->whereYear('created_at', date($start_yr))
				->orderBy('created_at', 'desc')
                ->get();
        
        }else{
            $creditNotes = Creditnote::where('dealer_user_id', $id)
                 ->whereMonth('created_at',$month)
                ->whereYear('created_at', date($start_yr))
				->orderBy('created_at', 'desc')
                ->get();
        }
            
        $creditdata = array();
        $index = 0;
        foreach ($creditNotes as $key => $value) {
            $documentid=$value->systemid;
            $creditnote_id=$value->id;
			$is_void = $value->is_void;

            if($type=='Issue'){
                    $dealuser_id =  $value ->dealer_user_id;
            }else{
                $dealuser_id =  $value ->creator_user_id;
            }
            
            $dealerCompany =  \App\Models\Company::where('owner_user_id', $dealuser_id)->first();
            $creditNotesitem = Creditnoteitem::where('creditnote_id',$creditnote_id)->
                    whereMonth('created_at',$month)
                    ->whereYear('created_at', date($start_yr))
                    ->get(); 
            $sumAmount =0;
            foreach ($creditNotesitem as $key => $value) {
                $sumAmount= $sumAmount+($value->price * $value->quantity);
            } 
            $creditdata[$index] = $value;
            $creditdata[$index] ->note_id =$creditnote_id;
            $creditdata[$index]->documentid = $documentid;
            $creditdata[$index]->company = $dealerCompany->name ;
            $creditdata[$index]->amount = $sumAmount;
            $creditdata[$index]->is_void = $is_void;
            $index++;
        }

		return Datatables::of($creditdata)->
            addIndexColumn()->
            addColumn('inven_pro_id', function ($memberList) {
                return '<a href="javascript:openNewTabURL(\'/creditnote/'.$memberList->note_id.
					'\')"  style="cursor: pointer;text-decoration:none" >'.
					$memberList->documentid.'</a>' ;
            })->
            addColumn('inven_pro_date', function ($memberList) {
                return date('dMy H:i:s',strtotime($memberList->created_at));
            })->
            addColumn('inven_pro_branch', function ($memberList) {
                return $memberList->company;
            })->
            addColumn('amount', function ($memberList) {
                return  number_format((float)$memberList->amount /100 , 2, '.', ''); 
            })->
			setRowClass(function ($memberList) {
				if ($memberList->is_void == 1) {
					return 'void_doc';
				}
			})-> 
            escapeColumns([])->
            make(true);
    	//return view('creditnote.creditnote_issued_list',compact('user_roles','is_king','creditnote_id'));
    }


    public function showCreditNoteIssuedListView(Request $request)
    {
        //creditnote Document ID	Date	Company Name	Amount(MYR)
    	$id = Auth::user()->id;
        $user_roles = usersrole::where('user_id',$id)->get();
        $is_king =  \App\Models\Company::where('owner_user_id',Auth::user()->id)->first();
       
        if ($is_king != null) {
            $is_king = true;
        } else {
            $is_king  = false;
        }
    	return view('creditnote.creditnote_issued_list',compact('user_roles','is_king','creditnote_id'));
    }


    
    public function showCreditNoteReceivedListView()
    {
        $id = Auth::user()->id;
        $user_roles = usersrole::where('user_id',$id)->get();
        $creditnote_id=12;

        $is_king =  \App\Models\Company::where('owner_user_id',Auth::user()->id)->first();

        if ($is_king != null) {
            $is_king = true;
        } else {
            $is_king  = false;
        }
        return view('creditnote.creditnote_received_list',compact('user_roles','is_king','creditnote_id'));
    }

    public function pdfCreditNoteView($creditnoteid)
    {
        $creditNotes = Creditnote::where('id',$creditnoteid)->first();

        $user_data= Staff::where('user_id', $creditNotes->creator_user_id)
            ->join('users', 'users.id', '=','staff.user_id')->first();
        $staff= Staff::where('user_id', $creditNotes->creator_user_id)->first();
        $is_king =  Company::where('owner_user_id',$creditNotes->creator_user_id)->first();

        $systemid =  $creditNotes->systemid;
        $code = DNS1D::getBarcodePNG(trim($systemid ), "C128");
        $qr = DNS2D::getBarcodePNG($systemid, "QRCODE");
        $product_detail = Creditnoteitem::where('creditnote_id',$creditnoteid)->get();
        $Totalprice = $product_detail->
        reduce(function($a, $b){
            return ($b->price * $b->quantity) + $a;
        });

        $mgLink = DB::table('mglink_cn')->
        where('creditnote_id', $creditnoteid)->
        first();

        $currency_code = DB::table('currency')->
            find($mgLink->currency_id)->
            code ?? 'MYR';

        $genDate = date('dMy h:m:s');
        $currency = '';
        $dealer_company = '';
        $creditnotes_void = DB::table('users')->join('staff','staff.user_id','users.id')->
        select('users.*', 'staff.systemid')->where('users.id',$creditNotes->void_user_id)->first();

        $pdf = PDF::setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true])
            ->loadView("creditnote.creditnote_pdf", compact(
                'is_king',
                'currency',
                'creditnoteid',
                'user_data',
                'dealer_company',
                'genDate',
                'staff',
                'Totalprice',
                'code',
                'creditNotes',
                'mgLink',
                'currency_code','creditnotes_void',
                'qr','product_detail','systemid'
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

        return $pdf->download('creditnote.pdf');

    }
}
