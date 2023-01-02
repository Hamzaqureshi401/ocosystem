<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CentralAdminController extends Controller
{

    public function showCentalAdminLogisticsView()
    {
        return view('central_admin.logistics');
    }
}
