<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use \App\Models\Country;
use \App\Models\Currency;
use \App\Models\Company;
use Log;

use \App\Classes\UserData;

class CountryController extends Controller
{
    //
    protected $user_data;

    public function __construct()
    {
         $this->middleware('auth');
         $this->middleware('CheckRole:stg');
//         $this->user_data = new UserData();
    }

	public function showCountryView() {
        $this->user_data = new UserData();
        $id =  $this->user_data->company_id();

        $company_data = \App\Models\Company::where('id',$id)->first();

		$country =  Country::get();
        $currency = Currency::get();

        if ($company_data) {
        $selected_country = $company_data->country_id;
        $selected_currency  = $company_data->currency_id;
        } else {
        $selected_country = 'null';
        $selected_currency  = 'null';
        }
        return array("country"=>$country,"currency"=>$currency,"selected_country"=>$selected_country,"selected_currency"=>$selected_currency);
	}


	public function updateCountry(Request $request) {
        try {
    	        
            $validation = Validator::make($request->all(), [
                'countryID' => 'required|min:1',
            ]);

            $this->user_data = new UserData();
			$countryID = $request->countryID;            	  
           	$id = $this->user_data->company_id();
            $is_country_id_valid = Country::find($countryID);
            Log::debug('country-'.$is_country_id_valid->name);
            $is_company_exist = \App\Models\Company::where('id',$id)->first();
            	 	
            if (!$is_country_id_valid) {
                $msg_dilog ="Invalid country selected.";
                return view('settings.general',compact('msg_dilog'));
            }
            if (!$is_company_exist) {
                $msg_dilog ="Company doesn't exist.";
                return view('settings.general',compact('msg_dilog'));
            }

            if ($is_company_exist->country_id == $countryID) {
                exit();
            }

            $is_company_exist->country_id = $countryID;
            $is_company_exist->save();

            $msg_dilog ="Country updated.";
            return view('settings.general',compact('msg_dilog'));

        } catch (\Illuminate\Database\QueryException $e) {
            $msg_dilog ="Invalid country selected";
            return view('settings.general',compact('msg_dilog'));
        }
	}

        public function updateCurrency(Request $request) {
            try {
                
                $validation = Validator::make($request->all(), [
                    'currencyID' => 'required|min:1',
                ]);

                $this->user_data = new UserData();
                $currencyID = $request->currencyID;
                $id =  $this->user_data->company_id();
                $is_currency_id_valid = Currency::find($currencyID);
                $is_company_exist = \App\Models\Company::where('id',$id)->first();

                if (!$is_currency_id_valid) {
                    $msg_dilog ="nvalid currency selected";
                    return view('settings.general',compact('msg_dilog'));
                }
                if (!$is_company_exist) {
                    $msg_dilog ="Company doesn't exist.";
                    return view('settings.general',compact('msg_dilog'));           }

                if ($is_company_exist->currency_id == $currencyID) {
                    exit();
                }

                $is_company_exist->currency_id = $currencyID;
                $is_company_exist->save();

                $msg_dilog ="Currency updated";
                return view('settings.general',compact('msg_dilog'));

        } catch (\Illuminate\Database\QueryException $e) {  
            $msg_dilog ="Invalid currency selected.";
            return view('settings.general',compact('msg_dilog'));
        }
    }
}
    