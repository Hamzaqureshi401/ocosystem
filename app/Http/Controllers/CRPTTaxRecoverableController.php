<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CRPTTaxRecoverableController extends Controller
{

    public function index(){
        return view('crpt.tax_recoverable.tax_recoverable');
    }
}
