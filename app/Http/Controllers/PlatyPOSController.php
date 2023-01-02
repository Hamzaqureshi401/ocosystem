<?php

namespace App\Http\Controllers;

use Jenssegers\Agent\Agent;

class PlatyPOSController extends Controller {

	/**
	 * @return string
	 **/
	function platyPOS(): string{
		$agent = new Agent();

		if ($agent->isMobile()) {
			return view('platypos.mob_plat_table');
		} else {
			return view('login.login');
		}
	}
}
