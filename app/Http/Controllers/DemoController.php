<?php
namespace App\Http\Controllers;
use App\Models\combine;
use App\Models\openBill;
use App\Models\openBillProduct;
use App\Models\opos_promo;
use App\Models\opos_promo_location;
use App\Models\opos_promo_product;
use App\Models\opos_receipt;
use App\Models\oposFtype;
use App\Models\platopenbillproductspecial;
use App\Models\productpreference;
use App\Models\reserve;
use App\Models\skipTable;
use App\Models\skipTableProduct;
use App\Models\skipTableProductSpecial;
use App\Models\splitTable;
use App\Models\opos_receiptproduct;
use App\Models\opos_extreceiptparam;
use App\User;
use App\Models\OgFuel;
use App\Models\OgFuelMovement;
use Carbon\Carbon;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Matrix\Exception;
use phpDocumentor\Reflection\Types\Null_;
use Yajra\DataTables\DataTables;
use \App\Classes\SystemID;
use \App\Classes\UserData;
use \App\Models\location;
use \App\Models\locationterminal;
use \App\Models\membership;
use \App\Models\merchantlocation;
use \App\Models\FranchiseMerchantLoc;
use \App\Models\merchantproduct;
use \App\Models\merchantprd_category;
use \App\Models\prd_inventory;
use \App\Models\product;
use \App\Models\restaurant;
use \App\Models\terminal;
use \App\Models\usersrole;
use \App\Models\voucher;
use \App\Models\warranty;
use \Illuminate\Support\Facades\Auth;
use App\Models\Company;
use App\Http\Controllers\OposComponentController;
use App\Models\prdcategory;
use App\Models\prd_subcategory;
use \App\Models\prd_special;
use \App\Models\productspecial;
use \App\Models\opos_btype;
use \App\Models\opos_terminalproduct;
use \App\Models\opos_refund;
use App\Models\opos_tablename;
use Log;
use DB;
use Milon\Barcode\DNS1D;
use App\Models\opos_receiptdetails;
use App\Models\takeaway;
use \App\Models\productcolor;
use \App\Models\Merchant;
use \App\Models\opos_brancheod;
use \App\Models\opos_eoddetails;
use \App\Models\opos_itemdetails;
use \App\Models\opos_itemdetailsremarks;
use \App\Models\opos_receiptproductspecial;
use \App\Models\locationproduct;
use \App\Models\opos_locationterminal;
use \App\Models\StockReport;
use \App\Models\stockreportremarks;
use \App\Models\opos_damagerefund;
use \App\Models\Staff;
use \App\Models\opos_wastage;
use \App\Models\opos_wastageproduct;
use \App\Models\productbarcode;
use \App\Models\warehouse;
use \App\Models\rack;
use \App\Models\rackproduct;
use \App\Models\stockreportproduct;
use \App\Models\stockreportproductrack;
use \App\Models\productbarcodelocation;
use \App\Models\voucherproduct;
use Illuminate\Support\Facades\Schema;
use \App\Classes\thumb;
use App\Models\FoodCourt;
use App\Models\FranchiseMerchantLocTerm;
use App\Models\voucherlist;
use App\Models\FoodCourtMerchantTerminal;
use App\Models\FoodCourtMerchant;


class DemoController extends Controller
{

    protected $user_data;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('CheckRole:ret');
    }


    public function index(){

    }


    public function demoOpsum()
    {
        return view('demo.demo');
    }


}
