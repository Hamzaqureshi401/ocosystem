<?php

namespace App\Console\Commands;

use \App\Models\OgFuelPrice;
use \App\Models\Company;

use \Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use \App\Http\Controllers\OposPetrolStationPumpController;
use \App\Classes\PTS2;
use \Log;
use Illuminate\Console\Command;
use \App\Http\Controllers\AnalyticsController;
use \App\Http\Controllers\IndustryOilGasController;

class updateFuelGrades extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'updateFuelGrades';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Automatic pushes the updated fuel grades to hardware';

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
		//
		Log::info("************** updateFuelGrades Command **************");
		$this->info("Updating fuel grades");

		$this->info("Getting companies data");
		$companies = Company::all();

		foreach($companies as $company) {

			$controllers = $this->getLocationIps($company->id);
			$fuelData = $this->getFuelData($company->id);

			Log::info([
				"Company name:"		=> $company->name,
				"Company Systemid:"	=> $company->systemid,
				"Fuel Grading:"		=> $fuelData,
				"Hardware:"			=> $controllers->toArray()		
			]);


			if ($fuelData == false) {
				$this->info("No change for $company->name...");
				continue;
			} else {

				$this->info("Updating data for $company->name...");
				$this->info("Fuel data:");

				$this->pushFuelPrices($controllers, $company->id, $fuelData);
			}
		}

		Log::info("************** END updateFuelGrades Command END **************");
		$this->info("Updating fuel graded ended");
	}

	public function getLocationIps($company_id) {
		$analyticsController = new AnalyticsController();
		$branch_location = [];
		$get_location = $analyticsController->get_location(true, $company_id);

		foreach ($get_location as $key => $val) {
			$location_id = array_column($branch_location, 'id');
			foreach($val as $location) {
				if (!in_array($location->id,$location_id)){
					$branch_location = array_merge($branch_location, [$location->id]);
				}
			}
		}

		$hardware = DB::table('og_controller')->
			whereIn('location_id', $branch_location)->
			where('company_id', $company_id)->
			select('ipaddress', 'public_ipaddress', 'location_id','id')->get();

		return $hardware;
	}

	public function getFuelData($company_id) {
		//get fuel records
		$industryController = new IndustryOilGasController();
		$fuelRecord = $industryController->getOgFuelQualifiedProducts($company_id);

		//fixing order
		uasort($fuelRecord, function($a,$b){
			return strcmp($a['id'], $b['id']);
		});

		$temp = [];	
		foreach( $fuelRecord as $val) {
			$temp[] = $val;
		}

		$fuelRecord = $temp;
		unset($temp);

		//check if push needed today
		$ids = array_map(function ($f) {return $f->og_f_id;}, $fuelRecord);

		$ogFuelPrice_check = OgFuelPrice::whereIn('ogfuel_id',$ids)->
			whereDate('start' , \Carbon\Carbon::today())->get();

		$localFuelChanges = DB::table('og_localfuelprice')->whereIn('ogfuel_id',$ids)->
			whereDate('start' , \Carbon\Carbon::today())->where('company_id', $company_id)->get();

		if ($ogFuelPrice_check->count() > 0 || $localFuelChanges->count() > 0 ) {
			Log::info("Fuel price change detected for company_id: $company_id");		
			// make json
			$index = 1;
			foreach($fuelRecord as $key => $f) {
				$array = [];
				$array['Id'] 	=	$index;
				$array['Name'] 	=	$f->name;
				$array['ogfuel_id'] = $f->og_f_id;
				$fuelRecord[$key] = $array;
				$index++;
			}
			return $fuelRecord;
		} else {
			Log::info("No fuel price change detected for company_id: $company_id");		
			return false;
		}
	}


	public function getPriceUpdated($packet, $location_id, $company_id) {

		$new_array = array_map( function($array) use ($location_id, $company_id) {
			$ogFuelPrice =	ogFuelPrice::where('ogfuel_id', $array['ogfuel_id'])->
				whereDate('start' , '<=',\Carbon\Carbon::today())->
				orderBy('start', 'desc')->
				first();

			$localPrice = DB::table('og_localfuelprice')->where('ogfuel_id',$array['ogfuel_id'])->
				whereDate('start' , \Carbon\Carbon::today())->where('location_id', $location_id)->
				where('company_id', $company_id)->
				first();

			if (!empty($localPrice)) 
				$array['Price'] = $localPrice->price ?? 0;	
			else if (!empty($ogFuelPrice)) 
				$array['Price'] = $ogFuelPrice->price ?? 0;
			else
				$array['Price'] = 0;

			$array['Price'] = number_format($array['Price'] / 100,2);
			return $array;
		}, $packet);

		return $new_array;
	}

	public function pushFuelPrices($hardware, $company_id, $fuelData) {
		$oposPetrolStationPumpController = new OposPetrolStationPumpController();
		foreach ($hardware as $controller) {
			$nozzleFuelData = DB::table('og_pumpnozzle')->
				join('og_pump','og_pump.id','=','og_pumpnozzle.pump_id')->
				join('og_controller','og_controller.id','=','og_pump.controller_id')->
				where([
					'og_controller.id'			=>	$controller->id,
					'og_controller.company_id'	=>	$company_id
				])->
				whereNull('og_pumpnozzle.deleted_at')->
				whereNull('og_pump.deleted_at')->
				whereNull('og_controller.deleted_at')->
				select('og_pumpnozzle.ogfuel_id','og_pump.pump_no',
					'og_pumpnozzle.nozzle_no', "og_pump.id as pump_id")->
					get();

			$fuelPricePacket	= collect($this->
				getPriceUpdated($fuelData, $controller->location_id, $company_id));

			if (env('PTS_MODE') == 'local') 
				$ip = $controller->ipaddress;
			else
				$ip = $controller->public_ipaddress;


			$DataByPump = $nozzleFuelData->groupBy('pump_no');
			$output = [];

			foreach ($DataByPump as $pump_no => $byPump) {

				$pumpNozzleFData = [];
				$nozzleFormated = [];

				foreach($byPump as $nozzle) {
					$data = $fuelPricePacket->where('ogfuel_id', $nozzle->ogfuel_id)->first();
					if (!empty($data)) {
						$pumpNozzleFData[$nozzle->nozzle_no] = $data['Price'];
					}
				}

				for($i = 1; $i <= 6; $i++)
					$nozzleFormated[] = $pumpNozzleFData[$i] ?? '0.00';

				$this->info(json_encode([
					"pump_no"		=>	$pump_no,
					"Data"			=>	$nozzleFormated,
					"Location_id"	=>	$controller->location_id,
					"Controller"	=>	$controller->id
				]));

				$oposPetrolStationPumpController->
					pumpSetPrices($pump_no, $nozzleFormated, $ip);
			}
		}	
	}
}
