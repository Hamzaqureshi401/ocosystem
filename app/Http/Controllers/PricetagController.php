<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use \App\Models\opos_pricetag;
use Illuminate\Support\Facades\Session;
use DB;
use Log;
use App\Models\terminal;


class PricetagController extends Controller
{
	
 public function GetPriceTagtable(Request $request, $id){
	  $currency=$id;
	  if($request->get('terminal_id')){
	  $request->session()->put('pricetag_terminal', $request->get('terminal_id')); 
	  }
	  $terminal_id =\Session::get('pricetag_terminal');
      $terminal = terminal::where('systemid', $terminal_id)->first();
	  $pricetagsaved=DB::table('opos_pricetag')->where('terminal_id',$terminal->id)->first();
      return view('opossum.pricetag',compact('currency','terminal_id','pricetagsaved'));
 }    
 public function PriceTagSubmit(Request $request){
     $terminal = terminal::where('systemid', $request->term_id)->first();
     $instupdateprice = opos_pricetag::updateOrCreate(
         ['terminal_id'=>$terminal->id],
         ['pricetag' =>$request->html]
     );

		return response('Successfull Update');
		
} 
public function PriceTagPrintPopUp(Request $request){
	
	$terminal_id=$request->get('term_id');
    $terminal = terminal::where('systemid', $terminal_id)->first();
	$ptagdata=DB::table('opos_pricetag')->where('terminal_id',$terminal->id)->first();
    return view('opossum.price-tag-print-popup')->with('pricetage', $ptagdata)->render();
}
	
}
