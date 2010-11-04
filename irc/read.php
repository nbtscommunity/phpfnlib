<?php
	require(dirname(__FILE__)."/scrolling.js");
	require_once(dirname(__FILE__)."/irc.php");

	function fetch_queue($NICKNAME, $window, $seen = FALSE, $limit = 200) {
		$r = mysql_fetch_all(mysql_query("SELECT * FROM queue WHERE nickname = '$NICKNAME' AND window = '$window' AND seen = ".($seen?1:0)." ORDER BY id DESC ".($limit?"LIMIT $limit":"")));
		$startid = $r[count($r) - 1]['id'];
		$endid = $r[0]['id'];
		if(!$seen) {
			mysql_query("UPDATE queue SET seen = 1 WHERE nickname = '$NICKNAME' AND window = '$window' AND id >= '$startid' AND id <= '$endid'");
		}
		krsort($r);
		return $r;
	}

	//KLUDGE:
//	print("<meta http-equiv='refresh' content='7; URL=".purlencode($PHP_SELF)."' />");

	print("\n");
	flush();

	function out($s) {
		print($s."\n");
		flush();
		//print(str_repeat(" ", 1024 - strlen($s)));
	}

	$r = fetch_queue($NICKNAME, $window, TRUE);

	foreach($r as $row) {
		out(format_message($row));
		if($row['type'] == 'quit') {
			print("Connection closed: " . $row['data']);
			exit();
		}
	}	

	while(!$quit) {
		$r = fetch_queue($NICKNAME, $window, FALSE, 0);
		foreach($r as $row) {
			if($row['type'] == 'quit') {
				out("Connection closed: " . $row['data']);
				$quit = TRUE;
			}
			out(format_message($row));
		}
		//$quit = TRUE;
		usleep(1000000);
	}

	print("<br /><a href='".purlencode($PHP_SELF)."'>Refresh</a>\n");

	function format_message($row) {
		$msg = irc_split_message($row['data']);
		extract($msg);
		switch($command) {
			case 'PRIVMSG':
				if($message[0] == "\1") {
					$o = "* " .format_nick($origin)
					. " " . str_replace("\1ACTION", '', $message);
				} else {
					$o = "&lt;".format_nick($origin)."&gt; $message";
				}
				break;
			case 'NICK': 
				$o = format_nick($origin) . " is now known as
				" . format_nick($message);
				break;
			case 'JOIN':
				$o = format_nick($origin) . " has joined $dest";
				break;
			case 'QUIT':
				$o = format_nick($origin) . " has quit ($message)";
				break;
			default:
				$o = $row['data'];
		}
		return format_timestamp($row['timestamp']) ." $o<br />";
	}

	function format_timestamp($stamp) {
		list($junk, $hour, $minute, $second) = sscanf($stamp, '%8d%2d%2d%2d');
		return sprintf('[%02s:%02s:%02s]', $hour, $minute, $second);
	}
?>
