<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Merchant;
use App\Models\Staff;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Log;
use \App\Classes\SystemID;
use \App\Models\companycontact;
use \App\Models\contact;

class SignupController extends Controller
{
    //
    public function signup(Request $request)
    {   

        $validate = $request->validate([
            "company_name"   => "required",
            "contact_person" => "required",
            "contact_mobile" => "required",
            "email"          => "required|email",
            "password"       => "required|string|min:6|confirmed",
            //'g-recaptcha-response' => 'required'
        ]);
        try {

            $company_name   = $request->company_name;
            $contact_person = $request->contact_person;
            $contact_mobile = $request->contact_mobile;
            $email          = $request->email;
            $password       = $request->password;
            $captcha = $request['g-recaptcha-response'];
            
            $verify = $this->google_validate($captcha);
 
            if ($verify != true) {
                session()->flash('message', 'Google reCaptcha Verification failed.');
                return back();
            }

            $is_exist = User::where('email', $email)->first();
            if ($is_exist) {
                session()->flash('message', 'Email already exist.');
                return back();
            }

            $merchant      = new Merchant();
            $company       = new Company();
            $king          = new User();
            $staff         = new staff();
            $system_id     = new SystemID('company');
            $system_id_ind = new SystemID('individual');

            $king->type     = 'staff';
            $king->status   = 'pending';
            $king->email    = $email;
            $king->name     = $contact_person;
            $king->password = Hash::make($password);
            $king->save();

            $company->name          = $company_name;
            $company->systemid      = $system_id;
            $company->owner_user_id = $king->id;
            $company->save();

            $staff->user_id    = $king->id;
            $staff->systemid   = $system_id_ind;
            $staff->company_id = $company->id;
            $staff->save();

            $merchant->company_id = $company->id;
            $merchant->save();

            $contact            = new contact();
            $companycontact     = new companycontact();
            $contact->name      = $contact_person;
            $contact->mobile_no = $contact_mobile;
            $contact->save();
            $companycontact->company_id = $company->id;
            $companycontact->contact_id = $contact->id;
            $companycontact->save();

            session()->flash('message', 'Thanks for signing up. Please wait for approval.');
            return back();

        } catch (\Exception $e) {
            log::debug($e);

            session()->flash('message', 'Some error occured.');
            return back();

        }
    }

    public function google_validate($receivedRecaptcha)
    {

        $google_secret        = "6Ldhgq4UAAAAAJjb1eZnLX6gcv6adS79BA8W58md";
        $verifiedRecaptchaUrl = 'https://www.google.com/recaptcha/api/siteverify?secret=' . $google_secret . '&response=' . $receivedRecaptcha;
        $handle               = \curl_init($verifiedRecaptchaUrl);
        \curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        \curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false); // not safe but works
        //curl_setopt($handle, CURLOPT_CAINFO, "./my_cert.pem"); // safe
        $response = \curl_exec($handle);
        $httpCode = \curl_getinfo($handle, CURLINFO_HTTP_CODE);
        \curl_close($handle);
        if ($httpCode >= 200 && $httpCode < 300) {
            if (strlen($response) > 0) {
                $responseobj = json_decode($response);
                if (!$responseobj->success) {
                    return false;
                } else {
                    return true;
                }
            }
        } else {
            return false;
        }
    }
}
