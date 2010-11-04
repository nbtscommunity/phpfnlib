<?php
	if(!defined('LOGIN_IMAP_KEEPCONECTION')) {
		define('LOGIN_IMAP_KEEPCONNECTION', FALSE);
	}

	function hostname() {
		$o = posix_uname();
		return($o['nodename']);
	}

	function authenticate_imap($user, $pass) {
		global $LOGIN_IMAP_CONNECTION;
		global $AUTH_ERR;
		if(hostname() == 'tauceti') {
			$server = '{localhost:143/imap/tls/novalidate-cert}';
		} elseif (hostname() == 'Daneel.dynamic.wondermill.com') {
			$server = '{localhost:143/imap/notls}';
		} else {
			$server = '{localhost:143/imap/tls/novalidate-cert}';
		}
		if($c = imap_open($server, $user, $pass, OP_HALFOPEN)) {
			if(LOGIN_IMAP_KEEPCONNECTION) {
				$LOGIN_IMAP_CONNECTION =& $c;
			} else {
				//debug('Closing connection');
				imap_close($c);
			}
			return AUTH_SUCCESS;
		} else {
			if($AUTH_ERR = imap_last_error()) {
				return AUTH_SERVFAIL;
			} else {
				return AUTH_DENY;
			}
		}
	}

	function login_get_imap_connection() {
		global $LOGIN_IMAP_CONNECTION;
		return $LOGIN_IMAP_CONNECTION;
	}
?>
