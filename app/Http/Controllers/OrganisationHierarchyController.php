<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use \App\Classes\UserData;
use \App\Models\Company;
use \App\Models\Staff;
use \App\Models\organisationhierarchy as OH;

class OrganisationHierarchyController extends Controller
{
    protected $user_data;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('CheckRole:stg');
    }

    public function fetchData($type)
    {
        $this->user_data = new UserData();
        $company_id      = $this->user_data->company_id();
        $data            = OH::where(['company_id' => $company_id, 'type' => $type])->get();
        return $data;
    }

    public function showModel(Request $request)
    {
        $this->user_data = new UserData();
        $getData         = true;
        $company_id      = $this->user_data->company_id();
        $validation      = Validator::make($request->all(), [
            'addType' => 'required',
        ]);

        if ($validation->fails()) {
            exit();
        }

        $type = $request->addType;

        if ($type == 'deleted') {
            $deleted = true;
            $id      = $request->data;
            return view('settings.general', compact(['deleted', 'type', 'id']));
        }

        $this_company = Company::findOrFail($company_id);
        return view('settings.general', compact(['getData', 'type', 'this_company']));
    }

    public function destory(Request $request)
    {
        try {
            $this->user_data = new UserData();
            $validation      = Validator::make($request->all(), [
                'id' => 'required',
            ]);

            if ($validation->fails()) {
                exit();
            }

            $is_exist = OH::where(['company_id' => $this->user_data->company_id(), "id" => $request->id])->first();

            if (!$is_exist) {
                throw new Exception("Error Processing Request", 1);
            }

            $is_used = Staff::where('department',$is_exist->field_value)->first();
            $is_used_ii = Staff::where('position',$is_exist->field_value)->first();

            if ($is_used || $is_used_ii) {
            $msg_dilog = ucfirst($is_exist->type)." in use, cannot be deleted";
            $OH_done   = 'true';
            return view('settings.general', compact('msg_dilog', 'OH_done'));
            }

            $type = $is_exist->type;
            $is_exist->delete();


            $msg_dilog = ucfirst($type)." removed";
            $OH_done   = 'true';
            return view('settings.general', compact('msg_dilog', 'OH_done'));

        } catch (\Exception $e) {

            $msg = $e;//"Eror occured.";
            return view('layouts.dialog', compact('msg'));

        }
     
    }

    public function store(Request $request)
    {

        try {
            $this->user_data = new UserData();
            $validation      = Validator::make($request->all(), [
                'addType' => 'required',
                'keyName' => 'required',
            ]);

            $this->user_data = new UserData();

            if ($validation->fails()) {
                exit();
            }

            $is_exist = OH::where(['company_id' => $this->user_data->company_id(), "field_value" => $request->keyName, 'type' => $request->addType])->first();

            if ($is_exist) {

                $msg_dilog = ucfirst($request->addType) . " already exist.";
                return view('settings.general', compact('msg_dilog'));
            }

            $OH              = new OH();
            $OH->company_id  = $this->user_data->company_id();
            $OH->field_value = $request->keyName;
            $OH->type        = $request->addType;
            $OH->save();

            $msg_dilog = ucfirst($request->addType) . " added";
            $OH_done   = 'true';
            return view('settings.general', compact('msg_dilog', 'OH_done'));

        } catch (\Exception $e) {

            $msg_dilog = "Eror occured.";
            return view('settings.general', compact('msg_dilog'));

        }

    }
}
