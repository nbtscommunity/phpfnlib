<?php

	require_once(dirname(__FILE__)."/../mysql.php");
	require_once(dirname(__FILE__)."/../wiki/render.php");

	function insert_message ($poster, $subject, $body) {
		global $messageboard_db, $messageboard_table;
		if(!$messageboard_table) { $messageboard_table = 'messages'; }
		$q = "insert into $messageboard_table (date, poster, subject, body) values (NOW(), '$poster', '$subject', '$body');";
		if(mysql_query($q, $messageboard_db)) {

			$q = "select last_insert_id() as id;";
			$r = mysql_query($q, $messageboard_db);
			$r = mysql_fetch_assoc($r);

			return($r['id']);
		} else {
			return FALSE;
		}
	}

	function get_messages($quals = NULL, $table = '') {
		global $messageboard_db, $messageboard_table;
		if(!$messageboard_table) { $messageboard_table = 'messages'; }
		if(!$table) {
			$ids = $quals;
			if(!is_array($ids) or count($ids) == 0) {
				return array();
			}
		} else {
			unset($ids);
		}
		$q = "SELECT $messageboard_table.poster AS poster, $messageboard_table.subject AS subject,
			$messageboard_table.date AS date, $messageboard_table.body AS body
			FROM messages ". ($table ? ", $table" : "").
			(($ids || $quals) ? " WHERE ":"").
			(is_array($ids) ? 
				"$messageboard_table.messageid IN ('" . join("','", $ids). "')" : "").
			(is_array($quals) ? join(' AND ', $quals) : "").
			" ORDER BY date";
			print("<!-- $q -->");
		return mysql_fetch_all(mysql_query($q, $messageboard_db));
	}

	function format_messages($messages) {
		global $PHP_SELF;
		$out .= "<table class='messages'>";
		if(count($messages) == 0 or !is_array($messages)) {
			$out .= '<tr><td>No posts on this page</td></tr>';
		} else {
			foreach($messages as $row) {
				extract($row);
				$shaded = !$shaded;
				$out .= '<tr class="' . ($shaded ? 'shaded' : 'unshaded') . '">';
				$out .= "<td rowspan='2' valign='top' class='poster'>$poster<br />$date</td>";
				$out .= "<td class='subject'>$subject</td>";
				$out .= "</tr>";
				$out .= '<tr class="' . ($shaded ? 'shaded' : 'unshaded') . '">';
				$out .= "<td class='body'>" . wiki_render($body) . "</td>";
				$out .= '</tr>';
			}
		}
		$out .= '</table>';
		if(authorized($USERNAME, 'postcomment')) {
			$out .= /* popup_*/ hyperlink("$PHP_SELF/Comment", 'Post Comment');
		}

		return("<center>". $out . "</center>");
	}
?>
