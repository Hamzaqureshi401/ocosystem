<?php

namespace App\Http\Controllers;

use App\Models\FinancialYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use \App\Classes\UserData;

class FinancialYearController extends Controller
{
    //
    protected $user_data;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('CheckRole:stg');
    }

    public function showFinancialYearView()
    {
        $this->user_data = new UserData();
        $id = $this->user_data->company_id();
        $this->auto_FY();
        $FYData = FinancialYear::where('company_id', $id)->orderBy('start_financial_year', 'ASC')->get();
        return $FYData;
    }

    public function auto_FY()
    {
        $this->user_data = new UserData();
        $id = $this->user_data->company_id();

        $currentDate = \Carbon\Carbon::now()->format('Y-m-d');
        $minYear     = FinancialYear::where('company_id', $id)->orderBy('start_financial_year', 'ASC')->first();

        if ($minYear) {

            $minYear = $minYear->start_financial_year->toDateTimeString();

        }

        if ($minYear) {

            $begin    = new \DateTime($minYear);
            $end      = new \DateTime();
            $interval = \DateInterval::createFromDateString('1 year - 1 day');

            $period = new \DatePeriod($begin, $interval, $end);

            foreach ($period as $dt) {

                $checkYear = FinancialYear::where(
                    'company_id', $id
                )->where(
                    'start_financial_year', '>=', date('Y-01-01', strtotime($dt->format("Y-m-d")))
                )->where(
                    'start_financial_year', '<=', date('Y-12-' . date('t', strtotime(date('Y-12-01', strtotime($dt->format("Y-m-d"))))), strtotime($dt->format("Y-m-d")))
                )->first();

                if ($checkYear) {
                    $LastFY = $checkYear->start_financial_year->toDateTimeString();

                } else {

                    if (!isset($LastFY)) {
                        break;
                    }

                    $endLastFY = \Carbon\Carbon::create($LastFY)->add(1, 'year')->add(-1, 'day')->format('Y-m-d');

                    if ($currentDate > $endLastFY) {
                        $new                       = new FinancialYear();
                        $new->company_id           = $id;
                        $new->start_financial_year = date('Y-m-d H:i:s', strtotime($LastFY . "1 year"));
                        $new->save();
                        $LastFY = $new->start_financial_year->toDateTimeString();
                    }
                }
            }

        }
    }

    public function showConfirmModal(Request $request)
    {

        try {

            $this->user_data = new UserData();
            $allInputs    = $request->all();
            $id           = $this->user_data->company_id();
            $startingDate = $request->get('startingDate');

            $validation = Validator::make($allInputs, [
                'id'           => 'required',
                'startingDate' => 'required',
            ]);

            //checking year
            $selectedYear = date('Y', strtotime($startingDate));
            $currentYear  = date('Y');

            if ($currentYear != $selectedYear) {
                $msg  = 'msg_dialog';
                $text = 'Invalid Year';
                return view('financialyear.show-confirm-modal', compact('text', 'msg'));
            }

            //Checking for existing year
            $check = FinancialYear::where(
                'company_id', $id
            )->where(
                'start_financial_year', '>=', date('Y-01-01', strtotime($startingDate))
            )->where(
                'start_financial_year', '<=', date('Y-12-' . date('t', strtotime(date('Y-12-01', strtotime($startingDate)))), strtotime($startingDate))
            )->first();

            // if ($check) {
            //   return abort(403);
            // }

            if ($request->get('overide') != 'true') {
                $msg  = 'Overide';
                $date = \Carbon\Carbon::create($startingDate);
                $FY   = $date->add(1, 'year')->add(-1, 'day');
                return view('financialyear.show-confirm-modal', compact(['msg', 'startingDate', 'FY']));
            }

            //Overide year (Without First Occurance Algorithm)
            if ($check) {
                $check->start_financial_year = date('Y-m-d H:i:s', strtotime($startingDate));
                $check->save();
                $msg  = 'msg_dialog';
                $text = 'Data saved successfully';
                return view('financialyear.show-confirm-modal', compact('text', 'msg'));
            }

            $new                       = new FinancialYear();
            $new->company_id           = $id;
            $new->start_financial_year = date('Y-m-d H:i:s', strtotime($startingDate));
            $new->save();

            $msg  = 'msg_dialog';
            $text = 'Data saved successfully';
            return view('financialyear.show-confirm-modal', compact('text', 'msg'));

        } catch (\Illuminate\Database\QueryException $ex) {
            $msg  = 'msg_dialog';
            $text = 'Error occured';
            return view('financialyear.show-confirm-modal', compact('text', 'msg'));
        }

    }
}
