<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\location;
use App\Models\usersrole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class MobAnalyticsController extends Controller
{   public function __construct() {
        $this->middleware('auth');
    }

    public function company()
    {
        $user_id = Auth::user()->id;
		$user_roles = usersrole::where('user_id',$user_id)->get();
		$logged_in_user_id = Auth::user()->id;
        $is_king =  \App\Models\Company::where('owner_user_id',
			Auth::user()->id)->first();

        if ($is_king != null) {
            $is_king = true;
        } else {
            $is_king  = false;
        }

        $companies = DB::select('
            SELECT company.id, company.name, staff.id FROM company, staff
            WHERE staff.user_id ='. $user_id . ' and staff.company_id = company.id
        '); 
        
        return view('analytics.mobile.company_list', compact('companies'));
    }

    public function branch()
    {
        $user_id = Auth::user()->id;

        $company_query = DB::select('
        SELECT owner_user_id, id FROM company
        WHERE owner_user_id ='. $user_id . ' 
    ');
        $company_id = ($company_query[0]->id);

        $sql = "
        SELECT
            l.branch,
            l.id,
            c.id
        FROM
            company c,
            merchant m,
            location l,
            merchantlocation ml
        WHERE
            c.id = $company_id
            AND m.company_id = c.id
            AND ml.merchant_id = m.id
            AND ml.location_id = l.id
            AND l.branch is NOT NULL  ";
        
        $branches = DB::select($sql);


        return view('analytics.mobile.branch', compact('branches'));
    }

    public function companySummary()
    {   
        // $sql = '
        //     select 
        //         op_recedet.receipt_id, 
        //         op_recedet.wallet, 
        //         op_recedet.cash_received,
        //         op_recedet.sst,
        //         op_recedet.creditcard,
        //         op_recedet.discount,
        //         op_rec.terminal_id,
        //         op_rec.staff_user_id,
        //         op_ter.currency,
        //         op_ter.servicecharge
        //     from 
        //         opos_receiptdetails op_recedet, 
        //         opos_receipt op_rec, 
        //         opos_terminal op_ter
        //     where 
        //         op_recedet.receipt_id = op_rec.id
        //         and op_rec.terminal_id = op_ter.id
        // ';
        // $br = DB::select($sql);

        // dd($br);

        $sql = "

            select s.user_id, c.name as 'company name', l.branch as 'company branch',
            l.name as location, oprc.cash_received, oprc.total,
            oprc.creditcard, oprc.sst, oprc.discount,
            oprc.wallet, opt.currency, opt.id as 'terminal id'
            from  
            staff s, company c, 
            merchantlocation ml,
            location l, merchant m, 
            opos_locationterminal olct, 
            opos_terminal opt, opos_receiptdetails oprc,
            opos_receipt oprec
            where s.user_id = 157 and 
            s.company_id = c.id and 
            ml.merchant_id = m.id and 
            m.company_id = c.id and 
            ml.location_id = l.id and 
            olct.location_id = l.id 
            and olct.terminal_id = opt.id 
            and oprc.receipt_id = oprec.id
            and oprec.terminal_id = opt.id
            AND l.branch is NOT NULL
            ;
        ";
        $branches = DB::select($sql);

        // dd($branches);
        return view('analytics.mobile.summary');
    }

    public function analytic()
    {
        return view('analytics.mobile.analytic');
    }
}
