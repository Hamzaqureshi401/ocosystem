<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MobInventoryController extends Controller
{
    public function Inventory()
    {
        return view('mob_inventory.mob_invent');
    }

    public function StockIn()
    {
        return view('mob_inventory.mob_stock_in');
    }

    public function ProductInventoryForm()
    {
        return view('mob_inventory.mob_prod_inventory_form');
    }

    public function StockOut()
    {
        return view('mob_inventory.mob_stock_out');
    }
}
