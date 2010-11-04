<?php

	if(isset($HTTP_SERVER_VARS) and is_array($HTTP_SERVER_VARS) and !is_array($_SERVER)) {
		$_SERVER =& $HTTP_SERVER_VARS;
		$_POST =& $HTTP_POST_VARS;
		$_GET =& $HTTP_GET_VARS;
		$_ENV =& $HTTP_ENV_VARS;
		$_COOKIE =& $HTTP_COOKIE_VARS;
		$_FILES =& $HTTP_POST_FILES;
	} 
	
	if(isset($_COOKIE['PHPSESSID'])) {
		session_start();
	}

	if(isset($HTTP_SESSION_VARS) and !is_array($_SESSION) and is_array($HTTP_SESSION_VARS)) {
		$_SESSION =& $HTTP_SESSION_VARS;
	}

	extract($_ENV);
	extract($_COOKIE);
	extract($_GET);
	extract($_POST);
	if(is_array($_FILES)) {
		extract($_FILES);
	}
	if(isset($_SESSION) and is_array($_SESSION)) {
		extract($_SESSION);
	}
	extract($_SERVER);

?>
