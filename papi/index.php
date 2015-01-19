<?php
/*
    API Demo, http://markroland.com/blog/restful-php-api/
 
    This script provides a RESTful API interface for a web application
 
    Input:
 
        $_GET['format'] = [ json | html | xml ]
        $_GET['method'] = []
 
    Output: A formatted HTTP response
 
    Author: Mark Roland
 
    History:
        11/13/2012 - Created
 
*/
 
// --- Step 1: Initialize variables and functions
 
/**
 * Deliver HTTP Response
 * @param string $format The desired HTTP response content type: [json, html, xml]
 * @param string $api_response The desired HTTP response data
 * @return void
 **/
function deliver_response($format, $api_response){
 
    // Define HTTP responses
    $http_response_code = array(
        200 => 'OK',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        403 => 'Forbidden',
        404 => 'Not Found'
    );
   
 
    // Set HTTP Response
    header('HTTP/1.1 '.$api_response['status'].' '.$http_response_code[ $api_response['status'] ]);
 
    // Process different content types
    if( strcasecmp($format,'json') == 0 ){
 
        // Set HTTP Response Content Type
        header('Content-Type: application/json; charset=utf-8');
 
        // Format data into a JSON response
        $json_response = json_encode($api_response);
 
        // Deliver formatted data
        echo $json_response;
 
    }elseif( strcasecmp($format,'xml') == 0 ){
 
        // Set HTTP Response Content Type
        header('Content-Type: application/xml; charset=utf-8');
 
        // Format data into an XML response (This is only good at handling string data, not arrays)
        $xml_response = '<?xml version="1.0" encoding="UTF-8"?>'."\n".
            '<response>'."\n".
            "\t".'<code>'.$api_response['code'].'</code>'."\n".
            "\t".'<data>'.$api_response['data'].'</data>'."\n".
            '</response>';
 
        // Deliver formatted data
        echo $xml_response;
 
    }else{
 
        // Set HTTP Response Content Type (This is only good at handling string data, not arrays)
        header('Content-Type: text/html; charset=utf-8');
 
        // Deliver formatted data
        echo $api_response['data'];
 
    }
 
    // End script process
    exit;
 
}
 
// Define whether an HTTPS connection is required
$HTTPS_required = FALSE;
 
// Define whether user authentication is required
$authentication_required = FALSE;
 
// Define API response codes and their related HTTP response
$api_response_code = array(
    'unknown_error' => array('HTTP Response' => 400, 'Message' => 'Unknown Error'),
    'success' => array('HTTP Response' => 200, 'Message' => 'Success'),
    'https_required' => array('HTTP Response' => 403, 'Message' => 'HTTPS Required'),
    'authentication_required' => array('HTTP Response' => 401, 'Message' => 'Authentication Required'),
    'authentication_failed' => array('HTTP Response' => 401, 'Message' => 'Authentication Failed'),
    'invalid_request' => array('HTTP Response' => 404, 'Message' => 'Invalid Request'),
    'invalid_response_format' => array('HTTP Response' => 400, 'Message' => 'Invalid Response Format')
);
 
// Set default HTTP response to unknown_error 'ok'
$response['code'] = 'unknown_error';
$response['status'] = 400;
$response['data'] = NULL;
 
// --- Step 2: Authorization

// --- Step 3: Process Request

function find_party($party, $data) {
  foreach ($data as $row) {
    if (isset($row->abbreviation) and ($row->abbreviation == $party))
        return $row;
    if (isset($row->name) and ($row->name == $party))
        return $row;
  }
  $default = new StdClass();  
  $default->color = '#' . str_pad(dechex(mt_rand(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT); //random color
  $default->abbreviation = $abbr;
  $default->position = 0;
  return $default;
}

function sort_object_by_position($a, $b) {
	if($a->position == $b->position){ return 0 ; }
	return ($a->position < $b->position) ? -1 : 1;
}

// filename
if (isset($_GET['set']))
    $filename = $_GET['set'] . '.json';
else
    $filename = 'data.json';

//
$out = [];
if (isset($_GET['parties'])) {
    $data = json_decode(file_get_contents($filename));
    if (is_array($_GET['parties'])) {
        foreach ($_GET['parties'] as $row) {
            $out[] = find_party($row,$data);
        }
        usort($out,'sort_object_by_position');
    } else {
        $out[] = find_party($_GET['parties'],$data);
    }
    $response['code'] = 'success';
    $response['data'] = $out;
} else {
    $response['code'] = 'invalid_request';
}
$response['status'] = $api_response_code[ $response['code'] ]['HTTP Response'];

// --- Step 4: Deliver Response
 
// Return Response to browser
    //set default:
if (isset($_GET['format']))
  $format = $_GET['format'];
else
  $format = 'json';
if ((!($format)) or (!in_array(strtolower($format),['json']))) $format = 'json';
deliver_response($format, $response);
 
?>
