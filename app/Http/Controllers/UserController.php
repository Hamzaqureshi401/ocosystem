<?php

namespace App\Http\Controllers;

use App\Models\role;
use App\Models\Staff;
use App\Models\usersrole;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Log;
use Matrix\Exception;
use Yajra\DataTables\DataTables;
use \App\Classes\SystemID;
use \App\Classes\UserData;
use \App\Http\Controllers\OrganisationHierarchyController as OH;
use \App\Http\Controllers\AnalyticsController;
use \App\Http\Controllers\ScriptController;
/**
 * Class StaffController
 * @package App\Http\Controllers
 */
class UserController extends Controller
{
    protected $user_data;

    public function __construct()
    {

        // $s1 = new systemid('company');
        // $s2 = new systemid('individual');
        // $s3 = new systemid('product');
        //          dd($s1,$s2,$s3);
        $this->middleware('auth');
        $this->middleware('CheckRole:data');
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function index()
    {
        $model = new User();
        //\App\Models\Company::first()->owner_user_id assuming to be Auth::user()->id

        $this->user_data = new UserData();
        $company_id      = $this->user_data->company_id();
        $is_king         = \App\Models\Company::findOrFail($company_id);

        $user_ids = Staff::where([['user_id', '!=', $is_king->owner_user_id], ['company_id', '=', $company_id]])->pluck('user_id');

        $staffListII = $model->with('staff')->where([['type', '=', 'staff']])->whereIn('id', $user_ids)->latest()
            ->get();

        $king = $model->with('staff')->where('id', $is_king->owner_user_id)->first();

        if ($king) {
            $king['king'] = true;
            $king         = collect([$king]);
            $staffList    = $king->merge($staffListII);
        } else {
            $staffList = $staffListII;
        }

        $id = 0;

        // addIndexColumn()->
        $data = Datatables::of($staffList)->
            addColumn('DT_RowIndex', function ($staffList) {
            global $id;
            $id_ = $id;
            $id += 1;
            return $id_;
        })->
            addColumn('id', function ($staffList) {
            return sprintf("%'.09d\n", $staffList->id);
        })->
            addColumn('sysid', function ($staffList) {
            return $staffList->staff->systemid;
        })->
            addColumn('name', function ($staffList) {
            return '<p data-field="staff_name" class="os-linkcolor" style="cursor: pointer; margin: 0;">' .
                (!empty($staffList->name) ? $staffList->name : 'Name') . '</p>';
        })->
            addColumn('function', function ($staffList) {
            return '<p data-field="staff_function" class="os-linkcolor" style="cursor: pointer; margin: 0;">Function</p>';

        })->
        	 addColumn('location', function ($staffList) {
            return '<p data-field="location" class="os-linkcolor" style="cursor: pointer; margin: 0;">Location</p>';
        })->
        	 addColumn('type', function ($staffList) {
            return '<p data-field="staff_role" class="os-linkcolor" style="cursor: pointer; margin: 0;">Roles</p>';
        })->
            addColumn('status', function ($staffList) {
            return '<p data-field="status" class="os-linkcolor" style="cursor: pointer;  margin: 0;">' .
            ucfirst($staffList->status) . '</p>';
        })->
            addColumn('pinkcrab', function ($staffList) {
			return '<div data-field="pinkcrab" class="">
				<img src="/images/pinkcrab_50x50.png"
				style="width:25px;height:25px;cursor:pointer"/>
				</div>';
        })->
            addColumn('bluecrab', function ($staffList) {
			return '<div data-field="bluecrab" class="">
				<img src="/images/bluecrab_50x50.png"
				style="width:25px;height:25px;cursor:pointer"/>
				</div>';
        })->

        addColumn('deleted', function ($staffList) {

            $is_secondary_admin = usersrole::where([
                'role_id' => role::where('name', 'sadmin')->first()->id,
                "user_id" => $staffList->id])->first();

            $is_this_admin = usersrole::where([
                'role_id' => role::where('name', 'sadmin')->first()->id,
                "user_id" => Auth::user()->id])->first() || $this->user_data->allow_all();

            $is_this_user_king = \App\Models\Company::where(
                'owner_user_id', Auth::user()->id)->first() || $this->user_data->allow_all();

            if ($staffList->id == Auth::user()->id) {
                return sprintf("\n", $staffList->id);
            }

            if ($is_secondary_admin) {
                if (!$is_this_admin and !$is_this_user_king) {
                    return sprintf("\n", $staffList->id);
                }
            }

            if (!isset($staffList->king)) {
				return '<div data-field="deleted" class="remove">
					<img src="/images/redcrab_50x50.png"
					style="width:25px;height:25px;cursor:pointer"/>
					</div>';

            } else {
                return sprintf("\n", $staffList->id);
            }
        })->setRowClass(function ($staffList) {
        $is_secondary_admin = usersrole::where([
            'role_id' => role::where('name', 'sadmin')->first()->id,
            "user_id" => $staffList->id])->first();

        $is_this_admin = usersrole::where([
            'role_id' => role::where('name', 'sadmin')->first()->id,
            "user_id" => Auth::user()->id])->first() || $this->user_data->allow_all();

            $is_this_user_king = \App\Models\Company::where(
                'owner_user_id', Auth::user()->id)->first();

            $self = null;

            if ($staffList->id == Auth::user()->id) {
                $self = 'self';
            }

	    if ($staffList->status != 'active') {
	    	$self .= 'crab_disable';
	    }
            if ($is_secondary_admin) {
                if (!$is_this_user_king and !$is_this_admin) {
                    return "sadmin name_disable status_disabled $self";
                } else {
                    return "sadmin $self";
                }
            } else if (isset($staffList->king)) {
                if (\App\Models\Company::first()->owner_user_id != Auth::user()->id) {
                    return 'G_King';
                } else {
                    return 'King';
                }
            } else {
                if ($staffList->email == '' or
                    $staffList->password == '' or
                    $staffList->name == '') {
                    return "status_disabled role_disabled  $self";
                }

                return $self;
            }
        })->escapeColumns([])->make(true);
        return ($data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        Log::debug('***** store() *****');

        //Create a new user here
        try {
            $userModel       = new User();
            $SystemID        = new SystemID('individual');
            $this->user_data = new UserData();

            /*        dd($SystemID->__toString());*/
            $userModel->type   = 'staff';
            $userModel->status = 'pending';
            $userModel->save();

            $staffModel             = new Staff();
            $staffModel->user_id    = $userModel->id;
            $staffModel->company_id = $this->user_data->company_id();
            $staffModel->systemid   = $SystemID;
            $staffModel->save();
			
			$scriptController = new ScriptController();
			$scriptController->activate_all_locations($userModel->id);

            $msg       = "User added successfully";
            $fieldName = 'msg_dilog';
            return view('user.edit-user-modal', compact('fieldName', 'msg'));

        } catch (\Exception $e) {
            $msg = "Some error occured";
            log::debug($e);
            $fieldName = 'msg_dilog';
            return view('user.edit-user-modal', compact('fieldName', 'msg'));
        }

        return $response;
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {

        try {

            if ($request->type === 'name') {
                User::where('id', $id)->update(['name' => $request->name]);
            } elseif ($request->type === 'approval') {
                $status = $request->status;
                if ($status == 'true') {
                    $approve = 'active';
                } else {
                    $approve = 'inactive';
                }

                User::where('id', $id)->update(['status' => $approve]);
            }
            return response()->json('Data updated successfully', 202);

        } catch (\Exception $e) {
            return response()->json($e, 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {

        //
        try {

            $this->user_data = new UserData();
			
			$is_exist        = Staff::where(['user_id' => $id, 'company_id' => $this->user_data->company_id()])->
				orWhere(['user_id' => $id, 'company_id' => NULL])->
				first();

            if (!$is_exist) {

                $msg       = "Error cannot delete, user not found";
                $fieldName = 'msg_dilog';
                return view('user.edit-user-modal',
                    compact('fieldName', 'msg'));
            }

            if (auth()->id() == $id) {

                $msg       = "Active user cannot be deleted";
                $fieldName = 'msg_dilog';
                return view('user.edit-user-modal',
                    compact('fieldName', 'msg'));

            } else {

                $is_g_sadmin       = usersrole::where(['role_id' => role::where('name', 'sadmin')->first()->id, "user_id" => $id])->first();
                $is_this_admin     = $this->user_data->is_secondary_admin();
                $is_this_user_king = $this->user_data->is_king() || $this->user_data->allow_all();
                $is_this_g_king    = \App\Models\Company::where('owner_user_id', $id)->first();

                if ($id == Auth::user()->id) {
                    $msg       = "Can't delete own account";
                    $fieldName = 'msg_dilog';
                    return view('user.edit-user-modal',
                        compact('fieldName', 'msg'));
                }

                if ($is_g_sadmin) {
                    if (!$is_this_admin and !$is_this_user_king) {
                        $msg       = "Can't delete secondary adminstrator";
                        $fieldName = 'msg_dilog';
                        return view('user.edit-user-modal',
                            compact('fieldName', 'msg'));
                    }
                }

                if ($is_this_g_king) {
                    $msg       = "Can't delete King";
                    $fieldName = 'msg_dilog';
                    return view('user.edit-user-modal',
                        compact('fieldName', 'msg'));
                }
		
				$location_ids = DB::table('userslocation')->
					where('user_id', $id)->
					get()->
					pluck('location_id')->toArray();
				$this->deleteRealtimeTerminals($id, $location_ids);

	
                User::where('id', $id)->delete();
                Staff::where('user_id', $id)->delete();
                $msg       = "User deleted successfully";
                $fieldName = 'msg_dilog';

					
                return view('user.edit-user-modal',
                    compact('fieldName', 'msg'));

            }
        } catch (\Illuminate\Database\QueryException $ex) {
            $msg       = "Some error occured";
            $fieldName = 'msg_dilog';
            return view('user.edit-user-modal',
                compact('fieldName', 'msg'));
            // $response = (new ApiMessageController())->queryexception($ex);
        }

        return $response;
    }

    public function showEditModal(Request $request)
    {
        try {
            $allInputs = $request->all();
            $id        = (int) $request->get('id');
            $fieldName = $request->get('field_name');
            $user_id   = Auth::user()->id;
            $this->user_data = new UserData();

            $OH         = new OH();
            $department = $OH->fetchData('department');
            $position   = $OH->fetchData('position');

	    $function = null;

            $is_king = \App\Models\Company::where('owner_user_id', $user_id)->first();

            $is_g_King = \App\Models\Company::where('owner_user_id', $id)->first();

            $is_this_user_sadmin = usersrole::where([
                'role_id' => role::where('name', 'sadmin')->first()->id,
                'user_id' => $user_id,
            ])->first();

            $is_g_user_sadmin = usersrole::where([
                'role_id' => role::where('name', 'sadmin')->first()->id,
                'user_id' => $id,
            ])->first();

            $is_king             = ($is_king || $this->user_data->is_super_admin()) ? true : false;
            $is_g_King           = ($is_g_King) ? true : false;
            $is_this_user_sadmin = ($is_this_user_sadmin) ? true : false;
            $is_g_user_sadmin    = ($is_g_user_sadmin) ? true : false;

            $is_self = ($id == $user_id) ? true : false;

            $validation = Validator::make($allInputs, [
                'id'         => 'required',
                'field_name' => 'required',
            ]);

            if ($validation->fails()) {
                $response = (new ApiMessageController())->
                    validatemessage($validation->errors()->first());

            } else {
                $userData = User::where('id', $id)->with('staff')->first();
                $role     = \App\Models\usersrole::where('user_id', $id)->get();

                if ($fieldName == 'status') {
                    if ($userData->email == null or $userData->password == '' or $userData->name == '') {
                        return '';
                    }
                }

		if ($fieldName == 'bluecrab') {
			$function = DB::table('function')
						->orderBy('name','asc')->get();
			
			$userData = User::where('id', $id)->with('staff')->first();
			foreach ($function as $z) {
				//dd($userData->id);
				$z->is_active = !empty(
					DB::table('usersfunction')
						->where([
							'user_id' => $userData->id,
							'company_id' => $userData->staff->company_id,
							'function_id' => $z->id
						])
						->whereNull('deleted_at')
						->first()
					);
			}					
		}

		if ($fieldName == 'pinkcrab') {
			$userData = User::where('id', $id)->with('staff')->first();

			$function = DB::table('mobrole')
					->whereNull('deleted_at')
					->get();
			
			foreach ($function as $z) {
			
				$z->is_active =  !empty(
					DB::table('usersmobrole')
						->where([
							'mobrole_id' => $z->id,	
							'user_id' => $userData->id,
							'company_id' => $userData->staff->company_id,
						])
						->whereNull('deleted_at')
						->first()
					);
			}
		}

		$branch_location = [];
		if ($fieldName == 'location') {

			$userData = User::where('id', $id)->first();

			$analyticsController =  new AnalyticsController();
			$get_location = $analyticsController->get_location(false);
				foreach ($get_location as $key => $val) {
					$$key = $val;
					$location_id = array_column($branch_location, 'id');
					foreach($val as $location) {
						if (!in_array($location->id,$location_id)){
							$branch_location = array_merge($branch_location, [$location]);
						}
					}
				}

			array_map(function($f) use ($userData) {
				$f->is_active = !empty(
					DB::table('userslocation')->
						where([
							"user_id"		=> $userData->id,
							"location_id"	=> $f->id,
							])->
						whereNull('deleted_at')->
						first()
					);
				}, $branch_location);

				$branch_location = collect($branch_location);
				$branch_location = $branch_location->unique('branch');
			}
                return view('user.edit-user-modal',
                    compact('id', 'fieldName', 'userData', 'role', 'is_king',
                        'is_g_King', 'is_this_user_sadmin','branch_location',
                        'is_g_user_sadmin', 'is_self', 'department', 'position','function'));
            }

        } catch (\Illuminate\Database\QueryException $ex) {
            $response = (new ApiMessageController())->queryexception($ex);
        }

        return $response;
    }

    public function toggleFunction(Request $request) {
	    try {
		    $f_id = $request->f_id;
		    $u_id = $request->u_id;

		    $this->user_data = new UserData();

		    $userData = User::find($u_id);
		    $company_id = $this->user_data->company_id();

		    $is_function_exist = DB::table('function')->find($f_id);
		    
		    if(empty($is_function_exist)) {
			    throw new \Exception("Invalid function record");
		    }

		    $is_functionrole_exist = DB::table('usersfunction')
						->where([
							'user_id' => $u_id,
							'company_id' => $company_id,
							'function_id' => $is_function_exist->id
						])
						->whereNull('deleted_at')
						->first();

		    if (!empty($is_functionrole_exist)) {

				DB::table('usersfunction')
						->where([
							'user_id' => $u_id,
							'company_id' => $company_id,
							'function_id' => $is_function_exist->id
						])
						->whereNull('deleted_at')
						->delete();
						
						/*/->update([
							"deleted_at" => date('Y-m-d H:i:s')
						]);
				/*/


			} else {

		    	 DB::table('usersfunction')
				->insert([
					'user_id' => $u_id,
					'company_id' => $company_id,
					'function_id' => $is_function_exist->id,
					"created_at" => date('Y-m-d H:i:s')
				]);

			 //return response()->json(["added"=>"true"]);
			}
			$msg = "Information updated";

	    } catch (\Exception $e) {
		    $msg = "Error: ".$e->getMessage();
		    Log::info($msg);
	    }


		$this->updateRealtimeTerminals($u_id);
		$fieldName = 'msg_dilog';
		return view('user.edit-user-modal', compact('fieldName', 'msg'));
    }

    public function toggleMobileRoles(Request $request) {
	    try {
		    $user_id = $request->u_id;
		    $role_id = $request->f_id;

		    $user_exist = User::find($user_id);

		    if (empty($user_exist)) {
			    throw new \Exception("User did not exist");
		    }
		    
		    $is_role_exist = DB::table('mobrole')->find($role_id);
		    
		    if (empty($is_role_exist)) {
			    throw new \Exception("Invalid role");
		    }

		    $merchant_id = $user_exist->staff->company_id;
		    
		    $role_state = DB::table('usersmobrole')	
					->where([
						"mobrole_id" => $role_id,
						"user_id" => $user_id,
						'company_id' => $merchant_id
					])
					->whereNull('deleted_at')
					->first();

		    if (empty($role_state)) {
			    	DB::table('usersmobrole')
					->insert([
						"mobrole_id" => $role_id,
						"user_id" => $user_id,
						'company_id' => $merchant_id,
						'created_at' => date('Y-m-d H:i:s')
					]);
		    } else {
				DB::table('usersmobrole')
					->where([
						"mobrole_id" => $role_id,
						"user_id" => $user_id,
						'company_id' => $merchant_id,	
					])->update([
						'deleted_at' => date('Y-m-d H:i:s')
					]);

		    }

		    return response()->json(["status"=>"done"]);
	    } catch (\Exception $e) {
		    Log::info ($e);
		    abort(404);
	    }
    }

	public function toggleLocationAuth(Request $request) {
		try {
			$validation = Validator::make($request->all(),[
				"u_id"	=>	"required",
				"f_id"	=>	"required"
			]);

			if ($validation->fails())
				throw new \Exception("Validation error");

			$userslocation = DB::table('userslocation');

			$condition = [
				"user_id"		=>	$request->u_id,
				"location_id"	=>	$request->f_id,
			];

			$is_exist =  $userslocation->
				where($condition)->
				whereNull('deleted_at')->
				first();
			if (empty($is_exist)) {
				$condition['created_at'] = date("Y-m-d h:i:s");
				$condition['updated_at'] = date("Y-m-d h:i:s");
				$userslocation->insert($condition);
				$this->updateRealtimeTerminals($request->u_id);
			} else {

				$this->deleteRealtimeTerminals($request->u_id, [  'location_id' => $request->f_id, 'user_id' => $request->u_id ]);
				$userslocation->where($condition)->update([
					"deleted_at"	=> date("Y-m-d h:i:s")
				]);
			}
			$msg = 'Updated terminal location';
			return view('layouts.dialog', compact('msg'));
		} catch (\Exception $e) {
			\Log::info([
				"Error"	=>	$e->getMessage(),
				"File"	=>	$e->getFile(),
				"Line"	=>	$e->getLine()
			]);
			abort(404);
		}
	}

    public function updateUserFields(Request $request)
    {

        try {

            $this->user_data = new UserData();
            $id              = $request->user_id;
            $validation      = Validator::make($request->all(), [
                'password' => 'sometimes|nullable|string|min:6|confirmed',
                'email'    => 'sometimes|nullable|string|email|max:255|unique:users,email,' . $id,
            ]);

            $is_king = \App\Models\Company::where('owner_user_id', $id)->first();
            $changed = false;

            if ($is_king) {
                if ($id != Auth::user()->id) {
                    $msg       = "You can't change the King"; // but he can change you!
                    $fieldName = 'msg_dilog';
                    return view('user.edit-user-modal', compact('fieldName', 'msg'));
                }
            }

            if ($request->has('email')) {

                $check = \App\User::where([['email', '=', $request->email], ['id', '<>', $request->user_id]]
                )->get()->count();

                if ($check > 0 && $request->email != '') {
                    $msg       = "Email already exists, please enter email that is not in the system";
                    $fieldName = 'msg_dilog';
                    return view('user.edit-user-modal', compact('fieldName', 'msg'));
                }

            }

            if ($validation->fails() && ($request->filled('password') || $request->filled('password_confirmation'))) {

                $msg       = "Error: Verify password, it needs to be matched with minimum 6 characters.";
                $fieldName = 'msg_dilog';
                return view('user.edit-user-modal', compact('fieldName', 'msg'));

            } else {

                $is_this_user_sadmin = usersrole::where([
                    'role_id' => role::where('name', 'sadmin')->first()->id,
                    'user_id' => Auth::user()->id,
                ])->first();

                $is_this_user_king = \App\Models\Company::where('owner_user_id', Auth::user()->id)->first() || $this->user_data->allow_all();

                $is_given_id_sadmin = usersrole::where([
                    'role_id' => role::where('name', 'sadmin')->first()->id,
                    'user_id' => $id,
                ])->first();

                if ($is_given_id_sadmin) {
                    if (!$is_this_user_sadmin and !$is_this_user_king) {
                        $msg       = "You can't change secondary adminstrator";
                        $fieldName = 'msg_dilog';
                        return view('user.edit-user-modal', compact('fieldName', 'msg'));
                    }
                }

                $userModel = User::find($request->user_id);
                $staffData = Staff::where('user_id', $request->user_id)->first();

                if ($staffData->company_id != $this->user_data->company_id()) {
                    $msg       = "Error cannot update data, user not found.";
                    $fieldName = 'msg_dilog';
                    return view('user.edit-user-modal', compact('fieldName', 'msg'));
                }

                if (!$userModel or !$staffData) {
                    $msg       = "Error: Record broken";
                    $fieldName = 'msg_dilog';
                    return view('user.edit-user-modal', compact('fieldName', 'msg'));
                }

                if ($request->has('email')) {

                    $check = \App\User::where([['email', '=', $request->email], ['id', '<>', $request->user_id]]
                    )->get()->count();

                    if ($userModel->email != $request->email) {

                        $userModel->email = $request->email;
                        if ($check > 0 && $request->email != '') {
                            $msg       = "Email already exists, please enter email that is not in the system";
                            $fieldName = 'msg_dilog';
                            return view('user.edit-user-modal', compact('fieldName', 'msg'));
                        }
                        $changed = true;
                    }
                }

                if ($request->has('password') && $request->filled('password') && $request->filled('password_confirmation')) {
                    $userModel->password = bcrypt($request->password);
                    $changed             = true;
                }

                if ($request->has('name')) {
                    if ($userModel->name != $request->name) {
                        $userModel->name = $request->name;
                        $changed         = true;
                    }
                }

                if ($request->has('department')) {

                    if ($staffData->department != $request->department) {
                        $staffData->department = $request->department;
                        $changed               = true;
                    }
                }

                if ($request->has('position')) {
                    if ($staffData->position != $request->position) {
                        $staffData->position = $request->position;
                        $changed             = true;
                    }
                }

                if ($changed == true) {
                    $updateUser  = $userModel->save();
                    $updateStaff = $staffData->save();
					$this->updateRealtimeTerminals($request->user_id);
                } else {
                    return abort(404);
                }
                if ($updateUser && $updateStaff) {

                    $msg       = "User updated successfully";
                    $fieldName = 'msg_dilog';
                    return view('user.edit-user-modal', compact('fieldName', 'msg'));

                } else {
                    $msg       = "Failed to update data";
                    $fieldName = 'msg_dilog';
                    return view('user.edit-user-modal', compact('fieldName', 'msg'));
                }
            }

        } catch (\Illuminate\Database\QueryException $e) {
            $msg       = "Failed to update data";
            $fieldName = 'msg_dilog';
            return view('user.edit-user-modal', compact('fieldName', 'msg'));
        }

        return $response;
    }

	public function updateRealtimeTerminals($user_id) {
		try {

			$user = DB::table('users')->where('users.id', $user_id)->
				join('staff','staff.user_id','users.id')->
				select("users.*",'staff.systemid')->
				get()->toArray();

			$post = [
				"users"	=>	json_encode($user)
			];

			$location_ = DB::table('locationipaddr')->
				join('userslocation','userslocation.location_id','locationipaddr.location_id')->
				where('userslocation.user_id', $user_id)->
				select('locationipaddr.ipaddr','locationipaddr.tsystem')->
				get()->unique();

			foreach ($location_ as $l) {
				if (!empty($l->tsystem)) {
					$url = "http://$l->tsystem/interface/update_data";
					$cURLConnection = curl_init($url);
					curl_setopt($cURLConnection, CURLOPT_POSTFIELDS, $post);
					curl_setopt($cURLConnection, CURLOPT_RETURNTRANSFER, true);
					curl_setopt($cURLConnection, CURLOPT_SSL_VERIFYPEER, false);
					curl_setopt($cURLConnection, CURLOPT_SSL_VERIFYHOST, false);
					$apiResponse = curl_exec($cURLConnection);
					curl_close($cURLConnection);
					$data = json_decode($apiResponse, true);

					\Log::info([
						"url"		=> $url,
						"response"	=> $apiResponse
					]);
				}
			}

		} catch (\Exception $e) {
			$err = [
				"Error"	=>	$e->getMessage(),
				"File"	=>	$e->getFile(),
				"Line"	=>	$e->getLine()
			];

			\Log::info($err);
		}
		return $err ?? true;
	}
	
	public function	deleteRealtimeTerminals($user_id, $location_id) {
		try {

			$user = DB::table('users')->
				leftjoin('staff','staff.user_id','users.id')->
				where('users.id', $user_id)->
				select("users.*",'staff.systemid')->
				first()->systemid;

			$user = ['table' => 'users', "condition" => ['systemid' => $user] ];
		
			$post = [
				"delete"	=>	json_encode($user)
			];

			if (!is_array($location_id)) {
				$location_ = DB::table('locationipaddr')->
					where('location_id', $location_id)->
					select('locationipaddr.ipaddr')->
					get()->unique();
			} else {
				$location_ = DB::table('locationipaddr')->
					whereIn('location_id', $location_id)->
					select('ipaddr','tsystem')->
					get()->unique();
			}

			foreach ($location_ as $l) {
				if (!empty($l->tsystem)) {
					$url = "http://$l->tsystem/interface/update_data";
					$cURLConnection = curl_init($url);
					curl_setopt($cURLConnection, CURLOPT_POSTFIELDS, $post);
					curl_setopt($cURLConnection, CURLOPT_RETURNTRANSFER, true);
					curl_setopt($cURLConnection, CURLOPT_SSL_VERIFYPEER, false);
					curl_setopt($cURLConnection, CURLOPT_SSL_VERIFYHOST, false);
					$apiResponse = curl_exec($cURLConnection);
					curl_close($cURLConnection);
					$data = json_decode($apiResponse, true);

					\Log::info([
						"url"		=> $url,
						"response"	=> $apiResponse
					]);
				}
			}

		} catch (\Exception $e) {
			$err = [
				"Error"	=>	$e->getMessage(),
				"File"	=>	$e->getFile(),
				"Line"	=>	$e->getLine()
			];

			\Log::info($err);
		}
		return $err ?? true;

	}

    public function updateUserStatus(Request $request)
    {
        try {
            $allInputs       = $request->all();
            $id              = $request->get('id');
            $status          = $request->get('status');
            $this->user_data = new UserData();

            $validation = Validator::make($allInputs, [
                'id'     => 'required',
                'status' => 'required',
            ]);

            if ($validation->fails()) {
                $msg       = "Validation error";
                $fieldName = 'msg_dilog';
                return view('user.edit-user-modal', compact('fieldName', 'msg'));

            } else {

                $is_king = \App\Models\Company::where('owner_user_id', $id)->first();
                if ($is_king) {
                    $msg       = "You can't change King status";
                    $fieldName = 'msg_dilog';
                    return view('user.edit-user-modal', compact('fieldName', 'msg'));
                }

                $is_this_user_sadmin = usersrole::where([
                    'role_id' => role::where('name', 'sadmin')->first()->id,
                    'user_id' => Auth::user()->id,
                ])->first();

                $is_this_user_king = \App\Models\Company::where('owner_user_id', Auth::user()->id)->first() || $this->user_data->allow_all();

                $is_given_id_sadmin = usersrole::where([
                    'role_id' => role::where('name', 'sadmin')->first()->id,
                    'user_id' => $id,
                ])->first();

                if ($is_given_id_sadmin) {
                    if (!$is_this_user_sadmin and !$is_this_user_king) {
                        $msg       = "You can't change secondary adminstrator";
                        $fieldName = 'msg_dilog';
                        return view('user.edit-user-modal', compact('fieldName', 'msg'));
                    }
                }

                $userData  = User::where('id', $id)->with('staff')->first();
                $staffData = Staff::where('user_id', $id)->first();

                if (!$userData or !$staffData) {
                    $msg       = "Error: Invalid record";
                    $fieldName = 'msg_dilog';
                    return view('user.edit-user-modal', compact('fieldName', 'msg'));
                }

                if ($staffData->company_id != $this->user_data->company_id()) {
                    $msg       = "Error occured while updating user, user not found";
                    $fieldName = 'msg_dilog';
                    return view('user.edit-user-modal', compact('fieldName', 'msg'));
                }

                if ($userData->email != '' or $userData->password != '') {
                    $userData->status = $status;
                    $update           = $userData->save();

                    $staffData->status = $status;
                    $updateStaff       = $staffData->save();

					$this->updateRealtimeTerminals($userData->id);

                    if ($update && $updateStaff) {
                        $msg       = "Status updated";
                        $fieldName = 'msg_dilog';
                        return view('user.edit-user-modal', compact('fieldName', 'msg'));
                    } else {
                        $msg       = "Error occured";
                        $fieldName = 'msg_dilog';
                        return view('user.edit-user-modal', compact('fieldName', 'msg'));
                    }

                } else {
                    $msg       = "Cannot update active status because email/Password not defined";
                    $fieldName = 'msg_dilog';
                    return view('user.edit-user-modal', compact('fieldName', 'msg'));
                }
            }

        } catch (\Illuminate\Database\QueryException $ex) {
            $response = (new ApiMessageController())->queryexception($ex);
        }

        return $response;
    }

    public function updateUserRole(Request $request)
    {
        //$request->roles => role ID
        try {
            $allInputs       = $request->all();
            $id              = $request->get('id');
            $role            = $request->get('roles');
            $this->user_data = new UserData();
            $fieldName       = 'msg_dilog';

            $validation = Validator::make($allInputs, [
                'id'      => 'required',
                "roles"   => "array|min:0",
                "roles.*" => "string|distinct|min:0",
            ]);

            if ($validation->fails()) {
                $msg = "Validation error";
                return view('user.edit-user-modal', compact('fieldName', 'msg'));

            } else {
                $is_exist = User::where('id', $id)->first();
                if (!$is_exist || $is_exist->staff->company_id != $this->user_data->company_id()) {
                    $msg = "User not found";
                    return view('user.edit-user-modal', compact('fieldName', 'msg'));
                }
                $is_king = \App\Models\Company::where('owner_user_id', $id)->first();

                if ($is_king) {
                    if ($id != Auth::user()->id) {
                        $msg = "You can't change King";
                        return view('user.edit-user-modal', compact('fieldName', 'msg'));
                    } else {

                        $role = array_map(function ($role) {
                            return $role['name'];
                        }, role::get()->toArray());
                    }
                }

                $is_this_user_sadmin = usersrole::where([
                    'role_id' => role::where('name', 'sadmin')->first()->id,
                    'user_id' => Auth::user()->id,
                ])->first();

                $is_this_user_king = \App\Models\Company::where('owner_user_id', Auth::user()->id)->first() || $this->user_data->allow_all();

                $is_given_id_sadmin = usersrole::where([
                    'role_id' => role::where('name', 'sadmin')->first()->id,
                    'user_id' => $id,
                ])->first();

                if ($is_given_id_sadmin) {
                    if (!$is_this_user_sadmin and !$is_this_user_king) {
                        $msg = "You can't change secondary adminstrator";
                        return view('user.edit-user-modal', compact('fieldName', 'msg'));
                    }
                }

                if ((!$is_this_user_king && !$is_this_user_sadmin) || $is_king) {
                    $role = array_filter($role, function ($role) {
                        return $role != 'sadmin';
                    });
                }

                if (!$is_this_user_king && $id == Auth::user()->id) {
                    return null;
                }

                usersrole::where('user_id', $id)->delete();

                if (is_array($role)) {

                    foreach ($role as $value) {

                        $role_detail = role::where('name', $value)->first();

                        $is_exist = usersrole::where([
                            'user_id' => $id,
                            'role_id' => $role_detail->id,
                        ])->withTrashed()->first();

                        if ($is_exist) {
                            $is_exist->deleted_at = null;
                            $is_exist->save();
                        } else {
                            usersrole::create([
                                'user_id'    => $id,
                                'role_id'    => $role_detail->id,
                                'company_id' => $this->user_data->company_id(),
                            ]);
                        }
                    }
                }

                if ($is_king) {
                    if ($id == Auth::user()->id) {
                        $msg = "King role cannot be modified";
                        return view('user.edit-user-modal',
                            compact('fieldName', 'msg'));
                    }
                }

                $msg      = "User roles updated";
                $response = view('user.edit-user-modal',
                    compact('fieldName', 'msg'));
            }

        } catch (\Illuminate\Database\QueryException $e) {

            $msg = "Some error occured";
            return view('user.edit-user-modal', compact('fieldName', 'msg'));
        }

        return $response;
    }
}
