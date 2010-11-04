<?php
	require_once("$JOURNAL_DIR/../wiki/render.php");
	require_once("$JOURNAL_DIR/../messages/message.php");
	require_once("$JOURNAL_DIR/../mime/mimetype.php");
	require_once("$JOURNAL_DIR/../login/login.php");
	require_once("$JOURNAL_DIR/../date.php");

	if(!defined('WIKI_WIKIWORDS')) {
		define('WIKI_WIKIWORDS', 'http://www.nbtsc.org/wiki/%s');
	}
	
	if(!defined('JOURNAL_ANONYMOUSPOSTS')) {
		define('JOURNAL_ANONYMOUSPOSTS', FALSE);
	}

	function handle_content($data, $contenttype) {
		if(!$contenttype) $contenttype = 'text/html';
		$contenttype = parse_content_type($contenttype);
		if($contenttype['type'] == 'text') {
			if($contenttype['subtype'] == "wiki") {
				$data = wiki_render($data, $contenttype['params']);
			} elseif($contenttype == 'text/plain') {
				$data = '<pre>\n'. $data . '</pre>';
			}
		} else {
			$data = 'Unable to process Content-type';
			print_r($contenttype);
		}
		return $data;
	}

	function row_to_td($row, $friend = NULL) {
		global $PHP_SELF;
		global $SCRIPT_URI;
		extract($row);
		$data = handle_content($data, $contenttype);
		if(!$time) { list($date, $time) = explode(' ', $date); }
		list($hour, $minute, $second) = explode(':', $time);
		list($year, $month, $day) = explode('-', $date);
		
		$timestamp = timezone_mktime($timezone, $hour, $minute, $second, 
			$month, $day, $year);
		$time_f = format_time($timestamp, $timezone);
		$date_f = format_date($timestamp, $timezone);
		if($date and $time) {
			$n = get_message_sum("$date $time");
			if($n > 0) {
				$messages = "<br /><a href='$SCRIPT_URI/$date/$time' id='postcomment'>" . ($n == 1 ? "1 Comment" : "$n Comments") . '</a>';
			}
			if((authorized($USERNAME, 'postcomment') and !$friend) or
				JOURNAL_ANONYMOUSPOSTS) {
				$messages .= "<br /><a href='$SCRIPT_URI/$date/$time/Comment' id='postcomment'>Post Comment</a>";
			}
		}
		if($friend) {
			$friendl = "<a class='friend' href='$friend_uri'>$friend</a>";
		}
		if(JOURNAL_USECSS) {
			return ("<div class='journalentry'".($friend ? " id='$friend'" : "").">\n".
				"\t<div class='metadata'><div class='time'>$time_f</div>$friendl$messages</div>\n".
				"\t<div class='content'>\n".
				(trim($subject) ? "\t\t<div class='subject'>$subject</div>\n" : '').
				"\t\t<div class='body'>$data</div>\n".
				"\t</div>\n".
				"</div>\n");
		} else {
			return ("<tr class='journalentry'>\n".
				"\t<td valign='top' rowspan='2' align='right' width='10%'><h2>$time_f</h2>$friend$messages</td>\n".
				"\t<td align='center' valign='top'><b>$subject</b></td>\n".
				"</tr>\n".
				"<tr><td valign='top'>$data</td></tr>\n");
		}

	}


	function get_message_sum($datetime) {
		$q = "SELECT count(*) AS count FROM entrymessages " . 
			"WHERE entrydate = '$datetime' AND entryuser = '".JOURNAL_USERNAME."'";
		print("<!-- $q -->");
		$r = mysql_query($q);
		$r = mysql_fetch_assoc($r);


		print("<!-- $q ... returns count: ". $r['count'] . " -->\n");

		return($r['count']);
	}


	function format_entry($r) {
		if(!is_array($r)) return;
		extract($r);
		if(!$time) { list($date, $time) = explode(' ',$date); }
		list($hour, $minute, $second) = explode(':', $time);
		list($year, $month, $day) = explode('-', $date);
		
		$timestamp = timezone_mktime($timezone, $hour, $minute, $second, $month, $day, $year);
		$data = handle_content($data, $contenttype);
		$date_f = format_date($timestamp, $timezone);
		$time_f = format_time($timestamp, $timezone);
		if($date and $time) {
			$n = get_message_sum("$date $time");
			$messages = get_messages(
				array(
					"entrydate = '$date $time'", 
					"entryuser = '" . JOURNAL_USERNAME ."'",
					'messages.messageid = entrymessages.messageid'), 
				'entrymessages');
		}
		if(defined("JOURNAL_USECSS")) {
			return("<div class='journal'". ($friend?"":" id='" . JOURNAL_USERNAME."'")."><div class='date'>$date_f</div>".
			row_to_td($r).
			"</div><div class='messages'>".
				(is_array($messages)?format_messages($messages) : "")
				) . 
			"</div>";
		} else {
			return("<table><tr><td><h1>$date_f</h1></td>".
				"<th>$subject</th>".
				"<td align='right'><h1>$time_f</h1></td></tr>".
				"<tr><td colspan='3'>$data</td></tr></table>".
				(is_array($messages)?format_messages($messages) : "")
				);
		}

	}

	function format_rows($rows, $limit = 0) {
		global $prevdate;
		$o = array();
		foreach($rows as $row) {
			$k =	$row['date'];
			list($row['date'], $row['time']) = explode(' ', $row['date']);
			$o[$k.++$seq] = $row;
			if($row['friend']) { $friendflag = TRUE; }
		}
		krsort($o);

		if(defined("JOURNAL_USECSS")) {
			$output .= ("<div class='journal' ".($friendflag?"" : "id='"
			. JOURNAL_USERNAME ."'").">");
			foreach($o as $row) {
				extract($row);
				if(!$time) { list($date, $time) = explode(' ', $date); }
				list($hour, $minute, $second) = explode(':', $time);
				list($year, $month, $day) = explode('-', $date);
				
				$timestamp = timezone_mktime($timezone, $hour, $minute, $second, $month, $day, $year);
				$date_f = format_date($timestamp, $timezone);
				if($limit and ++$i > $limit) {
					$prevdate = $date;
				} else {
					if($date_f != $lastdate) {
						$output .= ("<div class='date'>$date_f</div>\n");
						$lastdate = $date_f;
					}
					$output .= (row_to_td($row));
				}
			}
			$output .= ("</div>");
		} else {
			$output .= ("<table cellspacing='0'>");
			foreach($o as $row) {
				extract($row);
				if(!$time) { list($date, $time) = explode(' ', $date); }
				list($hour, $minute, $second) = explode(':', $time);
				list($year, $month, $day) = explode('-', $date);
				
				$timestamp = timezone_mktime($timezone, $hour, $minute, $second, $month, $day, $year);
				$date_f = format_date($timestamp, $timezone);
				if($limit and ++$i > $limit) { break; }
				if($date_f != $lastdate) {
					$output .= ("<tr class='journaldate'><td colspan='2' valign='top'><h1 class='journaldate' align='left'>$date_f</h1></td></tr>\n");
					$lastdate = $date_f;
				}
				$output .= (row_to_td($row));
			}
			$output .= ("</table>");
		}
		return($output);
	}

?>
