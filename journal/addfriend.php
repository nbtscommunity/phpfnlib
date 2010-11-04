<?php
	if($REQUEST_METHOD == 'POST') {
		if($friend and $uri) {
			$q = "INSERT INTO friends (friend, uri, username) 
				VALUES ('$friend', '$uri', '".JOURNAL_USERNAME."')";

			if(mysql_query($q)) { 
				print("Added.");
			} else {
				print(mysql_error());
			}
		} else {
			print("You have to enter both a name and URI.");
		}
	} else {
		print("<form action='$PHP_SELF' method='POST'>");
		print(hidden('mode', 'addfriend'));
		print("Friend's Name: <input type='text' name='friend' />");
		print("<br />URI: <input type='text' name='uri' />");
		print("<br /><input type='submit' value='Add' />");
		print("</form>");
		//
	}
?>
