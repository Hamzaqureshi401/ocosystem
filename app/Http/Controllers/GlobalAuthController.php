<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use \App\Classes\UserData;
use \Illuminate\Support\Facades\DB;
use Log;

class GlobalAuthController extends Controller
{
	protected $mobile_roles = null;
	protected $web_roles = null;

	protected $web_modules = null;
	protected $mobile_modules = null;

	protected $UserData;

	protected $merchant_id;
	protected $user_id;

	function __construct() {
		
		if (!\Auth::User()) {
			return;	
		}
		
		$this->UserData = new UserData();

		$this->merchant_id = $this->UserData->company_id();
		$this->user_id = $this->UserData->user->id;

		$this->web_roles = DB::table('role')
					->whereNull('role.deleted_at')
					->join('usersrole','role.id' , '=', 'usersrole.role_id')
					->where([
						'usersrole.user_id' => $this->user_id,
						"usersrole.company_id" => $this->merchant_id
					])
					->whereNull('usersrole.deleted_at')
					->get();

		$this->mobile_roles =  DB::table('mobrole')
					->whereNull('mobrole.deleted_at')
					->join('usersmobrole','mobrole.id' , '=', 'usersmobrole.mobrole_id')
					->where([
						'usersmobrole.user_id' => $this->user_id,
						"usersmobrole.company_id" => $this->merchant_id
					])
					->whereNull('usersmobrole.deleted_at')
					->get();

		$this->web_modules = DB::table('module')
					->whereNull('module.deleted_at')
					->join('merchantmodule','module.id','=','merchantmodule.module_id')
					->where('merchant_id',$this->merchant_id)
					->whereNull('merchantmodule.deleted_at')
					->get();

		$this->mobile_modules = DB::table('mobmodule')
						->whereNull('mobmodule.deleted_at')
						->join('merchantmobmodule','mobmodule.id','=','merchantmobmodule.mobmodule_id')
						->where('merchant_id', $this->merchant_id)
						->whereNull('merchantmobmodule.deleted_at')
						->get();
	}

	public function web_module($name) {
		
		if (!\Auth::User()) {
			return false;	
		}

		return $this->web_modules->contains('slug',$name);

	}
	
	public function mobile_module($name) {
		
		if (!\Auth::User()) {
			return false;	
		}

		return $this->mobile_modules->contains('slug',$name);
	}

	
	public function web_role($name) {
		
		if (!\Auth::User()) {
			return false;	
		}

		return $this->web_roles->contains('slug',$name) ||
			$this->UserData->is_king() || $this->UserData->is_super_admin();
	}

	public function mobile_role($name) {
		
		if (!\Auth::User()) {
			return false;	
		}

		return $this->mobile_roles->contains('slug',$name) ||
			$this->UserData->is_King() || $this->UserData->is_super_admin();
	}

	public function get_data($d) {
		return $this->$d;
	}
}

