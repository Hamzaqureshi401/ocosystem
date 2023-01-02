<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CRPTStatementOfChangesInEquityController extends Controller
{
   public function index(){
       return view('crpt.statement_of_changes_in_equity.statement_of_changes_in_equity');
   }
}
