<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\usersrole;
use \App\Classes\UserData;

use App\User;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;

use \App\Models\merchantlocation;
use \App\Models\location;
use \App\Models\opos_wastage;
use \App\Models\opos_wastageproduct;
use \App\Models\terminal;
use \App\Models\locationterminal;
use \App\Models\opos_receipt;
use App\Models\Merchant;
use App\Models\product;
use App\Models\inventorycost;
use App\Models\Company;
use Carbon\Carbon;
use \App\Models\merchantproduct;
class WastageController extends Controller
{
    protected $user_data;

    public function showWastageListView()
    {
    	$id = Auth::user()->id;
        $user_roles = usersrole::where('user_id',$id)->get();
        $is_king =  \App\Models\Company::
			where('owner_user_id',Auth::user()->id)->first();

        if ($is_king != null) {
            $is_king = true;
        } else {
            $is_king  = false;
        }
    	return view('wastage.wastage_list',
			compact('user_roles','is_king'));
    } 


    public function Wastagelistform(Request $request)
    {
        $month = $request->input('month');
        $year = $request->input('year');
        $userId = Auth::user()->id;
	$this->user_data = new UserData();

        $ids = merchantlocation::where('merchant_id', $this->user_data->company_id())->pluck('location_id');

        $merchant = Company::where('company.owner_user_id', $userId)
                ->join('merchant', 'company.id', '=', 'merchant.company_id')
                ->select('merchant.id as id')->first();


        
    //    $damage = Merchantproduct::where('merchant_id',$merchant->id)
	//			->join("product", 'merchantproduct.product_id', '=', 'product.id')
		$damage  = product::join("opos_receiptproduct", 'product.id', '=', 'opos_receiptproduct.product_id')
				->join("opos_refund", 'opos_receiptproduct.id', '=', 'opos_refund.receiptproduct_id')
                ->join("opos_damagerefund", 'opos_refund.id' , '=', 'opos_damagerefund.refund_id')
                ->whereMonth('opos_damagerefund.created_at', date($month))
                ->whereYear('opos_damagerefund.created_at', date($year))
                ->join('locationproduct','product.id','=','locationproduct.product_id')
                ->join('location','locationproduct.location_id','=','location.id')
				->leftjoin('opos_receipt', 'opos_receiptproduct.receipt_id', '=', 'opos_receipt.id')
				->join('staff','opos_refund.confirmed_user_id','=','staff.user_id')
				->where('staff.company_id',$this->user_data->company_id())
				->select('product.systemid as productsys_id', 'product.id as product_id', 'product.thumbnail_1', 
				'opos_receiptproduct.name', 'opos_receipt.systemid as document_no', 
				'opos_damagerefund.damage_qty as quantity', 'opos_damagerefund.created_at as last_update',
				'location.branch as location','location.id as locationid')
                ->groupBy('location')
                ->get();
				
		//$wastage = Merchantproduct::where('merchant_id',$merchant->id)
		//	 ->join("product", 'merchantproduct.product_id', '=', 'product.id')
		$wastage = product::
             join("opos_wastageproduct",'product.id' , '=','opos_wastageproduct.product_id' )
             ->whereMonth('opos_wastageproduct.created_at', date($month))
             ->whereYear('opos_wastageproduct.created_at', date($year))
             ->join('location','location.id','=','opos_wastageproduct.location_id')
			 ->join('opos_wastage', 'opos_wastage.id', '=', 'opos_wastageproduct.wastage_id')
			 ->join('staff','opos_wastage.staff_user_id','=','staff.user_id')
			 ->where('staff.company_id',$this->user_data->company_id())
			 ->select('product.systemid as productsys_id', 'product.id as product_id', 'product.thumbnail_1', 'product.name', 'opos_wastage.systemid as document_no', 'opos_wastageproduct.wastage_qty as quantity', 'opos_wastageproduct.created_at as last_update','location.branch as location','location.id as locationid')
             ->groupBy('location')
             ->get();
        

     //   $wastagedamage = $damage->merge($wastage);


        $item_count = count($damage);
		foreach ($wastage as $key => $value) {
			$damage[$item_count] = $value;
			$damage[$item_count]->type = 'wastage';
			$item_count++;
		}

        return Datatables::of($damage)->
            addIndexColumn()->
            addColumn('inven_pro_id', function ($memberList) {
                return '<a href="javascript: openNewTabURL(\'wastagereport/'.$memberList->document_no.'\')" style="cursor: pointer;text-decoration:none;" >'.$memberList->document_no.'</a>' ;
            })->
            addColumn('inven_pro_date', function ($memberList) {
                return date('dMy H:i:s',strtotime($memberList->last_update));
            })->
            addColumn('inven_pro_branch', function ($memberList) {
                return $memberList->location;
            })->
            escapeColumns([])->
            make(true);
    }
}
