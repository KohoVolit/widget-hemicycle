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
/* 
// Optionally require connections to be made via HTTPS
if( $HTTPS_required && $_SERVER['HTTPS'] != 'on' ){
    $response['code'] = 'https_required';
    $response['status'] = $api_response_code[ $response['code'] ]['HTTP Response'];
    $response['data'] = $api_response_code[ $response['code'] ]['Message'];
 
    // Return Response to browser. This will exit the script.
    deliver_response($_GET['format'], $response);
}
 
// Optionally require user authentication
if( $authentication_required ){
 
    if( empty($_POST['username']) || empty($_POST['password']) ){
        $response['code'] = 'authentication_required';
        $response['status'] = $api_response_code[ $response['code'] ]['HTTP Response'];
        $response['data'] = $api_response_code[ $response['code'] ]['Message'];
 
        // Return Response to browser
        deliver_response($_GET['format'], $response);
 
    }
 
    // Return an error response if user fails authentication. This is a very simplistic example
    // that should be modified for security in a production environment
    elseif( $_POST['username'] != 'foo' && $_POST['password'] != 'bar' ){
        $response['code'] = 'authentication_failed';
        $response['status'] = $api_response_code[ $response['code'] ]['HTTP Response'];
        $response['data'] = $api_response_code[ $response['code'] ]['Message'];
 
        // Return Response to browser
        deliver_response($_GET['format'], $response);
 
    }
 
}
 */
// --- Step 3: Process Request
 
// Method A: Say Hello to the API
function optimal_rows($n) {
  $out = floor(pow(((int)$n)/2 + 1, 0.48));
  return (string) $out;
  
}
if (isset($_GET['n'])) {
    $data = json_decode(file_get_contents("data.json"));
    if (isset($data->$_GET['n'])) {
      if (isset($_GET['rows'])) {
        if (isset($data->$_GET['n']->$_GET['rows'])) {
          $response['code'] = 'success';
          $response['data'] = $data->$_GET['n']->$_GET['rows'];
        } else {
          $response['code'] = 'invalid_request';
        }
      } else {
        $response['code'] = 'success';
        $rows = optimal_rows($_GET['n']);
        $response['data'] = $data->$_GET['n']->$rows;
      }
    } else {
      $response['code'] = 'invalid_request';
    }

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
