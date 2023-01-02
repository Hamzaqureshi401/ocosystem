<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OposPvoucher;
use Auth;
use App\Http\Controllers\ApiMessageController;

class OposPaperVoucherController extends Controller
{
    public function __construct() {
        $this->middleware('auth');
    }
    
    public function index(Request $request){
        $data = OposPvoucher::orderBy('created-at', 'desc')->get();
        return $response;
    }

    public function create(Request $request){
        try {
            
            OposPvoucher::create($request->all());
            $response = (new ApiMessageController())->successResponse([],
                    "Data saved successfully");

        } catch (\Exception $e) {
            $response = (new ApiMessageController())->queryexception($e);
        }
        return $response;
    }

    public function get($id) {
        try{
            $data = OposPvoucher::where('id', $id)->
				with('user')->first();

            return response()->json(['data' => $data]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e]);
        }
    }
}
