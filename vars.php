<?php

	if(preg_match('!~(.*?)/!', $REQUEST_URI, $matches)) {
		// Try URI first
		$OWNER_USERNAME = $matches[1];
	} else {
		// Then the file itself.
		if($SCRIPT_FILENAME) {
			if(!$OWNER_USERNAME = uid_to_name(fileowner($SCRIPT_FILENAME))) {
				$OWNER_USERNAME = uid_to_name(fileowner($_SERVER['argv'][0]));
			}
		}
	}

	function uid_to_name($id) {
		if(!is_int($id)) {
			return FALSE;
		}
		$pwent = posix_getpwuid($id);
		return $pwent['name'];
	}

?>
