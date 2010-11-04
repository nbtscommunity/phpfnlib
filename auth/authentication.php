<?php
	require_once(dirname(__FILE__)."/authenticate_imap.php");
	require_once(dirname(__FILE__)."/authenticate_passwd.php");
	require_once(dirname(__FILE__)."/../errors.php");
	require_once(dirname(__FILE__)."/../log/syslog.php");

	if(!defined('LOGIN_AUTHENTICATORS')) {
		define('LOGIN_AUTHENTICATORS', 'imap');
	}

	define('AUTH_SERVFAIL', 500);
	define('AUTH_DENY', 403);
	define('AUTH_SUCCESS', 200);

	define("AUTH_VERIFY", 1);
	define("AUTH_PROBE", 2);
	
	function authenticate_allow($u, $p) {
		return AUTH_SUCCESS;
	}

	function auth_error() {
		global $AUTH_ERR;
		return $AUTH_ERR;
	}

	function authenticate_deny ($u, $p) {
		return AUTH_DENY;
	}

	function authenticate($username, $password, $mode = AUTH_VERIFY) {
		$authenticators = split('\s+,\s+', LOGIN_AUTHENTICATORS);
		while(count($authenticators) > 0) {
			$af = 'authenticate_'.array_shift($authenticators);
			if(($authstat = $af($username, $password)) != AUTH_SERVFAIL) {
				if($mode != AUTH_PROBE) u_syslog("Authenticated $username");
				return $authstat;
			}
		}
		if($mode != AUTH_PROBE) u_syslog("Failed to authenticate $username");
		return AUTH_DENY;
			
		// Obsolete:
			
		if(LOGIN_AUTHENTICATOR == 'imap') {
			return authenticate_imap($username, $password);
		} elseif(LOGIN_AUTHENTICATOR == 'passwd') {
			return authenticate_passwd($username, $password);
		} else {
			return FALSE;
		}

		// Obsolete:
		if(USERNAME == 'aredridel') {
			if($username == 'aredridel' and $password == 'arcanixdesire') {
				return true;
			} else {
				return false;
			}
		} elseif(USERNAME == 'toodamnperky') {
			if(($username == 'toodamnperky' or $username == 'marina') and $password == 'perkyisgood') {
				return true;
			} else {
				return false;
			}
		} elseif(USERNAME == 'jadzia') {
			$crypted = '16BeVr1SnBnrQ';
			if(($username == 'jadzia') 
				and crypt($password, $crypted) == $crypted) {
				return true;
			} else {
				return false;
			}
		} elseif(USERNAME == 'ryland') {
			$crypted = '$1$Ne2wTZ2g$ykyhHrAD1fXAAvSSHCOSQ0';
			if(($username == 'ryland') 
				and crypt($password, $crypted) == $crypted) {
				return true;
			} else {
				return false;
			}
		} elseif(USERNAME == 'carrie') {
			if($username = 'carrie' and $password = 'password') {
				return TRUE;
			} else {
				return FALSE;
			}
		} elseif(USERNAME == 'thelhf') {
			if(($username == 'thelhf') 
				and $password == 'smart') {
				return true;
			} else {
				return false;
			}

		}

	}
?>
