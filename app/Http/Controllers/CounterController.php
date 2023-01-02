<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\KitchenPrinter;
use App\Models\Merchant;
use App\Models\PlatSubCat;
use App\Models\plat_counter;
use App\Models\plat_devprinter;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Log;
use Matrix\Exception;
use Yajra\DataTables\DataTables;
use \App\Classes\UserData;
use \App\Models\locationterminal;
use \App\Models\merchantprd_category;
use \App\Models\prd_subcategory;
use \App\Models\terminal;
use \App\Classes\SystemID;

use \App\Models\plat_countersubcat1;



class CounterController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $terminal = terminal::where('systemid', $request->terminal_id)->first();
        Log::debug("terminal_id=".$terminal->id);
        $data = plat_counter::where('terminal_id', $terminal->id)->orderby('created_at', 'desc')->get();

        $this->user_data = new UserData();
        $company_id      = $this->user_data->company_id();
        $category_ids    = merchantprd_category::where('merchant_id', $company_id)->pluck('category_id');
        $sub_cat_ids     = prd_subcategory::whereIn('category_id', $category_ids)->pluck('id');
        $this->sub_cat_ids = $sub_cat_ids;

        return Datatables::of($data)
            ->addIndexColumn()
            ->addColumn('counter_id', function ($memberList) {
                return '<p data-field="counter_id" style="margin: 0;text-align: center;">'.$memberList->systemid.'</p>';
            })
            ->addColumn('counter_name', function ($data) {
                return '<p class="os-linkcolor" data-field="counter_name" style="margin: 0;cursor:pointer">' . (empty($data->name) ? 'Name' : $data->name) . '</p>';
            })
            ->addColumn('counter_device', function ($data) {

                return '<p class="os-linkcolor" data-field="counter_device" style="margin: 0;text-align: center;cursor:pointer">' . (empty($data->devprinter->print_queue) ? 'Name' : $data->devprinter->print_queue) . '</p>';
            })
            ->addColumn('counter_cat', function ($data) {
                $sub_cat = plat_countersubcat1::where('counter_id', $data->id)->whereIn('subcat1_id',$this->sub_cat_ids)->get()->ToArray();
                $count = count($sub_cat);
                return '<p class="os-linkcolor" data-field="counter_cat" style="margin: 0;text-align: center;cursor:pointer">'.$count.'</p>';
            })
            ->addColumn('deleted', function ($data) {
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

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($terminal_id)
    {
        try {

            $terminal = terminal::where('systemid', $terminal_id)->first();
            if (!$terminal) {return abort(404);}
            $opos_locationterminal = locationterminal::where('terminal_id', $terminal->id)->first();

            $systemid = new SystemID('counter');

            $plat_counter              = new plat_counter();
            $plat_counter->systemid    = $systemid;
            $plat_counter->location_id = $opos_locationterminal->location_id;
            $plat_counter->terminal_id = $terminal->id;
            $plat_counter->save();

            $plat_devprinter             = new plat_devprinter();
            $plat_devprinter->counter_id = $plat_counter->id;
            $plat_devprinter->save();

            $msg = "Counter added successfully";

        } catch (\Exception $e) {

            if ($e->getMessage() == 'validation_error') {
                return '';
            } else {
                $msg = "Error occured while adding new counter";
            }

            Log::error(
                "Error @ " . $e->getLine() . " file " . $e->getFile() . ":" . $e->getMessage()
            );
        }

        return view('layouts.dialog', compact('msg'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $message = '';
        $data    = KitchenPrinter::where('print_queue', $request->name)->
            where('counter_id', $request->counter_id)->get()->last();

        $data1 = KitchenPrinter::where('counter_id', $request->counter_id)->get();

        if (count($data1) <= 0) {
            $data              = new KitchenPrinter();
            $data->counter_id  = $request->counter_id;
            $data->print_queue = $request->name;
//            $data->ipaddr = $request->ipaddr;
            //            $data->hwaddr = $request->hwaddr;
            $data->save();
            $message = "New printer selected successfully";

        } else if ($data) {
            $delPrinter1 = KitchenPrinter::find($data->id);
            if ($delPrinter1) {
                $delPrinter1->delete();
                $message = 'Printer deselected successfully';
            }

        } else if ($data1) {
            foreach ($data1 as $dt) {
                $delPrinter = KitchenPrinter::find($dt->id);
                $delPrinter->delete();
            }
            $data              = new KitchenPrinter();
            $data->counter_id  = $request->counter_id;
            $data->print_queue = $request->name;
//            $data->ipaddr = $request->ipaddr;
            //            $data->hwaddr = $request->hwaddr;
            $data->save();
            $message = 'Printer Changed successfully';
        }

        return response(['message' => $message]);
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
                $plat_counter = plat_counter::find($request->id);
                return view('plat_counter.plat_counter-modals', compact('plat_counter', 'fieldName'));

            }

        } catch (\Exception $e) {

            if ($e->getMessage() == 'validation_error') {
                return '';
            } else {
                $msg = "Error occured while adding new counter";
            }

            Log::error(
                "Error @ " . $e->getLine() . " file " . $e->getFile() . ":" . $e->getMessage()
            );
        }
    }

    public function getPrinterStatus(Request $request)
    {
        $data = KitchenPrinter::all();
        return response(['dat' => $data]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        try {
            $allInputs       = $request->all();
            $plat_counter_id = $request->get('plat_counter_id');
            $changed         = false;

            $validation = Validator::make($allInputs, [
                'plat_counter_id' => 'required',
            ]);

            if ($validation->fails()) {
                throw new Exception("product_not_found", 1);
            }

            $plat_counter = plat_counter::find($plat_counter_id);

            if (!$plat_counter) {
                throw new Exception("plat_counter_not_found", 1);
            }

            if ($request->has('name')) {
                if ($plat_counter->name != $request->name) {
                    $plat_counter->name = $request->name;
                    $changed            = true;
                    $msg                = "Name updated";
                }
            }

            Log::debug('**** Does counter have devprinter? ****');
			Log::debug($plat_counter->devprinter()->first()->print_queue);
            Log::debug('**** Requested devprinter ****');
			Log::debug($request->dev_name);

            if ($request->has('dev_name')) {
                $dev_printer = $plat_counter->devprinter()->first();

				Log::debug('dev_printer='.json_encode($dev_printer));

                if ($dev_printer->print_queue != $request->dev_name) {
                    $dev_printer->print_queue = $request->dev_name;
                    $dev_printer->save();
                    $changed = true;
                    $msg     = "Printer updated";
                }
            }

            if ($request->has('subcat_id')) {

                $is_exist = plat_countersubcat1::where(['counter_id'=>$plat_counter_id, "subcat1_id"=>$request->subcat_id])->first();

                if (!$is_exist) {
                $plat_countersubcat1 = new plat_countersubcat1();
                $plat_countersubcat1->counter_id = $plat_counter_id;
                $plat_countersubcat1->subcat1_id = $request->subcat_id;
                $plat_countersubcat1->save();
                $changed = true;
                $msg = "Sub-category added to counter";
                } else {
                $is_exist->delete();
                $changed = true;
                $msg = "Sub-category removed from counter";
                }
            }

            if ($changed == true) {
                $plat_counter->save();
                $response = view('layouts.dialog', compact('msg'));
            } else {
                $response = null;
            }

        } catch (\Exception $e) {
            if ($e->getMessage() == 'plat_counter_not_found') {
                $msg = "Counter not found";
            } else {
                $msg = "Some error occured";
            }

            // $msg = $e;
            $response = view('layouts.dialog', compact('msg'));
        }

        return $response;
    }

    public function catData($c_id)
    {
        try {
            $this->user_data = new UserData();
            $company_id      = $this->user_data->company_id();
            $category_ids    = merchantprd_category::where('merchant_id', $company_id)->pluck('category_id');
            $data            = prd_subcategory::whereIn('category_id', $category_ids)->get();
            $this->c_id = $c_id;
            $response        = Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('cat_name', function ($memberList) {
                    return '<p data-field="cat_name" class="bg-whiteprawn" style="cursor:pointer;text-align: center;margin: 0;border: 0;text-align:left">' . (empty($memberList->name) ? 'Name' : $memberList->name) . '</p>';
                })
                ->setRowClass(function($memberList) {
                    $is_exist = plat_countersubcat1::where(['counter_id'=>$this->c_id, "subcat1_id"=>$memberList->id])->first();
                    return $is_exist ? 'selected':'';
                })
                ->escapeColumns([])
                ->make(true);

        } catch (\Exception $e) {
            if ($e->getMessage() == 'plat_counter_not_found') {
                $msg = "Counter not found";
            } else {
                $msg = "Some error occured";
            }

            $msg      = $e;
            $response = view('layouts.dialog', compact('msg'));
        }
        return $response;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {

            // $this->user_data = new UserData();

            $is_exist = plat_counter::find($id);

            if (!$is_exist) {
                throw new Exception("Error Processing Request", 1);
            }

            $is_exist->delete();
            $msg = "Counter deleted successfully";

        } catch (\Illuminate\Database\QueryException $ex) {
            $msg = "Some error occured";
        }
        return view('layouts.dialog', compact('msg'));
    }

    public function counter_name($id, $bid)
    {
        $branch = DB::table('fairlocation')->
            where('id', $bid)->
            select(DB::raw("fairlocation.location as name"), 'id')->first();

        $user_id     = Auth::user()->id;
        $merchant    = Merchant::where('user_id', '=', $user_id)->first();
        $merchant_id = $merchant->id;
        $subcategory = DB::select(DB::raw(
            "select count(distinct(subcat_level_1.id)) as level1_id_count " .
            "from ( " .
            "select p.subcat_id, p.subcat_level, count(p.id) as pcount " .
            "from product as p " .
            "join merchantproduct as mp " .
            "on p.id = mp.product_id " .
            "and mp.merchant_id = " . $merchant_id . " " .
            // "join locationproduct as lp ".
            //     "on p.id = lp.product_id ".
            //     "and lp.location_id = ".$location_id." ".
            "GROUP BY p.subcat_id " .
            ") as subcat " .
            "join subcat_level_1 " .
            "on id = ( case " .
            "WHEN subcat_level = 1 then subcat_id " .
            "WHEN subcat_level = 2 then ( " .
            "select subcat_level_1_id " .
            "from subcat_level_2 as s2 " .
            "where s2.id = subcat_id) " .
            "WHEN subcat_level = 3 then ( " .
            "select subcat_level_1_id " .
            "from subcat_level_3 as s3 " .
            "where s3.id = subcat_id)" .
            "end ) " .
            // "GROUP by subcat_level_1.id ".
            "ORDER by subcat_level_1.name"));

        $selluser = User::find($user_id);
        if (count($subcategory) > 0 &&
            isset($subcategory[0]->level1_id_count)) {
            $subcat_count = $subcategory[0]->level1_id_count;

        } else {
            $subcat_count = 0;
        }

        $counters = DB::table('plat_counter')->select('plat_counter.*',
            DB::raw("(select plat_devprinter.print_queue from plat_devprinter
            where plat_counter.id=plat_devprinter.counter_id
            order by id desc limit 1) as printer_name"),
            DB::raw("(select count(plat_countersubcat1.counter_id) as total_cat
            from plat_countersubcat1
            where plat_counter.id=plat_countersubcat1.counter_id
            order by id desc limit 1) total_cats"))->
            where('terminal_id', $id)->
            orderBy('plat_counter.id', 'asc')->get();

        return view('seller.counter_name')->with('id', $id)
            ->with('counters', $counters)
            ->with('subcat_count', $subcat_count)
            ->with('selluser', $selluser)
            ->with('branch', $branch)->with('bid', $bid)
            ->with('id', $id);

    }

    public function add_counter_row(Request $request)
    {
        $terminalId = $request->id;
        $mode       = strtolower($request->mode);
        $mode       = strtr($mode, array('+' => '', ',' => '', ' ' => ''));
        $counter_id = DB::table('plat_counter')->
            insertGetId([
            'terminal_id' => $terminalId,
            'name'        => '', 'location_id' => $request->b_id, 'mode' => $mode,
        ]);

        Log::debug('mode=' . $mode);
        switch ($mode) {
            case "printer":
                DB::table('plat_devprinter')->
                    insert(['counter_id' => $counter_id]);
                break;

            case "jobcounter":
                DB::table('plat_devjobcounter')->
                    insert(['counter_id' => $counter_id]);
                break;

            case "dooraccess":
                break;

            case "thumbprint":
                break;

            default:
        }
    }

    public function deleteCounter($id)
    {
        $data = DB::table('plat_counter')->delete($id);

        if ($data) {
            return response(['deleted' => 'Delete']);
        } else {
            return response(['deleted' => 'Not']);
        }
    }

    public function postUpdateCountername(Request $request)
    {
        $updateData = DB::table('plat_counter')->
            where('id', $request->id)->
            update(['name' => $request->name]);

        return response(['msg' => 'Name updated successfully']);
    }

    /* Takes the list of order items from OpenBill (plat_openbill,
     * plat_openbillproduct), goes through each item and print the items
     * according to their respective selected counter printers, together
     * with special instructions */
    public function send_kitchen_printer(Request $request)
    {
        Log::info('***** send_kitchen_printer() *****');

        $orderedItems = DB::table('plat_openbillproduct')->
            select('plat_openbill.id as openbill_id',
            'product.id as p_id', 'product.retail_price as p_price',
            'plat_openbillproduct.quantity as qty',
            'product.subcat_id as subcat1_id',
            'plat_openbillproduct.id as p_bill_id',
            'product.name as p_name',
            'plat_openbill.terminal_id as terminal_id',
            'plat_countersubcat1.counter_id as counter_id',
            'plat_devprinter.print_queue as printer',
            'plat_openbillproduct.status as status1',
            'opos_ftype.fnumber as table_no')->
            join('plat_openbill', 'plat_openbillproduct.openbill_id', '=',
            'plat_openbill.id')->
            join('product', 'product.id', '=', 'plat_openbillproduct.product_id')->

            join('plat_countersubcat1', 'plat_countersubcat1.subcat1_id', '=',
            DB::raw("( case " .
                "when product.subcat_level = 1 then subcat_id " .
                "WHEN product.subcat_level = 2 then ( " .
                "select subcat_level_1_id " .
                "from subcat_level_2 as s2 " .
                "where s2.id = subcat_id) " .
                "WHEN subcat_level = 3 then ( " .
                "select subcat_level_1_id " .
                "from subcat_level_3 as s3 " .
                "where s3.id = subcat_id)" .
                "end ) "))->
            join('plat_devprinter', 'plat_devprinter.counter_id', '=',
            'plat_countersubcat1.counter_id')->
            join('opos_ftype', 'plat_openbill.ftype_id', '=', 'opos_ftype.id')->
            where('plat_openbillproduct.status', '<>', 'printed')->
            groupBy('product.id')->
            get();

        Log::debug('orderedItems=' . json_encode($orderedItems));

        $collection = collect();
        $mainArray  = [];

        foreach ($orderedItems as $item) {
            $display_price = 0;
            $display_price += $item->p_price;
            Log::debug('item=' . json_encode($item));

            $check    = $collection->where('printer', $item->printer);
            $specials = DB::table('plat_openbillproductspecial')->
                where('plat_openbillproductspecial.openbillproduct_id',
                $item->p_bill_id)->
                join('plat_special', 'plat_special.id', '=',
                'plat_openbillproductspecial.special_id')->
                groupBy('plat_special.id')->get();

            $mySpecial = '';
            foreach ($specials as $sp) {
                $display_price += $sp->price;
                $mySpecial .= '-' . $sp->name . '<br>';
            }

            $temp['printer']     = $item->printer;
            $temp['p_bill_id']   = $item->p_bill_id;
            $temp['status']      = $item->status1;
            $temp['terminal_id'] = $item->terminal_id;
            $temp['counter_id']  = $item->counter_id;
            $temp['table_no']    = $item->table_no;
            $temp['html']        = '<tr style="font-size:14px"><td>' .
            $item->p_name . '<br>' . $mySpecial .
            '</td><td style="font-size:14px" align="center">' .
            $item->qty . '</td><td style="font-size:14px" align="right"> MYR ' . number_format($display_price / 100, 2) . '</td></tr>';
            $collection->push($temp);
        }

        foreach ($collection as $cols) {
            $counters = DB::table('plat_counter')->
                where('id', $cols['counter_id'])->
                where('terminal_id', $cols['terminal_id'])->
                get();

            if (count($counters) > 0) {
                $counter = $counters[0];

                //generating html
                $html = '<html><header><style>
                body {font-family: Arial, Helvetica, sans-serif}
                </style></header>
                <body style="width:95%">
                <div>' .
                '<table style="width:100%;font-size:12px;' .
                'border-bottom: 1px solid #a0a0a0;">' .
                '<tr`>' .
                '<td style="font-size:12px">Counter:</td>' .
                '<td style="font-size:12px">' . $counter->name . '</td>' .
                '<td style="font-size:12px" align="right">' .
                sprintf("%05d", $counter->id) . '</td>' .
                '</tr>' .
                '<tr>' .
                '<td style="font-size:12px">Table:</td>' .
                '<td style="font-size:12px">' . $cols['table_no'] . '</td>' .
                '<td style="font-size:12px;text-align:right" align="right">' .
                date('dMy h:i:a') . '</td>' .
                    '</tr>' .
                    '</table>' .
                    '<table style="width:100%">' .
                    '<tbody>' .
                    $cols['html'] . '<br>' . $mySpecial .
                    '</tbody>' .
                    '</table>' .
                    '</td>' .
                    '.</tr>' .
                    '</table></div></body></html>';

                $temp['p_bill_id'] = $cols['p_bill_id'];
                $temp['html']      = $html;
                $temp['printer']   = $cols['printer'];
                $temp['status']    = $cols['status'];
                $temp['table_no']  = $cols['table_no'];
                //push in mainArray html and printer
                array_push($mainArray, $temp);
            }
        }
        //return response with printer name as an array of printer with html
        Log::debug('mainArray=' . json_encode($mainArray));
        $counters = DB::table('plat_counter')->
            select('plat_counter.*',
            DB::raw("(select plat_devprinter.print_queue
                from plat_devprinter
                where plat_counter.id=plat_devprinter.counter_id
                order by id desc limit 1) as printer_name"))->
            where('terminal_id', $request->terminal_id)->
            orderBy('plat_counter.id', 'asc')->get();

        return response([
            'dat'         => $mainArray,
            'counters'    => $counters,
            'terminal_id' => $request->terminal_id]);
    }

    public function updatePrinterStatus($id)
    {
        $updateStatus = DB::table('plat_openbillproduct')->
            where('id', $id)->update(['status' => 'printed']);
        return response([
            'msg'  => 'Updated',
            'data' => $updateStatus,
            'id'   => $id,
        ]);
    }

    public function getSubCatLevel1(Request $request)
    {
        $merchant    = Merchant::where('user_id', '=', Auth::user()->id)->first();
        $merchant_id = $merchant->id;
        $subcatdata  = DB::select(DB::raw(
            "select distinct count(distinct(product_id)) as productcount,
            subcat.subcat_id, subcat_level, " .
            "subcat_level_1.name,subcat_level_1.id as level1_id " .
            "from ( " .
            "select p.subcat_id, p.subcat_level, p.id as product_id " .
            "from product as p " .
            "join merchantproduct as mp " .
            "on p.id = mp.product_id " .
            "and mp.merchant_id = " . $merchant_id . " " .
            ") as subcat " .
            "join subcat_level_1 " .
            "on id = ( case " .
            "when subcat_level = 1 then subcat_id " .
            "WHEN subcat_level = 2 then ( " .
            "select subcat_level_1_id " .
            "from subcat_level_2 as s2 " .
            "where s2.id = subcat_id) " .
            "WHEN subcat_level = 3 then ( " .
            "select subcat_level_1_id " .
            "from subcat_level_3 as s3 " .
            "where s3.id = subcat_id)" .
            "end ) " .
            "GROUP by subcat_level_1.id " .
            "ORDER by subcat_level_1.name"));

        $thisCounter = PlatSubCat::where('counter_id', $request->counter_id)->
            get();

        return response([
            'dat'          => $subcatdata,
            'this_counter' => $thisCounter,
        ]);
    }

    public function postUpdateCountercat(Request $request)
    {
        $update = PlatSubCat::where('counter_id', $request->id)->
            where('subcat1_id', $request->cat_id)->
            get()->last();

        if ($update && $request->check == 'true') {
            return response(['msg' => 'Already Selected']);

        } elseif ($request->check == 'false' && ($update)) {
            $delete = PlatSubCat::find($update->id);
            $delete->delete();
            return response(['msg' => 'Category Unselected Successfully..']);

        } else {
            $newCat             = new PlatSubCat();
            $newCat->counter_id = $request->id;
            $newCat->subcat1_id = $request->cat_id;
            $newCat->save();
            return response(['success' =>
                'Category Selected Successfully',
                'data'                     => $newCat]);
        }
    }
}
