<?php

	# In the user's homedir
	$USER_OPTIONS_FILE = ".journalrc"

	# Loads a user's journal options
	# Options will be variables
	function load_user_options() {
echo "L<br />";
		$options = $HOME."/".$USER_OPTIONS_FILE;
		if (file_exists($options)) {
echo "E<br />";
			include_once($options);
		}
	}
?>
