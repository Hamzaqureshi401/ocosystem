<?php

namespace App\Http\Controllers;

use Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class CStoreController extends Controller
{
    //
    public function showcstoreView(){

        return view('virtualcabinet.cstore');
    }

    public function StoreCstoreReceipt($request){

         try {

            $records = explode("&", $request); 
            $final_array = []; 
            
            foreach($records as $record){ 
                $get_objects = explode("=", $record); 
                $final_array[trim($get_objects[0])] = json_decode($get_objects[1]); 
            } 
            $cstore_receipt = $final_array['cstore_receipt'][0]; 
            $cstore_itemdetails = $final_array['cstore_itemdetails']; 
            $cstore_receiptdetails = $final_array['cstore_receiptdetails'];
            $cstore_receiptproduct = $final_array['cstore_receiptproduct'];
            //$cstore_receiptrefund = $final_array['cstore_receiptrefund'];

           $InsertData = [
            "id"                    => $cstore_receipt->id,
            "systemid"              => $cstore_receipt->systemid,
            "cash_received"         => $cstore_receipt->cash_received,
            "cash_change"           => $cstore_receipt->cash_change,
            "servicecharge"         => $cstore_receipt->servicecharge,
            "service_tax"           => $cstore_receipt->service_tax,
            "payment_type"          => $cstore_receipt->payment_type,
            "terminal_id"           => $cstore_receipt->terminal_id,
            "staff_user_id"         => $cstore_receipt->staff_user_id,
            "creditcard_no"         => $cstore_receipt->creditcard_no,
            "company_id"            => $cstore_receipt->company_id,
            "mode"                  => $cstore_receipt->mode,
            "status"                => $cstore_receipt->status,
            "remark"                => $cstore_receipt->remark,
            "company_name"          => $cstore_receipt->company_name,
            "gst_vat_sst"           => $cstore_receipt->gst_vat_sst,
            "business_reg_no"       => $cstore_receipt->business_reg_no,
            "receipt_address"       => $cstore_receipt->receipt_address,
            "currency"              => $cstore_receipt->currency,
            "receipt_logo"          => $cstore_receipt->receipt_logo,
            "voided_at"             => $cstore_receipt->voided_at,
            "void_user_id"          => $cstore_receipt->void_user_id,
            "void_reason"           => $cstore_receipt->void_reason,
            "transacted"            => $cstore_receipt->transacted,
            "deleted_at"            => $cstore_receipt->deleted_at,
            "created_at"            => $cstore_receipt->created_at,
            "updated_at"            => $cstore_receipt->updated_at
        ];
            $table = "cstore_receipt";
            DB::table($table)->insert($InsertData);

            foreach($cstore_itemdetails as $cstore_itemdetail){
            
            $InsertData = [
            "id"                    => $cstore_itemdetail->id,
            "receiptproduct_id"     => $cstore_itemdetail->receiptproduct_id,
            "amount"                => $cstore_itemdetail->amount,
            "rounding"              => $cstore_itemdetail->rounding,
            "price"                 => $cstore_itemdetail->price,
            "sst"                   => $cstore_itemdetail->sst,
            "discount"              => $cstore_itemdetail->discount,
            "deleted_at"            => $cstore_itemdetail->deleted_at,
            "created_at"            => $cstore_itemdetail->created_at,
            "updated_at"            => $cstore_itemdetail->updated_at
        ];

            $table = "cstore_itemdetails";
            DB::table($table)->insert($InsertData);
    }  

            foreach($cstore_receiptdetails as $cstore_receiptdetail){
            $InsertData = [
            "id"                    => $cstore_receiptdetail->id,
            "receipt_id"            => $cstore_receiptdetail->receipt_id,
            "total"                 => $cstore_receiptdetail->total,
            "rounding"              => $cstore_receiptdetail->rounding,
            "item_amount"           => $cstore_receiptdetail->item_amount,
            "sst"                   => $cstore_receiptdetail->sst,
            "discount"              => $cstore_receiptdetail->discount,
            "cash_received"         => $cstore_receiptdetail->cash_received,
            "wallet"                => $cstore_receiptdetail->wallet,
            "change"                => $cstore_receiptdetail->change,
            "creditcard"            => $cstore_receiptdetail->creditcard,
            "void"                  => $cstore_receiptdetail->void,
            "deleted_at"            => $cstore_receiptdetail->deleted_at,
            "created_at"            => $cstore_receiptdetail->created_at,
            "updated_at"            => $cstore_receiptdetail->updated_at
        ];
            $table = "cstore_receiptdetails";
            DB::table($table)->insert($InsertData);
    }

             foreach($cstore_receiptproduct as $cstore_receiptproducts){
        
              $InsertData = [
              "id"                  => $cstore_receiptproducts->id,
              "receipt_id"          => $cstore_receiptproducts->receipt_id,
              "product_id"          => $cstore_receiptproducts->product_id,
              "name"                => $cstore_receiptproducts->name,
              "quantity"            => $cstore_receiptproducts->quantity,
              "price"               => $cstore_receiptproducts->price,
              "discount_pct"        => $cstore_receiptproducts->discount_pct,
              "discount"            => $cstore_receiptproducts->discount,
              "deleted_at"          => $cstore_receiptproducts->deleted_at,
              "created_at"          => $cstore_receiptproducts->created_at,
              "updated_at"          => $cstore_receiptproducts->updated_at
        ];
            $table = "cstore_receiptproduct";
            DB::table($table)->insert($InsertData);
    }
    
             Log::debug([$cstore_receipt]);
             Log::debug('receipt item =' .json_encode($cstore_itemdetails));
             Log::debug('receipt detail =' .json_encode($cstore_receiptdetails));
             Log::debug('receipt product =' . json_encode($cstore_receiptproduct));
             
             
         
         $return = ['success' => "sx" , 'a' => $cstore_receipt , 'b' =>$cstore_itemdetails , 'c' => $cstore_receiptdetails[0] , 'd' => $cstore_receiptproduct];
        
    }catch (\Exception $e) {
            \Log::error([
                "Error" => $e->getMessage(),
                "File" => $e->getFile(),
                "Line" => $e->getLine()
            ]);

            $return = ["error" => $e->getMessage()];
        }
        return $return;

    }

}
