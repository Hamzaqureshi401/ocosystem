<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Classes\UserData;
use App\Models\usersrole;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Auth;


class MPCRController extends Controller
{
    protected $user_data;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('CheckRole:prod');
    }

    public function index()
	{
		$data = collect([[
			'systemid' => 749469378378, 
			'description' => 'Crane Preventive Report', 
			'mechanic' => '', 
			'date' => '', 
			'status' => '', 
			'amount' => ''
		]]);

		return Datatables::of($data)
			->addIndexColumn()
			->addColumn('cpr_id', function ($cprList) {
				return '<p class="os-linkcolor" data-field="cpr_id" style="cursor: pointer; margin: 0; text-align: center;">
				<a class="os-linkcolor" href="/monthly-preventive-check-reports/'.$cprList['systemid'] .'" target="_blank" style="text-decoration: none;">' 
				.$cprList['systemid'] . 
				'</p>';
			})
			->addColumn('cpr_description', function ($cprList) {
				return  '<p class="descriptionOutput" data-field="cpr_description" style="margin: 0;display:inline-block">
				' . ucfirst($cprList['description']) .'</p>';
			})
			->addColumn('cpr_mechanic', function ($cprList) {
				return '<p class="mechanicOutput" data-field="cpr_mechanic" style="cursor: pointer; margin: 0;text-align: left;">
				'.ucfirst($cprList['mechanic']).
				'</p>';
			})

			->addColumn('cpr_date', function ($cprList) {

				return '<p data-field="cpr_date" disabled="disabled" style="margin: 0;" >
				'.$cprList['date'].
				'</p>';

			})
			->addColumn('cpr_status', function ($cprList) {

				return '<p   data-field="cpr_status" style=" margin: 0;">'.ucfirst($cprList['status']).'</p>';

			})
			->addColumn('cpr_amount', function ($cprList) {

				return '<p data-field="cpr_amount" style="margin: 0; text-align: right">
				'.$cprList['amount'].
				'</p>';

			})
			->addColumn('deleted', function ($cprList) {
				return '<p data-field="deleted"
					style="background-color:red;
					border-radius:5px;margin:auto;
					width:25px;height:25px;
					display:block;cursor: pointer;"
					class="text-danger remove">
					<i class="fas fa-times text-white"
					style="color:white;opacity:1.0;
					padding:4px;
					-webkit-text-stroke: 1px red;"></i></p>';

			})
			->escapeColumns([])
			->make(true);
	}

	public function show_cpr() 
	{
		return view('mpcr.mpcr');
	}

	public function mpcr_form($systemId) 
	{
		$id = Auth::user()->id;
		$user_data = new UserData();
		$user_data->exit_merchant();

		$user_roles = usersrole::where('user_id',$id)->get();

		$is_king =  Company::where('owner_user_id',Auth::user()->id)->first();

		return view('mpcr.mpcr_form', compact('user_roles', 'is_king', 'systemId'));
	}
}