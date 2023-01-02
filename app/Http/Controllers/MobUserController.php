<?php

namespace App\Http\Controllers;

use App\User;
use Milon\Barcode\DNS1D;
use Milon\Barcode\DNS2D;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Models\usersrole;
use App\Models\Company;
use \App\Models\CMRForm;
use App\Classes\SystemID;
use App\Classes\UserData;
use \App\Models\CMRManagement;
use App\Models\CMRPartsUsed;
use App\Models\CMRTravelTo;
use App\Models\CMRApproval;
use App\Models\CMRFormServices;
use App\Models\merchantproduct;
use App\Models\prd_inventory;
use App\Models\product;
use App\Models\terminal;

class MobUserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function show($id)
    {
        $user = User::find($id);
        $num = $user->staff->systemid;
        $username = $user->name;
        $code = DNS1D::getBarcodePNG($num, "C39+", 3, 33);
        // $qr = DNS2D::getBarcodeSVG($num, 'DATAMATRIX', 10, 10);
        // $qrcode = DNS2D::getBarcodeHTML($num, "QRCODE");
        
        $img = '<img style = "
            width: 93%;
            height: 65px;
            margin-top: 23px;" 
            src="data:image/png;base64, ' 
            . $code . '" alt="barcode" />';
        

        return view('platypos.user.users', compact('username', 'num'))->with('barcode', $img);
    }

    public function personal(User $user) {
        $num = $user->staff->systemid;
        $username = $user->name;
        $qrcode = DNS2D::getBarcodePNG($num, "QRCODE");
        $barcode = DNS1D::getBarcodePNG($num, "C128");
        return view('mob_personal.personal', compact('username', 'num'))->with([
            'barcode' =>  $barcode,
            'qrcode' =>  $qrcode,
        ]);
    }

    public function scanner() {
        return view('mob_personal.scanner');
    }

    public function repair_and_maintenance()
    {
        return view('mob_repairmaintenance.repair_maintenance');
    }

    public function repair_and_maintenance_item(Request $request) 
    {
        if($request->filter){
            $this->user_data = new UserData();
            $cmr  = CMRManagement::with('cmrform')->
            where('merchant_id', $this->user_data->company_id())->get();

            $filter = $request->filter;

            $cmrs = $cmr->filter(function($cmr) use ($filter) {
                        return stristr($cmr->name, $filter) ||
                           stristr($cmr->systemid, $filter) ||
                           stristr($cmr->cmrform->location_address, $filter);
                    })->all();

            return view('mob_repairmaintenance.repair_maint_item', compact(['cmrs']));
        }

        if($request->status){
            $this->user_data = new UserData();
            $cmrs  = CMRManagement::with('cmrform')->
            where('merchant_id', $this->user_data->company_id())->
            where('status', $request->status)->
            orderBy('created_at', 'desc')->
            get();
            
            return view('mob_repairmaintenance.repair_maint_item', compact(['cmrs']));
        }

        $this->user_data = new UserData();
        $cmrs  = CMRManagement::with('cmrform')->
        where('merchant_id', $this->user_data->company_id())->
        orderBy('created_at', 'desc')->
        get();

        return view('mob_repairmaintenance.repair_maint_item', compact(['cmrs']));
    }

    public function repair_and_maintenance_form($cmr_id)
    {
        try{
            $id = Auth::user()->id;
            $user_data = new UserData();
            $user_data->exit_merchant();

            $user_roles = usersrole::where('user_id',$id)->get();

            $is_king =  Company::where('owner_user_id',
                Auth::user()->id)->first();

            if ($is_king != null) {
                $is_king = true;

            } else {
                $is_king  = false;
            }

            if (!$user_data->company_id()) {
                abort(404);
            }

            $cmr = CMRManagement::find($cmr_id);

            $cmrForm =  CMRForm::with('parts.inventory.product_name')->where("cmrmgmt_id", $cmr_id)->
                first();

            if (!$cmrForm){
                return "<h1>CMR ID not found in CMR Form, Please create new CMR and try again </h1>";
            }


            // All approvals
            $allAprovals = CMRApproval::with('user.staff')->
                where("cmrform_id",$cmrForm->id)->
                get()->
                toArray();

            // Approvals by current logged in users
            $userApprovalDetails = array_filter($allAprovals, function($value) use ($id) {
                return $value['approver_user_id'] == $id;
             });
             $userApprovals = collect( array_map(function($value) {
                return ['approval_name'=>$value['approval_name'],'approver_user_id'=>$value['approver_user_id'] ];
            },  $userApprovalDetails));

             $customer_service = array_search('1customer_service',array_column($allAprovals,'approval_name'));
             $store = array_search('2store',array_column($allAprovals,'approval_name'));
             $maintenance_dept3 = array_search('3maintenance_dept',array_column($allAprovals,'approval_name'));
             $customer = array_search('4customer',array_column($allAprovals,'approval_name'));
             $maintenance_dept5 = array_search('5maintenance_dept',array_column($allAprovals,'approval_name'));
             $finance_dept = array_search('6finance_dept',array_column($allAprovals,'approval_name'));


            $travels = $cmrForm->travels;
            $services = collect( array_map(function($value) {
                return $value['service'];
            },  $cmrForm->services->toArray()));

            return view('mob_repairmaintenance.repair_maint_form', compact('cmr', 
                'cmr_id', 'cmrForm', 'travels', 'services',
                'user_roles','is_king','allAprovals',
                'userApprovals','customer_service', 'store',
                'maintenance_dept3','customer','maintenance_dept5',
                'finance_dept'));

        } catch (\Exception $e) {
            Log::error($e);
            $msg = "Some error occured";
            return response()->json([
                'status'    => 'success',
                'message'   => $msg,
            ]);
        }
    }

    public function repair_and_maintenance_form_update(Request $request)
    {
        //Create a new CMR
        try {
            $this->user_data = new UserData();
            global $cmrForm;
            $cmrForm =  CMRForm::where("cmrmgmt_id",$request->cmr_id)->first();
            $data = $request->input();
            $data['stations'] = json_decode($request->stations);

            if (!$cmrForm){
                $msg = "Corrective Maintenance Form not found or has been deleted";
                return response()->json([
                    'status'    => 'success',
                    'message'   => $msg,
                ]);
            }

            $services = array_map(function($value) {
                return [ "cmrform_id"=>$GLOBALS['cmrForm']['id'], "service"=>$value ];
            },  $data['stations']);

            // Extract the needed data for CMR form
            $cmrFormData = array_filter($data, function($k) {
                return !($k == 'id' || $k == '_token' ||  $k == 'cmr_id' ||  $k == 'stations' ||  $k == 'travel_to');
            }, ARRAY_FILTER_USE_KEY);

            // Extract the needed data for CMR form
            $cmrFormData = array_filter($data, function($k) {
                return !($k == '_token' ||  $k == 'cmr_id' || $k == 'stations');
            }, ARRAY_FILTER_USE_KEY);

            $changed = false;
            $current_form = CMRForm::where("cmrmgmt_id",$request->cmr_id)->first();
        //  dd($current_form);
             if(
             $current_form->equipment_serialno != $cmrFormData['equipment_serialno'] || 
             $current_form->equipment_modelno != $cmrFormData['equipment_modelno'] ||  
             $current_form->start_time != $cmrFormData['start_time'] || 
             $current_form->sitein_time != $cmrFormData['sitein_time'] ||  
             $current_form->siteout_time != $cmrFormData['siteout_time'] || 
             $current_form->return_time != $cmrFormData['return_time'] ||  
             $current_form->start_mileage != $cmrFormData['start_mileage'] || 
             $current_form->return_mileage != $cmrFormData['return_mileage'] ||
             $current_form->work_performed != $cmrFormData['work_performed'] || 
             $current_form->remarks != $cmrFormData['remarks'] 
             ){
                 $changed = true;
             }
             if($cmrFormData['start_mileage'] && $cmrFormData['return_mileage']){
                $cmrFormData['total_mileage'] = $cmrFormData['return_mileage'] - $cmrFormData['start_mileage'];
             }
        //   dd($changed);
             CMRForm::where("cmrmgmt_id",$request->cmr_id)
                 ->update($cmrFormData);

             $oldServicesIds = CMRFormServices::where("cmrform_id",$cmrForm->id)
                 ->pluck("id");
             // Create station services
        //   dd($services);
                $cmrForm->services()->createMany($services);
                // Delete old services
                CMRFormServices::destroy($oldServicesIds);


            if($changed){
                $msg =  "Corrective Maintenance Form saved successfully";
                return response()->json([
                    'status'    => 'success',
                    'message'   => $msg,
                ]);

            } else {
                $msg =  "";
                return response()->json([
                    'status'    => 'nochange',
                    'message'   => $msg,
                ]);
            }

        } catch (\Exception $e) {
            Log::error(
                "Error @ " . $e->getLine() . " file " . $e->getFile() .
                ":" . $e->getMessage()
            );

            $msg = "Some error occured";
            return response()->json([
                'status'    => 'success',
                'message'   => $msg,
            ]);
        }
    }

    public function repair_and_maintenance_form_add($cmrform_id)
    {
        // Get inventory products
        $this->user_data = new UserData();

        $ids = merchantproduct::where('merchant_id',
            $this->user_data->company_id())->
            pluck('product_id');

        $cmrForm = CMRForm::where("id", $cmrform_id)->first();

        // Inventory price can either be 0 or NULL
        $inventoryIds = prd_inventory::whereIn('product_id',$ids)->
            whereNotNull('price')->
            pluck('product_id');

        $products = product::whereIn('id', $inventoryIds)->
            where('ptype', 'inventory')->
            whereNotNull('name')->
            whereNotNull('photo_1')->
            whereNotNull('prdcategory_id')->
            whereNotNull('prdsubcategory_id')->
            whereNotNull('prdprdcategory_id')->
            latest()->
            get();

        return view('mob_repairmaintenance.repair_maint_add', compact(['products', 'cmrForm', 'cmrform_id']));
    }


    public function repair_and_maintenance_form_add_parts(Request $request)
    {
        try {

            $cmrform = CMRForm::find($request->cmrform_id);

            if (!$cmrform){
                throw new InvalidArgumentException("Form Id does not exist");
            }

            $cmrparts = new CMRPartsUsed();

            $cmrparts->cmrform_id   = $request->cmrform_id;
            $cmrparts->inventory_id = $request->inventory_id;
            $cmrparts->cmr_qty = $request->cmr_qty;
            $cmrparts->save();

            $msg = "Product added successfully";
            return response()->json([
                'status'    => 'success',
                'message'   => $msg,
            ]);

        } catch (\Exception $e) {
            $msg = "Product could not added. An error occurred";
            return response()->json([
                'status'    => 'success',
                'message'   => $msg,
            ]);
        }
    }

    public function repair_and_maintenance_form_add_parts_update(Request $request)
    {
       try {

            $cmrpart_id       = $request->id;
            $changed = false;
            $msg = '';

             $cmrpart     = CMRPartsUsed::find($cmrpart_id);

            if (empty($cmrpart)) {
                throw new Exception("CMR product part not found");
            }

            if ($request->has('part_no')) {
                $cmrpart->cmr_partno = $request->part_no;
                $changed = true;
                $msg = "Part No. updated successfully";
            }

            if ($request->has('cmr_qty')) {
                $cmrpart->cmr_qty = $request->cmr_qty;
                $changed = true;
                $msg = "Qty updated successfully";
            }

            if ($request->has('chargeable')) {
                $cmrpart->cmr_chargeable = $request->chargeable;
                $changed = true;
                $msg = "Chargeable status updated successfully";
            }

            if ($changed == true) {
                $cmrpart->save();
                log::debug('Saved_CMR'.json_encode($cmrpart));
                $response = response()->json([
                    'status'    => 'success',
                    'message'   => $msg,
                ]);

            } else {
                if(!empty($msg)) {
                    $response = response()->json([
                        'status'    => 'success',
                        'message'   => $msg,
                    ]);

                } else {
                    $response= '';
                }
            }

        }  catch (\Exception $e) {
            if ($e->getMessage() == 'cmr_not_found') {
                $msg = "CMR not found";
            }  else {
                $msg = "Some error occured";
            }

            // $msg = $e;
            $response = response()->json([
                'status'    => 'success',
                'message'   => $msg,
            ]);

            Log::error(
                "Error @ " . $e->getLine() . " file " . $e->getFile() .
                ":" . $e->getMessage()
            );
        }

        return $response;
    }


    public function repair_and_maintenance_form_destroy_parts($id)
    {
        try {

            CMRPartsUsed::destroy($id);
            $msg = "Part removed successfully";

            return response()->json([
                'status'    => 'success',
                'message'   => $msg,
            ]);

        } catch (\Illuminate\Database\QueryException $ex) {
            $msg = "An error occured, Could not remove Part";

            return response()->json([
                'status'    => 'success',
                'message'   => $msg,
            ]);
        }
    }

}
