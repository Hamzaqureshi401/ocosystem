<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CRPTStaffCostController extends Controller
{
    public function index()
    {
        return view('crpt.staff_cost.crpt_staff_cost');
    }
}
