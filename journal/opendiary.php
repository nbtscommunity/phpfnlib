<?php

	function get_friend_opendiary($uri) {
		$page = get($uri);
		if(!$page) { 
			debug("Couldn't get $uri\n");
			return array(); 
		}

		if(preg_match_all('!"(entryview\d+\.asp\?authorcode=[A-Z0-9]+\&entry=(\d+))"!', $page, $matches, PREG_PATTERN_ORDER)) {
			$entryuris = $matches[1];
			foreach($entryuris as $k => $v) {
				$entryuris[$k] = 'http://www.opendiary.com/' . $v;
			}
		} else {
			debug("None found\n");
			return array();
		}

		$days = array();
		foreach($entryuris as $entryuri) {
			if($entry = get_cached($entryuri)) {
				print("got $entryuri from cache\n");
				list($date, $time) = split(' ', $entry['date']);
				if(!is_array($days[$date])) {
					$days[$date] = array();
				}
				$days[$date][$time] = $entry;
			} else {
				$page = get($entryuri);
				if(!$page) {
					print("Couldn't get journal entry $entryuri\n");
				} else {
					$tagsre = '(?:\s|<[^>]+?>)+';
					if(preg_match("#<TABLE WIDTH=100%>+$tagsre(.*?)</TD>$tagsre(\d+/\d+/\d+)$tagsre"."(?:Time: (\d+.\d+)(am|pm))?(.*?)</TD>#s", $page, $matches)) {
						$subject = $matches[1];
						list($month, $day, $year) = explode('/', $matches[2]);
						$date = sprintf('%s-%s-%s', $year, $month, $day);
						
						$ampm = $matches[4];
						$time = explode('.', $matches[3]);
						if($ampm == 'pm') { $time[0] += 12; }
						$time = $time[0].":".$time[1];
						$data = $matches[5];

						if($time = ":") { $time = "00:00:00"; }

						// Fix up relative URIs:
						//$data = preg_replace("%(href|src)=('|\")(!?[a-z]://)(.*?)(\\2)%xi", '\1=\2http://www.livejournal.com/\3\2', $data);
							
						$entry = array(
							'date' => "$date $time",
							'subject' => $subject, 'data' => $data, 
							'uri' => $entryuri,
							'friend_uri' => $uri
						);
						print("Got entry $entryuri: \n");
						print_r($entry);
						print("\n");
						if(!is_array($days[$date])) {
							$days[$date] = array();
						}
						$days[$date][$time] = $entry;
						put_cache(array($entry));
					} else {
						print("Couldn't get entry from $entryuri\n");
						mail_entry("aredridel@nbtsc.org", "OpenDiary Entry Breaks Parser... more at 10.", $entryuri, "$page");
						continue;
					}
				}
			}
		}
		return collapse_times($days);
	}
?>
