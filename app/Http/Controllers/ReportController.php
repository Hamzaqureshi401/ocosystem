<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\usersrole;
use App\Models\role;
use App\Models\Company;

class ReportController extends Controller
{
    //

    public function __construct()
    {
        $this->middleware('auth');
         $this->middleware('CheckRole:stg');
    }

  
}
