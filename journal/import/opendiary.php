<?php

	require_once(dirname(__FILE__)."/../../http.php");
	require_once(dirname(__FILE__)."/../../mysql.php");

	if(!$journal_db = mysql_connect("localhost", "ljk", "ljk")) {
		print(mysql_error());
		exit();
	}
	mysql_select_db("ljk", $journal_db);

	function collapse_times($array) {
		$output = array();
		foreach($array as $date => $day) {
			if(is_array($day)) {
				foreach($day as $time => $entry) {
					$odate = format_lj_datetime($date, $time);
					$entry['date'] = $odate;
					$output[$odate] = $entry;
				}
			}
		}
		return($output);
	}

	function get_cached($uri) {
		$uri = addslashes($uri);
		$r = mysql_fetch_all(mysql_query("SELECT * FROM temp WHERE uri = '$uri'"));

		if(is_array($r)) {
			return array_shift($r);
		}
	}

	function put_cache($entry) {
		foreach($entry as $key => $value) {
			$$key = addslashes($value);
		}
		$r = mysql_query($q = "REPLACE INTO temp (friend_uri, uri, date, subject, data, username) VALUES ('$friend_uri', '$uri', '$date', '$subject', '$data', 'rebecca');");
		if(!$r) {
			print("caching $uri: $q: " . mysql_error());
		}
	}

	function get_friend_opendiary($uri) {
		$page = get($uri);
		if(!$page) { 
			print("Couldn't get $uri\n");
			return array(); 
		}

		if(preg_match('!(entrylist.asp\?.*?)>.*Next!m', $page, $matches)) {
			$next = 'http://www.opendiary.com/'.$matches[1];
		}

		if(preg_match_all('!"(entryview\d+\.asp\?authorcode=[A-Z0-9]+\&entry=(\d+))"!', $page, $matches, PREG_PATTERN_ORDER)) {
			$entryuris = $matches[1];
			foreach($entryuris as $k => $v) {
				$entryuris[$k] = 'http://www.opendiary.com/' . $v;
			}
		} else {
			return array();
		}

		foreach($entryuris as $entryuri) {
			if(!$entry = get_cached($entryuri)) {
				$page = get($entryuri);
				if($page) {
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
//						print("Got entry $entryuri: \n");
//						print_r($entry);
//						print("\n");
						put_cache($entry);
					} else {
//						print("Couldn't get entry from $entryuri\n");
						continue;
					}
				}
			}
		}
		if($next) get_friend_opendiary($next);
	}

	if($uri) {
		get_friend_opendiary($uri);
	}
?>
