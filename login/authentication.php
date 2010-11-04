<?php
	$LOGIN_DIR = dirname(__FILE__);

	require_once($LOGIN_DIR."/authenticate_imap.php");
	require_once($LOGIN_DIR."/authenticate_passwd.php");

	if(!defined("LOGIN_AUTH_URL")) 
		define("LOGIN_AUTH_URL", "imap://mail.nbtsc.org/");
	define('LOGIN_AUTHENTICATOR', preg_replace("/:.*/", '', LOGIN_AUTH_URL);

	function authenticate($username, $password) {
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
