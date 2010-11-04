<?php

	if($REQUEST_METHOD == 'POST' and (authorized($USERNAME, 'postcomment') or JOURNAL_ANONYMOUSPOSTS)) {
		if($un = is_logged_in() or JOURNAL_ANONYMOUSPOSTS) {
			if(JOURNAL_ANONYMOUSPOSTS) {
				$un = "Anonymous ($poster)";
			}
			$id = insert_message($un, $subject, $body);
			if($id) {
				$q = "insert into entrymessages (entrydate, entryuser, messageid) values
				('$date $time', '" . JOURNAL_USERNAME ."', '$id');"; 
				if(mysql_query($q)) {
					print('Posted.');
				} else {
					print('Error: '. mysql_error());
				}
			} else {
				print('Error: '. mysql_error());
			}
		} else {
			print("You must be logged in.  <a href='$SCRIPT_URI/Login'>Go here</a>.");
		}
	} else {
		// Show Form
		print("<form action='$PHP_SELF' method='POST'>".
			((JOURNAL_ANONYMOUSPOSTS and !is_logged_in()) ? ("Your name: ".field('poster')) : "").
			($journal ? hidden('journal', $journal) : "").
			($replyto ? hidden('replyto', $replyto) : "").
			"<table>".
				"<tr><td>Subject:</td><td><input type='text' name='subject' size='40' /></td></tr>".
				"<tr><td>Message:</td><td><textarea name='body' cols='40' rows='10'></textarea></td></tr>".
				"<tr><td><input type='submit' value='Post'></td></tr>".
			"</table>".
		"</form>");
			
	}
?>
