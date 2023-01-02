<?php

namespace App\Http\Controllers;
use \App\Models\usersrole;
use \App\Models\role;
use \Illuminate\Support\Facades\Auth;
use DB;

use Illuminate\Http\Request;

class ProductionController extends Controller
{
    //
    public function showProduction(){
    	return view('production.production');
    }

    public function showProductionBom($id){
        $id = Auth::user()->id;
        $user_roles = usersrole::where('user_id',$id)->get();

        $is_king =  \App\Models\Company::
			where('owner_user_id',Auth::user()->id)->first();

        if ($is_king != null) {
            $is_king = true;    
        } else {
            $is_king  = false;
        }

        $raw_materials = DB::table('prd_rawmaterial')->
			select(
				'prd_rawmaterial.product_id',
				'product.systemid',
				'product.thumbnail_1',
				'product.name'
			)->join('product','product.id','=','prd_rawmaterial.product_id')->get();

    	return view('production.bom', compact(
			'user_roles','is_king','raw_materials'
		));
    }


    public function showProductionLedger($id){
		return view('production_rawmaterial.ledger');
    }
}
