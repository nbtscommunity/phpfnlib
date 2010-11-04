<?php
	require("rcs.php");

	if(succeeds($f = rcs_load("testfile", "Current"))) {
		print($f);
	} else {
		print(errmsg());
	}
	
	if(succeeds($f = rcs_load("testfile", "1.1"))) {
		print($f);
	} else {
		print(errmsg()."\n");
	}
	
?>
