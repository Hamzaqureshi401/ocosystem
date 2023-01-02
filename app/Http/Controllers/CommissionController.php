<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Models\Commission;
use App\Models\CommAgent;
use App\Models\CommCompany;
use App\Models\CommScheme;
use App\Models\CommSchemeLevel;
use App\Models\CommSchemeMgmtAgent;
use App\Classes\SystemID;
use DB;
use Log;

class CommissionController extends Controller
{
	//Show Commision Main Page
    public function showCommissionView() {
        return view('commission.commission');
    }
    //Show agent page
     public function showAgent() {
        return view('commission.agent');
    }
    //Show Company Statement Page
    public function showCompanySatement() {
        return view('commission.company-statement');
    }
   //Show personal statement
   public function showPersonalSatement() {
        return view('commission.personal-statement');
    }
    public function ShowSchemeManagement(Request $request){
        
        $data = DB::table('comm_schememgmt')->
			join('comm_company', 'comm_company.id', '=',
				'comm_schememgmt.company_id')->
			select('comm_schememgmt.*', 'comm_company.name',
				'comm_company.systemid')->
			latest('comm_schememgmt.created_at')->
			get();

 
		$scheme = CommScheme::get();
		if (count($scheme) > 0) {
			for ($i = 0; $i < count($data); $i++) {
				for ($x = 0; $x < count($scheme); $x++) {
					if ($data[$i]->comm_scheme_id == $scheme[$x]->id) {
						$data[$i]->scheme_name = $scheme[$x]->name;
						$data[$i]->scheme_id = $scheme[$x]->id;
						$data[$i]->company_pct = $scheme[$x]->company_pct;
						$data[$i]->pool_pct = $scheme[$x]->pool_pct;
						$data[$i]->agent_pct = $scheme[$x]->agent_pct;
						$data[$i]->source = '';
						break;
					} else {
						$data[$i]->scheme_name = '';
						$data[$i]->scheme_id = 1;
						$data[$i]->source = '';
					}
				}
			}
        } else {
            for ($i = 0; $i < count($data); $i++) {
				$data[$i]->scheme_name = '';
				$data[$i]->scheme_id = 1;
				$data[$i]->source = '';
            }
        }
        
        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('systemid', function($data){
				$systemid = '<div data-toggle="modal" style="margin-top: 0;"'.
				'data-id="'.$data->company_id.'">'.$data->systemid.'</div>';
				return $systemid;
			})
            ->addColumn('company_name', function($data){
				$company_name = '<div data-toggle="modal" style="cursor:pointer;  margin-top: 0;" data-target="#editCompany"
				class="show_edit_company_modal os-linkcolor" data-id="'.$data->company_id.'" 
				data-company_name="'.$data->name.'">'.$data->name.'</div>';
				return $company_name;
			})
            ->addColumn('scheme_name', function($data){
				$comm_scheme = '<div style="cursor: pointer;  margin-top: 0;" class="os-linkcolor text-center 
				show_edit_scheme_modal" data-scheme_id="'.
				$data->scheme_id.'">'.$data->scheme_name.'</div>';
				return $comm_scheme;
			})
            ->addColumn('type', function($data){
            	if($data->type == ''){
            		$type = 'Select';
            	}else{
            	 $type = ucfirst($data->type);
            	}
            	
				$type = '<div class="show_edit_type_modal text-center '.
				'os-linkcolor" style="cursor:pointer; margin-top: 0;"'.
				'data-toggle="modal" data-target="#type_modal" data-id="'.
				$data->id.'">'.$type.'</div>';
				return $type;
			})
            ->addColumn('pool_amt', function($data){
				$pool_amt = '<div style="margin-top: 0;">'.number_format($data->pool_amt, 2).'</div>';
				return $pool_amt;
			})
            ->addColumn('commission_amt', function($data){
				$commission_amt = '<div style="margin-top: 0">'.number_format($data->commission_amt, 2).'</div>';
				return $commission_amt;
			})
			->addColumn('button1', function($data){
				return '<div'. 
					' data-toggle="modal" data-target="#commission_scheme_modal"'.
					' data-scheme_id="'.$data->scheme_id.'" data-c_pct="'.
					$data->company_pct.'" data-_a_pct="'.$data->agent_pct.
					'" data-_p_pct="'.$data->pool_pct.
					'" class="text-center commission_scheme_definition"'.
					' style="">
					<img class="" src="/images/yellowcrab_50x50.png"
					style="width:25px;height:25px;cursor:pointer"/>
					</div>';


				/*
				$button1  = '<input type="image" src="images/yellowcrab_25x25.png" alt=""'.
				' data-toggle="modal" data-target="#commission_scheme_modal"'.
				' data-scheme_id="'.$data->scheme_id.'" data-c_pct="'.
				$data->company_pct.'" data-_a_pct="'.$data->agent_pct.
				'" data-_p_pct="'.$data->pool_pct.'" class="text-center commission_scheme_definition"'.
				' style="cursor: pointer;"/>';
				return $button1;
				*/
			})
            ->addColumn('button2', function($data){
            	if ($data->name == 'Company Name') {
					$cursor = 'pointer-events:none;';
            		$state = 'filter:grayscale(100%) brightness(200%);';
            	} else {
					$cursor = 'cursor:pointer';
					$state = '';
            	}

				return '<div data-field="bluecrab" 
					data-toggle="modal" data-company_name="'.$data->name.'"
					style="'.$cursor.'"
					class="show_agent text-center align-items-center">
					<img src="/images/bluecrab_50x50.png"
					style="'.$state.'width:25px;height:25px;"/>
					</div>';
			})
            ->addColumn('button3', function($data){
				$button3 = '<input type="image" src="images/redcrab_25x25.png" data-id="'.
				$data->company_id.'" data-scheme_id="'.$data->scheme_id.
				'"alt="" class="text-danger bg-redcrab1 delete_button"'.
				'data-toggle="modal" data-target="#showMsgModal"'.
				'style="align-items:center; margin-top: 0; cursor: pointer;" />';
                return $button3;
			})
            ->rawColumns(['systemid','company_name','scheme_name', 'type', 'pool_amt',
				'commission_amt','button1','button2','button3'])
            ->make(true);
    }


    public function index(Request $request){
        //Create a new Comm here
        try {
            $newComm = new Commission();
            $newCommLevel = new CommSchemeLevel();
            $newCommScheme = new CommScheme();
            $newCommCompany = new CommCompany();
            $a =  new SystemID('comm_company');
            $new_system_id = $a->__toString();
            $x = 0;
            
            $newCommCompany->systemid   = $new_system_id;
            $newCommCompany->name   = 'Company Name';
            $newCommCompany->save();
            
            $new_id = $newCommCompany->id;

            $newCommScheme->name = 'Select';
            $newCommScheme->save();

            $comm_scheme_id = $newCommScheme->id;

            $newCommLevel->comm_scheme_id = $comm_scheme_id;
            $newCommLevel->save();

            $newComm->company_id = $new_id;
            $newComm->comm_scheme_id  = $comm_scheme_id;
            $newComm->type = 'Select';
            $newComm->pool_amt  = $x;
            $newComm->commission_amt = $x;

            $newComm->save();

            $response['success'] = true;
            $response['message'] = 'Company added successfully';
        } catch (\Exception $e) {
            $response['success'] = false;
            $response['message'] = $e->getMessage();
        }
        return $response;
    }


    public function destroy(Request $request){

        //Create a new Comm here
        try {
            $comm_id = $request->get('id');

            Commission::where('company_id', $comm_id)->delete();
            CommCompany::where('systemid', $comm_id)->delete();

            $response['success'] = true;
            $response['message'] = 'Company deleted successfully';
        } catch (\Exception $e) {
            $response['success'] = false;
            $response['message'] = $e->getMessage();
        }
        return $response;
    }

    public function showAgentView() {
		return view('commission.agent');
    }
    
    public function ShowAgentData(Request $request){
		$data = DB::table('comm_agent')->
			join('comm_schememgmt_agent', 'comm_agent.id', '=',
				'comm_schememgmt_agent.agent_id')->
			select('comm_agent.*', 'comm_schememgmt_agent.*')->
			groupBy('comm_agent.id')->
			latest('comm_agent.created_at')->
			get();


        $scheme = Commission::get();
        if (count($scheme) > 0) {
            for ($i = 0; $i < count($data); $i++) {
                for ($x = 0; $x < count($scheme); $x++) {
                    if ($data[$i]->comm_scheme_id == $scheme[$x]->comm_scheme_id) {
						$data[$i]->pool_amt = $scheme[$x]->pool_amt;
						$data[$i]->commission_amt = $scheme[$x]->commission_amt;
						break;
                    } else {
                        $data[$i]->pool_amt  = 00;
                        $data[$i]->commission_amt= 00;
                    }
                }
            }
        } else {
            for ($i = 0; $i < count($data); $i++) {
                $data[$i]->pool_amt  = 00;
                $data[$i]->commission_amt= 00;
            }
        }


		return DataTables::of($data)
			->addIndexColumn()
			->addColumn('name', function($data){
				 $company_name = '<div style="cursor: pointer" class="show_edit_agent_name_modal os-linkcolor" '.
				 'data-id="'.$data->agent_id.'" 
				 data-agent_name="'.$data->name.'">'.$data->name.'</div>';
				 return $company_name;
			})
			->addColumn('pool_amt', function($data){
				 $pool_amt = number_format($data->pool_amt, 2);
				 return $pool_amt;
			})
            ->addColumn('commission_amt', function($data){
				 $commission_amt = number_format($data->commission_amt, 2);
				 return $commission_amt;
			})
			->addColumn('button2', function($data){
				$button2 =  '<input type="image" src="/images/bluecrab_25x25.png" 
				id="bluecrab_tab" style="display:flex;align-items:center;'.
				'justify-content:center;" class="btn-primary bg-bluecrab personalStatement" />';
				return $button2;
			})
            ->addColumn('button1', function($data){
				 $button1  ='<img src="/images/redcrab_25x25.png" data-field="deleted"'.
				 ' style="cursor: pointer" data-id="'.$data->id.'"class="delete_button" >';
				 return $button1;
			})
            ->rawColumns(['button1', 'button2', 'name'])
            ->make(true);
	}


    public function add_agent(Request $request){

        try {
            $newAgent = new CommAgent();
            $a =  new SystemID('agent');
            $new_system_id = $a->__toString();

            $newAgent->name   = 'Commission Earner';
            $newAgent->company_name  = 'Company Name';
            $newAgent->business_reg_no  = '0000';
            $newAgent->address  = '16065 1st Eve';
            $newAgent->mobile_no = '0000';
            $newAgent->status = 'pending';
            $newAgent->systemid = $new_system_id;

            $newAgent->save();

            $newSchemeMgmtAgent = new CommSchemeMgmtAgent();
            $newSchemeMgmtAgent->comm_scheme_id = 51;
            $newSchemeMgmtAgent->agent_id = $newAgent->id;
            $newSchemeMgmtAgent->save();

            $response['success'] = true;
            $response['message'] = 'Commission Earner added successfully';

        } catch (\Exception $e) {
            $response['success'] = false;
            $response['message'] = $e->getMessage();
        }
        return $response;
    }


	public function updateCompanyName(Request $request){

		//Create a new Comm here
		try {
			$id = $request->get('id');
			$comm_name = $request->get('name');

			CommCompany::where('id', $id)->
				update((['name' => $comm_name]));

			$response['success'] = true;
			$response['message'] = 'Updated successfully';

		} catch (\Exception $e) {
			$response['success'] = false;
			$response['message'] = $e->getMessage();
		}
		return $response;
	}


	public function updateType(Request $request){
		//Create a new Comm here
		try {
			$comm_id = $request->get('id');
			$type = $request->get('type');

			Commission::where('id', $comm_id)->
				update((['type' => $type]));

			$response['success'] = true;
			$response['message'] = 'Updated successfully';

		} catch (\Exception $e) {
			$response['success'] = false;
			$response['message'] = $e->getMessage();
		}
		return $response;
	}


	//Update scheme name from a modal
	public function updateSchemeName(Request $request){
		//Create a new Comm here
		try {
			$comm_id = $request->get('id');
			$comm_scheme_name = $request->get('comm_scheme_name');

			CommScheme::where('id', $comm_id)->update((['name' => $comm_scheme_name]));

			$response['success'] = true;
			$response['message'] = 'Updated successfully';

		} catch (\Exception $e) {
			$response['success'] = false;
			$response['message'] = $e->getMessage();
		}
		return $response;
	}




    	//Update scheme definition from a modal
		public function updateSchemeDefinition(Request $request){
			//Create a new Comm here
			try {
				$comm_id = $request->get('comm_id');
				$company_percentage = $request->get('company_percentage');
				$pool_percentage = $request->get('pool_percentage');
				$agent_percentage = $request->get('agent_percentage');

				CommSchemeLevel::where('comm_scheme_id', $comm_id)->
					update((['company_pct' => $company_percentage,'pool_pct' => $pool_percentage, 'agent_pct' => $agent_percentage]));

				$response['success'] = true;
				$response['message'] = 'Updated successfully';

			} catch (\Exception $e) {
				$response['success'] = false;
				$response['message'] = $e->getMessage();
			}
			return $response;
		}


	public function destroyAgent(Request $request){
		//Create a new Comm here
		try {
			$id = $request->get('id');;

			//Agent::where('id', $agent_id)->delete();
			CommSchemeMgmtAgent::where('id', $id)->delete();

			$response['success'] = true;
			$response['message'] = 'Commission earner deleted successfully';

		} catch (\Exception $e) {
			$response['success'] = false;
			$response['message'] = $e->getMessage();
		}
		return $response;
	}


	public function updateAgentName(Request $request){
		//Create a new Comm here
		try {
			$agent_id = $request->get('id');
			$agent_name = $request->get('name');

			CommAgent::where('id', $agent_id)->
				update((['name' => $agent_name]));

			$response['success'] = true;
			$response['message'] = 'Updated successfully';

		} catch (\Exception $e) {
			$response['success'] = false;
			$response['message'] = $e->getMessage();
		}
		return $response;
	}



	public function getScheme(Request $request){
		//Fetch all schemes
		$data = CommScheme::where('name', '!=', 'Select')
		       ->groupBy('name')
		       ->get();
		return $data;
	}

	public function getSchemeDefinition(Request $request){

		$id = $request->get('id');
		//Fetch all schemes
		$data = CommSchemeLevel::where('comm_scheme_id', '=', $id)
		       ->first();
		return $data;
	}
}
