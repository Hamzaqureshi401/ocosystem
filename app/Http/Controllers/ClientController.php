<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
//use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Matrix\Exception;
use Yajra\DataTables\DataTables;

//use DB;

/**
 * Class StaffController
 * @package App\Http\Controllers
 */
class ClientController extends Controller
{

       public function __construct()
        {
        $this->middleware('auth');
        $this->middleware('CheckRole:client');

        }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function index()
    {
        //$model = new Company();
        $clientList = DB::table('client')->orderBy('id','DESC')->get();

        return Datatables::of($clientList)
            ->addIndexColumn()
            ->addColumn('client_id', function ($clientList) {
                return '<p data-field="client_id" style="cursor: pointer; margin: 0; color: blue;">' . sprintf("%'.09d\n", $clientList->id)
                    . '</p>';

            })
            ->addColumn('client_Name', function ($clientList) {
                return '<p data-field="client_Name" style="cursor: pointer; margin: 0; color: blue;">'."<a href='/client/client/{$clientList->id}' target='_blank' style='text-decoration: none;color: #00f;'>" . (!empty($clientList->name) ?
                       "". $clientList->name .'': 'Client Name')
                    . '</a></p>';
            })
            ->addColumn('client_sub', function ($clientList) {

                return '<p data-field="staff_role" style="cursor: pointer; margin: 0; color: blue;">' . (!empty($clientList->sub) ?
                        $clientList->type : 'Sub')
                    . '</p>';

            })
            ->addColumn('status', function ($clientList) {
                return '<p data-field="status" style="cursor: pointer;  margin: 0; color: blue;">' . ucfirst($clientList->status) . '</p>';
            })
            ->addColumn('deleted', function ($clientList) {
                return '<p data-field="deleted"
                    style="background-color:red;
                    border-radius:5px;margin:auto; 
                    width:25px;height:25px;
                    display:block;cursor: pointer;text-align:center" 
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
            
            DB::table('client')->insert(['status'=>'pending']);

            $response = (new ApiMessageController())->successResponse([],
                "Client added successfully");

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
                DB::table('client')::where('id', $id)->
					update(['name' => $request->name]);

            } elseif ($request->type === 'approval') {
                $status = $request->status;
                if ($status == 'true') {
                    $approve = 'active';
                } else {
                    $approve = 'inactive';
                }

                DB::table('client')::where('id', $id)->
					update(['status' => $approve]);
            }
            return response()->json('Client updated successfully', 202);

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

            
            \DB::table('client')->where('id', $id)->delete();
                $response = (new ApiMessageController())->
					saveresponse('Client deleted successfully!');
            
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
                $clientData = DB::table('client')->where('id', $id)->first();
                return view('client.edit-client-modal',
                    compact('id', 'fieldName', 'clientData'));
            }

        } catch (\Illuminate\Database\QueryException $ex) {
            $response = (new ApiMessageController())->queryexception($ex);
        }

        return $response;
    }


    public function updateCompanyFields(Request $request)
    {

        try {
            $id = $request->user_id;

            $validation = Validator::make($request->all(), [
                'password' => 'sometimes|nullable|string|min:6|confirmed',
                'email' => 'sometimes|nullable|string|email|max:255|unique:users,email,' . $id,
            ]);

            if ($validation->fails()) {

                $response = (new ApiMessageController())->
					validatemessage($validation->errors(), $validation->errors()->first());

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
                        "Client updated successfully");

                } else {
                    $response = (new ApiMessageController())->
						failedresponse("Error: Failed to update client");
                }
            }

        } catch (\Illuminate\Database\QueryException $e) {
            $response = (new ApiMessageController())->queryexception($e);
        }

        return $response;
    }

    public function updateClientStatus(Request $request)
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
                
                $clientData = Client::where('id', $id)->first();

                $clientData->status = $status;
                $update = $clientData->update();

                if ($update) {
                    $response = (new ApiMessageController())->
						saveresponse('Status updated successfully!');

                }else{
                    $response = (new ApiMessageController())->
						failedresponse('Error: Status not updated!');
                }
            }

        } catch (\Illuminate\Database\QueryException $ex) {
            $response = (new ApiMessageController())->queryexception($ex);
        }

        return $response;
    }

    public function addClient($id){
       $clientData = DB::table('client')->where('id', $id)->first();
        return view('client.addClient',compact('clientData'));
    }
}
