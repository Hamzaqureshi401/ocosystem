<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Matrix\Exception;
use Yajra\DataTables\DataTables;
use \App\Classes\SystemID;
use \App\Classes\UserData;
use \App\Models\merchantproduct;
use \App\Models\product;
use \App\Models\opos_receiptproduct;
use \App\Models\StockReport;
use \App\Models\stockreportremarks;
use \App\Models\opos_refund;
use \App\Models\opos_wastageproduct;
use \App\Models\SettingBarcodeMatrix;
use \App\Models\productbarcode;
use \App\Models\warranty;
use \Illuminate\Support\Facades\Auth;
use \App\Models\usersrole;
use \App\Models\role;
use Milon\Barcode\DNS1D;
class WarrantyController extends Controller
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
        $model = new warranty();

        $ids  = merchantproduct::where('merchant_id',
			$this->user_data->company_id())->
			pluck('product_id');

        $ids  = product::where('ptype', 'warranty')->
			whereIn('id', $ids)->pluck('id');

        $data = $model->whereIn('product_id', $ids)->
			orderBy('created_at', 'desc')->
			latest()->get();

        return Datatables::of($data)
            ->addIndexColumn()
            ->addColumn('warranty_pro_id', function ($warrantyList) {
                return '<p class="os-linkcolor" data-field="warranty_pro_id" style="cursor: pointer; margin: 0; text-align: center;"><a class="os-linkcolor" href="/landing/show-warrantyproduct-view/'. $warrantyList->product_name->systemid .'" target="_blank" style="text-decoration: none;">' . $warrantyList->product_name->systemid . '</a></p>';
            })
            ->addColumn('warranty_pro_name', function ($warrantyList) {
                if (!empty($warrantyList->product_name->thumbnail_1)) {
                    $img_src = '/images/product/' . $warrantyList->product_name->id . '/thumb/' . $warrantyList->product_name->thumbnail_1;
                    $img     = "<img src='$img_src' data-field='warranty_pro_name' style=' width: 25px;height: 25px;display: inline-block;margin-right: 8px;object-fit:contain;'/>";
                } else {
                    $img = null;
                }
                return $img . '<p class="os-linkcolor" data-field="warranty_pro_name" style="cursor: pointer; margin: 0;display:inline-block;">' . (!empty($warrantyList->product_name->name) ? $warrantyList->product_name->name : 'Product Name') . '</p>';
            })
            ->addColumn('warranty_price', function ($warrantyList) {

                return '<p class="os-linkcolor priceOutput" data-field="warranty_price" style="cursor: pointer; margin: 0; text-align: right;">'.(!empty($warrantyList->price) ? number_format(($warrantyList->price/100),2): '0.00').'</p>';

            })
            ->addColumn('warranty_extend_cover', function ($warrantyList) {

                return '<p class="os-linkcolor extendOutput" data-field="warranty_extend_cover" style="cursor: pointer; margin: 0; text-align: center;" data-target="#warrantyExtendModal" data-toggle="modal">3 months</p>';

            })
            ->addColumn('deleted', function ($warrantyList) {
				return '<div data-field="deleted" class="remove">
					<img src="/images/redcrab_50x50.png"
					style="width:25px;height:25px;cursor:pointer"/>
					</div>';
            })
            ->escapeColumns([])
            ->make(true);
    }

    public function getWarrantyProduct()
    {
        return Datatables::of(array(''))
            ->addIndexColumn()
            ->addColumn('warrantymodal_pro_name', function ($warrantyList) {
                return '<p class="os-linkcolor" data-field="warrantymodal_pro_name" style="cursor: pointer; margin: 0;  text-align: center;">Roman Helmet</p><img src="../images/barcode.jpg" width="150px" alt="Logo">';
            })
            ->addColumn('warrantymodal_qrcode', function ($warrantyList) {
                return '<p class="os-linkcolor" data-field="warrantymodal_qrcode" style="margin: 0;">SKU</p>
                    <img src="../images/qrcode.jpg" width="100px" alt="Logo">';
            })
            ->addColumn('warrantymodal_size', function ($warrantyList) {

                return '<p data-field="warrantymodal_size">Size XL <br> Color Bronze</p>';

            })
            ->addColumn('warrantymodal_warranty', function ($warrantyList) {

                return '<p class="os-linkcolor" data-field="warrantymodal_warranty" style="cursor: pointer; margin: 0; text-align: center;">Warranty No 45673672 <br> Invoice No 56787655</p>';

            })
            ->addColumn('warrantymodal_qty', function ($warrantyList) {

                return '<p data-field="warrantymodal_qty" style="cursor: pointer; margin: 0;text-align: center;">100</p>';

            })
            ->addColumn('print', function ($warrantyList) {
                return '</button>
                    <button class="btn btn-success sellerbutton bg-web"
                    style="padding-left:12px">
                    <span>Print</span>
                    </button>';
            })
            ->addColumn('deleted', function ($warrantyList) {
                return '<p data-field="deleted"
                    style="background-color:red;
                    border-radius:5px;margin:auto;
                    width:25px;height:25px;
                    display:block;cursor: pointer;"
                    class="text-danger remove">
                    <i class="fas fa-times text-white"
                    style="color:white;opacity:1.0;
                    padding:4px 7px;
                    -webkit-text-stroke: 1px red;"></i></p>';
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
            $product->ptype    = 'warranty';
            $product->save();

            $warranty             = new warranty();
            $warranty->product_id = $product->id;
            $warranty->save();

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

                $warranty = warranty::where('id', $id)->first();
                return view('warranty.warranty-modals', compact(['id', 'fieldName', 'warranty']));
            }

        } catch (\Illuminate\Database\QueryException $ex) {
            $response = (new ApiMessageController())->queryexception($ex);
        }
    }

	public function update(Request $request)
    {
        try {
            $allInputs = $request->all();
            $warranty_id = $request->get('warranty_id');
            $changed = false;

            $validation = Validator::make($allInputs, [
                'warranty_id' => 'required',
            ]);

            if ($validation->fails()) {
                throw new Exception("product_not_found", 1);
            }

			$warranty = warranty::find($warranty_id);

			if (!$warranty) {
                throw new Exception("product_not_found", 1);
            }

            if ($request->has('price')) {
                if ($warranty->price != (int) str_replace('.','',
					number_format($request->price,2))) {
                    $warranty->price = (int) str_replace('.','',
						number_format($request->price,2));
                    $changed = true;
                    $msg = "Price updated";
                }
            }

            if ($changed == true) {
				$warranty->save();
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
            $warranty        = warranty::find($id);
            $product_id      = $warranty->product_id;

            $is_exist = merchantproduct::where(['product_id' => $product_id, 'merchant_id' => $this->user_data->company_id()])->first();

            if (!$is_exist) {
                throw new Exception("Error Processing Request", 1);
            }

            $is_exist->delete();
            product::find($product_id)->delete();
            $warranty->delete();

            // \DB::table('client')->where('id', $id)->delete();
            $msg = "Product deleted successfully";
            return view('layouts.dialog', compact('msg'));
        } catch (\Illuminate\Database\QueryException $ex) {
            $msg = "Some error occured";

            return view('layouts.dialog', compact('msg'));
        }
    }

    public function showServiceWarranty()
    {
        return view('service_warranty.warranty');
    }

    public function showWarrantyView()
    {
        return view('warranty.warranty');
    }

    public function check_quantity($product_id)
    {
        $final_qty = 0;
        $sales_qty = opos_receiptproduct::where('product_id', $product_id)        ->leftjoin('opos_itemdetails','opos_itemdetails.receiptproduct_id','=','opos_receiptproduct.id')
                    ->leftjoin('opos_receipt','opos_receipt.id','=','opos_receiptproduct.receipt_id')
                    ->leftjoin('opos_receiptdetails','opos_receipt.id','=','opos_receiptdetails.receipt_id')
                    ->leftjoin('opos_locationterminal', 'opos_receipt.terminal_id', '=', 'opos_locationterminal.terminal_id')
                    ->leftjoin('location', 'location.id', '=', 'opos_locationterminal.location_id')
                    ->where('opos_receiptdetails.void','!=',1)
                    ->sum('opos_receiptproduct.quantity');
	$stock_qty = stockreport::join('stockreportproduct','stockreportproduct.stockreport_id','=','stockreport.id')->
		where('stockreportproduct.product_id', $product_id)->sum('stockreportproduct.quantity');

                $refund_c = opos_refund::join("opos_receiptproduct",'opos_receiptproduct.id','=','opos_refund.receiptproduct_id')->where('opos_receiptproduct.product_id',$product_id)->where('refund_type','C')->count();

                $refund_dx = opos_refund::join("opos_receiptproduct",'opos_receiptproduct.id','=','opos_refund.receiptproduct_id')->where('opos_receiptproduct.product_id',$product_id)->where('refund_type','Dx')->count();
                $wastage = opos_wastageproduct::where('product_id',$product_id)->sum('wastage_qty');
        $final_qty = $stock_qty - $sales_qty + $refund_c - $refund_dx - $wastage; 
        return $final_qty;
    }

    public function showWarrantyProductView($id)
    {
        $system_id = $id;
        $product = product::where('systemid', $system_id)->first();
        $product_id = $product->id;
        $product_qty = $this->check_quantity($product->id);
        $barcodematrix = null;//SettingBarcodeMatrix::where('category_id', $product->prdcategory_id)->first();
        
        $barcode_sku = productbarcode::where('product_id',$product->id)->first();

        $barcode = DNS1D::getBarcodePNG(trim($system_id), "C128");
        // dd($barcode);

        $id = Auth::user()->id;
        $user_roles = usersrole::where('user_id', $id)->get();

        $is_king =  \App\Models\Company::where('owner_user_id', Auth::user()->id)->first();

        if ($is_king != null) {
            $is_king = true;
        } else {
            $is_king  = false;
        }

        return view('warranty.warrantybarcode', compact('user_roles','system_id', 'is_king','product','product_qty','barcodematrix','barcode_sku','barcode','product_id'));

        // return view('warranty.warrantyproduct',compact('user_roles','is_king'));
    }
}
