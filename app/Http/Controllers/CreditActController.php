<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\locationipaddr;
use App\Models\MerchantLink;
use App\Models\MerchantLinkRelation;
use App\Models\OnewayRelation;
use Illuminate\Support\Facades\Auth;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CreditActController extends Controller
{
    //
    public function creditActTransfer($data, $url)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "http://" . $url . "/api/PostDateToOceania",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30000,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => array(
                // Set here requred headers
                "accept: */*",
                "accept-language: en-US,en;q=0.8",
                "content-type: application/json",
            ),
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            //echo "cURL Error #:" . $err;
            Log::error('creditActTranser:' . $err);
            return $err;
        } else {
            return json_decode($response);
        }
    }


    public function twowaytransfer($data, $url)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "http://" . $url . "/api/PostDateToOceaniatwoway",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30000,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => array(
                // Set here requred headers
                "accept: */*",
                "accept-language: en-US,en;q=0.8",
                "content-type: application/json",
            ),
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            //echo "cURL Error #:" . $err;
            Log::error('twowaytransfer:' . $err);
            return $err;
        } else {
            return json_decode($response);
        }
    }

    public static function saveMerchandLinkOceaniadb($data, $user_id,$url , $companyId)
    {
        //dd($companyId, $user_id,$url);
        $curl = curl_init();
        
        curl_setopt_array($curl, array(
            CURLOPT_URL => "http://" . $url . "/api/creditaccount/saveMerchandLink",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30000,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode([
				"mlink" => json_encode($data),
				"user_id" => $user_id,
				"companyId"=>$companyId
			]),
            CURLOPT_HTTPHEADER => array(
                // Set here requred headers
                "accept: */*",
                "accept-language: en-US,en;q=0.8",
                "content-type: application/json",
            ),
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        Log::error('saveMerchandLink response:' . $response);
        if ($err) {
            //echo "cURL Error #:" . $err;
            Log::error('saveMerchandLink:' . $err);
            return $err;
        } else {
            return json_decode($response);
        }
    }

    public static function saveMerchandLinkOneWayOceaniadb($data, $user_id,
		$companyId,$url)
    {
        Log::info("oneway : ".json_encode($data));
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "http://" . $url . "/api/creditaccount/saveMerchandLinkOneWay",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30000,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode([
				"mlink" => json_encode($data),
				"user_id"=>$user_id,
				"companyId"=>$companyId
			]),
            CURLOPT_HTTPHEADER => array(
                // Set here requred headers
                "accept: */*",
                "accept-language: en-US,en;q=0.8",
                "content-type: application/json",
            ),
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        Log::error('saveMerchandLink response:' . $response);
        if ($err) {
            //echo "cURL Error #:" . $err;
            Log::error('saveMerchandLink:' . $err);
            return $err;
        } else {
            return json_decode($response);
        }
    }

    public static function getUserCompany($user_id)
    {
        $company = Company::where("owner_user_id", $user_id)->first();
        return $company;
    }

    public function getMerchantLinkRelationForOceaniaDb(Request $data)
    {
        try {
            $already_exist_mlr = json_decode($data->already_exist_mlr, true);
            $already_exist_oneway = json_decode($data->already_exist_oneway, true);
            $company_user_id = $data->user_id;
            $merchantlinkrelations = MerchantLink::with(["merchantLinkRelation.company.owner_user", "initiator_user.user_company", "responder_user.user_company",])
                ->where("initiator_user_id", $company_user_id)
                ->orWhere("responder_user_id", $company_user_id)
                ->whereNotIn("id", $already_exist_mlr)
                ->get();

            $company = self::getUserCompany($company_user_id);
            $onewayrelations = OnewayRelation::with(["oneway"])
                ->whereHas("oneway", function ($query) use ($company) {
                    $query->where("self_merchant_id", $company->id);
                })->whereNotIn("id", $already_exist_oneway)
                ->get();

            return ["merchantlinkrelations" => $merchantlinkrelations, "onewayrelations" => $onewayrelations, "error" => false];
        } catch (\Exception $e) {
            \Log::info([
                "Error" => $e->getMessage(),
                "File" => $e->getFile(),
                "Line" => $e->getLine()
            ]);

            $return = ["error" => $e->getMessage()];
        }
    }

}
