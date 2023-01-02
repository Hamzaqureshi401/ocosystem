<?php

namespace App\Http\Controllers;

use App\StockReport;
use Illuminate\Http\Request;
use \App\Http\Controllers\FinancialYearController;
use App\Models\FinancialYear;
use Log;
use \App\Classes\SystemID;
use \App\Classes\UserData;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\usersrole;
use Matrix\Exception;
use Yajra\DataTables\DataTables;
use \App\Models\terminal;
use \App\Models\location;
use \App\Models\locationterminal;
use \App\Models\merchantlocation;
use \App\Models\product;
use \App\Models\opos_receipt;
use App\Models\Merchant;
use \App\Models\merchantproduct;
use App\Models\inventorycost;
use \App\Models\FranchiseMerchantLocTerm;
use Carbon\Carbon;

use App\Models\Creditnote;
use App\Models\Salesorder;
use App\Models\Debitnote;

class VirtualCabinetController extends Controller
{

	public function showVCAutoView() {
		Log::debug('***** showVCAutoView() *****');
        $FYData = new FinancialYearController();
        $FYData = $FYData->showFinancialYearView();
        return view('virtualcabinet.vc_auto',compact('FYData'));
    }

	function show1View($id) {
		try {

		if (!filter_var($id, FILTER_VALIDATE_INT)) {
			throw new Exception("validation_error", 1);
		}
	
		$FY = FinancialYear::find($id);

		$this->user_data = new UserData();
		$model           = new locationterminal();

        $ids  = 
        merchantlocation::join('location','location.id','=',
            'merchantlocation.location_id')->
            where('merchantlocation.merchant_id',
            $this->user_data->company_id())->
            whereNull('location.deleted_at')->
            pluck('merchantlocation.location_id');

		$dlt=$model->whereIn('location_id', $ids)->pluck('terminal_id');
		$franchise_terminal = FranchiseMerchantLocTerm::select('franchisemerchantlocterm.terminal_id')->
			join('franchisemerchantloc', 'franchisemerchantloc.id', '=',
				'franchisemerchantlocterm.franchisemerchantloc_id')->
			join('franchisemerchant','franchisemerchant.id','=',
				'franchisemerchantloc.franchisemerchant_id')->	
			join('franchise','franchise.id','=', 'franchisemerchant.franchise_id')->
			join('company', 'franchise.owner_merchant_id', '=','company.id')->			
			where(
			//	['franchisemerchantlocterm.terminal_id' => $terminal->id],
				["franchisemerchant.franchisee_merchant_id" => $this->user_data->company_id()]
			)->
			pluck('franchisemerchantlocterm.terminal_id');
		
		$dlt = $dlt->merge($franchise_terminal);
		
		$franchise_terminal_own = FranchiseMerchantLocTerm::select('franchisemerchantlocterm.terminal_id')->
			join('franchisemerchantloc', 'franchisemerchantloc.id', '=',
				'franchisemerchantlocterm.franchisemerchantloc_id')->
			join('franchisemerchant','franchisemerchant.id','=',
				'franchisemerchantloc.franchisemerchant_id')->	
			join('franchise','franchise.id','=', 'franchisemerchant.franchise_id')->
			join('company', 'franchise.owner_merchant_id', '=','company.id')->			
			where(
			//	['franchisemerchantlocterm.terminal_id' => $terminal->id],
				["franchise.owner_merchant_id" => $this->user_data->company_id()]
			)->
			pluck('franchisemerchantlocterm.terminal_id');

		$dlt = $dlt->diff($franchise_terminal_own);

		$dltrec =terminal::whereIn('id',$dlt)->get('id');

	  	$company = DB::table('company')->find($this->user_data->company_id());
		$userId = $company->owner_user_id;	

		//$userId = Auth::user()->id;
		$merchant = Merchant::select('merchant.id as id')
			->join('company', 'company.id', '=', 'merchant.company_id')
			->where('company.owner_user_id', $userId)->first();

		if (!$FY) {
			throw new Exception("FY_not_found", 25);
		}

		$start_yr = $FY->start_financial_year->format('dMy');

		$end_yr  = (\Carbon\Carbon::create($FY->start_financial_year->toDateTimeString())->
			add(1,'year')->add(-1,'day')->format('dMy'));


		$damageyr = $FY->start_financial_year->format('y');
         /*   	
		$damage = Merchantproduct::where('merchant_id',$merchant->id)
				->join("product", 'merchantproduct.product_id', '=', 'product.id')
				->join("opos_receiptproduct", 'product.id', '=', 'opos_receiptproduct.product_id')
				->join("opos_refund", 'opos_receiptproduct.id', '=', 'opos_refund.receiptproduct_id')
				->join("opos_damagerefund", 'opos_refund.id' , '=', 'opos_damagerefund.refund_id')
				->leftjoin('opos_receipt', 'opos_receiptproduct.receipt_id', '=', 'opos_receipt.id')
				->select('product.systemid as productsys_id', 'product.id as product_id', 'product.thumbnail_1', 'opos_receiptproduct.name', 'opos_receipt.systemid as document_no', 'opos_damagerefund.damage_qty as quantity', 'opos_damagerefund.created_at as last_update')
				->get();
			
		$wastage = Merchantproduct::where('merchant_id',$merchant->id)
			 ->join("product", 'merchantproduct.product_id', '=', 'product.id')
			 ->join("opos_wastageproduct",'product.id' , '=','opos_wastageproduct.product_id' )
			 ->join('opos_wastage', 'opos_wastage.id', '=', 'opos_wastageproduct.wastage_id')
			 ->select('product.systemid as productsys_id', 'product.id as product_id', 'product.thumbnail_1', 'product.name', 'opos_wastage.systemid as document_no', 'opos_wastageproduct.wastage_qty as quantity', 'opos_wastageproduct.created_at as last_update')
			->get();
		  */
		$damage  = product::join("opos_receiptproduct", 'product.id', '=', 'opos_receiptproduct.product_id')
				->join("opos_refund", 'opos_receiptproduct.id', '=', 'opos_refund.receiptproduct_id')
                ->join("opos_damagerefund", 'opos_refund.id' , '=', 'opos_damagerefund.refund_id')
          //      ->whereMonth('opos_damagerefund.created_at', date($month))
            //    ->whereYear('opos_damagerefund.created_at', date($year))
                ->join('locationproduct','product.id','=','locationproduct.product_id')
                ->join('location','locationproduct.location_id','=','location.id')
				->leftjoin('opos_receipt', 'opos_receiptproduct.receipt_id', '=', 'opos_receipt.id')
				->join('staff','opos_refund.confirmed_user_id','=','staff.user_id')
				->where('staff.company_id',$this->user_data->company_id())
				->select('product.systemid as productsys_id', 'product.id as product_id', 'product.thumbnail_1', 
				'opos_receiptproduct.name', 'opos_receipt.systemid as document_no', 
				'opos_damagerefund.damage_qty as quantity', 'opos_damagerefund.created_at as last_update',
				'location.branch as location','location.id as locationid')
                ->groupBy('location')
                ->get();
				
		//$wastage = Merchantproduct::where('merchant_id',$merchant->id)
		//	 ->join("product", 'merchantproduct.product_id', '=', 'product.id')
		$wastage = product::
             join("opos_wastageproduct",'product.id' , '=','opos_wastageproduct.product_id' )
             //->whereMonth('opos_wastageproduct.created_at', date($month))
            // ->whereYear('opos_wastageproduct.created_at', date($year))
             ->join('location','location.id','=','opos_wastageproduct.location_id')
			 ->join('opos_wastage', 'opos_wastage.id', '=', 'opos_wastageproduct.wastage_id')
			 ->join('staff','opos_wastage.staff_user_id','=','staff.user_id')
			 ->where('staff.company_id',$this->user_data->company_id())
			 ->select('product.systemid as productsys_id', 'product.id as product_id', 'product.thumbnail_1', 'product.name', 'opos_wastage.systemid as document_no', 'opos_wastageproduct.wastage_qty as quantity', 'opos_wastageproduct.created_at as last_update','location.branch as location','location.id as locationid')
             ->groupBy('location')
             ->get();
        
		$item_count = count($wastage);
		foreach ($wastage as $key => $value) {
			$damage[$item_count] = $value;
			$damage[$item_count]->type = 'wastage';
			$item_count++;
		}
		
		$purchaseorders = $purchaseorders_rev = $salesOrder  = $invoiceIssued =
		   	$arpayment_issued = $arpayment_rev = $invoiceRev = $DOIssued = $DORev = [];

		for($i=0;$i<12;$i++) {
			$opmonthreceipt[$i]=opos_receipt::
				whereIn('terminal_id',$dltrec)->
				whereMonth('created_at',$i+1)->
				whereBetween('created_at', [
					new Carbon($start_yr),
					new Carbon($end_yr)
				])->get()->count();

			$opmonthterminal[$i]=opos_receipt::
				whereIn('terminal_id',$dltrec)->
				whereMonth('created_at',$i+1)->
				whereBetween('created_at', [
					new Carbon($start_yr),
					new Carbon($end_yr)
				])->get('terminal_id')->count();

			$inventoryCost[$i] = inventorycost::
				where('buyer_merchant_id',  $this->user_data->company_id())->
				whereMonth('created_at',$i+1)->
				whereBetween('created_at', [
					new Carbon($start_yr),
					new Carbon($end_yr)
				])->get()->count();

			$tracking_report[$i] = StockReport::
				whereMonth('created_at',$i+1)->
				whereBetween('created_at', [
					new Carbon($start_yr),
					new Carbon($end_yr)
				])->get()->count();

			$creditissue[$i] = Creditnote::where('creator_user_id',$userId)->
				whereMonth('created_at',$i+1)->
				whereBetween('created_at', [
					new Carbon($start_yr),
					new Carbon($end_yr)
				])->get()->count();

			$debitissue[$i] = Debitnote::where('creator_user_id',$userId)->
				whereMonth('created_at',$i+1)->
				whereBetween('created_at', [
					new Carbon($start_yr),
					new Carbon($end_yr)
				])->get()->count();


			$creditreceived[$i] = Creditnote::where('dealer_user_id',$userId)->
				whereMonth('created_at',$i+1)->
				whereBetween('created_at', [
					new Carbon($start_yr),
					new Carbon($end_yr)
				])->get()->count();

			$debitreceived[$i] = Debitnote::where('dealer_user_id',$userId)->
				whereMonth('created_at',$i+1)->
				whereBetween('created_at', [
					new Carbon($start_yr),
					new Carbon($end_yr)
				])->get()->count();

			$invoiceIssued[$i] = DB::table('invoice')->where(
				'supplier_merchant_id',$this->user_data->company_id())->
				whereMonth('created_at',$i+1)->
				whereBetween('created_at', [
					new Carbon($start_yr),
					new Carbon($end_yr)
				])->get()->count();


			$invoiceRev[$i] = DB::table('invoice')->where(
				'dealer_merchant_id',$this->user_data->company_id())->
				whereMonth('created_at',$i+1)->
				whereBetween('created_at', [
					new Carbon($start_yr),
					new Carbon($end_yr)
				])->get()->count();

			$salesOrder[$i] = Salesorder::where('creator_user_id',$userId)->
				whereMonth('created_at',$i+1)->
				whereBetween('created_at', [
					new Carbon($start_yr),
					new Carbon($end_yr)
				])->get()->count();

				//$DOIssued = $DORev
			$DOIssued[$i] = DB::table('deliveryorder')->where('issuer_merchant_id', 
				$this->user_data->company_id())->
				whereMonth('created_at',$i+1)->
				whereBetween('created_at', [
					new Carbon($start_yr),
					new Carbon($end_yr)
				])->get();

			
			$DOIssued[$i] = $DOIssued[$i]->filter(function($f) {
				return !empty(DB::table('deliveryorderproduct')->
					where('deliveryorder_id', $f->id)->first());
			})->count();

			$DORev[$i] = DB::table('deliveryorder')->where('receiver_merchant_id', 
				$this->user_data->company_id())->
				whereMonth('created_at',$i+1)->
				whereBetween('created_at', [
					new Carbon($start_yr),
					new Carbon($end_yr)
				])->get();


			$DORev[$i] = $DORev[$i]->filter(function($f) {
				return !empty(DB::table('deliveryorderproduct')->
					where('deliveryorder_id', $f->id)->first());
			})->count();

			$purchaseorders[$i] = DB::table('purchaseorder')
				->join('merchantpurchaseorder', 'merchantpurchaseorder.purchaseorder_id','=','purchaseorder.id')
			//	->join('merchant', 'merchantpurchaseorder.merchant_id','=','merchant.company_id')
				->join('company', 'company.id','=','merchantpurchaseorder.merchant_id')
				->where('issuer_merchant_id', $this->user_data->company_id())
				->select('purchaseorder.id','purchaseorder.systemid','purchaseorder.status','company.name')
				->whereMonth('purchaseorder.created_at',$i+1)
				->whereBetween('purchaseorder.created_at', [
					new Carbon($start_yr),
					new Carbon($end_yr)
				])
				->get()->count();

			$purchaseorders_rev[$i] = DB::table('purchaseorder')
				->join('merchantpurchaseorder', 'merchantpurchaseorder.purchaseorder_id','=','purchaseorder.id')
				->join('merchant', 'purchaseorder.issuer_merchant_id','=','merchant.company_id')
				->join('company', 'company.id','=','merchant.company_id')
				->where('merchantpurchaseorder.merchant_id', $this->user_data->company_id())
				->select('purchaseorder.id','purchaseorder.systemid','purchaseorder.status','company.name')
				->whereMonth('purchaseorder.created_at',$i+1)
				->whereBetween('purchaseorder.created_at', [
					new Carbon($start_yr),
					new Carbon($end_yr)
				])
				->get()->count();

			
			$arpayment_rev[$i] = DB::table('arpayment')->
				join('arpaymentinv','arpaymentinv.arpayment_id','arpayment.id')->
				join('invoice','invoice.id','arpaymentinv.invoice_id')->
				where('invoice.dealer_merchant_id',$this->user_data->company_id())->
				whereMonth('arpayment.created_at',$i+1)->
				whereBetween('arpayment.created_at', [
					new Carbon($start_yr),
					new Carbon($end_yr)
				])->
				get()->count();

			$arpayment_rev[$i] += DB::table('arpayment')->
				join('arpaymentdn','arpaymentdn.arpayment_id','arpayment.id')->
				join('debitnote','debitnote.id','arpaymentdn.debitnote_id')->
				where('debitnote.creator_user_id',$userId)->
				whereMonth('arpayment.created_at',$i+1)->
				whereBetween('arpayment.created_at', [
					new Carbon($start_yr),
					new Carbon($end_yr)
				])->
				get()->count();


	
			$arpayment_issued[$i] = DB::table('arpayment')->
				join('arpaymentinv','arpaymentinv.arpayment_id','arpayment.id')->
				join('invoice','invoice.id','arpaymentinv.invoice_id')->
				where('invoice.supplier_merchant_id',$this->user_data->company_id())->
				whereMonth('arpayment.created_at',$i+1)->
				whereBetween('arpayment.created_at', [
					new Carbon($start_yr),
					new Carbon($end_yr)
				])->
				get()->count();

			$arpayment_issued[$i] += DB::table('arpayment')->
				join('arpaymentdn','arpaymentdn.arpayment_id','arpayment.id')->
				join('debitnote','debitnote.id','arpaymentdn.debitnote_id')->
				where('debitnote.dealer_user_id',$userId)->
				whereMonth('arpayment.created_at',$i+1)->
				whereBetween('arpayment.created_at', [
					new Carbon($start_yr),
					new Carbon($end_yr)
				])->
				get()->count();


		foreach($damage as $data){
				if(strval($i+1)== date('m', strtotime($data->last_update)) &&
				$damageyr == date('y', strtotime($data->last_update))){

				$damagewastedate[$i] = date('m', strtotime($data->last_update));
				break;

				} else{
				   $damagewastedate[$i] ='';
				}                   
			}     
		}

		// Squidster: protect $damagewastedate
		if (empty($damagewastedate)) $damagewastedate = null;

		$data = view('virtualcabinet.auto_template',
			compact('FY','start_yr','end_yr','opmonthreceipt','purchaseorders_rev',
			'opmonthterminal', 'inventoryCost', 'tracking_report', 'purchaseorders','salesOrder',
			'invoiceRev','invoiceIssued','purchaseorders_rev', 'DOIssued' , 'DORev',
			'damagewastedate','creditissue','debitissue','creditreceived','debitreceived',
			'arpayment_rev','arpayment_issued'
			));

		} catch (\Exception $e) {
			if ($e->getMessage() == 'validation_error') {
				$msg ="Invalid request";
			} else if ($e->getMessage() == 'FY_not_found') {
				$msg ="Error occured while opening Financial Year, Invalid FinancialYear selected";
			}  else {
				$msg ="Error occured while opening dialog";
			}
			
			Log::error("Error @ ".$e->getLine()." file ".$e->getFile().
			  ":".$e->getMessage());
			$data = view('layouts.dialog',compact('msg'));
		}

		return $data;
	}


	public function inventoryCosts(Request $request) {
	    $month = $request->input('month');

        $userData = new UserData();
        $buyerMerchantId = $userData->company_id();

        $query = inventorycost::selectRaw(
			"inventorycost.id as inventory_cost_id,
			DATE_FORMAT(doc_date, '%d%b%y') as dated ,doc_no"
		);

        $query->orderBy('doc_date', 'desc');

        $query->where('inventorycost.buyer_merchant_id', $buyerMerchantId)->
			whereMonth('inventorycost.created_at',$month+1);

        $totalRecords = $query->get()->count();

        // applying limit
        $inventoryCostDetails = $query->
			skip($request->input('start'))->
			take($request->input('length'))->
			get();

        $counter = 0 + $request->input('start');

        foreach ($inventoryCostDetails as $key => $inventoryCost) {
            $inventoryCostDetails[$key]['indexNumber'] = ++$counter;
        }

        $response = [
            'data' => $inventoryCostDetails,
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalRecords
        ];
        return response()->json($response);
    }


	function showPigeonView() {
		return view('virtualcabinet.pigeon');
	}


	function showVCRackView()
	{
		 $id = Auth::user()->id;
        $user_roles = usersrole::where('user_id',$id)->get();

        $is_king =  \App\Models\Company::where('owner_user_id',Auth::user()->id)->first();

        if ($is_king != null) {
            $is_king = true;
        } else {
            $is_king  = false;
        }
		return view('virtualcabinet.manual_rack',compact('user_roles','is_king'));
	}


	function showVCRackFileView()
	{
		 $id = Auth::user()->id;
        $user_roles = usersrole::where('user_id',$id)->get();

        $is_king =  \App\Models\Company::where('owner_user_id',Auth::user()->id)->first();

        if ($is_king != null) {
            $is_king = true;
        } else {
            $is_king  = false;
        }
		return view('virtualcabinet.manual_rack_file',compact('user_roles','is_king'));
	}


	function terminal_model(Request $request){
		try {
			$date = $request->date;
			$month = $request->month;
			
			return view('virtualcabinet.opossum_terminal_list',compact('date','month'));

		} catch (\Exception $e) {
			Log::error(
				"Error @ " . $e->getLine() . " file " . $e->getFile().
				":" . $e->getMessage()
			);
        }
	}


	function tracking_report_model(Request $request){
		try {
			$date = $request->date;
			$month = $request->month;

			return view('virtualcabinet.opossum_terminal_list',compact('date','month'));

		} catch (\Exception $e) {
			Log::error(
				"Error @ " . $e->getLine() . " file " . $e->getFile().
				":" . $e->getMessage()
			);
        }
	}


	function tracking_report_model_data(Request $request)
    {
        try {

            $this->user_data = new UserData();
            $model           = new StockReport();

            $data = $model->whereMonth('created_at',$request->month+1)->orderBy('created_at', 'asc')->latest()->get();

            return Datatables::of($data)
                ->addIndexColumn()

                ->addColumn('terminal_id', function ($location) {

                    return '<p class="os-linkcolor loyaltyOutput" data-field="terminal_id" style="cursor: pointer; margin: 0; text-align: center;" data-target="#branch" data-toggle="modal">aaa</p>';
                })
                ->escapeColumns([])
                ->make(true);

        } catch (\Exception $e) {

            Log::error(
                "Error @ " . $e->getLine() . " file " . $e->getFile() . ":" . $e->getMessage()
            );
        }
    }


	function terminal_model_data(Request $request) {
		try {

			$this->user_data = new UserData();
			$model           = new locationterminal();
        	  	
			$ids  = merchantlocation::join('location','location.id','=',
				'merchantlocation.location_id')->
				where('merchantlocation.merchant_id',
				$this->user_data->company_id())->
				whereNull('location.deleted_at')->
				pluck('merchantlocation.location_id');
			
			
			$franchiseLocations = DB::table('franchisemerchant')->
				join('franchisemerchantloc','franchisemerchantloc.franchisemerchant_id','=','franchisemerchant.id')->
				join('franchisemerchantlocterm','franchisemerchantlocterm.franchisemerchantloc_id',
					'=','franchisemerchantloc.id')->
				where([
					'franchisemerchant.franchisee_merchant_id' => $this->user_data->company_id()
				])->
				pluck('franchisemerchantlocterm.terminal_id');

			$franchise_terminal_own = FranchiseMerchantLocTerm::select('franchisemerchantlocterm.terminal_id')->
				join('franchisemerchantloc', 'franchisemerchantloc.id', '=',
				'franchisemerchantlocterm.franchisemerchantloc_id')->
				join('franchisemerchant','franchisemerchant.id','=',
				'franchisemerchantloc.franchisemerchant_id')->	
				join('franchise','franchise.id','=', 'franchisemerchant.franchise_id')->
				join('company', 'franchise.owner_merchant_id', '=','company.id')->			
				where(
				//	['franchisemerchantlocterm.terminal_id' => $terminal->id],
					["franchise.owner_merchant_id" => $this->user_data->company_id()]
				)->
				pluck('franchisemerchantlocterm.terminal_id');

			$foodcourt = DB::table('foodcourtmerchantterminal')->
				join('foodcourtmerchant','foodcourtmerchant.id','=','foodcourtmerchantterminal.foodcourtmerchant_id')->
				where('foodcourtmerchant.tenant_merchant_id', $this->user_data->company_id())->
				whereNull('foodcourtmerchantterminal.deleted_at')->
				whereNull('foodcourtmerchant.deleted_at')->
				pluck('foodcourtmerchantterminal.terminal_id');
		
			$foodcourt_exclude = DB::table('foodcourtmerchantterminal')->
				join('foodcourtmerchant','foodcourtmerchant.id','=','foodcourtmerchantterminal.foodcourtmerchant_id')->
				whereNull('foodcourtmerchantterminal.deleted_at')->
				whereNull('foodcourtmerchant.deleted_at')->
				pluck('foodcourtmerchantterminal.terminal_id');



			$dlt = $model->whereIn('location_id', $ids)->get('terminal_id');
			$dltrec =terminal::whereIn('id',$dlt)->pluck('id');

			$dltrec = $dltrec->merge($franchiseLocations);
			$dltrec = $dltrec->diff($foodcourt_exclude);
			$dltrec = $dltrec->merge($foodcourt);
			$dltrec = $dltrec->diff($franchise_terminal_own);

			$oprece=opos_receipt::whereIn('terminal_id',$dltrec)->
				whereMonth('created_at',$request->month+1)->
				get("terminal_id");

			Log::debug('oprece'.json_encode($oprece));

			$data = $model->whereIn('terminal_id', $oprece)->
				orderBy('created_at', 'asc')->
				latest()->get();
				
			return Datatables::of($data)
            ->addIndexColumn()
            ->addColumn('loc_id', function ($location) {

                $location_systemid = location::find($location->location_id)->systemid;
                return '<p data-field="loc_id" style="cursor: pointer; margin: 0; text-align: center;">' . $location_systemid . '</p>';
            })
            ->addColumn('name', function ($location) {
                $terminal     = location::find($location->location_id)->branch;
                $locationname = empty($terminal) ? "Branch" : $terminal;
                return '<p class="os- linkcolor loyaltyOutput" data-field="branch" style=" margin: 0;" data-target="#branch" data-toggle="modal">' . $locationname . '</p>';
            })
            ->addColumn('terminal_id', function ($location) {
                $terminal          = terminal::find($location->terminal_id);
                $terminalrec	=		terminal::find($location->terminal_id)->terminalreceipt;
                
                $terminal_systemid = empty($terminal->systemid) ? "Terminal ID" : $terminal->systemid;
                return '<p class="os-linkcolor loyaltyOutput" data-field="terminal_id" style="cursor: pointer; margin: 0; text-align: center;" data-target="#branch" data-toggle="modal">' . $terminal_systemid . '</p>';
            })
            ->escapeColumns([])
            ->make(true);

		} catch (\Exception $e) {

			Log::error(
				"Error @ " . $e->getLine() . " file " .
					$e->getFile() . ":" . $e->getMessage()
			);
        }
	}
}
