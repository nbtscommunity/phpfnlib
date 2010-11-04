<?php
	require_once(dirname(__FILE__)."/../http.php");

	function get_friend_diaryland($uri) {
		if($entry = get_cached($uri)) {
			return array($entry['date'] => $entry);
		} else {
			$page = get($uri);
			if(strlen($page) == 0) {
				print("No data at $uri\n");
				return array(); 
			} else {
				if(!preg_match('!(\d+-\d+-\d+)\s*-\s*(\d+:\d+)\s*(p\.m\.|a\.m\.).*?(<P>.*?)<P><A[^>]*>previous!ms', $page, $matches)) {
					print("Couldn't extract entry from $uri\n");
					mail_entry("aredridel@nbtsc.org", "DiaryLand Entry Breaks Parser... more at 10.", $entryuri, "$page");
					return array();
				} else {
					$time = explode(':', $matches[2]);
					if($matches[3] == 'p.m.') {
						$time[0] += 12;
					}
					$time = join(":", $time);
					$date = $matches[1] . " " . $time;
					
					$data = $matches[4];

					$friend_uri = $uri;
					$uri = $uri."/$date";

					$entry = compact('date', 'data', 'uri', 'friend_uri');
					put_cache(array($entry));
					return array($date => $entry);
				}
			}
		}
	}
?>
