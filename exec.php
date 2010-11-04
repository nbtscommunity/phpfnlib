<?php
	
	function do_exec($command, $output = NULL) {
		exec($command . ' 2>&1', &$output, $retval);
		return $retval;
	}

?>
