<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use App\Models\role;
use App\Models\usersrole;
use \App\Models\globaldata;
use App\User;
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
use \App\Http\Controllers\ScriptController;
use \App\Classes\SystemID;


class SuperadminController extends Controller
{
    //

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('CheckRole:super',["except"=>"showLandingView"]);
        
    }


      public function index()
    {
        $model = new User();
        //\App\Models\Company::first()->owner_user_id assuming to be Auth::user()->id

            $kingSuper =  Staff::where('systemid',globaldata::where('property','admin_system_id')->first()->value)->first()->user_id;

            if ($kingSuper) {
                $staffListII = $model->with('staff')->where([['id','!=',$kingSuper],['type','=','admin']])->latest()
                ->get();
                $king  = $model->with('staff')->where('id',$kingSuper)->first();
                 if ($king) {
                  $king['king'] = true;
                  $king =  collect([$king]);
                  $staffList = $king->merge($staffListII);
                } else {
                  $staffList = $staffListII;
                }

            } else {
                 $staffList = $model->with('staff')->where([['type','=','admin']])->latest()
                ->get();
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
                return  sprintf("%'.09d\n", $staffList->id);
            })->
                addColumn('sysid', function ($staffList) {
                    return   $staffList->staff->systemid;
            })->
            addColumn('name', function ($staffList) {
                return '<p data-field="staff_name" class="os-linkcolor" style="cursor: pointer; margin: 0;">'.
                (!empty($staffList->name) ?  $staffList->name : 'Name') . '</p>';
            })->
            addColumn('type', function ($staffList) {
                return '<p data-field="staff_role" class="os-linkcolor" style="cursor: pointer; margin: 0;">Roles</p>';
            })->
            addColumn('status', function ($staffList) {
                return '<p data-field="status" class="os-linkcolor" style="cursor: pointer;  margin: 0;">' .
                ucfirst($staffList->status) . '</p>';
            })->
            addColumn('deleted', function ($staffList) {

                $is_secondary_admin = usersrole::where([
                    'role_id'=>role::where('name','sadmin')->first()->id,
                    "user_id"=>$staffList->id])-> first();

                $is_this_admin = usersrole::where([
                    'role_id'=>role::where('name','sadmin')->first()->id,
                    "user_id"=>Auth::user()->id])->first();

                $is_this_user_king = Staff::where('systemid',globaldata::where('property','admin_system_id')->first()->value)->first()->user_id == Auth::user()->id;

                if ($staffList->id == Auth::user()->id) {
                    return  sprintf("\n", $staffList->id);
                }

                if ($is_secondary_admin) {
                    if (!$is_this_admin and !$is_this_user_king ) {
                        return  sprintf("\n", $staffList->id);
                    }
                }

                if (!isset($staffList->king)) {
					return '<div data-field="deleted"
						class="remove">
						<img class="" src="/images/redcrab_50x50.png"
						style="width:25px;height:25px;cursor:pointer"/>
						</div>';

					/*
                    return '<p data-field="deleted"
                    class="text-danger bg-redcrab1">
                    <i class="fas fa-times text-white bg-redcrab2"></i></p>
                    <span style="display:none;"</span>'; 
					*/

                } else {
                    return  sprintf("\n", $staffList->id);
                }

            })->setRowClass(function ($staffList) {
                $is_secondary_admin = usersrole::where([
                    'role_id'=>role::where('name','sadmin')->first()->id,
                    "user_id"=>$staffList->id])->first();

                $is_this_admin = usersrole::where([
                    'role_id'=>role::where('name','sadmin')->first()->id,
                    "user_id"=>Auth::user()->id])->first();

                $is_this_user_king = Staff::where('systemid',globaldata::where('property','admin_system_id')->first()->value)->first()->user_id == Auth::user()->id;

                $self = null;
      
                if ($staffList->id == Auth::user()->id) {
                    $self = 'self';
                }

                if ($is_secondary_admin) {
                    if (!$is_this_user_king and !$is_this_admin) {
                        return "sadmin name_disable status_disabled $self";
                    } else {
                        return "sadmin $self";
                    }

                } else if (isset($staffList->king)) {

                    if (Staff::where('systemid',globaldata::where('property','admin_system_id')->first()->value)->first()->user_id != Auth::user()->id) {
                        return 'G_King';
                    } else {
                        return 'King';
                    }

                } else {
                    if ($staffList->email == '' or
                        $staffList->password == '' or
                        $staffList->name == '') {
                        return "status_disabled role_disabled $self";
                    }

                    return $self;
                }
            })->escapeColumns([])->make(true);



      return ($data);
    }


     public function addAdmin(Request $request) {

         try {
            $userModel = new User();
            $SystemID = new SystemID('individual');

    /*        dd($SystemID->__toString());*/
            $userModel->type = 'admin';
            $userModel->status = 'pending';
            $userModel->save();

            $staffModel = new Staff();
            $staffModel->user_id = $userModel->id;
            $staffModel->company_id = NULL;
            $staffModel->systemid = $SystemID;
            $staffModel->save();

            $ScriptController = new ScriptController();
			$ScriptController->activate_all_roles($userModel->id,0);
            $ScriptController->activate_all_locations($userModel->id);

			$msg = "Administrator added successfully";
			return view('layouts.dialog',compact('msg'));

        } catch (\Exception $e) {
             $msg = $e->getMessage(); //"Some error occured";
             Log::debug($e);

          return view('layouts.dialog',compact('msg'));
        }

        return $response;
    }


	public function showEditModal(Request $request)
    {
        try {
            $allInputs = $request->all();
            $id = $request->get('id');
            $fieldName = $request->get('field_name');
            $user_id = Auth::user()->id;

              $OH = new OH();
              $department = $OH->fetchData('department');
              $position = $OH->fetchData('position');

             $is_king = Staff::where('systemid',globaldata::where('property','admin_system_id')->first()->value)->first()->user_id == $user_id;

             $is_g_King = Staff::where('systemid',globaldata::where('property','admin_system_id')->first()->value)->first()->user_id == $id;

            $is_this_user_sadmin = usersrole::where([
                'role_id'=>role::where('name','sadmin')->first()->id,
                'user_id' =>  $user_id,
              ])->first();

              $is_g_user_sadmin = usersrole::where([
                'role_id'=>role::where('name','sadmin')->first()->id,
                'user_id' => $id,
              ])->first();




            $is_king = ($is_king) ? true:false;
            $is_g_King = ($is_g_King) ? true:false;
            $is_this_user_sadmin = ($is_this_user_sadmin) ? true:false;
            $is_g_user_sadmin = ($is_g_user_sadmin) ? true:false;

            $is_self = ($id == $user_id) ? true:false;

            $validation = Validator::make($allInputs, [
                'id' => 'required',
                'field_name' => 'required'
            ]);

            if ($validation->fails()) {
                $response = (new ApiMessageController())->
                validatemessage($validation->errors()->first());

            } else {
                $userData = User::where('id', $id)->with('staff')->first();
                $role =  \App\Models\usersrole::where('user_id',$id)->get();

                if($fieldName == 'status') {
                  if( $userData->email == null or $userData->password == '' or $userData->name == '') {
                    return '';
                  }
                }

                

                return view('superadmin.edit-data-modal',
                    compact('id', 'fieldName', 'userData','role','is_king',
                    'is_g_King','is_this_user_sadmin',
                    'is_g_user_sadmin','is_self','department','position'));
            }

        } catch (\Illuminate\Database\QueryException $ex) {
            $response = (new ApiMessageController())->queryexception($ex);
        }

        return $response;
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
                     $msg = "Validation error";
                   return view('layouts.dialog',compact('msg'));

            } else {

                     $is_king =  Staff::where('systemid',globaldata::where('property','admin_system_id')->first()->value)->first()->user_id == $id;
                      if ($is_king) {
                     $msg = "You can't change King Status";
                      return view('layouts.dialog',compact('msg'));
                      }


               $is_this_user_sadmin = usersrole::where([
                'role_id'=>role::where('name','sadmin')->first()->id,
                'user_id' => Auth::user()->id
              ])->first();

               $is_this_user_king = Staff::where('systemid',globaldata::where('property','admin_system_id')->first()->value)->first()->user_id == Auth::user()->id;

              $is_given_id_sadmin = usersrole::where([
                'role_id'=>role::where('name','sadmin')->first()->id,
                'user_id' => $id
              ])->first();

              if ($is_given_id_sadmin) {
                if (!$is_this_user_sadmin and !$is_this_user_king) {
                         $msg = "You can't change secondary adminstrator.";
                        return view('layouts.dialog',compact('msg'));
                }
              } 


                $userData = User::where('id', $id)->with('staff')->first();
                $staffData = Staff::where('user_id', $id)->first();

               if (!$userData or !$staffData) {
                    $msg = "Error: Record broken";
                   return view('layouts.dialog',compact('msg'));
                }

                if ($userData->email != '' or $userData->password != '') {
                    $userData->status = $status;
                    $update = $userData->save();


                    $staffData->status = $status;
                    $updateStaff = $staffData->save();

                    if ($update && $updateStaff) {
                     $msg = "Status updated";
                    return view('layouts.dialog',compact('msg'));
                    } else {
                        $msg = "Error occured";
                    return view('layouts.dialog',compact('msg'));
                    }   

                } else {
                     $msg = "Cannot update active status because email/Password not defined.";
                     return view('layouts.dialog',compact('msg'));
                }
            }

        } catch (\Illuminate\Database\QueryException $ex) {
            $response = (new ApiMessageController())->queryexception($ex);
        }

        return $response;
    }

public function updateUserRole(Request $request) {
        //$request->roles => role ID
        try {
            $allInputs = $request->all();
            $id = $request->get('id');
            $role = $request->get('roles');
            $fieldName = 'msg_dilog';

            $validation = Validator::make($allInputs, [
                'id' => 'required',
                "roles"    => "array|min:0",
                "roles.*"  => "string|distinct|min:0",
            ]);

            if ($validation->fails()) {
                     $msg = "Validation error";
                return view('layouts.dialog',compact('msg'));

            } else {

              $is_king = Staff::where('systemid',globaldata::where('property','admin_system_id')->first()->value)->first()->user_id == $id;

              if ($is_king) {
                if ($id != Auth::user()->id) {
                        $msg = "You can't change King";
                      return view('layouts.dialog',compact('msg'));
                } else {
                       
                       $role = array_map(function($role) {
                           return $role['name'];
                       }, role::where('superadmin',true)->get()->toArray());
                  }
              }

              $is_this_user_sadmin = usersrole::where([
                'role_id'=>role::where('name','sadmin')->first()->id,
                'user_id' => Auth::user()->id
              ])->first();

               $is_this_user_king = Staff::where('systemid',globaldata::where('property','admin_system_id')->first()->value)->first()->user_id == Auth::user()->id;

              $is_given_id_sadmin = usersrole::where([
                'role_id'=>role::where('name','sadmin')->first()->id,
                'user_id' => $id
              ])->first();

              if ($is_given_id_sadmin) {
                   if (!$is_this_user_sadmin and !$is_this_user_king) {
                         $msg = "You can't change secondary adminstrator.";
                      return view('layouts.dialog',compact('msg'));
                      }
              } 

              if ((!$is_this_user_king && !$is_this_user_sadmin) || $is_king) {
                       $role = array_filter($role,function($role) {
                        return $role != 'sadmin';
                    });
              }

              if (!$is_this_user_king && $id == Auth::user()->id) {
                return null;
              }
              

                usersrole::where('user_id', $id)->delete();

                if (is_array($role)) {

                    foreach ($role as $value) {

                        $role_detail = role::where('name',$value)->first();

                        $is_exist = usersrole::where([
                            'user_id'=>$id,
                            'role_id'=>$role_detail->id
                        ])->withTrashed()->first();

                        if ($is_exist) {
                            $is_exist->deleted_at = null;
                            $is_exist->save();
                        } else {
                            usersrole::create([
                                'user_id'=>$id,
                                'role_id'=>$role_detail->id,
                                'company_id'=>0
                            ]);
                        }
                    }
                }

                
                if ($is_king) {
                        if ($id == Auth::user()->id) {
                               $msg = "King role cannot be modified";
                               return view('layouts.dialog',compact('msg'));
                        } 
              }
              
                    $msg = "Admin roles updated";
                     return view('layouts.dialog',compact('msg'));
            }

        } catch (\Illuminate\Database\QueryException $e) {

                    $msg = "Some error occured";
             return view('layouts.dialog',compact('msg'));
        }

        return $response;
    }

 public function updateUserFields(Request $request)
    {

        try {

            $id = $request->user_id;
            $superKing = Staff::where('systemid',globaldata::where('property','admin_system_id')->first()->value)->first()->user_id;
            $validation = Validator::make($request->all(), [
                'password' => 'sometimes|nullable|string|min:6|confirmed',
                'email' => 'sometimes|nullable|string|email|max:255|unique:users,email,' . $id,
            ]);

              $is_king =   $superKing == $id;
              $changed = false;

              if ($is_king) {
                if ($id != Auth::user()->id) {
                             $msg = "You can't change the King";
                       
                      return view('layouts.dialog',compact('msg'));
                }
              }



            if ($validation->fails() && ($request->filled('password') || $request->filled('password_confirmation'))) {

                $msg = "Error: Verify Password and Password need to be matched with minimum 6 characters.";
                     return view('layouts.dialog',compact('msg'));
            } else {

            $is_this_user_sadmin = usersrole::where([
                'role_id'=>role::where('name','sadmin')->first()->id,
                'user_id' => Auth::user()->id
              ])->first();

               $is_this_user_king =  $superKing == Auth::user()->id;

              $is_given_id_sadmin = usersrole::where([
                'role_id'=>role::where('name','sadmin')->first()->id,
                'user_id' => $id
              ])->first();

              if ($is_given_id_sadmin) {
                if (!$is_this_user_sadmin and !$is_this_user_king) {
                         $msg = "You can't change secondary adminstrator.";
                    return view('layouts.dialog',compact('msg'));
                }
              }


                $userModel = User::find($request->user_id);
                $staffData = Staff::where('user_id', $request->user_id)->first();

                if (!$userModel or !$staffData) {
                           $msg = "Error: Record broken";
                      
                       return view('layouts.dialog',compact('msg'));
                }


                if ($request->has('email')) {
                    
                    $check = \App\User::where([['email','=',$request->email],['id','<>',$request->user_id]]
                    )->get()->count();

                    if ($userModel->email != $request->email) {

                    $userModel->email = $request->email;
                    if ($check > 0 && $request->email != '') {
                         $msg = "Email already exists, please enter email that is not in the system";
                         return view('layouts.dialog',compact('msg'));
                    }
                     $changed = true;
                   }
                  }

                if ($request->has('password') && $request->filled('password') && $request->filled('password_confirmation')) {
                    $userModel->password = bcrypt($request->password);
                     $changed = true;
                }

                if ($request->has('name')) {
                    if($userModel->name != $request->name) {
                    $userModel->name = $request->name;
                    $changed = true;
                    }
                }


                if ($changed == true) {
                $updateUser = $userModel->save();
                $updateStaff = $staffData->save();
                } else {
                  return abort(404);
                }
                if ($updateUser && $updateStaff) {

                     $msg = "User updated successfully";
                        return view('layouts.dialog',compact('msg'));

                } else {
                        $msg = "Failed to update data";
                        return view('layouts.dialog',compact('msg'));
                }
            }

        } catch (\Illuminate\Database\QueryException $e) {
                         $msg = "Failed to update data";
                         return view('layouts.dialog',compact('msg'));
        }

        return $response;
    }

	public function destroy($id)
    {
        try {

            if (auth()->id() == $id) {
				$msg = "Active user cannot be deleted";
				return view('layouts.dialog',compact('msg'));

            } else {
				$is_g_sadmin = usersrole::where([
					'role_id'=>role::where('name','sadmin')->first()->id,
					"user_id"=>$id])->first();

				$is_this_admin = usersrole::where([
					'role_id'=>role::where('name','sadmin')->first()->id,
					"user_id"=>Auth::user()->id])->first();

				$is_this_user_king = Staff::where('systemid',
					globaldata::where('property','admin_system_id')->
					first()->value)->
					first()->user_id == Auth::user()->id;

				$is_this_g_king = Staff::where('systemid',
					globaldata::where('property','admin_system_id')->
					first()->value)->first()->user_id == $id;

				if ($id == Auth::user()->id) {
					$msg = "Can't delete self account.";
					return view('layouts.dialog', compact('msg'));
				}

				if ($is_g_sadmin) {
					if (!$is_this_admin and !$is_this_user_king ) {
						$msg = "Can't delete secondary adminstrator.";
						return view('layouts.dialog',compact('msg'));
					}
				}

				if ($is_this_g_king ) {
					$msg = "Can't delete King.";
					return view('layouts.dialog',compact('msg'));
				}

				User::where('id', $id)->delete();
				Staff::where('user_id', $id)->delete();
				$msg = "Administrator deleted successfully";

				return view('layouts.dialog',compact('msg'));
			}
        } catch (\Illuminate\Database\QueryException $e) {
			$msg = $e->getMessage();

			Log::error("Error @ " . $e->getLine() . " file " . $e->getFile() .
				":" . $msg
			);

			return view('layouts.dialog',compact('msg'));
            // $response = (new ApiMessageController())->queryexception($ex);
        }

        return $response;
    }


    public function showLandingView() {	
        $id = Auth::user()->id;
        $user_roles = usersrole::where('user_id',$id)->get();

        $is_king =  Staff::where('systemid',
			globaldata::where('property','admin_system_id')->
			first()->value)->
			first()->user_id == Auth::user()->id;

        $superAdmin = true;
        return view('superadmin.landing', compact([
			'superAdmin','user_roles','is_king'
		]));
    }


    public function showSuperadminView() {
        return view('superadmin.superadmin');
    }
}
