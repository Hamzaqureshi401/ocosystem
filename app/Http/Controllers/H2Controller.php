<?php

namespace App\Http\Controllers;

use Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class H2Controller extends Controller
{
     public function showhydrogenView(){

        return view('virtualcabinet.hydrogen');
    }
    public function storeh2receipt($request){

      //Log::debug("h2receiptproduct = " .json_encode($request));

            $records = explode("&", $request); 
            $final_array = []; 
            
            foreach($records as $record){ 
                $get_objects = explode("=", $record); 
                $final_array[trim($get_objects[0])] = json_decode($get_objects[1]); 
               
            } 
                $h2receiptdetails =  json_encode($final_array['h2receiptdetails']); 
                $h2receiptlist =     json_encode($final_array['h2receiptlist']);
                $h2receiptproduct =  json_encode($final_array['h2receiptproduct']);
                $h2receipt =         json_encode($final_array['h2receipt']); 
                $h2receiptdetails =  json_decode($h2receiptdetails);
                $h2receiptlist =     json_decode($h2receiptlist);
                $h2receiptproduct =  json_decode($h2receiptproduct);
                $h2receipt        =  json_decode($h2receipt);
                $h2receipt        = $h2receipt[0];
                $h2receiptdetails = $h2receiptdetails[0];
                //$h2receiptlist = $h2receiptlist[0];    
                $h2receiptproduct = $h2receiptproduct[0];

            $InsertData = [

                  "id"                             => $h2receiptproduct->id,
                  "receipt_id"                     => $h2receiptproduct->receipt_id,
                  "product_id"                     => $h2receiptproduct->product_id,
                  "name"                           => str_replace("+"," ",$h2receiptproduct->name),
                  "quantity"                       => $h2receiptproduct->quantity,
                  "price"                          => $h2receiptproduct->price,
                  "discount_pct"                   => $h2receiptproduct->discount_pct,
                  "discount"                       => $h2receiptproduct->discount,
                  "filled"                         => $h2receiptproduct->filled,
                  "deleted_at"                     => $h2receiptproduct->deleted_at,
                  "created_at"                     => $h2receiptproduct->created_at,
                  "updated_at"                     => $h2receiptproduct->updated_at
            ];
          DB::table('h2receiptproduct')->insert($InsertData);
         

            $InsertData = [

                  "id"                             => $h2receiptdetails->id,
                  "receipt_id"                     => $h2receiptdetails->receipt_id,
                  "total"                          => $h2receiptdetails->total,
                  "rounding"                       => $h2receiptdetails->rounding,
                  "item_amount"                    => $h2receiptdetails->item_amount,
                  "sst"                            => $h2receiptdetails->sst,
                  "discount"                       => $h2receiptdetails->discount,
                  "cash_received"                  => $h2receiptdetails->cash_received,
                  "wallet"                         => $h2receiptdetails->wallet,
                  "creditac"                       => $h2receiptdetails->creditac,
                  "change"                         => $h2receiptdetails->change,
                  "creditcard"                     => $h2receiptdetails->creditcard,
                  "void"                           => $h2receiptdetails->void,
                  "deleted_at"                     => $h2receiptdetails->deleted_at,
                  "created_at"                     => $h2receiptdetails->created_at,
                  "updated_at"                     => $h2receiptdetails->updated_at

            ];
         DB::table('h2receiptdetails')->insert($InsertData);
  
            $InsertData = [

                  "id"                             => $h2receipt->id,
                  "systemid"                       => $h2receipt->systemid,
                  "cash_received"                  => $h2receipt->cash_received,
                  "cash_change"                    => $h2receipt->cash_change,
                  "servicecharge"                  => $h2receipt->servicecharge,
                  "service_tax"                    => $h2receipt->service_tax,
                  "payment_type"                   => $h2receipt->payment_type,
                  "terminal_id"                    => $h2receipt->terminal_id,
                  "staff_user_id"                  => $h2receipt->staff_user_id,
                  "creditcard_no"                  => $h2receipt->creditcard_no,
                  "company_id"                     => $h2receipt->company_id,
                  "location_id"                    => $h2receipt->location_id,
                  "mode"                           => $h2receipt->mode,
                  "status"                         => $h2receipt->status,
                  "remark"                         => $h2receipt->remark,
                  "company_name"                   => str_replace("+"," ",$h2receipt->company_name),
                  "gst_vat_sst"                    => $h2receipt->gst_vat_sst,
                  "business_reg_no"                => $h2receipt->business_reg_no,
                  "receipt_address"                => $h2receipt->receipt_address,
                  "currency"                       => $h2receipt->currency,
                  "receipt_logo"                   => $h2receipt->receipt_logo,
                  "round"                          => $h2receipt->round,
                  "voided_at"                      => $h2receipt->voided_at,
                  "void_user_id"                   => $h2receipt->void_user_id,
                  "void_reason"                    => $h2receipt->void_reason,
                  "pump_no"                        => $h2receipt->pump_no,
                  "pump_id"                        => $h2receipt->pump_id,
                  "transacted"                     => $h2receipt->transacted,
                  "deleted_at"                     => $h2receipt->deleted_at,
                  "created_at"                     => $h2receipt->created_at,
                  "updated_at"                     => $h2receipt->updated_at

            ];
          DB::table('h2receipt')->insert($InsertData);
  
    
            $InsertData = [

                  "id"                             => $h2receiptlist->id,
                  "h2receipt_tstamp"               => $h2receiptlist->h2receipt_tstamp,
                  "h2receipt_id"                   => $h2receiptlist->h2receipt_id,
                  "h2receipt_systemid"             => $h2receiptlist->h2receipt_systemid,
                  "pump_no"                        => $h2receiptlist->pump_no,
                  "total"                          => $h2receiptlist->total,
                  "fuel"                           => $h2receiptlist->fuel,
                  "filled"                         => $h2receiptlist->filled,
                  "refund"                         => $h2receiptlist->refund,
                  "refund_qty"                     => $h2receiptlist->refund_qty,
                  "refund_staff_user_id"           => $h2receiptlist->refund_staff_user_id,
                  "refund_tstamp"                  => $h2receiptlist->refund_tstamp,
                  "status"                         => $h2receiptlist->distatusscount,
                  "deleted_at"                     => $h2receiptlist->deleted_at,
                  "created_at"                     => $h2receiptlist->created_at,
                  "updated_at"                     => $h2receiptlist->updated_at
            ];
          DB::table('h2receiptlist')->insert($InsertData);

          log::debug("h2 Synced");


    }
}
