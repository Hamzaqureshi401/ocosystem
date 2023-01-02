<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\usersrole;
use App\Models\role;
use App\Models\Company;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Matrix\Exception;
use \Log;
use Yajra\DataTables\DataTables;
use \App\Classes\SystemID;
use \App\Classes\UserData;

use \App\Models\Projmgmt;
use \App\Models\Pjob;
use \App\Models\Prjmgmtdoc;
use \App\Models\Prjjobdoc;
use \App\Models\Pjobuat;
use \App\Models\product;
use \App\Models\rawmaterial;
use \App\Models\merchantproduct;
use \App\Models\prd_proservices;
use \App\Models\restaurant;
use \App\Models\PjobMerchantProduct;
use \App\Models\PjobMerchant;
use \App\Models\MerchantRelation;
use \App\Models\Oneway;
use \App\Models\MerchantLink;
use \App\Models\MerchantLinkRelation;
use \App\Models\Pjobproduct;
use \App\Models\Pjobservice;

use \App\Models\PjobOneWay;
use \App\Models\PjobOneWayProduct;

use Milon\Barcode\DNS1D;
use Milon\Barcode\DNS2D;
use DB;

class ProjMgmtController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth');
  }
	public function showProjectJob($id)
	{
		$user_id = Auth::User()->id;
		$user_roles = usersrole::where('user_id',$user_id)->get();
		$is_king =  Company::where('owner_user_id',Auth::user()->id)->get();
			
		$project = Projmgmt::where('id', $id)->get()->first();
		$project_name = $project->name;
		$proj_id = $project->id;
	  
		return view('projmgmt.projmgmt_desc',
			compact('user_roles','is_king', 'proj_id', 'project_name'));
	}


	public function showJobTable(Request $request)
	{
		$proj_id = $request->id;
		
		$pjobs = Pjob::where('projmgmt_id', $proj_id)->orderBy('created_at', 'desc')->get();
    
		// $pjob->map(function($f){
		//   $rev = Pjobuat::where(['id'=>$request->rev_id,'pjob_id' =>  $request->pjob_id])->first();
		// });
      
		$totalRecords = Pjob::where('projmgmt_id', $proj_id)->count();
		$totalFiltered = $totalRecords;
		
		return Datatables::of($pjobs)
            ->addIndexColumn()
            ->addColumn('systemid', function ($data) {
                return '<a  target="_blank" target="_blank" style="margin: 0;text-align: center;text-decoration:none">' . (!empty($data->systemid)? $data->systemid : '000000000000') . '</a>';
            })
            ->addColumn('name', function ($data) {
                
                return '<p class="os-linkcolor" data-field="pjobname"  style="cursor: pointer; margin: 0;display: inline-block;text-align:left;cursor:pointer;">' . (!empty($data->name) ? ucfirst($data->name) : ' Job Name') . '</p>';
            })
            ->addColumn('completion', function ($data) {
                $date_1 = ($data->start != '0000-00-00' ? \Carbon\Carbon::parse($data->start)->format('dMy') : '-');
                $date_2 = ($data->completion != '0000-00-00' ? \Carbon\Carbon::parse($data->completion)->format('dMy') : '-');

              return '<p onclick="openDateModal(' . $data->id .",'$date_1','$date_2'". ')" class="os-linkcolor" style="margin: 0;display: inline-block;text-align:center;cursor:pointer;">' . $date_2 . '</p>';

            })
            ->addColumn('status', function ($data) {

              return '<p style="cursor: pointer; margin: 0;display: inline-block;text-align:center;">' . (!empty($data->status) ? ucfirst($data->status) : 'Pending') . '</p>';

            })
            ->addColumn('revenue', function ($data) {

              return '<p class="os-linkcolor" onclick="openRevenueModal(' . $data->id . ')"  style="margin: 0;display: inline-block;text-align:center;cursor:pointer;">' . (!empty($data->revenue) ? number_format($data->revenue/100,2) : '0.00') . '</p>';

            })
            ->addColumn('cost', function ($data) {
              $url = route('get-contractor-landing',$data->id);
              $cost = (!empty($data->cost)) ? number_format( $data->cost/ 100,2 ):"0.00";
              return '<p class="os-linkcolor" style=" margin: 0;display: inline-block;text-align:center;cursor: initial;">'."<a href='$url' target='_blank' style='cursor: initial;cursor:pointer;'>" .  $cost . '</a></p>';

            })
            ->addColumn('receivable', function ($data) {
              return  (!empty($data->receivable)) ? number_format( $data->receivable/ 100,2 ):"0.00";
            })
            ->addColumn('payable', function ($data) {
              return (!empty($data->payable)) ? ( $data->payable/ 100 ):"0.00";
            })
            ->addColumn('yellow', function ($data) {
              $url = asset('images/yellowcrab_50x50.png');

              return "<img style='width:25px;height:25px;cursor:pointer;'
              src='{$url}' onclick='display_gantt_job($data->systemid)'>";
            })
            ->addColumn('blue', function ($data) {
				$url = asset('images/bluecrab_50x50.png');

					return "<img style='width:25px;height:25px;cursor:pointer;'
					src='{$url}' onclick='upload_docs_fn($data->id)' >";
				})
			->addColumn('red', function ($data) {
				$url = asset('images/redcrab_50x50.png');

				if ($data->revenue == 0 && $data->cost == 0){
					return '<img onclick="deleteRow(' . $data->id .
						')" style="width:25px;height:25px;cursor:pointer;"
						src="'.$url.'" >';
				} else {
					return "<img  class='crab_disabled' style='width:25px;height:25px;cursor:pointer;' src='{$url}' >";
				}
			})
		->escapeColumns([])
		->make(true);
	}
  

	public function showJobCost($id){
      $id = Auth::user()->id;
      $user_roles = usersrole::where('user_id',$id)->get();
  
      $is_king =  \App\Models\Company::where('owner_user_id',Auth::user()->id)->first();
  
      if ($is_king != null) {
          $is_king = true;
      } else {
          $is_king  = false;
      }
      // $project = Projmgmt::find($id);
  
      $data = Pjob::where('id', $id)->get();
  
  
      return view('projmgmt.pjob_cost',compact('user_roles','is_king'));
  
     
    }

    public function showJobRawMaterial($id){
    

      $id = Auth::user()->id;
      $user_roles = usersrole::where('user_id',$id)->get();
  
      $is_king =  \App\Models\Company::where('owner_user_id',Auth::user()->id)->first();
  
      if ($is_king != null) {
          $is_king = true;
      } else {
          $is_king  = false;
      }
      // $project = Projmgmt::find($id);
  
      $data = Pjob::where('id', $id)->get();
  
  
      return view('projmgmt.pjob_raw_material',compact('user_roles','is_king'));
  
     
    }
  
  public function getRawMaterial($job_id) {
    try {

        $user_id = Auth::user()->id;
        $user_roles = usersrole::where('user_id',$user_id)->get();
        $is_king =  Company::where('owner_user_id',Auth::user()->id)->get();
       
        $pjob = Pjob::where('id', $job_id)->first();
        $projmgmt =  Projmgmt::find($pjob->projmgmt_id);

        return view('projmgmt.projmgmt_rawmaterial',compact('user_roles','is_king','job_id','projmgmt','pjob'));
    } catch (\Exception $e) {
      dd($e);
      Log::info("ProjMgmtController@getRawMaterial: error occured \n$e");
     // abort(404);
    }
  }
  //is actually contractor fetch
  public function getRawMaterial_fetch(Request $request)
  {

    $userData = new UserData();
    $companyId = $userData->company_id();
    $company = Company::find($companyId);
    $owner_user_id = $company->owner_user_id;

    if (!$request->has('pjob_id')) {
      throw new Exception("Pjob_id not specified", 1);
    }

    
    $responderIds = MerchantLink::select('id','responder_user_id as mL')->where('initiator_user_id', $owner_user_id)->get();
    $initiatorIds = MerchantLink::select('id','initiator_user_id as mL')->where('responder_user_id', $owner_user_id)->get();
    $merchantLink = $responderIds->merge($initiatorIds);

    //filter
      $merchantLink = $merchantLink->filter(function($f){
      $MerchantLinkRelation = MerchantLinkRelation::where('merchantlink_id',$f->id)->where('ptype', 'supplier')->first();
        return !empty($MerchantLinkRelation);
      });

    
    $oneWay = Oneway::select("id","company_name as name","business_reg_no")->where('self_merchant_id',$company->id)->get();

    $oneWay->map(function($f){
      $f->m_type = 'oneWay';
    });

    $twoWay = Company::select("id",'systemid','name',"business_reg_no")->whereIn('owner_user_id',$merchantLink->pluck('mL'))->get();
    
    $twoWay->map(function($f){
      $f->m_type = 'twoWay';
    });
    
    $data = $oneWay->merge($twoWay);

    foreach($data as $d) {
      $d->pjob = $request->pjob_id;
    }
   
    $data = $this->getContractorData($data);
    $data->map(function($z){

      if ($z->m_type == 'twoWay') {
        $pjobMerchant = PjobMerchant::where(['merchant_id' => $z->id, "pjob_id"=> $z->pjob ])->first();      
        $z->active =  !empty($pjobMerchant->active) ? ($pjobMerchant->active == 1 ? "active_button_activated":''):'';
        $z->allow = !empty($pjobMerchant->active) ? ($pjobMerchant->active == 1 ? true:false):false;
        $z->action = "custom_form";

      } else if ($z->m_type == 'oneWay')  {

        $pjobOneWay_ = PjobOneWay::where(['pjob_id' => $z->pjob, "oneway_id" =>  $z->id ])->first();
        $z->active =  !empty($pjobOneWay_->active) ? ($pjobOneWay_->active == 1 ? "active_button_activated":''):'';
        $z->allow = !empty($pjobOneWay_->active) ? ($pjobOneWay_->active == 1 ? true:false):false;
        $z->action = "one_way";
      }

    });

    return Datatables::of($data)
      ->addIndexColumn()
       ->addColumn('systemid', function ($data) {
			return empty($data->systemid) ? '-':$data->systemid;
        })
       ->addColumn('b_id', function ($data) {
			return $data->business_reg_no;
        })
       ->addColumn('name', function ($data) {
			return  $data->name;
        })
       ->addColumn('rawmaterial', function ($data) {
          $cost = number_format($data->raw_material_cost/100,2);

          $htmlTemplate = <<<EOD
          <p onclick="show_rawmateral(this,$data->id,'$data->m_type')" style="cursor:pointer" class="mb-0 os-linkcolor rawmaterial_$data->id">$cost</p>
          EOD;

			    return ($data->allow) ? $htmlTemplate:$cost;
        })
        ->addColumn('service', function ($data) {
          $cost = number_format($data->pro_service_cost/100,2);
          $htmlTemplate = <<<EOD
            <p onclick="show_service_model(this,$data->id,'$data->m_type')" style="cursor:pointer" class="mb-0 os-linkcolor service_$data->id">$cost</p>
          EOD;
          return ($data->allow) ? $htmlTemplate:$cost;
        })
        ->addColumn('total', function ($data) {
          $cost = number_format($data->total_cost/100,2);
          $html = <<<EOD
          <span merchant-id="$data->id">$cost</span?
          EOD;
			    return  $html;
        })
      ->addColumn('active_btn', function ($data) {
     
      
        $htmlTemplate = <<<EOD
         <button merchant-id="$data->id" $data->active merchant-type="$data->m_type" class="prawn btn $data->m_type trigger_save_$data->id active_button  $data->active" onclick="active_buttion(this)" style="min-width:75px">Active</button>
        EOD;
        return $htmlTemplate;
        })
		->escapeColumns([])
		->make(true);
  }

  public function rawmaterial_popup_table(Request $request)
  {
   
    $this->user_data = new UserData();
    $model           = new rawmaterial();
    $merchant_id = $request->merchant_id;
    $merchant_id_self = $this->user_data->company_id();

    if (empty($merchant_id)) {
      throw new Exception("Invalid merchant id with request", 1);     
    }

    if (!$request->has('pjob_id')) {
      throw new Exception("pjob_id missing with Request", 1);
    }


    $ids  = merchantproduct::where('merchant_id',  $merchant_id_self)->pluck('product_id');
    
    $ids  = product::where('ptype', 'rawmaterial')->whereIn('id', $ids)->pluck('id');
    $data = $model->whereIn('product_id', $ids)->orderBy('created_at', 'asc')->latest()->get();
    
    foreach($data as $f) {
      $f->merchant_id = $merchant_id;
    }

    $data = $data->filter(function($d){
      if (empty($d->product_name->name) || empty($d->price)) {
        return false;
      } 
      return true;
    });

    $data->map(function($data) {
      $pjob_id = request()->pjob_id;
      $merchant_id = request()->merchant_id;
      $m_type = request()->m_type;

      if ($m_type == 'oneWay')
      {
        $pjobMerchant = $this->pjoboneway_allocation($pjob_id, $merchant_id);
        $is_product_saved = PjobOneWayProduct::where(['pjoboneway_id'=>$pjobMerchant->id,  "product_id"  =>  $data->product_id, "active"  =>  1 ])->first();
        $data->action = "one_way";

      } else if ($m_type == 'twoWay') 
      {

        $pjobMerchant = $this->pjobmerchant_allocation($pjob_id ,$merchant_id);   
        $is_product_saved =  PjobMerchantProduct::where( ['pjobmerchant_id' =>  $pjobMerchant->id, "product_id" => $data->product_id,"active" =>  1 ])->first() ;
        $data->action = "custom_form";

      }

      if (!(empty($is_product_saved))) 
      {

        $data->selected_qty =  $is_product_saved->qty;
        $data->is_active = $is_product_saved->active == 1 ? "active_button_activated del_":'';
        $data->selected_qty = $is_product_saved->active == 1 ? $is_product_saved->qty:1;
        $data->is_disabled = "disabled_qty_rm_action_$data->product_id";

      } else 
      {
        $data->is_disabled = "disabled_qty_rm_action_$data->product_id disabled_qty_rm";
        $data->is_disabled_text_field = "disabled='disabled'";
        $data->is_active = "";
        $data->selected_qty = 1;
      }
    });


    return Datatables::of($data)
      ->addIndexColumn()
       ->addColumn('systemid', function ($memberList) {
                return $memberList->product_name->systemid;
        })
       ->addColumn('name', function ($memberList) {

        if (!empty($memberList->product_name->thumbnail_1)) {
          $img_src =  $memberList->product_name->id . '/thumb/' . $memberList->product_name->thumbnail_1;
          $img = <<<EOD
          <img src='/images/product/$img_src'  style=' width: 25px;height: 25px;display: inline-block;margin-right: 8px;object-fit:contain;' />
          EOD;
        } else {
          $img = null;
       }
       
        $name = (!empty($memberList->product_name->name) ? $memberList->product_name->name :  'Name');
        $htmlTemplate = <<<EOD
          $img <p class="" style="margin: 0;display:inline-block">$name</p>
        EOD;
        return $htmlTemplate;

        })
        ->addColumn('cost', function ($memberList) {
                return "<span class='_cost_$memberList->product_id'>".(!empty($memberList->price) ? number_format(($memberList->price / 100), 2) : '0.00')."</span>";
        })
        ->addColumn('qty', function ($data) {
          $htmlTemplate = <<<EOD
              <div class="value-button $data->is_disabled increase" id="increase_407" onclick="$data->action.change_value_qty(1,$data->product_id,$data->merchant_id,this)" value="Increase Value" style="margin-top:-25px;">
                <ion-icon class="ion-ios-plus-outline $data->is_disabled" style="font-size: 24px;margin-right:5px;"></ion-icon>
              </div>
              <input  type="number" id="number_407"  class="number $data->is_disabled _qty_$data->product_id" disabled="disabled" value="$data->selected_qty" $data->is_disabled_text_field min="1" required="">
              <div class="value-button $data->is_disabled decrease" id="decrease_407"  onclick="$data->action.change_value_qty(-1,$data->product_id,$data->merchant_id,this)" value="Decrease Value" style="margin-top:-25px;">
                <ion-icon class="ion-ios-minus-outline $data->is_disabled" style="font-size: 24px;margin-left:5px;"></ion-icon>
              </div>  
          EOD;

          return $htmlTemplate;
        })
        ->addColumn('total', function ($data) {
          return "<span class='_total_$data->product_id'>".(!empty($data->price) ? number_format((($data->price * $data->selected_qty) / 100), 2) : '0.00')."</span>";
        })
        ->addColumn('active', function ($data) {
 

          $htmlTemplate = <<<EOD
            <button product-id="$data->product_id" merchant-id="$data->merchant_id" price="$data->price" class="prawn btn active_button product_act_rm $data->is_active" onclick="active_buttion_raw_material(this,'$data->action')" style="width:75px">Active</button>
          EOD;

          return $htmlTemplate;
        })
       ->escapeColumns([])
        ->make(true);
  }

  public function service_popup_table(Request $request)
  {
    try { 

        $this->user_data = new UserData();
        $model           = new prd_proservices();
        $merchant_id = $request->merchant_id;
        $merchant_id_self = $this->user_data->company_id();
        $pjob_id = $request->pjob_id;
        $m_type = $request->m_type;

        if ($m_type == 'twoWay') 
        {

          $pjobMerchant = $this->pjobmerchant_allocation($pjob_id,  $merchant_id,  false);   
          $data_ids =  PjobMerchantProduct::where( ['pjobmerchant_id' =>  $pjobMerchant->id, "active" =>  1 ])->whereNotNull("pjobproduct_id")->get()->pluck("pjobproduct_id");
          $action = "custom_form";

        } else if ($m_type == "oneWay") 
        {

          $pjobMerchant = $this->pjoboneway_allocation($pjob_id, $merchant_id,  false);
          $data_ids = PjobOneWayProduct::where(['pjoboneway_id'=>$pjobMerchant->id, "active"  =>  1])->whereNotNull("pjobproduct_id")->get()->pluck("pjobproduct_id");
          $action = 'one_way';

        } else 
        {
          throw new Exception("Error invalid $m_type merchant type", 1);
        }
        if (count($data_ids) > 0) {

          $data = Pjobproduct::whereIn('id',$data_ids)->get();

        } else {

          $data  = collect('');

        }
        
        $id = $pjob_id;
        return view('projmgmt.customisation-model',compact(['id','data','merchant_id','action']));

      } catch (\Exception $e) {
        Log::info($e);
        abort(404);
      }
    }

  public function index() {
	  $user_data = new UserData();
	  
	  $data  = Projmgmt::orderBy('projmgmt.created_at', 'desc')->
		  join('projmgmtmerchant', 'projmgmtmerchant.projmgmt_id','=','projmgmt.id')->
		  where('projmgmtmerchant.merchant_id',$user_data->company_id())->
		  select("projmgmt.*")->
		  get();
		
		return Datatables::of($data)
            ->addIndexColumn()
            ->addColumn('systemid', function ($data) {
                return '<a class="os-linkcolor" href="/project/' . $data->id . '" target="_blank" target="_blank" style="cursor: pointer; margin: 0;text-align: center;text-decoration:none">' . (!empty($data->systemid)? $data->systemid : '000000000000') . '</a>';
            })
            ->addColumn('name', function ($data) {
                
                return '<p  class="os-linkcolor" data-field="projectname"  style="cursor: pointer; margin: 0;display: inline-block;text-align:left;">' . (!empty($data->name) ? ucfirst($data->name) : 'Project Name') . '</p>';
            })
            ->addColumn('status', function ($data) {

              return '<p style="margin: 0;display: inline-block;text-align:center;">' . (!empty($data->status) ? ucfirst($data->status) : 'Pending') . '</p>';

            })
            ->addColumn('revenue', function ($data) {

              return '<p style="margin: 0;display: inline-block;text-align:center;">' . (!empty($data->revenue) ? number_format($data->revenue/100,2) : '0.00') . '</p>';

            })
            ->addColumn('cost', function ($data) {

              return '<p style="cursor: pointer; margin: 0;display: inline-block;text-align:center;">' . (!empty($data->cost) ? number_format($data->cost/100,2) : '0.00') . '</p>';

            })
            ->addColumn('receivable', function ($data) {
              return  (!empty($data->receivable)) ? number_format( $data->receivable/ 100,2 ):"0.00";
            })
            ->addColumn('payable', function ($data) {
              return (!empty($data->payable)) ? ( $data->payable/ 100 ):"0.00";
            })
            ->addColumn('yellow', function ($data) {
              $url = asset('images/yellowcrab_50x50.png');

              return "<img style='width:25px;height:25px;cursor:pointer;'
              src='{$url}' onclick='display_gantt_proj($data->systemid)' >";

            })
            ->addColumn('blue', function ($data) {
              $url = asset('images/bluecrab_50x50.png');

              return "<img onclick='upload_docs_fn($data->id)' style='width:25px;height:25px;cursor:pointer;'
              src='{$url}' >";

            })
            ->addColumn('red', function ($data) {
              $countJob  = Pjob::where('projmgmt_id', $data->id)->get();
              $url = asset('images/redcrab_50x50.png');
              $counter = $countJob->count();

              if ($counter >= 1){
                return "<img  class='crab_disabled' style='width:25px;height:25px;cursor:pointer;' src='{$url}' >";
              }else{
                return '<img onclick="deleteRow(' . $data->id . ')" style="width:25px;height:25px;cursor:pointer;"
                 src="'.$url.'" >';
              }
              

            })
            ->escapeColumns([])
            ->make(true);
    }


    public function store(Request $request)
    {
        //Create a new poject here
        try {
            
            $SystemID        = new SystemID('projmgmt');
			$user_data		 = new UserData();

            $project = new Projmgmt;
            $project->systemid =  $SystemID;
            $project->name = 'Project name';
            $project->revenue = '0.00';
            $project->cost = '0.00';
            $project->save();

			DB::table('projmgmtmerchant')->insert([
				"projmgmt_id" => $project->id,
				"merchant_id" => $user_data->company_id(),
				"created_at"  => date('Y-m-d H:i:s'),
				"updated_at"  => date('Y-m-d H:i:s')
			]);


            $msg = "Project added successfully";
            return view('layouts.dialog', compact('msg'));

        } catch (\Exception $e) {
            $msg = $e->getMessage();
            return view('layouts.dialog', compact('msg'));
        }
    }

    public function delete(Request $request)
    {
        //Create a new poject here
        try {
                      
            $project = Projmgmt::where('id', $request->id);
            
            if($project){
              $project->delete();
            }

            $msg = "Project deleted successfully";
            return view('layouts.dialog', compact('msg'));

        } catch (\Exception $e) {
            $msg = $e->getMessage();
            return view('layouts.dialog', compact('msg'));
        }
    }

    public function deletePjob(Request $request)
    {
        //Create a new poject here
        try {
            
            
            $project = Pjob::where('id', $request->id)->first();
            if($project){
              
              $projmgmt = Projmgmt::find($project->projmgmt_id);
              
              if (empty($projmgmt)) {
                throw new Exception("Projmgmt record not found where id=$project->projmgmt_id", 1);  
              }

              //$projmgmt->receivable -= $project->receivable;
              //$projmgmt->update();

              $project->delete();
              $this->calculate_rows($merchant_id = null,  $pjob_id = null,  $projmgmt_id = $project->projmgmt_id, $aggresive = true,  $update  = true, null);
            }

            $msg = "Job deleted successfully";
            return view('layouts.dialog', compact('msg'));

        } catch (\Exception $e) {
            Log::info($e);
            $msg = $e->getMessage();
            return view('layouts.dialog', compact('msg'));
        }
    }

    public function PjobStore(Request $request)
    {
        //Create a new poject here
        try {
            
            $SystemID        = new SystemID('projmgmt_job');
            $pjob = new Pjob;
            $pjob->systemid =  $SystemID;
            $pjob->projmgmt_id =  $request->id;
            $pjob->name = 'Job name';
            $pjob->start = '';
            $pjob->completion = '';
            $pjob->actual_start = '';
            $pjob->completed_at = '';
            $pjob->status = '';
            $pjob->revenue = '0.00';
            $pjob->cost = '0.00';
            $pjob->save();


            $msg = "Job added successfully";
            return view('layouts.dialog', compact('msg'));

        } catch (\Exception $e) {
            $msg = $e->getMessage();
            return view('layouts.dialog', compact('msg'));
        }
    }


    public function showEditModal(Request $request)
    {
        try {
            $allInputs = $request->all();
            $id        = $request->get('id');
            $modal = $request->get('field_name');
            $pjob = null;

            $validation = Validator::make($allInputs, [
                'id'         => 'required',
                'field_name' => 'required',
            ]);

            if($request->type == 'project'){
                $data = Projmgmt::where('id', $id)->first();
            }

            if($request->type == 'job'){
                $data = Pjob::where('id', $id)->first();
            }

            if($request->field_name == 'doc'){

                if ($request->has('type')) {
                    
                    if ($request->type == 'projmgmt') {  
                      $data = Prjmgmtdoc::where("projmgmt_id", $id)->get();  

                    } else if ($request->type == 'pjob') {
                      $data = Prjjobdoc::where("pjob_id", $id)->get();

                    } else {
                      throw new Exception("type invalid", 1);
                    }

                } else {
                  throw new Exception("type not found", 1);
                }
            }

            if($request->field_name == "revenue" && $request->type == 'pjob') {
				$data = Pjobuat::where("pjob_id", $id)->get();
				$pjob = Pjob::where('id', $id)->first();

				if ($data->isEmpty()) {
					$data = collect('');
				}
            }

            return view('projmgmt.projmgmt_edit-modal',
				compact(['id', 'modal','data','pjob']));

		} catch (\Exception $e) {
				 $response = Log::info($e);
		   abort(404);
		}
    }

   

    public function update(Request $request)
    {
        try {

          if($request->type == "projectName"){
            
            $id = $request->project_id;
            $name = $request->project_name;

            $projmgmt = Projmgmt::where('id', $id)->first();

            if($request->project_name != $projmgmt->name){
                $projmgmt->name = $name;
                $projmgmt->save();
                $msg = "Data updated";
                return view('layouts.dialog', compact('msg'));
            }
            
           
          }


          if($request->type == "jobName"){
            $id = $request->pjob_id;
            $name = $request->pjob_name;

            $pjob = Pjob::where('id', $id)->first();
            if($request->pjob_name != $pjob->name){
              $pjob->name = $name;
              $pjob->save();

              $msg = "Data updated";
              return view('layouts.dialog', compact('msg'));
            }
            
            
          }

        } catch (\Exception $e) {
            $msg = "Some error occured";
            log::error($e);
            return view('layouts.dialog', compact('msg'));
        }
    }


    public function comp_update(Request $request)
    {
        try {
            
            $id = $request->pjob_id;

            $pjob = Pjob::where('id', $id)->first();

            if (!$pjob) {
              throw new Exception("Pjob record not found", 1);
              
            }
            $default_null = "0000-00-00";

            $validated_start = false;
            $validated_completed = false;
            $x=  null;

            switch(strtotime($request->start)) {
              case false:
                if ( empty($pjob->start)) {
                    $validated_start = true;
                }
        
                if ($pjob->start == $default_null ) {
                  $validated_start = true;
                }
              break;
              case strtotime($pjob->start):
                $validated_start = true;
              break;
            }
            $x = null;

            switch(strtotime($request->completion)) {
              
              case false:
                if ( empty($pjob->completion) ) {
                    $validated_completed = true;
                }

                if ($pjob->completion == $default_null ) {
                  $validated_completed = true;
                }
              break;

              case strtotime($pjob->completion):
                $validated_completed = true;
              break;
            }


           if ($validated_completed == true  ) {
            if ($validated_start == true) {
              
              return;
              
            } 
           }
          

            $start =  empty($request->start) ? '00-0000-00':date('Y-m-d',strtotime($request->start));
            $completion = empty($request->completion) ? '00-0000-00':date('Y-m-d',strtotime($request->completion));
              
           
              $pjob->start =   $start;
              $pjob->completion = $completion;
              $pjob->update();

              $msg = "Date updated";
              return view('layouts.dialog', compact('msg'));
            
        } catch (\Exception $e) {
            $msg = "Some error occured";
            log::error($e);
            return view('layouts.dialog', compact('msg'));
        }
    }


       public function upload_doc(Request $request)
    {
      try {
        if ($request->hasfile('file')) {
            $this->user_data = new UserData();
            $file = $request->file('file');
            $blockNo = $request->blockNo;

    
            $extension = $file->getClientOriginalExtension(); // getting image extension
            $filename = time() . '.' . $extension;

          
          if ($request->type == 'projmgmt')
          {

            $attachment = new Prjmgmtdoc();
            $attachment->projmgmt_id = $request->project_id;
            $attachment->docfile = $filename;
            $attachment->save();

            $location = "/images/projmgmt/$request->project_id/document/";
            $src = "/projmgmt/$request->project_id/document/$filename";

          } else if ($request->type == 'pjob') {

            $attachment = new Prjjobdoc();
            $attachment->pjob_id = $request->project_id;
            $attachment->docfile = $filename;
            $attachment->save();

            $location = "/images/pjob/$request->project_id/document/";
            $src = "/pjob/$request->project_id/document/$filename";

          }

            $this->check_location($location);

            $file->move(public_path() . ($location), $filename);

            $return_arr = array(
                "name" => $file->getClientOriginalName(),
                "size" => 000,
                "type" => in_array($extension, array(
                    'jpg', 'JPG', 'png', 'PNG', 'jpeg', 'JPEG', 'gif', 'GIF', 'bmp', 'BMP', 'tiff', 'TIFF',
                )) ? "image" : "doc",
                "src" => $src,
                "id" => $attachment->id);

            return response()->json($return_arr);
          } else {
            throw new Exception("No file selected", 1);
            
          }
        } catch (\Exception $e){
          \Log::info($e);
          return abort(403);
        }
      //  } else {
        //}
    }

    public function show_doc_($type, $project_id,$filename)
    {
      if ($type == 'projmgmt')
      {
        $is_exist = Prjmgmtdoc::where(["projmgmt_id" => $project_id, "docfile" => $filename])->first();
      } else if ($type == 'pjob') {
        $is_exist = Prjjobdoc::where(["pjob_id" => $project_id, "docfile" => $filename])->first();
      } else {
        $is_exist = false;
      }

        if ($is_exist) {
            $headers = array('Content-Type: application/octet-stream', "Content-Disposition: attachment; filename=$filename");
           
            $location = "/images/$type/$project_id/document/$filename";

            if (!file_exists(public_path() . $location)) {
                return abort(500);
            }

            $response = \Response::file(public_path() . ($location), $headers);

            ob_end_clean();

            return $response;

        } else {
            return abort(500);
        }
    }

  public function delDOC(Request $request)
    {

        $this->user_data = new UserData();

        $validation = Validator::make($request->all(), [
            'fileName' => "required",
            'project_id'=>"required"
        ]);

        $project_id = $request->project_id;
        $filename = $request->fileName;

       if ($request->has('type')) {
              
          if ($request->type == 'projmgmt')
          {  
            $is_exist = Prjmgmtdoc::where(["projmgmt_id" => $project_id, "id" => $filename])->first();
             $count = Prjmgmtdoc::where(["projmgmt_id" => $project_id])->get()->count();
             $count -= 1;

          } else if ($request->type == 'pjob') {
            $is_exist = Prjjobdoc::where(["pjob_id" => $project_id, "id" => $filename])->first();
            $count = Prjjobdoc::where(["pjob_id" => $project_id])->get()->count();
            $count -= 1;
          } else {
            throw new Exception("type invalid", 1);
          }

          } else {
            throw new Exception("type not found", 1);
          }


        if ($is_exist) {
            $is_exist->delete();
            return response()->json(array("status" => "deleted", "count" => $count));
        } else {
            return response()->json(array("status" => "Error occured"));
        }

    }

    public function check_location($location)
    {
        $location = array_filter(explode('/', $location));
        $path = public_path();

        foreach ($location as $key) {
            $path .= "/$key";
            if (is_dir($path) != true) {
                mkdir($path, 0777, true);
            }
        }
    }

    public function Pjobuat_new(Request $request){
    //Pjobuat

      try {

        $description = $request->description;
        $payment = $request->payment;
        $del_intersect = [];

        $pjob = Pjob::find($request->pjob_id);
          
        if (empty($pjob)) {
          throw new Exception("Pjob record not found where pjob.id=$request->pjob_id", 1);  
        }

        if (count($description) > 0) {
          
          $existing = Pjobuat::select('description','payment')->where('pjob_id',$request->pjob_id)->get()->toArray();

          //dd($existing,$if_exist,Array("description"=> $description[1], "payment"=> $payment[1]));

          for ($x = 0; $x < count($description); $x++) {
            
            if ($description[$x] == null || $payment[$x] == null) {
              continue;
            }

            $array = Array("description"=> $description[$x], "payment"=> $payment[$x]);
            $if_exist = in_array($array, $existing);

            if ($if_exist) {
              $del_intersect[] = $array;
              continue;
            }

            $pjobuat = new Pjobuat();
            $pjobuat->pjob_id = $request->pjob_id;
            $pjobuat->description = $description[$x];
            $pjobuat->payment = $payment[$x] ;
            $pjobuat->save();
          }
          
        
        
          $delete = [];

          foreach($existing as $e) {
            if (!in_array($e,$del_intersect)) {
                $delete[]  = $e;
            }
          }
       
          foreach ($delete as $d) {

            $is_active = Pjobuat::where([
              'pjob_id' =>  $request->pjob_id,  "payment" => $d['payment'], "description" => $d['description']
              ])->first();

            if (!empty($is_active)) {
              $is_active->delete();
            }
          }
        
          $this->calculate_rows($merchant_id = null,  $pjob_id = $request->pjob_id,  $projmgmt_id = null, $aggresive = true,  $update  = true, null);

        } else {
          throw Exception("0 array count");
        }

      $msg = "Data saved successfully";
      return view('layouts.dialog', compact('msg'));

      } catch (\Exception $e) {
        Log::info($e);
        abort(404);
      }
    }

    public function Pjobuat_togleActive(Request $request) {
      try {

        if (!$request->has('rev_id') || !$request->has('pjob_id')) {
          throw new Exception("id or pjob_id missing with Request", 1);
          
        }
        
        $rev = Pjobuat::where([
          'id'=>$request->rev_id,
          'pjob_id' =>  $request->pjob_id
          ])->first();

        if(empty($rev)) {
          throw new Exception("Pjobuat record not found for id $request->rev_id", 1);
        }

        $pjob_id = $request->pjob_id;
        $pjob = Pjob::find($pjob_id);
          
        if (empty($pjob)) {
          throw new Exception("Pjob record not found where pjob.id=$pjob_id", 1);  
        }

        $rev->answer = $rev->answer == 0 ? 1:0;
        $rev->update();

        $this->calculate_rows($merchant_id = null,  $pjob_id = $request->pjob_id,  $projmgmt_id = null, $aggresive = true,  $update  = true, null);

        return response()->json(["status"=>true]);

      } catch (\Exception $e) {
        Log::info($e);
        abort(404);
      }
    }

    public function contactor_togleActive(Request $request) {
      try {

        $this->user_data = new UserData();
        $merchant_id_self = $this->user_data->company_id();
        $pjob_id = $request->pjob_id;
        $merchant_id = $request->merchant_id;
        $m_type = $request->merchant_type;
        
        //comman tables
        $model           = new rawmaterial();
        $pjobproduct_model = new Pjobproduct();
        $product_model = new product();

          //onslute        
        //  $pjobMerchantProduct_model = new PjobMerchantProduct();
        //   $pjobservice_model = new Pjobservice();
        // $merchantproduct_model = new merchantproduct();

        //top interface
        if ($m_type == 'twoWay') {

          $pjobMerchant = $this->pjobmerchant_allocation($pjob_id ,$merchant_id); 
         
          $pjobProductLink_model = new PjobMerchantProduct();
         
          $where_condition_Prd_lk = ['pjobmerchant_id' =>  $pjobMerchant->id];
       
        } else if ($m_type == 'oneWay') {

          $pjobMerchant = $this->pjoboneway_allocation($pjob_id, $merchant_id);
          
          $pjobProductLink_model = new PjobOneWayProduct();

          $where_condition_Prd_lk = ['pjoboneway_id'=>$pjobMerchant->id];

        } else {

          throw new Exception("One way merchant", 1);

        }
  
        $add_product = json_decode($request->json_string_add_product);
        $del_rm = json_decode($request->josn_string_del_rm);
        $del_service = json_decode($request->josn_string_delete_service);

       if (is_array($add_product)) {
          
          $service_products = array_filter($add_product,function($z){
              return $z->formType == "service";
          });

          $raw_material_products = array_filter($add_product,function($z){
            return $z->formType == "rm";
          });

        } else {

          $raw_material_products = $service_products =  [];

        }
  
        if (is_array($del_service)) {
          
          $delete_all_service = array_filter($del_service,function($z){
            
            return $z->formType == "delete_all_service";

          });

        } else {
          
          $delete_all_service = [];

      }

      $existing_service_fk = $pjobProductLink_model->where($where_condition_Prd_lk)->whereNotNull("pjobproduct_id")->get();
      $existing_service = $pjobproduct_model->whereIn('id',$existing_service_fk->pluck("pjobproduct_id"))->get();

      $existing_product_fk = $pjobProductLink_model->where($where_condition_Prd_lk)->whereNotNull("product_id")->get();

      //deleting raw materials
      if (is_array($del_rm) && count($del_rm) > 0) {
        //filtering ids
        $del_rm = array_column($del_rm,'id');
        
        $del_rm = array_map(  function  ($z)  {
        
          return $z < 0 ? ($z * -1) : $z;
        
        } ,  $del_rm);
        
        $pjob_products  = $pjobProductLink_model->where($where_condition_Prd_lk)->whereIn('product_id',$existing_product_fk->pluck('product_id'))->get();

        if (!$pjob_products->isEmpty()) {

          $pjob_products->map(function($z){

            $z->delete();

          });

        }
      }


    //deleting service
      if (count($delete_all_service) > 0) {
        if (  !empty($existing_service_fk)  ) {
          $existing_service_fk->map(  function  ($f){
            $f->delete();
         });
        }
        
        if (!empty($existing_services)) {
          $existing_services->map(  function  ($f) {
            $f->delete();
          });
        }
      }

      if (count($service_products) > 0) {

        foreach ($service_products as $input_product)
        {
          $SystemID = new SystemID('pjobproduct');
          $SystemID = $SystemID->__toString();

          $pjob_product = new $pjobproduct_model;
          $pjob_product->systemid = $SystemID;
          $pjob_product->name = $input_product->name;
          $pjob_product->price = $input_product->price;
          $pjob_product->save();

          $pjobProductLink = new $pjobProductLink_model;
          $pjobProductLink->pjobproduct_id = $pjob_product->id;
          $pjobProductLink->product_id = null;
          $pjobProductLink->qty = 1;
          $pjobProductLink->price = $input_product->price;
          $pjobProductLink->active = 1;
          
          if ($m_type == 'twoWay') {
            $pjobProductLink->pjobmerchant_id  = $pjobMerchant->id;
          } else if ($m_type == 'oneWay') {
            $pjobProductLink->pjoboneway_id  = $pjobMerchant->id;
          }

          $pjobProductLink->save();
        }
      }


      if (count($raw_material_products) > 0) {
        foreach ($raw_material_products as $input_product)
        {

          $product_id = $input_product->id;

          $data = $model->where('product_id',$product_id)->first();
          $product  = $product_model->where('ptype', 'rawmaterial')->where('id', $data->product_id)->first();
        
          
          if (empty($data) || empty($product)) {
            throw new Exception("Product not found in raw material or product", 1);  
          }

          $pjobProductLink = new $pjobProductLink_model();
          $pjobProductLink->pjobproduct_id = null;
          $pjobProductLink->product_id = $product->id;;
          $pjobProductLink->price = $input_product->price / $input_product->qty;
          $pjobProductLink->qty = !empty($input_product->qty) ? $input_product->qty:1;
          $pjobProductLink->active = 1;
          
          if ($m_type == 'twoWay') {

            $pjobProductLink->pjobmerchant_id  = $pjobMerchant->id;

          } else if ($m_type == 'oneWay') {

            $pjobProductLink->pjoboneway_id  = $pjobMerchant->id;

          }

          $pjobProductLink->save();
        }
      }

      if (!count($service_products) > 0 && !is_array($add_product)  && !count($delete_all_service)  > 0 ) {
  
        $pjobMerchant->active = $pjobMerchant->active == 0 ? 1:0;

      } else {

        $pjobMerchant->active = 1;

      }

      $pjobMerchant->update();

   
     $this->calculate_rows($merchant_id = $request->merchant_id,  
    $pjob_id = $request->pjob_id,  $projmgmt_id = $pjobMerchant->id, 
      $aggresive = true,  $update  = true, $m_type = $m_type);

      return response()->json(["status"=>"done"]);
          
      } catch (\Exception $e) {
        Log::info($e);
        abort(404);
      }
    }

    public function pjobmerchant_allocation($pjob_id, $merchant_id,  $force_create=true) {
      try {

          $pjobMerchant_ = PjobMerchant::where(['pjob_id' => $pjob_id, "merchant_id" => $merchant_id ])->first();
          
          if (empty($pjobMerchant_) && $force_create == true) {
            $pjobMerchant_ = new PjobMerchant();
            $pjobMerchant_->pjob_id = $pjob_id;
            $pjobMerchant_->merchant_id = $merchant_id;
            $pjobMerchant_->save();
          } else if (empty($pjobMerchant_) && $force_create == false) {
            $pjobMerchant_ = null;
          }


          return $pjobMerchant_;

      } catch (\Exception $e) {
        Log::info($e);
        abort(404);
      }
    }

    public function pjoboneway_allocation($pjob_id, $merchant_id,  $force_create=true) {
      try {

          $pjobOneWay_ = PjobOneWay::where(['pjob_id' => $pjob_id, "oneway_id" => $merchant_id ])->first();
        
          if (empty($pjobOneWay_) && $force_create == true) {
          
            $pjobOneWay_ = new PjobOneWay();
            $pjobOneWay_->pjob_id = $pjob_id;
            $pjobOneWay_->oneway_id = $merchant_id;
            $pjobOneWay_->save();

          } else if (empty($pjobOneWay_) && $force_create == false) {
        
            $pjobOneWay_ = null;

          }

          return $pjobOneWay_;

      } catch (\Exception $e) {
        Log::info($e);
        abort(404);
      }
    }


    private function calculate_rows($merchant_id = null,  $pjob_id = null,  $projmgmt_id = null, $aggresive = false,  $update  = true, $m_type) {
      $merchant_level_total = 0;
      
      $pjob_level_revenue = 0;
      $pjob_level_recievable = 0;
      $pjob_level_cost = 0;
      $pjob_ = 0;


      $projmgmt_level_cost = 0;
      $projmgmt_level_revenue  = 0;
      $projmgmt_level_receivable = 0;
      
      if ($merchant_id != null) {
      
    
          $pjobMerchant_ids = PjobMerchant::where(['pjob_id' => $pjob_id,"active" => 1])->pluck('id');
         
          $all_products_twoway = PjobMerchantProduct::where('active',1)->whereIn('pjobmerchant_id', $pjobMerchant_ids)->get();

          $pjobMerchant_ids = PjobOneWay::where(['pjob_id' =>  $pjob_id, "active" => 1])->pluck('id');
          
          $all_products_oneway = PjobOneWayProduct::where('active',1)->whereIn('pjoboneway_id', $pjobMerchant_ids)->get();


       
          $merchant_level_total = $all_products_twoway->reduce(function($m,$z) {
            return $m + $z->price * $z->qty;
          });

          $merchant_level_total += $all_products_oneway->reduce(function($m,$z) {
            return $m + $z->price * $z->qty;
          });
  
        if ($aggresive == true) {
          $pjob_id = $pjob_id;
        }
      }

      //job level row calculations
      if ($pjob_id != null) {
        $pjobuat = Pjobuat::where("pjob_id",$pjob_id)->get();
        $pjob = Pjob::find($pjob_id);
  
        $pjob_level_revenue = $pjobuat->sum('payment');

        $filter_pjobuat = $pjobuat->filter(function($f){
          return $f->answer == 1;
        });

        $pjob_level_recievable = $filter_pjobuat->sum('payment');

        if ($update == true) {
          if ($merchant_id  != null) {
            $pjob->cost = $merchant_level_total;
          }
          $pjob->revenue = $pjob_level_revenue;
          $pjob->receivable = $pjob_level_recievable;
          $pjob->update();
          
        }

        if ($aggresive == true) {
          $projmgmt_id = $pjob->projmgmt_id;
        }
      }

      //project level row calculations
      if ( $projmgmt_id != null) {
        $projmgmt = Projmgmt::find($projmgmt_id);
        $pjob = Pjob::where('projmgmt_id',$projmgmt_id)->get();
        
        $projmgmt_level_cost = $pjob->sum('cost');
        $projmgmt_level_revenue  = $pjob->sum('revenue');
        $projmgmt_level_receivable = $pjob->sum('receivable');
        
        if ($update == true) {
          $projmgmt->cost = $projmgmt_level_cost;
          $projmgmt->revenue = $projmgmt_level_revenue;
          $projmgmt->receivable = $projmgmt_level_receivable;
          $projmgmt->update();
        }
      }

      return Array( 
      "merchant_level_total"  =>  $merchant_level_total,
      "pjob_level_revenue" => $pjob_level_revenue,
      "pjob_level_recievable"=> $pjob_level_recievable,
      "pjob_level_cost" => $pjob_level_cost,
      "projmgmt_level_cost" =>  $projmgmt_level_cost,
      "projmgmt_level_revenue"  =>  $projmgmt_level_revenue,
      "projmgmt_level_receivable" =>  $projmgmt_level_receivable
      );


    }

    private function getContractorData($data) {
    
      $data->map(function($z){
        $pjob = request()->pjob_id;
      
        if ($z->m_type == 'twoWay') {
          
          $pjobMerchant = PjobMerchant::where(['merchant_id' => $z->id, "pjob_id"=>$pjob])->first(); 
          
          if (empty($pjobMerchant)) {
            return;
          }

          $products = PjobMerchantProduct::where(['pjobmerchant_id' =>  $pjobMerchant->id])->get();

        } else if ($z->m_type == 'oneWay') {

          $pjobMerchant = PjobOneWay::where(['pjob_id' =>  $pjob, "oneway_id" =>  $z->id ])->first();
       
          if (empty($pjobMerchant)) {
            return;
          }

          $products = PjobOneWayProduct::where( ['pjoboneway_id'=>$pjobMerchant->id])->get();

        }
          
        if (empty($pjobMerchant) || empty($products)) {
          return;
        }

        $raw_materals = $products->filter(function($z){
            return $z->pjobproduct_id == null;
        });

        $raw_material_cost = $raw_materals->reduce(function($m,$z) {
            return $m + $z->price * $z->qty;
        });

        $servcie = $products->filter(function($z){
          return $z->product_id == null;
        });

        $pro_service_cost = $servcie->reduce(function($m,$z) {
          return $m + $z->price;
        });

 
        $z->raw_material_cost = $raw_material_cost;
        $z->pro_service_cost = $pro_service_cost;
        $z->total_cost = $raw_material_cost + $pro_service_cost ;
        $z->pro_service_cost = $pro_service_cost;

        if ($pjobMerchant->active == 1) {
          
          $z->merchant_active_cost = $raw_material_cost + $pro_service_cost;

        } else {

          $z->merchant_active_cost = 0;

        }

        return $z;
      });
    
      return $data;
    }

    
}
