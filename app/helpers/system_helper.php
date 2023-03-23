<?php

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

    // For php-fpm: define the getallheaders() function if it is not defined
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

    $requestHeaders = getallheaders();
    if(isset($requestHeaders['x-access-token'])) {
        $header = trim($requestHeaders['x-access-token']);
    }
    else if(isset($requestHeaders['X-Access-Token'])) {
        $header = trim($requestHeaders['X-Access-Token']);
    }

    return $header;
}

/**
 * determines if ajax call was made
 */
function is_ajax() {
	return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'));
}