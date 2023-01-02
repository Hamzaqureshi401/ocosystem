<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Matrix\Exception;
use Yajra\DataTables\DataTables;

class CRMController extends Controller
{
    //
public function __construct()
    {
        $this->middleware('auth');
         $this->middleware('CheckRole:crm');
    }
    

	public function index()
    {
        return Datatables::of(array('',''))
            ->addIndexColumn()
            ->addColumn('crm_id', function ($staffList) {
                return '<p data-field="crm_item" style="cursor: pointer; margin: 0; color: blue;">0000</p>';

            })
            ->addColumn('crm_item', function ($staffList) {
                return '<p data-field="crm_item" style="cursor: pointer; margin: 0; color: blue;">Dummy</p>';
            })
            ->addColumn('crm_customer', function ($staffList) {
                return '<p data-field="crm_customer" style="cursor: pointer; margin: 0; color: blue;">Dummy</p>';
            })
            ->addColumn('crm_contact', function ($staffList) {

                return '<p data-field="crm_contact" style="cursor: pointer; margin: 0; color: blue;">Dummy</p>';

            })
            ->addColumn('status', function ($staffList) {
                return '<p data-field="status" style="cursor: pointer;  margin: 0; color: blue;">Dummy</p>';
            })
             ->addColumn('crm_quotation', function ($staffList) {
                return '<p data-field="crm_quotation" style="cursor: pointer;  margin: 0; color: blue;">00.00</p>';
            })
            ->addColumn('deleted', function ($staffList) {
                return '<p id="deleted" data-field="deleted"
                    style="background-color:red;
                    border-radius:5px;margin:auto; 
                    width:25px;height:25px;
                    display:block;cursor: pointer;text-align: center;" 
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


	public function showCRM() {
		return view('crm.crm');
	}


    // public function showEditModal(Request $request)
    // {
    //     try {
    //         $allInputs = $request->all();
    //         $id = $request->get('id');
    //         $fieldName = $request->get('field_name');


    //         $validation = Validator::make($allInputs, [
    //             'id' => 'required',
    //             'field_name' => 'required'
    //         ]);

    //         if ($validation->fails()) {
    //             $response = (new ApiMessageController())->
    //             validatemessage($validation->errors()->first());

    //         } else {
    //             $userData = User::where('id', $id)->with('staff')->first();
    //             return view('staff.edit-staff-modal',
    //                 compact('id', 'fieldName', 'userData'));
    //         }

    //     } catch (\Illuminate\Database\QueryException $ex) {
    //         $response = (new ApiMessageController())->queryexception($ex);
    //     }

    //     return $response;
    // }

    public function deleteCRM(Request $request) {
		return response("deleted", 200);
	}
}
