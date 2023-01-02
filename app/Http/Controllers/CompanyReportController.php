<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\usersrole;
use App\Models\role;
use App\Models\Company;
use App\Models\crptdirectory;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Auth;
use \App\Classes\UserData;

class CompanyReportController extends Controller
{
	function showDirectoryView() {
        $user_id = Auth::user()->id;
        $user_roles = usersrole::where('user_id', $user_id)->get();
        $is_king =  Company::where('owner_user_id', Auth::user()->id)->get();

        return view('company_report.company_report', compact('user_roles', 'is_king'));
    }
    public function index()
    {
        
        $this->user_data = new UserData(); 
        $data = crptdirectory::orderBy('id', 'asc')->latest()->get();

        return Datatables::of($data)
            ->addIndexColumn()            
            ->addColumn('company_name', function ($data) {
               return '<p class="os-linkcolor" data-field="company_name" style="cursor: pointer; margin: 0; text-align: left"><a class="os-linkcolor" href="'.$data->route.'/1" target="_blank" style="text-decoration: none;">'.$data->description.'</a></p>';

            })
            
            ->escapeColumns([])
            ->make(true);
   
    }
    public function showDirectoryList()
    {
        
        return view('crpt.directory.directory_view');
    }
}