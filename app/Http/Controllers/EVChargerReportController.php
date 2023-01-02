<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exports\GTTransactionExport;
use App\Exports\GTUptimeExport;
use App\Exports\GTRevenueExport;
use Maatwebsite\Excel\Facades\Excel;

use Maatwebsite\Excel\HeadingRowImport;

class EVChargerReportController extends Controller
{
    public function generateExcel(Request $req) 
    {
        if($req->istab=="transaction") {
            $res = Excel::download(new GTTransactionExport,
				'transaction-report.xlsx',\Maatwebsite\Excel\Excel::XLSX);
            ob_end_clean();
            return $res;

        } else if($req->istab=="revenue") {
            $res = Excel::download(new GTRevenueExport,
				'revenue-report.xlsx',\Maatwebsite\Excel\Excel::XLSX);
            ob_end_clean();
            return $res;

        } else if($req->istab=="uptime") {
            $res = Excel::download(new GTUptimeExport,
				'uptime-report.xlsx',\Maatwebsite\Excel\Excel::XLSX);
            ob_end_clean();
            return $res;
        }
    }    
}
