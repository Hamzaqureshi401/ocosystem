<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EODController extends Controller
{
    //
    
    public function getReceiptFromLocal(Request $request)
    { 

        $receipt = $request->receipt;
        $receipt_details = $request->receipt_details;
        $receipt_product = $request->receipt_product;
        $user_details = $request->user;
        $itemdetails = $request->itemdetails;
        Log::debug('User '.json_encode($request->user));

        $user = DB::table('staff')
                ->where('systemid', $user_details['systemid'])
                ->first();

        Log::debug('Received Receipt: '.json_encode($user));

        
        $array = ['systemid'=>$receipt['systemid'], 
                'cash_received'=>$receipt['cash_received'], 
                'servicecharge'=>$receipt['servicecharge'], 
                'payment_type'=>$receipt['payment_type'], 
                'terminal_id'=>$receipt['terminal_id'], 
                'staff_user_id'=>$user->user_id,
                'creditcard_no'=>$receipt['creditcard_no'], 
                'company_id'=>$user->company_id, 
                'mode'=>$receipt['mode'], 
                'status'=>$receipt['status'],
                'remark'=>$receipt['remark'], 
                'receipt_address'=>$receipt['receipt_address'], 
                'currency'=>$receipt['currency']];
       $rID = DB::table('opos_receipt')
            ->insertGetId($array);

        $data_details=['receipt_id'=>$rID,
                        'total'=>$receipt_details ['total'],
                        'rounding'=>$receipt_details['rounding'],
                        'item_amount'=>$receipt_details['item_amount'],
                        'sst'=>$receipt_details['sst'],
                        'discount'=>$receipt_details['discount'],
                        'cash_received'=>$receipt_details['cash_received'],
                        'change'=>$receipt_details['change'],
                        'creditcard'=>$receipt_details['creditcard'],
                        'void'=>$receipt_details['void']
        ];
        
        DB::table('opos_receiptdetails')
                ->insert($data_details);

        $data_product =[
                        'product_id'=>$receipt_product['product_id'],
                        'receipt_id'=>$rID,
                        'name'=>$receipt_product['name'],
                        'quantity'=>$receipt_product['quantity'],
                        'price'=>$receipt_product['price'],
                        'discount_pct'=>$receipt_product['discount'],
                        'discount'=>$receipt_product['discount'],
                        'created_at'=>$receipt['created_at'],
                        'updated_at'=>$receipt['updated_at']
        ];

        $rpID = DB::table('opos_receiptproduct')
                ->insertGetId($data_product);

        $item_details= [
                        'receiptproduct_id'=>$rpID,
                        'amount'=>$itemdetails['amount'],
                        'rounding'=>$itemdetails['rounding'],
                        'price'=>$itemdetails['price'],
                        'sst'=>$itemdetails['sst'],
                        'discount'=>$itemdetails['discount'],
                        'service_charge'=>$receipt['servicecharge']??0,
                        'created_at'=>$itemdetails['created_at'],
                        'updated_at'=>$itemdetails['updated_at']
        ];

        DB::table('opos_itemdetails')
                ->insert($item_details);
    }
}
