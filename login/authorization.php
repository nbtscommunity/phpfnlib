<?php

	function authorized($username, $service) {
		if($service == 'http') { 
			return TRUE;
		} elseif($service == 'postcomment' and is_logged_in()) {
			return TRUE;
		} elseif(is_logged_in() and ($service == 'updatejournal') and ($username == JOURNAL_USERNAME)) {
			return TRUE;
		} else {
			return FALSE;
		}
	}
?>
