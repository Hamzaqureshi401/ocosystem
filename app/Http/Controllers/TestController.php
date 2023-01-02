<?php

namespace App\Http\Controllers;

use \App\Classes\PTS2;
use Illuminate\Http\Request;

class TestController extends Controller
{
    private $username = "admin";
    private $password = "admin";

	public function get_battery_voltage() {
		dump('TestController@get_battery_voltage()');

		$pts2 = new PTS2();
		$res = $pts2->get_battery_voltage();

		dump($res);

		$pts2->close_channel();
	}

	public function get_unique_identifier() {
		dump('TestController@get_unique_identifier()');

		$pts2 = new PTS2();
		$res = $pts2->get_unique_identifier();

		dump($res);

		$pts2->close_channel();
	}

	public function get_firmware_information() {
		dump('TestController@get_firmware_information()');

		$pts2 = new PTS2();
		$res = $pts2->get_firmware_information();

		dump($res);

		$pts2->close_channel();
	}

	public function get_sd_information() {
		dump('TestController@get_sd_information()');

		$pts2 = new PTS2();
		$res = $pts2->get_sd_information();

		dump($res);

		$pts2->close_channel();
	}

	public function get_user_information() {
		dump('TestController@get_user_information()');

		$pts2 = new PTS2();
		$res = $pts2->get_user_information();

		dump($res);

		$pts2->close_channel();
	}

	public function get_configuration_identifier() {
		dump('TestController@get_configuration_identifier()');

		$pts2 = new PTS2();
		$res = $pts2->get_configuration_identifier();

		dump($res);

		$pts2->close_channel();
	}

	public function get_datetime() {
		dump('TestController@get_datetime()');

		$pts2 = new PTS2();
		$res = $pts2->get_datetime();

		dump($res);

		$pts2->close_channel();
	}

	public function set_datetime($datetime) {
		dump('TestController@set_datetime()');

		$pts2 = new PTS2();
		$res = $pts2->set_datetime($datetime);

		dump($res);

		$pts2->close_channel();
	}

	public function get_pts_network_settings() {
		dump('TestController@get_pts_network_settings()');

		$pts2 = new PTS2();
		$res = $pts2->get_pts_network_settings();

		dump($res);

		$pts2->close_channel();
	}

	public function get_system_decimal_digits() {
		dump('TestController@get_system_decimal_digits()');

		$pts2 = new PTS2();
		$res = $pts2->get_system_decimal_digits();

		dump($res);

		$pts2->close_channel();
	}

	public function get_parameter($device, $number, $address) {
		dump('TestController@get_parameter()');

		$pts2 = new PTS2();
		$res = $pts2->get_parameter($device, $number, $address);

		dump($res);

		$pts2->close_channel();
	}

	public function get_pumps_configuration() {
		dump('TestController@get_pumps_configuration()');

		$pts2 = new PTS2();
		$res = $pts2->get_pumps_configuration();

		dump($res);

		$pts2->close_channel();
	}

	public function get_probes_configuration() {
		dump('TestController@get_probes_configuration()');

		$pts2 = new PTS2();
		$res = $pts2->get_probes_configuration();

		dump($res);

		$pts2->close_channel();
	}

	public function get_fuel_grades_configuration() {
		dump('TestController@get_fuel_grades_configuration()');

		$pts2 = new PTS2();
		$res = $pts2->get_fuel_grades_configuration();

		dump($res);

		$pts2->close_channel();
	}

	public function get_pump_nozzles_configuration() {
		dump('TestController@get_pump_nozzles_configuration()');

		$pts2 = new PTS2();
		$res = $pts2->get_pump_nozzles_configuration();

		dump($res);

		$pts2->close_channel();
	}

	public function get_tanks_configuration() {
		dump('TestController@get_tanks_configuration()');

		$pts2 = new PTS2();
		$res = $pts2->get_tanks_configuration();

		dump($res);

		$pts2->close_channel();
	}

	public function get_users_configuration() {
		dump('TestController@get_users_configuration()');

		$pts2 = new PTS2();
		$res = $pts2->get__configuration();

		dump($res);

		$pts2->close_channel();
	}

	public function get_port_logging_configuration() {
		dump('TestController@get_port_logging_configuration()');

		$pts2 = new PTS2();
		$res = $pts2->get_port_logging_configuration();

		dump($res);

		$pts2->close_channel();
	}

	public function pump_get_status($pump_no) {
		dump('TestController@pump_get_status()');

		$pts2 = new PTS2();
		$res = $pts2->pump_get_status($pump_no);

		dump($res);

		$pts2->close_channel();
	}

	public function pump_authorize($pump_no, $nozzle, $type, $dose, $price) {
		dump('TestController@pump_authorize()');

		/* Note case sensitive: type=(Volume|Amount|FullTank) */
		$pts2 = new PTS2();
		$res = $pts2->pump_authorize($pump_no, $nozzle, $type, $dose, $price);

		dump($res);

		$pts2->close_channel();
	}

	public function pump_transaction_information($pump_no, $transaction) {
		dump('TestController@pump_get_transaction_information()');

		$pts2 = new PTS2();
		$res = $pts2->pump_get_transaction_information($pump_no, $transaction);

		dump($res);

		$pts2->close_channel();
	}

	public function pump_stop($pump_no) {
		dump('TestController@pump_stop()');

		$pts2 = new PTS2();
		$res = $pts2->pump_stop($pump_no);

		dump($res);

		$pts2->close_channel();
	}

	public function pump_resume($pump_no) {
		dump('TestController@pump_resume()');

		$pts2 = new PTS2();
		$res = $pts2->pump_resume($pump_no);

		dump($res);

		$pts2->close_channel();
	}

	public function pump_suspend($pump_no) {
		dump('TestController@pump_suspend()');

		$pts2 = new PTS2();
		$res = $pts2->pump_suspend($pump_no);

		dump($res);

		$pts2->close_channel();
	}

	public function pump_close_transaction($pump_no, $transaction) {
		dump('TestController@pump_close_transaction()');

		$pts2 = new PTS2();
		$res = $pts2->pump_close_transaction($pump_no, $transaction);

		dump($res);

		$pts2->close_channel();
	}



	public function pump_emergency_stop($pump_no) {
		dump('TestController@pump_emergency_stop()');

		$pts2 = new PTS2();
		$res = $pts2->pump_emergency_stop($pump_no);

		dump($res);

		$pts2->close_channel();
	}

	public function pump_get_totals($pump_no, $nozzle) {
		dump('TestController@pump_get_totals()');

		$pts2 = new PTS2();
		$res = $pts2->pump_get_totals($pump_no, $nozzle);

		dump($res);

		$pts2->close_channel();
	}

	public function pump_get_prices($pump_no) {
		dump('TestController@pump_get_prices()');

		$pts2 = new PTS2();
		$res = $pts2->pump_get_prices($pump_no);

		dump($res);

		$pts2->close_channel();
	}

	public function pump_get_display_data($pump_no) {
		dump('TestController@pump_get_display_data()');

		$pts2 = new PTS2();
		$res = $pts2->pump_get_display_data($pump_no);

		dump($res);

		$pts2->close_channel();
	}

	public function pump_get_tag($pump_no, $nozzle) {
		dump('TestController@pump_get_tag()');

		$pts2 = new PTS2();
		$res = $pts2->pump_get_tag($pump_no, $nozzle);

		dump($res);

		$pts2->close_channel();
	}

	public function pump_set_lights($pump_no, $state) {
		dump('TestController@pump_set_lights()');

		$pts2 = new PTS2();
		$res = $pts2->pump_set_lights($pump_no, $state);

		dump($res);

		$pts2->close_channel();
	}






}
