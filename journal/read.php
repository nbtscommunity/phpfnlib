<?php

	if(!defined('JOURNAL_DISPLAYLIMIT')) {
		define('JOURNAL_DISPLAYLIMIT', 15);
	}

	$q = "SELECT date, timezone, subject, data, contenttype FROM journal 
		WHERE username = '". JOURNAL_USERNAME . "'" . 
		(($date and $time) ? " AND date = '$date $time' " : 
			($date ? " AND date_format(date, '%Y-%m-%d') = '$date'" : "") . 
			($time ? " AND date_format(date, '%H:%i:%s') = '$time'" : "") 
		).
		" ORDER BY date DESC";
	
	if(JOURNAL_DISPLAYMODE == 'oneentry') {
		$q .= " LIMIT 1";
	} else {
		$q .= " LIMIT ".(JOURNAL_DISPLAYLIMIT + 1);
	}
	$r = mysql_query($q);

	if($nsidebars = JOURNAL_SIDEBARS) {
		$sidebars = mysql_fetch_all(mysql_query(
			"SELECT * FROM sidebars 
				WHERE username = '" . JOURNAL_USERNAME . "' 
				ORDER BY timestamp DESC
				LIMIT $nsidebars"));

		print("<div class='sidebars'>");
		foreach($sidebars as $sidebar) {
			extract($sidebar);
			print("<div class='sidebar'><h2
			class='title'>$title</h2><div class='content'>"
			. wiki_render($data) . "</div></div>");
		}
		print("</div>");
	}

	if($r) {
		$r = mysql_fetch_all($r);
		if(count($r) > 1) {
			print(format_rows($r, JOURNAL_DISPLAYLIMIT));
		} else {
			print(format_entry(array_shift($r)));
		}

		if($date and !$time) {
			$q = "SELECT date_format(date, '%Y-%m-%d') AS date 
				FROM journal 
				WHERE username = '".USERNAME."' 
					AND date_format(date, '%Y-%m-%d') > '$date' 
				ORDER BY date LIMIT 1";
			$r = mysql_query($q);
			$row = mysql_fetch_assoc($r);
			$nextdate = $row['date'];

			$q = "SELECT date_format(date, '%Y-%m-%d') AS date 
				FROM journal 
				WHERE username = '".USERNAME."' 
					AND date_format(date, '%Y-%m-%d') < '$date' 
				ORDER BY date DESC LIMIT 1";
			$r = mysql_query($q);
			$row = mysql_fetch_assoc($r);
			$prevdate = $row['date'];
		}

	} else {
		print("No journal entries, sadly");
	}
?>
