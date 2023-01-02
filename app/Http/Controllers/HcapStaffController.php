<?php

namespace App\Http\Controllers;

use App\Classes\SystemID;
use App\Classes\UserData;
use App\Models\Staff;
use App\Models\Company;
use App\Models\usersrole;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class HcapStaffController extends Controller
{
    public function showView()
    {
        $new_staffs =  DB::table('users as u')
            ->select('u.id', 'u.name', 'u.email', 'u.status','s.department', 's.position')
            ->join('staff as s', 's.user_id', '=', 'u.id')
            ->join('company as c', 'c.id', '=', 's.company_id')
            ->whereNOTIn('u.id', function($query){
                $query->select('user_id')->from('hcap_staff');
            })
            ->where('c.owner_user_id', Auth::user()->id)
            ->where('u.status', 'active')
            ->orwhere('u.status', 'inactive')
            ->get();

        foreach ($new_staffs as $staff){
            $hcap_staff_record = DB::table('hcap_staff')->where('user_id', $staff->id)->first();
            if($hcap_staff_record == null) {
                $SystemID = new SystemID('hcap_staff');
                $result = DB::table('hcap_staff')->insert([
                    'systemid' => $SystemID,
                    'user_id' => $staff->id,
                    'name' => $staff->name,
                    'email' => $staff->email
                ]);
            }
        }

        $staffs = DB::table('hcap_staff as a')
                    ->join('users as b', 'b.id','=','a.user_id')
                    ->select('a.*', 'b.status')
                    ->get();

        return view('hcap_staff.hcap_staff',compact('staffs'));
    }

    public function personalDetails($user_id)
    {
      $systemid = $user_id;
      $user_id = Auth::user()->id;
      $user_roles = usersrole::where('user_id', $user_id)->get();
      $is_king =  Company::where('owner_user_id', Auth::user()->id)->get();
      return view('hcap_staff.personal_details', compact('systemid','user_roles', 'is_king', 'user_id'));
    }

    public function personalTab(request $tab)
    {
//        dd($tab->staff_id);
      switch ($tab->tabName) {
        case 'personalDetails':
            $staffPersonalData = DB::table('hcap_staff')->where('systemid', $tab->staff_id)->first();
            $staffEducationData = DB::table('hcap_staffeducation')->where('hcap_staff_id', $staffPersonalData->id)->get();
            $staffEorkData = DB::table('hcap_staffexperience')->where('hcap_staff_id', $staffPersonalData->id)->get();
//            dd($staffEducationData);
            return view('hcap_staff.form_personal', compact('staffEorkData','staffEducationData','staffPersonalData', 'tab'));
          break;

        case 'staffDetail':
            $staffPersonalData = DB::table('hcap_staff')->where('systemid', $tab->staff_id)->first();
            $staffDetailsData = DB::table('hcap_staffdetails as a')
                                    ->join('hcap_staffdetailsatype as b', 'a.id','=', 'b.staffdetails_id')
                                    ->where('a.hcap_staff_id', $staffPersonalData->id)
                                    ->select('a.*', 'b.allowance_type', 'b.amount')
                                    ->groupBy(['a.id', 'b.allowance_type', 'b.amount'])
                                    ->get();
            $staffDetailsAllowance = array();
            $id = null;
            foreach($staffDetailsData as $row){
                if($id !=  $row->id) {
                    $id = $row->id;
                }
                
                $staffDetailsAllowance[$id][] = array(
                                                'id' => $row->id,
                                                'allowance_type' => $row->allowance_type,
                                                'amount' =>  $row->amount
                                            );
            }

            $staffDetailsData = DB::table('hcap_staffdetails')->where('hcap_staff_id', $staffPersonalData->id)->get();

            return view('hcap_staff.form_detail', compact('staffDetailsAllowance', 'staffPersonalData','staffDetailsData','tab'));
          break;
        case 'staffAppraisal':
            $staffPersonalData = DB::table('hcap_staff')->where('systemid', $tab->staff_id)->first();
            $staffAppraisalData = DB::table('hcap_staffappraisal')->where('hcap_staff_id', $staffPersonalData->id)->get();
            return view('hcap_staff.form_appraisal', compact('staffPersonalData','staffAppraisalData','tab'));
          break;
        
        default:
          return false;
          break;
      }
    }

    public function staffAppraisalUpdate(Request $request)
    {
        foreach ($request->staff_appr_from as $key=>$val){
            if(isset( $request->staffappraisal_id[$key])){
                $result = DB::table('hcap_staffappraisal')
                    ->where('id', $request->staffappraisal_id[$key])
                    ->update([
                        'hcap_staff_id' => $request->staff_id,
                        'from' => date("Y-m-d", strtotime($request->staff_appr_from[$key])),
                        'to' => date("Y-m-d", strtotime($request->staff_appr_to[$key])),
                        'manager_comment' => $request->staff_appr_man_comment[$key],
                        'staff_comment' => $request->staff_appr_staff_comment[$key]
                    ]);
            }else {
                $result = DB::table('hcap_staffappraisal')->insert(
                    [
                        'hcap_staff_id' => $request->staff_id,
                        'from' => date("Y-m-d", strtotime($request->staff_appr_from[$key])),
                        'to' => date("Y-m-d", strtotime($request->staff_appr_to[$key])),
                        'manager_comment' => $request->staff_appr_man_comment[$key],
                        'staff_comment' => $request->staff_appr_staff_comment[$key]
                    ]);
            }
        }
        if($result) return "Data updated successfully!";
    }

    public function staffDetailsUpdate(Request $request)
    {
        foreach ($request->staff_det_department as $key=>$val){
            if(isset( $request->staffdetails_id[$key])){
                //update DB
                $staffdetails_id = $request->staffdetails_id[$key];
                DB::table('hcap_staffdetailsatype')->where( 'staffdetails_id' , $staffdetails_id)->delete();
                $result = DB::table('hcap_staffdetails')
                    ->where('id', $staffdetails_id)
                    ->update([
                        'hcap_staff_id' => $request->staff_id,
                        'department' => $request->staff_det_department[$key],
                        'position' => $request->staff_det_position[$key],
                        'reponsibility' => $request->staff_det_responsibility[$key],
                        'salary' => $request->staff_det_salary[$key],
                        'epf_employee' => $request->staff_det_employee[$key],
                        'epf_employer' => $request->staff_det_employer[$key],
                        'socso' => $request->staff_det_socso[$key]
                    ]);

                foreach ($request->staff_det_type[$key] as $k => $v) {
                    if(isset( $request->staffdetailsatype_id[$key][$k])){
                        $result = DB::table('hcap_staffdetailsatype')
                            ->where('id', $request->staffdetailsatype_id[$key][$k])
                            ->update([
                                'staffdetails_id' => $staffdetails_id,
                                'allowance_type' => $request->staff_det_type[$key][$k],
                                'amount' => $request->staff_det_myr[$key][$k]
                            ]);
                    }else {
                        $result = DB::table('hcap_staffdetailsatype')->insertGetId(
                            [
                                'staffdetails_id' => $staffdetails_id,
                                'allowance_type' => $request->staff_det_type[$key][$k],
                                'amount' => $request->staff_det_myr[$key][$k]
                            ]
                        );
                    }
                }
            }else {
                //insert into DB
                $staffdetails_id = DB::table('hcap_staffdetails')->insertGetId(
                    [
                        'hcap_staff_id' => $request->staff_id,
                        'department' => $request->staff_det_department[$key],
                        'position' => $request->staff_det_position[$key],
                        'reponsibility' => $request->staff_det_responsibility[$key],
                        'salary' => $request->staff_det_salary[$key],
                        'epf_employee' => $request->staff_det_employee[$key],
                        'epf_employer' => $request->staff_det_employer[$key],
                        'socso' => $request->staff_det_socso[$key]
                    ]
                );
                foreach ($request->staff_det_type[$key] as $k => $v) {
                    $result = DB::table('hcap_staffdetailsatype')->insertGetId(
                        [
                            'staffdetails_id' => $staffdetails_id,
                            'allowance_type' => $request->staff_det_type[$key][$k],
                            'amount' => $request->staff_det_myr[$key][$k]
                        ]
                    );
                }
            }
        }
        if(isset($result)) return "Data updated successfully!";
        else return "Empty!";
    }

    public function personalDetailsUpdate(Request $request){
//        dd($request);
        $hcap_staff_record = DB::table('hcap_staff')->where('systemid', $request->staff_id)->first();
        if($hcap_staff_record == null) {
            $result = DB::table('hcap_staff')->insert(
                [
                    'systemid' => $request->staff_id,
                    'name' => $request->staff_name,
                    'email' => $request->staff_email,
                    'mobile_no' => $request->staff_mobile,
                    'nric' => $request->staff_nric,
                    'dateofbirth' =>  date("Y-m-d", strtotime($request->staff_dob)),
                    'address' => $request->staff_address,
                    'photo_image_file' => $request->uploadedFilePath,
                    'remarks' => $request->staff_remarks
                ]
            );
        }else{
            $result = DB::table('hcap_staff')
                ->where('systemid', $request->staff_id)
                ->update([
                    'name' => $request->staff_name,
                    'email' => $request->staff_email,
                    'mobile_no' => $request->staff_mobile,
                    'nric' => $request->staff_nric,
                    'dateofbirth' => date("Y-m-d", strtotime($request->staff_dob)),
                    'address' => $request->staff_address,
                    'photo_image_file' => $request->uploadedFilePath,
                    'remarks' => $request->staff_remarks
                ]);
        }
        $staff_id = DB::table('hcap_staff')->where('systemid', $request->staff_id)->pluck('id')[0];

        //education data
        foreach ($request->staff_from as $key => $val){
            if(isset( $request->staffeducation_id[$key])){
                //update data
                $result = DB::table('hcap_staffeducation')
                    ->where('id', $request->staffeducation_id[$key])
                    ->update([
                        'from' => date("Y-m-d", strtotime($request->staff_from[$key])),
                        'to' => date("Y-m-d", strtotime($request->staff_to[$key])),
                        'course' => $request->staff_course[$key],
                        'school_university' => $request->staff_school[$key],
                        'remarks' => $request->staff_achievment[$key]
                    ]);
            }
            else{
                //insert into database
                if($request->staff_from[$key] != "" && $request->staff_to[$key] != "") {
                    $result = DB::table('hcap_staffeducation')->insert(
                        [
                            'hcap_staff_id' => $staff_id,
                            'from' => date("Y-m-d", strtotime($request->staff_from[$key])),
                            'to' => date("Y-m-d", strtotime($request->staff_to[$key])),
                            'course' => $request->staff_course[$key],
                            'school_university' => $request->staff_school[$key],
                            'remarks' => $request->staff_achievment[$key]
                        ]
                    );
                }
            }
        }

        //work experience data
        foreach ($request->staff_work_from as $key => $val){
            if(isset( $request->staffexperience_id[$key])){
                //update data
                $result = DB::table('hcap_staffexperience')
                    ->where('id', $request->staffexperience_id[$key])
                    ->update([
                        'from' => date("Y-m-d", strtotime($request->staff_work_from[$key])),
                        'to' => date("Y-m-d", strtotime($request->staff_work_to[$key])),
                        'position' => $request->staff_position[$key],
                        'company' => $request->staff_company[$key],
                        'job_description' => $request->staff_job_description[$key],
                        'remarks' => $request->staff_job_achievment[$key]
                    ]);
            }
            else {
                //insert into database
                try {
                    if($request->staff_work_from[$key] != "" && $request->staff_work_to[$key] != "") {
                        $result = DB::table('hcap_staffexperience')->insert(
                            [
                                'hcap_staff_id' => $staff_id,
                                'from' => date("Y-m-d", strtotime($request->staff_work_from[$key])),
                                'to' => date("Y-m-d", strtotime($request->staff_work_to[$key])),
                                'position' => $request->staff_position[$key],
                                'company' => $request->staff_company[$key],
                                'job_description' => $request->staff_job_description[$key],
                                'remarks' => $request->staff_job_achievment[$key]
                            ]
                        );
                    }
                } catch (\Illuminate\Database\QueryException $ex) {
                    $response = (new ApiMessageController())->queryexception($ex);
                    return $response;
                }
            }
        }

        if(isset($result)) return "Data updated successfully!";
        else return "Empty!";
    }

    public function staffPicture(Request $request)
    {
      if ($request->file('photo')->isValid()) {
        // return $request->photo->path();
        // $path = $request->file('photo')->store('images');
        // return $path;
        $this->check_location("/images/profile");

        $file = $request->file('photo');
          $extension = $file->getClientOriginalExtension(); // getting image extension
          $user_id = Auth::user()->id;

          if (!in_array($extension, array(
              'jpg', 'JPG', 'png', 'PNG', 'jpeg', 'JPEG', 'gif', 'GIF', 'bmp', 'BMP', 'tiff', 'TIFF'))) {
              return abort(403);
          }

          $filename = 'profile_'.rand(111, 999) .'_' .$user_id .'.' .$extension;

          $file->move(public_path() . ("/images/profile/"), $filename);
          
          return asset('/images/profile/' .$filename);
      }
    }

    public function staffDelPicture(request $request) {
        dd($request);
      $path = array_filter(explode('/', $request->path));
      $photo_path = '/' .$path[3] .'/' .$path[4] .'/' .$path[5];
      
      File::delete(public_path() .$photo_path);
      return '';
    }

    public function check_location($location)
    {
        $location = array_filter(explode('/', $location));
        $path = public_path();

        foreach ($location as $key) {
            $path .= "/$key";

			Log::debug('check_location(): $path='.$path);

            if (is_dir($path) != true) {
                mkdir($path, 0775, true);
            }
        }
    }
}
