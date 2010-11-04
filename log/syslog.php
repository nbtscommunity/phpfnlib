<?php

	require_once(dirname(__FILE__)."/../vars.php");

	define_syslog_variables();

	function u_openlog($app) {
		global $OWNER_USERNAME, $_SYSLOG_FD, $_SYSLOG_MODE;
		if($f = @fopen("/home/users/$OWNER_USERNAME/.weblog", "a+")) {
			$_SYSLOG_FD = $f;
			$_SYSLOG_MODE = 'file';
			return TRUE;
		} else {
			$_SYSLOG_MODE = 'syslog';
			openlog($app, LOG_PID, LOG_USER);
			return TRUE;
		}
		return FALSE;
	}

	function u_syslog($str) {
		global $_SYSLOG_FD, $_SYSLOG_MODE;
		if(!$_SYSLOG_MODE) {
			u_openlog('unknown');
		}
		if($_SYSLOG_MODE == 'syslog') {
			syslog(LOG_DEBUG, $s);
			return TRUE;
		} elseif($_SYSLOG_MODE == 'file') {
			$uname = posix_uname();
			if(fputs($_SYSLOG_FD, date("M d H:i:s")." ".$uname['nodename']." ".$str."\n")) {
				return TRUE;
			}
		}
		return FALSE;
	}

?>
