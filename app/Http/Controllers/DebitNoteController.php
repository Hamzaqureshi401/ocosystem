<?php

namespace App\Http\Controllers;

use App\Classes\SystemID;
use App\Classes\UserData;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\usersrole;

use App\Models\Company;
use App\Models\Merchant;

use App\Models\Staff;
use App\Models\MerchantCreditLimit;
use Milon\Barcode\DNS1D;
use Milon\Barcode\DNS2D;
use App\Models\Debitnote;
use App\Models\Debitnoteitem;
use App\Models\product;
use DB;
use App\Models\Currency;
use Carbon\Carbon;
use Yajra\DataTables\DataTables;
use PDF;
class DebitNoteController extends Controller
{


    public function saveDebitNote(Request $request)
    {
          //	id	systemid	creator_user_id	dealer_user_id
        $company_id = Merchant::where('company_id', $request->merchant_id)->value('company_id');
        $company_data = Company:: where('id', $company_id)->first();
        $currency=  \App\Models\Currency::where('id',
                $company_data->currency_id)->first();
    
		$userData_global = new UserData();
		$user_company_data  = DB::table('company')->find($userData_global->company_id());
        $tableData =$request->tableData;
        $Totalprice=0;
      
        $debitnote =new Debitnote();
    
        $system_id = new SystemID('debitnote');
        $systemid= $system_id-> __toString();
        $debitnote->systemid = $systemid;// $value['ProductID'];
        $debitnote->creator_user_id =  $user_company_data->owner_user_id;
        $debitnote->dealer_user_id =   $company_data->owner_user_id;
        $debitnote->status =  'active';
        $debitnote->save();
		
		$code = DNS1D::getBarcodePNG(trim($systemid), "C128");
		$qr = DNS2D::getBarcodePNG($systemid, "QRCODE");

        foreach ($tableData as $key => $product) {
	    
			$product_thumb = product::find($product['ProductID'])->thumbnail_1 ?? '';
            $debitnoteitem =new Debitnoteitem();
            $debitnoteitem->debitnote_id	 = $debitnote->id;
            $debitnoteitem->product_id = (int) $product['ProductID'];
			$debitnoteitem->quantity = (int) $product['Qty'];
			$debitnoteitem->product_name = $product['ProductName'];
			$debitnoteitem->product_systemid = $product['ProductSysID'];
			$debitnoteitem->product_thumbnail = $product_thumb;
			$debitnoteitem->price = (((int) str_replace(',','',$product['Price'])) * 100);
            $debitnoteitem->save();
            $Totalprice =	$Totalprice	+ ((int) str_replace(',','',$product['TQty']) * 100);

        }

			//MGLINK
		$merchantglobal = DB::table('merchantglobal')->
			where('merchant_id', $userData_global->company_id())->
			first();

		$supplier_merchant = DB::table('company')->find($userData_global->company_id());
		$dealer_merchant = DB::table('company')->find($company_id);
	
	
		$merchant_credit_limit = MerchantCreditLimit::where([
                'dealer_merchant_id'=> $dealer_merchant->id,
                'supplier_merchant_id'=> $supplier_merchant->id
		])->increment('avail_credit_limit',  $Totalprice);


		$mgLink = [];
		$mgLink['debitnote_id']				= $debitnote->id;
		
		$mgLink['dn_footer']				= $merchantglobal->dn_footer ?? '';
		$mgLink['currency_id']				= $supplier_merchant->currency_id ?? 0;

		$mgLink['supplier_company_name']	= $supplier_merchant->name;
		$mgLink['supplier_business_reg_no'] = $supplier_merchant->business_reg_no ?? '';
		$mgLink['supplier_address'] 		= $supplier_merchant->office_address;

		$mgLink['dealer_company_name']		= $dealer_merchant->name;
		$mgLink['dealer_business_reg_no'] 	= $dealer_merchant->business_reg_no ?? '';
		$mgLink['dealer_address'] 			= $dealer_merchant->office_address ?? '';

		$mgLink['created_at']				= date("Y-m-d H:i:s");
		$mgLink['updated_at']				= date("Y-m-d H:i:s");

		if (!empty($merchantglobal->dn_has_logo)) {
			if ($merchantglobal->dn_has_logo == 1 ) {
				$mgLink['dn_headerlogo']	= $supplier_merchant->corporate_logo;
			}
		}

		DB::table('mglink_dn')->insert($mgLink);

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
						"debitId"	=> $debitnote->id
                        ];
            return response()->json($response);
    }


    public function showDebitNoteView($debitnoteid)
    {
       $debitNotes = Debitnote::where('id',$debitnoteid)->first();
	   $dealer_company =  Company::where('owner_user_id', $debitNotes->dealer_user_id)->first();
   	   $staff= Staff::where('user_id', $debitNotes->creator_user_id)->first();
	   $user_data= Staff::where('user_id', $debitNotes->creator_user_id)->
		 join('users', 'users.id', '=','staff.user_id')->first();
  	   $is_king =  Company::where('owner_user_id',$debitNotes->creator_user_id)->first();
	   $systemid =  $debitNotes->systemid;
  	   $code = DNS1D::getBarcodePNG(trim($systemid ), "C128");
	   $qr = DNS2D::getBarcodePNG($systemid, "QRCODE");
		
		$product_detail = Debitnoteitem::where('debitnote_id',$debitnoteid)->get(); 
	 	$Totalprice = $product_detail->reduce(function($a, $b){return ($b->price * $b->quantity) + $a;});

		$mgLink = DB::table('mglink_dn')->
			where('debitnote_id', $debitnoteid)->
			first();
		
		$currency_code = DB::table('currency')->find($mgLink->currency_id)->code ?? 'MYR';
		$debitnote_void = DB::table('users')->join('staff','staff.user_id','users.id')->
			select('users.*','staff.systemid')->where('users.id',$debitNotes->void_user_id)->
			first();

        $genDate = date('dMy h:m:s');
	   
		$paymentDetails = DB::table('arpayment')->
			join('arpaymentdn','arpaymentdn.arpayment_id','=','arpayment.id')->
			leftjoin('bank','bank.id','=','arpayment.bank_id')->
			where('arpaymentdn.debitnote_id',$debitNotes->id)->
			select("arpayment.*", "bank.company_name as bank_name")->
			whereNull('arpayment.deleted_at')->
			get();
	   
		$totalPayment = $paymentDetails->reduce(function($a, $record) {
            return $a + $record->amount;
        });
      
        return view("debitnote.debitnote",compact('is_king','debitnoteid',
                        'user_data',
                        'dealer_company',
                        'genDate',
                        'staff',
                        'Totalprice',
                        'code',
						'debitNotes',
						'mgLink','currency_code','debitnote_void',
						'qr','product_detail', 'systemid',
						'paymentDetails','totalPayment'
					));
    }

 

    public function DebitNoteIssuedList(Request $request)
    {
   
		$user_data = new UserData();
	  	$company = DB::table('company')->find($user_data->company_id());
		$id = $company->owner_user_id;	
        $month= $request->month;
        $start_yr= $request->year;
       
        $type = $request->type;
        if($type=='Issue'){
            $debitNotes = Debitnote::where('creator_user_id', $id)->
                 whereMonth('created_at',$month)
                ->whereYear('created_at', date($start_yr))
				->orderBy('created_at', 'desc')
                ->get();
        
        }else{

             $debitNotes = Debitnote::where('dealer_user_id', $id)->
                 whereMonth('created_at',$month)
                ->whereYear('created_at', date($start_yr))
				->orderBy('created_at', 'desc')
                ->get();
        
        }
        
        $debitdata = array();
        $index = 0;
        foreach ($debitNotes as $key => $value) {
            $documentid=$value->systemid;
            $debitnote_id=$value->id;

			if($type=='Issue'){
                    $dealuser_id =  $value->dealer_user_id;
            } else {
                $dealuser_id =  $value->creator_user_id;
			}

            $dealerCompany =  \App\Models\Company::where('owner_user_id', $dealuser_id)->first();
            $debitnoteitem = Debitnoteitem::where('debitnote_id',$debitnote_id)->
                    whereMonth('created_at',$month)
                    ->whereYear('created_at', date($start_yr))
                    ->get();
            $sumAmount =0;
            foreach ($debitnoteitem as $key => $value) {
                $sumAmount= $sumAmount+($value->price * $value->quantity);
            } 
            $debitdata[$index] = $value;
            $debitdata[$index]->noteid = $debitnote_id;
            $debitdata[$index]->documentid = $documentid;
            $debitdata[$index]->company = $dealerCompany->name ?? '' ;
            $debitdata[$index]->is_void = DB::table('debitnote')->find($debitnote_id)->is_void ;
            $debitdata[$index]->amount = $sumAmount / 100;
            $index++;
        }
       
         return Datatables::of($debitdata)->
            addIndexColumn()->
            addColumn('inven_pro_id', function ($memberList) {
				return '<a href="javascript:openNewTabURL(\'/debitnote/'.$memberList->noteid.
					'\')" style="cursor: pointer;text-decoration:none" >'.
					$memberList->documentid.'</a>' ;
            })->
            addColumn('inven_pro_date', function ($memberList) {
                return date('dMy H:i:s',strtotime($memberList->created_at));
            })->
            addColumn('inven_pro_branch', function ($memberList) {
                return $memberList->company;
            })->
            addColumn('amount', function ($memberList) {
                return  number_format((float)$memberList->amount, 2, '.', ''); 
            })->
			setRowClass(function ($memberList) {
				if ($memberList->is_void == 1) {
					return 'void_doc';
				}
			})-> 
            escapeColumns([])->
            make(true);
    	
    }

	public function showDebitNoteIssuedListView()
	{
		$id = Auth::user()->id;
        $user_roles = usersrole::where('user_id',$id)->get();
        $debitnote_id=12;

        $is_king =  \App\Models\Company::where('owner_user_id',Auth::user()->id)->first();

        if ($is_king != null) {
            $is_king = true;
        } else {
            $is_king  = false;
        }
         return view('debitnote.debitnote_issued_list',compact('user_roles','is_king','debitnote_id'));
	}
    public function showDebitNoteReceivedListView()
    {
        $id = Auth::user()->id;
        $user_roles = usersrole::where('user_id',$id)->get();
        $debitnote_id=12;

        $is_king =  \App\Models\Company::where('owner_user_id',Auth::user()->id)->first();

        if ($is_king != null) {
            $is_king = true;
        } else {
            $is_king  = false;
        }
         return view('debitnote.debitnote_received_list',compact('user_roles','is_king','debitnote_id'));
    }

    public function pdfDebitNoteView($debitnoteid)
    {
        $debitNotes = Debitnote::where('id',$debitnoteid)->first();
        $dealer_company =  Company::where('owner_user_id', $debitNotes->dealer_user_id)->first();
        $staff= Staff::where('user_id', $debitNotes->creator_user_id)->first();
        $user_data= Staff::where('user_id', $debitNotes->creator_user_id)->
        join('users', 'users.id', '=','staff.user_id')->first();
        $is_king =  Company::where('owner_user_id',$debitNotes->creator_user_id)->first();
        $systemid =  $debitNotes->systemid;
        $code = DNS1D::getBarcodePNG(trim($systemid ), "C128");
        $qr = DNS2D::getBarcodePNG($systemid, "QRCODE");

        $product_detail = Debitnoteitem::where('debitnote_id',$debitnoteid)->get();
        $Totalprice = $product_detail->reduce(function($a, $b){return ($b->price * $b->quantity) + $a;});

        $mgLink = DB::table('mglink_dn')->
        where('debitnote_id', $debitnoteid)->
        first();

        $currency_code = DB::table('currency')->find($mgLink->currency_id)->code ?? 'MYR';
        $debitnote_void = DB::table('users')->join('staff','staff.user_id','users.id')->
        select('users.*','staff.systemid')->where('users.id',$debitNotes->void_user_id)->
        first();

        $genDate = date('dMy h:m:s');

        $paymentDetails = DB::table('arpayment')->
        join('arpaymentdn','arpaymentdn.arpayment_id','=','arpayment.id')->
        leftjoin('bank','bank.id','=','arpayment.bank_id')->
        where('arpaymentdn.debitnote_id',$debitNotes->id)->
        select("arpayment.*", "bank.company_name as bank_name")->
        whereNull('arpayment.deleted_at')->
        get();

        $totalPayment = $paymentDetails->reduce(function($a, $record) {
            return $a + $record->amount;
        });



        $pdf = PDF::setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true])->loadView("debitnote.debitnote_pdf",compact('is_king','debitnoteid',
            'user_data',
            'dealer_company',
            'genDate',
            'staff',
            'Totalprice',
            'code',
            'debitNotes',
            'mgLink','currency_code','debitnote_void',
            'qr','product_detail', 'systemid',
            'paymentDetails','totalPayment'
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
        return $pdf->download('debitnote.pdf');
    }
}
