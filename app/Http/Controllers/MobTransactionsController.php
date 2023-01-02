<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MobTransactionsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function transactionsList()
    {
        return view('transaction.transaction_list');
    }

    public function deliveryOrder()
    {
        return view('transaction.delivery_order');
    }
}
