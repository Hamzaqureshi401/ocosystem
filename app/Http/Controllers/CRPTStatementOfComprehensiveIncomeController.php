<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CRPTStatementOfComprehensiveIncomeController extends Controller
{
   public function index(){
       return view('crpt.statement_of_comprehensive_income.statement_of_comprehensive_income');
   }
}
