<?php
	session_set_cookie_params(360000);
	if((isset($_COOKIES) and $_COOKIES['PHPSESSID']) or isset($_REQUEST['PHPSESSID'])) {
		session_start();
		if($_COOKIES['debug'] == 'true') {
			print("Session started<br />");
		}
	}
?>
