<?php

require_once("SimpleLogger.php");

function base_url() {

	return $_SERVER["REQUEST_URI"];
}

/**
 * Get token from header.
 *
 * @return string encoded JWT, bool false if token header does not exist
 */
function get_access_header() {

    $header = false;
	$logger = new SimpleLogger();

    if (!function_exists('getallheaders')) {
    function getallheaders() {
    $headers = [];
    foreach ($_SERVER as $name => $value) {
        if (substr($name, 0, 5) == 'HTTP_') {
            $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
        }
    }
    return $headers;
    }
}
	$logger->log("Server var:");
	$logger->log(print_r($_SERVER,1));
    $requestHeaders = getallheaders();
	$logger->log("Req headers:");
	$logger->log(print_r($requestHeaders,1));
    if(isset($requestHeaders['x-access-token'])) {
        $header = trim($requestHeaders['x-access-token']);
    }
    else if(isset($requestHeaders['X-Access-Token'])) {
        $header = trim($requestHeaders['X-Access-Token']);
    }
	$logger->log($header);
    return $header;
}

/**
 * determines if ajax call was made
 */
function is_ajax() {
	return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'));
}
