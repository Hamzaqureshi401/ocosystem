<?php

namespace App\Http\Controllers;

use App\Models\Individual;
use App\Models\Staff;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

/**
 * Class StaffController
 * @package App\Http\Controllers
 */
class IndividualController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
         $this->middleware('CheckRole:prt');
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function index()
    {
        $model = new Individual();
        $individualList = $model->latest()
            ->get();

        return Datatables::of($individualList)
            ->addIndexColumn()
            ->addColumn('partner_id', function ($individualList) {
                return  sprintf("%'.09d\n", $individualList->id);

            })
            ->addColumn('name', function ($individualList) {
                return '<p data-field="partner_name" style="cursor: pointer; margin: 0; color: blue;">' . (!empty($individualList->name) ?
                        ucfirst($individualList->name) : 'Name')
                    . '</p>';
            })
            ->addColumn('status', function ($individualList) {
                return '<p data-field="status" style="cursor: pointer;  margin: 0; color: blue;">' . ucfirst($individualList->status) . '</p>';
            })
            ->addColumn('deleted', function ($individualList) {
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
            Individual::create(['name' => ucfirst('name'), 'type' => ucfirst('introducer'), 'status' => 'pending']);
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

        try {
            Individual::where('id', $id)->delete();
            $response = (new ApiMessageController())->saveresponse('Individual deleted successfully');
        } catch (\Illuminate\Database\QueryException $ex) {
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
                $individualData = Individual::where('id', $id)->first();
                return view('individual.edit-individual-modal',
                    compact('id', 'fieldName', 'individualData'));
            }

        } catch (\Illuminate\Database\QueryException $ex) {
            $response = (new ApiMessageController())->queryexception($ex);
        }

        return $response;
    }


    public function updateIndividualFields(Request $request)
    {

        try {

            $id = $request->id;


            $individual = Individual::find($id);

         

            if ($request->has('name')) {
                $individual->name = $request->name;
            }

            if ($request->has('company')) {
                $individual->company_name = $request->company;
            }

            $updateIndividual = $individual->save();

            if ($updateIndividual) {

                $response = (new ApiMessageController())->successResponse([],
                    "Data updated successfully");

            } else {
                $response = (new ApiMessageController())->failedresponse("Failed to update data");
            }


        } catch (\Illuminate\Database\QueryException $e) {
            $response = (new ApiMessageController())->queryexception($e);
        }

        return $response;
    }

    public function updateIndividualStatus(Request $request)
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
                $individualData = Individual::where('id', $id)->first();

                $individualData->status = $status;
                $update = $individualData->save();

                if ($update) {
                    $response = (new ApiMessageController())->saveresponse('Status updated successfully!');
                } else {
                    $response = (new ApiMessageController())->failedresponse('Status updated successfully!');
                }
            }

        } catch (\Illuminate\Database\QueryException $ex) {
            $response = (new ApiMessageController())->queryexception($ex);
        }

        return $response;
    }


}
