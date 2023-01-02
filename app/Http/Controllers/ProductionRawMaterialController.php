<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\usersrole;
use App\Models\role;
use App\Models\Company;
use Illuminate\Support\Facades\Auth;

class ProductionRawMaterialController extends Controller
{

  public function showProductionLedger($id){
    $user_id = Auth::user()->id;
    $user_roles = usersrole::where('user_id',$user_id)->get();
    $is_king =  Company::where('owner_user_id',Auth::user()->id)->get();
    return view('production_rawmaterial.ledger',compact('user_roles','is_king'));
  }

  public function showPrdRMStockIn()
  {
    $id = Auth::user()->id;
    $user_roles = usersrole::where('user_id', $id)->get();

    $is_king =  \App\Models\Company::where('owner_user_id', Auth::user()->id)->first();

    if ($is_king != null) {
      $is_king = true;
    } else {
      $is_king  = false;
    }
    return view('production_rawmaterial.prdrawmaterialstockin', compact('user_roles', 'is_king'));
  }

  public function showPrdRMStockOut()
  {
    $id = Auth::user()->id;
    $user_roles = usersrole::where('user_id', $id)->get();

    $is_king =  \App\Models\Company::where('owner_user_id', Auth::user()->id)->first();

    if ($is_king != null) {
      $is_king = true;
    } else {
      $is_king  = false;
    }
    return view('production_rawmaterial.prdrawmaterialstockout', compact('user_roles', 'is_king'));
  }

}
