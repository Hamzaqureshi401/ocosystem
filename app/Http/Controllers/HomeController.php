<?php

namespace App\Http\Controllers;

use Log;
use Illuminate\Http\Request;
use App\Models\Merchant;
use Auth;
use App\Models\Company;
use App\Models\Twoway;
use App\Models\Oneway;
use App\Models\Merchant_Relation;
use App\Http\Controllers\ApiMessageController;
use App\Http\Controllers\IndustryOilGasController;
use \App\Classes\UserData;
use DB;
class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index() {
        return view('home');
    }

     /**
     * Shows tabs in response to an ajax 
     * request rendering the html.
     *
     * @return \Illuminate\Http\Response
     */                    
    public function showUserView() {
        return view('user.user');
    }

    public function showClientView() {
        return view('client.client');
    }

    public function showIndividualView() {
        return view('individual.individual');
    }

    public function showCompanyView() {
        return view('company.company');
    }

    public function showCRMView() {
        return view('crm.crm');
    }

    public function showEVChargerView() {
        return view('evcharger.evcharger');
    }

    public function showSettingView() {
		return view('settings.settings');
    }

    public function showProductView() {
		return view('product.product');
    }

    public function showLogisticsView() {
	   return view('logistics.logistics');
    }

    public function showLocationView(){
		return view('location.location');
    }

    public function showopoSsumView(){
        return view('production.production');
    }

    public function showDataManagementView() {
        return view('data.data');
    }

    public function showCashView() {
        return view('analytics.cash');
    }

    public function showStockLevelView(){
        return view('analytics.stock');
    }
 
    public function showCreditView() {
        return view('analytics.credit');
    }
 
    public function showOperatorView() {
        return view('analytics.operator');
    }
 
    public function showVCManualView() {
		Log::debug('***** showVCManualView() *****');
        // return view('pigeon.pigeon');
        return view('virtualcabinet.manual_template');
    }
    
    public function showAgeingView() {
		Log::debug('***** showAgeingView() *****');
        return view('report.ageing');
    }

    public function HeaderFooter() {
		$user_data = new UserData();
		
		$data = DB::table('merchantglobal')->
			where('merchant_id', $user_data->company_id())->
			first();
        return view('settings.HeaderFooter', compact('data'));
    }

    public function showFunctionView() {
        return view('settings.function');
    }

    public function showCompanyReportView() {
        return view('company_report.company_report');
    }
    public function showOilGasView() {

        return view('industry.oil_gas.og_oilgas');
    }
    public function FuelPrice() {
        
        return view('industry.oil_gas.og_fuelprice');
    }
    public function showTankMonitoring() {

		$industryOilGasController = new IndustryOilGasController();
		$color_guide = $industryOilGasController->color_guide();

        return view('industry.oil_gas.og_tankmonitoring', compact('color_guide'));
    }
	public function showTankManagement(Request $request) {

		if (!empty($request->t_id)) {
			$user_data = new UserData();
			$location = DB::table('location')->
				join('opos_locationterminal', 'opos_locationterminal.location_id','=','location.id')->
				join('opos_terminal', 'opos_terminal.id','=','opos_locationterminal.terminal_id')->
				join('merchantlocation', 'merchantlocation.location_id','=', 'location.id')->
				join('company','company.id','=','merchantlocation.merchant_id')->
				where('opos_terminal.systemid', $request->t_id)->
				select("location.*","merchantlocation.merchant_id", 'company.name as c_name', 'company.systemid as c_systemid')->
				first();
		
			$is_franchise = $location->merchant_id == $user_data->company_id();
		
		} else {
			$is_franchise = false;
			$location = null;
		}

		$industryOilGasController = new IndustryOilGasController();
		$color_guide = $industryOilGasController->color_guide();
	
        return view('industry.oil_gas.og_tankmanagement', compact('location', 'is_franchise', 'color_guide'));
    }
  
    public function showWarehouseView() {
        return view('warehouse.warehouse-tabs');
    }

    public function showWarehouseallocatedproductView() {
        return view('warehouse.allocatedproduct');
    }

    public function showWarehouseqtyproductView() {
        return view('warehouse.qtyproduct');
    }

    public function showVideoMonster() {
        return redirect('http://videomonster');
    }

    public function updateSupplierStatus(Request $request)
    {
        $status = data_get($request,'val','');
        $merchant_id = data_get($request,'id','');
        $res = TwoWay::where('id',$merchant_id)->update(['supplier_status' => $status]);
        if($res){
            return (new APIMessageController())->successResponse($res, 'Supplier Status has been updated!');     
        }else{
            return (new APIMessageController())->failedresponse('Sorry! There is something wrong.');
        }
    }

    public function updateDealerStatus(Request $request)
    {
        $status = data_get($request,'val','');
        $merchant_id = data_get($request,'id','');
        $res = TwoWay::where('id',$merchant_id)->update(['dealer_status' => $status]);
        if($res){
            return (new APIMessageController())->successResponse($res, 'Dealer Status has been updated!');     
        }else{
            return (new APIMessageController())->failedresponse('Sorry! There is something wrong.');
        }
    }

    public function addNewDealer(Request $request)
    {   
        $dealer_id = data_get($request,'merchant_id','');
        $supplier_id = Auth::user()->id;
        $check_in_db = Company::where('owner_user_id',$dealer_id)->first();
        if($supplier_id != $dealer_id)
        {
            if($check_in_db){
            $twoway_save = TwoWay::create([
                'initiator_user_id' => $supplier_id,
                'responder_user_id' => $dealer_id,
                'supplier_status' => 'pending',
                'dealer_status' => 'pending'
            ]);
            if($twoway_save){
                $self_merchant_id = Company::leftJoin('merchant','company.id','=','merchant.company_id')->
				where('owner_user_id',$supplier_id)->
				pluck('merchant.id')->first();

                $partner_merchant_id = Company::leftJoin('merchant','company.id','=','merchant.company_id')->
				where('owner_user_id',$dealer_id)->
				pluck('merchant.id')->first();

                $partner_oneway_id = OneWay::where('owner_user_id',$supplier_id)->
				pluck('id')->first();

                if(!$partner_oneway_id) { $partner_oneway_id = 1; }

                $relation_save = Merchant_Relation::create([
                    'self_merchant_id' => $self_merchant_id,
                    'twoway_id' => $twoway_save->id,
                    'partner_merchant_id' => $partner_merchant_id,
                    'partner_oneway_id' => $partner_oneway_id,
                    'is_dealer' => true
                ]);
                return (new APIMessageController())->successResponse($relation_save, 'Your Request has been sent to your dealer!');
            }

            }else{
                return (new ApiMessageController())->failedresponse('Sorry! This merchant does not exist');
            }    
        }else{
            return (new ApiMessageController())->failedresponse('Sorry! A merchant cannot add himself');
        }
        
    }
}
