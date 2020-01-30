<?php
/* 
	Title: Flasklike
	Descr: Mini-Framework (router)
	Author: Jean-Luc Cyr
	Date: 2019/10/18
*/

////////////////////////////////////////////////////////////////
// Add a default / route
//global $route_defs;
$route_defs['/']['GET'] = 
function(){
	echo "Hello World!";
};

////////////////////////////////////////////////////////////////
// Running the controller to process the request
// Args : None
function fl_run() {
	// handle the routes
	// retrieve method/route
	$method = $_SERVER['REQUEST_METHOD'];
	$route = $_SERVER['REQUEST_URI'];
	// use global route definitions
	global $route_defs;
	// call the proper route
	if (array_key_exists($route, $route_defs)) {
		if (array_key_exists($method, $route_defs[$route])) {
			call_user_func_array($route_defs[$route][$method], $_SERVER);
		} else {
			// Return : Method not implemented - 
			header($_SERVER["SERVER_PROTOCOL"]." 501 Method not implemented", true, 501);
			echo "501 - Method not implemented";
		}
	} else {
		// Return : Not Found - 404
		header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found", true, 404);
		echo "404 - File not found";
	}
	die();
}

////////////////////////////////////////////////////////////////
// Redirect user to a new page
// Args : New url
function fl_redirect($url) {
	header('Location: ' . $url, true, 302);
	die();
}

////////////////////////////////////////////////////////////////
// Template engine
// Args : filename of the template, optionnal array of key/value to fill template
function fl_render_template($filename, ...$values_optional)  {
	if (isset($values_optional[0])) {
		$values = $values_optional[0];
	} else {
		$values = NULL;
	}
	$page = file_get_contents($filename);
	// find all vars placeholder
	preg_match_all("|{{(.*)}}|U", $page, $out, PREG_PATTERN_ORDER);
	foreach($out[0] as $code) {
		$var = str_replace('{{', '', $code);
		$var = str_replace('}}', '', $var);
		$var = trim($var);
		$val = "";
		// check if value is defined
		if ($values!=NULL) {
			if (array_key_exists($var, $values)) {
				$val = $values[$var];
			} else {
				$val = "";
			}
		}
		// replace value
		$page = str_replace($code, $val, $page);
	}
	// find all code placeholder - TAKE CARE OF INJECTIONS!!!
	preg_match_all("|{%(.*)%}|U", $page, $out, PREG_PATTERN_ORDER);
	foreach($out[0] as $code) {
		$var = str_replace('{%', '', $code);
		$var = str_replace('%}', '', $var);
		$var = trim($var);
		ob_start();
		eval($var);
		$val = ob_get_contents();
		ob_end_clean();
		// replace value
		$page = str_replace($code, $val, $page);
	}
	// return rendered content
	echo $page;
	return;
}

////////////////////////////////////////////////////////////////
// Authentication validation
// Args : filename of the template, optionnal array of key/value to fill template
// Return : True if auth of False elswhere
function fl_auth(...$auth_info) {
	// check for cookie

	// check auth_info
	if ( (isset($auth_info[0])) && (isset($auth_info[1])) ) {
		$user = $auth_info[0];
		$pass = $auth_info[1];
		if (($user=='test')&&($pass=='test')) {
			// create session + cookie
			return True;
		}
	}
	return False;
}