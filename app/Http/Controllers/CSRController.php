<?php

namespace App\Http\Controllers;

use Log;
use App\Models\CSRApproval;
use \App\Models\CSRManagement;
use \App\Models\CSRForm;
use App\Classes\SystemID;
use App\Classes\UserData;
use App\Models\CSRPartsUsed;
use App\Models\CSRTravelTo;
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
//use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Matrix\Exception;
use Yajra\DataTables\DataTables;
use PDF;


class CSRController extends Controller
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
		$csr  = CSRManagement::with(([
			'csrform.parts' => function ($query) {
				$query->where('csr_chargeable', 1);
			}, 'csrform.parts.inventory:id,price'
			]))->
			where('merchant_id', $this->user_data->company_id())->
			orderBy('created_at', 'desc')->
			get()->
			toArray();

		$amount = 0;
		$data =  array_map(function($value) {

			Log::debug('CSR: value='. json_encode($value));
           
			if ($value['csrform']['parts']){
				$sum =  array_map(function($val) {
					// dd($val);
					if($val['inventory']){
						return $val['inventory']['price'] *
							$val['csr_qty'] * $val['csr_chargeable'];
					}
				},  $value['csrform']['parts']);

				/*
				return [
					'id'=>$value['id'],
					'systemid'=>$value['systemid'],
					'name'=>$value['name'],
					'technician'=>$value['technician'],
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
				'technician'=>$value['technician'],
				'status'=>$value['status'],
				'deleted_at'=>$value['deleted_at'],
				'created_at'=>$value['created_at'],
				'updated_at'=>$value['updated_at'],
				'merchant_id'=>$value['merchant_id'],
				'amount'=>$amount
			];
		},  $csr);

		// return $data;
		// Log::debug('CSRs'.$data);

		return Datatables::of($data)
			->addIndexColumn()
			->addColumn('csr_id', function ($csrList) {
				return '<p class="os-linkcolor" data-field="csr_id" style="cursor: pointer; margin: 0; text-align: center;"><a class="os-linkcolor" href="/crane-service-reports/'.$csrList['id'].'" target="_blank" style="text-decoration: none;">' . $csrList['systemid'] . '</p>';
			})
			->addColumn('csr_name', function ($csrList) {
				return  '<p class="os-linkcolor nameOutput" data-field="csr_name" style="cursor: pointer; margin: 0;display:inline-block">' . ucfirst((!empty($csrList['name']) ? $csrList['name'] : 'Crane Service Report') ). '</p>';
			})
			->addColumn('csr_technician', function ($csrList) {
				$csr = \App\User::find($csrList['technician'])->name ??  'Technician Name';
				return '<p class="os-linkcolor technicianOutput" data-field="csr_technician" style="cursor: pointer; margin: 0;text-align: left;" >'.ucfirst($csr).'</p>';
			})

			->addColumn('csr_date', function ($csrList) {

				return '<p data-field="csr_date" disabled="disabled" style="margin: 0;" >'.date("dMy",strtotime($csrList['created_at'])).'</p>';

			})
			->addColumn('csr_status', function ($csrList) {

				return '<p   data-field="csr_status" style=" margin: 0;">'.ucfirst($csrList['status']).'</p>';

			})
			->addColumn('csr_amount', function ($csrList) {

					return '<p data-field="csr_amount" style="margin: 0; text-align: right">'.((!empty($csrList['amount']) && $csrList['amount'] != 0)  ? $csrList['amount']: "0.00" ) .'</p>';

			})
			->addColumn('deleted', function ($csrList) {
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
        //Create a new CSR
        try {
            $this->user_data = new UserData();
            $SystemID        = new SystemID('csr');
            $csr         = new CSRManagement();

            // Save CSR
            $csr->merchant_id   = $this->user_data->company_id();
            $csr->systemid = $SystemID;
            $csr->save();

            // Create CSR Form
            CSRForm::create(["csrmgmt_id" => $csr->id]);

            $msg = "Crane Service Report added successfully";
            return response()->json([
				'status' 	=> 'success',
				'message' 	=> $msg,
			]);

        } catch (\Exception $e) {
			$emsg = "Error @ " . $e->getLine() . " file " . $e->getFile() .
				":" . $e->getMessage();
			Log::error($emsg);

            $msg = "Some error occured";
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

                $csr = CSRManagement::find($id);

                return view('csr.csr-modals', compact(['id', 'fieldName', 'csr']));
            }

        } catch (\Illuminate\Database\QueryException $ex) {
			$emsg = "Error @ " . $e->getLine() . " file " . $e->getFile() .
				":" . $e->getMessage();
			Log::error($emsg);

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

            $csr_id       = $request->get('id');
            $changed = false;
            $msg = '';

             $csr     = CSRManagement::find($csr_id);
             log::debug('CSR'.json_encode($csr));

            if (empty($csr)) {
                throw new Exception("csr_not_found", 1);
            }

            if ($request->has('name')) {
				if($request->name != $csr->name){
					$csr->name = $request->name;
					$changed = true;
					$msg = "Name updated successfully";
				}
            }

            if ($request->has('technician_user_id')) {   
				
				if($request->technician_user_id != $csr->technician){
					$csr->technician = $request->technician_user_id;
					$changed = true;
					$msg = "Technician updated successfully";
				}                  
            }

            if ($changed == true) {
                $csr->save();
                log::debug('Saved_CSR'.json_encode($csr));
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

        }  catch (\Exception $e) {
            if ($e->getMessage() == 'csr_not_found') {
                $msg = "Crane Service Report not found";
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
            CSRManagement::destroy($id);
            $msg = "Crane Service Report deleted successfully";

            return response()->json([
				'status' 	=> 'success',
				'message' 	=> $msg,
			]);

        } catch (\Illuminate\Database\QueryException $ex) {
			$emsg = "Error @ " . $e->getLine() . " file " . $e->getFile() .
				":" . $e->getMessage();
			Log::error($emsg);

            $msg = "Some error occured, could not delete Crane Service Report ";
            return response()->json([
				'status' 	=> 'success',
				'message' 	=> $msg,
			]);
        }
    }


    public function showCSR()
    {
        return view('csr.csr');
    }


    public function showForm($csr_id)
    {
        try{
			$id = Auth::user()->id;
			$user_data = new UserData();
			$user_data->exit_merchant();

			$user_roles = usersrole::where('user_id',$id)->get();

			$is_king =  Company::where('owner_user_id',Auth::user()->id)->first();

			if ($is_king != null) {
				$is_king = true;
			} else {
				$is_king  = false;
			}

			if (!$user_data->company_id()) {
				abort(404);
			}

            $csr     = CSRManagement::find($csr_id);
            $csrForm =  CSRForm::where("csrmgmt_id",$csr_id)->first();

            if (!$csrForm){
                return "<h1>CSR ID not found in CSR Form, Please create new CSR and try again </h1>";
            }

            // All approvals
            $allAprovals = CSRApproval::with('user.staff')
            ->where("csrform_id",$csrForm->id)
            ->get()
            ->toArray();

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



            $travels = $csrForm->travels;
            $services = collect( array_map(function($value) {
                return $value['service'];
            },  $csrForm->services ? $csrForm->services->toArray() : []));

        return view('csr.csr_form', compact('csr',
			'csr_id', 'csrForm', 'travels', 'services',
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
        //Create a new CSR
        try {
            $this->user_data = new UserData();
            global $csrForm;
            $csrForm =  CSRForm::where("csrmgmt_id",$request->csr_id)->first();
            $data = $request->input();
            $data['stations'] = json_decode($request->stations);

            if (!$csrForm){
                   $msg = "Crane Service Report Form not found or has been deleted";
                    return response()->json([
								'status' 	=> 'success',
								'message' 	=> $msg,
							]);
            }


            $services = array_map(function($value) {
                return [ "csrform_id"=>$GLOBALS['csrForm']['id'], "service"=>$value ];
            },  $data['stations']);

            $travelsData = array_map(function($value) {
                return [ "csrform_id"=>$GLOBALS["csrForm"]["id"], "travelled_to"=>$value ];
            },  $data["travel_to"]);

             // Extract the travel whch are not empty
             $travels = array_filter($travelsData, function($value) {
                return !empty($value['travelled_to']);
             });

            // Extract the needed data for CSR form
             $csrFormData = array_filter($data, function($k) {
                return !($k == 'id' || $k == '_token' ||  $k == 'csr_id' ||  $k == 'stations' ||  $k == 'travel_to');
             }, ARRAY_FILTER_USE_KEY);

			 $changed = false;
             $current_form = CSRForm::where("csrmgmt_id",$request->csr_id)->first();
		//	dd($current_form);
			 if(
			 $current_form->region != $csrFormData['region'] || 
			 $current_form->site != $csrFormData['site'] || 
			 $current_form->complaint != $csrFormData['complaint'] ||  
			 $current_form->equipment_serialno != $csrFormData['equipment_serialno'] || $current_form->equipment_modelno != $csrFormData['equipment_modelno'] ||  
			 $current_form->start_time != $csrFormData['start_time'] || $current_form->sitein_time != $csrFormData['sitein_time'] ||  
			 $current_form->siteout_time != $csrFormData['siteout_time'] || $current_form->return_time != $csrFormData['return_time'] ||  
			 $current_form->start_mileage != $csrFormData['start_mileage'] || $current_form->return_mileage != $csrFormData['return_mileage'] ||  
			 $current_form->total_mileage != $csrFormData['total_mileage'] || $current_form->return_time != $csrFormData['return_time'] ||  
			 $current_form->siteout_time != $csrFormData['siteout_time'] || $current_form->travelled_from != $csrFormData['travelled_from'] 
              || $current_form->customer != $csrFormData['customer'] 
              || $current_form->installation_dismantling_base != $csrFormData['installation_dismantling_base'] 
              || $current_form->installation_dismantling_mast != $csrFormData['installation_dismantling_mast'] 
              || $current_form->installation_dismantling_collar != $csrFormData['installation_dismantling_collar'] 
              || $current_form->collar_installation_no != $csrFormData['collar_installation_no'] 
              || $current_form->wall_slab_no != $csrFormData['wall_slab_no'] 
              || $current_form->i_beam_length != $csrFormData['i_beam_length'] 
              || $current_form->quantity != $csrFormData['quantity'] 
              || $current_form->jacking_extension_mast != $csrFormData['jacking_extension_mast'] 
              || $current_form->ph_tie_quantity != $csrFormData['ph_tie_quantity'] 
              || $current_form->internal_jacking_no != $csrFormData['internal_jacking_no'] 
              || $current_form->equipment_tc_ph_no != $csrFormData['equipment_tc_ph_no'] 
			 ){
				 $changed = true;
			 }
		//	 dd($changed);
             CSRForm::where("csrmgmt_id",$request->csr_id)
                 ->update($csrFormData);


             $oldTravelsIds = CSRTravelTo::where("csrform_id",$GLOBALS['csrForm']['id'])
                 ->pluck("id");

             // Create station services
                $csrForm->travels()->createMany($travels);
            //   Delete old services
            CSRTravelTo::destroy($oldTravelsIds);


		  if($changed){
			  $msg =  "Crane Service Report Form saved successfully";
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

    public function openProductsModal(Request $request)
    {
        $csrform_id = $request->csrform_id;
        return view('csr.products_modal', compact('csrform_id'));
    }

    public function getInventoryProducts(Request $request)
    {

        // Get inventory products

        $this->user_data = new UserData();

        $ids = merchantproduct::where('merchant_id',
  			$this->user_data->company_id())->
  			pluck('product_id');


             // Inventory price can either be 0 or NULL
             $inventoryIds = prd_inventory::whereIn('product_id',$ids)
                // ->where('price', '!=', 0)
                 ->whereNotNull('price')
                 ->pluck('product_id');

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

    public function getPartsUsed( $csrform_id)
    {
        try {

        // Get inventory products
        $partsUsed = CSRPartsUsed::with('inventory.product_name')
            ->where('csrform_id', $csrform_id)
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
                return '<p data-field="part_no" id="part_no" class=" m-0 text-left partclass">' .(!empty($memberList->csr_partno) ? $memberList->csr_partno : "Add Part No.") . '</p>';
            })
            ->addColumn('qty', function ($memberList) {
                return '<p data-field="qty" id="qty" class="text-center m-0  qtyclass">'.(!empty($memberList->csr_qty) ? $memberList->csr_qty : 0).' </p>';
            })

            ->addColumn('price', function ($memberList) {
                return '<p data-field="price" class="text-right m-0 ">' .  number_format(($memberList->inventory->price / 100), 2) . '</p>';
            })

            ->addColumn('chargeable', function ($memberList) {
                return '<p data-field="chargeable" id="chargeable" onclick="updateChargeable('.$memberList->id.','.(($memberList->csr_chargeable == 0) ? 1 : 0 ) .')" class="  m-0   chargeable chargeclass" chargeable="'.$memberList->csr_chargeable.'">' .(($memberList->csr_chargeable == 0) ? "Not Chargeable" : "Chargeable") . '</p>';
            })
            ->addColumn('amount', function ($memberList) {
				if($memberList->csr_chargeable == 0){
					return '<p data-field="amount" class="text-right m-0 ">0.00</p>';
				} else {
					return '<p data-field="amount" class="text-right m-0 ">' . number_format((($memberList->csr_qty*$memberList->inventory->price )/100),2) .'</p>';
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
                            <i class="fas fa-times text-white"
                            style="color:white;opacity:1.0;
                            padding:4px;
                            -webkit-text-stroke: 1px #ddd;"></i></p>';

                })
            ->escapeColumns([])
            ->make(true);

        } catch (\Exception $e) {
//            return $e;
            return "Some error occured";
            return response()->json([
								'status' 	=> 'success',
								'message' 	=> $msg,
							]);
        }
    }

    public function AddPartUsed(Request $request)
    {
        try {

            $csrform = CSRForm::find($request->csrform_id);

            if (!$csrform){
                throw new InvalidArgumentException("Form Id does not exist");
            }

            $csrparts = new CSRPartsUsed();

            $csrparts->csrform_id   = $request->csrform_id;
            $csrparts->inventory_id = $request->inventory_id;
            $csrparts->save();

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
                $csrPart = CSRPartsUsed::find($id);
                return view('csr.csr_parts_edit_modals',
					compact(['id', 'fieldName', 'csrPart']));
            }

        } catch (\Illuminate\Database\QueryException $ex) {
            $response = (new ApiMessageController())->queryexception($ex);
        }
    }

    public function updatePartsUsed(Request $request)
    {
       try {

            $csrpart_id       = $request->id;
            $changed = false;
            $msg = '';

             $csrpart     = CSRPartsUsed::find($csrpart_id);

            if (empty($csrpart)) {
                throw new Exception("CSR product part not found");
            }

            if ($request->has('part_no')) {
				$csrpart->csr_partno = $request->part_no;
				$changed = true;
				$msg = "Part Number updated successfully";
            }

            if ($request->has('csr_qty')) {
				$csrpart->csr_qty = $request->csr_qty;
				$changed = true;
				$msg = "Quantity updated successfully";
            }

			if ($request->has('chargeable')) {
				$csrpart->csr_chargeable = $request->chargeable;
				$changed = true;
				$msg = "Chargeable status updated successfully";
            }

            if ($changed == true) {
                $csrpart->save();
                log::debug('Saved_CSR'.json_encode($csrpart));
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
            if ($e->getMessage() == 'csr_not_found') {
                $msg = "Crane Service Report not found";
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
            CSRPartsUsed::destroy($id);
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

            $csrForm =  CSRForm::find($request->csrform_id);
            $approvalData =  $request->input('userApprovals');

            if (!$csrForm){
				$msg = "Crane Service Report not found";
				return response()->json([
					'status' 	=> 'success',
					'message' 	=> $msg,
				]);
            }

			$oldUserApprovals = CSRApproval::
				where("csrform_id",$csrForm->id)->
				where("approver_user_id", $userId)->
				pluck("id");

			// Create station services
            if($approvalData){
                $csrForm->approvals()->createMany($approvalData);
            }

            // Delete old services
            CSRApproval::destroy($oldUserApprovals);

			$msg = "Approval saved successfully";
			return response()->json([
				'status' 	=> 'success',
				'message' 	=> $msg,
			]);

        } catch (\Exception $e) {
			Log::error(
                "Error @ " . $e->getLine() . " file " . $e->getFile() .
                ":" . $e->getMessage()
            );

            $msg = "Some error occured, could not save approvals";
            return response()->json([
				'status' 	=> 'success',
				'message' 	=> $msg,
			]);
        }
    }
    public function downloadPdf($csr_id)
    {
        try{
            $id = Auth::user()->id;
            $user_data = new UserData();
            $user_data->exit_merchant();

            $user_roles = usersrole::where('user_id',$id)->get();

            $is_king =  Company::where('owner_user_id',Auth::user()->id)->first();

            if ($is_king != null) {
                $is_king = true;
            } else {
                $is_king  = false;
            }

            if (!$user_data->company_id()) {
                abort(404);
            }

            $csr     = CSRManagement::find($csr_id);
            $csrForm =  CSRForm::where("csrmgmt_id",$csr_id)->first();

            if (!$csrForm){
                return "<h1>CSR ID not found in CSR Form, Please create new CSR and try again </h1>";
            }

            // All approvals
            $allAprovals = CSRApproval::with('user.staff')
                ->where("csrform_id",$csrForm->id)
                ->get()
                ->toArray();

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



            $travels = $csrForm->travels;
            $services = collect( array_map(function($value) {
                return $value['service'];
            },  $csrForm->services ? $csrForm->services->toArray() : []));

            $partsUsed = CSRPartsUsed::with('inventory.product_name')
                ->where('csrform_id', $csrForm->id)
                ->get();
            $is_king =  Company::where('owner_user_id',
                Auth::user()->id)->first();
            $pdf = PDF::setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true])
                ->loadView('csr.csr_pdf', compact('csr',
                    'csr_id', 'csrForm', 'travels', 'services',
                    'user_roles','is_king','allAprovals',
                    'userApprovals','customer_service', 'store',
                    'maintenance_dept3','customer','maintenance_dept5',
                    'finance_dept','partsUsed'));

            $pdf->getDomPDF()->setBasePath(public_path() . '/');
            $pdf->getDomPDF()->setHttpContext(
                stream_context_create([
                    'ssl' => [
                        'allow_self_signed' => true,
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                    ]
                ])
            );
            $pdf->setPaper('A4', 'portrait');
            //return $pdf->stream();
            return $pdf->download('csr.pdf');
        } catch (\Exception $e) {
            Log::error($e);
            $msg = "Some error occured";
            return response()->json([
                'status' 	=> 'success',
                'message' 	=> $msg,
            ]);
        }
    }

}

