<?php

	require_once(dirname(__FILE__)."/../html.php");
	require_once(dirname(__FILE__)."/../file.php");
	require_once(dirname(__FILE__)."/edit.php");
	
	if(defined('EDIT_FILE')) {
		if($REQUEST_METHOD == 'POST') {
			if(edit_save(EDIT_FILE, $data)) {
				print("Saved");
			} else {
				die("Couldn't save file");
			}
		} else {
			print(edit_form(EDIT_FILE));
		}
	} else {
		die("No file specified.");
	}

?>
