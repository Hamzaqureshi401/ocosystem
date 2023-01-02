<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CRPTBlockDiscountingController extends Controller
{
   public function index(){
       return view('crpt.block_discounting.crpt_block_discounting');
   }
}
