<?php

namespace App\Http\Controllers;

use App\User;
use App\Models\_function;
use App\Models\usersfunction;
use App\Models\CMRApproval;
use App\Models\CMRFormServices;
use \App\Models\CMRManagement;
use \App\Models\CMRForm;
use App\Classes\SystemID;
use App\Classes\UserData;
use App\Models\CMRPartsUsed;
use App\Models\CMRTravelTo;
use App\Models\Company;
use App\Models\merchantproduct;
use App\Models\prd_inventory;
use App\Models\product;
use App\Models\terminal;
use App\Models\usersrole;
use http\Exception\InvalidArgumentException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Matrix\Exception;
use Yajra\DataTables\DataTables;
use PDF;



class CMRController extends Controller
{
    protected $user_data;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('CheckRole:prod');
    }

    public function index()
	{
		$this->user_data = new UserData();
		$cmr  = CMRManagement::with(([
			'cmrform.parts' => function ($query) {
				$query->where('cmr_chargeable', 1);
			}, 'cmrform.parts.inventory:id,price'
		]))->
		where('merchant_id', $this->user_data->company_id())->
		orderBy('created_at', 'desc')->
		get()->
		toArray();

		$amount = 0;
		$data =  array_map(function($value) {
           
			if ($value['cmrform']['parts']){
				$sum =  array_map(function($val) {
					// dd($val);
					if($val['inventory']){
						return $val['inventory']['price'] * $val['cmr_qty'] * $val['cmr_chargeable'];
					}
				},  $value['cmrform']['parts']);

				/*
				return [
					'id'=>$value['id'],
					'systemid'=>$value['systemid'],
					'name'=>$value['name'],
					'mechanic'=>$value['mechanic'],
					'status'=>$value['status'],
					'deleted_at'=>$value['deleted_at'],
					'created_at'=>$value['created_at'],
					'updated_at'=>$value['updated_at'],
					'merchant_id'=>$value['merchant_id'],
					'amount'=>number_format(array_sum($sum))
				];
				*/
				
				$amount = 0;
				for($ii =0; $ii < sizeof($sum); $ii++){
					$amount += $sum[$ii];
				}
				//dd($amount);
                $amount = number_format(($amount/ 100), 2);
				
                
			}else{
                $amount = 0;
            }

			return [
				'id'=>$value['id'],
				'systemid'=>$value['systemid'],
				'name'=>$value['name'],
				'technician_user_id'=>$value['technician_user_id'],
				'status'=>$value['status'],
				'deleted_at'=>$value['deleted_at'],
				'created_at'=>$value['created_at'],
				'updated_at'=>$value['updated_at'],
				'merchant_id'=>$value['merchant_id'],
				'amount'=>$amount
			];
		},  $cmr);

		// return $data;
		// Log::debug('CMRs'.$data);

		return Datatables::of($data)
			->addIndexColumn()
			->addColumn('cmr_id', function ($cmrList) {
				return '<p class="os-linkcolor" data-field="cmr_id" style="cursor: pointer; margin: auto; text-align: center;"><a class="os-linkcolor" href="/corrective-maintenance-reports/'.$cmrList['id'].'" target="_blank" style="text-decoration: none;">' . $cmrList['systemid'] . '</p>';
			})
			->addColumn('cmr_name', function ($cmrList) {
				return  '<p class="os-linkcolor nameOutput" data-field="cmr_name" style="cursor: pointer; margin: auto;display:inline-block">' . ucfirst((!empty($cmrList['name']) ? $cmrList['name'] : 'Corrective Maintenance Report') ). '</p>';
			})
			->addColumn('cmr_mechanic', function ($cmrList) {
				$technician = User::where("id", $cmrList['technician_user_id'])->first();

				return '<p class="os-linkcolor mechanicOutput" data-field="cmr_mechanic" style="cursor: pointer; margin: auto;text-align: left;" >'.ucfirst((!empty($technician) ? $technician->name : 'Technician Name')).'</p>';
			})

			->addColumn('cmr_date', function ($cmrList) {

				return '<p data-field="cmr_date" disabled="disabled" style="margin: auto;" >'.\Carbon\Carbon::parse($cmrList['created_at'])->format('dMy').'</p>';

			})
			->addColumn('cmr_status', function ($cmrList) {

				return '<p   data-field="cmr_status" style=" margin: auto;">'.ucfirst($cmrList['status']).'</p>';

			})
			->addColumn('cmr_amount', function ($cmrList) {

					return '<p data-field="cmr_amount" style="margin: auto; text-align: right">'.((!empty($cmrList['amount']) && $cmrList['amount'] != 0)  ? $cmrList['amount']: "0.00" ) .'</p>';

			})
			->addColumn('deleted', function ($cmrList) {
				return '<p data-field="deleted"
					style="background-color:red;
					border-radius:5px;margin:auto;
					width:25px;height:25px;
					display:block;cursor: pointer;"
					class="text-danger remove">
					<img src="/images/redcrab_25x25.png"></p>';

			})
			->escapeColumns([])
			->make(true);
	}


    public function store(Request $request)
    {
        //Create a new CMR
        try {
            $this->user_data = new UserData();
            $SystemID        = new SystemID('cmr');
            $cmr         = new CMRManagement();

            // Save CMR
            $cmr->merchant_id   = $this->user_data->company_id();
            $cmr->systemid = $SystemID;
            $cmr->save();

            // Create CMR Form
            CMRForm::create(["cmrmgmt_id" => $cmr->id]);


            $msg = "Corrective Maintenance Report added successfully";
            return response()->json([
				'status' 	=> 'success',
				'message' 	=> $msg,
			]);

        } catch (\Exception $e) {
            $msg = $e->getMessage();
            Log::error(
                "Error @ " . $e->getLine() . " file " . $e->getFile() .
                ":" . $msg
            );

            return response()->json([
				'status' 	=> 'success',
				'message' 	=> $msg,
			]);
        }
    }


    public function showEditModal(Request $request)
    {
        try {
            $allInputs = $request->all();
            $id        = $request->get('id');
            $fieldName = $request->get('field_name');

            $validation = Validator::make($allInputs, [
                'id'         => 'required',
                'field_name' => 'required',
            ]);

            if ($validation->fails()) {
                $response = (new ApiMessageController())->
                validatemessage($validation->errors()->first());

            } else {

                $cmr = CMRManagement::find($id);

                return view('cmr.cmr-modals', compact(['id', 'fieldName', 'cmr']));
            }

        } catch (\Illuminate\Database\QueryException $ex) {

            $msg = "Some error occured";
            return response()->json([
								'status' 	=> 'success',
								'message' 	=> $msg,
							]);
        }
    }

    public function update(Request $request)
    {
       try {

            $cmr_id       = $request->get('id');
            $changed = false;
            $msg = '';

             $cmr     = CMRManagement::find($cmr_id);
             log::debug('CMR'.json_encode($cmr));

            if (empty($cmr)) {
                throw new Exception("cmr_not_found", 1);
            }

            if ($request->has('name')) {
				if($request->name != $cmr->name){
					$cmr->name = $request->name;
					$changed = true;
					$msg = "Name updated successfully";
				}
            }

            if ($request->has('technician_user_id')) {   
				
				if($request->technician_user_id != $cmr->technician_user_id){
					$cmr->technician_user_id = $request->technician_user_id;
					$changed = true;
					$msg = "Technician updated successfully";
				}                  
            }

            if ($changed == true) {
                $cmr->save();
                log::debug('Saved_CMR'.json_encode($cmr));
				$response =  response()->json([
					'status' 	=> 'success',
					'message' 	=> $msg,
				]);

            } else {
            	if(!empty($msg)) {
					$response = response()->json([
						'status' 	=> 'success',
						'message' 	=> $msg,
					]);

            	} else {
					$response = response()->json([
						'status' 	=> 'nothing',
						'message' 	=> $msg,
					]);
            	}
            }

        } catch (\Exception $e) {
            if ($e->getMessage() == 'cmr_not_found') {
                $msg = "Corrective Maintenance Report not found";
            }  else {
                $msg = "Some error occured";
            }

            // $msg = $e;
            $response = response()->json([
				'status' 	=> 'success',
				'message' 	=> $msg,
			]);

            Log::error(
                "Error @ " . $e->getLine() . " file " . $e->getFile() .
                ":" . $e->getMessage()
            );
        }

        return $response;
    }


    public function destroy($id)
    {
        try {
            CMRManagement::destroy($id);
            $msg = "Corrective Maintenance Report deleted successfully";

            return response()->json([
				'status' 	=> 'success',
				'message' 	=> $msg,
			]);

        } catch (\Illuminate\Database\QueryException $ex) {
            $msg = "Some error occured, Could not delete Corrective Maintenance Report";

            return response()->json([
				'status' 	=> 'success',
				'message' 	=> $msg,
			]);
        }
    }


    public function showCMR()
    {
        return view('cmr.cmr');
    }


    public function showForm($cmr_id)
    {

        try{
			$id = Auth::user()->id;
			$user_data = new UserData();
			$user_data->exit_merchant();

			$user_roles = usersrole::where('user_id',$id)->get();

			$is_king =  Company::where('owner_user_id',
				Auth::user()->id)->first();

			if ($is_king != null) {
				$is_king = true;

			} else {
				$is_king  = false;
			}

			if (!$user_data->company_id()) {
				abort(404);
			}

            $cmrForm =  CMRForm::where("cmrmgmt_id", $cmr_id)->
				first();

            if (!$cmrForm){
                return "<h1>CMR ID not found in CMR Form, Please create new CMR and try again </h1>";
            }


            // All approvals
            $allAprovals = CMRApproval::with('user.staff')->
				where("cmrform_id",$cmrForm->id)->
				get()->
				toArray();

			// Approvals by current logged in users
            $userApprovalDetails = array_filter($allAprovals, function($value) use ($id) {
                return $value['approver_user_id'] == $id;
             });
             $userApprovals = collect( array_map(function($value) {
                return ['approval_name'=>$value['approval_name'],'approver_user_id'=>$value['approver_user_id'] ];
            },  $userApprovalDetails));

             $customer_service = array_search('1customer_service',array_column($allAprovals,'approval_name'));
             $store = array_search('2store',array_column($allAprovals,'approval_name'));
             $maintenance_dept3 = array_search('3maintenance_dept',array_column($allAprovals,'approval_name'));
             $customer = array_search('4customer',array_column($allAprovals,'approval_name'));
             $maintenance_dept5 = array_search('5maintenance_dept',array_column($allAprovals,'approval_name'));
             $finance_dept = array_search('6finance_dept',array_column($allAprovals,'approval_name'));


            $travels = $cmrForm->travels;
            $services = collect( array_map(function($value) {
                return $value['service'];
            },  $cmrForm->services->toArray()));

			return view('cmr.cmr_form', compact(
				'cmr_id', 'cmrForm', 'travels', 'services',
				'user_roles','is_king','allAprovals',
				'userApprovals','customer_service', 'store',
				'maintenance_dept3','customer','maintenance_dept5',
				'finance_dept'));

        } catch (\Exception $e) {
            Log::error($e);
            $msg = "Some error occured";
            return response()->json([
				'status' 	=> 'success',
				'message' 	=> $msg,
			]);
        }
    }


    public function saveForm(Request $request)
    {
        //Create a new CMR
        try {
            $this->user_data = new UserData();
            global $cmrForm;
            $cmrForm =  CMRForm::where("cmrmgmt_id",$request->cmr_id)->first();
            $data = $request->input();
            $data['stations'] = json_decode($request->stations);

            if (!$cmrForm){
				$msg = "Corrective Maintenance Form not found or has been deleted";
				return response()->json([
					'status' 	=> 'success',
					'message' 	=> $msg,
				]);
            }


            $services = array_map(function($value) {
                return [ "cmrform_id"=>$GLOBALS['cmrForm']['id'], "service"=>$value ];
            },  $data['stations']);

            $travelsData = array_map(function($value) {
                return [ "cmrform_id"=>$GLOBALS["cmrForm"]["id"], "travelled_to"=>$value ];
            },  $data["travel_to"]);

			// Extract the travel whch are not empty
			$travels = array_filter($travelsData, function($value) {
				return !empty($value['travelled_to']);
			});

			// Extract the needed data for CMR form
			$cmrFormData = array_filter($data, function($k) {
                return !($k == 'id' || $k == '_token' ||  $k == 'cmr_id' ||  $k == 'stations' ||  $k == 'travel_to');
			}, ARRAY_FILTER_USE_KEY);

			$changed = false;
			$current_form = CMRForm::where("cmrmgmt_id",$request->cmr_id)->first();
		//	dd($current_form);
			 if(
			 $current_form->job_no != $cmrFormData['job_no'] || $current_form->region != $cmrFormData['region'] || 
			 $current_form->location_address != $cmrFormData['location_address'] || 
			 $current_form->complaint != $cmrFormData['complaint'] || $current_form->complained_by != $cmrFormData['complained_by'] || 
			 $current_form->equipment_serialno != $cmrFormData['equipment_serialno'] || $current_form->equipment_modelno != $cmrFormData['equipment_modelno'] ||  
			 $current_form->start_time != $cmrFormData['start_time'] || $current_form->sitein_time != $cmrFormData['sitein_time'] ||  
			 $current_form->siteout_time != $cmrFormData['siteout_time'] || $current_form->return_time != $cmrFormData['return_time'] ||  
			 $current_form->start_mileage != $cmrFormData['start_mileage'] || $current_form->return_mileage != $cmrFormData['return_mileage'] ||  
			 $current_form->total_mileage != $cmrFormData['total_mileage'] || $current_form->return_time != $cmrFormData['return_time'] ||  
			 $current_form->siteout_time != $cmrFormData['siteout_time'] || $current_form->travelled_from != $cmrFormData['travelled_from'] 
			 ){
				 $changed = true;
			 }
		//	 dd($changed);
             CMRForm::where("cmrmgmt_id",$request->cmr_id)
                 ->update($cmrFormData);

             $oldServicesIds = CMRFormServices::where("cmrform_id",$cmrForm->id)
                 ->pluck("id");
             // Create station services
		//	 dd($services);
                $cmrForm->services()->createMany($services);
                // Delete old services
                CMRFormServices::destroy($oldServicesIds);


             $oldTravelsIds = CMRTravelTo::where("cmrform_id",$GLOBALS['cmrForm']['id'])
                 ->pluck("id");

             // Create station services
                $cmrForm->travels()->createMany($travels);
            //   Delete old services
			CMRTravelTo::destroy($oldTravelsIds);


			if($changed){
				$msg =  "Corrective Maintenance Form saved successfully";
				return response()->json([
					'status' 	=> 'success',
					'message' 	=> $msg,
				]);

			} else {
				$msg =  "";
				return response()->json([
					'status' 	=> 'nochange',
					'message' 	=> $msg,
				]);
			}

		} catch (\Exception $e) {
			Log::error(
                "Error @ " . $e->getLine() . " file " . $e->getFile() .
                ":" . $e->getMessage()
            );

            $msg = "Some error occured";
            return response()->json([
				'status' 	=> 'success',
				'message' 	=> $msg,
			]);
        }
    }


    public function printForm(Request $request,$cmr_id)
    {
        //Create a new CMR
        try {
            $id = Auth::user()->id;
            $user_data = new UserData();
            $user_data->exit_merchant();

            $user_roles = usersrole::where('user_id',$id)->get();

            $is_king =  Company::where('owner_user_id',
                Auth::user()->id)->first();

//            if ($is_king != null) {
//                $is_king = true;
//
//            } else {
//                $is_king  = false;
//            }

            if (!$user_data->company_id()) {
                abort(404);
            }

            $cmrForm =  CMRForm::where("cmrmgmt_id", $cmr_id)->
            first();

            if (!$cmrForm){
                return "<h1>CMR ID not found in CMR Form, Please create new CMR and try again </h1>";
            }


            // All approvals
            $allAprovals = CMRApproval::with('user.staff')->
            where("cmrform_id",$cmrForm->id)->
            get()->
            toArray();

            // Approvals by current logged in users
            $userApprovalDetails = array_filter($allAprovals, function($value) use ($id) {
                return $value['approver_user_id'] == $id;
            });
            $userApprovals = collect( array_map(function($value) {
                return ['approval_name'=>$value['approval_name'],'approver_user_id'=>$value['approver_user_id'] ];
            },  $userApprovalDetails));

            $customer_service = array_search('1customer_service',array_column($allAprovals,'approval_name'));
            $store = array_search('2store',array_column($allAprovals,'approval_name'));
            $maintenance_dept3 = array_search('3maintenance_dept',array_column($allAprovals,'approval_name'));
            $customer = array_search('4customer',array_column($allAprovals,'approval_name'));
            $maintenance_dept5 = array_search('5maintenance_dept',array_column($allAprovals,'approval_name'));
            $finance_dept = array_search('6finance_dept',array_column($allAprovals,'approval_name'));


            $travels = $cmrForm->travels;
            $services = collect( array_map(function($value) {
                return $value['service'];
            },  $cmrForm->services->toArray()));

             $partsUsed = CMRPartsUsed::with('inventory.product_name')
                ->where('cmrform_id', $cmrForm->id)
                ->get();

//return $services;
            $pdf = PDF::setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true])->loadView('cmr.cmr_pdf', compact(
                'cmr_id', 'cmrForm', 'travels', 'services',
                'user_roles','is_king','allAprovals',
                'userApprovals','customer_service', 'store',
                'maintenance_dept3','customer','maintenance_dept5',
                'finance_dept','partsUsed'));

            $pdf->getDomPDF()->setBasePath(public_path().'/');
            $pdf->getDomPDF()->setHttpContext(
                stream_context_create([
                    'ssl' => [
                        'allow_self_signed'=> true,
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                    ]
                ])
            );
            $pdf->setPaper('A4', '');
            //return $pdf->stream();
            return $pdf->download('cmr_form.pdf');

            //return view('cmr.cmr_pdf', compact(['cmr', 'cmrForm']));

		} catch (\Exception $e) {
			Log::error(
                "Error @ " . $e->getLine() . " file " . $e->getFile() .
                ":" . $e->getMessage()
            );
            dd($e);

            return redirect()->back();
        }
    }


    public function openProductsModal(Request $request)
    {
        $cmrform_id = $request->cmrform_id;
        return view('cmr.products_modal', compact('cmrform_id'));
    }


    public function getInventoryProducts(Request $request)
    {
        // Get inventory products
        $this->user_data = new UserData();

        $ids = merchantproduct::where('merchant_id',
			$this->user_data->company_id())->
			pluck('product_id');

		// Inventory price can either be 0 or NULL
		$inventoryIds = prd_inventory::whereIn('product_id',$ids)->
			whereNotNull('price')->
			pluck('product_id');

		$data = product::whereIn('id', $inventoryIds)->
			where('ptype', 'inventory')->
			whereNotNull('name')->
			whereNotNull('photo_1')->
			whereNotNull('prdcategory_id')->
			whereNotNull('prdsubcategory_id')->
			whereNotNull('prdprdcategory_id')->
			latest()->
			get();

		return Datatables::of($data)
            ->addIndexColumn()
            ->addColumn('product_id', function ($memberList) {
                $product_id = $memberList->systemid;
                return '<p data-field="product_id" style="margin: 0;">' . ucfirst($product_id) . '</p>';
            })
            ->addColumn('product_name', function ($memberList) {
                if (!empty($memberList->thumbnail_1)) {
                    $img_src = '/images/product/' . $memberList->id . '/thumb/' . $memberList->thumbnail_1;
                    $img     = "<img src='$img_src' data-field='product_name' style=' width: 25px;height: 25px;display: inline-block;margin-right: 8px;object-fit:contain;'>";
                } else {
                    $img = null;
                }
                return $img . '<p class="os- linkcolor"  onclick="addProductPart('. $memberList->id .','. $memberList->inventory->id .')" data-field="restaurantnservices_pro_name" style="cursor: pointer; margin: 0;display: inline-block;color:#007bff;">' . (!empty($memberList->name) ? $memberList->name : 'Product Name') . '</p>';
            })
            ->addColumn('price', function ($memberList) {
                 return '<p data-field="price" style="margin: 0;" class="text-right">' . number_format($memberList->inventory->price,2) . '</p>';
            })
            ->escapeColumns([])
            ->make(true);
    }


    public function getPartsUsed( $cmrform_id)
    {
        try {
			// Get inventory products
			$partsUsed = CMRPartsUsed::with('inventory.product_name')
				->where('cmrform_id', $cmrform_id)
				->get();

			return Datatables::of($partsUsed)
				->addIndexColumn()
				->addColumn('product_id', function ($memberList) {
					$product_id = $memberList->inventory->product_name->systemid;
					return '<p data-field="product_id" style="margin: 0;">' . ucfirst($product_id) . '</p>';
				})
				->addColumn('product_name', function ($memberList) {
					if (!empty($memberList->inventory->product_name->thumbnail_1)) {
						$img_src = '/images/product/' . $memberList->inventory->product_name->id . '/thumb/' . $memberList->inventory->product_name->thumbnail_1;
						$img     = "<img src='$img_src' data-field='product_name' style=' width: 25px;height: 25px;display: inline-block;margin-right: 8px;object-fit:contain;'>";
					} else {
						$img = null;
					}
					return $img . '<p  data-field="restaurantnservices_pro_name" style="  margin: 0;display: inline-block;">' . (!empty($memberList->inventory->product_name->name) ? $memberList->inventory->product_name->name : 'Product Name') . '</p>';
				})
				->addColumn('part_no', function ($memberList) {
					log::debug('esto es memberlist'.json_encode($memberList));
					return '<p data-field="part_no" id="part_no" class=" m-0 text-left partclass">' .(!empty($memberList->cmr_partno) ? $memberList->cmr_partno : "Add Part No.") . '</p>';
				})
				->addColumn('qty', function ($memberList) {
					return '<p data-field="qty" id="qty" class="text-center m-0  qtyclass">'.(!empty($memberList->cmr_qty) ? $memberList->cmr_qty : 0).' </p>';
				})

				->addColumn('price', function ($memberList) {
					return '<p data-field="price" class="text-right m-0 ">' .  number_format(($memberList->inventory->price / 100), 2) . '</p>';
				})

				->addColumn('chargeable', function ($memberList) {
					return '<p data-field="chargeable" id="chargeable" onclick="updateChargeable('.$memberList->id.','.(($memberList->cmr_chargeable == 0) ? 1 : 0 ) .')" class="  m-0   chargeable chargeclass" chargeable="'.$memberList->cmr_chargeable.'">' .(($memberList->cmr_chargeable == 0) ? "Not Chargeable" : "Chargeable") . '</p>';
				})
				->addColumn('amount', function ($memberList) {
					if($memberList->cmr_chargeable == 0){
						return '<p data-field="amount" class="text-right m-0 ">0.00</p>';
					} else {
						return '<p data-field="amount" class="text-right m-0 ">' . number_format((($memberList->cmr_qty*$memberList->inventory->price )/100),2) .'</p>';
					}
				})
				->addColumn('deleted', function ($memberList) {
					return '<p  
						data-field="deleted"
						style="
						border-radius:5px;margin:auto;
						width:25px;height:25px;
						display:block;"
						class="text-danger text-center remove removeClass">
						<img src="/images/redcrab_25x25.png"></p>';
					})
				->escapeColumns([])
				->make(true);

        } catch (\Exception $e) {
            return response()->json([
				'status' 	=> 'error',
				'message' 	=> $e->getMessage(),
			]);
        }
    }


    public function AddPartUsed(Request $request)
    {
        try {

            $cmrform = CMRForm::find($request->cmrform_id);

            if (!$cmrform){
                throw new InvalidArgumentException("Form Id does not exist");
            }

            $cmrparts = new CMRPartsUsed();

            $cmrparts->cmrform_id   = $request->cmrform_id;
            $cmrparts->inventory_id = $request->inventory_id;
            $cmrparts->save();

            $msg = "Product added successfully";
            return response()->json([
				'status' 	=> 'success',
				'message' 	=> $msg,
			]);

        } catch (\Exception $e) {
            $msg = "Product could not added. An error occurred";
            return response()->json([
				'status' 	=> 'success',
				'message' 	=> $msg,
			]);
        }
    }


    public function showPartsEditModal(Request $request)
    {
        try {
            $allInputs = $request->all();
            $id        = $request->get('id');
            $fieldName = $request->get('field_name');

            $validation = Validator::make($allInputs, [
                'id'         => 'required',
                'field_name' => 'required',
            ]);

            if ($validation->fails()) {
                $response = (new ApiMessageController())->
                validatemessage($validation->errors()->first());

            } else {
                $cmrPart = CMRPartsUsed::find($id);

                return view('cmr.cmr_parts_edit_modals',
					compact(['id', 'fieldName', 'cmrPart']));
            }

        } catch (\Illuminate\Database\QueryException $ex) {
            $response = (new ApiMessageController())->queryexception($ex);
        }
    }


    public function updatePartsUsed(Request $request)
    {
       try {

            $cmrpart_id       = $request->id;
            $changed = false;
            $msg = '';

             $cmrpart     = CMRPartsUsed::find($cmrpart_id);

            if (empty($cmrpart)) {
                throw new Exception("CMR product part not found");
            }

            if ($request->has('part_no')) {
				$cmrpart->cmr_partno = $request->part_no;
				$changed = true;
				$msg = "Part Number updated successfully";
            }

            if ($request->has('cmr_qty')) {
				$cmrpart->cmr_qty = $request->cmr_qty;
				$changed = true;
				$msg = "Quantity updated successfully";
            }

			if ($request->has('chargeable')) {
				$cmrpart->cmr_chargeable = $request->chargeable;
				$changed = true;
				$msg = "Chargeable status updated successfully";
            }

            if ($changed == true) {
                $cmrpart->save();
                log::debug('Saved_CMR'.json_encode($cmrpart));
				$response = response()->json([
					'status' 	=> 'success',
					'message' 	=> $msg,
				]);

            } else {
            	if(!empty($msg)) {
					$response = response()->json([
						'status' 	=> 'success',
						'message' 	=> $msg,
					]);

            	} else {
					$response= '';
            	}
            }

        }  catch (\Exception $e) {
            if ($e->getMessage() == 'cmr_not_found') {
                $msg = "CMR not found";
            }  else {
                $msg = "Some error occured";
            }

            // $msg = $e;
            $response = response()->json([
				'status' 	=> 'success',
				'message' 	=> $msg,
			]);

            Log::error(
                "Error @ " . $e->getLine() . " file " . $e->getFile() .
                ":" . $e->getMessage()
            );
        }

        return $response;
    }


    public function destroyPartUsed($id)
    {
        try {

            CMRPartsUsed::destroy($id);
            $msg = "Part removed successfully";

            return response()->json([
				'status' 	=> 'success',
				'message' 	=> $msg,
			]);

        } catch (\Illuminate\Database\QueryException $ex) {
            $msg = "An error occured, Could not remove Part";

            return response()->json([
				'status' 	=> 'success',
				'message' 	=> $msg,
			]);
        }
    }


	public function saveApprovals(Request $request)
    {
        try {
            $userId = Auth::user()->id;

            $cmrForm =  CMRForm::find($request->cmrform_id);
            $approvalData =  $request->input('userApprovals');

            if (!$cmrForm){
				$msg = "Corrective Maintenance Form not found";
				return response()->json([
					'status' 	=> 'success',
					'message' 	=> $msg,
				]);
            }

			$oldUserApprovals = CMRApproval::
				where("cmrform_id",$cmrForm->id)->
				where("approver_user_id", $userId)->
				pluck("id");


			// Create station services
			if($approvalData){
				$cmrForm->approvals()->createMany($approvalData);
			}

            // Delete old services
            CMRApproval::destroy($oldUserApprovals);

			$msg = "Approval saved successfully";
			return response()->json([
				'status' 	=> 'success',
				'message' 	=> $msg,
			]);

        } catch (\Exception $e) {
            return $e;
            $msg = "Some error occured, could not save approvals";
            return response()->json([
				'status' 	=> 'success',
				'message' 	=> $msg,
			]);
        }
    }
}
