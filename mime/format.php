<?php
	function mime_format_header($header, $style='table') {
		extract($header);
		return '<table>'.
			($date?"<tr><th>Date:</th><td>$date</td></tr>":'').
			($from?"<tr><th>From:</th><td>$from</td></tr>":'').
			($to?"<tr><th>To:</th><td>$to</td></tr>":'').
			($subject?"<tr><th>Subject:</th><td>$subject</td></tr>":'').
			($title?"<tr><th>Title:</th><td>$title</td></tr>":'').
			($in_reply_to?"<tr><th>In Reply To:</th><td>$in_reply_to</td></tr>":'').
			'</table>';
	}
?>
