<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Classes\UserData;
use App\Classes\SystemID;
use App\Models\CPCRForm;
use App\Models\CPCRManagement;
use App\Models\CPCRApproval;
use App\Models\usersrole;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Matrix\Exception;

use PDF;

class CPCRController extends Controller
{
    protected $user_data;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('CheckRole:prod');
    }

    public function index()
	{
		$user_data = new UserData();
		$data = CPCRManagement::where('merchant_id', $user_data->company_id())->
			get()->
			toArray();
		
		return Datatables::of($data)
			->addIndexColumn()
			->addColumn('cpcr_id', function ($cpcrList) {
				return '<p class="os-linkcolor" data-field="cpcr_id" style="cursor: pointer; margin: 0; text-align: center;">
				<a class="os-linkcolor" href="/crane-preventive-check-reports/'.$cpcrList['id'] .'" target="_blank" style="text-decoration: none;">' 
				.$cpcrList['systemid'] . 
				'</p>';
			})
			->addColumn('cpcr_name', function ($cpcrList) {
				return  '<p class="os-linkcolor descriptionOutput" data-field="cpcr_name" style="margin: auto;display:inline-block; cursor: pointer;">
				' . $cpcrList['name'] .'</p>';
			})
			->addColumn('cpcr_technician', function ($cpcrList) {
				$cpcr = \App\User::find($cpcrList['technician'])->name ?? 'Technician';
				return '<p class="os-linkcolor mechanicOutput" data-field="cpcr_technician" style="cursor: pointer; margin: auto;text-align: left;">
				'.ucfirst($cpcr).
				'</p>';
			})

			->addColumn('cpcr_date', function ($cpcrList) {

				return '<p data-field="cpcr_date" disabled="disabled" style="margin: auto;" >
				'. \Carbon\Carbon::parse($cpcrList['created_at'])->format('dMy') .
				'</p>';

			})
			->addColumn('cpcr_status', function ($cpcrList) {

				return '<p   data-field="cpcr_status" style=" margin: auto;">'.ucfirst($cpcrList['status']).'</p>';

			})
			->addColumn('cpcr_amount', function ($cpcrList) {

				return '<p data-field="cpcr_amount" style="margin: auto; text-align: right">
				'.'0.00'.
				'</p>';

			})
			->addColumn('deleted', function ($cpcrList) {
				return '<p data-field="deleted"
					style="background-color:red;
					border-radius:5px;margin:auto;
					width:25px;height:25px;
					display:block;cursor: pointer;"
					class="text-danger remove">
					<img src="/images/redcrab_25x25.png"></p>';

			})
			->escapeColumns([])
			->make(true);
	}


    public function store(Request $request)
    {
        //Create a new CSR
        try {
            $this->user_data = new UserData();
            $SystemID        = new SystemID('csr');
            $cpcr         = new CPCRManagement();

            // Save CSR
            $cpcr->merchant_id   = $this->user_data->company_id();
            $cpcr->systemid = $SystemID;
            $cpcr->name = "Crane Preventive Check Report";
            $cpcr->technician = "Technician";
            $cpcr->save();

            // Create CSR Form
            CPCRForm::create(["cpcrmgmt_id" => $cpcr->id]);


            $msg = "Crane Preventive Check Report added successfully";
            return response()->json([
								'status' 	=> 'success',
								'message' 	=> $msg,
							]);

        } catch (\Exception $e) {
            $msg = "Some error occured";
            return response()->json([
								'status' 	=> 'success',
								'message' 	=> $msg,
							]);
        }

    }

    public function showEditModal(Request $request)
    {
        try {
            $allInputs = $request->all();
            $id        = $request->get('id');
            $fieldName = $request->get('field_name');

            $validation = Validator::make($allInputs, [
                'id'         => 'required',
                'field_name' => 'required',
            ]);

            if ($validation->fails()) {
                $response = (new ApiMessageController())->
                validatemessage($validation->errors()->first());

            } else {

                $cpcr = CPCRManagement::find($id);

                return view('cpcr.cpcr-modals', compact(['id', 'fieldName', 'cpcr']));
            }

        } catch (\Illuminate\Database\QueryException $ex) {

            $msg = "Some error occured";
            return response()->json([
								'status' 	=> 'success',
								'message' 	=> $msg,
							]);
        }
    }

    public function update(Request $request)
    {
       try {

            $cpcr_id       = $request->get('id');
            $changed = false;
            $msg = '';

             $cpcr     = CPCRManagement::find($cpcr_id);
             log::debug('CSR'.json_encode($cpcr));

            if (empty($cpcr)) {
                throw new Exception("cpcr_not_found", 1);
            }

            if ($request->has('name')) {
					if($request->name != $cpcr->name){
						$cpcr->name = $request->name;
						$changed = true;
						$msg = "Name updated successfully";
					}
            }
            if ($request->has('technician')) {   
				if($request->technician != $cpcr->technician){
					$cpcr->technician = $request->technician;
					$changed = true;
					$msg = "Technician updated successfully";
				}                  
            }


            if ($changed == true) {
                $cpcr->save();
                log::debug('Saved_CPCR'.json_encode($cpcr));
				$response =  response()->json([
								'status' 	=> 'success',
								'message' 	=> $msg,
							]);
            } else {
            	if(!empty($msg)) {
                $response = response()->json([
								'status' 	=> 'success',
								'message' 	=> $msg,
							]);
            	} else {
				$response = response()->json([
								'status' 	=> 'nothing',
								'message' 	=> $msg,
							]);
            	}
            }

        }  catch (\Exception $e) {
            if ($e->getMessage() == 'cpcr_not_found') {
                $msg = "Crane Preventive Check Report not found";
            }  else {
                $msg = "Some error occured";
            }

            // $msg = $e;
            $response = response()->json([
								'status' 	=> 'success',
								'message' 	=> $msg,
						]);

            Log::error(
                "Error @ " . $e->getLine() . " file " . $e->getFile() .
                ":" . $e->getMessage()
            );
        }

        return $response;

    }

    public function destroy($id)
    {

        try {

            CPCRManagement::destroy($id);
            $msg = "Crane Preventive Check Report deleted successfully";

            return response()->json([
								'status' 	=> 'success',
								'message' 	=> $msg,
							]);
        } catch (\Illuminate\Database\QueryException $ex) {
            $msg = "Some error occured, Could not delete Crane Preventive Check Report";

            return response()->json([
								'status' 	=> 'success',
								'message' 	=> $msg,
							]);
        }
    }

	public function show_cpcr() 
	{
		return view('cpcr.cpcr');
	}

	public function cpcr_form($cpcr_id) 
	{
        try{

            	$id = Auth::user()->id;
	            $user_data = new UserData();
	            $user_data->exit_merchant();

	            $user_roles = usersrole::where('user_id',$id)->get();

	            $is_king =  Company::where('owner_user_id',Auth::user()->id)->first();

                if ($is_king != null) {
                    $is_king = true;
                } else {
                    $is_king  = false;
                }

                if (!$user_data->company_id()) {
                    abort(404);
                }


            $cpcr     = CPCRManagement::find($cpcr_id);
            $cpcrForm =  CPCRForm::where("cpcrmgmt_id",$cpcr_id)->first();

            if (!$cpcrForm){
                return "<h1>CSR ID not found in CSR Form, Please create new CSR and try again </h1>";
            }


            // All approvals
            $allAprovals = CPCRApproval::with('user.staff')
            ->where("cpcrform_id",$cpcrForm->id)
            ->get()
            ->toArray();

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



            $travels = $cpcrForm->travels;
            $services = collect( array_map(function($value) {
                return $value['service'];
            },  $cpcrForm->services ? $cpcrForm->services->toArray() : []));

        return view('cpcr.cpcr_form', compact('cpcr',
			'cpcr_id', 'cpcrForm', 'travels', 'services',
			'user_roles','is_king','allAprovals',
            'userApprovals','customer_service', 'store',
			'maintenance_dept3','customer','maintenance_dept5',
			'finance_dept'));

        } catch (\Exception $e) {
            Log::error($e);
            $msg = "Some error occured";
            return response()->json([
								'status' 	=> 'success',
								'message' 	=> $msg,
							]);
        }
	}
	public function cpcr_pdf($cpcr_id)
	{
        try{

            	$id = Auth::user()->id;
	            $user_data = new UserData();
	            $user_data->exit_merchant();

	            $user_roles = usersrole::where('user_id',$id)->get();

	            $is_king =  Company::where('owner_user_id',Auth::user()->id)->first();

//                if ($is_king != null) {
//                    $is_king = true;
//                } else {
//                    $is_king  = false;
//                }

                if (!$user_data->company_id()) {
                    abort(404);
                }


            $cpcr     = CPCRManagement::find($cpcr_id);
            $cpcrForm =  CPCRForm::where("cpcrmgmt_id",$cpcr_id)->first();

            if (!$cpcrForm){
                return "<h1>CSR ID not found in CSR Form, Please create new CSR and try again </h1>";
            }


            // All approvals
            $allAprovals = CPCRApproval::with('user.staff')
            ->where("cpcrform_id",$cpcrForm->id)
            ->get()
            ->toArray();

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



            $travels = $cpcrForm->travels;
            $services = collect( array_map(function($value) {
                return $value['service'];
            },  $cpcrForm->services ? $cpcrForm->services->toArray() : []));

            $pdf = PDF::setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true])->loadView('cpcr.cpcr_pdf', compact('cpcr',
                'cpcr_id', 'cpcrForm', 'travels', 'services',
                'user_roles','is_king','allAprovals',
                'userApprovals','customer_service', 'store',
                'maintenance_dept3','customer','maintenance_dept5',
                'finance_dept'));

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
            $pdf->setPaper('A4', '');
            //return $pdf->stream();
            return $pdf->download('cpcr.pdf');


        } catch (\Exception $e) {
            Log::error($e);
            $msg = "Some error occured";
            return response()->json([
								'status' 	=> 'success',
								'message' 	=> $msg,
							]);
        }
	}

    public function saveForm(Request $request)
    {
        try {
            $this->user_data = new UserData();
            global $cpcrForm;
            $cpcrForm =  CPCRForm::where("cpcrmgmt_id",$request->cpcr_id)->first();
            $data = $request->input();
            //$data['stations'] = json_decode($request->stations);

            if (!$cpcrForm){
                   $msg = "Crane Service Report Form not found or has been deleted";
                    return response()->json([
								'status' 	=> 'success',
								'message' 	=> $msg,
							]);
            }

            // Extract the needed data for CSR form
             $cpcrFormData = array_filter($data, function($k) {
                return !($k == 'id' || $k == '_token' ||  $k == 'cpcr_id' ||  $k == 'travel_to');
             }, ARRAY_FILTER_USE_KEY);

			 $changed = false;
             $current_form = CPCRForm::where("cpcrmgmt_id",$request->cpcr_id)->first();
		//	dd($current_form);
			 if(
			 $current_form->region != $cpcrFormData['region'] ||
			 $current_form->company != $cpcrFormData['company'] ||
			 $current_form->crane_model != $cpcrFormData['crane_model'] ||
			 $current_form->serial_no != $cpcrFormData['serial_no'] ||
			 $current_form->job_site != $cpcrFormData['job_site'] ||
			 $current_form->job_length != $cpcrFormData['job_length'] ||
			 $current_form->hook_height != $cpcrFormData['hook_height'] ||
			 $current_form->falls != $cpcrFormData['falls'] ||
			 $current_form->foundation_a_checks != $cpcrFormData['foundation_a_checks'] ||
			 $current_form->foundation_a_grease != $cpcrFormData['foundation_a_grease'] ||
			 $current_form->hoisting_system_a_checks != $cpcrFormData['hoisting_system_a_checks'] ||
			 $current_form->hoisting_system_b_checks != $cpcrFormData['hoisting_system_b_checks'] ||
			 $current_form->hoisting_system_c_checks != $cpcrFormData['hoisting_system_c_checks'] ||
			 $current_form->hoisting_system_d_checks != $cpcrFormData['hoisting_system_d_checks'] ||
			 $current_form->hoisting_system_e_checks != $cpcrFormData['hoisting_system_e_checks'] ||
			 $current_form->hoisting_system_f_checks != $cpcrFormData['hoisting_system_f_checks'] ||
			 $current_form->hoisting_system_a_grease != $cpcrFormData['hoisting_system_a_grease'] ||
			 $current_form->hoisting_system_b_grease != $cpcrFormData['hoisting_system_b_grease'] ||
			 $current_form->hoisting_system_c_grease != $cpcrFormData['hoisting_system_c_grease'] ||
			 $current_form->hoisting_system_d_grease != $cpcrFormData['hoisting_system_d_grease'] ||
			 $current_form->hoisting_system_e_grease != $cpcrFormData['hoisting_system_e_grease'] ||
			 $current_form->hoisting_system_f_grease != $cpcrFormData['hoisting_system_f_grease'] ||
			 $current_form->slewing_system_a_checks != $cpcrFormData['slewing_system_a_checks'] ||
			 $current_form->slewing_system_b_checks != $cpcrFormData['slewing_system_b_checks'] ||
			 $current_form->slewing_system_c_checks != $cpcrFormData['slewing_system_c_checks'] ||
			 $current_form->slewing_system_d_checks != $cpcrFormData['slewing_system_d_checks'] ||
			 $current_form->slewing_system_a_grease != $cpcrFormData['slewing_system_a_grease'] ||
			 $current_form->slewing_system_b_grease != $cpcrFormData['slewing_system_b_grease'] ||
			 $current_form->slewing_system_c_grease != $cpcrFormData['slewing_system_c_grease'] ||
			 $current_form->slewing_system_d_grease != $cpcrFormData['slewing_system_d_grease'] ||
			 $current_form->trolley_luffing_system_check != $cpcrFormData['trolley_luffing_system_check'] ||
			 $current_form->trolley_luffing_system_a_checks != $cpcrFormData['trolley_luffing_system_a_checks'] ||
			 $current_form->trolley_luffing_system_b_checks != $cpcrFormData['trolley_luffing_system_b_checks'] ||
			 $current_form->trolley_luffing_system_c_checks != $cpcrFormData['trolley_luffing_system_c_checks'] ||
			 $current_form->trolley_luffing_system_d_checks != $cpcrFormData['trolley_luffing_system_d_checks'] ||
			 $current_form->trolley_luffing_system_e_checks != $cpcrFormData['trolley_luffing_system_e_checks'] ||
			 $current_form->trolley_luffing_system_f_checks != $cpcrFormData['trolley_luffing_system_f_checks'] ||
			 $current_form->trolley_luffing_system_a_grease != $cpcrFormData['trolley_luffing_system_a_grease'] ||
			 $current_form->trolley_luffing_system_b_grease != $cpcrFormData['trolley_luffing_system_b_grease'] ||
			 $current_form->trolley_luffing_system_c_grease != $cpcrFormData['trolley_luffing_system_c_grease'] ||
			 $current_form->trolley_luffing_system_d_grease != $cpcrFormData['trolley_luffing_system_d_grease'] ||
			 $current_form->trolley_luffing_system_e_grease != $cpcrFormData['trolley_luffing_system_e_grease'] ||
			 $current_form->trolley_luffing_system_f_grease != $cpcrFormData['trolley_luffing_system_f_grease'] ||
			 $current_form->electrical_system_a_checks != $cpcrFormData['electrical_system_a_checks'] ||
			 $current_form->electrical_system_b_checks != $cpcrFormData['electrical_system_b_checks'] ||
			 $current_form->electrical_system_c_checks != $cpcrFormData['electrical_system_c_checks'] ||
			 $current_form->electrical_system_d_checks != $cpcrFormData['electrical_system_d_checks'] ||
			 $current_form->electrical_system_e_checks != $cpcrFormData['electrical_system_e_checks'] ||
			 $current_form->electrical_system_f_checks != $cpcrFormData['electrical_system_f_checks'] ||
			 $current_form->electrical_system_a_grease != $cpcrFormData['electrical_system_a_grease'] ||
			 $current_form->electrical_system_b_grease != $cpcrFormData['electrical_system_b_grease'] ||
			 $current_form->electrical_system_c_grease != $cpcrFormData['electrical_system_c_grease'] ||
			 $current_form->electrical_system_d_grease != $cpcrFormData['electrical_system_d_grease'] ||
			 $current_form->electrical_system_e_grease != $cpcrFormData['electrical_system_e_grease'] ||
			 $current_form->electrical_system_f_grease != $cpcrFormData['electrical_system_f_grease'] ||
			 $current_form->overall_condition_mentioned != $cpcrFormData['overall_condition_mentioned'] ||
			 $current_form->comments != $cpcrFormData['comments']
			 ){
				 $changed = true;
			 }
		//	 dd($changed);
             CPCRForm::where("cpcrmgmt_id",$request->cpcr_id)
                 ->update($cpcrFormData);


		  if($changed){
			  $msg =  "Crane Preventive Check Report Form saved successfully";
			  return response()->json([
									'status' 	=> 'success',
									'message' 	=> $msg,
								]);
		  } else {
			  $msg =  "";
			 return response()->json([
									'status' 	=> 'nochange',
									'message' 	=> $msg,
								]);
		  }



        } catch (\Exception $e) {
              Log::error(
                "Error @ " . $e->getLine() . " file " . $e->getFile() .
                ":" . $e->getMessage()
            );
            $msg = "Some error occured";
            return response()->json([
								'status' 	=> 'success',
								'message' 	=> $msg,
							]);
        }

    }

     public function saveApprovals(Request $request)
    {
        try {
            $userId = Auth::user()->id;

            $cpcrForm =  CPCRForm::find($request->cpcrform_id);
            $approvalData =  $request->input('userApprovals');

            if (!$cpcrForm){
                   $msg = "Crane Preventive Check Report Form not found";
                    return response()->json([
								'status' 	=> 'success',
								'message' 	=> $msg,
							]);
            }


             $oldUserApprovals = CPCRApproval::where("cpcrform_id",$cpcrForm->id)
                 ->where("approver_user_id", $userId)
                 ->pluck("id");

             // Create station services
			if($approvalData){
				$cpcrForm->approvals()->createMany($approvalData);
			}
            //   Delete old services
            CPCRApproval::destroy($oldUserApprovals);



          $msg = "Approval saved successfully";
          return response()->json([
								'status' 	=> 'success',
								'message' 	=> $msg,
							]);



        } catch (\Exception $e) {
            return $e;
            $msg = "Some error occured, could not save approvals";
            return response()->json([
								'status' 	=> 'success',
								'message' 	=> $msg,
							]);
        }

    }
}
