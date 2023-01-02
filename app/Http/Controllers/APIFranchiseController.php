<?php

namespace App\Http\Controllers;

use Log;
use Illuminate\Http\Request;

class APIFranchiseController extends Controller
{   
    private $header;
    private $ch = null;
    private $username;
    private $password;
    public $endpoint;

    //
    public function __construct($endpoint)
    {   
        $this->endpoint = $endpoint;
        $this->header = array('Content-Type: application/json');
    }

    
    public function sendToOceania($ip, $payload){
        $url = 'http://'.$ip.$this->endpoint;
		Log::debug('url='.$url);
        
        $this->ch = curl_init($url);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        //curl_setopt($this->ch, CURLOPT_USERPWD, "$this->username:$this->password");
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, $this->header);
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, $payload);
		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, false);
        
        $resp = curl_exec($this->ch);
        $http_code = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
        $response = json_decode($resp);
        $ret['response'] = $response;
        $ret['http_code'] = $http_code;

        return $ret;
    }


    public function close_channel() {
		if (!empty($this->ch)) {
			curl_close($this->ch);
        }
    }
}
