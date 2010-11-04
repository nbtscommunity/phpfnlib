<?php

	$windows = array();

	function queue($window, $data, $type = 'message') {
		global $NICKNAME, $windows;
		if(!in_array(strtolower($window), $windows)) {
			add_window(strtolower($window));
		}
		return mysql_query("INSERT INTO queue (nickname, window, data, type) VALUES ('$NICKNAME', '$window', '$data', '$type')");
	}

	function get_outgoing() {
		global $NICKNAME;
		$r = mysql_fetch_all(mysql_query("SELECT * FROM outgoing WHERE nickname = '$NICKNAME' ORDER BY id"));
		$firstid=$r[0]['id'];
		$lastid=$r[count($r) - 1]['id'];
		mysql_query("DELETE FROM outgoing WHERE nickname = '$NICKNAME' AND id >= '$firstid' AND id <= '$lastid'");
		return $r;
	}

	function irc_split_message($message) {
		if($message[0] ==':') {
			list($origin, $message) = explode(' ', $message, 2);
			$origin = substr($origin, 1);
		}
		list($command, $message) = explode(' ', $message, 2);
		$command = strtoupper($command);

		if($command == 'MODE' or $command == 'PRIVMSG' or $command = 'PART') {
			list($dest,$message) = explode(' ', $message, 2);
		} elseif($command == 'JOIN') {
			$dest = substr($message, 1);
			$message = '';
		} elseif($command == 'QUIT') {
			unset($dest);
		}

		if($message[0] == ':') {
			$message = substr($message, 1);
		}
		if($dest[0] == ':') {
			$dest = substr($message, 1);
		}

		return compact('origin','command','dest', 'message');
	}
	
	function format_nick($nick) {
		list($nick, $userhost) = explode('!', $nick, 2);
		return $nick;
	}


	function add_window($window) {
		global $NICKNAME;
		mysql_query("REPLACE INTO windows (nickname, title) VALUES ('$NICKNAME', '$window');");
	}

	function get_windows() {
		global $NICKNAME;
		$o = array();
		$r = mysql_fetch_all(mysql_query("SELECT * FROM windows WHERE
		nickname ='$NICKNAME'"));
		foreach($r as $row) {
			$o[] = $row['title'];
		}
		return $o;
	}

?>
