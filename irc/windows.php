<?php

	require_once(dirname(__FILE__)."/../string.php");
	require_once(dirname(__FILE__)."/irc.php");

	print("<meta http-equiv='refresh' content='15; URL=\"".purlencode($PHP_SELF)."?NICKNAME=$NICKNAME\"' />");

	$r = mysql_fetch_all(mysql_query("SELECT * FROM windows WHERE nickname = '$NICKNAME'"));

	$w = get_windows();

	foreach($w as $window) {
		$o .= td("<a href='$SCRIPT_NAME/Windows/".purlencode($window)."?NICKNAME=$NICKNAME' target='window'>$window</a>");
	}

	$o .= td("<a href='$SCRIPT_NAME/Logoff?NICKNAME=$NICKNAME' target='_top'>Log Off</a>");
	$o .= td("<a href='$PHP_SELF?NICKNAME=$NICKNAME'>Refresh</a>");

	print(table(tr($o)));

?>
