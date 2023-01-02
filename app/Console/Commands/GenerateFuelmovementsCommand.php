<?php

namespace App\Console\Commands;

use \App\Classes\SystemID;
use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;
use App\Models\OgFuelMovement;
use \App\Models\stockreportproduct;
use \App\Models\opos_receiptproduct;
use \Log;
/* Add these lines into crontab:
docroot=$HOME/ocosystem/trunk/ocosystem
0 0 * * * cd $docroot;/usr/bin/php artisan generate:fuelmovement
*/


class GenerateFuelmovementsCommand extends Command
{
    /**clear
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:fuelmovement';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creating new fuel movements';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info('Start generating daily fuelMovements...');
        $current_day = date('Y-m-d');
		$this->info("Today is: $current_day");

        // fetch existing location_ogfuel con
        $og_fuelMovements = OgFuelMovement::select(
			['location_id', 'ogfuel_id','franchisee_merchant_id'])->
			groupBy('location_id', 'ogfuel_id','franchisee_merchant_id')->get();

		$this->info("og_fuelMovements records: ".$og_fuelMovements->count());
		
			
		$i = 0;
        foreach ($og_fuelMovements as $og_fuelMovement) {
		
			// check if there is fuelMovement in current day
            $today_og_fuelMovement = OgFuelMovement::where([
                    ['ogfuel_id' , $og_fuelMovement->ogfuel_id],
					['location_id' , $og_fuelMovement->location_id],
					['franchisee_merchant_id',$og_fuelMovement->franchisee_merchant_id]
                ])->
				whereBetween('date', [
					date($current_day.' 00:00:00'),
					date($current_day.' 23:59:59')
				])->
				get()->first();
		
            if(!$today_og_fuelMovement){
                // generate new row of fuel movement   
                $og_fuelManage = OgFuelMovement::where([
					['ogfuel_id' , $og_fuelMovement->ogfuel_id],
					['location_id' , $og_fuelMovement->location_id],
					['franchisee_merchant_id',$og_fuelMovement->franchisee_merchant_id]
				])->
				orderBy('date','desc')->get()->first();

				$company_data = DB::table('company')->find($og_fuelMovement->franchisee_merchant_id);
				$product_data = DB::table('product')->select('product.*','prd_ogfuel.id as ogfuel_id')->
					join('prd_ogfuel','prd_ogfuel.product_id','=','product.id')->
					where('prd_ogfuel.id',$og_fuelMovement->ogfuel_id)->
					first();

                if($og_fuelManage) {
					$sales = $this->_getFuelSales($og_fuelManage->ogfuel_id,
						date('Y-m-d'), $og_fuelManage->franchisee_merchant_id);
					$receipt = $this->_getFuelReceipt($og_fuelManage->ogfuel_id,
						date('Y-m-d'), $og_fuelManage->franchisee_merchant_id);
					$book = $this->_getFuelBook($og_fuelManage->tank_dip, $sales,
						$receipt);

                    OgFuelMovement::create([
                        'location_id' 			  	=> $og_fuelManage->location_id,
						'ogfuel_id'    				=> $og_fuelManage->ogfuel_id,
						'franchisee_merchant_id'	=> $og_fuelManage->franchisee_merchant_id,
                        'date'       			   	=> date('Y-m-d H:i:s'),
                        'cforward'     				=> $og_fuelManage->tank_dip,
                        'sales' 			        => $sales,
                        'receipt'    				=> $receipt,
						'book'      			    => $book,
                    ]);

					Log::info([
						"Daily Variance: (Tank Dip)" => !empty($og_fuelManage->tank_dip)
					]);

					if (!empty($og_fuelManage->tank_dip)) {	
						$stock_system = new SystemID('stockreport');
						$stock_system_id = $stock_system->__toString();
						$stock_id = DB::table('stockreport')->insertGetId([
							"systemid"			=>	$stock_system_id,
							"creator_user_id" 	=>	$company_data->owner_user_id,
							"receiver_user_id"	=>	0,
							"stocktakemgmt_id"	=>	0,
							"status"			=>	"confirmed",
							"type"				=>	"daily_variance",
							"location_id"		=>	$og_fuelManage->location_id,
							"created_at"		=>	date('Y-m-d H:i:s'),
							"updated_at"		=>	date('Y-m-d H:i:s')
						]);

						Log::info([
							"Daily Variance: (stock id)" => $stock_id
						]);

						DB::table('stockreportproduct')->insert([
							"product_id"		=>	$product_data->id,
							"stockreport_id"	=>	$stock_id,
							"quantity"			=>	(int) str_replace('.','', number_format((
								$og_fuelManage->tank_dip - $og_fuelManage->book),2)),
							"received"			=>	0,
							"created_at"		=>	date('Y-m-d H:i:s'),
							"updated_at"		=>	date('Y-m-d H:i:s')
						]);

				
						DB::table('stockreportmerchant')->insert([
							"stockreport_id" 			=> $stock_id,
							"franchisee_merchant_id"	=> $company_data->id,
							"created_at"				=> date("Y-m-d H:i:s"),
							"updated_at"				=> date("Y-m-d H:i:s")
						]);	
					}
                    $i++;
                }
            }
        }
        
        $this->info('Operation finished, ('. $i . ') rows created ');
    }

    protected function _getFuelSales($product_id , $date, $merchant_id){
        $sold_products = opos_receiptproduct::where([['product_id',$product_id]])->
			leftjoin('opos_receipt','opos_receipt.id',
				'=','opos_receiptproduct.receipt_id')->
			leftjoin('staff','staff.user_id','=','opos_receipt.staff_user_id')->
			where('staff.company_id',$merchant_id)->
			whereBetween('opos_receiptproduct.updated_at',
				[date($date. ' 00:00:00'), date($date.' 23:59:59')])->
			pluck('opos_receiptproduct.quantity');

        $sales = 0;
        foreach ($sold_products as $key => $value) {
            $sales += (float)$value;
        }
        return $sales;
    }

    protected function _getFuelReceipt($product_id , $date, $merchant_id){
        $loc_product = stockreportproduct::where([
			['product_id',$product_id]
			])->
			leftjoin('stockreportmerchant','stockreportmerchant.stockreport_id',
				'=','stockreportproduct.stockreport_id')->
			where('stockreportmerchant.franchisee_merchant_id', $merchant_id)->
			whereBetween('stockreportproduct.updated_at', [
				date($date.' 00:00:00'),
				date($date.' 23:59:59')
			])->
			pluck('stockreportproduct.quantity');

        $receipt = 0;
        foreach ($loc_product as $key => $value) {
            $receipt += (float)$value;
        }
        return $receipt;
    }

    protected function _getFuelBook($cforward, $sales , $receipt) {
        return (($cforward - $sales) + $receipt);
    }
}
