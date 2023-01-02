<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MobPlatyPOSOrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function mobOrder()
    {
        return view('platypos.mob_plat_order');
    }

    public function mobOrderConfirm()
    {
        return view('platypos.mob_plat_confirm_order');
    }

    public function mobMenu()
    {
        return view('platypos.mob_menu_split');
    }

    public function mobMain()
    {
        return view('platypos.mob_menu_cancel');
    }

    public function mobSingleProduct()
    {
        return view('platypos.mob_single_product');
    }

    public function MobComfirmedOrder()
    {
        return view('platypos.mob_comfirmed_order');
    }
}
