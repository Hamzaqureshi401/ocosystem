<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ApiMessageController extends Controller
{

public function queryexception($ex)
{
	return \Response::json(array(
        'Exception' => $ex,
        'status' => 400,
        'error' => true,
        'message' => "Query Exception"
    ),400);
}


public function failedresponse($message)
{
    return \Response::json(array(
        'Exception' => "",
        'status' => 400,
        'error' => true,
        'message' => $message,
        'data' => []
	), 400);
}


public function validatemessage($data = [], $message = "Please fill all the fields")
{
    return \Response::json(array(
        'Exception' => "",
        'status' => 400,
        'error' => true,
        'message' => $message,
        'data' => $data
    ), 400);
}

public function saveresponse($message)
{
    return \Response::json(array(
        'Exception' => "",
        'status' => 200,
        'error' => false,
        'message' => $message
    ));
}
public function uniqueresponse($message)
{
    return \Response::json(array(
        'Exception' => "",
        'error' => false,
        'message' => $message
    ));
}



public function successResponse($data = null, $message = null)
{
    return \Response::json(array(
        'Exception' => "",
        'status' => 200,
        'error' => false,
        'message' => $message,
        'data' => $data

    ));
}

public function forbiddenResponse($message = "You are not authorized to perform this action.")
{
        # code...
    return \Response::json(array(
        'Exception' => "",
        'status' => 403,
        'error' => true,
        'message' => $message,


    ),403);
}

static function alertMessage($message)
{
    $data = [
       'message' => $message,
       'status' => 400 
    ];
   return $data; 
}
}




