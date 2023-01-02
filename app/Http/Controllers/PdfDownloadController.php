<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \Illuminate\Support\Facades\Auth;
use PDF;
use DB;



class PdfDownloadController extends Controller
{
   public function printPDF($startDate , $endDate , $view , $system_id){

      $systemid   = $system_id;
      $startdate  = $startDate;
      $enddate    = $endDate;
      $branch     = DB::table('location')->where('systemid' , $systemid)->pluck('branch')->first();
      $data = array(

         'systemid' => $systemid,
         'startdate'=> $startdate,
         'enddate' => $enddate,
         'branch' => $branch

      );    
      if ($view == "C-Store"){

         return $this->downloadcstorepdf($data);

      }elseif($view == "Fuel"){
      
         return $this->downloadfuelpdf($data);
      
      }elseif($view == "EV"){

         return $this->downloadevpdf($data);
     
      }elseif($view == "hydrogen"){

        $get_receipts =  Db::table('h2receipt')
        ->join('h2receiptdetails' , 'h2receiptdetails.receipt_id', 'h2receipt.id')
        ->join('h2receiptproduct' , 'h2receiptproduct.receipt_id' , 'h2receipt.id')
        ->select('h2receipt.id' ,'h2receipt.service_tax' ,'h2receipt.payment_type' , 'h2receiptproduct.name' , 'h2receiptproduct.quantity' , 'h2receiptdetails.item_amount' , 'h2receiptproduct.price' , 'h2receiptdetails.total' , 'h2receiptdetails.sst')->get();

       // dd($get_receipts);

         return $this->downloadhydrogenpdf($data , $get_receipts);
         
      }elseif($view == "e-Wallet"){

         return $this->downloadewalletpdf($data);
         
      }elseif($view == "payment_terminal"){

         return $this->downloadpaymentterminapdf($data);
         
      }elseif($view == "open-item"){

         return $this->downloadopenitempdf($data);
         
      }


      }
      public function downloadcstorepdf($data){

        view()->share('data',$data);
        $pdf = PDF::loadView('itemized_sales_report.convenience_store_sales_pdf');
        $pdf->setPaper('A4', 'portrait');
        return $pdf->download('itemized_sales_report.pdf');
      }
       public function downloadfuelpdf($data){

        view()->share('data',$data);
        $pdf = PDF::loadView('itemized_sales_report.fuel_pdf');
        $pdf->setPaper('A4', 'portrait');
        return $pdf->download('itemized_sales_report.pdf');
      }
       public function downloadevpdf($data){
       view()->share('data',$data);
        $pdf = PDF::loadView('itemized_sales_report.evcharger_pdf');
        $pdf->setPaper('A4', 'portrait');
        return $pdf->download('itemized_sales_report.pdf');
      }
       public function downloadhydrogenpdf($data ,$get_receipts){

       //  dd($get_receipts);
 
      // view()->share('data',$data , 'get_receipts' , $get_receipts);
        $pdf = PDF::loadView('itemized_sales_report.hydrogen_pdf', compact('data' , 'get_receipts'));
        $pdf->setPaper('A4', 'portrait');
        return $pdf->download('itemized_sales_report.pdf');
      }
       public function downloadewalletpdf($data){
 
       view()->share('data',$data);
        $pdf = PDF::loadView('itemized_sales_report.ewallet_pdf');
        $pdf->setPaper('A4', 'portrait');
        return $pdf->download('itemized_sales_report.pdf');
      }
       public function downloadpaymentterminapdf($data){
 
       view()->share('data',$data);
        $pdf = PDF::loadView('itemized_sales_report.payment_terminal_pdf');
        $pdf->setPaper('A4', 'portrait');
        return $pdf->download('itemized_sales_report.pdf');
      }
      public function downloadopenitempdf($data){
 
         view()->share('data',$data);
        $pdf = PDF::loadView('itemized_sales_report.open_item_pdf');
        $pdf->setPaper('A4', 'portrait');
        return $pdf->download('itemized_sales_report.pdf');
      }

       function getmerid (Request $request){

        return DB::table('merchantlinkrelation')->whereId($request->pass_id)->pluck('merchantlink_id')->first();
    }
       
}
