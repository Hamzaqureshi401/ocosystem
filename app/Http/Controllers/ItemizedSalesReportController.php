<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \Illuminate\Support\Facades\Auth;
use PDF;
use DB;


class ItemizedSalesReportController extends Controller
{
    public function showItemizedSalesReportView(){

		$user = DB::table('company')->
			where('owner_user_id' , Auth::user()->id)->first();

		if ($user == false){ // no record exist
			$user = DB::table('staff')->
				where('user_id' , Auth::user()->id)->first();
			// Fetch record from staff where user_id = auth id get company id
			$company_id = $user->company_id;
		} else {
			 // Fetch record from company where owner_user_id = auth id
			 // get company id
			 $company_id = $user->id;
		}

		$locations = DB::table('franchisemerchant')->
		 	join('franchisemerchantloc',
			'franchisemerchantloc.franchisemerchant_id',
			'franchisemerchant.id')->
			join('location' , 'location.id',
				'franchisemerchantloc.location_id')->
			where('franchisemerchant.franchisee_merchant_id' , $company_id)->
			get();
		 $query = "select l.branch , l.systemid from merchantlocation ml, location l where ml.location_id = l.id and ml.merchant_id = $company_id";
		 $locations = DB::select(DB::raw($query));
		//dd($locations);

		$date =  date('dMy');

		return view('itemized_sales_report.itemized_sales_report',
			compact('locations' , 'date'));
    }
}
