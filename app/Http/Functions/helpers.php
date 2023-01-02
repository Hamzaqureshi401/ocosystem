<?php

use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\User;
use App\Classes\UserData;
use App\Models\_function;
use App\Models\usersfunction;


// QR Code generator
function qr_code_generator($qr_id) {
	return DNS2D::getBarcodePNG($qr_id, "QRCODE");
}

// Barcode Generator
function bar_code_generator($qr_id) {
	return DNS1D::getBarcodePNG($qr_id, "C128");
}

// For 3 digit sequence number
function seq_number_fix($number) {
	if(strlen($number) > 4)
		return substr($number, 2);
	if(strlen($number) > 3)
		return substr($number, 1);
	if(strlen($number) > 2)
		return substr($number, 0);
	if(strlen($number) > 1)
		return '0' . $number;
	if(strlen($number) > 0)
		return '00' . $number;
	return $number;
}

// To remove leading zeros from table prefix
function table_number_fix($number) {
	if ($number == 0) {
	  return '';
	}
	return (int) $number;
}

function technicians() {
	$user_data = new UserData();
	$function = _function::where("name", 'technician')->first();
	$user_ids = usersfunction::where('company_id', $user_data->company_id())->
		where("function_id", $function->id)->
		get()->
		pluck('user_id');

	$technicians = User::orderBy("name")->
		whereIn("id", $user_ids)->
		get();

	return $technicians;
}
