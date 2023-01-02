<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Matrix\Exception;
use Yajra\DataTables\DataTables;
use \App\Classes\SystemID;
use \App\Classes\UserData;
use \App\Models\membership;
use \App\Models\merchantproduct;
use \App\Models\product;
use \App\Models\productbarcode;
use \App\Models\opos_receiptproduct;
use Milon\Barcode\DNS1D;

use App\Models\usersrole;
use App\Models\role;
use Illuminate\Support\Facades\Auth;
class MembershipController extends Controller
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
        $model = new membership();

        $ids = merchantproduct::where('merchant_id',
			$this->user_data->company_id())->
			pluck('product_id');

        $ids = product::where('ptype', 'membership')->
			whereIn('id', $ids)->pluck('id');


        $data = $model->whereIn('product_id', $ids)->
			orderBy('created_at', 'asc')->
			latest()->get();

        return Datatables::of($data)
            ->addIndexColumn()
            ->addColumn('membership_pro_id', function ($memberList) {
                return '<p class="os-linkcolor" data-field="membership_pro_id" style="cursor: pointer; margin: 0; text-align: center;"><a  href="/landing/membership_products/'.$memberList->product_name->systemid.'" target="_blank" style="text-decoration: none;">' . $memberList->product_name->systemid . '</a></p>';
            })
            ->addColumn('membership_pro_name', function ($memberList) {
                if (!empty($memberList->product_name->thumbnail_1)) {
                    $img_src = '/images/product/' . $memberList->product_name->id . '/thumb/' . $memberList->product_name->thumbnail_1;
                    $img     = "<img src='$img_src' data-field='membership_pro_name' style=' width: auto;height: 30px;display: inline-block;margin-right: 16px'/>";
                } else {
                    $img = null;
                }
                return $img . '<p class="os-linkcolor" data-field="membership_pro_name" style="cursor: pointer; margin: 0;display:inline-block">' . (!empty($memberList->product_name->name) ? $memberList->product_name->name : 'Product Name') . '</p>';
            })
            ->addColumn('membership_buy', function ($memberList) {

                return '<p class="os-linkcolor buyOutput" data-field="membership_buy" style="cursor: pointer; margin: 0; text-align: right;">'.(!empty($memberList->buy) ? number_format(($memberList->buy/100),2): '0.00').'</p>';

            })
            ->addColumn('membership_get', function ($memberList) {

                return '<p class="os-linkcolor getOutput" data-field="membership_get" style="cursor: pointer; margin: 0; text-align: right;">'.(!empty($memberList->get) ? number_format(($memberList->get/100),2): '0.00').'</p>';

            })
            ->addColumn('deleted', function ($memberList) {
                $sale_id = opos_receiptproduct::where('product_id', $memberList->product_name->id)->first();
                if (!$sale_id) {
					return '<div data-field="deleted" class="remove">
						<img class="" src="/images/redcrab_50x50.png"
						style="width:25px;height:25px;cursor:pointer"/>
						</div>';

                } else {
					return '<div><img class="" src="/images/redcrab_50x50.png"
						style="width:25px;height:25px;cursor:not-allowed;
						filter:grayscale(100%) brightness(200%)"/>
						</div>';
                }
            })
            ->escapeColumns([])
            ->make(true);
    }


    public function edit($id)
    {
        //
    }


    public function store(Request $request)
    {
        //Create a new product here
        try {
            $this->user_data = new UserData();
            $merchantproduct = new merchantproduct();
            $SystemID        = new SystemID('product');
            $product         = new product();

            $product->systemid = $SystemID;
            $product->ptype    = 'membership';
            $product->save();

            $membership             = new membership();
            $membership->product_id = $product->id;
            $membership->save();

            $merchantproduct->product_id  = $product->id;
            $merchantproduct->merchant_id = $this->user_data->company_id();
            $merchantproduct->save();

            $msg = "Product added successfully";
            return view('layouts.dialog', compact('msg'));

        } catch (\Exception $e) {
            $msg = "Some error occured";
            return view('layouts.dialog', compact('msg'));
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

                $membership = membership::where('id', $id)->first();
                return view('membership.membership-modals', compact(['id', 'fieldName', 'membership']));
            }

        } catch (\Illuminate\Database\QueryException $ex) {
            $response = (new ApiMessageController())->queryexception($ex);
        }
    }


	public function update(Request $request)
    {
        try {

            $allInputs = $request->all();
            $membership_id       = $request->get('membership_id');
            $changed = false;

            $validation = Validator::make($allInputs, [
                'membership_id'         => 'required',
            ]);

            if ($validation->fails()) {
                throw new Exception("product_not_found", 1);
            }

             $membership = membership::find($membership_id);

             if (!$membership) {
                throw new Exception("product_not_found", 1);
            }

            if ($request->has('buy')) {

                if ($membership->buy != (int) str_replace('.','',number_format($request->buy,2)))
                {
                    $membership->buy = (int) str_replace('.','',number_format($request->buy,2));
                    $changed = true;
                    $msg = "Price updated";
                }
            }
            if ($request->has('get')) {

                if ($membership->get != (int) str_replace('.','',number_format($request->get,2)))
                {
                    $membership->get = (int) str_replace('.','',number_format($request->get,2));
                    $changed = true;
                    $msg = "Price updated";
                }
            }

            if ($changed == true) {
                $membership->save();
                 $response = view('layouts.dialog', compact('msg'));
            } else {
                $response  = null;
            }

        }  catch (\Exception $e) {
            if ($e->getMessage() == 'product_not_found') {
                $msg = "Product not found";
            } else if ($e->getMessage() == 'invalid_cost') {
                $msg = "Invalid cost";
            } else {
                $msg = "Some error occured";
            }

            // $msg = $e;
            $response = view('layouts.dialog', compact('msg'));
        }

        return $response;
    }


    public function destroy($id)
    {
        try {
            $this->user_data = new UserData();
            $membership      = membership::find($id);
            $product_id      = $membership->product_id;

            $is_exist = merchantproduct::where([
				'product_id' => $product_id,
				'merchant_id' => $this->user_data->company_id()
			])->first();

            if (!$is_exist) {
                throw new Exception("Error Processing Request", 1);
            }

            $is_exist->delete();
            product::find($product_id)->delete();
            $membership->delete();

            $msg = "Product deleted successfully";
            return view('layouts.dialog', compact('msg'));

        } catch (\Illuminate\Database\QueryException $ex) {
            $msg = "Some error occured";

            return view('layouts.dialog', compact('msg'));
        }
    }


    public function showMembershipView()
    {
        return view('membership.membership');
    }


    public function showMembershipProducts($id){
        $system_id = $id;
        $product = product::where('systemid', $system_id)->first();
        $product_id = $product->id;
        // $product_qty = $this->check_quantity($product->id);
        // $barcodematrix = SettingBarcodeMatrix::where('category_id', $product->prdcategory_id)->first();
        
        $barcode_sku = productbarcode::where('product_id',$product->id)->first();

        $barcode = DNS1D::getBarcodePNG(trim($system_id), "C128");
        $id = Auth::user()->id;
        $user_roles = usersrole::where('user_id',$id)->get();

        $is_king =  \App\Models\Company::where('owner_user_id',Auth::user()->id)->first();

        if ($is_king != null) {
            $is_king = true;
        } else {
            $is_king  = false;
        }

        return view('membership.membership_products',compact('user_roles','is_king','barcode_sku','barcode','system_id','product','product_id'));
    }
}
