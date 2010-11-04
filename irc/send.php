<?php

	require_once(dirname(__FILE__)."/irc.php");

	function sendtoirc($nick, $window, $input) {
		if($input[0] == '/') {
			$input = substr($input, 1);
			list($command, $remainder) = explode(' ', $input);
			$command = strtoupper($command);
			if($command == 'ME') {
				$input = ":$nick PRIVMSG $window :\1ACTION$remainder\1";
			} elseif($command == 'QUERY') {
				$parts = explode(',', $remainder);
				foreach($parts as $part) {
					add_window(strtolower($part));
				}
				return;
			} elseif($command == 'CLOSE') {
				$parts = explode(',', $remainder);
				foreach($parts as $part) {
//					del_window(strtolower($part));
				}
				return;
			} else {
				$input = ":$nick ". $input;
			}
		} else {
			$input = ":$nick PRIVMSG $window :$input";
		}
		if(!mysql_query("INSERT INTO outgoing (nickname, data) VALUES ('$nick', '$input')")) {
			print(mysql_error());
		}
		queue($window, $input);
	}

?>
