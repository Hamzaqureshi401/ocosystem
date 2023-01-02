<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CRPTBorrowingsByCurrencyController extends Controller
{
    public function index(){
        return view('crpt.borrowings_by_currency.borrowings_by_currency');
    }
}
