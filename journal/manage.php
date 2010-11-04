<?php

	$r = mysql_fetch_all(mysql_query("SELECT * FROM friends WHERE username = '" . JOURNAL_USERNAME ."'"));
	print("<div class='controls'>");
	if(is_array($r)) {
		print("<ul class='friends'><h2>Friends:</h2>");
		foreach($r as $row) {
			extract($row);
			print("<li class='friend'><span class='name'>$friend</span>
			<span class='uri'><a href='$uri'>$uri</a></span></li>");
		}
		print('</ul>');
	}
	print("<div class='toolbar'>");
	print("<a href='$PHP_SELF/Friends/Add'>Add Friend</a>");
	print("</div>");
	
	$r = mysql_fetch_all(mysql_query("SELECT * FROM sidebars WHERE username = '" . JOURNAL_USERNAME ."' ORDER BY title" ));
	if(is_array($r)) {
		print("<ul class='sidebars'><h2>Sidebars:</h2>");
		foreach($r as $row) {
			extract($row);
			print("<li class='sidebar'><span class='title'>$title</span><span class='controls'>". hyperlink("$SCRIPT_URI/Manage/Sidebar/Edit/" . $title, 'Edit') . "</span></li>");
		}
		print('</ul>');
	}
	print("</div>");

	print("<div class='toolbar'>");
	print("<a href='$PHP_SELF/Sidebar/Add'>Add Sidebar</a>");
	print("</div>");
?>
