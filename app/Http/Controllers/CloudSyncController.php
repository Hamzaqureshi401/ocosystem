<?php

namespace App\Http\Controllers;

use DB;
use Log;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\product;
use App\Models\merchantproduct;
use App\Models\dgo_apisecurity;

class CloudSyncController extends Controller
{
	// Global synchronization interval in minutes
	private $interval = 6000;


    public function verify_licensekey(Request $request) {
		$lickey = $request->input('licensekey');
		$all = $request->all();
		Log::debug('verify_licensekey: all='.json_encode($all));
	}

    public function pull_products($api_key, $company_systemid) {
		Log::debug('api_key='.$api_key);
		Log::debug('company_systemid='.$company_systemid);

		/*
		dump($api_key);
		dump($company_systemid);
		*/

		// Authenticate API KEY from legal dingo clients
		// dgo_apisecurity.api_key and
		// dgo_apisecurity.company_systemid
		$result = dgo_apisecurity::where('api_key', trim($api_key))->
			where('company_systemid', trim($company_systemid))->
			get();

		Log::debug('result='.json_encode($result));

		(count($result) > 0) ? $ret=true : $ret=false;
		if ($ret) {
			// Check if within the last interval, are there any new records
			$curnow = Carbon::now();
			$curprev = Carbon::now()->subMinutes($this->interval);

			Log::debug('curnow='.$curnow->toDateTimeString());
			Log::debug('curprev='.$curprev->toDateTimeString());

			$newstuff = [];

			// Extract new records for product
			$newprod = product::
				where('product.updated_at','>',$curprev)->
				select('product.*')->
				where('product.updated_at','<',$curnow)->
				join('merchantproduct as mp','product.id','=','mp.product_id')->
				join('company as c','mp.merchant_id','=','c.id')->
				where('c.systemid','=',$company_systemid)->
				get();

			// Extract new records for prd_inventory
			$newinv = product::
				where('product.updated_at','>',$curprev)->
				select('pi.*')->
				where('product.updated_at','<',$curnow)->
				join('merchantproduct as mp','product.id','=','mp.product_id')->
				join('company as c','mp.merchant_id','=','c.id')->
				join('prd_inventory as pi','pi.product_id','=','product.id')->
				where('c.systemid','=',$company_systemid)->
				get();

			// Extract new records for prd_restaurant
			$newrest = product::
				where('product.updated_at','>',$curprev)->
				select('pr.*')->
				where('product.updated_at','<',$curnow)->
				join('merchantproduct as mp','product.id','=','mp.product_id')->
				join('company as c','mp.merchant_id','=','c.id')->
				join('prd_restaurant as pr','pr.product_id','=','product.id')->
				where('c.systemid','=',$company_systemid)->
				get();

			// Extract new records for prd_ogfuel
			$newfuel = product::
				where('product.updated_at','>',$curprev)->
				select('pr.*')->
				where('product.updated_at','<',$curnow)->
				join('merchantproduct as mp','product.id','=','mp.product_id')->
				join('company as c','mp.merchant_id','=','c.id')->
				join('prd_ogfuel as pr','pr.product_id','=','product.id')->
				where('c.systemid','=',$company_systemid)->
				get();

			// Extract new records for merchantproduct
			$newmproduct = merchantproduct::
				where('merchantproduct.updated_at','>',$curprev)->
				select('merchantproduct.*')->
				where('merchantproduct.updated_at','<',$curnow)->
				join('product as p','p.id','=','merchantproduct.product_id')->
				join('company as c','merchantproduct.merchant_id','=','c.id')->
				where('c.systemid','=',$company_systemid)->
				get();

			// Extract new records for prd_category
			$newcat = null;

			// Extract new records for prd_subcategory
			$newsubcat = null;

			// Extract new records for prdcategory
			$newprdcat = null;

			// Retrieving the photo_1 and thumbnail_1 images from the products
			$prefix = '/images/product/';
			$arrContextOptions=array(
				"ssl"=>array(
					'verify_peer'=> false,
					'verify_peer_name'=> false,
				),
			);

			foreach ($newprod as $data) {
				$photo_path = asset($prefix.$data->id.'/'.$data->photo_1);
				$thumb_path = asset($prefix.$data->id.'/thumb/'.$data->thumbnail_1);
				Log::debug('photo_path='.$photo_path);
				Log::debug('thumb_path='.$thumb_path);

				// Retrieve photo and encode in base64
				$photo_content = base64_encode(file_get_contents($photo_path,
					false, stream_context_create($arrContextOptions)));
				$thumb_content = base64_encode(file_get_contents($thumb_path,
					false, stream_context_create($arrContextOptions)));

				$data->photo_content = $photo_content;
				$data->thumb_content = $thumb_content;
			}

			$newstuff = [
				"product"		=> $newprod,
				"inventory"		=> $newinv,
				"restaurant"	=> $newrest,
				"fuel"			=> $newfuel,
				"mproduct"		=> $newmproduct,
				"cat"			=> $newcat,
				"subcat"		=> $newsubcat,
				"prdcat"		=> $newprdcat
			];

			Log::debug('newprods='.json_encode($newstuff));
		}
		return response()->json($newstuff);
	}
}
