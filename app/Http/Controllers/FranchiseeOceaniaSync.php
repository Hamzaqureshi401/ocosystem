<?php

namespace App\Http\Controllers;
use DB;
use Illuminate\Http\Request;
use App\Models\Company;
use \App\Classes\UserData;
use App\Models\prd_inventory;
use Illuminate\Support\Facades\Log;
class FranchiseeOceaniaSync extends Controller
{
    //
    public function syncProductToOceania($systemid) {
		try {
			$user_data = new UserData();	
            
			$company_id = $user_data->company_id();

			Log::debug('syncProductToOceania: company_id='.$company_id);

			$franchise = DB::table('franchise')->
			where(["owner_merchant_id" => $company_id])->
			get()->first();

			$franchisemerchants = DB::table('franchisemerchant')->
			where(["franchisemerchant.franchise_id" => $franchise->id])->
			get();

			Log::debug('franchisemerchants = '.$franchisemerchants);

			foreach ($franchisemerchants as $franchisemerchant) {
           
				\Log::info('syncProductToOceania franchisemerchant:',
				['franchisemerchant' => $franchisemerchant->franchise_id ] );
						
				
				\Log::info('syncProductToOceania owner_merchant_id:',
					['owner_merchant_id' => $franchisemerchant->franchisee_merchant_id ] );

				$location_data = DB::table('locationipaddr')->
					where('company_id', $franchisemerchant->franchisee_merchant_id)->
					select('ipaddr','tsystem')->
					get()->unique();

				Log::debug('location data = '.$location_data);

				$products = DB::table('product')
					->join('franchiseproduct','franchiseproduct.product_id','product.id')
					->leftjoin('franchisemerchant','franchisemerchant.franchise_id','=','franchiseproduct.franchise_id')
					->where('product.systemid', $systemid)->
					where([
						['product.name', '<>', null],
						['product.photo_1', '!=', null],
					])->
					where([
						'franchisemerchant.franchisee_merchant_id' =>$franchisemerchant->franchisee_merchant_id,
						'franchiseproduct.active' => 1
					])->
					whereNull('franchiseproduct.deleted_at')->
					select('product.*')->
					get()->filter(function ($z) {
						return !empty($z->name) && !empty($z->thumbnail_1);
					});

					Log::debug('product = '.$products);
					Log::debug('systemid ='.$systemid);

				if ($products->isEmpty())
					return;
			
				$api = new APIFcController();
				$thumbnailData = $api->generateThumbnailContent($products);

				$new_request = [
					'products'			=> json_encode($products),
					'thumbnailData'     => json_encode($thumbnailData)
				];

				$prd_inventory = DB::table('prd_inventory')->
					join('product','prd_inventory.product_id','product.id')->
					where('product.systemid', $systemid)->
					where([
						['product.name', '<>', null],
						['product.photo_1', '!=', null],
					])->
					select('prd_inventory.*', 'product.systemid')->
					get();
				Log::debug('prd_inventory='.json_encode($prd_inventory));

				$locationPrice = DB::table('franchiseproduct')->
					join('product','product.id','franchiseproduct.product_id')->
					where('product.systemid', $systemid)->
					whereIn('product.ptype', ['inventory'])->
					select("franchiseproduct.*","product.systemid")->
					get();
				Log::debug('location_price='.json_encode($locationPrice));

				if (!$prd_inventory->isEmpty()) {
					$new_request['locationPrice'] = json_encode($locationPrice);
					$new_request['prdInventory'] = json_encode($prd_inventory);
				}

				$productbmatrixbarcode = DB::table('productbmatrixbarcode')->
					join('product','product.id','productbmatrixbarcode.product_id')->
					where('product.systemid', $systemid)->
					select("productbmatrixbarcode.*", 'product.systemid')->
					get();

				if (!$productbmatrixbarcode->isEmpty()) {
					$new_request['productbmatrixbarcode'] = json_encode($productbmatrixbarcode);
				}

				$productbarcode = DB::table('productbarcode')->
					join('product','product.id','productbarcode.product_id')->
					where('product.systemid', $systemid)->
					select("productbarcode.*", 'product.systemid')->
					get();

				if (!$productbarcode->isEmpty()) {
					$new_request['productbarcode'] = json_encode($productbarcode);
				}

				$prdbmatrixbarcodegen = DB::table('prdbmatrixbarcodegen')->
					join('product','product.id','prdbmatrixbarcodegen.product_id')->
					where('product.systemid', $systemid)->
					select("prdbmatrixbarcodegen.*", 'product.systemid')->
					get();

				if (!$prdbmatrixbarcodegen->isEmpty()) {
					$new_request['prdbmatrixbarcodegen'] = json_encode($prdbmatrixbarcodegen);
				}
				Log::debug('request ='.$location_data);
				foreach ($location_data as $l) {
					
					if (!empty($l->ipaddr)) {
						$select_web_string = DB::table('locationipaddr')->
						where('ipaddr', $l->ipaddr)->
						pluck('tsystem')->
						first();
						Log::debug('select_web_string ='.$select_web_string);
						$api->init_send_data_via_api($select_web_string , $new_request);
					}
				}
			}

		} catch (\Exception $e) {
			\Log::info([
				"Error"	=> 	$e->getMessage(),
				"File"	=>	$e->getFile(),
				"Line"	=>	$e->getLine()
			]);
		}
	}


	public function syncCompanyToData($loyalty_pgm) {
		try {
			$api = new APIFcController();

			Log::debug('syncCompanyToData: loyalty_pgm='.$loyalty_pgm);

			$user_data = new UserData();	
            
			$company_id = $user_data->company_id();

			Log::debug('syncCompanyToData: company_id='.$company_id);

			DB::table('company')->
				where('company.id', $company_id)->
				update([
					'loyalty_pgm' => $loyalty_pgm,
					'updated_at' => now()
				]);

			$company = DB::table('company')->
				where('company.id', $company_id)->
					whereNull('company.deleted_at')->
					select('company.*')->
					first();

			$new_request['company']=json_encode($company);

			$location_data = DB::table('locationipaddr')->
				where('company_id', $company_id)->
				select('ipaddr')->
				get()->unique();

			foreach ($location_data as $l) {
				Log::info([
					'syncCompanyToData new company data '=>
					json_encode($new_request['company']),
				]);
				if (!empty($l->ipaddr)) {
					$api->init_send_data_via_api($l->ipaddr , $new_request);
				}
			}

		} catch (Exception $e) {
			Log::info([
				"Error"	=> 	$e->getMessage(),
				"File"	=>	$e->getFile(),
				"Line"	=>	$e->getLine()
			]);
		}
	}
}
