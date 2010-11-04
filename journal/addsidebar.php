<?php
	if($REQUEST_METHOD == 'POST') {
		if($sidebarname) {
			$sidebarname = addslashes($sidebarname);
			$q = "INSERT INTO sidebars (title, contenttype, username) 
				VALUES ('$sidebarname', '$contenttype', '".JOURNAL_USERNAME."')";

			if(mysql_query($q)) { 
				print("Added.");
			} else {
				print(mysql_error());
			}
		} else {
			print("You have to enter a title.");
		}
	} else {
		print("<form action='$PHP_SELF' method='POST'>");
		print(table(
			row2("Sidebar Title:","<input type='text' name='sidebarname' />").
			row2("Content-type:", 
					"<input type='radio' name='contenttype' ".
						"value='text/wiki' / checked='checked'>WikiWiki ".
					"<input type='radio' name='contenttype' ".
						"value='text/html' /> HTML").
			row2('', "<input type='submit' value='Add' />")));
		print("</form>");
	}
?>
