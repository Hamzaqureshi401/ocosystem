<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

/**
 * Class StaffController
 * @package App\Http\Controllers
 */
class CompanyController extends Controller
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
        $companyList = DB::table('company')->orderBy('id','DESC')->get();

        return Datatables::of($companyList)
            ->addIndexColumn()
            ->addColumn('company_id', function ($companyList) {
                return  sprintf("%'.09d\n", $companyList->id);

            })
            ->addColumn('name', function ($companyList) {
                return '<p data-field="company_Name" style="cursor: pointer; margin: 0; color: blue;">'."<a href='/company/company/{$companyList->id}' target='_blank' style='text-decoration: none;color: #00f;'>" . (!empty($companyList->name) ?
                        "". $companyList->name .'': 'Company Name')
                    . '</a></p>';
            })
            ->addColumn('type', function ($companyList) {

                return (!empty($companyList->type) ?
                        ucfirst($companyList->type): 'Introducer')
                   ;

            })
            ->addColumn('status', function ($companyList) {
                return '<p data-field="status" style="cursor: pointer;  margin: 0; color: blue;">' . ucfirst($companyList->status) . '</p>';
            })
            ->addColumn('deleted', function ($companyList) {
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
            Company::create(['name' => ucfirst('company name'), 'type' => ucfirst('introducer'), 'status' => 'pending']);

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
                Company::where('id', $id)->update(['name' => $request->name]);
            } elseif ($request->type === 'approval') {
                $status = $request->status;
                if ($status == 'true') {
                    $approve = 'active';
                } else {
                    $approve = 'inactive';
                }

                Company::where('id', $id)->update(['status' => $approve]);
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
            \DB::table('company')->where('id', $id)->delete();
            $response = (new ApiMessageController())->saveresponse('Data Deleted Successfully!');
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
                $companyData = Company::where('id', $id)->first();
                return view('company.edit-company-modal',
                    compact('id', 'fieldName', 'companyData'));
            }

        } catch (\Illuminate\Database\QueryException $ex) {
            $response = (new ApiMessageController())->queryexception($ex);
        }

        return $response;
    }


    public function updateCompanyFields(Request $request)
    {

        try {

            $id = $request->id;


            $company = Company::find($id);

            if ($request->has('name')) {
                $company->name = $request->name;
            }

            if ($request->has('company')) {
                $company->company_name = $request->company;
            }

            $updateCompany = $company->save();

            if ($updateCompany) {

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

    public function updateCompanyStatus(Request $request)
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
                $individualData = Company::where('id', $id)->first();

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


    public function showCompanyDetails(Company $company)
    {
        return view('company.company-details')->with('company', $company);
    }


}
