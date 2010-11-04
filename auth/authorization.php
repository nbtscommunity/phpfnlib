<?php

	require_once(dirname(__FILE__)."/authentication.php");

	function authorized($username, $service, $mode = AUTH_VERIFY) {
		if($service == 'http') { 
			if($mode != AUTH_PROBE) u_syslog("Gave $service access to $username");
			return TRUE;
		} elseif($service == 'postcomment' and is_logged_in()) {
			if($mode != AUTH_PROBE) u_syslog("Gave $service access to $username");
			return TRUE;
		} elseif(is_logged_in() and ($service == 'updatejournal') and ($username == JOURNAL_USERNAME)) {
			if($mode != AUTH_PROBE) u_syslog("Gave $service access to $username");
			return TRUE;
		} else {
			if($mode != AUTH_PROBE) u_syslog("Denied $service access to $username");
			return FALSE;
		}
	}
?>
