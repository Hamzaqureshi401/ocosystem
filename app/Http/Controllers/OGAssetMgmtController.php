<?php

namespace App\Http\Controllers;

use App\Classes\SystemID;
use App\Models\location;
use App\Models\OgAssetManagement;
use Dompdf\Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class OGAssetMgmtController extends Controller
{
    public function assetMangLoad()
    {
        return view("industry.service_maintenance.asset_load");
    }

    public function assetValues()
    {
        return view("industry.service_maintenance.asset_values");
    }

    public function assetServiceBook()
    {
        return view("industry.service_maintenance.asset_service_book");
    }

    public function assetProduct()
    {
        return view("industry.service_maintenance.asset_product");
    }

    public function index()
    {
        
    }

    public function create()
    {
        $locations=location::query()->pluck('branch','id');
        return view("industry.service_maintenance.add_asset",compact('locations'));
    }

    public function store(Request  $request)
    {
        try {

            Validator::make($request->all(), [
                'name' => 'required|string',
                'value' => 'required|integer',
                'location_id' => 'required|integer',
            ]);
            // check duplicate form request
            if ($request->unique_token != Session::get('unique_token')) {
                //return back()->with('error','Duplicate request found.');
            }
                $token = md5(session_id() . time());
                Session::put('unique_token', $token);
            // end off check duplicate form request
            $system_id = new SystemID('debitnote');
            $systemid= $system_id-> __toString();
            $request['systemid']=$systemid;
            OgAssetManagement::create($request->except('_token','unique_token'));
            DB::commit();
            return back()->with('success','Add asset successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return  $e;
            return back()->with('error',$e->getMessage());
        }
    }


}
