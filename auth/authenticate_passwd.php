<?php
	if(!defined('LOGIN_AUTHENTICATOR_PASSWD_FILE')) {
		define('LOGIN_AUTHENTICATOR_PASSWD_FILE', '.htpasswd');
	}

	if(!defined('LOGIN_AUTHENTICATOR_PASSWD_CRYPT')) {
		define('LOGIN_AUTHENTICATOR_PASSWD_CRYPT', TRUE);
	}

	function authenticate_passwd($user, $pass) {
		$f = file(LOGIN_AUTHENTICATOR_PASSWD_FILE);
		$pass = trim($pass);
		if(!is_array($f)) {
			return FALSE; //SERVFAIL
		} else {
			foreach($f as $line) {
				list($username, $crypted) = explode(':', $line, 2);
				$crypted = trim($crypted);
				if($username == $user) {
					if(LOGIN_AUTHENTICATOR_PASSWD_CRYPT) {
						if(crypt($pass, $crypted) == $crypted) {
							return TRUE;
						} else {
							return FALSE;
						}
					} else {
						if($pass == $crypted) {
							return TRUE;
						} else {
							return FALSE;
						}
					}
				}
			}
			return FALSE;
		}
	}
?>
