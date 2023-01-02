<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CRPTDeferredTaxController  extends Controller
{
   public function index(){
       return view('crpt.deferred_tax.deferred_tax');
   }
}
