<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CRPTDeferredIncomeController extends Controller
{
   public function index(){
       return view('crpt.deferred_income.deferred_income');
   }
}
