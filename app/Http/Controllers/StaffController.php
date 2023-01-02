<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Matrix\Exception;
use Yajra\DataTables\DataTables;

/**
 * Class StaffController
 * @package App\Http\Controllers
 */
class StaffController extends Controller
{

    /**
     * @return mixed
     * @throws \Exception
     */
    public function index()
    {
        $model = new User();
        $staffList = $model->with('staff')->latest()
            ->get();

        return Datatables::of($staffList)
            ->addIndexColumn()
            ->addColumn('staff_id', function ($staffList) {
                return '<p data-field="staff_id" style="cursor: pointer; margin: 0; color: blue;">' . sprintf("%'.09d\n", $staffList->id)
                    . '</p>';

            })
            ->addColumn('name', function ($staffList) {
                return '<p data-field="staff_name" style="cursor: pointer; margin: 0; color: blue;">' . (!empty($staffList->name) ?
                        $staffList->name : 'Name')
                    . '</p>';
            })
            ->addColumn('type', function ($staffList) {

                return '<p data-field="staff_role" style="cursor: pointer; margin: 0; color: blue;">' . (!empty($staffList->type) ?
                        $staffList->type : 'Roles')
                    . '</p>';

            })
            ->addColumn('status', function ($staffList) {
                return '<p data-field="status" style="cursor: pointer;  margin: 0; color: blue;">' . ucfirst($staffList->status) . '</p>';
            })
            ->addColumn('deleted', function ($staffList) {
                return '<p data-field="deleted"
                    style="background-color:red;
                    border-radius:5px;margin:auto; 
                    width:25px;height:25px;
                    display:block;cursor: pointer;" 
					class="text-danger">
					<i class="fas fa-times text-white"
					style="color:white;opacity:1.0;
					padding-top:4px;
                    -webkit-text-stroke: 1px red;"></i></p>
					<span style="display:none;"</span>';
            })
            ->escapeColumns([])
            ->make(true);
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
        //Create a new user here
        try {
            $userModel = new User();
            $userModel->type = 'roles';
            $userModel->status = 'pending';
            $userModel->save();

            $staffModel = new Staff();
            $staffModel->user_id = $userModel->id;
            $staffModel->save();

            $response = (new ApiMessageController())->successResponse([],
                "Data saved successfully");

        } catch (\Exception $e) {
            $response = (new ApiMessageController())->queryexception($e);
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

            if (auth()->id() == $id) {
                $response = (new ApiMessageController())->failedresponse('Active user cannot be deleted');
            } else {
                User::where('id', $id)->delete();
                $response = (new ApiMessageController())->saveresponse('User deleted successfully!');
            }
        }catch (\Illuminate\Database\QueryException $ex) {
            $response = (new ApiMessageController())->queryexception($ex);
        }

        return $response;
    }


    public function showEditModal(Request $request)
    {
        try {
            $allInputs = $request->all();
            $id = $request->get('id');
            $fieldName = $request->get('field_name');


            $validation = Validator::make($allInputs, [
                'id' => 'required',
                'field_name' => 'required'
            ]);

            if ($validation->fails()) {
                $response = (new ApiMessageController())->
                validatemessage($validation->errors()->first());

            } else {
                $userData = User::where('id', $id)->with('staff')->first();
                return view('staff.edit-staff-modal',
                    compact('id', 'fieldName', 'userData'));
            }

        } catch (\Illuminate\Database\QueryException $ex) {
            $response = (new ApiMessageController())->queryexception($ex);
        }

        return $response;
    }


    public function updateStaffFields(Request $request)
    {

        try {

            $id = $request->user_id;

            $validation = Validator::make($request->all(), [
                'password' => 'sometimes|nullable|string|min:6|confirmed',
                'email' => 'sometimes|nullable|string|email|max:255|unique:users,email,' . $id,
            ]);

            if ($validation->fails()) {

                $response = (new ApiMessageController())->validatemessage($validation->errors(), $validation->errors()->first());
            } else {

                $userModel = User::find($request->user_id);

                $staffData = Staff::where('user_id', $request->user_id)->first();

                if ($request->has('email')) {
                    $userModel->email = $request->email;
                }

                if ($request->has('password') && $request->filled('password')) {

                    $userModel->password = bcrypt($request->password);
                }


                if ($request->has('name')) {

                    $userModel->name = $request->name;
                }



                if ($request->has('department')){
                    $staffData->department = $request->department;
                }

                if ($request->has('position')){
                    $staffData->position = $request->position;
                }

                $updateUser = $userModel->save();
                $updateStaff = $staffData->save();

                if ($updateUser && $updateStaff) {

                    $response = (new ApiMessageController())->successResponse([],
                        "Data updated successfully");

                } else {
                    $response = (new ApiMessageController())->failedresponse("Failed to update data");
                }
            }

        } catch (\Illuminate\Database\QueryException $e) {
            $response = (new ApiMessageController())->queryexception($e);
        }

        return $response;
    }

    public function updateStaffStatus(Request $request)
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
                $response = (new ApiMessageController())->
                validatemessage($validation->errors()->first());

            } else {
                $userData = User::where('id', $id)->with('staff')->first();
                $staffData = Staff::where('user_id', $id)->first();

                $userData->status = $status;
                $update = $userData->save();


                $staffData->status = $status;
                $updateStaff = $staffData->save();



                if ($update && $updateStaff) {
                    $response = (new ApiMessageController())->saveresponse('Status updated successfully!');
                }else{
                    $response = (new ApiMessageController())->failedresponse('Status updated successfully!');
                }
            }

        } catch (\Illuminate\Database\QueryException $ex) {
            $response = (new ApiMessageController())->queryexception($ex);
        }

        return $response;
    }


}
