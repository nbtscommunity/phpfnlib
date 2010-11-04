<?php

	require_once(dirname(__FILE__)."/../html.php");
	require_once(dirname(__FILE__)."/login.php");
	require_once(dirname(__FILE__)."/../auth/authentication.php");

	if(!defined("LOGIN_SERVICE")) { define("LOGIN_SERVICE", 'http'); }

	if(isset($PHP_AUTH_USER) and isset($PHP_AUTH_PW)) {
		if (!authenticate($PHP_AUTH_USER, $PHP_AUTH_PW)) {
			http_401();
			print("Access Denied");
			exit();
		} else {
			$LOGIN_USERNAME = $PHP_AUTH_USER;
			$LOGIN_PASSWORD = $PHP_AUTH_PW;
		}
	} else {
		http_403();
		Header("WWW-Authenticate: Basic realm='Log In'");
		print("Please Log in");
		exit();
	}

			
?>
