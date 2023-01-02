<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use \App\Classes\UserData;

use \App\Models\Projmgmt;
use \App\Models\Pjob;
use \App\Models\usersrole;
use \App\Models\Company;
// use \App\Models\ganttchartPjobLink;

use \Log;

class ProjMgmtGanttchartController extends Controller
{
    //

    public function ShowGPJobs($pjob_id) {
        try {
            $id = \Auth::user()->id;
            $user_data = new UserData();
            $user_roles = usersrole::where('user_id',$id)->get();
            $is_king =  \App\Models\Company::where('owner_user_id', \Auth::user()->id)->first();
    
            $is_king = $is_king != null ? true : false;
            $pjob = Pjob::where('systemid',$pjob_id)->first();
            $title = $pjob->name;
            $model_type = 'Pjob';
            return view('projmgmt.ganttchart',compact(['pjob_id','model_type','user_roles','is_king','title']));
        } catch (\Exception $e) {
            Log::info($e);
            abort(404);
        }
    }

    public function GetGPJobs($pjob_id) {
        try {

            $pjob = Pjob::select('systemid as id','name as text','start as start_date','duration','completion','created_at')->where('systemid',$pjob_id)->get();
            // dd($pjob);
            $pjob = $pjob->filter(function($z){
                return ($z->start_date != '0000-00-00' &&  !empty($z->start_date));
            });


            $pjob->map(function($f){
                     
                if ($f->completion == '0000-00-00' || $f->completion == null) {
                    if (date('Y-m-d',strtotime($f->start_date)) > (date('Y-m-d'))) {
                        $f->completion = date("Y-m-d",  strtotime($f->start_date));
                    } else {
                        $f->completion = date("Y-m-d");
                    }
                }
                
                $datetime1 = new \DateTime(date('Y-m-d', strtotime($f->start_date)));
                $datetime2 = new \DateTime(date('Y-m-d', strtotime($f->completion)));
                $interval = $datetime2->diff($datetime1);
                $days = (int) $interval->days;  
                
                $f->duration =  $days == 0 ? 1:$days;

                $f->color =  "green";//sprintf('#%06X', mt_rand(0x000000, 0xFFFFFF / 1.5));
                $f->textColor = "#FFF";

            });


   
            $res =   response()->json([
                "data" =>  $pjob
            ]);


            return $res;

        } catch (\Exception $e) {
            Log::info($e);
            abort(404);
        }
    }

    public function ShowGProj($p_id) {
        try {

            $id = \Auth::user()->id;
            $user_data = new UserData();
            $user_roles = usersrole::where('user_id',$id)->get();
            $is_king =  \App\Models\Company::where('owner_user_id', \Auth::user()->id)->first();
    
            $is_king = $is_king != null ? true : false;

            $proj = Projmgmt::where('systemid',$p_id)->first();
            $title = $proj->name;
        
            $model_type = 'Proj';
            return view('projmgmt.ganttchart',compact(['p_id','model_type','user_roles','is_king','title']));
        } catch (\Exception $e) {
            Log::info($e);
            abort(404);
        }
    }

    public function GetGProj($p_id) {
        try {

            $proj = Projmgmt::select('id as r_id','systemid as id','name as text','created_at as start_date','duration')->where('systemid',$p_id)->get();
           
            
            $pjob = Pjob::select('systemid as id','name as text', 'start as start_date','completion',"projmgmt_id as parent")->where('projmgmt_id',$proj[0]->r_id)->get();
            
            foreach($pjob as $p) {
                $p->parent = $proj[0]->id;
            }
            
            $pjob = $pjob->filter(function($z){
                return ($z->start_date != '0000-00-00' &&  !empty($z->start_date));
            });


            $pjob->map(function($f){
                                    
                    $datetime1 = new \DateTime(((!empty($f->start_date) && $f->start_date != "0000-00-00" ? $f->start_date:date('Y-m-d')) ));
                    $datetime2 = new \DateTime((!empty($f->completion) && $f->completion != "0000-00-00" ? $f->completion:date('Y-m-d')));
                    $interval = $datetime1->diff($datetime2);
                    $days = (int) $interval->days;  
                    $f->duration =  $days;
                    $f->color = "green";//sprintf('#%06X', mt_rand(0x000000, 0xFFFFFF / 1.5));// "#".substr(str_shuffle('ABCDEF0123456789'), 0, 6);
                    $f->textColor = "#FFF";

            });


            $proj[0]->start_date = date('Y-m-d',strtotime($proj[0]->start_date));

            $datetime2 = new \DateTime(date('Y-m-d',strtotime($proj[0]->start_date)));
            
            if (!$pjob->isEmpty()) {
                if ($pjob->max('completion') != "0000-00-00") {
                    $datetime1 = new \DateTime( date('Y-m-d',   strtotime($pjob->max('completion'))));
                } else {
                    $datetime1 = new \DateTime(date('Y-m-d', strtotime($pjob->max('start_date') . '+1 Day')));
                }
            } else {
                $datetime1 = new \DateTime(date('Y-m-d', strtotime( $proj[0]->start_date  . '+1 Day')));
            }


            $interval = $datetime1  ->diff($datetime2);
         
            $days = (int) $interval->days;
          
            $proj[0]->duration = $days;
            $proj[0]->project_level = "Project";
            $proj[0]->color = "Darkblue";

            $data = $proj->merge($pjob);

            $res =   response()->json([
                "data" =>  $data
            ]);


            return $res;

        } catch (\Exception $e) {
            Log::info($e);
            abort(404);
        }
    }

    public function ShowGAll() {
        try {

            $id = \Auth::user()->id;
            $user_data = new UserData();
            $user_roles = usersrole::where('user_id',$id)->get();
            $is_king =  \App\Models\Company::where('owner_user_id', \Auth::user()->id)->first();
    
            $is_king = $is_king != null ? true : false;

            $title = "Overall Gantt Chart";
        
            $model_type = 'All';
            return view('projmgmt.ganttchart',compact(['model_type','user_roles','is_king','title']));
        } catch (\Exception $e) {
            Log::info($e);
            abort(404);
        }
    }


    public function GetGAll() {
        try {
            $proj = Projmgmt::select('systemid as id','name as text','created_at as start_date','duration')->get();
            
            $data = [];
            
            foreach ($proj as $p) {
                $result = $this->GetGProj($p->id);
                $de_coded_result = json_decode($result->getContent());
                $data[] = $de_coded_result->data;
            }

            $data = json_encode($data);
            $data = Str_replace("[",'',$data);
            $data = Str_replace("]",'',$data);
            $data = <<< EOD
                {"data":[$data]}
            EOD;
            return $data;

        } catch (\Exception $e) {
            Log::info($e);
            abort(404);
        }
    }

    public function GetDataSlider($date,$type) {
        try {
            
            $pjob = Pjob::whereDate('completion','<=', \Carbon\Carbon::parse($date)->toDateString())->get();
            
            $cost = $pjob->sum('cost');
            $revenue  = $pjob->sum('revenue');

            return response()->json(["cost"=>$cost,"revenue"=>$revenue]);

        } catch (\Exception $e) {
            Log::info($e);
            abort(404);
        }
    }

    
}
