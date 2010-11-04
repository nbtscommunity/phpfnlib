<?php
	$sidebars = mysql_fetch_all(mysql_query(
		"SELECT * FROM sidebars 
			WHERE username = '" . JOURNAL_USERNAME . "' 
				AND title = '$sidebar'"));

	foreach($sidebars as $sidebar) {
		extract($sidebar);
		print("<div class='sidebar'><h2
		class='title'>$title</h2><div class='content'>"
		. wiki_render($data) . "</div></div>");
	}
?>
