<?php
	if($REQUEST_METHOD == 'POST') {
		if($data) {
			$q = "REPLACE INTO sidebars (title, data, username) 
				VALUES ('$sidebar', '$data', '".JOURNAL_USERNAME."')";

			if(mysql_query($q)) { 
				print("Updated.");
			} else {
				print(mysql_error());
			}
		} else {
			print("You have to enter /something/.");
		}
	} else {
		$r = mysql_fetch_all(mysql_query(
			"SELECT * FROM sidebars WHERE username = '". JOURNAL_USERNAME ."' 
				AND title = '$sidebar'"));
		extract($r[0]);
		print("<form action='$PHP_SELF' method='POST'>");
		print(table(
			row1(heading(2, $sidebar)).
			row1(textarea('data', $data)).
			row1("<input type='submit' value='Add' />")));
		print("</form>");
	}
?>
