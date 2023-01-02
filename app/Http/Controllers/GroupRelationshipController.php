<?php

namespace App\Http\Controllers;
use \App\Classes\UserData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\usersrole;
// use App\Models\groupinfo;
// use App\Models\groupholding;
use App\Models\grouplink;
use App\Models\Company;
use Yajra\DataTables\DataTables;

class GroupRelationshipController extends Controller
{
    //
	public function groupIndex(){
	
		$user_data = new UserData();
	
		$company = DB::table('company')->
			where('id', $user_data->company_id())->
			first();

        if (!empty($company)){
			$merchant = DB::table('merchant')->
				where('company_id', $company->id)->
				first();
			$merchant_id = $merchant->id;
			$company_id  = $company->id;
			$user_id 	 = $company->owner_user_id;
		} 

		/*
		else {
			$staff = DB::table('staff')
			->join('merchant','staff.company_id','=','merchant.id')
			->where('staff.user_id',$user_id)
			->select('merchant.id','merchant.company_id')
			->get();
			$merchant_id = $staff[0]->id;
        	$company = $company[0];
		}*/

        $group_info = DB::table('groupinfo')->where('owner_user_id', $user_id)->first();
		
		$group_holding = DB::table('groupholding')->
			join('company','groupholding.shareholder_owner_user_id','=','company.owner_user_id')->
			where('groupholding.owner_user_id', $user_id)->
			get();

        $level_arr = [];
		
		// echo '<pre>';

        if(!empty($group_holding)):
            foreach($group_holding as $key =>  $v){
                $level_arr[$key]['name'] = $v->name;
                $get_next_level = DB::table('groupholding')
                ->join('company','groupholding.shareholder_owner_user_id','=','company.owner_user_id')
                ->where('groupholding.owner_user_id', $v->shareholder_owner_user_id)->get();
                 $level_arr[$key]['next'] = $get_next_level;
                }
                // print_r($level_arr);
        endif;

		return view('group_relationship.group_relationship', 
			compact('company','merchant_id','group_info','group_holding','level_arr'));
    }


    public function showGroupRelationshipData(){

		$user_data = new UserData();
		
		$company = DB::table('company')->find($user_data->company_id());
        $user_id = $company->owner_user_id;
		
		$group_relationship = DB::table('grouplink')->
			join('company', 'grouplink.responder_user_id','=','company.owner_user_id')->
			where('initiator_user_id', $user_id)->
			get();
		
		$data = [];
        $data[0]['merchant_id'] = $company->systemid;
        $data[0]['business_reg_no'] = $company->business_reg_no;
        $data[0]['name'] = $company->name;
        $data[0]['user_id'] = $company->owner_user_id;
        $data[0]['inbound_remote_login'] = '';
        $data[0]['outbound_remote_login'] = '';
        $data[0]['auth_user_id'] = $user_id;
		
		$key_count = 0;
		
		foreach ($group_relationship as $key => $value) {
            $key_count = $key_count + 1;
            $data[$key_count]['merchant_id'] = $value->systemid;
            $data[$key_count]['business_reg_no'] = $value->business_reg_no;
            $data[$key_count]['name'] = $value->name;
            $data[$key_count]['user_id'] = $value->owner_user_id;
            $data[$key_count]['inbound_remote_login'] = $value->inbound_remote_login ?? null;
            $data[$key_count]['outbound_remote_login'] = $value->outbound_remote_login ?? null;
            $data[$key_count]['auth_user_id'] = $user_id;
        }

        return DataTables::of($data)
			->addIndexColumn()

			->addColumn('merchant_id',function($data){
                if($data['outbound_remote_login'] == 1){
					return '<p data-field="retail_cust_id" style="margin:0;cursor: pointer;"  
						class="text-center text-primary">'.$data['merchant_id'].'</p>';
                }else{
                    return '<p data-field="retail_cust_id" style="margin:0;"  class="text-center">'.$data['merchant_id'].'</p>';
                }
			   
			})
			->addColumn('business_reg_no',function($data){
			    return '<p data-field="retail_cust_name" style="margin:0;" class="text-center">'.$data['business_reg_no'].'</p>';
			})
			->addColumn('name',function($data){
			    return '<p data-field="retail_status" style="margin:0;">'.ucfirst($data['name']).'</p>';
			})
			->addColumn('holding',function($data){
				return '<p data-company="" style="margin:0;cursor: pointer;" 
					onclick="showHolding('.$data['auth_user_id'].','.$data['user_id'].')"
					 class="text-center text-primary">Holding</p>';
			})
 			->addColumn('yellow_crab',function($data){

				return '<div data-target="#groupHierarchy" data-toggle="modal"
					class="text-center">
					<img src="/images/yellowcrab_50x50.png"
						style="width:25px;height:25px;cursor:pointer"/>
					</div>';
			})
  			->addColumn('blue_crab',function($data){
                if ($data['user_id'] == $data['auth_user_id']) {
                    $pointer = 'display: none;';
                    $disable = 'disabled';
                } else {
					$pointer = 'pointer-events: auto';
					$disable = '';
                }

				return '<div data-toggle="modal" style="'.$pointer.'"
					class="text-center">
					<img src="/images/bluecrab_50x50.png"
						onclick="showCrossCompany('.
						$data['user_id'].','.
						$data['inbound_remote_login'].','.
						$data['outbound_remote_login'].')"
						style="width:25px;height:25px;cursor:pointer"/>
					</div>';
            })
            ->addColumn('red_crab',function($data){
                 if ($data['user_id'] == $data['auth_user_id']) {
                    $pointer = 'display: none;';
                    $disable = 'disabled';
                } else {
					$pointer = 'pointer-events: auto';
					$disable = '';
                }

                return '<div data-target="#confirmDelete'.$data['user_id'].'"
					data-toggle="modal" style="'.$pointer.';"
					class="remove text-center">
					<img class="" src="/images/redcrab_50x50.png"
					style="width:25px;height:25px;cursor:pointer"/>
					</div>

					<div class="modal fade" id="confirmDelete'.$data['user_id'].
						'" tabindex="-1" role="dialog" aria-hidden="true">
					<div class="modal-dialog modal-dialog-centered mw-75 w-50" role="document">
						<div class="modal-content modal-inside bg-greenlobster">
							<div style="border-width:0" class="modal-header text-center"></div>
							<div class="modal-body text-center">
								<h5 class="modal-title text-white"
								id="statusModalLabel">
								Do you want to permanently delete this merchant?</h5>
							</div>
							<div class="modal-footer"
								style="border-top:0 none; padding-left: 0px; padding-right: 0px;">
								<div class="row" style="width: 100%; padding-left: 0px; padding-right: 0px;">
								<div class="col col-m-12 text-center">
								<button type="button" class="btn btn-primary primary-button" onclick="del_company('.
									$data['user_id'].')" data-dismiss="modal">Yes</button>
								<button type="button" class="btn btn-danger primary-button" data-dismiss="modal">No</button>
								</div>
								</div>
							</div>
						</div>
					</div>
					</div>
									
					';
			})
    		
		->escapeColumns([])
		->make(true);
    }


    public function showGroupHolding(Request $request){

		$user_data = new UserData();
	
		$company = DB::table('company')->
			where('id', $user_data->company_id())->
			first();

		$user_id = $company->owner_user_id;

		$company_user_id_to_exclude = $request->get('user_id');

        //$company = DB::table('company')->where('owner_user_id',$user_id)->get();

        $group_relationship = DB::table('grouplink')->
			join('company', 'grouplink.responder_user_id','=','company.owner_user_id')->
			where('initiator_user_id', $user_id)->
			where('company.owner_user_id', '!=', $company_user_id_to_exclude)->
			get();

        $data = [];
        if( $company->owner_user_id != $company_user_id_to_exclude){
            $data[0]['merchant_id'] = $company->systemid;
            $data[0]['business_reg_no'] = $company->business_reg_no;
            $data[0]['name'] = $company->name;
            $data[0]['user_id'] = $company->owner_user_id;
            $data[0]['auth_user_id'] = $user_id;
            $data[0]['to_exclude'] = $company_user_id_to_exclude;
        }
        if( $company->owner_user_id != $company_user_id_to_exclude){
            $key_count = 0;
        }else{
            $key_count = -1;
        }
        
        
        foreach ($group_relationship as $key => $value) {
            $key_count = $key_count + 1;
            $data[$key_count]['merchant_id'] = $value->systemid;
            $data[$key_count]['business_reg_no'] = $value->business_reg_no;
            $data[$key_count]['name'] = $value->name;
            $data[$key_count]['user_id'] = $value->owner_user_id;
            $data[$key_count]['auth_user_id'] = $user_id;
            $data[$key_count]['to_exclude'] = $company_user_id_to_exclude;
        }

        return DataTables::of($data)
			->addIndexColumn()
			->addColumn('company_name',function($data){
                return '<p data-field="company_name" style="margin:0;vertical-align:middle;"  >'.ucfirst($data['name']).'</p>';
			})
			->addColumn('psa',function($data){
                 $get_holding = DB::table('groupholding')
                        ->where('owner_user_id', $data['auth_user_id'])
                        ->where('shareholder_owner_user_id', $data['user_id'])
                        ->first();
                if(!empty($get_holding)){
                    $psa = ucfirst(str_replace('_',' ',$get_holding->psa));
                    if(strlen($get_holding->psa) === 0){
                    $psa = 'Select';
                }
                }
                if(empty($get_holding) === true){
                    $psa = 'Select';
                }
                
			    return '<p id="psa_field_text'.$data['user_id'].'" data-field="psa" style="margin:0;cursor:pointer;vertical-align:middle;" onclick="show_psa_popup('.$data['auth_user_id'].','.$data['user_id'].')" class="text-center text-primary">'.$psa.'</p>'; 
			})
			->addColumn('per',function($data){
                $get_holding = DB::table('groupholding')
                        ->where('owner_user_id', $data['auth_user_id'])
                        ->where('shareholder_owner_user_id', $data['user_id'])
                        ->first();
                if(!empty($get_holding)){
                    $per = $get_holding->percent_shareholding;
                }else{
                    $per = '0.00';
                }
               
                    return '<input data-company-id="'.$data['user_id'].'" type="text" id="holdingPercentInput'.$data['user_id'].'" style="width:100%" class="form-control text-center" value="'.number_format($per, 1).'"/>
		<input type="hidden" id="buffer_main_price" value="0.0">';
                            
			    
			})
		
			->escapeColumns([])
			->make(true);
 
    }

    public function updateHoldingPSA(Request $request){
        $link_id = 0;
        $owner_id = Auth::user()->id;
        $company_id = $request->get('company_id');
        $psa = $request->get('psa');
        $group_holding =  DB::table('groupholding')
        ->where('owner_user_id', $owner_id)
        ->where('shareholder_owner_user_id', $company_id)
        ->first();
        if(!empty($group_holding) || $group_holding != ''){
            $update = DB::table('groupholding')
                    ->where('owner_user_id', $owner_id)
                    ->where('shareholder_owner_user_id', $company_id)
                    ->update([
                        'psa' => $psa,
                       'updated_at' => date('Y-m-d H:i:s')
                    ]);
        }else{
            $update = DB::table('groupholding')
                    ->insert([
                        'grouplink_id' => $link_id,
                        'owner_user_id' => $owner_id,
                        'shareholder_owner_user_id' => $company_id,
                        'psa' => $psa,
                        'percent_shareholding' => '0.0',
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
        }
             if($update){
                  return response()->json([
					'msg' => 'Updated',
                    'status' => 'true'
                    ]
                );
             }else{
                 return response()->json([
					'msg' => 'Failed',
                    'status' => 'false'
                    ]
                 );
             }
            // return json_encode($group_holding);
        }

        public function updateHoldingPercentage(Request $request){
			$user_data = new UserData();
		
			$company = DB::table('company')->
				where('id', $user_data->company_id())->
				first();
			
			$owner_id = $company->owner_user_id;

            $percentage = $request->get('percentage');
		
			$link_id = 0;
            foreach($percentage as $per_value){
				
				$check_holding =  DB::table('groupholding')->
					where('owner_user_id', $owner_id)->
					where('shareholder_owner_user_id', $per_value['id'])->
					first();

                if(!empty($check_holding) || $check_holding != ''){
                     $update = DB::table('groupholding')
                    ->where('owner_user_id', $owner_id)
                    ->where('shareholder_owner_user_id', $per_value['id'])
                    ->update([
                        'percent_shareholding' => $per_value['per'],
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                }else{
                    $update = DB::table('groupholding')->
						insert([
                        	'grouplink_id' => $link_id,
                        	'owner_user_id' => $owner_id,
                        	'psa' => 'no_relationship',
                     	    'shareholder_owner_user_id' => $per_value['id'],
                        	'percent_shareholding' => $per_value['per'],
                  		    'created_at' => date('Y-m-d H:i:s'),
                 		    'updated_at' => date('Y-m-d H:i:s')
                    ]);
                }
            }
             if($update){
                  return response()->json([
					'msg' => 'Updated',
                    'status' => 'true'
                    ]
                );
             }else{
                 return response()->json([
					'msg' => 'Failed',
                    'status' => 'false'
                    ]
                 );
             }
        }


     public function getHoldingData(Request $request){
		 
		 $user_data = new UserData();
		 $company = DB::table('company')->
			 where('id', $user_data->company_id())->
			 first();
		 $owner_id = $company->owner_user_id;

        $company_id = $request->get('company_id');
        $group_holding = DB::table('groupholding')
        ->where('owner_user_id', $owner_id)
        ->where('shareholder_owner_user_id', $company_id)
        ->first();
        if(!empty($group_holding) || $group_holding != ''){
                return response()->json([
					'data' => $group_holding,
                    'status' => 'true'
                    ]
                );
        }else{
             return response()->json([
					'msg' => 'No entry',
                    'status' => 'false'
                    ]
                );
            // return json_encode($group_holding);
        }

    }

    public function GroupsaveMerchantTwoWayLinking(Request $request){
		$user_data = new UserData();
	
		$company = DB::table('company')->
			where('id', $user_data->company_id())->
			first();


        $initiatorUserId = $company->owner_user_id;//Auth::user()->id;
        // $initiatorUserId = $this->getCompanyUserId();
        $responder = $request->input('merchant_id');
        $responderCompany = DB::table('company')->where('systemid', $responder)->first();
        if ($responderCompany !== null) {
            $responderUserId = $responderCompany->owner_user_id;
            if ($initiatorUserId == $responderUserId) {
                return response()->json([
					'msg' => 'A merchant cannot add himself',
					'status' => 'false']);
            }

            $checkGroupLink = DB::table('grouplink')->
				where('responder_user_id', $responderUserId)->
				where('initiator_user_id', $initiatorUserId)->
                first();
			

            if (!empty($checkGroupLink) &&
				$checkGroupLink->initiator_user_id == $initiatorUserId &&
				$checkGroupLink->responder_user_id == $responderUserId) {
                return response()->json([
					'msg' => 'Merchant ID already added',
					'status' => 'false'
				]);

            } else {
                //add group holding
                DB::table('grouplink')->insert([
                    'initiator_user_id' => $initiatorUserId,
                    'responder_user_id' => $responderUserId,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
                return response()->json([
					'msg' => 'Merchant ID added successfully',
					'status' => 'true'
				]);
            }

        } else {
            return response()->json([
				'msg' => 'Merchant ID not found',
				'status' => 'false'
			]);
        }
    }

    public function getCompanyHoldingDetails(Request $request){
        $company_user_id = $request->get('user_id');
        $company_user_id = DB::table('company')->where('owner_user_id', $company_user_id)->first();
        echo $company_user_id->name;
    }

    public function getHoldingList(Request $request){
        $owner_user_id = $request->get('owner_user_id');
        $shareholder_owner_user_id = $request->get('shareholder_owner_user_id');
        $get_holding = DB::table('groupholding')
                        ->where('owner_user_id', $owner_user_id)
                        ->where('shareholder_owner_user_id', $shareholder_owner_user_id)
                        ->get();
        $data = $get_holding;
        DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('merchant_id',function($data){
			    return '<p data-field="retail_cust_id" style="margin:0;"  class="text-center">'.$data['merchant_id'].'</p>';
            })
             ->addColumn('merchant_id',function($data){
			    return '<p data-field="retail_cust_id" style="margin:0;"  class="text-center">'.$data['merchant_id'].'</p>';
            })
            ->escapeColumns([])
			->make(true);
    }

    public function delGroup(Request $request){
        $user_id = Auth::user()->id;
        $company_id = $request->get('company_id');
        $link = DB::table('grouplink')->
                 where('initiator_user_id', $user_id)->
                 where('responder_user_id', $company_id)->
                 delete();
        $holding = DB::table('groupholding')
                    ->where('owner_user_id', $user_id)
                    ->where('shareholder_owner_user_id', $company_id)
                    ->delete();
        echo $company_id;
    }

    public function updateName(Request $request){
        $user_id = Auth::user()->id;
        $name = $request->get('group_name');
        $check_groupinfo_exist = DB::table('groupinfo')->where('owner_user_id', $user_id)->first();
        if(empty($check_groupinfo_exist)){
            $update = DB::table('groupinfo')->insert([
                'groupname' => $name,
                'owner_user_id' => $user_id,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        }else{
            $update = DB::table('groupinfo')->where('owner_user_id', $user_id)->update([
                'groupname' => $name,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        }
        if($update){
            return response()->json([
                'msg' => 'Group name updated',
                'status' => 'true'
                ]);
        }else{
            return response()->json([
                'msg' => 'Failed to update',
                'status' => 'false'
                ]);
        }
    }

    public function updateIcon(Request $request){
         if ($request->hasfile('file')) {
            $file = $request->file('file');
            $extension = $file->getClientOriginalExtension(); // getting image extension
            $user_id = Auth::user()->id;
           
            if (!in_array($extension, array(
                'jpg', 'JPG', 'png', 'PNG', 'jpeg', 'JPEG', 'gif', 'GIF', 'bmp', 'BMP', 'tiff', 'TIFF'))) {
                return abort(403);
            }

            $filename = ('group_icon' . sprintf("%010d", $user_id)) . '-m' . sprintf("%010d", $user_id) . rand(1000, 9999) . '.' . $extension;

            if (!file_exists(public_path()."/images/company/$user_id")) {
                echo 'no.. create';
                $path = public_path()."/images/company/$user_id";
                mkdir($path, 0775, true);
            }
            $file->move(public_path() . ("/images/company/$user_id/"), $filename);

            $check_groupinfo_exist = DB::table('groupinfo')->where('owner_user_id', $user_id)->first();
            if(empty($check_groupinfo_exist)){
            $update = DB::table('groupinfo')->insert([
                'groupicon' => $filename,
                'owner_user_id' => $user_id,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            }else{
                $update = DB::table('groupinfo')->where('owner_user_id', $user_id)->update([
                    'groupicon' => $filename,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            }
            $return_arr = array("name" => $filename, "size" => 000, "src" => "images/company/$user_id/$filename");
            return response()->json($return_arr);
            } else {
                return abort(403);
            }
    }

    public function InboundRemoteLogin(Request $request){
        $owner_id = Auth::user()->id;
        $cross_company_id = $request->get('cross_comapny_id');
        $active = $request->get('active');
        if($active == 'true'){
            $update = DB::table('grouplink')
                ->where('initiator_user_id', $owner_id)
                ->where('responder_user_id', $cross_company_id)
                ->update([
                    'inbound_remote_login' => FALSE,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
        }else{
            $update = DB::table('grouplink')
                ->where('initiator_user_id', $owner_id)
                ->where('responder_user_id', $cross_company_id)
                ->update([
                    'inbound_remote_login' => TRUE,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
        }
        if($update){
            return response()->json([
                'msg' => 'Active',
                'status' => 'true'
                ]);
        }
         
    }

     public function delPicture(Request $request)
    {
        $owner_id = Auth::user()->id;
        $delete = DB::table('groupinfo')->where('owner_user_id', $owner_id)->update([
            'groupicon' => ''
        ]);

        // try {
        //     $validation = Validator::make($request->all(), [
        //         'systemid' => 'required',
        //     ]);

        //     if ($validation->fails()) {
        //         throw new \Exception("validation_error", 19);
        //     }

        //     $product_details = product::where('systemid', $request->systemid)->first();

        //     if (!$product_details) {
        //         throw new \Exception('product_not_found', 25);
        //     }

        //     $product_details->photo_1 = null;
        //     $product_details->thumbnail_1 = null;
        //     $product_details->save();
        //     $return = response()->json(array("deleted" => "True"));

        // } catch (\Exception $e) {
            $return = response()->json(array("deleted" => "True"));
        // }

        return $return;

    }

    //  public function check_location($location)
    // {
    //     $location = array_filter(explode('/', $location));
    //     $path = public_path();

    //     foreach ($location as $key) {
    //         $path .= "/$key";

	// 		Log::debug('check_location(): $path='.$path);

    //         if (is_dir($path) != true) {
    //             mkdir($path, 0775, true);
    //         }
    //     }
    // }


}
