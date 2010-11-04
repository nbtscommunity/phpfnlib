<?php

	require_once(dirname(__FILE__) . "/../mysql.php");

	$q = mysql_query("SELECT * FROM todo ORDER BY duedate,  WHERE username = " . TODO_USERNAME);

	$r = mysql_fetch_all(mysql_query($q));

	print('<table>');
	foreach($r as $row) {
		extract($row);
		print("<tr><td>$duedate</td><td>$item</td></tr>");
	}
	print('</table>');
?>
