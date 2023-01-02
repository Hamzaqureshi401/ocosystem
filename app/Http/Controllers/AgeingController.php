<?php
namespace App\Http\Controllers;

use Yajra\DataTables\DataTables;
use \App\Classes\SystemID;
use \App\Classes\UserData;

use App\Models\Staff;
use \App\Models\globaldata;
use App\Models\fakeModal;
use App\Models\MerchantCreditLimit;

use Milon\Barcode\DNS1D;
use Milon\Barcode\DNS2D;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\usersrole;
use App\Models\role;
use App\Models\Company;
use Log;
use PDF;
class AgeingController extends Controller
{
  public function __construct()
  {
      $this->middleware('auth');
       $this->middleware('CheckRole:stg');
  }

  public function fetchDataRev() {
	  try{
		  
			$user_data = new UserData();
			$mycompany = DB::table('company')->find($user_data->company_id());

			$data_1 = DB::table('invoice')->
				where('invoice.supplier_merchant_id', $mycompany->id)->
				leftjoin('company','company.id','=','invoice.dealer_merchant_id')->
				join('invoiceproduct','invoiceproduct.invoice_id','=','invoice.id')->
				orderBy('invoice.created_at','desc')->
				select(DB::RAW("SUM(invoiceproduct.price * invoiceproduct.quantity) as amount"),
					'invoice.systemid', DB::RAW("'invoice'"), 'invoice.id as invoice_id','invoice.is_void',
					'invoice.created_at', 'company.name as company_name','company.systemid as company_systemid',
					'company.id as company_id'
				)->
				groupBy('invoice.systemid')->
				get();
			
			$data_2 = DB::table('debitnote')->
				where('debitnote.creator_user_id', $mycompany->owner_user_id)->
				leftjoin('company','company.owner_user_id','=','debitnote.dealer_user_id')->
				join('debitnoteitem','debitnoteitem.debitnote_id','=','debitnote.id')->
				orderBy('debitnote.created_at','desc')->
				select(DB::RAW("SUM(debitnoteitem.price * debitnoteitem.quantity) as amount"),
					'debitnote.systemid', 'debitnote.status', DB::RAW("'debitnote'"), 'debitnote.id as debit_id',
					'debitnote.created_at','company.name as company_name','company.systemid as company_systemid',
					'company.id as company_id','debitnote.is_void'
				)->	
				groupBy('debitnote.systemid')->
				get();
		
			$data_3 = DB::table('creditnoteitem')->
				join('creditnote','creditnoteitem.creditnote_id','=','creditnote.id')->
				where('creditnote.creator_user_id', $mycompany->owner_user_id)->
				leftjoin('company','company.owner_user_id','=','creditnote.dealer_user_id')->
				orderBy('creditnote.created_at','desc')->
				select(DB::RAW("-1 * SUM(creditnoteitem.price * creditnoteitem.quantity) as amount"),
					'creditnote.systemid',
					DB::RAW("'creditnote'"), 'creditnote.id as creditnote_id', 'creditnote.created_at',
					'company.name as company_name','company.systemid as company_systemid','company.id as company_id',
					'creditnote.is_void'
				)->
				groupBy('creditnote.systemid')->
				get();

			$rev = $data_1->prepend($data_2)->prepend($data_3)->flatten(1);
			$rev  =  $rev->sortByDESC('created_at')->values();

			$rev->map(function($f) {
				$f->amount_paid = 0;
				if (!empty($f->debitnote)) {
					$amount_paid = DB::table('arpayment')->
						join('arpaymentdn','arpaymentdn.arpayment_id','=','arpayment.id')->
						whereNotIn('arpayment.is_void',['1'])->
						where('arpaymentdn.debitnote_id', $f->debit_id)->
						whereNull('arpayment.deleted_at')->
						get();
					$f->amount_paid = $amount_paid->sum('amount');
				} else if (!empty($f->invoice)) {
					$amount_paid = DB::table('arpayment')->
						join('arpaymentinv','arpaymentinv.arpayment_id','=','arpayment.id')->
						whereNotIn('arpayment.is_void',['1'])->
						where('arpaymentinv.invoice_id', $f->invoice_id)->
						whereNull('arpayment.deleted_at')->
						get();
					$f->amount_paid = $amount_paid->sum('amount');
				} else {
					$f->amount_paid = 0; 
				}

				$f->balance = $f->amount - $f->amount_paid;
				if ($f->amount < $f->amount_paid) {
					$f->status = "Completed";
				} else {
					$f->status = "Active";
				}

				if ($f->is_void == 1) {
					$f->balance = 0;
					$f->amount_paid = 0;
					$f->status = "Void";
				}
			});		
			return $rev->values();
		} catch (\Exception $e) {
			\Log::error([
				"error"	=>	$e->getMessage(),
				"File" 	=>	$e->getFile(),
				"Line"	=>	$e->getLine()
			]);
			abort(404);
		}	
  }

	public function fetchDataPay() {
		try {
			$user_data = new UserData();
			$mycompany = DB::table('company')->find($user_data->company_id());
			$data_1 = DB::table('invoice')->
				where('invoice.dealer_merchant_id', $mycompany->id)->
				leftjoin('company','company.id','=','invoice.supplier_merchant_id')->
				join('invoiceproduct','invoiceproduct.invoice_id','=','invoice.id')->
				orderBy('invoice.created_at','desc')->
				select(DB::RAW("SUM(invoiceproduct.price * invoiceproduct.quantity) as amount"),
					'invoice.systemid', DB::RAW("'invoice'"), 'invoice.id as invoice_id',
					'invoice.created_at', 'company.name as company_name','company.systemid as company_systemid',
					'company.id as company_id','invoice.is_void'
				)->
				groupBy('invoice.systemid')->
				get();
			
			$data_2 = DB::table('debitnote')->
				where('debitnote.dealer_user_id', $mycompany->owner_user_id)->
				leftjoin('company','company.owner_user_id','=','debitnote.creator_user_id')->
				join('debitnoteitem','debitnoteitem.debitnote_id','=','debitnote.id')->
				orderBy('debitnote.created_at','desc')->
				select(DB::RAW("SUM(debitnoteitem.price * debitnoteitem.quantity) as amount"),
					'debitnote.systemid', 'debitnote.status', DB::RAW("'debitnote'"), 'debitnote.id as debit_id',
					'debitnote.created_at','company.name as company_name','company.systemid as company_systemid',
					'company.id as company_id','debitnote.is_void'
				)->	
				groupBy('debitnote.systemid')->
				get();

			$data_3 = DB::table('creditnoteitem')->
				join('creditnote','creditnoteitem.creditnote_id','=','creditnote.id')->
				where('creditnote.dealer_user_id', $mycompany->owner_user_id)->
				leftjoin('company','company.owner_user_id','=','creditnote.creator_user_id')->
				orderBy('creditnote.created_at','desc')->
				select(DB::RAW("-1 * SUM(creditnoteitem.price * creditnoteitem.quantity) as amount"),
				'creditnote.systemid',
				DB::RAW("'creditnote'"), 'creditnote.id as creditnote_id', 'creditnote.created_at',
				'company.name as company_name','company.systemid as company_systemid',
			   	'company.id as company_id','creditnote.is_void'
			)->
			groupBy('creditnote.systemid')->
			get();

			$rev = $data_1->prepend($data_2)->prepend($data_3)->flatten(1);
			$rev  =  $rev->sortByDESC('created_at')->values();

			$rev->map(function($f) {
				$f->amount_paid = 0;
				if (!empty($f->debitnote)) {
					$amount_paid = DB::table('arpayment')->
						join('arpaymentdn','arpaymentdn.arpayment_id','=','arpayment.id')->
						whereNotIn('arpayment.is_void',['1'])->

						where('arpaymentdn.debitnote_id', $f->debit_id)->
						whereNull('arpayment.deleted_at')->
						get();
					$f->amount_paid = $amount_paid->sum('amount');
				} else if (!empty($f->invoice)) {
					$amount_paid = DB::table('arpayment')->
						join('arpaymentinv','arpaymentinv.arpayment_id','=','arpayment.id')->
						whereNotIn('arpayment.is_void',['1'])->
						where('arpaymentinv.invoice_id', $f->invoice_id)->
						whereNull('arpayment.deleted_at')->
						get();
					$f->amount_paid = $amount_paid->sum('amount');
				} else {
					$f->amount_paid = 0; 
				}
					

				$f->balance = $f->amount - $f->amount_paid;
				if ($f->amount < $f->amount_paid && empty($f->creditnote)) {
					$f->status = "Completed";
				} else {
					$f->status = "Active";
				}

				if ($f->is_void == 1) {
					$f->balance = 0;
					$f->amount_paid = 0;
					$f->status = "Void";
				}

			});		
			return $rev->values();
		} catch (\Exception $e) {
			\Log::error([
				"error"	=>	$e->getMessage(),
				"File" 	=>	$e->getFile(),
				"Line"	=>	$e->getLine()
			]);
			abort(404);
		}	
	}

  
  public function ShowAgeingView() {
        $id = Auth::user()->id;
        return view('ageing.ageing');
  }
 
  public function ContraView($id){
  	try {
	  	$user_data = new UserData();
		$mycompany = DB::table('company')->find($user_data->company_id());
		$contracompany = DB::table('company')->where('systemid', $id)->first();

		$data_1 = DB::table('invoice')->
			where('invoice.supplier_merchant_id', $mycompany->id)->
			where('invoice.dealer_merchant_id', $contracompany->id)->
			join('invoiceproduct','invoiceproduct.invoice_id','=','invoice.id')->
			orderBy('invoice.created_at','desc')->
			select(DB::RAW("SUM(invoiceproduct.price * invoiceproduct.quantity) as amount"),
				'invoice.systemid', DB::RAW("'invoice'"), 'invoice.id as invoice_id',
				'invoice.created_at'
			)->
			groupBy('invoice.systemid')->
			get();

		$data_2 = DB::table('debitnote')->
			where('debitnote.creator_user_id', $mycompany->owner_user_id)->
			where('debitnote.dealer_user_id', $contracompany->owner_user_id)->
			join('debitnoteitem','debitnoteitem.debitnote_id','=','debitnote.id')->
			orderBy('debitnote.created_at','desc')->
			select(DB::RAW("SUM(debitnoteitem.price * debitnoteitem.quantity) as amount"),
				'debitnote.systemid', 'debitnote.status', DB::RAW("'debitnote'"), 'debitnote.id as debit_id',
				'debitnote.created_at'
			)->	
			groupBy('debitnote.systemid')->
			get();
	
		$data_3 = DB::table('creditnote')->
			where('creditnote.creator_user_id', $mycompany->owner_user_id)->
			where('creditnote.dealer_user_id', $contracompany->owner_user_id)->
			leftjoin('company','company.owner_user_id','=','creditnote.creator_user_id')->
			join('creditnoteitem','creditnoteitem.creditnote_id','=','creditnote.id')->
			orderBy('creditnote.created_at','desc')->
			select(DB::RAW("-1 * SUM(creditnoteitem.price * creditnoteitem.quantity) as amount"),
				'company.systemid as companyId', 'company.name as company_name', 'creditnote.systemid',
				DB::RAW("'creditnote'"), 'creditnote.id as creditnote_id', 'creditnote.created_at'
			)->
				groupBy('companyId')->
			get();



		$rev = $data_1->prepend($data_2)->prepend($data_3)->flatten(1);
		$rev = $rev->unique('systemid')->sortByDESC('created_at')->values();
		
		$data_1 = DB::table('invoice')->
			where('invoice.dealer_merchant_id', $mycompany->id)->
			where('invoice.supplier_merchant_id', $contracompany->id)->
			join('invoiceproduct','invoiceproduct.invoice_id','=','invoice.id')->
			orderBy('invoice.created_at','desc')->
			select(DB::RAW("SUM(invoiceproduct.price * invoiceproduct.quantity) as amount"),
				'invoice.systemid',  DB::RAW("'invoice'"), 'invoice.id as invoice_id',
				'invoice.created_at'
			)->
			groupBy('invoice.systemid')->
			get();

		$data_2 = DB::table('debitnote')->
			where('debitnote.dealer_user_id', $mycompany->owner_user_id)->
			where('debitnote.creator_user_id', $contracompany->owner_user_id)->
			join('debitnoteitem','debitnoteitem.debitnote_id','=','debitnote.id')->
			orderBy('debitnote.created_at','desc')->
			select(DB::RAW("SUM(debitnoteitem.price * debitnoteitem.quantity) as amount"),
				'debitnote.systemid', 'debitnote.status', DB::RAW("'debitnote'"), 'debitnote.id as debit_id',
				'debitnote.created_at'
			)->	
			groupBy('debitnote.systemid')->
			get();

		$data_3 = DB::table('creditnote')->
			where('creditnote.dealer_user_id', $mycompany->owner_user_id)->
			where('creditnote.creator_user_id', $contracompany->owner_user_id)->
			leftjoin('company','company.owner_user_id','=','creditnote.creator_user_id')->
			join('creditnoteitem','creditnoteitem.creditnote_id','=','creditnote.id')->
			orderBy('creditnote.created_at','desc')->
			select(DB::RAW("-1 * SUM(creditnoteitem.price * creditnoteitem.quantity) as amount"),
				'company.systemid as companyId', 'company.name as company_name', 'creditnote.systemid',
				DB::RAW("'creditnote'"), 'creditnote.id as creditnote_id', 'creditnote.created_at'
			)->
				groupBy('companyId')->
			get();

		$payable = $data_1->prepend($data_2)->prepend($data_2)->flatten(1);
		$payable = $payable->unique('systemid')->sortByDESC('created_at')->values();

		return view('ageing.contra',compact('contracompany', 'rev', 'payable'));
	} catch (\Exception $e) {
		\Log::error([
			"error"	=>	$e->getMessage(),
			"File" 	=>	$e->getFile(),
			"Line"	=>	$e->getLine()
		]);
		abort(404);
	}
  }
  public function CreditorAllTransactions($id){
	  $rev = $this->fetchDataPay();
    return view('ageing.creditor_all_transactions',compact('rev'));
  }
  public function CreditorBalance( $record_type, $fk_id ){	
	$user_data 	= new UserData();
	$company 	= DB::table('company')->find($user_data->company_id());
	$currency 	= DB::table('currency')->find($company->currency_id)->code ?? "MYR";
    return view('ageing.creditor_balance', compact('currency', 'record_type','fk_id'));
  }
  public function CreditorDetails($id){
		$user_data = new UserData();
		$mycompany = DB::table('company')->find($user_data->company_id());
		$selectedCompany = DB::table('company')->where('systemid', $id)->first();
		
		$fetchData = $this->fetchDataPay();
		$rev = $fetchData->where('company_id', $selectedCompany->id)->values();
		return view('ageing.creditor_details', compact('rev','selectedCompany'));
  }
  public function DebtorAllTransactions($id){
		$rev = $this->fetchDataRev();  
	  return view('ageing.debtor_all_transactions',compact('rev'));
  }
  
  public function DebtorBalance($record_type,$fk_id){

	$user_data 	= new UserData();
	$company 	= DB::table('company')->find($user_data->company_id());
	$currency 	= DB::table('currency')->find($company->currency_id)->code ?? "MYR";
	$bankList 	= DB::table('bank')->get();
    return view('ageing.debtor_balance', compact('record_type', 'fk_id', 'bankList', 'currency'));
  }

  public function DebtorBalanceTable(Request $request) {
	 try {

		 	$user_data = new UserData();
			$company = DB::table('company')->find($user_data->company_id());

			switch($request->record_type) {
				case 'debitnote':
					$data = DB::table('arpayment')->
						leftjoin('bank','bank.id','=','arpayment.bank_id')->
						join('arpaymentdn','arpaymentdn.arpayment_id','=','arpayment.id')->
						join('debitnote','debitnote.id','=','arpaymentdn.debitnote_id')->
						where('arpaymentdn.debitnote_id', $request->fk_id)->
						where('debitnote.creator_user_id', $company->owner_user_id)->
						whereNull('arpayment.deleted_at')->
					  	select("arpayment.*", "bank.company_name as bank_name")->
						get();
					break;
				case 'invoice':
					$data = DB::table('arpayment')->
						leftjoin('bank','bank.id','=','arpayment.bank_id')->
						join('arpaymentinv','arpaymentinv.arpayment_id','=','arpayment.id')->
						join('invoice','invoice.id', '=', 'arpaymentinv.invoice_id')->
						where('arpaymentinv.invoice_id',$request->fk_id)->
						where('invoice.supplier_merchant_id', $company->id)->
						whereNull('arpayment.deleted_at')->
					  	select("arpayment.*", "bank.company_name as bank_name")->
						get();
					break;
				default:
					$data = collect();
				break;
			}

		  return Datatables::of($data)->
			  addIndexColumn()->
			  
			  
			  addColumn('receipt_no', function ($data) {
				  	$url = route('receipt.payment', $data->systemid);
					return <<<EOD
					<span class="os-linkcolor" style="cursor:pointer;"
						onclick="openNewTabURL('$url')">$data->systemid</span>
EOD;
			  })->
			  addColumn('date_paid', function ($data) {
				  return date("dMy",  strtotime($data->date_paid));
			  })->

			  addColumn('bank', function ($data) {
				  return $data->bank_name;
			  })->

			  addColumn('note', function ($data) {
				  return $data->note;
			  })->

			  addColumn('method', function ($data) {
				  return ucfirst(str_replace('_', ' ',$data->method));
			  })->

			  addColumn('amount_paid', function ($data) {

				  if ($data->is_void == 1) {
					  return "0.00";
					}
				  return number_format($data->amount / 100,2);
			  })->

			  addColumn('del', function ($data) {
				$img = asset('/images/redcrab_50x50.png');
				return <<<EOD
					<img src="$img" height="25" 
						onclick="deletePayment($data->id)" style='cursor:pointer' />
EOD;
			  })->
		  
			  setRowClass(function ($memberList) {
				if ($memberList->is_void == 1) {
					return 'void_doc';
				}
			  })-> 

			  escapeColumns([])->
			  make(true);

	  } catch (\Exception $e) {
	  	Log::error([
			"Error"	=>	$e->getMessage(),
			"File"	=>	$e->getFile(),
			"Line"	=>	$e->getLine()
		]);
		abort(404);
	  }  
  }

  public function CreditorBalanceTable(Request $request) {
	 try {

		 	$user_data = new UserData();
			$company = DB::table('company')->find($user_data->company_id());

			switch($request->record_type) {
				case 'debitnote':
					$data = DB::table('arpayment')->
						leftjoin('bank','bank.id','=','arpayment.bank_id')->
						join('arpaymentdn','arpaymentdn.arpayment_id','=','arpayment.id')->
						join('debitnote','debitnote.id','=','arpaymentdn.debitnote_id')->
						where('arpaymentdn.debitnote_id', $request->fk_id)->
						where('debitnote.dealer_user_id', $company->owner_user_id)->
						whereNull('arpayment.deleted_at')->
					  	select("arpayment.*", "bank.name as bank_name")->
						get();
					break;
				case 'invoice':
					$data = DB::table('arpayment')->
						leftjoin('bank','bank.id','=','arpayment.bank_id')->
						join('arpaymentinv','arpaymentinv.arpayment_id','=','arpayment.id')->
						join('invoice','invoice.id', '=', 'arpaymentinv.invoice_id')->
						where('arpaymentinv.invoice_id',$request->fk_id)->
						where('invoice.dealer_merchant_id', $company->id)->
						whereNull('arpayment.deleted_at')->
					  	select("arpayment.*", "bank.name as bank_name")->
						get();
					break;
				default:
					$data = collect();
				break;
			}

		  return Datatables::of($data)->
			  addIndexColumn()->
			  
			  addColumn('date_paid', function ($data) {
				  return date("dMy",  strtotime($data->date_paid));
			  })->

			  addColumn('bank', function ($data) {
				  return $data->bank_name;
			  })->

			  addColumn('note', function ($data) {
				  return $data->note;
			  })->

			  addColumn('method', function ($data) {
				  return ucfirst(str_replace('_', ' ',$data->method));
			  })->

			  addColumn('amount_paid', function ($data) {
				  
				  if ($data->is_void == 1) {
					  return "0.00";
					}

				  return number_format($data->amount / 100,2);
			  })->

			  addColumn('del', function ($data) {
				$img = asset('/images/redcrab_50x50.png');
				return <<<EOD
					<img src="$img" height="25" 
						onclick="deletePayment($data->id)" style='cursor:pointer' />
EOD;
			  })->
			  
			  setRowClass(function ($memberList) {
				if ($memberList->is_void == 1) {
					return 'void_doc';
				}
			  })-> 


			  escapeColumns([])->
			  make(true);

	  } catch (\Exception $e) {
	  	Log::error([
			"Error"	=>	$e->getMessage(),
			"File"	=>	$e->getFile(),
			"Line"	=>	$e->getLine()
		]);
		abort(404);
	  }
  
  }



  public function newPayment(Request $request) {
	  try {

		  $user_data = new UserData();
		  $validation = Validator::make($request->all(), [
		  		"date_paid"				=>	"required",
				"bank"					=>	"required",
				"select_payment_method" =>	"required",
				"amount_paid"			=>	"required",
				"fk_id"					=>	"required",
				"record_type"			=>	"required"
		  ]);

		  $systemid = new SystemID('arpayment');
		  if ($validation->fails() || !in_array($request->record_type, ['debitnote','invoice'])) {
			  	throw new \Exception("Incomplete information");
		  }

		  switch($request->record_type) {
		  	case 'debitnote':
				$debitNoteAmount = DB::table('debitnoteitem')->
						select(DB::RAW("sum(debitnoteitem.price * debitnoteitem.quantity) as amount"))->
						where('debitnoteitem.debitnote_id', $request->fk_id)->
						groupBy('debitnoteitem.debitnote_id')->
						get()->first()->amount ?? 0;
					
				$paidAmount = DB::table('arpayment')->
					join('arpaymentdn','arpaymentdn.arpayment_id',
						'=','arpayment.id')->
					where('arpaymentdn.debitnote_id', $request->fk_id)->
					select('arpayment.amount')->
					get()->sum('amount');
				
				$payable = $debitNoteAmount - $paidAmount;

				$dealerCompany = DB::table('company')->
					join('debitnote','debitnote.dealer_user_id','=','company.owner_user_id')->
					where('debitnote.id', $request->fk_id)->
					select('company.*')->
					first();
				
				$supCompany = DB::table('company')->
					join('debitnote','debitnote.creator_user_id','=','company.owner_user_id')->
					where('debitnote.id', $request->fk_id)->
					select('company.*')->
					first();

				if (!empty(DB::table('debitnote')->where([
					"id"		=>  $request->fk_id,
					'is_void'	=>	1
					])->first())) {
					throw new 
						\Exception("Debit note is void.");
				}

				if ($request->amount_paid > $payable) {
					throw new 
					\Exception("Unable to process due to the payment amount exceeding the balance.
				   		Please try again.");
				}
			break;

			case 'invoice':
				$invoiceAmount = DB::table('invoiceproduct')->
					select(DB::RAW("sum(invoiceproduct.price * invoiceproduct.quantity) as amount"))->
					where('invoiceproduct.invoice_id', $request->fk_id)->
					groupBy('invoiceproduct.invoice_id')->
					get()->first()->amount ?? 0;

				$paidAmount = DB::table('arpayment')->
					join('arpaymentinv','arpaymentinv.arpayment_id',
						'=','arpayment.id')->
					where('arpaymentinv.arpayment_id', $request->fk_id)->
					select('arpayment.amount')->
					get()->sum('amount');

				$dealerCompany = DB::table('company')->
					join('invoice','invoice.dealer_merchant_id','=','company.id')->
					where('invoice.id', $request->fk_id)->select('company.*')->first();
				
				$supCompany = DB::table('company')->
					join('invoice','invoice.supplier_merchant_id','=','company.id')->
					where('invoice.id', $request->fk_id)->select('company.*')->first();
			
				if (!empty(DB::table('invoice')->where([
					"id"		=>  $request->fk_id,
					'is_void'	=>	1
					])->first())) {
					throw new 
						\Exception("Invoice is void.");
				}


				$payable = $invoiceAmount - $paidAmount;

				if ($request->amount_paid > $payable) {
					throw new 
						\Exception("Unable to process due to the payment amount exceeding the balance.
				   		Please try again.");
				}
	
				break;
		  }

			$fetchData			= $this->fetchDataRev();
		  	$fetchData 			= $fetchData->where('company_systemid', $dealerCompany->systemid);
			$totalOutstanding 	= $fetchData->sum('balance');
			if ($request->amount_paid > $totalOutstanding) {
				throw new 
					\Exception("The payment amount is exceeding the total outstanding.");
			}
		  $arpayment_id  = DB::table('arpayment')->insertGetId([
			 	'systemid'		=>	$systemid,
			  	"date_paid"		=>	date('Y-m-d', strtotime($request->date_paid)),
		  		"bank_id"		=> 	$request->bank,
				"method"		=>	$request->select_payment_method,
				"note"			=>	$request->note,
				"amount"		=>	$request->amount_paid,
				'user_id'		=>	Auth::User()->id,
				"created_at"	=>	date("Y-m-d H:i:s"),
				"updated_at"	=>	date("Y-m-d H:i:s")
		  ]);

		  switch($request->record_type) {
		  	case 'debitnote':
					DB::table('arpaymentdn')->insert([
					"arpayment_id"	=>	$arpayment_id,
					"debitnote_id"	=>	$request->fk_id,
					"created_at"	=>	date("d-m-y H:i:s"),
					"updated_at"	=>	date("d-m-y H:i:s")
				]);
			break;
			case 'invoice':
				DB::table('arpaymentinv')->insert([
					"arpayment_id"	=>	$arpayment_id,
					"invoice_id"	=>	$request->fk_id,
					"created_at"	=>	date("d-m-y H:i:s"),
					"updated_at"	=>	date("d-m-y H:i:s")
				]);
			break;
			default:
				throw new \Exception("Invalid entry");
			break;
		  }
	  
		  $merchant_credit_limit = MerchantCreditLimit::where([
                'dealer_merchant_id'=> $dealerCompany->dealer_merchant_id ?? 0,
                'supplier_merchant_id'=> $user_data->company_id()
			])->decrement('avail_credit_limit',  $request->amount_paid);

		  $merchantglobal = DB::table('merchantglobal')->
				where('merchant_id', $supCompany->id)->
				first();

		  $msg = "Payment added";
		  $this->mgLink('rcp', 'arpayment_id', $arpayment_id,  $supCompany, $dealerCompany, $merchantglobal);
	  } catch (\Exception $e) {
	  	Log::error([
			"Error"	=>	$e->getMessage(),
			"File"	=>	$e->getFile(),
			"Line"	=>	$e->getLine()
		]);

		$msg = $e->getMessage();
	  }

		return view('layouts.dialog', compact('msg'))->render();
  }


 	public function ReceiptPayment(Request $request) {
		try {
		
			$document 	= DB::table('arpayment')->where('systemid',$request->systemid)->first();
			
			if (!empty($document)) {

				$document_child = DB::table('arpaymentinv')->
					join('invoice','invoice.id','arpaymentinv.invoice_id')->
					where('arpaymentinv.arpayment_id',$document->id)->first();

				if (empty($document_child)) {
					$document_child = DB::table('arpaymentdn')->
						join('debitnote','debitnote.id','arpaymentdn.debitnote_id')->
						where('arpaymentdn.arpayment_id',$document->id)->first();
					
					$document_child->merchant_id = DB::table('company')->
						where('owner_user_id',$document_child->creator_user_id)->first()->id;
					$document_child->doc_name = "Debit Note";
				} else {
					$document_child->merchant_id = $document_child->supplier_merchant_id;
					$document_child->doc_name = "Invoice";
				} 
			}

			$code 		= DNS1D::getBarcodePNG(trim($document->systemid), "C128");
			$qr			= DNS2D::getBarcodePNG($document->systemid, "QRCODE");

			$bankDetails = DB::table('bank')->find($document->bank_id);

			$mgLink = DB::table('mglink_rcp')->
				where('arpayment_id',$document->id)->
				first();

			$document_void = DB::table('users')->join('staff','staff.user_id','users.id')->
				where('users.id',$document->void_user_id)->first();
			
			$user_data = DB::table('users')->join('staff','staff.user_id','users.id')->
				select('users.*','staff.systemid')->where('users.id',$document->user_id)->
				first();

			$currency_code = DB::table('currency')->find($mgLink->currency_id)->code ?? 'MYR';
			return view('ageing.payment_receipt', compact('document','code','qr',
				'mgLink','document_child','mgLink','currency_code', 'document_void', 
				'user_data', 'bankDetails'));
		} catch (\Exception $e) {
			Log::error([
				"Error"	=>	$e->getMessage(),
				"File"	=>	$e->getFile(),
				"Line"	=>	$e->getLine()
			]);
			abort(404);
		}

	}

    public function ReceiptPaymentDownload(Request $request) {
        try {

            $document 	= DB::table('arpayment')->where('systemid',$request->systemid)->first();

            if (!empty($document)) {

                $document_child = DB::table('arpaymentinv')->
                join('invoice','invoice.id','arpaymentinv.invoice_id')->
                where('arpaymentinv.arpayment_id',$document->id)->first();

                if (empty($document_child)) {
                    $document_child = DB::table('arpaymentdn')->
                    join('debitnote','debitnote.id','arpaymentdn.debitnote_id')->
                    where('arpaymentdn.arpayment_id',$document->id)->first();

                    $document_child->merchant_id = DB::table('company')->
                    where('owner_user_id',$document_child->creator_user_id)->first()->id;
                    $document_child->doc_name = "Debit Note";
                } else {
                    $document_child->merchant_id = $document_child->supplier_merchant_id;
                    $document_child->doc_name = "Invoice";
                }
            }

            $code 		= DNS1D::getBarcodePNG(trim($document->systemid), "C128");
            $qr			= DNS2D::getBarcodePNG($document->systemid, "QRCODE");

            $bankDetails = DB::table('bank')->find($document->bank_id);

            $mgLink = DB::table('mglink_rcp')->
            where('arpayment_id',$document->id)->
            first();

            $document_void = DB::table('users')->join('staff','staff.user_id','users.id')->
            where('users.id',$document->void_user_id)->first();

            $user_data = DB::table('users')->join('staff','staff.user_id','users.id')->
            select('users.*','staff.systemid')->where('users.id',$document->user_id)->
            first();

            $currency_code = DB::table('currency')->find($mgLink->currency_id)->code ?? 'MYR';


            $pdf = PDF::setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true])->loadView('ageing.payment_receipt_pdf', compact('document','code','qr',
                'mgLink','document_child','mgLink','currency_code', 'document_void',
                'user_data', 'bankDetails'));


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
            return $pdf->download('receipt.pdf');
        } catch (\Exception $e) {
            Log::error([
                "Error"	=>	$e->getMessage(),
                "File"	=>	$e->getFile(),
                "Line"	=>	$e->getLine()
            ]);
            abort(404);
        }

    }

	public function deletePayment(Request $request) {
		try {
			DB::table('arpayment')->update([
				"deleted_at" =>	date("d-m-Y H:i:s")
			]);
			$msg = "Payment deleted";
			return view('layouts.dialog',
				compact('msg'))->render();

		} catch (\Exception $e) {
			Log::error([
				"Error"	=>	$e->getMessage(),
				"File"	=>	$e->getFile(),
				"Line"	=>	$e->getLine()
			]);
			abort(404);
		}
	}

	public function ReceiptPaymentRevList(Request $request) {
		try {
			$user_data = new UserData();
			$company = DB::table('company')->find($user_data->company_id());

			$arpayment_rev = DB::table('arpayment')->
				join('arpaymentinv','arpaymentinv.arpayment_id','arpayment.id')->
				join('invoice','invoice.id','arpaymentinv.invoice_id')->
				leftjoin('company','company.id','invoice.supplier_merchant_id')->
				where('invoice.dealer_merchant_id',$company->id)->
				select('arpayment.*','company.name')->
				get()
				->
				merge(
					DB::table('arpayment')->
						join('arpaymentdn','arpaymentdn.arpayment_id','arpayment.id')->
						join('debitnote','debitnote.id','arpaymentdn.debitnote_id')->
						leftjoin('company','company.id','debitnote.dealer_user_id')->
						where('debitnote.creator_user_id',$company->owner_user_id)->
						select('arpayment.*','company.name')->
						get()
					);

			return view('ageing.payment_received_list', compact('arpayment_rev'));
		}  catch (\Exception $e) {
			Log::error([
				"Error"	=>	$e->getMessage(),
				"File"	=>	$e->getFile(),
				"Line"	=>	$e->getLine()
			]);
			abort(404);
		}
	}

	public function ReceiptPaymentIssueList(Request $request) {
		try {
			$user_data = new UserData();
			$company = DB::table('company')->find($user_data->company_id());

			$arpayment_rev = DB::table('arpayment')->
				join('arpaymentinv','arpaymentinv.arpayment_id','arpayment.id')->
				join('invoice','invoice.id','arpaymentinv.invoice_id')->
				leftjoin('company','company.id','invoice.dealer_merchant_id')->
				where('invoice.supplier_merchant_id',$company->id)->
				select('arpayment.*','company.name')->
				get()
				->
				merge(
					DB::table('arpayment')->
						join('arpaymentdn','arpaymentdn.arpayment_id','arpayment.id')->
						join('debitnote','debitnote.id','arpaymentdn.debitnote_id')->
						leftjoin('company','company.id','debitnote.creator_user_id')->
						where('debitnote.dealer_user_id',$company->owner_user_id)->
						select('arpayment.*','company.name')->
						get()
					);


			return view('ageing.payment_issued_list',compact('arpayment_rev'));
		}  catch (\Exception $e) {
			Log::error([
				"Error"	=>	$e->getMessage(),
				"File"	=>	$e->getFile(),
				"Line"	=>	$e->getLine()
			]);
			abort(404);
		}
	}

	public function DebtorDetails($id){
		$user_data = new UserData();
		$mycompany = DB::table('company')->find($user_data->company_id());
		$selectedCompany = DB::table('company')->where('systemid', $id)->first();

		$fetchData = $this->fetchDataRev();
		$rev = $fetchData->where('company_id', $selectedCompany->id)->values();
		$rev  =  $rev->sortByDESC('created_at')->values();
		return view('ageing.debtor_details',
			compact('rev','selectedCompany'));
	}


	public function payable_details_table(Request $request) {
		try {
			$fetchData = $this->fetchDataPay();
			$fetchData = $fetchData->groupBy('company_systemid');
			$data = collect();
			
			$fetchData->map(function($z) use ($data) {
				$packet = collect();
				$packet->companyId  	= $z[0]->company_systemid;
				$packet->company_name  	= $z[0]->company_name;
				$packet->amount			= $z->sum('balance');
				$data->push($packet);
			});

			return Datatables::of($data)->
				addIndexColumn()->
			
				addColumn('company_id', function($data) {
					return $data->companyId;
				})->
				addColumn('company_name', function($data) {
					return $data->company_name;
				})->
				addColumn('total', function($data) {
					$amount = number_format($data->amount/100,2) ?? '0.00';
					return <<<EOD
				<a href="javascript:openNewTabURL('/creditor_details/$data->companyId')" class="os-linkcolor">$amount</a>
EOD;
				})->

				escapeColumns([])->
				make(true);

		} catch (\Exception $e) {
			\Log::error([
				"error"	=>	$e->getMessage(),
				"File" 	=>	$e->getFile(),
				"Line"	=>	$e->getLine()
			]);
			abort(404);
		}	
	}


	public function receivable_details_table(Request $request) {
		try {	
			$fetchData = $this->fetchDataRev();
			$fetchData = $fetchData->groupBy('company_systemid');
			$data = collect();
			
			$fetchData->map(function($z) use ($data) {
				$packet = collect();
				$packet->companyId  	= $z[0]->company_systemid;
				$packet->company_name  	= $z[0]->company_name;
				$packet->amount			= $z->sum('balance');
				$data->push($packet);
			});

			return Datatables::of($data)->
				addIndexColumn()->
			
				addColumn('company_id', function($data) {
					return $data->companyId;
				})->
				addColumn('company_name', function($data) {
					return $data->company_name;
				})->
				addColumn('total', function($data) {
					$amount =  number_format($data->amount/100,2) ?? '0.00';

					return <<<EOD
				<a href="javascript: openNewTabURL('/debtor_details/$data->companyId')" class="os-linkcolor">$amount</a>
EOD;
				})->
				addColumn('contra', function($data) {
					return <<<EOD
				<a href="/contra/$data->companyId" target="_blank" style="text-align:center;" class="">
					<div data-field="" data-target="#" data-toggle="modal" class="">
						<img class="" src="/images/bluecrab_50x50.png" style="width:25px;height:25px;cursor:pointer">
					</div>
				</a>
EOD;
				})->
				escapeColumns([])->
				make(true);

		} catch (\Exception $e) {
			\Log::error([
				"error"	=>	$e->getMessage(),
				"File" 	=>	$e->getFile(),
				"Line"	=>	$e->getLine()
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

			$slug_footer = $slug."_footer";
			$mgLink[$fk]	= $fk_value;
			$mgLink[$slug."_footer"]	= $merchantglobal->$slug_footer ?? '';
			DB::table("mglink_".$slug)->insert($mgLink);
	}


}
