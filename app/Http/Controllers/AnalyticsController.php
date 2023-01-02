<?php

namespace App\Http\Controllers;

use App\Classes\MonthlySalesStatement;
use App\Models\opos_locationterminal;
use App\Models\opos_receiptdetails;
use Log;
use Illuminate\Http\Request;
use \App\Models\usersrole;
use \App\Models\role;
use \Illuminate\Support\Facades\Auth;
use App\Models\merchantlocation;
use App\Models\location;
use App\Classes\UserData;

use \App\Classes\SystemID;
use \App\Models\locationterminal;
use \App\Models\FranchiseMerchantLoc;
use Yajra\DataTables\DataTables;


use \App\Models\membership;

use \App\Models\merchantproduct;
use \App\Models\prd_inventory;
use \App\Models\product;
use \App\Models\restaurant;
use \App\Models\terminal;
use \App\Models\FoodCourt;

use \App\Models\voucher;
use \App\Models\warranty;

use \App\Models\FranchiseMerchantLocTerm;

use App\Models\Company;
use App\Http\Controllers\OposComponentController;
use App\Models\prdcategory;
use App\Models\prd_subcategory;
use \App\Models\prd_special;
use \App\Models\productspecial;
use \App\Models\opos_btype;
use \App\Models\opos_terminalproduct;
use \App\Models\opos_eoddetails;

use \App\Models\opos_receiptproduct;
use \App\Models\opos_itemdetails;
use \App\Models\opos_receipt;
use \App\Models\Merchant;
use DB;
use Mpdf\Mpdf;

use \App\Models\CMRManagement;

use \App\User;

use \Carbon\Carbon;

use \App\Http\Controllers\InventoryController as Inventory;
use PDF;
use Excel;
class AnalyticsController extends Controller
{

    public function get_location($filter = true, $merchant_id = null)
    {

        if ($merchant_id == null) {
            $user_data = new UserData();
            $merchant_id = $user_data->company_id();
        }

        $return = [];

        $return['direct_location'] = DB::select('
			SELECT
				l.branch,
				l.created_at,
				l.id,
				l.foodcourt,
				c.name
			FROM
				company c,
				location l,
				merchantlocation ml
			WHERE
				c.id = ' . $merchant_id . '
				AND ml.merchant_id = c.id
				AND ml.location_id = l.id
				AND l.branch is NOT NULL
				AND l.deleted_at is NULL
				AND l.foodcourt = 0;
		');

        foreach ($return['direct_location'] as $key => $val) {
            $val->direct = 1;

            $checkLocation = DB::table('franchisemerchantloc')->
            where('location_id', $val->id)->
            first();

            $val->franchisor = !empty($checkLocation);
            $return['direct_location'][$key] = $val;
        }


        $return['foodcourt_location'] = DB::select('
			SELECT
				l.branch,
				l.created_at,
				l.id,
				l.foodcourt,
				c.name
			FROM
				company c,
				location l,
				merchantlocation ml
			WHERE
				c.id = ' . $merchant_id . '
				AND ml.merchant_id = c.id
				AND ml.location_id = l.id
				AND l.branch is NOT NULL
				AND l.deleted_at is NULL
				AND l.foodcourt = 1;
		');

        $return['tenant_foodcourt'] = DB::table('location')->
        join('foodcourt', 'foodcourt.location_id', '=', 'location.id')->
        join('foodcourtmerchant', 'foodcourtmerchant.foodcourt_id', '=', 'foodcourt.id')->
        where('foodcourtmerchant.tenant_merchant_id', $merchant_id)->
        whereNull('location.deleted_at')->
        whereNull('foodcourt.deleted_at')->
        select('location.branch', 'location.created_at', 'location.id', 'location.foodcourt')->
        get()->toArray();

        $return['franchiseLocations'] = DB::table('franchisemerchant')->
        join('franchisemerchantloc', 'franchisemerchantloc.franchisemerchant_id', '=', 'franchisemerchant.id')->
        join('location', 'location.id', '=', 'franchisemerchantloc.location_id')->
        where([
            'franchisemerchant.franchisee_merchant_id' => $merchant_id
        ])->
        whereNull('franchisemerchant.deleted_at')->
        whereNull('franchisemerchantloc.deleted_at')->
        select('location.branch', 'location.created_at', 'location.id', 'location.foodcourt')->
        get()->toArray();

        foreach ($return['franchiseLocations'] as $key => $val) {
            $val->franchise = 1;
            $return['franchiseLocations'][$key] = $val;
        }

        if ($filter) {
            /*$allow_locations = DB::table('userslocation')->
                where('user_id', Auth::User()->id)->
                whereNull('deleted_at')->
                get()->
                pluck('location_id');

                $return = 	array_map(function($f) use ($allow_locations) {
                    return array_filter($f, function($z) use ($allow_locations) {
                        return ($allow_locations->contains($z->id));
                    });
                },$return);
             */
        }

        return $return;
    }

    public function excluded_term()
    {
        $this->user_data = new UserData();
        $model = new terminal();
        $company_id = $this->user_data->company_id();

        $ids = merchantlocation::
        join('location', 'location.id', '=',
            'merchantlocation.location_id')->
        where('merchantlocation.merchant_id',
            $this->user_data->company_id())->
        whereNull('location.deleted_at')->
        pluck('merchantlocation.location_id');

        $fids = FranchiseMerchantLoc::join('location', 'location.id', '=',
            'franchisemerchantloc.location_id')->
        join('franchisemerchant', 'franchisemerchant.id', '=',
            'franchisemerchantloc.franchisemerchant_id')->
        where('franchisemerchant.franchisee_merchant_id',
            $this->user_data->company_id())->
        whereNull('location.deleted_at')->
        pluck('franchisemerchantloc.location_id');

        $foodCourtTerminalIds = FoodCourt::
        join('foodcourtmerchant', 'foodcourtmerchant.foodcourt_id',
            '=', 'foodcourt.id')->
        join('foodcourtmerchantterminal',
            'foodcourtmerchantterminal.foodcourtmerchant_id',
            '=', 'foodcourtmerchant.id')->
        where('foodcourtmerchant.tenant_merchant_id',
            $this->user_data->company_id())->
        pluck('foodcourtmerchantterminal.terminal_id');

        $terminalExcludeIds = FoodCourt::
        join('foodcourtmerchant', 'foodcourtmerchant.foodcourt_id',
            '=', 'foodcourt.id')->
        join('foodcourtmerchantterminal',
            'foodcourtmerchantterminal.foodcourtmerchant_id',
            '=', 'foodcourtmerchant.id')->
        where('foodcourt.owner_merchant_id',
            $this->user_data->company_id())->
        whereNotIn('foodcourtmerchantterminal.terminal_id',
            $foodCourtTerminalIds)->
        pluck('foodcourtmerchantterminal.terminal_id');

        $franchiseTerminalIds = FranchiseMerchantLocTerm::
        join('franchisemerchantloc', 'franchisemerchantloc.id', '=',
            'franchisemerchantlocterm.franchisemerchantloc_id')->
        join('franchisemerchant', 'franchisemerchant.id', '=',
            'franchisemerchantloc.franchisemerchant_id')->
        where('franchisemerchant.franchisee_merchant_id',
            $this->user_data->company_id())->
        pluck('franchisemerchantlocterm.terminal_id');

        $allTerminalIds = $foodCourtTerminalIds->toArray();
        $allTerminalIds2 = $franchiseTerminalIds->toArray();

        $data = $model->join('opos_locationterminal',
            'opos_locationterminal.terminal_id', '=', 'opos_terminal.id')->
        whereNull('opos_terminal.deleted_at')->
        whereIn('location_id', $ids)->
        whereNotIn('opos_terminal.id', $terminalExcludeIds)->
        orWhereIn('opos_terminal.id', $allTerminalIds)->
        orderby('opos_terminal.created_at', 'asc')->
        select(
            'opos_locationterminal.location_id',
            'opos_locationterminal.terminal_id'
        );

        $data2 = $model->join('franchisemerchantlocterm',
            'franchisemerchantlocterm.terminal_id', '=', 'opos_terminal.id')
            ->join('franchisemerchantloc',
                'franchisemerchantlocterm.franchisemerchantloc_id', '=', 'franchisemerchantloc.id')->
            whereNull('opos_terminal.deleted_at')->
            whereIn('franchisemerchantloc.location_id', $fids)->
            WhereIn('opos_terminal.id', $allTerminalIds2)->
            orderby('opos_terminal.created_at', 'asc')->
            select(
                'franchisemerchantloc.location_id',
                'franchisemerchantlocterm.terminal_id'
            );

        $merged = $data->unionAll($data2)->get();

        $merged = $merged->filter(function ($z) use ($company_id) {
            $is_own = DB::table('franchisemerchantlocterm')->
            join('franchisemerchantloc', 'franchisemerchantloc.id',
                '=', 'franchisemerchantlocterm.franchisemerchantloc_id')->
            leftjoin('franchisemerchant', 'franchisemerchant.id',
                '=', 'franchisemerchantloc.franchisemerchant_id')->
            leftjoin('franchise', 'franchise.id', '=', 'franchisemerchant.franchise_id')->
            where('franchisemerchantlocterm.terminal_id', $z->terminal_id)->
            where('franchise.owner_merchant_id', $company_id)->
            whereNull('franchise.deleted_at')->
            first();

            return empty($is_own);
        });

        return $merged;
    }

    function showCashProductSalesView(Request $request)
    {

        Log::debug('***** showCashProductSales() *****');

        $user_data = new UserData();
        $merchant_id = $user_data->company_id();

        $logged_in_user_id = Auth::user()->id;


        $landing = true;
        $since = \Carbon\Carbon::now();
        $locations = [];
        $branch_location = [];

        $get_location = $this->get_location();
        foreach ($get_location as $key => $val) {
            $$key = $val;
            $location_id = array_column($branch_location, 'id');
            foreach ($val as $location) {
                if (!in_array($location->id, $location_id)) {
                    $branch_location = array_merge($branch_location, [$location]);
                }
            }
        }

        //branch location
        switch ($request->segment) {
            case 'direct':
                $loc_id = array_map(function ($f) {
                    return $f->id;
                }, $direct_location);
                break;
            case 'franchise':
                $loc_id = array_map(function ($f) {
                    return $f->id;
                }, $franchiseLocations);

                break;
            case 'food':
                $loc_id = array_merge($foodcourt_location, $tenant_foodcourt);

                $loc_id = array_map(function ($z) {
                    return $z->id;
                }, $loc_id);

                break;
            case 'all':
                break;
        }

        $all_data = DB::table('opos_itemdetails')->
        join('opos_receiptproduct', 'opos_itemdetails.receiptproduct_id', '=', 'opos_receiptproduct.id')->
        join('opos_receipt', 'opos_receipt.id', '=', 'opos_receiptproduct.receipt_id')->
        join('opos_locationterminal', 'opos_locationterminal.terminal_id', '=', 'opos_receipt.terminal_id')->
        join('staff', 'opos_receipt.staff_user_id', '=', 'staff.user_id')->
        leftjoin('product', 'product.id', '=', 'opos_receiptproduct.product_id')->
        whereNull('opos_receiptproduct.promo_id')->
        where('opos_receipt.company_id', $merchant_id)->
        select(
            'opos_itemdetails.amount',
            'product.name', 'product.thumbnail_1',
            'product.id', 'staff.company_id as merchant_id',
            'opos_locationterminal.location_id',
            'opos_receiptproduct.created_at'
        )->
        get();

        $credit_sales = DB::table('invoice')->
        join('invoiceproduct', 'invoiceproduct.invoice_id',
            '=', 'invoice.id')->
        select('invoiceproduct.product_name as name',
            'invoiceproduct.product_thumbnail as thumbnail_1',
            'invoiceproduct.product_id as id',
            'invoice.dealer_merchant_id as merchant_id', 'invoice.created_at',
            DB::RAW('(invoiceproduct.quantity * invoiceproduct.price) as amount')
        )->
        where('invoice.supplier_merchant_id', $merchant_id)->
        get();

        if ($request->has('button_filter')) {

            if ($request->button_filter == 'credit') {
                $all_data = $credit_sales;
            } else if ($request->button_filter == 'all') {
                $credit_sales->map(function ($f) use ($all_data) {
                    $all_data->push($f);
                });
            }

            $landing = false;
        }
        $promo = DB::table('opos_receiptproduct')->
        join('opos_receipt', 'opos_receipt.id', '=', 'opos_receiptproduct.receipt_id')->
        join('opos_locationterminal', 'opos_locationterminal.terminal_id', '=', 'opos_receipt.terminal_id')->
        join('staff', 'opos_receipt.staff_user_id', '=', 'staff.user_id')->
        leftjoin('opos_promo', 'opos_promo.id', '=', 'opos_receiptproduct.promo_id')->
        where('opos_receiptproduct.product_id', 0)->
        whereNotNull('opos_receiptproduct.promo_id')->
        where('opos_receipt.company_id', $merchant_id)->
        select(
            'opos_receiptproduct.price as amount',
            'opos_receiptproduct.name', 'opos_promo.thumb_photo as thumbnail_1',
            'opos_receiptproduct.promo_id as id', 'staff.company_id as merchant_id',
            'opos_locationterminal.location_id', 'opos_receiptproduct.promo_id',
            'opos_receiptproduct.created_at'
        )->
        get();

        //getting segment
        if (!empty($request->segment)) {
            if (isset($loc_id)) {
                $all_data = $all_data->whereIn('location_id', $loc_id);
                $promo = $promo->whereIn('location_id', $loc_id);
                $landing = false;
            }
        }

        if (!empty($request->loc_id) && $request->loc_id != 'all') {
            $all_data = $all_data->where('location_id', $request->loc_id);
            $promo = $promo->where('location_id', $request->loc_id);
            $landing = false;
        }

        if (!empty($request->from_date_all) && !empty($request->to_date_all)) {

            $dateTimeFrom = strtotime($request->from_date_all);
            $dateTimeTo = strtotime($request->to_date_all);

            $all_data = $all_data->filter(function ($z) use ($dateTimeFrom, $dateTimeTo) {
                if (strtotime($z->created_at) >= $dateTimeFrom && strtotime($z->created_at) <= $dateTimeTo) {
                    return true;
                } else {
                    return false;
                }
            });

            $promo = $promo->filter(function ($z) use ($dateTimeFrom, $dateTimeTo) {
                if (strtotime($z->created_at) >= $dateTimeFrom && strtotime($z->created_at) <= $dateTimeTo) {
                    return true;
                } else {
                    return false;
                }
            });


            $landing = false;
        }

        if ($request->segment == 'all' || $request->loc_id == 'all') {
            $landing = false;
        }

        if ($landing == true) {
            $all_data = $all_data->whereBetween('created_at', [date('Y-m-d 00:00:00'), date('Y-m-d 23:59:59')]);
            $promo = $promo->whereBetween('created_at', [date('Y-m-d 00:00:00'), date('Y-m-d 23:59:59')]);
        }

        $result = collect();

        $each_Product_amount = $all_data->unique('id');
        $each_Product_amount->map(function ($z) use ($all_data, $result) {
            $z->T_amount = $all_data->where('id', $z->id)->sum('amount');
            $result->push($z);
        });

        $promo_mapped = $promo->unique('id');

        $promo_mapped->map(function ($z) use ($promo, $result, $each_Product_amount) {
            $z->T_amount = $promo->where('id', $z->id)->sum('amount');
            $result->push($z);
            $each_Product_amount->push($z);
        });

        $result = $result->sortByDesc('T_amount')->values();
        if ($landing == false) {
            return response()->json($result);
        }

        $approved_merchant = company::find($merchant_id);
        $userApprovedDate = date("Y-m-d", strtotime($approved_merchant->approved_at)) ?? date("Y-m-d");

        return view('analytics.cash_productsales', compact(
            'since',
            'locations',
            'each_Product_amount',
            'branch_location',
            'approved_merchant',
            'userApprovedDate'
        ));
    }

    public function showCashProductSalesViewDownloadPDF(Request $request)
    {
        $user_data = new UserData();
        $merchant_id = $user_data->company_id();

        $logged_in_user_id = Auth::user()->id;


        $landing = true;
        $since = \Carbon\Carbon::now();
        $locations = [];
        $branch_location = [];

        $get_location = $this->get_location();
        foreach ($get_location as $key => $val) {
            $$key = $val;
            $location_id = array_column($branch_location, 'id');
            foreach ($val as $location) {
                if (!in_array($location->id, $location_id)) {
                    $branch_location = array_merge($branch_location, [$location]);
                }
            }
        }
        $segmentName="All";
        //branch location
        switch ($request->segment) {
            case 'direct':
                $loc_id = array_map(function ($f) {
                    return $f->id;
                }, $direct_location);
                $segmentName="Direct Segment";
                break;
            case 'franchise':
                $loc_id = array_map(function ($f) {
                    return $f->id;
                }, $franchiseLocations);
                $segmentName="Franchise Segment";
                break;
            case 'food':
                $loc_id = array_merge($foodcourt_location, $tenant_foodcourt);
                $loc_id = array_map(function ($z) {
                    return $z->id;
                }, $loc_id);
                $segmentName="Food Court Segment";
                break;
            case 'all':
                break;
        }


        $all_data = DB::table('opos_itemdetails')->
        join('opos_receiptproduct', 'opos_itemdetails.receiptproduct_id', '=', 'opos_receiptproduct.id')->
        join('opos_receipt', 'opos_receipt.id', '=', 'opos_receiptproduct.receipt_id')->
        join('opos_locationterminal', 'opos_locationterminal.terminal_id', '=', 'opos_receipt.terminal_id')->
        join('staff', 'opos_receipt.staff_user_id', '=', 'staff.user_id')->
        leftjoin('product', 'product.id', '=', 'opos_receiptproduct.product_id')->
        whereNull('opos_receiptproduct.promo_id')->
        where('opos_receipt.company_id', $merchant_id)->
        select(
            'opos_itemdetails.amount',
            'product.name', 'product.thumbnail_1',
            'product.id', 'staff.company_id as merchant_id',
            'opos_locationterminal.location_id',
            'opos_receiptproduct.created_at', 'product.systemid', 'opos_receiptproduct.quantity', 'opos_itemdetails.price'
        )->
        get();

		Log::debug('merchant_id='.$merchant_id);
		Log::debug('all_data='.json_encode($all_data));

        $credit_sales = DB::table('invoice')->
        join('invoiceproduct', 'invoiceproduct.invoice_id',
            '=', 'invoice.id')->
        select('invoiceproduct.product_name as name',
            'invoiceproduct.product_thumbnail as thumbnail_1',
            'invoiceproduct.product_id as id',
            'invoice.dealer_merchant_id as merchant_id', 'invoice.created_at',
            DB::RAW('(invoiceproduct.quantity * invoiceproduct.price) as amount')
        )->
        where('invoice.supplier_merchant_id', $merchant_id)->
        get();

        if ($request->has('button_filter')) {

            if ($request->button_filter == 'credit') {
                $all_data = $credit_sales;
            } else if ($request->button_filter == 'all') {
                $credit_sales->map(function ($f) use ($all_data) {
                    $all_data->push($f);
                });
            }

            $landing = false;
        }
        $promo = DB::table('opos_receiptproduct')->
        join('opos_receipt', 'opos_receipt.id', '=', 'opos_receiptproduct.receipt_id')->
        join('opos_locationterminal', 'opos_locationterminal.terminal_id', '=', 'opos_receipt.terminal_id')->
        join('staff', 'opos_receipt.staff_user_id', '=', 'staff.user_id')->
        leftjoin('opos_promo', 'opos_promo.id', '=', 'opos_receiptproduct.promo_id')->
        where('opos_receiptproduct.product_id', 0)->
        whereNotNull('opos_receiptproduct.promo_id')->
        where('opos_receipt.company_id', $merchant_id)->
        select(
            'opos_receiptproduct.price as amount',
            'opos_receiptproduct.name', 'opos_promo.thumb_photo as thumbnail_1',
            'opos_receiptproduct.promo_id as id', 'staff.company_id as merchant_id',
            'opos_locationterminal.location_id', 'opos_receiptproduct.promo_id',
            'opos_receiptproduct.created_at'
        )->
        get();

        //getting segment
        if (!empty($request->segment)) {
            if (isset($loc_id)) {
                $all_data = $all_data->whereIn('location_id', $loc_id);
                $promo = $promo->whereIn('location_id', $loc_id);
                $landing = false;
            }
        }

        if (!empty($request->loc_id) && $request->loc_id != 'all') {
            $all_data = $all_data->where('location_id', $request->loc_id);
            $promo = $promo->where('location_id', $request->loc_id);
            $landing = false;
        }

        if (!empty($request->from_date_all) && !empty($request->to_date_all)) {

            $dateTimeFrom = strtotime($request->from_date_all);
            $dateTimeTo = strtotime($request->to_date_all);

            $all_data = $all_data->filter(function ($z) use ($dateTimeFrom, $dateTimeTo) {
                if (strtotime($z->created_at) >= $dateTimeFrom && strtotime($z->created_at) <= $dateTimeTo) {
                    return true;
                } else {
                    return false;
                }
            });

            $promo = $promo->filter(function ($z) use ($dateTimeFrom, $dateTimeTo) {
                if (strtotime($z->created_at) >= $dateTimeFrom && strtotime($z->created_at) <= $dateTimeTo) {
                    return true;
                } else {
                    return false;
                }
            });


            $landing = false;
        }

        if ($request->segment == 'all' || $request->loc_id == 'all') {
            $landing = false;
        }

        if ($landing == true) {
            $all_data = $all_data->whereBetween('created_at', [date('Y-m-d 00:00:00'), date('Y-m-d 23:59:59')]);
            $promo = $promo->whereBetween('created_at', [date('Y-m-d 00:00:00'), date('Y-m-d 23:59:59')]);
        }

        $result = collect();

        $each_Product_amount = $all_data->unique('id');
        $each_Product_amount->map(function ($z) use ($all_data, $result) {
            $z->T_amount = $all_data->where('id', $z->id)->sum('amount');
            $z->T_quantity = $all_data->where('id', $z->id)->sum('quantity');
            $result->push($z);
        });

        $promo_mapped = $promo->unique('id');

        $promo_mapped->map(function ($z) use ($promo, $result, $each_Product_amount) {
            $z->T_amount = $promo->where('id', $z->id)->sum('amount');
            $z->T_quantity = $promo->where('id', $z->id)->sum('quantity');
            $result->push($z);
            $each_Product_amount->push($z);
        });

        $product_detail = $result->sortByDesc('T_amount')->values();
//        if ($landing == false) {
//            return response()->json($result);
//        }

        $approved_merchant = company::find($merchant_id);
        $userApprovedDate = date("Y-m-d", strtotime($approved_merchant->approved_at)) ?? date("Y-m-d");
        $requestValue = $request->all();
        if ($request->loc_id != 'all') {
            $collection = collect($branch_location);
             $branch = $collection->where('id', $request->loc_id)->first();
            $branchName = $branch->branch;
        } else {
            $branchName = $request->loc_id;
        }
        $pdf = PDF::setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true])
            ->loadView("analytics.cash_productsales_pdf", compact('approved_merchant', 'product_detail', 'requestValue', 'branchName','segmentName'
            ));

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
       // return $pdf->stream();
        return $pdf->download('product_sales.pdf');
    }

    public function monthlySalesStatement(Request $request)
    {
        $user_data = new UserData();
        $merchant_id = $user_data->company_id();

        $branch_location = [];

        $get_location = $this->get_location();
        foreach ($get_location as $key => $val) {
            $$key = $val;
            $location_id = array_column($branch_location, 'id');
            foreach ($val as $location) {
                if (!in_array($location->id, $location_id)) {
                    $branch_location = array_merge($branch_location, [$location]);
                }
            }
        }

        //branch location
        switch ($request->segment) {
            case 'direct':
                $loc_id = array_map(function ($f) {
                    return $f->id;
                }, $direct_location);
                break;
            case 'franchise':
                $loc_id = array_map(function ($f) {
                    return $f->id;
                }, $franchiseLocations);

                break;
            case 'food':
                $loc_id = array_merge($foodcourt_location, $tenant_foodcourt);

                $loc_id = array_map(function ($z) {
                    return $z->id;
                }, $loc_id);

                break;
            case 'all':
            default:
                $loc_id = null;
                break;
        }

        $all_data = DB::table('opos_itemdetails')->
        join('opos_receiptproduct', 'opos_itemdetails.receiptproduct_id', '=', 'opos_receiptproduct.id')->
        join('opos_receipt', 'opos_receipt.id', '=', 'opos_receiptproduct.receipt_id')->
        join('opos_locationterminal', 'opos_locationterminal.terminal_id', '=', 'opos_receipt.terminal_id')->
        join('staff', 'opos_receipt.staff_user_id', '=', 'staff.user_id')->
        leftjoin('product', 'product.id', '=', 'opos_receiptproduct.product_id')->
        whereNull('opos_receiptproduct.promo_id')->
        where('opos_receipt.company_id', $merchant_id)->
        where([['product.id', '!=', 0]])->
        select(
            'opos_itemdetails.amount',
            'product.name', 'product.thumbnail_1',
            'product.id', 'product.prdsubcategory_id',
            'staff.company_id as merchant_id',
            'opos_locationterminal.location_id',
            'opos_receiptproduct.created_at'
        )->
        get();


        $all_data_promo = DB::table('opos_receiptproduct')->
        join('opos_receipt', 'opos_receipt.id', '=', 'opos_receiptproduct.receipt_id')->
        join('opos_locationterminal', 'opos_locationterminal.terminal_id', '=', 'opos_receipt.terminal_id')->
        join('staff', 'opos_receipt.staff_user_id', '=', 'staff.user_id')->
        //leftjoin('product','product.id', '=' ,'opos_receiptproduct.product_id')->
        whereNotNull('opos_receiptproduct.promo_id')->
        where('opos_receiptproduct.product_id', '0')->
        where('opos_receipt.company_id', $merchant_id)->
        select(
            'opos_receiptproduct.price as amount',
            'opos_receiptproduct.promo_id as id',
            'staff.company_id as merchant_id',
            'opos_locationterminal.location_id',
            'opos_receiptproduct.created_at'
        )->
        get();


        $promo_ids = $all_data_promo->pluck('id')->unique();


        $promo_details = DB::table('opos_promo')->
        whereIn('id', $promo_ids)->
        select('opos_promo.id', 'opos_promo.title as name')->
        get()->unique();

        $subCatIds = $all_data->pluck('prdsubcategory_id')->unique();

        $subCats = DB::table('prd_subcategory')->
        whereIn('id', $subCatIds)->
        whereNull('deleted_at')->
        get();


        $result = collect();

        if ($request->has('from_date_all')) {

            $from_date_all = strtotime($request->from_date_all);
            $month = date('m', $from_date_all);
            $year = date('Y', $from_date_all);

        } else {
            $month = date('m');
            $year = date('Y');
        }

        $totalDays = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        for ($x = 1; $x <= $totalDays; $x++) {

            $date = "$year-$month-$x";

            $row = collect();

            $total = 0;

            $dateTimeFrom = strtotime("$date 00:00:00");
            $dateTimeTo = strtotime("$date 23:59:59");

            $sum = $all_data->filter(function ($a) use ($dateTimeFrom, $dateTimeTo) {
                if (strtotime($a->created_at) >= $dateTimeFrom && strtotime($a->created_at) <= $dateTimeTo
                ) {
                    return true;
                } else {
                    return false;
                }
            });


            $sum_promo = $all_data_promo->filter(function ($a) use ($dateTimeFrom, $dateTimeTo) {

                if (strtotime($a->created_at) >= $dateTimeFrom && strtotime($a->created_at) <= $dateTimeTo
                ) {
                    return true;
                } else {
                    return false;
                }
            });

            if (!empty($request->loc_id) && $request->loc_id != 'all') {
                $sum = $sum->where('location_id', $request->loc_id);
                $sum_promo = $sum_promo->where('location_id', $request->loc_id);
            }

            //getting segment
            if (!empty($request->segment)) {
                if (isset($loc_id)) {
                    $sum = $sum->whereIn('location_id', $loc_id);
                    $sum_promo = $sum_promo->whereIn('location_id', $loc_id);
                }
            }


            $t = $sum->sum('amount');
            $t += $sum_promo->sum('amount');

            $row->push([
                "date" => date("dMy", strtotime($date)),
                "total" => number_format($t / 100, 2)
            ]);

            $promo_details->map(function ($z) use ($row, $sum_promo) {

                $sumByPromo = $sum_promo->where('id', $z->id);
                $cell['subcat_id'] = 0;
                $cell['name'] = $z->name;
                $cell['amount'] = number_format($sumByPromo->sum('amount') / 100, 2);
                $row->push($cell);
            });

            $subCats->map(function ($z) use ($row, $sum) {

                $sumByCat = $sum->where('prdsubcategory_id', $z->id);

                $cell['subcat_id'] = $z->id;
                $cell['name'] = $z->name;
                $cell['amount'] = number_format($sumByCat->sum('amount') / 100, 2);
                $row->push($cell);
            });


            $result->push($row);
        }
//        return $result;
//        if ($request->has('ajax')) {
//            return $result;
//        }

        $months = [];
        for ($x = 1; $x <= 12; $x++) {
            $date = "2020-$x-$x";
            $months[] = date('M', strtotime($date));
        }
        $subCats = $promo_details->merge($subCats);

        $approved_merchant = company::find($merchant_id);
       // return $result;
        $userApprovedDate = date("Y-m-d", strtotime($approved_merchant->approved_at)) ?? date("Y-m-d");
//        return view('analytics.export.monthly_sales', [
//            'data' => $result,
//            'subCats' => $subCats,
//        ]);
        //return  Excel::download(new UserExport(),'ff.csv');

        return Excel::download(new MonthlySalesStatement($result,$subCats), 'monthly_sales.csv');
    }

    public function showCashProductSalesViewDownloadPDFOld(Request $request)
    {
        return $request->all();
        Log::debug('***** showCashProductSales() *****');

        $id = Auth::user()->id;
        $logged_in_user_id = Auth::user()->id;
        $user_roles = usersrole::where('user_id', $id)->get();

        $is_king = \App\Models\Company::where('owner_user_id',
            Auth::user()->id)->first();

        if ($is_king != null) {
            $is_king = true;

        } else {
            $is_king = false;
        }

        $since = \Carbon\Carbon::now();
        $locations = [];


        $each_Product_amount = DB::select(
            'SELECT SUM(opos_itemdetails.amount) AS T_amount,product.name,product.thumbnail_1,product.id,merchantproduct.merchant_id 

			FROM 
				opos_itemdetails,opos_receiptproduct,product,merchantproduct,company
			WHERE
				opos_itemdetails.receiptproduct_id = opos_receiptproduct.id 
			    AND product.id = opos_receiptproduct.product_id
			    AND merchantproduct.product_id = opos_receiptproduct.product_id
			    AND company.owner_user_id=:id
			    AND company.id=merchantproduct.merchant_id
			   
			GROUP BY 
				product.id 
			ORDER BY 
				(T_amount) DESC;', ['id' => $logged_in_user_id]
        );


        //date range filter
        $dateTimeFrom = $request->from_date_all . ' 00:00:00';
        $dateTimeTo = $request->to_date_all . ' 23:59:59';

        $each_amount_date_range = DB::select(
            'SELECT SUM(opos_itemdetails.amount) AS T_amount,product.name,product.thumbnail_1,product.id,merchantproduct.merchant_id 

			FROM 
				opos_itemdetails,opos_receiptproduct,product,merchantproduct,company
			WHERE
				opos_itemdetails.receiptproduct_id = opos_receiptproduct.id 
			    AND product.id = opos_receiptproduct.product_id
			    AND merchantproduct.product_id = opos_receiptproduct.product_id
			    AND company.owner_user_id=:id
			    AND company.id=merchantproduct.merchant_id
                AND opos_receiptproduct.created_at BETWEEN  :froms AND :to
			   
			GROUP BY 
				product.id 
			ORDER BY 
				(T_amount) DESC;', ['id' => $logged_in_user_id, 'froms' => $dateTimeFrom, 'to' => $dateTimeTo]
        );

        // return response()->json($each_amount_date_range);

        if ($each_amount_date_range != null) {
            return response()->json($each_amount_date_range);
        }

        //branch location
        $branch_location = DB::select('
			SELECT
				l.branch,
				l.created_at,
				l.id,
				c.name
			FROM
				company c,
				merchant m,
				location l,
				merchantlocation ml
			WHERE
				c.owner_user_id = ' . $id . '
				AND m.company_id = c.id
				AND ml.merchant_id = m.id
				AND ml.location_id = l.id
				AND l.branch is NOT NULL
				AND l.deleted_at is NULL;
		');


        //location all_branch
        $branch_detail = DB::select(
            'SELECT 
				SUM(opos_itemdetails.amount) AS T_amount,
				product.name,
				product.thumbnail_1,
				product.id,
				merchantproduct.merchant_id,
				opos_receipt.id as recptid	

			FROM 
				opos_itemdetails,
				opos_receiptproduct,
				product,
				merchantproduct,
				company,
				opos_receipt,
                location,
                opos_locationterminal
			WHERE
				opos_itemdetails.receiptproduct_id = opos_receiptproduct.id 
			    AND product.id = opos_receiptproduct.product_id
			    AND merchantproduct.product_id = opos_receiptproduct.product_id
			    AND company.id=merchantproduct.merchant_id
				AND	location.id=:loc_id
				AND location.id = opos_locationterminal.location_id
				AND opos_locationterminal.terminal_id=opos_receipt.terminal_id
				AND opos_receipt.id = opos_receiptproduct.receipt_id
			   
			GROUP BY 
				product.name 
			ORDER BY 
				(T_amount) DESC;', ['loc_id' => $request->branch_id]
        );

        if ($branch_detail != null) {
            return response()->json($branch_detail);
        }

        //custome daterange_location all_branch

        $daterange_branch_detail = DB::select(
            'SELECT 
				SUM(opos_itemdetails.amount) AS T_amount,
				product.name,
				product.thumbnail_1,
				product.id,
				merchantproduct.merchant_id,
				opos_receipt.id as recptid	

			FROM 
				opos_itemdetails,
				opos_receiptproduct,
				product,
				merchantproduct,
				company,
				opos_receipt,
                location,
                opos_locationterminal
			WHERE
				opos_itemdetails.receiptproduct_id = opos_receiptproduct.id 
			    AND product.id = opos_receiptproduct.product_id
			    AND merchantproduct.product_id = opos_receiptproduct.product_id
			    AND company.owner_user_id=:id
			    AND company.id=merchantproduct.merchant_id
				AND location.id = opos_locationterminal.location_id
				AND opos_locationterminal.terminal_id=opos_receipt.terminal_id
				AND opos_receipt.id = opos_receiptproduct.receipt_id
				AND opos_receiptproduct.created_at BETWEEN  :froms AND :to
			   
			GROUP BY 
				product.name 
			ORDER BY 
				(T_amount) DESC;', ['id' => $logged_in_user_id, 'froms' => $request->from_date, 'to' => $request->to_date]
        );

        if ($daterange_branch_detail != null) {
            return response()->json($daterange_branch_detail);
        }

        //check approved merchat
        // if merchant not approved then will be disable all date rage
        $approved_merchant = DB::select('SELECT created_at FROM `company` WHERE `owner_user_id` =:id AND created_at IS NOT NULL;', ['id' => $logged_in_user_id]
        );
        //User Approved Date
        $userApprovedDate = null;

        // DB::select('
        // 	SELECT
        // 		c.approved_at
        // 	FROM
        // 		company c
        // 	WHERE
        // 		c.owner_user_id = '.$id.'
        // ');
        // $userApprovedDate = \Carbon\Carbon::parse($userApprovedDate[0]->approved_at)->format('d F Y');

        $view = view('analytics.cash_productsales_pdf', compact(
            'user_roles',
            'is_king',
            'since',
            'locations',
            'each_Product_amount',
            'branch_location',
            'approved_merchant',
            'userApprovedDate'
        ))->render();

        $pdf = \App::make('dompdf.wrapper');

        $pdf->loadHTML($view);

        return $pdf->stream();
    }

    function showCashProductSalesQtyView(Request $request)
    {
        Log::debug('***** showCashProductSales() *****');

        $user_data = new UserData();
        $merchant_id = $user_data->company_id();
        $logged_in_user_id = Auth::user()->id;

        $landing = true;
        $since = \Carbon\Carbon::now();
        $locations = [];
        $branch_location = [];

        $get_location = $this->get_location();
        foreach ($get_location as $key => $val) {
            $$key = $val;
            $location_id = array_column($branch_location, 'id');
            foreach ($val as $location) {
                if (!in_array($location->id, $location_id)) {
                    $branch_location = array_merge($branch_location, [$location]);
                }
            }
        }

        //branch location
        switch ($request->segment) {
            case 'direct':
                $loc_id = array_map(function ($f) {
                    return $f->id;
                }, $direct_location);
                break;
            case 'franchise':
                $loc_id = array_map(function ($f) {
                    return $f->id;
                }, $franchiseLocations);

                break;
            case 'food':
                $loc_id = array_merge($foodcourt_location, $tenant_foodcourt);

                $loc_id = array_map(function ($z) {
                    return $z->id;
                }, $loc_id);

                break;
            case 'all':
                break;
        }

        $all_data = DB::table('opos_itemdetails')->
        join('opos_receiptproduct', 'opos_itemdetails.receiptproduct_id', '=', 'opos_receiptproduct.id')->
        join('opos_receipt', 'opos_receipt.id', '=', 'opos_receiptproduct.receipt_id')->
        join('opos_locationterminal', 'opos_locationterminal.terminal_id', '=', 'opos_receipt.terminal_id')->
        join('staff', 'opos_receipt.staff_user_id', '=', 'staff.user_id')->
        leftjoin('product', 'product.id', '=', 'opos_receiptproduct.product_id')->
        whereNull('opos_receiptproduct.promo_id')->
        where('opos_receipt.company_id', $merchant_id)->
        select(
            'opos_receiptproduct.quantity',
            'product.name', 'product.thumbnail_1',
            'product.id', 'staff.company_id as merchant_id',
            'opos_locationterminal.location_id',
            'opos_receiptproduct.created_at'
        )->
        get();

        $credit_sales = DB::table('invoice')->
        join('invoiceproduct', 'invoiceproduct.invoice_id',
            '=', 'invoice.id')->
        select('invoiceproduct.product_name as name',
            'invoiceproduct.product_thumbnail as thumbnail_1',
            'invoiceproduct.product_id as id',
            'invoice.dealer_merchant_id as merchant_id', 'invoice.created_at',
            'invoiceproduct.quantity',
            DB::RAW('(invoiceproduct.quantity * invoiceproduct.price) as amount')
        )->
        where('invoice.supplier_merchant_id', $merchant_id)->
        get();


        if ($request->has('button_filter')) {

            if ($request->button_filter == 'credit') {
                $all_data = $credit_sales;
            } else if ($request->button_filter == 'all') {
                $credit_sales->map(function ($f) use ($all_data) {
                    $all_data->push($f);
                });
            }

            $landing = false;
        }

        $promo = DB::table('opos_receiptproduct')->
        join('opos_receipt', 'opos_receipt.id', '=', 'opos_receiptproduct.receipt_id')->
        join('opos_locationterminal', 'opos_locationterminal.terminal_id', '=', 'opos_receipt.terminal_id')->
        join('staff', 'opos_receipt.staff_user_id', '=', 'staff.user_id')->
        leftjoin('opos_promo', 'opos_promo.id', '=', 'opos_receiptproduct.promo_id')->
        where('opos_receiptproduct.product_id', 0)->
        whereNotNull('opos_receiptproduct.promo_id')->
        where('opos_receipt.company_id', $merchant_id)->
        select(
            'opos_receiptproduct.quantity',
            'opos_receiptproduct.name', 'opos_promo.thumb_photo as thumbnail_1',
            'opos_receiptproduct.promo_id as id', 'staff.company_id as merchant_id',
            'opos_locationterminal.location_id', 'opos_receiptproduct.promo_id',
            'opos_receiptproduct.created_at'
        )->
        get();

        //getting segment
        if (!empty($request->segment)) {
            if (isset($loc_id)) {
                $all_data = $all_data->whereIn('location_id', $loc_id);
                $landing = false;
            }
        }

        if (!empty($request->loc_id) && $request->loc_id != 'all') {
            $all_data = $all_data->where('location_id', $request->loc_id);
            $promo = $promo->where('location_id', $request->loc_id);
            $landing = false;
        }

        if (!empty($request->from_date_all) && !empty($request->to_date_all)) {

            $dateTimeFrom = strtotime($request->from_date_all);
            $dateTimeTo = strtotime($request->to_date_all);

            $all_data = $all_data->filter(function ($z) use ($dateTimeFrom, $dateTimeTo) {
                if (strtotime($z->created_at) >= $dateTimeFrom && strtotime($z->created_at) <= $dateTimeTo) {
                    return true;
                } else {
                    return false;
                }
            });

            $promo = $promo->filter(function ($z) use ($dateTimeFrom, $dateTimeTo) {
                if (strtotime($z->created_at) >= $dateTimeFrom && strtotime($z->created_at) <= $dateTimeTo) {
                    return true;
                } else {
                    return false;
                }
            });


            $landing = false;
        }

        if ($request->segment == 'all' || $request->loc_id == 'all') {
            $landing = false;
        }


        if ($landing == true) {
            $all_data = $all_data->whereBetween('created_at', [date('Y-m-d 00:00:00'), date('Y-m-d 23:59:59')]);
            $promo = $promo->whereBetween('created_at', [date('Y-m-d 00:00:00'), date('Y-m-d 23:59:59')]);
        }

        $result = collect();
        $each_product_quantity = $all_data->unique('id');
        $each_product_quantity->map(function ($z) use ($all_data, $result) {
            $z->T_quantity = $all_data->where('id', $z->id)->sum('quantity');
            $result->push($z);
        });

        $promo_mapped = $promo->unique('id');
        $promo_mapped->map(function ($z) use ($promo, $result, $each_product_quantity) {
            $z->T_quantity = $promo->where('id', $z->id)->sum('quantity');
            $result->push($z);
            $each_product_quantity->push($z);
        });

        $result = $result->sortByDesc('T_quantity')->values();


        if ($landing == false) {
            return response()->json($result);
        }

        $approved_merchant = DB::select('SELECT created_at FROM `company` 
			WHERE `id` =:id 
			AND created_at IS NOT NULL;', ['id' => $merchant_id]
        );

        $approved_merchant = company::find($merchant_id);
        $userApprovedDate = date("Y-m-d", strtotime($approved_merchant->approved_at)) ?? date("Y-m-d");

        return view('analytics.cash_productsales_by_qty', compact(
            'since',
            'locations',
            'each_product_quantity',
            'branch_location',
            'approved_merchant',
            'userApprovedDate'
        ));
    }


    function showCashCashierSalesView(Request $request)
    {
        $user_data = new UserData();
        $merchant_id = $user_data->company_id();
        $logged_in_user_id = Auth::user()->id;
        $is_approved = false;
        $approved_at = null;
        $is_enable = false;

        $landing = true;
        $since = \Carbon\Carbon::now();
        $locations = [];
        $branch_location = [];

        $get_location = $this->get_location();
        foreach ($get_location as $key => $val) {
            $$key = $val;
            $location_id = array_column($branch_location, 'id');
            foreach ($val as $location) {
                if (!in_array($location->id, $location_id)) {
                    $branch_location = array_merge($branch_location, [$location]);
                }
            }
        }

        //branch location
        switch ($request->segment) {
            case 'direct':
                $loc_id = array_map(function ($f) {
                    return $f->id;
                }, $direct_location);
                break;
            case 'franchise':
                $loc_id = array_map(function ($f) {
                    return $f->id;
                }, $franchiseLocations);

                break;
            case 'food':
                $loc_id = array_merge($foodcourt_location, $tenant_foodcourt);

                $loc_id = array_map(function ($z) {
                    return $z->id;
                }, $loc_id);

                break;
            case 'all':
                break;
        }

        $all_data = DB::table('opos_itemdetails')->
        join('opos_receiptproduct', 'opos_itemdetails.receiptproduct_id', '=', 'opos_receiptproduct.id')->
        join('opos_receipt', 'opos_receipt.id', '=', 'opos_receiptproduct.receipt_id')->
        join('opos_locationterminal', 'opos_locationterminal.terminal_id', '=', 'opos_receipt.terminal_id')->
        join('staff', 'opos_receipt.staff_user_id', '=', 'staff.user_id')->
        join('users', 'users.id', '=', 'staff.user_id')->
        leftjoin('product', 'product.id', '=', 'opos_receiptproduct.product_id')->
        where('opos_receipt.company_id', $merchant_id)->
        select(
            'opos_itemdetails.amount',
            'opos_receipt.staff_user_id', 'users.name',
            'product.id', 'staff.company_id as merchant_id',
            'opos_locationterminal.location_id',
            'opos_receiptproduct.created_at'
        )->
        get();
        //getting segment
        if (!empty($request->segment)) {
            if (isset($loc_id)) {
                $all_data = $all_data->whereIn('location_id', $loc_id);
                $landing = false;
            }
        }

        if (!empty($request->loc_id) && $request->loc_id != 'all' && (empty($request->segment) || $request->segment == 'all')) {
            $all_data = $all_data->where('location_id', $request->loc_id);
            $landing = false;
        }

        if (!empty($request->from_date_all) && !empty($request->to_date_all)) {

            $dateTimeFrom = strtotime($request->from_date_all);
            $dateTimeTo = strtotime($request->to_date_all);

            $all_data = $all_data->filter(function ($z) use ($dateTimeFrom, $dateTimeTo) {
                if (strtotime($z->created_at) >= $dateTimeFrom && strtotime($z->created_at) <= $dateTimeTo) {
                    return true;
                } else {
                    return false;
                }
            });

            $landing = false;
        }

        if ($request->segment == 'all' || $request->loc_id == 'all') {
            $landing = false;
        }

        if ($request->has('button_filter')) {

            if ($request->button_filter == 'credit') {
                $all_data = collect();
            }

            $landing = false;
        }

        if ($landing == true) {
            $all_data = $all_data->whereBetween('created_at', [date('Y-m-d 00:00:00'), date('Y-m-d 23:59:59')]);
        }

        $result = collect();
        $each_staff_total = $all_data->unique('staff_user_id');
        $each_staff_total->map(function ($z) use ($all_data, $result) {
            $z->each_total = $all_data->where('staff_user_id', $z->staff_user_id)->sum('amount');
            $result->push($z);
        });

		Log::debug('BEFORE amount='.json_encode($result));

        $result = $result->sortByDesc('each_total')->values();

		Log::debug('AFTER amount='.json_encode($result));

        if ($landing == false) {
            return response()->json($result);
        }

        $approved_merchant = DB::select('SELECT created_at FROM `company` 
			WHERE `id` =:id 
			AND created_at IS NOT NULL;', ['id' => $merchant_id]
        );

        $approved_merchant = company::find($merchant_id);
        $userApprovedDate = date("Y-m-d", strtotime($approved_merchant->approved_at)) ?? date("Y-m-d");


        return view('analytics.cash_cashiersales',
            compact(
                'since',
                'locations',
                'each_staff_total',
                'branch_location',
                'is_approved',
                'approved_at',
                'is_enable',
                'userApprovedDate'
            ));
    }


    function showCashStaffSalesView(Request $request)
    {
        $user_data = new UserData();
        $merchant_id = $user_data->company_id();

        $logged_in_user_id = Auth::user()->id;


        $landing = true;
        $since = \Carbon\Carbon::now();
        $locations = [];
        $branch_location = [];

        $get_location = $this->get_location();
        foreach ($get_location as $key => $val) {
            $$key = $val;
            $location_id = array_column($branch_location, 'id');
            foreach ($val as $location) {
                if (!in_array($location->id, $location_id)) {
                    $branch_location = array_merge($branch_location, [$location]);
                }
            }
        }

        //branch location
        switch ($request->segment) {
            case 'direct':
                $loc_id = array_map(function ($f) {
                    return $f->id;
                }, $direct_location);
                break;
            case 'franchise':
                $loc_id = array_map(function ($f) {
                    return $f->id;
                }, $franchiseLocations);

                break;
            case 'food':
                $loc_id = array_merge($foodcourt_location, $tenant_foodcourt);

                $loc_id = array_map(function ($z) {
                    return $z->id;
                }, $loc_id);

                break;
            case 'all':
                break;
        }

        $all_data = collect();

        $credit_sales = DB::table('invoice')->
        join('invoiceproduct', 'invoiceproduct.invoice_id',
            '=', 'invoice.id')->
        join('salesorderdeliveryorder', 'salesorderdeliveryorder.deliveryorder_id', '=',
            'invoice.deliveryorder_id')->
        select('invoiceproduct.product_name as name',
            'invoiceproduct.product_thumbnail as thumbnail_1',
            'invoiceproduct.product_id as id',
            'invoice.dealer_merchant_id as merchant_id', 'invoice.created_at',
            'invoice.staff_user_id as staff_user',
            DB::RAW('(invoiceproduct.quantity * invoiceproduct.price) as amount'),
			)->where('invoice.supplier_merchant_id', $merchant_id)->
        get();

        if ($request->has('button_filter')) {

            if ($request->button_filter == 'credit') {
                $all_data = $credit_sales;
            } else if ($request->button_filter == 'all') {
                $credit_sales->map(function ($f) use ($all_data) {
                    $all_data->push($f);
                });
            }

            $landing = false;
        }

        //getting segment
        if (!empty($request->segment)) {
            if (isset($loc_id)) {
                $all_data = $all_data->whereIn('location_id', $loc_id);
                $promo = $promo->whereIn('location_id', $loc_id);
                $landing = false;
            }
        }

        if (!empty($request->loc_id) && $request->loc_id != 'all') {
            $all_data = $all_data->where('location_id', $request->loc_id);
            $promo = $promo->where('location_id', $request->loc_id);
            $landing = false;
        }

        if (!empty($request->from_date_all) && !empty($request->to_date_all)) {

            $dateTimeFrom = strtotime($request->from_date_all);
            $dateTimeTo = strtotime($request->to_date_all);

            $all_data = $all_data->filter(function ($z) use ($dateTimeFrom, $dateTimeTo) {
                if (strtotime($z->created_at) >= $dateTimeFrom && strtotime($z->created_at) <= $dateTimeTo) {
                    return true;
                } else {
                    return false;
                }
            });


            $landing = false;
        }

        if ($request->segment == 'all' || $request->loc_id == 'all') {
            $landing = false;
        }

        if ($landing == true) {
            $all_data = $all_data->whereBetween('created_at', [date('Y-m-d 00:00:00'), date('Y-m-d 23:59:59')]);
        }

        $result = collect();

        $each_Product_amount = $all_data->unique('staff_user');
        $each_Product_amount->map(function ($z) use ($all_data, $result) {
            $z->T_amount = $all_data->where('staff_user', $z->staff_user)->sum('amount');
            $z->staff_user = DB::table('users')->find($z->staff_user)->name;
            $result->push($z);
        });

        $result = $result->sortByDesc('T_amount')->values();
        if ($landing == false) {
            return response()->json($result);
        }

        $approved_merchant = company::find($merchant_id);
        $userApprovedDate = date("Y-m-d", strtotime($approved_merchant->approved_at)) ?? date("Y-m-d");

        return view('analytics.cash_staffsales', compact(
            'since',
            'locations',
            'each_Product_amount',
            'branch_location',
            'approved_merchant',
            'userApprovedDate'
        ));

        //analytics.cash_staffsales
    }


    public function cashStaffSalesPdf(Request  $request)
    {

        $user_data = new UserData();
        $merchant_id = $user_data->company_id();

        $logged_in_user_id = Auth::user()->id;


        $landing = true;
        $since = \Carbon\Carbon::now();
        $locations = [];
        $branch_location = [];

        $get_location = $this->get_location();
        foreach ($get_location as $key => $val) {
            $$key = $val;
            $location_id = array_column($branch_location, 'id');
            foreach ($val as $location) {
                if (!in_array($location->id, $location_id)) {
                    $branch_location = array_merge($branch_location, [$location]);
                }
            }
        }

        //branch location
        switch ($request->segment) {
            case 'direct':
                $loc_id = array_map(function ($f) {
                    return $f->id;
                }, $direct_location);
                break;
            case 'franchise':
                $loc_id = array_map(function ($f) {
                    return $f->id;
                }, $franchiseLocations);

                break;
            case 'food':
                $loc_id = array_merge($foodcourt_location, $tenant_foodcourt);

                $loc_id = array_map(function ($z) {
                    return $z->id;
                }, $loc_id);

                break;
            case 'all':
                break;
        }

        $all_data = collect();

        $credit_sales = DB::table('invoice')->
        join('invoiceproduct', 'invoiceproduct.invoice_id',
            '=', 'invoice.id')->
        join('salesorderdeliveryorder', 'salesorderdeliveryorder.deliveryorder_id', '=',
            'invoice.deliveryorder_id')->
        select('invoiceproduct.product_name as name',
            'invoiceproduct.product_thumbnail as thumbnail_1',
            'invoiceproduct.product_id as id',
            'invoice.dealer_merchant_id as merchant_id', 'invoice.created_at',
            'invoice.staff_user_id as staff_user',
            DB::RAW('(invoiceproduct.quantity * invoiceproduct.price) as amount'),
			)->where('invoice.supplier_merchant_id', $merchant_id)->
        get();

        if ($request->has('button_filter')) {

            if ($request->button_filter == 'credit') {
                $all_data = $credit_sales;
            } else if ($request->button_filter == 'all') {
                $credit_sales->map(function ($f) use ($all_data) {
                    $all_data->push($f);
                });
            }

            $landing = false;
        }

        //getting segment
        if (!empty($request->segment)) {
            if (isset($loc_id)) {
                $all_data = $all_data->whereIn('location_id', $loc_id);
                $promo = $promo->whereIn('location_id', $loc_id);
                $landing = false;
            }
        }

        if (!empty($request->loc_id) && $request->loc_id != 'all') {
            $all_data = $all_data->where('location_id', $request->loc_id);
            $promo = $promo->where('location_id', $request->loc_id);
            $landing = false;
        }

        if (!empty($request->from_date_all) && !empty($request->to_date_all)) {

            $dateTimeFrom = strtotime($request->from_date_all);
            $dateTimeTo = strtotime($request->to_date_all);

            $all_data = $all_data->filter(function ($z) use ($dateTimeFrom, $dateTimeTo) {
                if (strtotime($z->created_at) >= $dateTimeFrom && strtotime($z->created_at) <= $dateTimeTo) {
                    return true;
                } else {
                    return false;
                }
            });


            $landing = false;
        }

        if ($request->segment == 'all' || $request->loc_id == 'all') {
            $landing = false;
        }

        if ($landing == true) {
            $all_data = $all_data->whereBetween('created_at', [date('Y-m-d 00:00:00'), date('Y-m-d 23:59:59')]);
        }

        $result = collect();

          $each_Product_amount = $all_data->unique('staff_user');
        $each_Product_amount->map(function ($z) use ($all_data, $result) {
            $z->T_amount = $all_data->where('staff_user', $z->staff_user)->sum('amount');
            $staff=DB::table('staff')->where('user_id',$z->staff_user)->first();
            $z->staff_systemId =$staff->systemid ;
            $z->staff_user = DB::table('users')->find($z->staff_user)->name;

            $result->push($z);
        });

        $staff_detail = $result->sortByDesc('T_amount')->values();
//        if ($landing == false) {
//            return response()->json($result);
//        }

        $approved_merchant = company::find($merchant_id);
        $userApprovedDate = date("Y-m-d", strtotime($approved_merchant->approved_at)) ?? date("Y-m-d");
        $requestValue = $request->all();
        if ($request->loc_id != 'all') {
            $collection = collect($branch_location);
            $branch = $collection->where('id', $request->loc_id)->first();
            $branchName = $branch->branch;
        } else {
            $branchName = $request->loc_id;
        }
        $pdf = PDF::setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true])
            ->loadView("analytics.cash_staffsales_pdf", compact('approved_merchant', 'staff_detail', 'requestValue'
            ));

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
        return $pdf->download('staff_sales.pdf');

    }


    function showCashBranchSalesView(Request $request)
    {

        $user_data = new UserData();
        $merchant_id = $user_data->company_id();


        $id = Auth::user()->id;
        $user_roles = usersrole::where('user_id', $id)->get();
        $logged_in_user_id = Auth::user()->id;

        $is_king = \App\Models\Company::where('owner_user_id',
            Auth::user()->id)->first();

        if ($is_king != null) {
            $is_king = true;

        } else {
            $is_king = false;
        }

        $excluded_term = $this->excluded_term();
        $excluded_loc = $excluded_term->pluck('location_id')->unique();

        $since = \Carbon\Carbon::now();
        $locations = [];
        $branch_location = [];
        $landing = true;
        $get_location = $this->get_location();

        foreach ($get_location as $key => $val) {
            $$key = $val;
            $location_id = array_column($branch_location, 'id');
            foreach ($val as $location) {

                $is_t_loc = DB::table('opos_locationterminal')->
                where('location_id', $location->id)->
                whereNull('deleted_at')->first();

                if (!in_array($location->id, $location_id) && !empty($is_t_loc)) {
                    $branch_location = array_merge($branch_location, [$location]);
                }
            }
        }

        $branch_location_ids = array_map(function ($z) {
            return $z->id;
        }, $branch_location);

        $all_data = DB::table('opos_itemdetails')->
        join('opos_receiptproduct', 'opos_itemdetails.receiptproduct_id', '=', 'opos_receiptproduct.id')->
        join('opos_receipt', 'opos_receipt.id', '=', 'opos_receiptproduct.receipt_id')->
        join('opos_locationterminal', 'opos_locationterminal.terminal_id', '=', 'opos_receipt.terminal_id')->
        join('staff', 'opos_receipt.staff_user_id', '=', 'staff.user_id')->
        join('users', 'users.id', '=', 'staff.user_id')->
        join('location', 'location.id', '=', 'opos_locationterminal.location_id')->
        leftjoin('product', 'product.id', '=', 'opos_receiptproduct.product_id')->
        where('opos_receipt.company_id', $merchant_id)->
        select(
            'opos_itemdetails.amount',
            'opos_receipt.staff_user_id', 'users.name',
            'product.id', 'staff.company_id as merchant_id',
            'opos_locationterminal.location_id',
            'location.branch',
            'opos_itemdetails.receiptproduct_id',
            'opos_receiptproduct.created_at'
        )->
        get();

        if (!empty($request->from_date_all) && !empty($request->to_date_all)) {

            $dateTimeFrom = strtotime($request->from_date_all);
            $dateTimeTo = strtotime($request->to_date_all);

            $all_data = $all_data->filter(function ($z) use ($dateTimeFrom, $dateTimeTo) {
                if (strtotime($z->created_at) >= $dateTimeFrom && strtotime($z->created_at) <= $dateTimeTo) {
                    return true;
                } else {
                    return false;
                }
            });

            $landing = false;
        }


        if ($request->has('button_filter')) {

            if ($request->button_filter == 'credit') {
                $all_data = collect();
            }

            $landing = false;
        }

        if ($landing == true) {
            $all_data = $all_data->whereBetween('created_at', [date('Y-m-d 00:00:00'), date('Y-m-d 23:59:59')]);
        }

        $result = collect();

        $each_location_total = $all_data->unique('location_id');

        $each_location_total->map(function ($z) use ($all_data, $result) {
            $z->T_amount = $all_data->where('location_id', $z->location_id)->sum('amount');
            $result->push($z);
        });

        array_map(function ($z) use ($each_location_total, $result, $excluded_loc) {
            if (!$each_location_total->contains('branch', $z->branch) && $excluded_loc->contains($z->id)) {
                $temp = [];
                $temp['T_amount'] = 0;
                $temp['location_id'] = $z->id;
                $temp['branch'] = $z->branch;
                $each_location_total->push($temp);
                $result->push($temp);
                unset($temp);
            }
        }, $branch_location);

        $result = $result->sortByDesc('T_amount')->values();
        $each_location_total = (object)$each_location_total->map(function ($z) {
            return (object)$z;
        })->reject(function ($z) {
        });

        if ($landing == false) {
            return response()->json($result);
        }

        //User Approved Date
        $approved_merchant = company::find($merchant_id);
        $userApprovedDate = date("Y-m-d", strtotime($approved_merchant->approved_at)) ?? date("Y-m-d");

        return view('analytics.cash_branchsales',
            compact(
                'user_roles',
                'is_king',
                'since',
                'locations',
                'each_location_total',
                'userApprovedDate'
            ));
    }

    public function branchSalesPdf(Request $request)
    {
        $user_data = new UserData();
        $merchant_id = $user_data->company_id();


        $id = Auth::user()->id;
        $user_roles = usersrole::where('user_id', $id)->get();
        $logged_in_user_id = Auth::user()->id;

        $is_king = \App\Models\Company::where('owner_user_id',
            Auth::user()->id)->first();

        if ($is_king != null) {
            $is_king = true;

        } else {
            $is_king = false;
        }

        $excluded_term = $this->excluded_term();
        $excluded_loc = $excluded_term->pluck('location_id')->unique();

        $since = \Carbon\Carbon::now();
        $locations = [];
        $branch_location = [];
        $landing = true;
        $get_location = $this->get_location();

        foreach ($get_location as $key => $val) {
            $$key = $val;
            $location_id = array_column($branch_location, 'id');
            foreach ($val as $location) {

                $is_t_loc = DB::table('opos_locationterminal')->
                where('location_id', $location->id)->
                whereNull('deleted_at')->first();

                if (!in_array($location->id, $location_id) && !empty($is_t_loc)) {
                    $branch_location = array_merge($branch_location, [$location]);
                }
            }
        }

        $branch_location_ids = array_map(function ($z) {
            return $z->id;
        }, $branch_location);

        $all_data = DB::table('opos_itemdetails')->
        join('opos_receiptproduct', 'opos_itemdetails.receiptproduct_id', '=', 'opos_receiptproduct.id')->
        join('opos_receipt', 'opos_receipt.id', '=', 'opos_receiptproduct.receipt_id')->
        join('opos_locationterminal', 'opos_locationterminal.terminal_id', '=', 'opos_receipt.terminal_id')->
        join('staff', 'opos_receipt.staff_user_id', '=', 'staff.user_id')->
        join('users', 'users.id', '=', 'staff.user_id')->
        join('location', 'location.id', '=', 'opos_locationterminal.location_id')->
        leftjoin('product', 'product.id', '=', 'opos_receiptproduct.product_id')->
        where('opos_receipt.company_id', $merchant_id)->
        select(
            'opos_itemdetails.amount',
            'opos_receipt.staff_user_id', 'users.name',
            'product.id', 'staff.company_id as merchant_id',
            'opos_locationterminal.location_id',
            'location.branch',
            'opos_itemdetails.receiptproduct_id',
            'opos_receiptproduct.created_at','location.systemid as locationId'
        )->
        get();

        if (!empty($request->from_date_all) && !empty($request->to_date_all)) {

            $dateTimeFrom = strtotime($request->from_date_all);
            $dateTimeTo = strtotime($request->to_date_all);

            $all_data = $all_data->filter(function ($z) use ($dateTimeFrom, $dateTimeTo) {
                if (strtotime($z->created_at) >= $dateTimeFrom && strtotime($z->created_at) <= $dateTimeTo) {
                    return true;
                } else {
                    return false;
                }
            });

            $landing = false;
        }


        if ($request->has('button_filter')) {

            if ($request->button_filter == 'credit') {
                $all_data = collect();
            }

            $landing = false;
        }

        if ($landing == true) {
            $all_data = $all_data->whereBetween('created_at', [date('Y-m-d 00:00:00'), date('Y-m-d 23:59:59')]);
        }

        $result = collect();

        $each_location_total = $all_data->unique('location_id');

        $each_location_total->map(function ($z) use ($all_data, $result) {
            $z->T_amount = $all_data->where('location_id', $z->location_id)->sum('amount');
            $result->push($z);
        });

        array_map(function ($z) use ($each_location_total, $result, $excluded_loc) {
            if (!$each_location_total->contains('branch', $z->branch) && $excluded_loc->contains($z->id)) {
                $temp = [];
                $temp['T_amount'] = 0;
                $loc=DB::table('location')->find($z->id);
                $temp['location_id'] = $loc->systemid;
                $temp['branch'] = $z->branch;
                $each_location_total->push($temp);
                $result->push($temp);
                unset($temp);
            }
        }, $branch_location);

        $branch_data = $result->sortByDesc('T_amount')->values();
        $each_location_total = (object)$each_location_total->map(function ($z) {
            return (object)$z;
        })->reject(function ($z) {
        });

//        if ($landing == false) {
//            return response()->json($result);
//        }

        //User Approved Date
        $approved_merchant = company::find($merchant_id);
        $userApprovedDate = date("Y-m-d", strtotime($approved_merchant->approved_at)) ?? date("Y-m-d");
        $requestValue = $request->all();


        $pdf = PDF::setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true])
            ->loadView("analytics.cash_branchsales_pdf", compact( 'branch_data', 'requestValue'
            ));

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
        return $pdf->download('branchsales.pdf');

    }


    function showCashPaymentModeView(Request $request)
    {

        $user_data = new UserData();
        $merchant_id = $user_data->company_id();
        $ajax = false;
        $locationId = -1;

        if (!empty($request->loc_id)) {
            $locationId = $request->loc_id;

        }

        $since = \Carbon\Carbon::now();
        $locations = [];

        $terminalId = opos_locationterminal::join('merchantlocation', 'merchantlocation.location_id',
            '=', 'opos_locationterminal.location_id')->
        where('merchantlocation.merchant_id', $user_data->company_id())->
        pluck('terminal_id');


        $receipts = opos_receipt::select("opos_receipt.*", 'opos_locationterminal.location_id')->
        join('opos_locationterminal', 'opos_locationterminal.terminal_id',
            '=', 'opos_receipt.terminal_id')->
        join('staff', 'staff.user_id', '=', 'opos_receipt.staff_user_id')->
        where('opos_receipt.company_id', $user_data->company_id())->
        get();

        if ($request->has('button_filter')) {

            if ($request->button_filter == 'credit') {
                $receipts = collect();
            }
            $ajax = true;
        }


        $receipt_records['cash_total'] = 0;
        $receipt_records['credit_total'] = 0;
        $receipt_records['membership_total'] = 0;
        $receipt_records['voucher_total'] = 0;
        $receipt_records['grand_total'] = 0;

        if ($locationId != -1 && $locationId != 'all' && $locationId != 'null') {
            $receipts = $receipts->where('location_id', $locationId);
            $ajax = true;
        }

        if (!empty($request->from_date_all) && !empty($request->to_date_all)) {

            $dateTimeFrom = strtotime($request->from_date_all);
            $dateTimeTo = strtotime($request->to_date_all);

            $receipts = $receipts->filter(function ($z) use ($dateTimeFrom, $dateTimeTo) {
                if (strtotime($z->created_at) >= $dateTimeFrom && strtotime($z->created_at) <= $dateTimeTo) {
                    return true;
                } else {
                    return false;
                }
            });

            $ajax = true;

        }

        if (!$ajax) {
            $receipts = $receipts->whereBetween('created_at', [date('Y-m-d 00:00:00'), date('Y-m-d 23:59:59')]);
        }


        foreach ($receipts as $receipt) {
            $receipt_details = opos_receiptdetails::where('receipt_id', $receipt->id)->get();
            foreach ($receipt_details as $receipt_detail) {
                $receipt_detail->cash = $receipt_detail->cash_received - $receipt_detail->change;
                $receipt_records['cash_total'] += $receipt_detail->cash;
                $receipt_records['credit_total'] += $receipt_detail->creditcard;
                $receipt_records['membership_total'] += $receipt_detail->point;
                $receipt_records['voucher_total'] += $receipt_detail->wallet;
                $receipt_records['grand_total'] += $receipt_detail->total;
            }
        }

        $each_payment = DB::select(
            'SELECT
   				SUM(opos_receiptdetails.item_amount) AS cash_total,
    			(SELECT 
    				SUM(opos_receiptdetails.item_amount)  
    			FROM 
    				opos_receiptdetails 
    			where  
    				opos_receiptdetails.cash_received IS null ) AS cradit_total
			FROM 
				opos_receiptdetails
			WHERE 
				opos_receiptdetails.cash_received IS NOT null'
        );

        // p: payment mode
        $branch_location = [];

        $get_location = $this->get_location();

        foreach ($get_location as $key => $val) {
            $$key = $val;
            $location_id = array_column($branch_location, 'id');
            foreach ($val as $location) {
                if (!in_array($location->id, $location_id)) {
                    $branch_location = array_merge($branch_location, [$location]);
                }
            }
        }


        $total = $receipt_records['membership_total'] + $receipt_records['voucher_total'];
        $total = $total == 0 ? 1 : $total;
        /*
                $receipt_records['cash_percentage'] = (($total / $receipt_records['cash_total'] * 100)) - 15;
                $receipt_records['cash_percentage'] = $receipt_records['cash_percentage'] < 15 ? 0:$receipt_records['cash_percentage'];


                $receipt_records['credit_percentage'] = (($receipt_records['credit_total'] / $total) * 100) - 15;
                $receipt_records['credit_percentage'] = $receipt_records['credit_percentage'] < 15 ? 0:$receipt_records['credit_percentage'];
         */

        /*$receipt_records['cash_total'] = number_format($receipt_records['cash_total'] /100, 2);
        $receipt_records['credit_total'] = number_format( $receipt_records['credit_total']/100,2);
         */
        $receipt_records['cash_percentage'] = 0;
        $receipt_records['credit_percentage'] = 0;

        if ($receipt_records['cash_total'] > $receipt_records['credit_total']) {
            $receipt_records['cash_percentage'] = 100 - 15;
            $receipt_records['credit_percentage'] = ($receipt_records['credit_total'] * 100) / $receipt_records['cash_total'] - 15;
        } else if ($receipt_records['cash_total'] < $receipt_records['credit_total']) {
            $receipt_records['credit_percentage'] = 100 - 15;
            $receipt_records['cash_percentage'] = ($receipt_records['cash_total'] * 100) / $receipt_records['credit_total'] - 15;
        }

        $receipt_records['cash_total'] = number_format(($receipt_records['cash_total'] / 100), 2);
        $receipt_records['credit_total'] = number_format(($receipt_records['credit_total'] / 100), 2);

        if ($receipt_records['cash_percentage'] >= 100) {
            $receipt_records['cash_percentage'] = 100;
        } else if ($receipt_records['cash_percentage'] <= 0) {
            $receipt_records['cash_percentage'] = 0;
        }
        if ($receipt_records['credit_percentage'] >= 100) {
            $receipt_records['credit_percentage'] = 100;
        } else if ($receipt_records['credit_percentage'] <= 0) {
            $receipt_records['credit_percentage'] = 0;
        }

        $receipt_records['membership_percentage'] = ($receipt_records['membership_total'] / $total) * 100 - 15;
        $receipt_records['membership_percentage'] = $receipt_records['membership_percentage'] < 15 ? 0 : $receipt_records['membership_percentage'];

        $receipt_records['voucher_percentage'] = ($receipt_records['voucher_total'] / $total) * 100 - 15;
        $receipt_records['voucher_percentage'] = $receipt_records['voucher_percentage'] < 15 ? 0 : $receipt_records['voucher_percentage'];

        if ($ajax) {
            return response()->json($receipt_records);
        }

        $approved_merchant = company::find($merchant_id);
        $userApprovedDate = date("Y-m-d", strtotime($approved_merchant->approved_at)) ?? date("Y-m-d");
        return view('analytics.cash_payment_mode',
            compact(
                'since',
                'locations',
                'each_payment',
                'branch_location',
                'userApprovedDate',
                'receipt_records'

            ));
    }


    function showCashPaymentModeFiltered(Request $request)
    {
        $id = Auth::user()->id;
        $user_roles = usersrole::where('user_id', $id)->get();

        $is_king = \App\Models\Company::where('owner_user_id',
            Auth::user()->id)->first();

        if ($is_king != null) {
            $is_king = true;

        } else {
            $is_king = false;
        }
        $locationId = -1;
        $from = 'all';
        $to = 'all';
        if (!empty($request->type)) {
            if ($request->type == 'location') {
                $locationId = $request->locationId;
            }
            if ($request->type == 'date') {
                $from = $request->from;
                $to = $request->to;
            }
        }
        $since = \Carbon\Carbon::now();
        $locations = [];

        $receipt_records['cash_total'] = 0;
        $receipt_records['credit_total'] = 0;
        $receipt_records['grand_total'] = 0;
        if ($locationId != -1) {
            $terminalId = opos_locationterminal::where('location_id', $locationId)->first()['terminal_id'];
            $receipts = opos_receipt::where('terminal_id', $terminalId)->get();
            foreach ($receipts as $receipt) {
                $receipt_details = opos_receiptdetails::where('receipt_id', $receipt->id)->get();
                foreach ($receipt_details as $receipt_detail) {
                    $receipt_detail->cash = $receipt_detail->cash_received - $receipt_detail->change;
                    $receipt_records['cash_total'] += $receipt_detail->cash;
                    $receipt_records['credit_total'] += $receipt_detail->creditcard;
                    $receipt_records['grand_total'] += $receipt_detail->total;
                }
            }
        } else if ($from != 'all' && $to != 'all') {
            $receipt_details = opos_receiptdetails::all()->whereBetween('created_at', [$from, $to]);
            foreach ($receipt_details as $receipt_detail) {
                $receipt_detail->cash = $receipt_detail->cash_received - $receipt_detail->change;
                $receipt_records['cash_total'] += $receipt_detail->cash;
                $receipt_records['credit_total'] += $receipt_detail->creditcard;
                $receipt_records['grand_total'] += $receipt_detail->total;
            }
        }

        if ($from == 'all' && $to == 'all' && $locationId == -1 && $request->type == "all") {
            $receipt_details = opos_receiptdetails::all();
            //sum up credit here
            foreach ($receipt_details as $receipt_detail) {
                $receipt_detail->cash = $receipt_detail->cash_received - $receipt_detail->change;
                $receipt_records['cash_total'] += $receipt_detail->cash;
                $receipt_records['credit_total'] += $receipt_detail->creditcard;
                $receipt_records['grand_total'] += $receipt_detail->total;
            }
        } else if ($from == 'all' && $to == 'all' && $locationId == -1 && $request->type == "cash") {
            $receipt_details = opos_receiptdetails::all();
            foreach ($receipt_details as $receipt_detail) {
                $receipt_detail->cash = $receipt_detail->cash_received - $receipt_detail->change;
                $receipt_records['cash_total'] += $receipt_detail->cash;
                $receipt_records['credit_total'] += $receipt_detail->creditcard;
                $receipt_records['grand_total'] += $receipt_detail->total;
            }
        }

        $receipt_records['cash_percentage'] = 0;
        $receipt_records['credit_percentage'] = 0;
        if ($receipt_records['cash_total'] > $receipt_records['credit_total']) {
            $receipt_records['cash_percentage'] = 100 - 15;
            $receipt_records['credit_percentage'] = ($receipt_records['credit_total'] * 100) / $receipt_records['cash_total'] - 15;
        } else if ($receipt_records['cash_total'] < $receipt_records['credit_total']) {
            $receipt_records['credit_percentage'] = 100 - 15;
            $receipt_records['cash_percentage'] = ($receipt_records['cash_total'] * 100) / $receipt_records['credit_total'] - 15;
        }
        $receipt_records['cash_total'] = number_format(($receipt_records['cash_total'] / 100), 2);
        $receipt_records['credit_total'] = number_format(($receipt_records['credit_total'] / 100), 2);
        if ($receipt_records['cash_percentage'] >= 100) {
            $receipt_records['cash_percentage'] = 100;
        } else if ($receipt_records['cash_percentage'] <= 0) {
            $receipt_records['cash_percentage'] = 5;
        }
        if ($receipt_records['credit_percentage'] >= 100) {
            $receipt_records['credit_percentage'] = 100;
        } else if ($receipt_records['credit_percentage'] <= 0) {
            $receipt_records['credit_percentage'] = 5;
        }
        return json_encode($receipt_records);
    }

    function showCashHourlySalesView(Request $request)
    {

        $user_data = new UserData();
        $merchant_id = $user_data->company_id();

        $branch_location = [];

        $get_location = $this->get_location();
        foreach ($get_location as $key => $val) {
            $$key = $val;
            $location_id = array_column($branch_location, 'id');
            foreach ($val as $location) {
                if (!in_array($location->id, $location_id)) {
                    $branch_location = array_merge($branch_location, [$location]);
                }
            }
        }


        $since = \Carbon\Carbon::now();

        if (isset($request->filterByDate)) {
            $date = $request->filterByDate;
        } else {
            $date = date('Y-m-d');
        }

        if (isset($request->locationId)) {
            $where_location = ' AND  opos_locationterminal.location_id = ' . $request->locationId;
        } else {
            $where_location = '';
        }

        $from_date = $date . ' 00:00:00';
        $to_date = $date . ' 23:59:59';

        $filter_data = DB::select(
            "SELECT 
				HOUR(opos_itemdetails.created_at) AS FromHour,
				(SUM(opos_itemdetails.amount)/100) AS total,
				count(opos_itemdetails.id) AS count
			FROM 
				opos_itemdetails
			LEFT JOIN  
				opos_receiptproduct  ON ( opos_itemdetails.receiptproduct_id = opos_receiptproduct.id) 
			LEFT JOIN 
				opos_receipt ON (opos_receiptproduct.receipt_id = opos_receipt.id )
			LEFT JOIN 
				opos_terminal ON (opos_receipt.terminal_id = opos_terminal.id )
			LEFT JOIN 
				opos_locationterminal ON (opos_terminal.id = opos_locationterminal.terminal_id )
			WHERE 
				opos_itemdetails.created_at BETWEEN :froms AND :to
				AND opos_receipt.company_id = :company_id
			$where_location
			GROUP BY
				HOUR(opos_itemdetails.created_at)", [
                'froms' => $from_date, 'to' => $to_date, 'company_id' => $merchant_id]
        );

        $ar = array();
        $hour = array_pad($ar, 24, 0);

        foreach ($hour as $keyh => $valueh) {
            foreach ($filter_data as $keya => $valuea) {
                if ($keyh == $valuea->FromHour) {
                    $hour[$keyh] = $valuea->total;
                }
            }
        }

        $approved_merchant = company::find($merchant_id);
        $userApprovedDate = date("Y-m-d", strtotime($approved_merchant->approved_at)) ?? date("Y-m-d");

        if (isset($request->filterByParams)) {
            return response()->json($hour, 200);
        } else {

            return view('analytics.cash_hourly_sales',
                compact(
                    'since',
                    'branch_location',
                    'hour',
                    'userApprovedDate'
                ));
        }
    }

    // Credits Term

    function showCreditProductSalesView()
    {

        $id = Auth::user()->id;
        $user_roles = usersrole::where('user_id', $id)->get();

        $is_king = \App\Models\Company::where('owner_user_id',
            Auth::user()->id)->first();

        if ($is_king != null) {
            $is_king = true;

        } else {
            $is_king = false;
        }

        $since = \Carbon\Carbon::now();
        $locations = [];

        return view('analytics.credit_productsales', compact(
            'user_roles',
            'is_king',
            'since',
            'locations'
        ));
    }

    function showCreditStaffSalesView()
    {

        $id = Auth::user()->id;
        $user_roles = usersrole::where('user_id', $id)->get();

        $is_king = \App\Models\Company::where('owner_user_id',
            Auth::user()->id)->first();

        if ($is_king != null) {
            $is_king = true;

        } else {
            $is_king = false;
        }

        $since = \Carbon\Carbon::now();
        $locations = [];

        return view('analytics.credit_staffsales', compact(
            'user_roles',
            'is_king',
            'since',
            'locations'
        ));
    }


    function showCashSalesMothlyStatementView()
    {
        $since = \Carbon\Carbon::now();
        $locations = [];
        return view('analytics.cash_salesmonthly_statement', compact('since', 'locations'
        ));
    }


    public function dummy()
    {
        $data = opos_eoddetails::select('total_amount', 'sst')->get();
        return Datatables::of($data)
            ->addIndexColumn()
            ->addColumn('amount', function ($data) {
                return '<p class="pull-right" style=" margin: 0;">' . number_format($data['total_amount'] / 100, 2) . '</p>';
            })
            ->addColumn('sst', function ($data) {
                return '<p class="os- linkcolor loyaltyOutput pull-right" style=" margin: 0;">' . number_format($data['sst'] / 100, 2) . '</p>';
            })
            ->escapeColumns([])
            ->make(true);
    }


    // Operative View
    function showOperativeViewMerchantSalesView(Request $request)
    {

        $user_data = new UserData();

        $company_id = $user_data->company_id();
        $owner_merchant = merchant::where('company_id', $company_id)->first();
        $owner_merchant_id = $owner_merchant->id;
        $since = \Carbon\Carbon::now();
        $locations = [];

        if (isset($request->filterByDateFrom)) {
            $from_date = $request->filterByDateFrom;
            $to_date = $request->filterByDateTo;
        } else {

            $from_date = $since->format('Y-m-d');
            $to_date = $since->format('Y-m-d');
        }

        //User Approved Date
        $userApprovedDate = DB::select('
			SELECT
				c.approved_at
			FROM
				company c
			WHERE
				c.id = ' . $company_id . '
		');
        $userApprovedDate = \Carbon\Carbon::parse($userApprovedDate[0]->approved_at)->format('d F Y');
        $res_mechant_sales = DB::table('foodcourtmerchant')
            ->select('foodcourtmerchant.tenant_merchant_id', DB::raw("SUM(opos_receiptdetails.total) as merchant_total"), 'company.name')
            ->join('foodcourt', 'foodcourt.id', '=', 'foodcourtmerchant.foodcourt_id')
            ->join('merchant', 'foodcourtmerchant.tenant_merchant_id', '=', 'merchant.id')
            ->join('company', 'merchant.company_id', '=', 'company.id')
            ->join('foodcourtmerchantterminal', 'foodcourtmerchantterminal.foodcourtmerchant_id', '=', 'foodcourtmerchant.id')
            ->join('opos_receipt', 'opos_receipt.terminal_id', '=', 'foodcourtmerchantterminal.terminal_id')
            ->join('opos_receiptdetails', 'opos_receiptdetails.receipt_id', '=', 'opos_receipt.id')
            ->where('foodcourt.owner_merchant_id', $owner_merchant_id)
            ->whereBetween('opos_receipt.created_at', [$from_date, $to_date])
            ->groupBy('foodcourtmerchant.tenant_merchant_id')
            ->orderBy('merchant_total', 'DESC')
            ->get();

        $maxValue = 0;
        foreach ($res_mechant_sales as $key => $item) {
            if ($maxValue < $item->merchant_total) {
                $maxValue = $item->merchant_total;
            }
        }

        $mechant_sales = json_decode(json_encode($res_mechant_sales->toArray()), true);
        foreach ($mechant_sales as $key => $item) {
            $mechant_sales[$key]['percent'] = $item['merchant_total'] * 100 / $maxValue;
        }

        if (isset($request->filterByParams)) {
            return response()->json($mechant_sales, 200);
        }

        return view('analytics.ov_merchantsales',
            compact(
                'since',
                'locations',
                'userApprovedDate',
                'mechant_sales'
            ));
    }

    function showOperativeViewOverallProductSalesView(Request $request)
    {


        $user_data = new UserData();
        $company_id = $user_data->company_id();
        $owner_merchant = merchant::where('company_id', $company_id)->first();
        $owner_merchant_id = $owner_merchant->id;
        $since = \Carbon\Carbon::now();
        $locations = [];

        if (isset($request->filterByDateFrom)) {
            $from_date = $request->filterByDateFrom;
            $to_date = $request->filterByDateTo;
        } else {
            $from_date = $since->format('Y-m-d');
            $to_date = $since->format('Y-m-d');
        }


        //User Approved Date
        $userApprovedDate = DB::select('
			SELECT
				c.approved_at
			FROM
				company c
			WHERE
				c.id = ' . $company_id . '
		');
        $userApprovedDate = \Carbon\Carbon::parse($userApprovedDate[0]->approved_at)->format('d F Y');

        $res_mechant_sales = DB::table('foodcourtmerchant')
            ->select('foodcourtmerchant.tenant_merchant_id', DB::raw("SUM(opos_receiptdetails.total) as merchant_total"), 'company.name')
            ->join('foodcourt', 'foodcourt.id', '=', 'foodcourtmerchant.foodcourt_id')
            ->join('merchant', 'foodcourtmerchant.tenant_merchant_id', '=', 'merchant.id')
            ->join('company', 'merchant.company_id', '=', 'company.id')
            ->join('foodcourtmerchantterminal', 'foodcourtmerchantterminal.foodcourtmerchant_id', '=', 'foodcourtmerchant.id')
            ->join('opos_receipt', 'opos_receipt.terminal_id', '=', 'foodcourtmerchantterminal.terminal_id')
            ->join('opos_receiptdetails', 'opos_receiptdetails.receipt_id', '=', 'opos_receipt.id')
            ->where('foodcourt.owner_merchant_id', $owner_merchant_id)
            ->whereBetween('opos_receipt.created_at', [$from_date, $to_date])
            ->groupBy('foodcourt.id')
            ->orderBy('merchant_total', 'DESC')
            ->get();

        $maxValue = 0;
        foreach ($res_mechant_sales as $key => $item) {
            if ($maxValue < $item->merchant_total) {
                $maxValue = $item->merchant_total;
            }
        }

        $mechant_sales = json_decode(json_encode($res_mechant_sales->toArray()), true);
        foreach ($mechant_sales as $key => $item) {
            $mechant_sales[$key]['percent'] = $item['merchant_total'] * 100 / $maxValue;
        }

        if (isset($request->filterByParams)) {
            return response()->json($mechant_sales, 200);
        }

        return view('analytics.ov_overall_product_sales',
            compact(
                'since',
                'locations',
                'userApprovedDate',
                'mechant_sales'
            ));
    }


    function showOperativeViewFoodCourtHourlySalesView(Request $request)
    {

        $user_data = new UserData();
        $company_id = $user_data->company_id();
        $owner_merchant = merchant::where('company_id', $company_id)->first();
        $owner_merchant_id = $owner_merchant->id;

        $since = \Carbon\Carbon::now();
        $locations = [];

        if (isset($request->filterByDate)) {
            $date = $request->filterByDate;
        } else {
            $date = date('Y-m-d');
        }

        $from_date = $date . ' 00:00:00';
        $to_date = $date . ' 23:59:59';

        $filter_data = DB::table('foodcourtmerchant')
            ->select(
                DB::raw('HOUR(opos_receiptdetails.created_at) AS FromHour'),
                DB::raw("(SUM(opos_receiptdetails.item_amount)/100) AS total"),
                DB::raw('count(opos_receiptdetails.id) AS count'))
            ->join('foodcourt', 'foodcourt.id', '=', 'foodcourtmerchant.foodcourt_id')
            ->join('merchant', 'foodcourtmerchant.tenant_merchant_id', '=', 'merchant.id')
            ->join('company', 'merchant.company_id', '=', 'company.id')
            ->join('foodcourtmerchantterminal', 'foodcourtmerchantterminal.foodcourtmerchant_id', '=', 'foodcourtmerchant.id')
            ->join('opos_receipt', 'opos_receipt.terminal_id', '=', 'foodcourtmerchantterminal.terminal_id')
            ->join('opos_receiptdetails', 'opos_receiptdetails.receipt_id', '=', 'opos_receipt.id')
            ->where('foodcourt.owner_merchant_id', $owner_merchant_id)
            ->whereBetween('opos_receiptdetails.created_at', [$from_date, $to_date])
            ->groupBy('FromHour')
            ->get();

        $ar = array();
        $hour = array_pad($ar, 24, 0);

        foreach ($hour as $keyh => $valueh) {
            foreach ($filter_data as $keya => $valuea) {
                if ($keyh == $valuea->FromHour) {
                    $hour[$keyh] = $valuea->total;
                }
            }
        }

        //User Approved Date
        $userApprovedDate = DB::select('
			SELECT
				c.approved_at
			FROM
				company c
			WHERE
				c.id = ' . $company_id . '
		');
        $userApprovedDate = \Carbon\Carbon::parse($userApprovedDate[0]->approved_at)->format('d F Y');

        if (isset($request->filterByParams)) {
            return response()->json($hour, 200);
        } else {
            return view('analytics.ov_foodcourt_hourly_sales',
                compact(
                    'since',
                    'locations',
                    'hour',
                    'userApprovedDate'
                ));
        }
    }


    function showStockProductSalesQtyView(Request $request)
    {

        $user_data = new UserData();
        $merchant_id = $user_data->company_id();

        $inventory = new Inventory();

        $landing = !false;
        $since = \Carbon\Carbon::now();
        $branch_location = [];

        $check_qty_type = 'all';

        $get_location = $this->get_location();
        foreach ($get_location as $key => $val) {
            $$key = $val;
            $location_id = array_column($branch_location, 'id');
            foreach ($val as $location) {
                if (!in_array($location->id, $location_id)) {
                    $branch_location = array_merge($branch_location, [$location]);
                }
            }
        }

        switch ($request->segment) {
            case 'direct':
                $loc_id = array_map(function ($f) {
                    return $f->id;
                }, $direct_location);
                break;
            case 'franchise':
                $loc_id = array_map(function ($f) {
                    return $f->id;
                }, $franchiseLocations);

                break;
            case 'food':
                $loc_id = array_merge($foodcourt_location, $tenant_foodcourt);

                $loc_id = array_map(function ($z) {
                    return $z->id;
                }, $loc_id);

                break;
            case 'all':
            default:
                if (!empty($request->loc_id)) {
                    $loc_id = $request->loc_id;
                }
                break;
        }

        $all_data = DB::table('stockreport')->
        join('stockreportproduct', 'stockreportproduct.stockreport_id', '=', 'stockreport.id')->
        join('product', 'product.id', '=', 'stockreportproduct.product_id')->
        join('staff', 'staff.user_id', '=', 'stockreport.creator_user_id')->
        where("staff.company_id", $merchant_id)->
        whereNotIn('ptype', ['oilgas'])->
        select("stockreportproduct.quantity", "product.name", "product.id",
            'stockreport.location_id',
            "stockreport.created_at", 'product.thumbnail_1')->
        get();


        //getting segment
        if (!empty($request->segment)) {
            if (isset($loc_id)) {
                $all_data = $all_data->whereIn('location_id', $loc_id);
                $check_qty_type = "segment";
                $landing = false;
            }
        }

        if (!empty($request->loc_id) && $request->loc_id != 'all' && (empty($request->segment) || $request->segment == 'all')) {
            $all_data = $all_data->where('location_id', $request->loc_id);
            $check_qty_type = "location";
            $landing = false;
        }

        if (!empty($request->from_date_all) && !empty($request->to_date_all)) {

            $dateTimeFrom = strtotime($request->from_date_all);
            $dateTimeTo = strtotime($request->to_date_all);

            $all_data = $all_data->filter(function ($z) use ($dateTimeFrom, $dateTimeTo) {
                if (strtotime($z->created_at) >= $dateTimeFrom && strtotime($z->created_at) <= $dateTimeTo) {
                    return true;
                } else {
                    return false;
                }
            });

            $landing = false;
        }

        if ($request->segment == 'all' || $request->loc_id == 'all') {
            $landing = false;
        }

        if ($request->has('button_filter')) {

            if ($request->button_filter == 'credit') {
                $all_data = collect();
            }

            $landing = false;
        }

        $result = collect();
        $each_product_quantity = $all_data->unique('id');

        $loc_id = $loc_id ?? false;
        $each_product_quantity->map(function ($z) use ($all_data, $result, $check_qty_type, $inventory, $loc_id) {

            if ($check_qty_type == 'all') {
                $z->T_amount = $inventory->check_quantity($z->id);
            } else if ($check_qty_type == "location") {
                $z->T_amount = $inventory->location_productqty($z->id, $loc_id);
            } else if ($check_qty_type == 'segment') {
                $z->T_amount = array_reduce($loc_id, function ($init, $loc) use ($z, $inventory) {
                    return $init + $inventory->location_productqty($z->id, $loc);
                });
            }

            if ($z->T_amount < 1) {
                return;
            }

            $result->push($z);
        });

        $result = $result->sortByDesc('T_amount')->values();

        if ($landing == false) {
            return response()->json($result);
        }

        $approved_merchant = DB::select('SELECT created_at FROM `company` 
			WHERE `id` =:id 
			AND created_at IS NOT NULL;', ['id' => $merchant_id]
        );

        $approved_merchant = company::find($merchant_id);
        $userApprovedDate = date("Y-m-d", strtotime($approved_merchant->approved_at)) ?? date("Y-m-d");

        return view('analytics.stocklevel', compact(
            'since',
            'each_product_quantity',
            'branch_location',
            'userApprovedDate'
        ));
    }

    function downloadStocklevelPdf(Request $request)
    {

        $user_data = new UserData();
        $merchant_id = $user_data->company_id();

        $inventory = new Inventory();

        $landing = !false;
        $since = \Carbon\Carbon::now();
        $branch_location = [];

        $check_qty_type = 'all';

        $get_location = $this->get_location();
        foreach ($get_location as $key => $val) {
            $$key = $val;
            $location_id = array_column($branch_location, 'id');
            foreach ($val as $location) {
                if (!in_array($location->id, $location_id)) {
                    $branch_location = array_merge($branch_location, [$location]);
                }
            }
        }

        switch ($request->segment) {
            case 'direct':
                $loc_id = array_map(function ($f) {
                    return $f->id;
                }, $direct_location);
                $segmentName="Direct Segment";
                break;
            case 'franchise':
                $loc_id = array_map(function ($f) {
                    return $f->id;
                }, $franchiseLocations);
                $segmentName="Franchise Segment";
                break;
            case 'food':
                $loc_id = array_merge($foodcourt_location, $tenant_foodcourt);

                $loc_id = array_map(function ($z) {
                    return $z->id;
                }, $loc_id);
                $segmentName="Food Court Segment";
                break;
            case 'all':
            default:
                if (!empty($request->loc_id)) {
                    $loc_id = $request->loc_id;
                }
            $segmentName="All";
                break;
        }

        $all_data = DB::table('stockreport')->
        join('stockreportproduct', 'stockreportproduct.stockreport_id', '=', 'stockreport.id')->
        join('product', 'product.id', '=', 'stockreportproduct.product_id')->
        join('staff', 'staff.user_id', '=', 'stockreport.creator_user_id')->
        where("staff.company_id", $merchant_id)->
        whereNotIn('ptype', ['oilgas'])->
        select("stockreportproduct.quantity", "product.name", "product.id",
            'stockreport.location_id',
            "stockreport.created_at", 'product.thumbnail_1','product.systemid')->
        get();


        //getting segment
        if (!empty($request->segment)) {
            if (isset($loc_id)) {
                $all_data = $all_data->whereIn('location_id', $loc_id);
                $check_qty_type = "segment";
                $landing = false;
            }
        }

        if (!empty($request->loc_id) && $request->loc_id != 'all' && (empty($request->segment) || $request->segment == 'all')) {
            $all_data = $all_data->where('location_id', $request->loc_id);
            $check_qty_type = "location";
            $landing = false;
        }

        if (!empty($request->from_date_all) && !empty($request->to_date_all)) {

            $dateTimeFrom = strtotime($request->from_date_all);
            $dateTimeTo = strtotime($request->to_date_all);

            $all_data = $all_data->filter(function ($z) use ($dateTimeFrom, $dateTimeTo) {
                if (strtotime($z->created_at) >= $dateTimeFrom && strtotime($z->created_at) <= $dateTimeTo) {
                    return true;
                } else {
                    return false;
                }
            });

            $landing = false;
        }

        if ($request->segment == 'all' || $request->loc_id == 'all') {
            $landing = false;
        }

        if ($request->has('button_filter')) {

            if ($request->button_filter == 'credit') {
                $all_data = collect();
            }

            $landing = false;
        }

        $result = collect();
        $each_product_quantity = $all_data->unique('id');

        $loc_id = $loc_id ?? false;
        $each_product_quantity->map(function ($z) use ($all_data, $result, $check_qty_type, $inventory, $loc_id) {

            if ($check_qty_type == 'all') {
                $z->T_amount = $inventory->check_quantity($z->id);
            } else if ($check_qty_type == "location") {
                $z->T_amount = $inventory->location_productqty($z->id, $loc_id);
            } else if ($check_qty_type == 'segment') {
                $z->T_amount = array_reduce($loc_id, function ($init, $loc) use ($z, $inventory) {
                    return $init + $inventory->location_productqty($z->id, $loc);
                });
            }

            if ($z->T_amount < 1) {
                return;
            }

            $result->push($z);
        });

        $product_detail = $result->sortByDesc('T_amount')->values();

//        if ($landing == false) {
//            return response()->json($result);
//        }

        $approved_merchant = DB::select('SELECT created_at FROM `company` 
			WHERE `id` =:id 
			AND created_at IS NOT NULL;', ['id' => $merchant_id]
        );

        $approved_merchant = company::find($merchant_id);
        $userApprovedDate = date("Y-m-d", strtotime($approved_merchant->approved_at)) ?? date("Y-m-d");
        $requestValue = $request->all();
        if (isset($request->loc_id)){

        if ($request->loc_id != 'all') {
            $collection = collect($branch_location);
            $branch = $collection->where('id', $request->loc_id)->first();
            $branchName = @$branch->branch;
        } else {
            $branchName = $request->loc_id;
        }

        }else{
            $branchName ="All";
        }

        $pdf = PDF::setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true])
            ->loadView("analytics.stocklevel_pdf", compact( 'product_detail', 'requestValue','segmentName','branchName'
            ));

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
        return $pdf->download('stock_level.pdf');

    }
    public function showCashSalesMonthlyStatementView(Request $request)
    {
        $user_data = new UserData();
        $merchant_id = $user_data->company_id();

        $branch_location = [];

        $get_location = $this->get_location();
        foreach ($get_location as $key => $val) {
            $$key = $val;
            $location_id = array_column($branch_location, 'id');
            foreach ($val as $location) {
                if (!in_array($location->id, $location_id)) {
                    $branch_location = array_merge($branch_location, [$location]);
                }
            }
        }

        //branch location
        switch ($request->segment) {
            case 'direct':
                $loc_id = array_map(function ($f) {
                    return $f->id;
                }, $direct_location);
                break;
            case 'franchise':
                $loc_id = array_map(function ($f) {
                    return $f->id;
                }, $franchiseLocations);

                break;
            case 'food':
                $loc_id = array_merge($foodcourt_location, $tenant_foodcourt);

                $loc_id = array_map(function ($z) {
                    return $z->id;
                }, $loc_id);

                break;
            case 'all':
            default:
                $loc_id = null;
                break;
        }

        $all_data = DB::table('opos_itemdetails')->
        join('opos_receiptproduct', 'opos_itemdetails.receiptproduct_id', '=', 'opos_receiptproduct.id')->
        join('opos_receipt', 'opos_receipt.id', '=', 'opos_receiptproduct.receipt_id')->
        join('opos_locationterminal', 'opos_locationterminal.terminal_id', '=', 'opos_receipt.terminal_id')->
        join('staff', 'opos_receipt.staff_user_id', '=', 'staff.user_id')->
        leftjoin('product', 'product.id', '=', 'opos_receiptproduct.product_id')->
        whereNull('opos_receiptproduct.promo_id')->
        where('opos_receipt.company_id', $merchant_id)->
        where([['product.id', '!=', 0]])->
        select(
            'opos_itemdetails.amount',
            'product.name', 'product.thumbnail_1',
            'product.id', 'product.prdsubcategory_id',
            'staff.company_id as merchant_id',
            'opos_locationterminal.location_id',
            'opos_receiptproduct.created_at'
        )->
        get();


        $all_data_promo = DB::table('opos_receiptproduct')->
        join('opos_receipt', 'opos_receipt.id', '=', 'opos_receiptproduct.receipt_id')->
        join('opos_locationterminal', 'opos_locationterminal.terminal_id', '=', 'opos_receipt.terminal_id')->
        join('staff', 'opos_receipt.staff_user_id', '=', 'staff.user_id')->
        //leftjoin('product','product.id', '=' ,'opos_receiptproduct.product_id')->
        whereNotNull('opos_receiptproduct.promo_id')->
        where('opos_receiptproduct.product_id', '0')->
        where('opos_receipt.company_id', $merchant_id)->
        select(
            'opos_receiptproduct.price as amount',
            'opos_receiptproduct.promo_id as id',
            'staff.company_id as merchant_id',
            'opos_locationterminal.location_id',
            'opos_receiptproduct.created_at'
        )->
        get();


        $promo_ids = $all_data_promo->pluck('id')->unique();


        $promo_details = DB::table('opos_promo')->
        whereIn('id', $promo_ids)->
        select('opos_promo.id', 'opos_promo.title as name')->
        get()->unique();

        $subCatIds = $all_data->pluck('prdsubcategory_id')->unique();

        $subCats = DB::table('prd_subcategory')->
        whereIn('id', $subCatIds)->
        whereNull('deleted_at')->
        get();


        $result = collect();

        if ($request->has('from_date_all')) {

            $from_date_all = strtotime($request->from_date_all);
            $month = date('m', $from_date_all);
            $year = date('Y', $from_date_all);

        } else {
            $month = date('m');
            $year = date('Y');
        }

        $totalDays = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        for ($x = 1; $x <= $totalDays; $x++) {

            $date = "$year-$month-$x";

            $row = collect();

            $total = 0;

            $dateTimeFrom = strtotime("$date 00:00:00");
            $dateTimeTo = strtotime("$date 23:59:59");

            $sum = $all_data->filter(function ($a) use ($dateTimeFrom, $dateTimeTo) {
                if (strtotime($a->created_at) >= $dateTimeFrom && strtotime($a->created_at) <= $dateTimeTo
                ) {
                    return true;
                } else {
                    return false;
                }
            });


            $sum_promo = $all_data_promo->filter(function ($a) use ($dateTimeFrom, $dateTimeTo) {

                if (strtotime($a->created_at) >= $dateTimeFrom && strtotime($a->created_at) <= $dateTimeTo
                ) {
                    return true;
                } else {
                    return false;
                }
            });

            if (!empty($request->loc_id) && $request->loc_id != 'all') {
                $sum = $sum->where('location_id', $request->loc_id);
                $sum_promo = $sum_promo->where('location_id', $request->loc_id);
            }

            //getting segment
            if (!empty($request->segment)) {
                if (isset($loc_id)) {
                    $sum = $sum->whereIn('location_id', $loc_id);
                    $sum_promo = $sum_promo->whereIn('location_id', $loc_id);
                }
            }


            $t = $sum->sum('amount');
            $t += $sum_promo->sum('amount');

            $row->push([
                "date" => date("dMy", strtotime($date)),
                "total" => number_format($t / 100, 2)
            ]);

            $promo_details->map(function ($z) use ($row, $sum_promo) {

                $sumByPromo = $sum_promo->where('id', $z->id);
                $cell['subcat_id'] = 0;
                $cell['name'] = $z->name;
                $cell['amount'] = number_format($sumByPromo->sum('amount') / 100, 2);
                $row->push($cell);
            });

            $subCats->map(function ($z) use ($row, $sum) {

                $sumByCat = $sum->where('prdsubcategory_id', $z->id);

                $cell['subcat_id'] = $z->id;
                $cell['name'] = $z->name;
                $cell['amount'] = number_format($sumByCat->sum('amount') / 100, 2);
                $row->push($cell);
            });


            $result->push($row);
        }

        if ($request->has('ajax')) {
            return $result;
        }

        $months = [];
        for ($x = 1; $x <= 12; $x++) {
            $date = "2020-$x-$x";
            $months[] = date('M', strtotime($date));
        }
        $subCats = $promo_details->merge($subCats);

        $approved_merchant = company::find($merchant_id);
        $userApprovedDate = date("Y-m-d", strtotime($approved_merchant->approved_at)) ?? date("Y-m-d");

        return view('analytics.cash_salesmonthly_statement', compact('branch_location', 'subCats', 'months', 'userApprovedDate'));
    }

    public function monthlyProcuredView(Request $request)
    {

        $user_data = new UserData();
        $merchant_id = $user_data->company_id();

        $logged_in_user_id = Auth::user()->id;

        $landing = true;
        $since = \Carbon\Carbon::now();
        $locations = [];
        $branch_location = [];

        $get_location = $this->get_location();
        foreach ($get_location as $key => $val) {
            $$key = $val;
            $location_id = array_column($branch_location, 'id');
            foreach ($val as $location) {
                if (!in_array($location->id, $location_id)) {
                    $branch_location = array_merge($branch_location, [$location]);
                }
            }
        }

        //branch location
        switch ($request->segment) {
            case 'direct':
                $loc_id = array_map(function ($f) {
                    return $f->id;
                }, $direct_location);
                break;
            case 'franchise':
                $loc_id = array_map(function ($f) {
                    return $f->id;
                }, $franchiseLocations);

                break;
            case 'food':
                $loc_id = array_merge($foodcourt_location, $tenant_foodcourt);

                $loc_id = array_map(function ($z) {
                    return $z->id;
                }, $loc_id);

                break;
            case 'all':
                break;
        }

        $all_data = collect();

        $credit_sales = DB::table('invoice')->
        join('invoiceproduct', 'invoiceproduct.invoice_id',
            '=', 'invoice.id')->
        join('purchaseorderdeliveryorder', 'purchaseorderdeliveryorder.id', 'invoice.deliveryorder_id')->
        select('invoiceproduct.product_name as name',
            'invoiceproduct.product_thumbnail as thumbnail_1',
            'invoiceproduct.product_id as id',
            'invoice.dealer_merchant_id as merchant_id', 'invoice.created_at',
            DB::RAW('(invoiceproduct.quantity * invoiceproduct.price) as amount')
        )->
        where('invoice.supplier_merchant_id', $merchant_id)->
        get();

        if ($request->has('button_filter')) {

            if ($request->button_filter == 'credit') {
                $all_data = $credit_sales;
            } else if ($request->button_filter == 'all') {
                $credit_sales->map(function ($f) use ($all_data) {
                    $all_data->push($f);
                });
            }

            $landing = false;
        }

        //getting segment
        if (!empty($request->segment)) {
            if (isset($loc_id)) {
                $all_data = $all_data->whereIn('location_id', $loc_id);
                $landing = false;
            }
        }

        if (!empty($request->loc_id) && $request->loc_id != 'all') {
            $all_data = $all_data->where('location_id', $request->loc_id);
            $landing = false;
        }

        if (!empty($request->from_date_all)) {

            $dateTimeFrom = strtotime($request->from_date_all);
            $totalDays = cal_days_in_month(CAL_GREGORIAN, date('m', $dateTimeFrom), date('Y', $dateTimeFrom));
            $dateTimeTo = strtotime(date('Y-m-' . $totalDays . ' 23:59:59', $dateTimeFrom));

            $all_data = $all_data->filter(function ($z) use ($dateTimeFrom, $dateTimeTo) {
                if (strtotime($z->created_at) >= $dateTimeFrom && strtotime($z->created_at) <= $dateTimeTo) {
                    return true;
                } else {
                    return false;
                }
            });

            $landing = false;
        }

        if ($request->segment == 'all' || $request->loc_id == 'all') {
            $landing = false;
        }

        if ($landing == true) {
            $all_data = $all_data->whereBetween('created_at', [date('Y-m-d 00:00:00'), date('Y-m-d 23:59:59')]);
        }

        $result = collect();

        $each_Product_amount = $all_data->unique('id');
        $each_Product_amount->map(function ($z) use ($all_data, $result) {
            $z->T_amount = $all_data->where('id', $z->id)->sum('amount');
            $result->push($z);
        });

        $result = $result->sortByDesc('T_amount')->values();

        if ($landing == false) {
            return response()->json($result);
        }

        $approved_merchant = company::find($merchant_id);
        $userApprovedDate = date("Y-m-d", strtotime($approved_merchant->approved_at)) ?? date("Y-m-d");


        $months = [];
        for ($x = 1; $x <= 12; $x++) {
            $date = "2020-$x-$x";
            $months[] = date('M', strtotime($date));
        }

        return view('analytics.monthly_procured', compact(
            'since',
            'locations',
            'each_Product_amount',
            'branch_location',
            'approved_merchant',
            'userApprovedDate', 'months'
        ));


    }

    /*
    // product sales pdf start
    */
    public function productSalesPdf(Request $request)
    {
        $date_from = $request->from_date;
        $date_to = $request->to_date;
        $range_name = $request->range_name;
        $branch_name = $request->branch_name;
        $branch_id = $request->branch_id;

        //for change range name ...
        if ($range_name == '') {
            $range_name = date('dMy', strtotime($date_to)) . ' to ' . date('dMy', strtotime($date_to));
        } else {
            if ($range_name == 'since') {
                $range_name = ucfirst($range_name) . ' ' . date('dMy', strtotime($date_from));
            } else {
                $range_name = strtoupper($range_name) . ' ' . date('dMy', strtotime($date_from));
            }
        }
        //dynamic title name
        $title_name = $request->title_name;

        //ligged user 
        $logged_in_user_id = Auth::user()->id;


        if ($branch_id != -1) {
            //if have branch info
            $each_amount_date_range = DB::select(
                'SELECT 
				SUM(opos_itemdetails.amount) AS T_amount,
				product.name,
				product.systemid
					

				FROM 
				opos_itemdetails,
				opos_receiptproduct,
				product,
				merchantproduct,
				company,
				opos_receipt,
				location,
				opos_locationterminal
				WHERE
				 product.id = opos_receiptproduct.product_id
				AND merchantproduct.product_id = opos_receiptproduct.product_id
				AND company.owner_user_id=:id
				AND	location.id=:loc_id
				AND location.id = opos_locationterminal.location_id
				AND opos_locationterminal.terminal_id=opos_receipt.terminal_id
				AND opos_receipt.id = opos_receiptproduct.receipt_id
				AND opos_receiptproduct.created_at BETWEEN :froms AND :to

				GROUP BY 
				product.name 
				ORDER BY 
				(T_amount) DESC;', [
                    'id' => $logged_in_user_id,
                    'loc_id' => $branch_id,
                    'froms' => $date_from,
                    'to' => $date_to
                ]
            );

        } else {
            //have not branch info
            $each_amount_date_range = DB::select(
                'SELECT SUM(opos_itemdetails.amount) AS T_amount,product.name,product.systemid FROM 
				opos_itemdetails,opos_receiptproduct,product,merchantproduct,company
				WHERE
				product.id = opos_receiptproduct.product_id
				AND merchantproduct.product_id = opos_receiptproduct.product_id
				AND company.owner_user_id=:id
				AND company.id=merchantproduct.merchant_id
				AND opos_receiptproduct.created_at BETWEEN :froms AND :to
				GROUP BY product.id ORDER BY 
				(T_amount) DESC;', ['id' => $logged_in_user_id, 'froms' => $date_from, 'to' => $date_to]
            );
        }

        //company name .....
        $company_data = Company::where('owner_user_id', $logged_in_user_id)->first();
        if (!empty($company_data)) {
            $company_name = $company_data->name;
        }


        //send data to view PDF
        $contents = view("analytics.cash_productsales_pdf",
            compact('title_name', 'range_name', 'each_amount_date_range', 'company_name', 'branch_name'))->render();

        // initialization Mpdf and object create
        $mpdf = new Mpdf([
            'utf-8', // mode - default ''
            'A4', // format - A4, for example, default ''
            10, // font size - default 0
            'dejavusans', // default font family
            10, // margin_left
            10, // margin right
            10, // margin top
            15, // margin bottom
            10, // margin header
            9, // margin footer
            'P'
        ]);

        $mpdf->SetDefaultBodyCSS('color', '#000');
        $mpdf->SetTitle("ocosystem");
        $mpdf->SetSubject("Subject");
        $mpdf->SetAuthor("Company Name");
        $mpdf->autoScriptToLang = true;
        $mpdf->baseScript = 1;
        $mpdf->autoVietnamese = true;
        $mpdf->autoArabic = true;
        $mpdf->autoLangToFont = true;
        $mpdf->SetDisplayMode('fullwidth');
        $mpdf->setFooter('{PAGENO} / {nb}');
        $mpdf->setAutoTopMargin = 'stretch';
        $mpdf->setAutoBottomMargin = 'stretch';
        $stylesheet = file_get_contents('css/appviewPDF.css');
        $mpdf->WriteHTML($stylesheet, 1);
        $mpdf->WriteHTML($contents, 2);
        $mpdf->defaultfooterfontsize = 10;
        $mpdf->defaultfooterfontstyle = 'B';
        $mpdf->defaultfooterline = 0;
        $mpdf->SetCompression(true);
        $filename = date("Y-m-d_his") . '_download.pdf';
        $path = "pdf/analytics/";
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
            $myfile = fopen($path . "/index.html", "w");
            fclose($myfile);
        }

        $mpdf->Output($path . $filename, 'F');

        $data = ['responseCode' => 1, 'file_name' => $filename];
        return response()->json($data);
    }
    //end product sales pdf

    /*
     product sales qty pdf start
    */
    function productSalesQtyPdf(Request $request)
    {

        $date_from = $request->from_date;
        $date_to = $request->to_date;
        $range_name = $request->range_name;
        $branch_name = $request->branch_name;
        $branch_id = $request->branch_id;

        //for change range name ...
        if ($range_name == '') {
            $range_name = date('dMy', strtotime($date_to)) . ' to ' . date('dMy', strtotime($date_to));
        } else {
            if ($range_name == 'since') {
                $range_name = ucfirst($range_name) . ' ' . date('dMy', strtotime($date_from));
            } else {
                $range_name = strtoupper($range_name) . ' ' . date('dMy', strtotime($date_from));
            }
        }

        //dynamic title name
        $title_name = $request->title_name;

        //ligged user 
        $logged_in_user_id = Auth::user()->id;


        if ($branch_id != -1) {
            //if have branch info
            //indiviual location quantity
            $date_rage_product_quantity = DB::select(
                'SELECT 
				SUM(opos_itemdetails.amount) AS T_amount,
				product.name,
				product.systemid
					

				FROM 
				opos_itemdetails,
				opos_receiptproduct,
				product,
				merchantproduct,
				company,
				opos_receipt,
				location,
				opos_locationterminal
				WHERE
				 product.id = opos_receiptproduct.product_id
				AND merchantproduct.product_id = opos_receiptproduct.product_id
				AND company.owner_user_id=:id
				AND	location.id=:loc_id
				AND location.id = opos_locationterminal.location_id
				AND opos_locationterminal.terminal_id=opos_receipt.terminal_id
				AND opos_receipt.id = opos_receiptproduct.receipt_id
				AND opos_receiptproduct.created_at BETWEEN :froms AND :to

				GROUP BY 
				product.name 
				ORDER BY 
				(T_amount) DESC;', ['id' => $logged_in_user_id, 'loc_id' => $branch_id, 'froms' => $date_from]
            );

        } else {
            //have not branch info
            //date range quantity
            $date_rage_product_quantity = DB::select(
                'SELECT SUM(opos_receiptproduct.quantity) AS T_quantity,product.name,product.systemid 
			FROM 
				opos_receiptproduct,product,merchantproduct,company
			WHERE
			    product.id = opos_receiptproduct.product_id
			    AND merchantproduct.product_id = opos_receiptproduct.product_id
			    AND company.owner_user_id=:id
			    AND company.id=merchantproduct.merchant_id
			    AND opos_receiptproduct.created_at > :froms						   
			GROUP BY 
				product.id 
			ORDER BY 
				(T_quantity) DESC;', ['id' => $logged_in_user_id, 'froms' => $date_from]
            );
        }

        //company name .....
        $company_data = Company::where('owner_user_id', $logged_in_user_id)->first();
        if (!empty($company_data)) {
            $company_name = $company_data->name;
        }


        //send data to view PDF
        $contents = view("analytics.cash_productsales_pdf_qty",
            compact('title_name', 'range_name', 'date_rage_product_quantity', 'company_name', 'branch_name'))->render();

        // initialization Mpdf and object create
        $mpdf = new Mpdf([
            'utf-8', // mode - default ''
            'A4', // format - A4, for example, default ''
            10, // font size - default 0
            'dejavusans', // default font family
            10, // margin_left
            10, // margin right
            10, // margin top
            15, // margin bottom
            10, // margin header
            9, // margin footer
            'P'
        ]);

        $mpdf->SetDefaultBodyCSS('color', '#000');
        $mpdf->SetTitle("ocosystem");
        $mpdf->SetSubject("Subject");
        $mpdf->SetAuthor("Company Name");
        $mpdf->autoScriptToLang = true;
        $mpdf->baseScript = 1;
        $mpdf->autoVietnamese = true;
        $mpdf->autoArabic = true;
        $mpdf->autoLangToFont = true;
        $mpdf->SetDisplayMode('fullwidth');
        $mpdf->setFooter('{PAGENO} / {nb}');
        $mpdf->setAutoTopMargin = 'stretch';
        $mpdf->setAutoBottomMargin = 'stretch';
        $stylesheet = file_get_contents('css/appviewPDF.css');
        $mpdf->WriteHTML($stylesheet, 1);
        $mpdf->WriteHTML($contents, 2);
        $mpdf->defaultfooterfontsize = 10;
        $mpdf->defaultfooterfontstyle = 'B';
        $mpdf->defaultfooterline = 0;
        $mpdf->SetCompression(true);
        $filename = date("Y-m-d_his") . '_download.pdf';
        $path = "pdf/analytics/";
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
            $myfile = fopen($path . "/index.html", "w");
            fclose($myfile);
        }
        $mpdf->Output($path . $filename, 'F');

        $data = ['responseCode' => 1, 'file_name' => $filename];
        return response()->json($data);
    }


    public function productStockQtyPdf(Request $request)
    {

        $date_from = $request->from_date;
        $branch_name = $request->branch_name;
        $branch_id = $request->branch_id;
        //dynamic title name
        $title_name = $request->title_name;
        //ligged user 
        $logged_in_user_id = Auth::user()->id;

        if ($branch_id == "all" || empty($branch_id)) {
            //date range quantity
            $date_rage_product_quantity = DB::select(
                'SELECT SUM(opos_receiptproduct.quantity) AS T_quantity,product.name,product.systemid 
			FROM 
				opos_receiptproduct,product,merchantproduct,company
			WHERE
			    product.id = opos_receiptproduct.product_id
			    AND merchantproduct.product_id = opos_receiptproduct.product_id
			    AND company.owner_user_id=:id
			    AND company.id=merchantproduct.merchant_id
			    AND opos_receiptproduct.created_at > :froms						   
			GROUP BY 
				product.id 
			ORDER BY 
				(T_quantity) DESC;', ['id' => $logged_in_user_id, 'froms' => date("Y-m-d", strtotime($date_from))]
            );

        } else {

            //indiviual location quantity
            $date_rage_product_quantity = DB::select(
                'SELECT 
				SUM(opos_itemdetails.amount) AS T_amount,
				product.name,
				product.systemid
					
				FROM 
				opos_itemdetails,
				opos_receiptproduct,
				product,
				merchantproduct,
				company,
				opos_receipt,
				location,
				opos_locationterminal
				WHERE
				 product.id = opos_receiptproduct.product_id
				AND merchantproduct.product_id = opos_receiptproduct.product_id
				AND company.owner_user_id=:id
				AND	location.id=:loc_id
				AND location.id = opos_locationterminal.location_id
				AND opos_locationterminal.terminal_id=opos_receipt.terminal_id
				AND opos_receipt.id = opos_receiptproduct.receipt_id
				AND opos_receiptproduct.created_at BETWEEN :froms AND :to

				GROUP BY 
				product.name 
				ORDER BY 
				(T_amount) DESC;', ['id' => $logged_in_user_id, 'loc_id' => $branch_id, 'froms' => date("Y-m-d", strtotime($date_from))]
            );
        }

        //company name .....
        $company_data = Company::where('owner_user_id', $logged_in_user_id)->first();
        if (!empty($company_data)) {
            $company_name = $company_data->name;
        }


        //send data to view PDF
        $contents = view("analytics.stocklevelpdf",
            compact('title_name', 'date_from', 'date_rage_product_quantity', 'company_name', 'branch_name'))->render();

        // initialization Mpdf and object create
        $mpdf = new Mpdf([
            'utf-8', // mode - default ''
            'A4', // format - A4, for example, default ''
            10, // font size - default 0
            'dejavusans', // default font family
            10, // margin_left
            10, // margin right
            10, // margin top
            15, // margin bottom
            10, // margin header
            9, // margin footer
            'P'
        ]);

        $mpdf->SetDefaultBodyCSS('color', '#000');
        $mpdf->SetTitle("ocosystem");
        $mpdf->SetSubject("Subject");
        $mpdf->SetAuthor("Company Name");
        $mpdf->autoScriptToLang = true;
        $mpdf->baseScript = 1;
        $mpdf->autoVietnamese = true;
        $mpdf->autoArabic = true;
        $mpdf->autoLangToFont = true;
        $mpdf->SetDisplayMode('fullwidth');
        $mpdf->setFooter('{PAGENO} / {nb}');
        $mpdf->setAutoTopMargin = 'stretch';
        $mpdf->setAutoBottomMargin = 'stretch';
        $stylesheet = file_get_contents('css/appviewPDF.css');
        $mpdf->WriteHTML($stylesheet, 1);
        $mpdf->WriteHTML($contents, 2);
        $mpdf->defaultfooterfontsize = 10;
        $mpdf->defaultfooterfontstyle = 'B';
        $mpdf->defaultfooterline = 0;
        $mpdf->SetCompression(true);
        $filename = date("Y-m-d_his") . '_download.pdf';
        $path = "pdf/analytics/";
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
            $myfile = fopen($path . "/index.html", "w");
            fclose($myfile);
        }
        $mpdf->Output($path . $filename, 'F');

        $data = ['responseCode' => 1, 'file_name' => $filename];
        return response()->json($data);
    }

    public function job_duration_mgmt(Request $request)
    {
        $this->user_data = new UserData();

        $cmrs_company = CMRManagement::with('cmrform')
            ->where('merchant_id', $this->user_data->company_id())
            ->where('created_at', '>=', Carbon::parse($request->start_of_month)->startOfMonth())
            ->where('created_at', '<=', Carbon::parse($request->start_of_month)->endOfMonth())
            ->orderBy('created_at', 'desc')
            ->get();

        if ($request->technician_user_id) {
            $cmrs = CMRManagement::with('cmrform')
                ->where('merchant_id', $this->user_data->company_id())
                ->where('created_at', '>=', Carbon::parse($request->start_of_month)->startOfMonth())
                ->where('created_at', '<=', Carbon::parse($request->start_of_month)->endOfMonth())
                ->orderBy('created_at', 'desc')
                ->get();

            if ($request->technician_user_id != 'all_tech') {
                $cmrs = $cmrs->where('technician_user_id', $request->technician_user_id);
                $technician = User::where("id", $request->technician_user_id)->first();
            } else {
                $technician = "All Technician";
            }

        } else {
            $cmrs = CMRManagement::with('cmrform')
                ->where('merchant_id', $this->user_data->company_id())
                //	->where('technician_user_id', technicians()->first() ? technicians()->first()->id : 0)
                ->where('created_at', '>=', Carbon::parse($request->start_of_month)->startOfMonth())
                ->where('created_at', '<=', Carbon::parse($request->start_of_month)->endOfMonth())
                ->orderBy('created_at', 'desc')
                ->get();

            $technician = "All Technician"; //technicians()->first();
        }


        $merchant = Merchant::where("company_id", $this->user_data->company_id())->first();

        $personalAverage = 0;

        $companyAverage = 0;

        $withinsla = 0;

        $late = 0;

        foreach ($cmrs as $cmr) {
            if ((Carbon::parse($cmr->created_at)->diffInHours(Carbon::parse($cmr->cmrform->start_time))) <= $merchant->cmr_sla) {
                $withinsla++;
            } else {
                $late++;
            }

            $personalAverage += (Carbon::parse($cmr->cmrform->sitein_time)->diffInHours(Carbon::parse($cmr->cmrform->siteout_time)));
        }

        foreach ($cmrs_company as $cmr) {
            $companyAverage += (Carbon::parse($cmr->cmrform->sitein_time)->diffInHours(Carbon::parse($cmr->cmrform->siteout_time)));
        }

        if (count($cmrs) > 0) {
            $personalAverage /= count($cmrs);
        }

        if (count($cmrs_company) > 0) {
            $companyAverage /= count($cmrs_company);
        }

        $months = [];
        for ($x = 1; $x <= 12; $x++) {
            $date = "2020-$x-$x";
            $months[] = date('M', strtotime($date));
        }

        $month = $request->start_of_month;

        Log::debug('Controller: technician=' . json_encode($technician));

        $approved_merchant = company::find($this->user_data->company_id());
        $userApprovedDate = date("Y-m-d", strtotime($approved_merchant->approved_at)) ?? date("Y-m-d");

        return view('analytics.job_duration_mgmt', compact([
            'cmrs_company', 'cmrs', 'month', 'months', 'merchant',
            'withinsla', 'late', 'technician', 'personalAverage',
            'companyAverage', 'userApprovedDate'
        ]));
    }


    public function job_duration_mgmt_update_sla(Request $request)
    {
        $this->user_data = new UserData();
        $merchant = Merchant::where("company_id", $this->user_data->company_id())->first();

        $merchant->update([
            'cmr_sla' => $request->sla,
        ]);

        return response()->json([
            'msg' => 'SLA updated succussfully'
        ]);
    }

    public function job_duration_pdf(Request $request)
    {
        $this->user_data = new UserData();

        $cmrs_company = CMRManagement::with('cmrform')
            ->where('merchant_id', $this->user_data->company_id())
            ->where('created_at', '>=', Carbon::parse($request->start_of_month)->startOfMonth())
            ->where('created_at', '<=', Carbon::parse($request->start_of_month)->endOfMonth())
            ->orderBy('created_at', 'desc')
            ->get();

        if ($request->technician_user_id) {
            $cmrs = CMRManagement::with('cmrform')
                ->where('merchant_id', $this->user_data->company_id())
                ->where('created_at', '>=', Carbon::parse($request->start_of_month)->startOfMonth())
                ->where('created_at', '<=', Carbon::parse($request->start_of_month)->endOfMonth())
                ->orderBy('created_at', 'desc')
                ->get();

            if ($request->technician_user_id != 'all_tech') {
                $cmrs = $cmrs->where('technician_user_id', $request->technician_user_id);
                $technician = User::where("id", $request->technician_user_id)->first();
            } else {
                $technician = "All Technician";
            }

        } else {
            $cmrs = CMRManagement::with('cmrform')
                ->where('merchant_id', $this->user_data->company_id())
                //	->where('technician_user_id', technicians()->first() ? technicians()->first()->id : 0)
                ->where('created_at', '>=', Carbon::parse($request->start_of_month)->startOfMonth())
                ->where('created_at', '<=', Carbon::parse($request->start_of_month)->endOfMonth())
                ->orderBy('created_at', 'desc')
                ->get();

            $technician = "All Technician"; //technicians()->first();
        }


        $merchant = Merchant::where("company_id", $this->user_data->company_id())->first();

        $personalAverage = 0;

        $companyAverage = 0;

        $withinsla = 0;

        $late = 0;

        foreach ($cmrs as $cmr) {
            if ((Carbon::parse($cmr->created_at)->diffInHours(Carbon::parse($cmr->cmrform->start_time))) <= $merchant->cmr_sla) {
                $withinsla++;
            } else {
                $late++;
            }

            $personalAverage += (Carbon::parse($cmr->cmrform->sitein_time)->diffInHours(Carbon::parse($cmr->cmrform->siteout_time)));
        }

        foreach ($cmrs_company as $cmr) {
            $companyAverage += (Carbon::parse($cmr->cmrform->sitein_time)->diffInHours(Carbon::parse($cmr->cmrform->siteout_time)));
        }

        if (count($cmrs) > 0) {
            $personalAverage /= count($cmrs);
        }

        if (count($cmrs_company) > 0) {
            $companyAverage /= count($cmrs_company);
        }

        $months = [];
        for ($x = 1; $x <= 12; $x++) {
            $date = "2020-$x-$x";
            $months[] = date('M', strtotime($date));
        }

        $month = $request->start_of_month;

        Log::debug('Controller: technician=' . json_encode($technician));

        $approved_merchant = company::find($this->user_data->company_id());
        $userApprovedDate = date("Y-m-d", strtotime($approved_merchant->approved_at)) ?? date("Y-m-d");
        $pdf = PDF::setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true])
        ->loadView('analytics.job_duration_mgmt_pdf', compact([
            'cmrs_company', 'cmrs', 'month', 'months', 'merchant',
            'withinsla', 'late', 'technician', 'personalAverage',
            'companyAverage', 'userApprovedDate'
        ]));

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
    return $pdf->download('job_duration_mgmt_pdf.pdf');
      
    }

}
