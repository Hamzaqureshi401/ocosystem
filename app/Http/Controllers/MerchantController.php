<?php

namespace App\Http\Controllers;

use App\Models\Merchant;
use App\Models\Company;
use App\Models\Staff;
use App\Models\role;
use App\Models\usersrole;
use App\User;
use Illuminate\Support\Carbon;
use Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Matrix\Exception;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Collection;
use \App\Http\Controllers\OrganisationHierarchyController as OH;
use \App\Classes\SystemID;
use \App\Http\Controllers\ScriptController;

use \App\Classes\UserData;

class MerchantController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('CheckRole:super');
    }


    public function index() {

    	$dataList = Merchant::latest();

		Log::debug('Merchant::latest = '.json_encode($dataList));

		$data = Datatables::of($dataList)->
			addIndexColumn()->
			addColumn('sysid', function ($memberList) {
				$companyy = Company::where('id',
					$memberList->company_id)->
				first();
				$systemid = "";
			if(!is_null($companyy)){
				$systemid = $companyy->systemid;
			}

			return '<p class="os-linkcolor" data-field="mechantid" style="margin: 0;text-align: center;cursor:pointer">'.$systemid.'</p>';
		})->
		addColumn('mname', function ($memberList) {
			$companyy = Company::where('id',
				$memberList->company_id)->
				first();
			$company_name =  '';
			if(!is_null($companyy)){
				$company_name =  $companyy->name;
			}	
			
			$name = empty($company_name) ? "Name":$company_name;
			return "<p class='os-linkcolor mname' data-field='name' data-option='edit' style='margin: 0;cursor:pointer'>$name</p>";
		})->
		addColumn('status', function ($memberList) {
			$companyy = Company::where('id',
				$memberList->company_id)->
				first();
			$company_name =  '';
			$user = null;
			if(!is_null($companyy)){
				$owner_id =  $companyy->owner_user_id;
				$user = User::where('id',$owner_id)->first();
			}	
			
			if (empty($user) or empty($user->email) or empty($user->password)) {
				return '<p data-field="disabled_status" style="cursor:pointer;
					margin:0;color:#ccc !important;
					cursor: not-allowed !important;">' .
					ucfirst($memberList->status) . '</p>';       
			}

			return '<p data-field="status" class="os-linkcolor" style="cursor: pointer;  margin: 0;">' .
			ucfirst($memberList->status) . '</p>';
		})->
        addColumn('approved', function ($memberList) {
			$companyy = Company::where('id',
				$memberList->company_id)->
				first();
			$approved_at =  '';
			if(!is_null($companyy)){
				$approved_at =  $companyy->approved_at;
			}
            $approved_status = empty($approved_at) ? "-":
				Carbon::parse($approved_at)->format('dMy H:i:s');

            return $approved_status;
        })->
        addColumn('pinkcrab', function ($memberList) {
			$cpy = Company::where('id',
				$memberList->company_id)->
				first();

			Log::debug('memberList->company_id='. $memberList->company_id);
			Log::debug('cpy='. json_encode($cpy));

			if (empty($cpy)) {
				$cpy_id = null;
			} else {
				$cpy_id = $cpy->id;
			}

			return '<div data-field="'.$cpy_id.'"
				data-field="pinkcrab" id="pinkcrab"
				data-platform_id="pinkcrab" data-option="pinkcrab"
				class="pinkcrab">
				<img src="/images/pinkcrab_50x50.png"
				style="width:25px;height:25px;cursor:pointer"
				</img></div>';
		})->
        addColumn('bluecrab', function ($memberList) {
			return '<div data-field="'.$memberList->id.'" data-field="bluecrab" id="bluecrab"
				data-platform_id="bluecrab" data-option="bluecrab" class="bluecrab">
				<img src="/images/bluecrab_50x50.png"
				style="width:25px;height:25px;cursor:pointer"
				</img></div>';
		})->
        addColumn('deleted', function ($memberList) {
			$merchant_id = $memberList->id;
			$query = "        
			SELECT 
				ec.id as id,
				ec.systemid,
				ec.platform,
				ec.url,
				ec.id as api,
				ec.status
			FROM ec_ecommercemgmt as ec
			WHERE merchant_id = ".$merchant_id."
			AND ec.status = 'online' 
			ORDER BY 
			 ec.created_at 
			DESC";
			$platforms = DB::select(DB::raw($query));
			if(sizeof($platforms) == 0){

				return '<div data-field="deleted" 
					data-option="delete" class="remove">
					<img src="/images/redcrab_50x50.png"
					style="width:25px;height:25px;cursor:pointer"
					</img></div>';

			} else {
				 return '<a href="javascript:void(0);" 
					style="background-color:#ddd;
					border-radius:5px;margin:auto;
					width:25px;height:25px;
					display:block;cursor: not-allowed;"
					class="text-danger" disabled>
					<img src="images/redcrab_disabled_25x25.png">
					</a>'; 				
			}
		})->escapeColumns([])->make(true);

		return $data;
    }
	
    public function connected_platforms(request $request) 
    {
		$merchant_id = $request->merchant_id;
		$query = "        
		SELECT 
			ec.id as id,
			ec.systemid,
			ec.platform,
			ec.url,
			ec.id as api,
			ec.status
		FROM ec_ecommercemgmt as ec
		GROUP BY(ec.systemid)
		ORDER BY 
			ec.created_at 
		DESC";

		$platforms = DB::select(DB::raw($query));
		foreach($platforms as $platform){
			$platform->exists = false;
			$platform_econ = DB::table('ec_ecommercemgmt')
				->where('systemid', $platform->systemid)
				->where('merchant_id', $merchant_id)
				->first();
			//dd($platform_econ);
			if(!is_null($platform_econ)){
				if($platform_econ->status == 'online'){
					$platform->exists = true;
				}
			}
			
		}
	//	dd($platforms);
        return view('merchant.merchant-platforms', compact(
			'merchant_id', 'platforms'
		));
    }	
	

    public function upsert_product_platform(Request $request) {	
		 $platforms = $request->platforms;
		 $notplatforms = $request->notplatforms;
		 $merchant_id = $request->merchant_id;
		 $error = "";
		 $response = array();
		//	dd($notplatforms);
		if(!empty($platforms)){
			for($t = 0; $t < sizeof($platforms); $t++){
				$platform = DB::table('ec_ecommercemgmt')->
					where('systemid', $platforms[$t])->
					where('merchant_id', $merchant_id)->first();
				 
				 if(!is_null($platform)){
					// dd($platform->id);
					DB::table('ec_ecommercemgmt')->
						where('id', $platform->id)
						->update([
							'status'=> 'online',
							'updated_at' => date('Y-m-d H:i:s'
						)]);

				} else {
					$platform_old = DB::table('ec_ecommercemgmt')->
					  	where('systemid', $platforms[$t])->first();
					//  dd();

					if(!is_null($platform_old)){
						DB::table('ec_ecommercemgmt')
						->insert([
							'systemid' => $platforms[$t],
							'merchant_id' => $merchant_id,
							'url' => $platform_old->url,
							'platform' => $platform_old->platform,
							'status' => 'online',
							'created_at' => date('Y-m-d H:i:s'),
							'updated_at' => date('Y-m-d H:i:s')	
						]);
					}
				}
			 }
		 }

		if(!empty($notplatforms)){

			for($t = 0; $t < sizeof($notplatforms); $t++){
				$platform = DB::table('ec_ecommercemgmt')->
					where('systemid', $notplatforms[$t])->
					where('merchant_id', $merchant_id)->
					first();

				if(!is_null($platform)){
					DB::table('ec_ecommercemgmt')->
						where('id', $platform->id)->
						update([
							'status'=> 'offline',
							'updated_at' => date('Y-m-d H:i:s')
						]);
				} 
			}		 
		}		 

		return response()->json($response);
	}	


	public function store() {
		try {
			$merchant = new Merchant();
			$company = new Company();
			$king = new User();
			$staff = new staff();
			$system_id = new SystemID('company');
			$system_id_ind = new SystemID('individual');

			$king->type = 'staff';
			$king->status = 'pending';
			$king->save();

			$company->systemid = $system_id;
			$company->owner_user_id = $king->id;
			$company->save();

			$staff->user_id = $king->id;
			$staff->systemid = $system_id_ind;
			$staff->company_id = $company->id;
			$staff->save();

			$merchant->company_id = $company->id;
			//$merchant->system_id = $system_id;
			$merchant->save();
			
            		$ScriptController = new ScriptController();
            		$ScriptController->activate_all_modules($merchant->id);

			 $msg = "Merchant added successfully";
			 return view('layouts.dialog',compact('msg'));

		} catch (\Exception $e) {
			 $msg = "Some error occured";
			 log::debug($e);
			 return view('layouts.dialog',compact('msg'));
		}
   	}


   	public function showEditModel(Request $request) {
		try {
			$allInputs = $request->all();
			$id = $request->get('id');
			$field_name = $request->field_name;

			$merchant = Merchant::find($id);
			$company = Company::find($merchant->company_id);
			$king = User::with('staff')->find($company->owner_user_id);

	  
			if ($request->field_name == 'name') {
			$model = 'edit';
			} else if ($request->field_name == 'status') {
				$model = 'status';
			} elseif ($request->field_name == 'deleted') {
						$model =  "deleted";
			} else {
			   return '';
			}

			return view('merchant.edit-model',
				compact('merchant','company','king','model'));

		} catch (\Exception $e) {
			 $msg = "Some error occured";
			 log::debug($e);
			 return view('layouts.dialog',compact('msg'));
		}
   	}


	public function updateUserStatus(Request $request)
    {
        try {
            $allInputs = $request->all();
            $id = $request->get('id');
            $status = $request->get('status');


            $validation = Validator::make($allInputs, [
                'id' => 'required',
                'status' => 'required'
            ]);

            if ($validation->fails()) {
				$msg = "Validation Error";
				$fieldName = 'msg_dilog';
				return view('user.edit-user-modal',compact('fieldName','msg'));
            }

			$merchant = Merchant::findOrFail($id);
			$company = Company::findOrFail($merchant->company_id);
			$userData = User::findOrFail($company->owner_user_id);
			$staffData = Staff::where('user_id', $company->owner_user_id)->first();

            if ($userData->email == '' or $userData->password == '') {
				$msg = "Cannot update active status because email/Password not defined.";
				return view('layouts.dialog',compact('msg'));
            }

			$userData->status = $status;
			$userData->save();

			$staffData->status = $status;
			$staffData->save();

			$company->status = $status;
			if ((empty($company->approved_at) or $company->approved_at == '') and $status == 'active') {
                $now = Carbon::now()->toDateTimeString();
                $company->approved_at = $now;
            }
			$company->save();

			$merchant->status = $status;
			$merchant->save();

			$msg = "Status updated.";
			return view('layouts.dialog',compact('msg'));

        } catch (\Illuminate\Database\QueryException $ex) {
			$msg = "Some error occured";
			return view('layouts.dialog',compact('msg'));
        }

        return $response;
    }


	public function update(Request $request)
    {
        try {
            $company_id = $request->company_id;
            $company = Company::find($company_id);
            $merchant = Merchant::where('company_id',$company->id)->first();
            $king = User::with('staff')->findOrFail($company->owner_user_id);
            $changed = false;
            if ($request->has('mname')) {
				if($company->name != $request->mname) {
					$company->name = $request->mname;
					$changed = true;
				}
			}

			if ($request->has('pname')) {
				if($king->name != $request->pname) {
					$king->name = $request->pname;
					$changed = true;
				}
			}

			if ($request->has('email')) {
				if($king->email != $request->email) {
                    $validation = Validator::make($request->all(), [
                        'email' => 'sometimes|nullable|email',
                    ]);

					if ($validation->fails()  && $request->email != '' ) {
						$msg = "Error: Invalid Email";
						return view('layouts.dialog',compact('msg'));
                    }

                    $is_email_exist = User::where('email',$request->email)->first();
                    if ($is_email_exist && $request->email != '') {
						$msg = "Error: Email already exists, please enter email that is not in the system";
						return view('layouts.dialog',compact('msg'));
                    }

                    $king->email = $request->email;
                    $changed = true;
				}
			}
     
        	if ($request->has('password') &&
				$request->filled('password') &&
				$request->password != '') {

				$validation = Validator::make($request->all(), [
					'password' => 'sometimes|nullable|string|min:6|confirmed',
				]);

				if ($validation->fails() &&
					($request->filled('password') ||
					$request->filled('password_confirmation'))) {

					$msg = "Error: Verify Password and Password need to be matched with minimum 6 characters.";
					return view('layouts.dialog', compact('msg'));
				}

				$king->password = bcrypt($request->password);
				$changed = true;
			}

			if ($changed) {
				$company->save();
				$merchant->save();
				$king->save();

				$msg = "Details updated";
				return view('layouts.dialog',compact('msg'));
			}

		} catch (\Illuminate\Database\QueryException $e) {
			log::debug($e);
			$msg = "Failed to update data";
			return view('layouts.dialog',compact('msg'));
		}
	}


	public function destroy($id)
    {
        try {
			$merchant = Merchant::find($id);
			$company = Company::find($merchant->company_id);


			$userid = Staff::where('company_id', $company->id)->pluck('user_id');
			$staffData = Staff::where('company_id', $company->id)->delete();

			$user = User::whereIn('id',$userid)->delete();

			$merchant->delete();
			$company->delete();

			$msg = "Merchant deleted successfully";
			return view('layouts.dialog', compact('msg'));

        } catch (\Illuminate\Database\QueryException $e) {
			$msg = "Failed to delete data";
			log::debug($e);
			return view('layouts.dialog', compact('msg'));
		}
    }


    public function viewmerchant(Request $request, $id) {
		$company_id = Merchant::where('company_id',$id)->first();
		$user_data =  new UserData();
		if (!$company_id || !$user_data->is_super_admin()) {
			abort(404);
		}

		$merchant_hash = $user_data->view_merchant($id);
		\Log::info("Logged in merchant $id with hash: $merchant_hash");
		
		if ( $request->route()->getName() == 'mechant.viewmerchant') {
			return response()->redirectTo("viewmerchant/$id/processed?&?&merchant_hash=$merchant_hash");
		}

		$user_roles = [];
		$is_king  = $user_data->is_super_admin();
		
		return view('landing.landing', compact('user_roles','is_king','merchant_hash'));
	}


    public function showMerchant(){
    	return view('merchant.merchant');
    }
}
