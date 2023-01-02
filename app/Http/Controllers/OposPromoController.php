<?php

namespace App\Http\Controllers;

use App\Models\opos_promo;
use Illuminate\Http\Request;

class OposPromoController extends Controller
{
    public function index(){

    }

    public function create(){
        //
    }

    public function store(Request $request){
        dd($request->all());
        $validatedData = $request->validate([
            'p_name' => 'required|max:255',
            'price' => 'required|numeric',
            'from_daate' => 'required',
            'to_daate' => 'required',
        ]);
        $promo = opos_promo::create($validatedData);
        dd($promo);

    }
    public function show($id){

    }

    public function edit($id){

    }

    public function update(Request $request, $id)
    {

    }

    public function destroy($id){

    }
}
