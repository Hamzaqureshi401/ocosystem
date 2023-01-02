<?php

namespace App\Http\Controllers;

use App\Classes\PTS2;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

class ATGController extends Controller
{
    public function getProbesConfigurations(Request $request) {

//        $validator = Validator::make($request->all(), [
//            'username' => 'required|string',
//            'password' => 'required|string',
//            'url' => 'required|url',
//        ]);
//
//        if ($validator->fails()) {
//            return response()->json($validator->messages(),422);
//        }
//
//        $pts2_obj = new PTS2($request->username,$request->password,$request->url);
        
        $pts2_obj = new PTS2(null,null,null);
        $result = $pts2_obj->get_probes_configuration();

        return response()->json($result['response']);
    }

    public function setProbesConfigurations(Request $request) {

        $validator = Validator::make($request->all(), [
//            'username' => 'required|string',
//            'password' => 'required|string',
//            'url' => 'required|url',

            'ports' => 'required|array',
            'ports.*.Id' => ['required','string',Rule::in(['DISP','LOG','USER'])],
            'ports.*.Protocol' => 'required|integer|between:1,99',
            'ports.*.BaudRate' => 'required|integer|between:1,99',

            'probes' => 'required|array',
            'probes.*.Id' => 'required|integer|between:1,64',
            'probes.*.Port' =>  ['required','string',Rule::in(['DISP','LOG','USER'])],
            'probes.*.Address' => 'required|integer|between:1,999999',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(),422);
        }

//        $pts2_obj = new PTS2($request->username,$request->password,$request->url);

        $pts2_obj = new PTS2(null,null,null);
        $result = $pts2_obj->set_probes_configuration($request->ports,$request->probes);

        return response()->json($result['response']);
    }

    public function getTanksConfigurations(Request $request) {

//        $validator = Validator::make($request->all(), [
//            'username' => 'required|string',
//            'password' => 'required|string',
//            'url' => 'required|url',
//        ]);
//
//        if ($validator->fails()) {
//            return response()->json($validator->messages(),422);
//        }
//
//        $pts2_obj = new PTS2(null,null,null);

        $pts2_obj = new PTS2(null,null,null);
        $result = $pts2_obj->get_tanks_configuration();

        return response()->json($result['response']);
    }

    public function setTanksConfigurations(Request $request) {

        $validator = Validator::make($request->all(), [
//            'username' => 'required|string',
//            'password' => 'required|string',
//            'url' => 'required|url',

            'tanks' => 'required|array',
            'tanks.*.Id' => 'required|integer|between:1,32',
            'tanks.*.FuelGradeId' => 'required|integer|between:1,10',
            'tanks.*.Height' => 'required|integer|between:1,99999',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(),422);
        }

//        $pts2_obj = new PTS2($request->username,$request->password,$request->url);

        $pts2_obj = new PTS2(null,null,null);
        $result = $pts2_obj->set_tanks_configuration($request->tanks);

        return response()->json($result['response']);
    }

    public function probeGetMeasurements(Request $request) {

        $validator = Validator::make($request->all(), [
//            'username' => 'required|string',
//            'password' => 'required|string',
//            'url' => 'required|url',
            'probe_no' => 'required|integer|between:1,32',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(),422);
        }

//        $pts2_obj = new PTS2($request->username,$request->password,$request->url);

        $pts2_obj = new PTS2(null,null,null);
        $result = $pts2_obj->probe_get_measurements($request->probe_no);

        return response()->json($result['response']);
    }

    public function probeGetTankVolumeHeight(Request $request) {

        $validator = Validator::make($request->all(), [
//            'username' => 'required|string',
//            'password' => 'required|string',
//            'url' => 'required|url',
            'probe_no' => 'required|integer|between:1,32',
            'height' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(),422);
        }

//        $pts2_obj = new PTS2($request->username,$request->password,$request->url);

        $pts2_obj = new PTS2(null,null,null);
        $result = $pts2_obj->probe_get_tank_volume_for_height($request->probe_no,$request->height);

        return response()->json($result['response']);
    }
}
