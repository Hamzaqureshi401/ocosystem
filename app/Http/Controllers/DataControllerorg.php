<?php

namespace App\Http\Controllers;

use App\Classes\SystemID;
use App\Models\MerchantLinkRelation;
use App\Models\stockreportproduct;
use App\Models\warranty;
use App\Models\Wholesale;
use App\StockReport;
use Illuminate\Http\Request;
use \App\Models\usersrole;
use \App\Models\role;
use Illuminate\Support\Carbon;
use \Illuminate\Support\Facades\Auth;
use App\Models\merchantlocation;
use App\Models\location;
use App\Classes\UserData;
use App\Models\prd_inventory;
use App\Models\product;
use App\Models\rawmaterial;
use App\Models\Company;
use App\Models\Currency;
use App\Models\Twoway;
use App\User;
use Yajra\DataTables\DataTables;
use App\Models\MerchantLink;
use \App\Models\merchantproduct;
use App\Models\inventorycost;
use App\Models\PurchaseOrder;
use App\Models\inventorycostproduct;
use App\Models\Merchant;
use App\Models\MerchantCreditLimit;
use App\Models\Oneway;
use App\Models\locationipaddr;
use App\Models\OnewayRelation;
use App\Models\FoodCourtMerchant;
use App\Models\FoodCourtMerchantTerminal;
use App\Models\terminal;
use App\Models\prd_proservices;
use App\Models\opos_locationterminal;
use App\Models\opos_receipt;
use DB;
use Milon\Barcode\DNS1D;
use Milon\Barcode\DNS2D;
use Illuminate\Support\Facades\Validator;
use Log;
use App\Http\Controllers\CreditActController;


class DataController extends Controller
{
    public function showSupplierView()
    {
        $this->user_data = new UserData();
        //$idd = Auth::user()->id;

        $ids = merchantlocation::where('merchant_id', $this->user_data->
        company_id())->
        pluck('location_id');

        $location = location::where([['branch', '!=', 'null']])->
        whereIn('id', $ids)->
        latest()->
        get();

        $user_id = $this->getCompanyUserId();

        return view('data.supplier', compact(
            'location', 'user_id'
        ));
    }


    public function showDocumenttrackingView($merchantId, Request $request)
    {
        $this->user_data = new UserData();
        //$idd = Auth::user()->id;

        $id = Auth::user()->id;
        $user_data = new UserData();

        $user_roles = usersrole::where('user_id', $id)->get();

        $is_king = \App\Models\Company::where('owner_user_id', Auth::user()->id)->first();

        if ($is_king != null) {
            $is_king = true;
        } else {
            $is_king = false;
        }

        if (!$user_data->company_id()) {
            abort(404);
        }

        $ids = merchantproduct::where('merchant_id',
            $this->user_data->company_id())->
        pluck('product_id');
        //dd($ids);
        $inventory_data = product::whereIn('ptype',
            ['inventory', 'rawmaterial'])->
        whereNotNull('name')->
        whereIn('id', $ids)->
        where('photo_1', '!=', '')->
        where('thumbnail_1', '!=', '')->
        where('prdcategory_id', '!=', '')->
        where('prdsubcategory_id', '!=', '')
            ->get();

        $ids = merchantlocation::where('merchant_id',
            $this->user_data->company_id())->
        pluck('location_id');

        $locations = location::where([['branch', '!=', 'null']])->where('warehouse', 0)->
        whereIn('id', $ids)->latest()->
        get();
        //dd($locations);

        $user_id = $this->getCompanyUserId();

        $report_id = stockreport::count();
        $report_id += 1;

        return view('data.tracking', compact(
            'locations', 'user_id', 'merchantId', 'inventory_data', 'report_id', 'user_roles', 'is_king'
        ));
    }

    public function getLocations()
    {
        $this->user_data = new UserData();

        $ids = merchantlocation::where('merchant_id', $this->user_data->company_id())->pluck('location_id');
        $location = location::where([['branch', '!=', 'null']])->whereIn('id', $ids)->where('warehouse', 0)->latest()->get();
        $response = [
            'data' => $location,
            'recordsTotal' => location::whereIn('id', $ids)->latest()->count(),
            'recordsFiltered' => location::whereIn('id', $ids)->latest()->count()
        ];
        return response()->json($response);
    }

    public function getLocationqty($location_id)
    {
        $this->user_data = new UserData();
        //$idd = Auth::user()->id;

        $id = Auth::user()->id;
        $user_data = new UserData();

        $ids = merchantproduct::where('merchant_id',
            $this->user_data->company_id())->
        pluck('product_id');

        $inventory_data = product::whereIn('ptype',
            ['inventory', 'rawmaterial'])->
        whereNotNull('name')->
        whereIn('id', $ids)->
        where('photo_1', '!=', '')->
        where('thumbnail_1', '!=', '')->
        where('prdcategory_id', '!=', '')->
        where('prdsubcategory_id', '!=', '')
            ->get();

        $quantities = [];
        $k = 0;
        foreach ($inventory_data as $myproduct) {
            $quantity = app('App\Http\Controllers\InventoryController')->location_productqty($myproduct->id, $location_id);
            $quantities[$k] = new \stdClass();
            $quantities[$k]->product_id = $myproduct->id;
            $quantities[$k]->product_qty = (int)$quantity;
            $k++;
        }

        $response = [
            'data' => $quantities
        ];
        return response()->json($response);
        //dd($quantities);
    }

    public function getCompanyUserId()
    {
        $userData = new UserData();
        $companyId = $userData->company_id();
        $company = Company::find($companyId);
        return $company->owner_user_id;
    }


    public function merchantTransactionExist($merchantId)
    {
        return merchantlocation::select('merchantlocation.id')
            ->join('opos_locationterminal', 'opos_locationterminal.location_id', '=', 'merchantlocation.location_id')
            ->join('opos_receipt', 'opos_receipt.terminal_id', '=', 'opos_locationterminal.terminal_id')
            ->where('merchantlocation.merchant_id', $merchantId)
            ->get()->count() > 0 ? true : false;
    }


    public function getSupplierData(Request $request)
    {
        //$userId = Auth::user()->id;
        $userId = $this->getCompanyUserId();

        $userData = new UserData();

        $responderIds = MerchantLink::where('initiator_user_id', $userId)->
        pluck('responder_user_id')->toArray();

        $initiatorIds = MerchantLink::where('responder_user_id', $userId)->
        pluck('initiator_user_id')->toArray();

        $merchantUserIds = array_merge($responderIds, $initiatorIds);

        $first = Company::select('company.id as company_id',
            'company.name as company_name',
            'company.business_reg_no as company_business_reg_no',
            'company.systemid as company_system_id',
            'company.owner_user_id',
            'company.id as merchant_id'
        );

        //->join('merchant', 'merchant.company_id', '=', 'company.id');


        /*
        // name filter
        $search = $request->input('search');
        if (isset($search['value']) && $search['value'] != '')
            $query->where('title', 'like', '%'.$search['value'].'%');

        // order by
        $order = $request->input('order');
        if (isset($order[0]['column']) && $order[0]['column'] != '') {
            if ($order[0]['column'] == 2) {
                $query->orderBy('title', $order[0]['dir']);
            }
            if ($order[0]['column'] == 4) {
                $query->orderBy('price', $order[0]['dir']);
            }
        }

        $query->orderBy('id',  'desc');
        */

        $first->whereIn('company.owner_user_id', $merchantUserIds);


        // one way information
        $oneWayData = Oneway::selectRaw(
            "id as company_id,
			company_name,
			business_reg_no as company_business_reg_no,
			null as company_system_id,
			null as owner_user_id,
			null as merchant_d"
        )->where('self_merchant_id', $userData->company_id());

        $query = Company::select('company.id as company_id',
            'company.name as company_name',
            'company.business_reg_no as company_business_reg_no',
            'company.systemid as company_system_id',
            'company.owner_user_id',
            'company.id as merchant_id'
        );

        //->join('merchant', 'merchant.company_id', '=', 'company.id');

        $query->where('company.owner_user_id', $userId)->
        union($oneWayData)->
        union($first);

        // applying limit
        $suppliers = $query->skip($request->input('start'))->
        take($request->input('length'))->get();

        $dealerMerchantId = $userData->company_id();

        $counter = 0 + $request->input('start');
        foreach ($suppliers as $key => $supplier) {
            $suppliers[$key]['indexNumber'] = ++$counter;
            $suppliers[$key]['merchant_link_id'] = 0;
            $suppliers[$key]['status'] = 'pending';
            $suppliers[$key]['merchant_link_relation_id'] = 0;
            $suppliers[$key]['merchant_location'] = 'Location';
            $suppliers[$key]['merchant_location_id'] = '';
            $suppliers[$key]['row_type'] = 'own';

            $suppliers[$key]['delete_status'] = 'active';

            if (!is_null($supplier->merchant_id)) {
                //$mlIds = merchantlocation::where('merchant_id', $supplier->merchant_id)->pluck('location_id');
                //$suppliers[$key]['merchant_location'] = location::whereIn('id', $mlIds)->get()->count();

                $suppliers[$key]['delete_status'] = $this->merchantTransactionExist($supplier->merchant_id) ? 'inactive' : 'active';


                if ($supplier->merchant_id == $dealerMerchantId) {
                    $suppliers[$key]['delete_status'] = 'inactive';
                    $merchant = Merchant::where('id', $supplier->merchant_id)->first();
                    if (!empty($merchant->supplier_default_location_id)) {
                        $merchantLocation =
                            location::where('id',
                                $merchant->supplier_default_location_id)->
                            first();

                        $suppliers[$key]['merchant_location'] =
                            $merchantLocation->branch;

                        $suppliers[$key]['merchant_location_id'] =
                            $merchant->supplier_default_location_id;
                    }
                }


                // credit limit
                $suppliers[$key]['credit_limit'] = 0;
                $suppliers[$key]['credit_limit_id'] = '';
                $merchantCreditLimit = MerchantCreditLimit::where('supplier_merchant_id',
                    $supplier->company_id)->
                where('dealer_merchant_id', $dealerMerchantId)->
                first();

                if ($merchantCreditLimit) {
                    $suppliers[$key]['credit_limit'] = $merchantCreditLimit->credit_limit / 100;
                    $suppliers[$key]['credit_limit_id'] = $merchantCreditLimit->id;
                    $suppliers[$key]['used_limit'] = $merchantCreditLimit->avail_credit_limit / 100;
                }

                if (in_array($supplier->owner_user_id, $responderIds)) {
                    $suppliers[$key]['row_type'] = 'twoway';
                    $suppliers[$key]['responder'] = '0';
                    $merchantLink = MerchantLink::where('initiator_user_id', $userId)->
                    where('responder_user_id', $supplier->owner_user_id)->
                    first();

                    $suppliers[$key]['merchant_link_id'] = $merchantLink->id;

                    // initiator
                    $merchantLinkRelation = MerchantLinkRelation::where('merchantlink_id',
                        $merchantLink->id)->
                    where('ptype', 'dealer')->
                    first();

                    if ($merchantLinkRelation != null) {
                        $suppliers[$key]['status'] = $merchantLinkRelation->status;
                        $suppliers[$key]['merchant_link_relation_id'] =
                            $merchantLinkRelation->id;

                        $own_merchantLinkRelation = MerchantLinkRelation::where('merchantlink_id', $merchantLink->id)->
                        where('company_id', $dealerMerchantId)->where('ptype', 'dealer')->first();

                        if (!empty($own_merchantLinkRelation->default_location_id)) {
                            $suppliers[$key]['merchant_link_relation_id'] = $own_merchantLinkRelation->id;
                            $location = location::where('id', $own_merchantLinkRelation->default_location_id)->first();
                            $suppliers[$key]['merchant_location'] = $location->branch;
                            $suppliers[$key]['merchant_location_id'] = $own_merchantLinkRelation->default_location_id;
                        }
                    }
                }

                if (in_array($supplier->owner_user_id, $initiatorIds)) {
                    $suppliers[$key]['row_type'] = 'twoway';
                    $merchantLink = MerchantLink::where('initiator_user_id',
                        $supplier->owner_user_id)->
                    where('responder_user_id', $userId)->
                    first();

                    $suppliers[$key]['merchant_link_id'] = $merchantLink->id;
                    $suppliers[$key]['responder'] = '1';

                    // responder
                    $merchantLinkRelation = MerchantLinkRelation::where('merchantlink_id',
                        $merchantLink->id)->
                    where('ptype', 'supplier')->
                    first();

                    if ($merchantLinkRelation != null) {
                        $suppliers[$key]['status'] = $merchantLinkRelation->status;
                        $suppliers[$key]['merchant_link_relation_id'] = $merchantLinkRelation->id;

                        if (!is_null($merchantLinkRelation->default_location_id)) {
                            $location = location::where('id',
                                $merchantLinkRelation->default_location_id)->
                            first();

                            $suppliers[$key]['merchant_location'] = $location->branch;
                            $suppliers[$key]['merchant_location_id'] =
                                $merchantLinkRelation->default_location_id;
                        }
                    }
                }

            } else {
                // process one way
                $suppliers[$key]['row_type'] = 'oneway';

                $oneWayRelation = OnewayRelation::where('oneway_id',
                    $supplier->company_id)->
                where('ptype', 'supplier')->
                first();

                if (!is_null($oneWayRelation)) {
                    $suppliers[$key]['status'] = $oneWayRelation->status;
                    $suppliers[$key]['merchant_link_relation_id'] =
                        $oneWayRelation->id;

                    if (!is_null($oneWayRelation->default_location_id)) {
                        $location = location::where('id',
                            $oneWayRelation->default_location_id)->first();

                        $suppliers[$key]['merchant_location'] =
                            $location->branch;

                        $suppliers[$key]['merchant_location_id'] =
                            $oneWayRelation->default_location_id;
                    }
                }
            }
        }

        // dd($supplier);

        $response = [
            'data' => $suppliers,
            'recordsTotal' => $suppliers->count(),
            'recordsFiltered' => $suppliers->count()
        ];
        return response()->json($response);

    }


    public function showDealerView()
    {
        $this->user_data = new UserData();
        //$idd = Auth::user()->id;
        $ids = merchantlocation::where('merchant_id',
            $this->user_data->company_id())->
        pluck('location_id');

        $location = location::where([['branch', '!=', 'null']])->
        whereIn('id', $ids)->latest()->
        get();

        $user_id = $this->getCompanyUserId();
        $company = Company::where("owner_user_id", $user_id)->first();
        $selfMerchantId = $company->id;
        $locationipaddr = locationipaddr::where('company_id', $selfMerchantId)->first();
		if (!empty($locationipaddr)) {
			$locationipaddr = $locationipaddr->tsystem;
		} 
        return view('data.dealer', compact('location', 'user_id', 'locationipaddr'));
    }

    public function getDealerData(Request $request)
    {

        //$userId = Auth::user()->id;
        $userId = $this->getCompanyUserId();

        $responderIds = MerchantLink::where('initiator_user_id',
            $userId)->pluck('responder_user_id')->toArray();

        $initiatorIds = MerchantLink::where('responder_user_id',
            $userId)->pluck('initiator_user_id')->toArray();

        $merchantUserIds = array_merge($responderIds, $initiatorIds);

        $first = Company::select('company.id as company_id',
            'company.name as company_name',
            'company.business_reg_no as company_business_reg_no',
            'company.systemid as company_system_id',
            'company.owner_user_id',
            'company.id as merchant_id'
        );
        //->join('merchant', 'merchant.company_id', '=', 'company.id');

        /*
        // name filter
        $search = $request->input('search');
        if (isset($search['value']) && $search['value'] != '')
            $query->where('title', 'like', '%'.$search['value'].'%');

        // order by
        $order = $request->input('order');
        if (isset($order[0]['column']) && $order[0]['column'] != '') {
            if ($order[0]['column'] == 2) {
                $query->orderBy('title', $order[0]['dir']);
            }
            if ($order[0]['column'] == 4) {
                $query->orderBy('price', $order[0]['dir']);
            }
        }

        $query->orderBy('id',  'desc');
        */

        $first->whereIn('company.owner_user_id', $merchantUserIds);

        $userData = new UserData();

        // one way information
        $oneWayData = Oneway::selectRaw("id as company_id,
			company_name,
			business_reg_no as company_business_reg_no,
			null as company_system_id,
			null as owner_user_id,
			null as merchant_d"
        )->where('self_merchant_id', $userData->company_id());


        $query = Company::select('company.id as company_id',
            'company.name as company_name',
            'company.business_reg_no as company_business_reg_no',
            'company.systemid as company_system_id',
            'company.owner_user_id',
            'company.id as merchant_id'
        );
        //->join('merchant', 'merchant.company_id', '=', 'company.id');

        $query->where('company.owner_user_id', $userId)->
        union($oneWayData)->
        union($first);


        // applying limit
        $dealers = $query->skip($request->input('start'))->take($request->input('length'))->get();

        $counter = 0 + $request->input('start');


        $supplierMerchantId = $userData->company_id();

        foreach ($dealers as $key => $dealer) {
            $dealers[$key]['indexNumber'] = ++$counter;
            $dealers[$key]['merchant_link_id'] = 0;
            $dealers[$key]['status'] = 'pending';
            $dealers[$key]['merchant_link_relation_id'] = 0;

            $dealers[$key]['merchant_location'] = 'Location';
            $dealers[$key]['merchant_location_id'] = '';
            $dealers[$key]['row_type'] = 'own';

            $dealers[$key]['delete_status'] = 'active';

            if (!is_null($dealer->merchant_id)) {

                //$mlIds = merchantlocation::where('merchant_id', $dealer->merchant_id)->pluck('location_id');
                //$dealers[$key]['merchant_location'] = location::whereIn('id', $mlIds)->get()->count();

                $dealers[$key]['delete_status'] = $this->merchantTransactionExist($dealer->merchant_id) ? 'inactive' : 'active';

                if ($dealer->merchant_id == $supplierMerchantId) {
                    $dealers[$key]['delete_status'] = 'inactive';
                    $merchant = Merchant::where('company_id', $dealer->merchant_id)->first();
                    if (!empty($merchant->dealer_default_location_id)) {
                        $merchantLocation = location::where('id', $merchant->dealer_default_location_id)->first();
                        $dealers[$key]['merchant_location'] = $merchantLocation->branch;
                        $dealers[$key]['merchant_location_id'] = $merchant->dealer_default_location_id;
                    }
                }


                // credit limit
                $dealers[$key]['credit_limit'] = 0;
                $dealers[$key]['credit_limit_id'] = '';
                $merchantCreditLimit = MerchantCreditLimit::where('supplier_merchant_id', $supplierMerchantId)->where('dealer_merchant_id', $dealer->merchant_id)->first();
                if ($merchantCreditLimit) {
                    $dealers[$key]['credit_limit'] = $merchantCreditLimit->credit_limit / 100;
                    $dealers[$key]['credit_limit_id'] = $merchantCreditLimit->id;
                    $dealers[$key]['avail_credit_limit'] = $merchantCreditLimit->avail_credit_limit / 100;
                }

                if (in_array($dealer->owner_user_id, $responderIds)) {

                    // check for transaction


                    $dealers[$key]['row_type'] = 'twoway';
                    $dealers[$key]['responder'] = '0';
                    $merchantLink = MerchantLink::where('initiator_user_id', $userId)->where('responder_user_id', $dealer->owner_user_id)->first();
                    $dealers[$key]['merchant_link_id'] = $merchantLink->id;

                    // initiator
                    $merchantLinkRelation = MerchantLinkRelation::where('merchantlink_id', $merchantLink->id)->
                    where('ptype', 'supplier')->first();

                    if ($merchantLinkRelation != null) {
                        $dealers[$key]['merchant_link_relation_id'] = $merchantLinkRelation->id;
                        $dealers[$key]['status'] = $merchantLinkRelation->status;

                        $own_merchantLinkRelation = MerchantLinkRelation::where('merchantlink_id', $merchantLink->id)->
                        where('company_id', $supplierMerchantId)->where('ptype', 'supplier')->first();

                        if (!empty($own_merchantLinkRelation->default_location_id)) {
                            $dealers[$key]['merchant_link_relation_id'] = $own_merchantLinkRelation->id;
                            $location = location::where('id', $own_merchantLinkRelation->default_location_id)->first();
                            $dealers[$key]['merchant_location'] = $location->branch;
                            $dealers[$key]['merchant_location_id'] = $own_merchantLinkRelation->default_location_id;
                        }

                    }
                }

                if (in_array($dealer->owner_user_id, $initiatorIds)) {
                    $dealers[$key]['row_type'] = 'twoway';
                    $dealers[$key]['responder'] = '1';
                    $merchantLink = MerchantLink::where('initiator_user_id', $dealer->owner_user_id)->where('responder_user_id', $userId)->first();
                    $dealers[$key]['merchant_link_id'] = $merchantLink->id;

                    // responder
                    $merchantLinkRelation = MerchantLinkRelation::where('merchantlink_id', $merchantLink->id)->where('ptype', 'dealer')->first();
                    if ($merchantLinkRelation != null) {
                        $dealers[$key]['status'] = $merchantLinkRelation->status;
                        $dealers[$key]['merchant_link_relation_id'] = $merchantLinkRelation->id;

                        if (!is_null($merchantLinkRelation->default_location_id)) {
                            $location = location::where('id', $merchantLinkRelation->default_location_id)->first();
                            $dealers[$key]['merchant_location'] = $location->branch;
                            $dealers[$key]['merchant_location_id'] = $merchantLinkRelation->default_location_id;
                        }

                    }

                }

            } else {

                // process one way
                $dealers[$key]['row_type'] = 'oneway';

                $oneWayRelation = OnewayRelation::where('oneway_id', $dealer->company_id)->where('ptype', 'dealer')->first();
                if (!is_null($oneWayRelation)) {
                    $dealers[$key]['status'] = $oneWayRelation->status;
                    $dealers[$key]['merchant_link_relation_id'] = $oneWayRelation->id;

                    if (!is_null($oneWayRelation->default_location_id)) {
                        $location = location::where('id', $oneWayRelation->default_location_id)->first();
                        $dealers[$key]['merchant_location'] = $location->branch;
                        $dealers[$key]['merchant_location_id'] = $oneWayRelation->default_location_id;
                    }

                }

            }

        }


        $response = [
            'data' => $dealers,
            'recordsTotal' => $dealers->count(),
            'recordsFiltered' => $dealers->count()
        ];
        return response()->json($response);
    }

    public function getOwnwayMerchantData($companyId)
    {
        $oneway = Oneway::where('id', $companyId)->first();
        return response()->json($oneway);
    }

    /**
     * save inventory cost
     * @param Request $request
     */
    public function saveInventoryCost(Request $request)
    {
//        copy:
        $documentNo = inventorycost::where('doc_no', $request->input('documentNo'))->first();
        if ($documentNo != null) {
            return response()->json(['msg' => 'Document No Already Exist', 'status' => 'false']);
        }

        //$userId = Auth::user()->id;
        $userId = $this->getCompanyUserId();

        $merchant = Merchant::select('merchant.id as id')
            ->join('company', 'company.id', '=', 'merchant.company_id')
            ->where('company.owner_user_id', $userId)->first();

        if ($merchant == null) {
            return response()->json(['msg' => 'Buyer Merchant ID not exist', 'status' => 'false']);
        }

        $inventoryCost = new inventorycost();
        $inventoryCost->seller_merchant_id = $request->input('merchantId');
        $inventoryCost->buyer_merchant_id = $merchant->id;
        $inventoryCost->doc_date = date('Y-m-d', strtotime($request->input('dated')));
        $inventoryCost->doc_no = $request->input('documentNo');
        $inventoryCost->save();

        $inventoryCostId = $inventoryCost->id;

        $products = $request->input('products');
        foreach ($products as $product) {
            $inventoryCostProduct = new inventorycostproduct();
            $inventoryCostProduct->inventorycost_id = $inventoryCostId;
            $inventoryCostProduct->product_id = $product['productId'];
            $inventoryCostProduct->quantity = $product['qty'];
            $inventoryCostProduct->cost = $product['productCost'] * 100;
            $inventoryCostProduct->save();
        }

        return response()->json(['msg' => 'Inventory Cost saved successfully', 'status' => 'true']);
    }

    public function saveMerchantCreditLimit(Request $request)
    {
        $userData = new UserData();
        $supplierMerchantId = $userData->company_id();
        $dealerMerchantId = $request->input('merchantId');
        $creditLimitId = $request->input('creditLimitId');
        $creditLimit = (float)str_replace(",", "", $request->input('creditLimit'));
        $creditLimit = $creditLimit * 100;

        if ($creditLimitId != '') {
            $merchantCreditLimit = MerchantCreditLimit::find($creditLimitId);
            if ($merchantCreditLimit->credit_limit == $creditLimit) {
                abort(404);
            }
        } else {
            $merchantCreditLimit = new MerchantCreditLimit();
            if (empty($request->creditLimit)) {
                abort(404);
            }
        }
        $merchantCreditLimit->dealer_merchant_id = $dealerMerchantId;
        $merchantCreditLimit->supplier_merchant_id = $supplierMerchantId;
        $merchantCreditLimit->credit_limit = $creditLimit;
        $merchantCreditLimit->save();
        return response()->json(['msg' => 'Credit limit updated successfully', 'status' => 'true']);
    }


    public function saveMerchantTwoWayLinking(Request $request)
    {
        //$initiatorUserId = Auth::user()->id;
        $initiatorUserId = $this->getCompanyUserId();

        $responder = $request->input('merchant_id');
        $merchant = Company::where('systemid', $responder)->first();
        $company_id = $merchant->id;
        if ($merchant !== null) {
            $responderUserId = $merchant->owner_user_id;
            if ($initiatorUserId == $responderUserId) {
                return response()->json([
                    'msg' => 'A merchant cannot add himself',
                    'status' => 'false']);
            }

            Log::debug('TW responder=' . $responder);
            Log::debug('TW responderUserId=' . $responderUserId);
            Log::debug('TW initiatorUserId=' . $initiatorUserId);

            $merchantLink = MerchantLink::
            where('responder_user_id', $responderUserId)->
            where('initiator_user_id', $initiatorUserId)->
            first();

            if (!empty($merchantLink)) {
                Log::debug('TW merchantLink=' . json_encode($merchantLink));
                Log::debug('TW merchantLink->initiator_user_id=' .
                    $merchantLink->initiator_user_id);
                Log::debug('TW merchantLink->responder_user_id=' .
                    $merchantLink->responder_user_id);
            }

            if (!empty($merchantLink) &&
                $merchantLink->initiator_user_id == $initiatorUserId &&
                $merchantLink->responder_user_id == $responderUserId) {
                return response()->json([
                    'msg' => 'Merchant ID already added',
                    'status' => 'false'
                ]);

            } else {
                //Add Two way linking
                $mLink = new MerchantLink();
                $mLink->initiator_user_id = $initiatorUserId;
                $mLink->responder_user_id = $responderUserId;
                //$mLink->save();
                $userData = new UserData();
                $selfMerchantId = $userData->company_id();
                $id = $mLink->id;
                $merchantlink = MerchantLink::with(["merchantLinkRelation.company.owner_user", "initiator_user.user_company", "responder_user.user_company",])
                    ->find($id);

                Log::info($initiatorUserId != '' ? 'Merchant updated :' : ' Merchant Added :' . $id);
                $location = locationipaddr::where('company_id', $selfMerchantId)->groupBy('location_id')->get();
                foreach ($location as $loc) {
                    $response = (new CreditActController)->twowaytransfer(array('company_id' => $merchant->id, 'name' => $merchant->name, 'business_reg_no' => $merchant->business_reg_no, 'systemid' => $merchant->systemid, 'corporate_logo' => $merchant->corporate_logo, 'owner_user_id' => $merchant->owner_user_id, 'gst_vat_sst' => $merchant->gst_vat_sst, 'currency_id' => $merchant->currency_id, 'office_address' => $merchant->office_address, 'status' => $merchant->status, 'selfMerchantId' => $selfMerchantId, 'location_id' => $loc->location_id, 'id' => $id, 'initiator_user_id' => $initiatorUserId, 'responder_user_id' => $responderUserId), $loc->tsystem);

                    CreditActController::saveMerchandLinkOceaniadb($merchantlink, Auth::user()->id, $loc->tsystem , $company_id);
                     Log::debug('hamza=' .
                    $company_id);
                    return response()->json($response);
                }

                //             return response()->json([
                // 	'msg' => 'Merchant ID added successfully ',
                // 	'status' => 'true'
                // ]);
            }

        } else {
            return response()->json([
                'msg' => 'Merchant ID not found',
                'status' => 'false'
            ]);
        }
    }

    public function saveMerchantDefaultLocation(Request $request)
    {
        $user_data = new UserData();
        $locationId = $request->input('locationId');
        $rowType = $request->input('rowType');
        $linkingId = $request->input('linkingId');

        $status = false;
        if ($rowType == 'own') {
            $type = $request->input('type');
            $userData = new UserData();
            $merchantId = $userData->company_id();
            $merchant = Merchant::where('company_id', $merchantId)->first();
            if ($type == 'supplier') {
                $merchant->supplier_default_location_id = $locationId;
            } else {
                $merchant->dealer_default_location_id = $locationId;
            }
            $merchant->save();
            $status = true;
        } elseif ($rowType == 'oneway') {
            $oneWayRelation = OnewayRelation::where('id', $linkingId)->first();
            if ($oneWayRelation != null) {
                $oneWayRelation->default_location_id = $locationId;
                $oneWayRelation->save();
            }
            $status = true;
        } elseif ($rowType == 'twoway') {
            $twoWayRelation = MerchantLinkRelation::where('id', $linkingId)->first();
            if ($twoWayRelation != null && $twoWayRelation->company_id == $user_data->company_id()) {
                $twoWayRelation->default_location_id = $locationId;
                $twoWayRelation->save();
            } else {
                DB::table('merchantlinkrelation')->insert([
                    "merchantlink_id" => $twoWayRelation->merchantlink_id,
                    "default_location_id" => $locationId,
                    "company_id" => $user_data->company_id(),
                    'ptype' => $twoWayRelation->ptype,
                    'status' => 'active',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            }

            $status = true;
        }

        if ($status) {
            return response()->json(['msg' => 'Default location save successfully', 'status' => 'true']);
        } else {
            return response()->json(['msg' => 'Default location not saved', 'status' => 'false']);
        }


    }

    public function delMerchantOnyWay(Request $request)
    {
        $oneWayId = $request->input('companyId');
        Oneway::find($oneWayId)->delete();
        OnewayRelation::where('oneway_id', $oneWayId)->delete();
        return response()->json(['status' => 'true']);
    }


    public function delMerchantTwoWayLinking(Request $request)
    {
        $selectedMerchantId = $request->input('selectedMerchantId');
        $foodCourtMerchant = FoodCourtMerchant::where('tenant_merchant_id', $selectedMerchantId)->get();
        foreach ($foodCourtMerchant as $fcMerchant) {
            $fcMerchantTerminals = FoodCourtMerchantTerminal::where('foodcourtmerchant_id', $fcMerchant->id)->get();
            foreach ($fcMerchantTerminals as $fcMerchantTerminal) {
                terminal::where('id', $fcMerchantTerminal->terminal_id)->delete();
            }
            FoodCourtMerchantTerminal::where('foodcourtmerchant_id', $fcMerchant->id)->delete();
        }

        FoodCourtMerchant::where('tenant_merchant_id', $selectedMerchantId)->delete();

        $merchantLinkId = $request->input('merchantLinkId');
        MerchantLink::find($merchantLinkId)->delete();
        MerchantLinkRelation::where('merchantlink_id', $merchantLinkId)->delete();
        return response()->json(['status' => 'true']);
    }

    public function getMerchantLocations($merchantId)
    {
        $ids = merchantlocation::where('merchant_id', $merchantId)->pluck('location_id');
        $location = location::whereIn('id', $ids)->latest()->get();
        $response = [
            'data' => $location,
            'recordsTotal' => location::where([['branch', '!=', 'null']])->whereIn('id', $ids)->latest()->count(),
            'recordsFiltered' => location::where([['branch', '!=', 'null']])->whereIn('id', $ids)->latest()->count()
        ];
        return response()->json($response);

    }

    public function saveMerchantOneway(Request $request)
    {
        $userData = new UserData();
        $selfMerchantId = $userData->company_id();
        Log::info('saveMerchantOneway: selfMerchantId  :'. $selfMerchantId);
        $companyId = $request->input('company_id');

        if ($companyId != '') {
            $oneWay = Oneway::where('id', $companyId)->first();
        } else {
            $oneWay = new Oneway();
        }

        $oneWay->self_merchant_id = $selfMerchantId;
        $oneWay->company_name = $request->input('company_name');
        $oneWay->business_reg_no = $request->input('business_reg_no');
        $oneWay->address = $request->input('address');
        $oneWay->contact_name = $request->input('contact_name');
        $oneWay->mobile_no = $request->input('mobile_no');
        $oneWay->save();
        $id = $oneWay->id;
        //save onewayrelation and location
        if ($companyId == '') {
            DB::table('onewayrelation')->insert([
                'oneway_id' => $id,
                'status' => 'active',
                'ptype' => 'dealer',
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        Log::info($companyId != '' ? 'Merchant updated :' : ' Merchant Added :' . $id);
        $location = locationipaddr::where('company_id', $selfMerchantId)->groupBy('location_id')->get();
        foreach ($location as $loc) {
            if ($companyId == '') {
                DB::table('onewaylocation')->insert([
                    'oneway_id' => $id,
                    'location_id' => $loc->location_id,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
            $onewayrelation = Oneway::with(["onewayrelation","onewaylocation"])
                ->where("id",$id)
                ->first();
            $response = CreditActController::saveMerchandLinkOneWayOceaniadb($onewayrelation,Auth::user()->id,$companyId,$loc->tsystem);
            //  $response = (new CreditActController)->CreditActTransfer(array('location_id' => $loc->location_id, 'id' => $id, 'self_merchant_id' => $selfMerchantId, 'company_name' => $request->input('company_name'), 'business_reg_no' => $request->input('business_reg_no'), 'address' => $request->input('address'), 'contact_name' => $request->input('contact_name'), 'mobile_no' => $request->input('mobile_no')), $loc->ipaddr);
        }
        // return response()->json($response);
        return response()->json(['msg' => $companyId != '' ? 'Merchant updated successfully' : 'Merchant added successfully', 'status' => 'true']);

    }

    public function saveOnewayRelation(Request $request)
    {
        $onewayRelation = new OnewayRelation();
        $onewayRelation->oneway_id = $request->input('companyId');
        $onewayRelation->ptype = $request->input('ptype');
        $onewayRelation->status = $request->input('status');
        $onewayRelation->save();
        return response()->json(['msg' => 'Status updated successfully', 'status' => 'true']);
    }

    public function changeOnewayRelationStatus(Request $request)
    {
        $onewayRelation = OnewayRelation::find($request->input('merchantLinkRelationId'));
        $onewayRelation->status = $request->input('status');
        $onewayRelation->save();
        return response()->json(['msg' => 'Status updated successfully', 'status' => 'true']);
    }

    public function saveMerchantLinkRelation(Request $request)
    {
        $user_data = new UserData();
        $merchantLinkRelation = new MerchantLinkRelation();
        $merchantLinkRelation->merchantlink_id = $request->input('merchantLinkId');
        $merchantLinkRelation->ptype = $request->input('ptype');
        $merchantLinkRelation->status = $request->input('status');
        $merchantLinkRelation->company_id = $user_data->company_id();
        $merchantLinkRelation->save();
        return response()->json(['msg' => 'Merchant ID added successfully', 'status' => 'true']);
    }

    public function deactivateMerchantLinkRelation(Request $request)
    {
        $merchantLinkRelationId = $request->input('merchantLinkRelationId');
        $status = $request->input('status');
        $merchantLinkRelation = MerchantLinkRelation::find($merchantLinkRelationId);
        $merchantLinkRelation->status = $status;
        $merchantLinkRelation->save();
        return response()->json(['msg' => 'Status deactivated successfully', 'status' => 'true']);
    }

    public function activateMerchantLinkRelation(Request $request)
    {
        $merchantLinkId = $request->input('merchantLinkId');
        $responderUserId = $request->input('merchantUserId');
        $merchantLinkRelationId = $request->input('merchantLinkRelationId');
        //$initiaterUserId = Auth::user()->id;
        $initiaterUserId = $this->getCompanyUserId();

        /*
        $merchantLink = MerchantLink::find($merchantLinkId);
        $merchantLink->initiator_user_id = $initiaterUserId;
        $merchantLink->responder_user_id = $responderUserId;
        $merchantLink->save();
        */
        MerchantLinkRelation::find($merchantLinkRelationId)->delete();
        return response()->json(['msg' => 'Request send successfully', 'status' => 'true']);
    }

    public function savePo(Request $request)
    {
        $user_data = new UserData();
        //	dd($request->poproducts);
        $systemid = new SystemID('purchaseorder');
        $PurchaseOrder = new PurchaseOrder();
        $PurchaseOrder->systemid = $systemid->__toString();
        $PurchaseOrder->issuer_merchant_id = $request->issuer_merchant_id;
        $PurchaseOrder->status = 'pending';
        $PurchaseOrder->issuer_location_id = $request->location_id;
        $PurchaseOrder->save();
        $systemid = $systemid->__toString();
        $code = DNS1D::getBarcodePNG(trim($systemid), "C128");
        $qr = DNS2D::getBarcodePNG($systemid, "QRCODE");

        for ($i = 0; $i < sizeof($request->poproducts); $i++) {

            $product_find = product::find($request->poproducts[$i]['product_id']);
            DB::table('purchaseorderproduct')->insert([
                'purchaseorder_id' => $PurchaseOrder->id,
                'product_id' => $request->poproducts[$i]['product_id'],
                'quantity' => $request->poproducts[$i]['quantity'],
                'product_systemid' => $product_find->systemid,
                'product_name' => $product_find->name,
                'product_thumbnail' => $product_find->thumbnail_1 ?? '',
                'purchase_price' => ((int)str_replace(',', '', $request->poproducts[$i]['price'])) * 100,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

        }

        DB::table('merchantpurchaseorder')->insert([
            'purchaseorder_id' => $PurchaseOrder->id,
            'merchant_id' => $request->merchant_id,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        //MGLINK
        $merchantglobal = DB::table('merchantglobal')->
        where('merchant_id', $user_data->company_id())->
        first();

        $supplier_merchant = DB::table('company')->find($request->merchant_id);
        $dealer_merchant = DB::table('company')->find($user_data->company_id());
        $location = location::find($request->location_id);

        $mgLink = [];
        $mgLink['purchaseorder_id'] = $PurchaseOrder->id;

        $mgLink['po_footer'] = $merchantglobal->po_footer ?? '';
        $mgLink['currency_id'] = $dealer_merchant->currency_id ?? 0;

        $mgLink['supplier_company_name'] = $supplier_merchant->name;
        $mgLink['supplier_business_reg_no'] = $supplier_merchant->business_reg_no ?? '';
        $mgLink['supplier_address'] = $supplier_merchant->office_address ?? '';

        $mgLink['dealer_company_name'] = $dealer_merchant->name;
        $mgLink['dealer_business_reg_no'] = $dealer_merchant->business_reg_no ?? '';
        $mgLink['dealer_address'] = $dealer_merchant->office_address ?? '';

        $mgLink['delivery_address'] = $location->address_line1 ?? '';

        $mgLink['created_at'] = date("Y-m-d H:i:s");
        $mgLink['updated_at'] = date("Y-m-d H:i:s");

        if (!empty($merchantglobal)) {
            if ($merchantglobal->po_has_logo == 1) {
                $mgLink['po_headerlogo'] = $dealer_merchant->corporate_logo;
            }
        }

        \Log::info([
            "Log name" => "PO header and footer",
            "if Logo" => $merchantglobal->po_has_logo ?? "Logo not activated",
            "Logo" => $dealer_merchant->corporate_logo,
            "data" => $mgLink
        ]);

        DB::table('mglink_po')->insert($mgLink);


        //dd($code);
        return response()->json(['msg' => 'Request send successfully', 'status' => 'true'
            , 'code' => $code, 'qr' => $qr, 'systemid' => $systemid,
            'POId' => $PurchaseOrder->id]);
    }

    public function showDocumentSupplierView($merchant_id)
    {
        $user_data = new UserData();

        $ids = merchantproduct::where('merchant_id',
            $user_data->company_id())->pluck('product_id');

        $inventory_data = product::whereIn('ptype',
            ['inventory', 'rawmaterial', 'warranty', 'oilgas'])->
        whereNotNull('name')->whereIn('id', $ids)->get();

        $company_detail = \App\Models\Company::where('id',
            $merchant_id)->first();

        $my_company_detail = \App\Models\Company::find($user_data->company_id());

        $issuer_merchant_id = $my_company_detail->id;

        $currency = \App\Models\Currency::where('id',
            $my_company_detail->currency_id)->first();

        if (empty($currency)) {
            $currency = \App\Models\Currency::where('code', "MYR")->first();
        }

        $merchantlinkrelation = DB::table('merchantlinkrelation')->
        join('merchantlink', 'merchantlink.id', '=', 'merchantlinkrelation.merchantlink_id')->
        where([
            'merchantlink.initiator_user_id' => $my_company_detail->owner_user_id,
            'merchantlink.responder_user_id' => $company_detail->owner_user_id,
        ])->
        orWhere([
            'merchantlink.responder_user_id' => $my_company_detail->owner_user_id,
            'merchantlink.initiator_user_id' => $company_detail->owner_user_id,
        ])->
        where([
            'merchantlinkrelation.ptype' => 'dealer',
            'merchantlinkrelation.company_id' => $my_company_detail->id
        ])->
        orderBy('merchantlinkrelation.created_at', 'desc')->
        first();

        $warranty_prd_ids = product::whereIn('ptype',
            ['warranty'])->
        whereNotNull('name')->
        whereIn('id', $ids)->pluck('id');

        $pro_prd_ids = product::whereIn('ptype',
            ['customization'])->
        whereNotNull('name')->
        whereNotNull('prdcategory_id')->
        whereIn('id', $ids)->pluck('id');

        $pro_inventory_ids = product::whereIn('ptype',
            ['inventory'])->
        whereNotNull('name')->
        whereNotNull('photo_1')->
        whereNotNull('prdcategory_id')->
        whereNotNull('prdsubcategory_id')->
        whereNotNull('prdprdcategory_id')->
        whereIn('id', $ids)->pluck('id');
        // followed prices from wholesales
        $inventory_idx = product::whereIn('ptype',
            ['inventory'])->
        whereNotNull('name')->
        whereIn('id', $ids)->pluck('id');

        $warranty_price = warranty::
        whereNull('deleted_at')->
        whereIn('product_id', $warranty_prd_ids)->pluck('price', 'product_id');

        // followed prices from pro service
        $proservice_price = prd_proservices::
        whereNull('deleted_at')->
        whereIn('product_id', $pro_prd_ids)->pluck('price', 'product_id');

        // followed prices from inventory
        $inventory_price = prd_inventory::
        whereNull('deleted_at')->
        whereIn('product_id', $pro_inventory_ids)->pluck('price', 'product_id');
        foreach ($warranty_price as $key => $value) {
            $in_float = $value / 100;
            // $warranty_price[$key] = is_int($in_float) ? $in_float.'.00' : $in_float;
            $warranty_price[$key] = $in_float;
        }


        foreach ($proservice_price as $key => $value) {
            $in_float = $value / 100;
            // $warranty_price[$key] = is_int($in_float) ? $in_float.'.00' : $in_float;
            $proservice_price[$key] = number_format($in_float, 2);
        }

        /*
                foreach ($inventory_data as $key => $value) {
                    # code...
                    if($value->ptype =='inventory'){
                        if( empty($inventory_price[$value->id])){
                         unset($inventory_data[$key]);

                        }
                    }
                    else if($value->ptype =='warranty'){
                        if( empty($warranty_price[$value->id])){
                         unset($inventory_data[$key]);

                        }
                    }
                    else if($value->ptype =='customization'){
                        if( empty($proservice_price[$value->id])){
                         unset($inventory_data[$key]);

                        }
                    }
                }
         */
        $wholesale_prices = new Wholesale();
        $product_whole_sale_price_and_range = [];

        foreach ($inventory_idx as $key => $value) {
            $wholesale = $wholesale_prices->where('product_id', $value)->get()->toArray();

            if (count($wholesale) !== 0) {
                $price_in_float = $wholesale[0]['price'] / 100;
                $per_item_whole_sale_price = $price_in_float;  /// $wholesale[0]['unit']
                $wholesale[0]['per_item_whole_sale_price'] = number_format($per_item_whole_sale_price, 2);
            } else {
                $wholesale[0]['per_item_whole_sale_price'] = "0.00";
                $wholesale[0]['price'] = "0.00";
            }


            $product_whole_sale_price_and_range[$value] = $wholesale;
        }


        $fuel_ids = $inventory_data->where('ptype', 'oilgas')->pluck('id')->toArray();
        $product_oil_gas_price = [];

        foreach ($fuel_ids as $f) {

            $ogFuelPrice = DB::table('prd_ogfuel')->where('product_id', $f)->first()->wholesale_price ?? 0;
            /*
            DB::table('og_fuelprice')->join('prd_ogfuel','prd_ogfuel.id',
                '=','og_fuelprice.ogfuel_id')->
            whereDate('og_fuelprice.start' , '<=',\Carbon\Carbon::today())->
            orderBy('og_fuelprice.start', 'desc')->
            where('prd_ogfuel.product_id', $f)->
            select("og_fuelprice.*")->
            first()->price ?? 0;*/

            $product_oil_gas_price[$f] = number_format($ogFuelPrice / 100, 2);
        }

        $ids = merchantlocation::where('merchant_id', $issuer_merchant_id)->pluck('location_id');
        $location = location::where([['branch', '!=', 'null']])->whereIn('id', $ids)->latest()->get();

        return view('data.documentsupplier', compact(
            'inventory_data', 'merchant_id', 'company_detail',
            'my_company_detail', 'product_whole_sale_price_and_range', 'wholesale_prices',
            'currency', 'issuer_merchant_id',
            'warranty_price', 'product_oil_gas_price',
            'proservice_price',
            'location', 'merchantlinkrelation'
        ));

    }


    public function showDocumentDealerView($merchant_id)
    {
//        flag:
        $user_data = new UserData();

        $id = Auth::user()->id;

        $ids = merchantproduct::where('merchant_id',
            $user_data->company_id())->pluck('product_id');

        $inventory_data = product::whereIn('ptype',
            ['inventory', 'customization', 'oilgas'])->
        whereNotNull('name')->
        whereIn('id', $ids)->get();
        // ['inventory', 'rawmaterial', 'warranty'] // remove rawmaterial from products for now.

        $company_detail = \App\Models\Company::where('id',
            $merchant_id)->first();

        $my_company_detail = \App\Models\Company::where('id',
            $user_data->company_id())->first();

        $currency = \App\Models\Currency::where('id', $my_company_detail->currency_id)->first();
        if (empty($currency)) {
            $currency = \App\Models\Currency::where('code', "MYR")->first();
        }

        $warranty_prd_ids = product::whereIn('ptype',
            ['warranty'])->
        whereNotNull('name')->
        whereIn('id', $ids)->pluck('id');

        $pro_prd_ids = product::whereIn('ptype',
            ['customization'])->
        whereNotNull('name')->
        whereNotNull('prdcategory_id')->
        whereIn('id', $ids)->pluck('id');

        $pro_inventory_ids = product::whereIn('ptype',
            ['inventory'])->
        whereNotNull('name')->
        whereNotNull('photo_1')->
        whereNotNull('prdcategory_id')->
        whereNotNull('prdsubcategory_id')->
        whereNotNull('prdprdcategory_id')->
        whereIn('id', $ids)->pluck('id');

        // followed prices from warranty
        $warranty_price = warranty::
        whereNull('deleted_at')->
        whereIn('product_id', $warranty_prd_ids)->pluck('price', 'product_id');

        // followed prices from pro service
        $proservice_price = prd_proservices::
        whereNull('deleted_at')->
        whereIn('product_id', $pro_prd_ids)->pluck('price', 'product_id');

        // followed prices from inventory
        $inventory_price = prd_inventory::
        whereNull('deleted_at')->
        whereIn('product_id', $pro_inventory_ids)->pluck('price', 'product_id');


        // Convert to original - float
        foreach ($warranty_price as $key => $value) {
            $in_float = $value / 100;
            // $warranty_price[$key] = is_int($in_float) ? $in_float.'.00' : $in_float;
            $warranty_price[$key] = $in_float;
        }


        foreach ($proservice_price as $key => $value) {
            $in_float = $value / 100;
            // $warranty_price[$key] = is_int($in_float) ? $in_float.'.00' : $in_float;
            $proservice_price[$key] = number_format($in_float, 2);
        }

        /*
        foreach ($inventory_data as $key => $value) {
            # code...
            if($value->ptype =='inventory'){
                if( empty($inventory_price[$value->id])){
                 unset($inventory_data[$key]);

                }
            }
            else if($value->ptype =='warranty'){
                if( empty($warranty_price[$value->id])){
                 unset($inventory_data[$key]);

                }
            }
            else if($value->ptype =='customization'){
                if( empty($proservice_price[$value->id])){
                 unset($inventory_data[$key]);

                }
            }
            
        }*/

        // followed prices from wholesales
        $inventory_idx = product::whereIn('ptype',
            ['inventory'])->
        whereNotNull('name')->
        whereIn('id', $ids)->pluck('id');

        $wholesale_prices = new Wholesale();
        $product_whole_sale_price_and_range = [];

        foreach ($inventory_idx as $key => $value) {
            $wholesale = $wholesale_prices->where('product_id', $value)->get()->toArray();

            if (count($wholesale) !== 0) {
                $price_in_float = $wholesale[0]['price'] / 100;
                $per_item_whole_sale_price = $price_in_float;  /// $wholesale[0]['unit']
                $wholesale[0]['per_item_whole_sale_price'] = number_format($per_item_whole_sale_price, 2);
            } else {
                $wholesale[0]['price'] = "0.00";
                $wholesale[0]['per_item_whole_sale_price'] = "0.00";
            }


            $product_whole_sale_price_and_range[$value] = $wholesale;
        }

        $fuel_ids = $inventory_data->where('ptype', 'oilgas')->pluck('id')->toArray();
        $product_oil_gas_price = [];

        foreach ($fuel_ids as $f) {

            $ogFuelPrice = DB::table('prd_ogfuel')->where('product_id', $f)->first()->wholesale_price ?? 0;
            /*
            DB::table('og_fuelprice')->
            join('prd_ogfuel','prd_ogfuel.id',
                '=','og_fuelprice.ogfuel_id')->
            whereDate('og_fuelprice.start' , '<=',\Carbon\Carbon::today())->
            orderBy('og_fuelprice.start', 'desc')->
            where('prd_ogfuel.product_id', $f)->
            select("og_fuelprice.*")->
            first()->price ?? 0;*/

            $product_oil_gas_price[$f] = number_format($ogFuelPrice / 100, 2);
        }

        $merchantlinkrelation = DB::table('merchantlinkrelation')->
        join('merchantlink', 'merchantlink.id', '=', 'merchantlinkrelation.merchantlink_id')->
        where([
            'merchantlink.initiator_user_id' => $my_company_detail->owner_user_id,
            'merchantlink.responder_user_id' => $company_detail->owner_user_id,
        ])->
        orWhere([
            'merchantlink.responder_user_id' => $my_company_detail->owner_user_id,
            'merchantlink.initiator_user_id' => $company_detail->owner_user_id,
        ])->
        where([
            'merchantlinkrelation.ptype' => 'supplier',
            'merchantlinkrelation.company_id' => $my_company_detail->id
        ])->
        orderBy('merchantlinkrelation.created_at', 'desc')->
        first();

        $ids = merchantlocation::where('merchant_id', $my_company_detail->id)->pluck('location_id');
        $location = location::where([['branch', '!=', 'null']])->whereIn('id', $ids)->latest()->get();
        return view('data.documentdealer', compact(
            'my_company_detail',
            'inventory_data',
            'merchant_id',
            'company_detail',
            'warranty_price',
            'proservice_price',
            'currency',
            'product_whole_sale_price_and_range',
            'product_oil_gas_price',
            'location',
            'merchantlinkrelation'
        ));
    }

    public function viewDocumentInventoryCost($inventoryCostId)
    {
        $inventoryCost = inventorycost::find($inventoryCostId);
        if ($inventoryCost != null) {

            //$id = Auth::user()->id;
            $id = $this->getCompanyUserId();
            $user_data = new UserData();
            $user_data->exit_merchant();
            $user_roles = usersrole::where('user_id', $id)->get();
            $is_king = \App\Models\Company::where('owner_user_id',
                Auth::user()->id)->first();

            $is_king = $is_king != null ? true : false;

            $inventory_data = inventorycostproduct::select('quantity', 'cost', 'product.*')->
            join('product', 'product.id', '=', 'inventorycostproduct.product_id')->
            where('inventorycostproduct.inventorycost_id', $inventoryCost->id)->get();

            $merchant_data = \App\Models\Company::find($inventoryCost->buyer_merchant_id);
            //Merchant::select('company.name', 'company.systemId')->
            //	leftjoin('company','company.id','=','merchant.company_id')->
            //	where('merchant.id', $inventoryCost->buyer_merchant_id)->first();

            return view('data.viewDocumentInventoryCost', compact(
                'user_roles', 'is_king', 'inventory_data', 'merchant_data', 'inventoryCost'
            ));
        }
    }


    public function showDocumentInventoryCostView($merchant_id)
    {

        //$id = Auth::user()->id;
        $id = $this->getCompanyUserId();
        $user_data = new UserData();
        // $user_data->exit_merchant();
        $user_roles = usersrole::where('user_id', $id)->get();
        $is_king = \App\Models\Company::where('owner_user_id',
            Auth::user()->id)->first();

        $is_king = $is_king != null ? true : false;

        $ids = merchantproduct::where('merchant_id',
            $user_data->company_id())->pluck('product_id');

        $inventory_data = product::whereIn('ptype', ['inventory', 'rawmaterial'])->
        whereNotNull('name')->
        whereIn('id', $ids)->get();

        $merchant_data = Company::find($merchant_id);
        //	Merchant::select('company.name', 'company.systemId')->
        //	leftjoin('company','company.id','=','merchant.company_id')->
        //	where('merchant.id', $merchant_id)->
        //	first();

        return view('data.inventoryCost', compact(
            'user_roles', 'is_king', 'inventory_data', 'merchant_id', 'merchant_data'
        ));
    }


    public function showConsignmentView()
    {
        // $id = $this->getCompanyUserId();
        // $user_data = new UserData();
        // $user_data->exit_merchant();
        // $user_roles = usersrole::where('user_id',$id)->get();
        // $is_king =  \App\Models\Company::where('owner_user_id',Auth::user()->id)->first();
        // $is_king = $is_king != null ? true : false;
        return view('consignment.consignment');
        // ,compact('user_roles','is_king', 'merchant_id')

    }

    public function showDocumentInventoryCostLoadData()
    {
        try {
            $prd_inventory_ids = prd_inventory::where([['price', '!=', 'NULL']])->
            pluck('product_id')->toArray();

            $prd_rawmaterial_ids = rawmaterial::where([['price', '!=', 'NULL']])->
            pluck('product_id')->toArray();

            $filter = array_merge($prd_inventory_ids, $prd_rawmaterial_ids);
            $data = product::where([['prdcategory_id', '!=', 'NULL'],
                ['name', '!=', 'NULL']])->
            whereIn('id', $filter)->
            latest()->get();

            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('product_id', function ($product) {
                    return '<p style="padding:0;margin:0;">' . $product->systemid . '</p>';
                })
                ->addColumn('product_name', function ($product) {
                    if (!empty($product->thumbnail_1)) {
                        $img_src = '/images/product/' . $product->id . '/thumb/' . $product->thumbnail_1;
                        $img = "<img src='$img_src' data-field='product_name' style=' width: 25px;height: 25px;display: inline-block;margin-right: 8px;object-fit:contain;'>";
                    } else {
                        $img = null;
                    }
                    return $img . '<p class="os- linkcolor" data-field="restaurantnservices_pro_name" style="cursor: pointer; margin: 0;display: inline-block;color:#007bff;padding:0;">' . (!empty($product->name) ? $product->name : 'Product Name') . '</p>';
                })
                ->addColumn('product_price', function ($product) {
                    return '<a href="JavaScript:void(0)" class="priceOutput currentprice" data-toggle="modal" data-target="#costPriceModal" style="color: #007bff;margin:0;text-decoration:none;" >0.00</a>';
                })
                ->addColumn('product_qty', function ($product) {
                    return '<a href="JavaScript:void(0)" class="qtyOutput" data-toggle="modal" data-target="#costQtyModal" style="color: #007bff;margin:0;text-decoration:none;" >1</a>';
                })
                ->addColumn('product_amount', function ($location) {
                    return '<p class="product_amount" style="padding:0;margin:0;">0</p>';
                })
                ->escapeColumns([])
                ->make(true);

        } catch (\Exception $e) {
            Log::error(
                "Error @ " . $e->getLine() . " file " . $e->getFile() . ":" . $e->getMessage()
            );
        }
    }


    public function save_unconfirmed_data(Request $request)
    {
        Log::debug('location_id=' . $request->location_id);
        Log::debug('dest_location_id=' . $request->dest_location_id);

        $systemid = new SystemID('stockreport');

        Log::debug('systemid=' . $systemid->__toString());

        $stockreport = new stockreport();

        $stockreport->systemid = $systemid->__toString();
        $stockreport->creator_user_id = Auth::user()->id;
        $stockreport->type = 'transfer';
        $stockreport->location_id = $request->location_id;
        $stockreport->dest_location_id = $request->dest_location_id;

        $stockreport->save();

        Log::debug('stockreportaftersave=' . json_encode($stockreport));

        foreach ($request->products as $product) {
            $stockreportproduct = new stockreportproduct();
            $stockreportproduct->product_id = $product['id'];
            $stockreportproduct->quantity = $product['qty'];
            $stockreportproduct->received = $product['qty'];
            $stockreportproduct->stockreport_id = $stockreport->id;
            $stockreportproduct->save();
        }
        $response = [
            'data' => 'success',
            'report_id' => $stockreport->id
        ];
        return response()->json($response);
    }


    public function showDocumenttrackingConfirmView($stockreport_id, Request $request)
    {
        $this->user_data = new UserData();
        //$idd = Auth::user()->id;

        $id = Auth::user()->id;
        $user_data = new UserData();

        $user_roles = usersrole::where('user_id', $id)->get();

        $is_king = \App\Models\Company::where('owner_user_id', Auth::user()->id)->first();

        if ($is_king != null) {
            $is_king = true;
        } else {
            $is_king = false;
        }

        $stockreport = StockReport::find($stockreport_id);
        $stockreport->created_date = Carbon::parse($stockreport->created_at)->format('dMy H:i:s');
        $stockreport->received_tstamp = !empty($stockreport->received_tstamp) ? Carbon::parse($stockreport->received_tstamp)->format('dMy H:i:s') : '';

        $stockreport->location_from = location::find($stockreport->location_id);
        $stockreport->location_to = location::find($stockreport->dest_location_id);

        $stockreport->creator_user = User::find($stockreport->creator_user_id);
        $stockreport->receiver_user = !empty($stockreport->receiver_user_id) ? User::find($stockreport->receiver_user_id) : '';
        //dd($stockreport->receiver_user);

        $stockreportproducts = stockreportproduct::where('stockreport_id', $stockreport->id)->get();


//        print_r(  Auth::user()->name );exit;
        $this->user_data = new UserData();
        $ids = merchantlocation::where('merchant_id',
            $this->user_data->company_id())->
        pluck('location_id');

        $locations = location::where([['branch', '!=', 'null']])->
        whereIn('id', $ids)->latest()->
        get();

        Log::debug('stockreport  =' . json_encode($stockreport));
        Log::debug('location_from=' . $stockreport->location_from);
        Log::debug('location_to  =' . $stockreport->location_to);

        return view('data.trackingconfirm', compact(
            'stockreport', 'stockreportproducts', 'locations', 'user_roles', 'is_king'
        ));
    }


    public function save_received_report_data(Request $request)
    {
        $stockreport = StockReport::find($request->stockreport_id);
        $stockreport->status = 'confirmed';
        $stockreport->receiver_user_id = Auth::user()->id;
        $stockreport->received_tstamp = Carbon::now()->toDateTimeString();
        $stockreport->save();

        foreach ($request->products as $product) {
            $stockreportproduct = stockreportproduct::find($product['id']);
            $stockreportproduct->received = $product['received'];
            $stockreportproduct->status = !empty($product['received']) ? 'checked' : 'unchecked';
            $stockreportproduct->save();
        }

        return 'Staff Name: ' . Auth::user()->name . ' <br>
                Staff ID: ' . Auth::user()->staff->systemid . ' <br>
                Date: ' . Carbon::parse($stockreport->received_tstamp)->format('dMy H:i:s');

    }

    public function showInventoryPriceRange(Request $request)
    {
        $product_id = (int)$request->productId;

        $product_whole_sale_price_and_range = Wholesale::
        where('product_id', $product_id)
            ->get()
            ->toArray();

        return view('data.inventory-price-range-modals', compact([
            'product_whole_sale_price_and_range']));
    }

    public function voidDocument(Request $request)
    {
        try {

            $validation = Validator::make($request->all(), [
                "document_type" => "required",
                "document_key" => "required"
            ]);

            if ($validation->fails())
                throw new \Exception("validation_fails");

            $update_array = [];
            $update_array['is_void'] = true;
            $update_array['void_user_id'] = Auth::User()->id;
            $update_array['void_reason'] = $request->void_reason ?? '';

            switch ($request->document_type) {
                case 'salesorder':
                    $salesorder = DB::table('salesorder')->
                    where('systemid', $request->document_key)->
                    first();

                    //is void
                    if ($salesorder->is_void == 1)
                        return response()->json([
                            "msg" => "This is an existing void sales order."
                        ]);

                    //invoice
                    if (!empty(DB::table('invoice')->
                    join('salesorderdeliveryorder',
                        'salesorderdeliveryorder.deliveryorder_id', 'invoice.deliveryorder_id')->
                    where('salesorderdeliveryorder.salesorder_id', $salesorder->id)->
                    first()))
                        return response()->json([
                            "msg" => "Unable to void the sales order, delivery order and invoice has been issued."]);


                    DB::table('salesorder')->
                    where([
                        "systemid" => $request->document_key
                    ])->update($update_array);

                    break;
                case 'purchaseorder':
                    $purchaseOrder = DB::table('purchaseorder')->
                    where('systemid', $request->document_key)->first();


                    if ($purchaseOrder->is_void == 1)
                        return response()->json([
                            "msg" => "This is an existing void purchase order."
                        ]);

                    if (!empty(DB::table('purchaseorderdeliveryorder')->
                    where('purchaseorder_id', $purchaseOrder->id)->first()))
                        return response()->json([
                            "msg" => "Unable to void the sales order, delivery order and invoice have been issued."]);

                    DB::table('purchaseorder')->
                    where([
                        "systemid" => $request->document_key
                    ])->update($update_array);

                    break;
                case 'invoice':
                    $invoice = DB::table('invoice')->
                    where('systemid', $request->document_key)->first();

                    if ($invoice->is_void == 1)
                        return response()->json([
                            "msg" => "This is an existing void invoice."
                        ]);

                    $is_anyPayment = DB::table('arpaymentinv')->
                    where('invoice_id', $invoice->id)->first();

                    if (!empty($is_anyPayment))
                        return response()->json([
                            "msg" => "There is a payment record for this invoice."
                        ]);

                    DB::table('invoice')->
                    where([
                        "systemid" => $request->document_key
                    ])->update($update_array);

                    break;
                case 'debitnote':
                    $debitnote = DB::table('debitnote')->
                    where('systemid', $request->document_key)->first();

                    if ($debitnote->is_void == 1)
                        return response()->json([
                            "msg" => "This is an existing void debit note."
                        ]);


                    DB::table('debitnote')->
                    where([
                        "systemid" => $request->document_key
                    ])->update($update_array);

                    break;
                case 'creditnote':

                    $creditnote = DB::table('creditnote')->
                    where('systemid', $request->document_key)->first();

                    if ($creditnote->is_void == 1)
                        return response()->json([
                            "msg" => "This is an existing void credit note."
                        ]);

                    DB::table('creditnote')->
                    where([
                        "systemid" => $request->document_key
                    ])->update($update_array);


                    break;

                case 'receipt':

                    $arpayment = DB::table('arpayment')->
                    where('systemid', $request->document_key)->first();

                    if ($arpayment->is_void == 1)
                        return response()->json([
                            "msg" => "This is an existing void receipt."
                        ]);

                    DB::table('arpayment')->
                    where([
                        "systemid" => $request->document_key
                    ])->update($update_array);

                    break;
            }

            return response()->json(["status" => true]);

        } catch (\Exception $e) {
            \Log::info([
                "Error" => $e->getMessage(),
                "File" => $e->getFile(),
                "Line" => $e->getLine()
            ]);
        }
    }
}
