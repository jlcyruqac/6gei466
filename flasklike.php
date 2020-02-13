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

	// call the proper route - pregmatch
	foreach ($route_defs as $key => $value) {
		$safe_key = $key."$";
		$safe_key = str_replace("*", "(.*)", $safe_key);
		//error_log("Looking for : ".$safe_key." In : ".$route);
		if (preg_match("#".$safe_key."#", $route, $out)) {
			if (array_key_exists($method, $route_defs[$key])) {
				call_user_func_array($route_defs[$key][$method], $_SERVER);
			} else {
				// Return : Method not implemented - 
				header($_SERVER["SERVER_PROTOCOL"]." 501 Method not implemented", true, 501);
				echo "501 - Method not implemented";
			}
		}
	}

	header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found", true, 404);
	echo "404 - File not found";
	die();

	// call the proper route - absolute match
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
// Lazy mime-type detection
// Args : filename (complete path) 
// Return : text string containing mime-type
function lazy_mime_type($filename) {

	if (preg_match("#\.css$#", $filename)) {
		return "text/css";
	} elseif (preg_match("#\.(jpg|jpeg)$#", $filename)) {
		return "image/jpeg";
	}  elseif (preg_match("#\.js$#", $filename)) {
		return "text/javascript";
	}  elseif (preg_match("#\.png$#", $filename)) {
		return "image/png";
	}  elseif (preg_match("#\.pdf$#", $filename)) {
		return "application/pdf";
	}  elseif (preg_match("#\.txt$#", $filename)) {
		return "text/plain";
	}  elseif (preg_match("#\.js$#", $filename)) {
		return "text/javascript";
	}  elseif (preg_match("#\.otf$#", $filename)) {
		return "font/otf";
	}  elseif (preg_match("#\.(htm|html)$#", $filename)) {
		return "text/html";
	}
	return "application/octet-stream";
}

////////////////////////////////////////////////////////////////
// Redirect user to a new page
// Args : New url
function fl_static($url) {
    $file_location = $_SERVER["DOCUMENT_ROOT"] . $url;
    $file_location = str_replace("/", "\\", $file_location);
    if (file_exists($file_location)) {
        header($_SERVER["SERVER_PROTOCOL"] . " 200 OK");
        header("Cache-Control: public"); // needed for internet explorer
        // For this to work uncomment module extension=fileinfo in php.ini
        // If php is installed with scoop it's in user/scoop/persist/php/cli
        // header("Content-Type: ".mime_content_type($file_location));
        header("Content-Type: ".lazy_mime_type($file_location));
        header("Content-Length:".filesize($file_location));
        //header("Content-Disposition: attachment; filename=images.jpg");
		error_log("Returning : ".$file_location." (".filesize($file_location).")");
        readfile($file_location);
        flush();
        die();
    } else {
		header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found", true, 404);
		echo "404 - File not found : ".$file_location;    	
    }
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