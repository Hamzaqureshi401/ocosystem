<?php

namespace App\Http\Controllers;

use DB;
use App\Models\product;
use Illuminate\Http\Request;
use App\Models\terminal;
use App\Models\location;
use App\Models\locationterminal;
use App\Models\merchantproduct;
use App\Models\merchantlocation;

class OposScreenEController extends Controller
{

    public function index(Request $request)
    {

        $terminal_id = (\Session::get('terminalID'));
        $terminal = strval($terminal_id);
        $terminal = terminal::where('systemid', $terminal)->first();
        //$locationterminal = locationterminal::where('terminal_id', $terminal->id)->first();
        $location_id = $request->location_id;
        $location = location::where('id',$location_id)->first();
        $merchantlocation = merchantlocation::where('location_id',$location_id)->first();
        $product_ids = merchantproduct::where('merchant_id',$merchantlocation->merchant_id)->pluck('product_id');

		$company_details = DB::table('company')->find($merchantlocation->merchant_id);

		if (!empty($company_details->corporate_logo))
			$logo = "logo/$company_details->id/$company_details->corporate_logo";
		$logo = $logo ?? null;
		return view('opossum/opossum_e')->with(['terminal' => $terminal, 
			'product_ids' => $product_ids, 'location' => $location, "logo" => $logo]);
    }

    public function get_product_ids(Request $request)
    {

        $terminal_id = (\Session::get('terminalID'));
        $terminal = strval($terminal_id);
        $terminal = terminal::where('systemid', $terminal)->first();
        //$locationterminal = locationterminal::where('terminal_id', $terminal->id)->first();
        $location_id = $request->location_id;
        $merchantlocation = merchantlocation::where('location_id',$location_id)->first();
        $product_ids = merchantproduct::where('merchant_id',$merchantlocation->merchant_id)->pluck('product_id');

        return response()->json($product_ids);
    }
}


