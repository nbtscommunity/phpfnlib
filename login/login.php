<?php
	require_once(dirname(__FILE__)."/../sessions/sessions.php");
	require_once(dirname(__FILE__)."/../errors.php");
	require_once(dirname(__FILE__)."/../auth/authentication.php");
	require_once(dirname(__FILE__)."/../auth/authorization.php");

	if(!defined("LOGIN_SERVICE")) define("LOGIN_SERVICE", 'http'); 
	if(!defined("LOGIN_STYLE")) define("LOGIN_STYLE", 'form'); 
	if(!defined("LOGIN_TIMEOUT")) define("LOGIN_TIMEOUT", 0);

	function is_logged_in($ns = NULL) {
		static $LOGGED_IN;
		if($ns !== NULL) {
			$LOGGED_IN = $ns;
		}
		return $LOGGED_IN;
	}

	if(isset($_SESSION['LOGIN_USERNAME'])) { is_logged_in(true); }
	if(isset($_SERVER['REMOTE_USER'])) { is_logged_in(true); }

	if(isset($_SERVER['Authorization']))
		list($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']) = explode(':', base64_decode(substr($_SERVER['Authorization'], 6)), 2);

	function logout() {
		global $_SESSION, $_GLOBALS;
		is_logged_in(false);
		$_SESSION = array();
		//session_destroy();
	}

	function login_get_username() {
		global $LOGIN_USERNAME;
		return $LOGIN_USERNAME;
	}

	function login_get_password() {
		global $LOGIN_PASSWORD;
		return $LOGIN_PASSWORD;
	}

	function login_do_http_auth() {
		global $LOGIN_PASSWORD, $LOGIN_USERNAME;
		global $_SERVER;

		if($_SERVER['REMOTE_USER']) {
			is_logged_in(true);
			return;
		}
		
		if(!$_SERVER['PHP_AUTH_USER']) {
			is_logged_in(false);
			return;
		}
		$status = authenticate($_SERVER['PHP_AUTH_USER'],
			$_SERVER['PHP_AUTH_PW']);
		if(!succeeds($status)) {
			is_logged_in(false);
			if(!fatal($status)) {
				if($_SERVER['PHP_AUTH_USER']) {
					http_401();
				}
			} else {
				print("Error logging in: ".auth_error());
			}
		} else {
			$LOGIN_USERNAME = $_SERVER['PHP_AUTH_USER'];
			$LOGIN_PASSWORD = $_SERVER['PHP_AUTH_PW'];
			is_logged_in(true);
		}

	}

?>
