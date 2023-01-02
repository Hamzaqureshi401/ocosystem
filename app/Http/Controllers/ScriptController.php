<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \App\Models\terminal;
use App\Models\opos_tablename;
use App\Models\location;
use App\Models\Merchant;
use App\Models\merchantlocation;
use App\Models\Company;
use App\Models\role;
use App\Models\usersrole;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

use \App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\GlobalAuthController;

class ScriptController extends Controller
{
    
	public function populate_opos_tablename() {
        $tableName1 = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','1','2','3','4','5','6','7','8','9','10','11','12','13','14','15','16','17','18','19','20','21','22','23','24','25','26','27','28','29','30','31','32','33','34','35','36','37','38','39','40','41','42','43','44','45','46','47','48','49','50','51','52','53','54','55','56','57','58','59','60','61','62','63','64','65','66','67','68','69','70','71','72','73','74','75','76','77','78','79','80','81','82','83','84','85','86','87','88','89','90','91','92','93','94','95','96','97','98','99','100','101','102','103','104','105','106','107','108','109','110','111','112','113','114','115','116','117','118','119','120','121','122','123','124','125','126','127','128','129','130','131','132','133','134','135','136','137','138','139','140','141','142','143','144','145','146','147','148','149','150','151','152','153','154','155','156','157','158','159','160','161','162','163','164','165','166','167','168','169','170','171','172','173','174','175','176','177','178','179','180','181','182','183','184','185','186','187','188','189','190','191','192','193','194','195','196','197','198','199','200');
        $locations = location::all();
		foreach ($locations as $location) {
			echo "<h3 style='margin-bottom:0'>location_id=$location->id ($location->systemid)</h3>";
			foreach ($tableName1 as $t) {
				echo "$t ";
				$tn = new opos_tablename();
				$tn->location_id = $location->id;
				$tn->default_name = $t;
				$tn->save();
			}
			echo "\n";
		}
    }

    public function create_takeaway_seq() {
        $locations = location::all();
        foreach ($locations as $location) {
            echo "<h3 style='margin-bottom:0'>location_id=$$location->id ($$location->systemid)</h3>";
            echo "$$location ";
            $sq_name = 'takeaway_seq_' . sprintf("%06d",$location->id);

            \DB::select(\DB::raw("create sequence $sq_name nocache nocycle"));
            echo "\n";
        }
    }

    public function populate_takeaway_seq()
    {
        $locations = location::whereNotNull('branch')->get();
        $i = 1;
        foreach ($locations as $location) {
            // echo "$$location ";
            $sq_name = 'takeaway_seq_' . sprintf("%06d",$location->id);
            if(!\DB::select("SHOW TABLES LIKE '".$sq_name."'"))
            {   
                $merchantlocation = merchantlocation::where('location_id',$location->id)->pluck('merchant_id')->first();
                if(!empty($merchantlocation)) {
                    $merchant = Merchant::where('id',$merchantlocation)->pluck('company_id')->first();
                    if(!empty($merchant)) {
                        $Company = Company::where('id',$merchant)->first();
                    }
                }
                $comp_name = (!empty($Company)) ? ", ".$Company->name ."." : '';
                echo $i." ".$sq_name." for ". $location->branch .$comp_name;
                $i++;
                \DB::select(\DB::raw("create sequence $sq_name nocache nocycle"));
            }
            echo "\n"; 
        }
    }

    public function populate_counter_seq() {
        echo "<h3 style='margin-bottom:0'>creating counter_seq</h3>";
        $sq_name = 'counter_seq';

        \DB::select(\DB::raw("create sequence $sq_name nocache nocycle"));
        echo "\n";
        echo "<h3 style='margin-bottom:0'>counter_seq created successfully</h3>";
        echo "\n";
    }

    public function activate_me() {
  	if (\Auth::User()) {
		$globalAuth = new GlobalAuthController();
		$this->activate_all_roles($globalAuth->get_data('user_id'), $globalAuth->get_data('merchant_id'));
		$this->activate_all_modules($globalAuth->get_data('merchant_id'));
		$this->activate_all_locations($globalAuth->get_data('user_id'));
        	
		return response()->json(["success"=>"Modules and Roles Activated"]);

	} else {
		return response()->json(["error"=>"please log in"]);
	}
    }

    public function activate_all_roles($user_id,$company_id) {
       $role = role::all();
       
       $role = $role->filter(function($slug){
           $admin_role = ['super','sadmin','mrc'];
           return !in_array($slug->slug,$admin_role);
       });
    
       foreach($role as $r) {
           $is_exist = usersrole::where([
               'user_id'=>$user_id,'role_id'=>$r->id,"company_id"=>$company_id
               ])->first();

            if (!empty($is_exist)) {
                continue;
            }

           $user_role = new usersrole();
           $user_role->user_id = $user_id;
           $user_role->role_id = $r->id;
           $user_role->company_id = $company_id;
           $user_role->save();
       }
    }

    public function activate_all_modules($merchant_id) {
	    $web_mod = DB::table('module')
		->whereNull('deleted_at')
		->get();
	    
	    $web_in = [];
	    
	    foreach ($web_mod as $mod) {
       
		    $is_not_exist = empty(DB::table('merchantmodule'
		    		)->where([
					"merchant_id" => $merchant_id,
					"module_id" => $mod->id
				])
				->whereNull('deleted_at')
				->first());
	
		if ($is_not_exist) {
			$web_in[] = Array(
				"merchant_id" => $merchant_id,
				"module_id" => $mod->id,
				"created_at" => date("Y-m-d H:i:s")
			);
		}
	    }
		
	    

	    DB::table('merchantmodule')->insert($web_in);

	    $mob_mod = DB::table('mobmodule')
		->whereNull('deleted_at')
	    	->get();	

	    $mob_in = [];
	  
	    foreach ($mob_mod as $mod) {
		    $is_not_exist = empty(DB::table('merchantmobmodule')
			    		->where([
					"merchant_id" => $merchant_id,
					"mobmodule_id" => $mod->id
				])
				->whereNull('deleted_at')
				->first());
	
		if ($is_not_exist) {
			$mob_in[] = Array(
				"merchant_id" => $merchant_id,
				"mobmodule_id" => $mod->id,
				"created_at" => date("Y-m-d H:i:s")
			);
		}
	    }

	    DB::table('merchantmobmodule')->insert($mob_in);
    }

    public function activate_all_merchant_module() {
	    $merchant = DB::table('company')
		    		->whereNull('deleted_at')
				->get();
	   $m_name = [];
	   
	    foreach ($merchant as $m) {
		    $this->activate_all_modules($m->id);
		    $m_name[] = $m->name;
	    }
	    
	    return $m_name;
    }

	public function activate_all_locations($user_id) {
		$analyticsController =  new AnalyticsController();
		$get_locations = $analyticsController->get_location(false);
		$userslocation = DB::table('userslocation');

		array_map(function($f) use ($user_id, $userslocation){
			array_map(function($z) use ($user_id, $userslocation) {
				$condition = [
					"user_id"		=>	$user_id,
					"location_id"	=>	$z->id,
				];

				$is_exist =  $userslocation->
					where($condition)->
					whereNull('deleted_at')->
					first();

				if (empty($is_exist)) {
					$condition['created_at'] = date("Y-m-d h:i:s");
					$condition['updated_at'] = date("Y-m-d h:i:s");
					$userslocation->insert($condition);
				}

			}, $f);
		}, $get_locations);
	}
}
