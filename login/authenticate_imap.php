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
		if(hostname() == 'tauceti') {
			$server = '{localhost:143/imap/tls/novalidate-cert}';
		} else {
			$server = '{localhost:143/imap}';
		}
		if($c = @imap_open($server, $user, $pass, OP_HALFOPEN)) {
			if(LOGIN_IMAP_KEEPCONNECTION) {
				$LOGIN_IMAP_CONNECTION =& $c;
			} else {
				print('Closing connection');
				imap_close($c);
			}
			return TRUE;
		} else {
			return FALSE;
		}
	}

	function login_get_imap_connection() {
		global $LOGIN_IMAP_CONNECTION;
		return $LOGIN_IMAP_CONNECTION;
	}
?>
