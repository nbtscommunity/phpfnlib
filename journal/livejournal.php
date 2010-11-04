<?php

	$LJ_SERVER = "www.livejournal.com";
	$LJ_PORT = 80;
	$LJ_TIMEOUT = 15;

	function insert_livejournal($subject, $data, $date) {
		global $LJ_SERVER, $LJ_PORT, $LJ_TIMEOUT;
	
		if (defined('LIVEJOURNAL_USER') and defined('LIVEJOURNAL_PASSWD')) {
			$user = urlencode(LIVEJOURNAL_USER);
			$md5passwd = urlencode(md5(LIVEJOURNAL_PASSWD));
		} else {
			// Display ERROR here: no login info
			return;
		}

		$subject = urlencode($subject);

		# Convert CRLF and CR to LF
		$data = preg_replace("/\r\n/", "\n", $data);
		$data = preg_replace("/\r/", "\n", $data);
		$data = urlencode($data);

		$time = localtime(strtotime($date), 1);
		$year = $time['tm_year']+1900;
		$month = $time['tm_mon']+1;
		$day = $time['tm_mday'];
		$hour = $time['tm_hour'];
		$minute = $time['tm_min'];
		
		$security = "public";
		//	$security = "usemask";
		//	$mask = 1;

		$mode = "postevent";
		# Line endings in the $data are LF
		$lineendings = urlencode("\n");

//		$request = "mode=$mode&user=$user&hpassword=$md5passwd&subject=$subject&event=$data&lineendings=$lineendings&year=$year&mon=$month&day=$day&hour=$hour&min=$minute&security=$security&allowmask=$mask";
		$request = "mode=$mode&user=$user&hpassword=$md5passwd&subject=$subject&event=$data&lineendings=$lineendings&year=$year&mon=$month&day=$day&hour=$hour&min=$minute&security=$security";
		$result = "";
		$fp = fsockopen($LJ_SERVER, $LJ_PORT, &$errno, &$errstr, $LJ_TIMEOUT);
		if ($fp) {
			# LiveJournal HTTP requests need CRLF
			fputs($fp,
				"POST /interface/flat HTTP/1.0\r\n".
				"Host: www.livejournal.com\r\n".
				"Content-type: application/x-www-form-urlencoded\r\n".
				"Content-length: ".strlen($request)."\r\n".
				"\r\n".$request."\r\n"
			);

			while (!feof($fp)) {
				$result .= fgets($fp, 128);
			}

			fclose($fp);
		} else {
			# ERROR: couldn't connect
		}

		preg_match("/success\n([^\n]+)/m", $result, $matches);
		if ($matches[1] != "OK") {
			preg_match("/errmsg\n([^\n]+)/m", $result, $matches);
			# ERROR: $matches[1]
		} else {
			# SUCCESS
		}
	}
	
	function get_friend_livejournal($uri) {
		$page = get($uri);
		if(!$page) { 
			return array(); 
		}

		if(preg_match_all('!"(http://www\.livejournal\.com/talkpost\.bml\?journal=.*itemid=(\d+))"!', $page, $matches, PREG_PATTERN_ORDER)) {
			$entryuris = $matches[1];
		}

		if(count($entryuris) == 0) {
			return array();
		}

		$days = array();
		foreach($entryuris as $entryuri) {
			$entryuri = str_replace("&amp;", "&", $entryuri);
			if($entry = get_cached($entryuri)) {
				list($date, $time) = split(' ', $entry['date']);
				if(!is_array($days[$date])) {
					$days[$date] = array();
				}
				$days[$date][$time] = $entry;
			} else {
				$page = get($entryuri);
				if($page) {
					if(preg_match('#<!-- body area -->(.*)<!-- /body area -->#ms', $page, $matches)) {
						$page = $matches[1];
						$linkre = '<a[^>]>(\d+)</a>';
						$tagsre = '(?:\s|<[^>]+?>)+';
						if(preg_match("!(?:said|wrote),$tagsre@$tagsre(\d+)$tagsre-$tagsre(\d+)$tagsre-$tagsre(\d+)$tagsre(\d+:\d+:\d+).*?</CENTER>.*?<ul>(.*)</ul>!ms", $page, $matches)) {
							$date = $matches[1]."-".$matches[2]."-".$matches[3];
							$time = $matches[4];
							$data = trim($matches[5]);

							if(preg_match('!^<table.*?>(.*?)</table>(.*)$!ms', $data, $matches)) {
								$data = trim($matches[2])."<table>".$matches[1]."</table>";
							}

							$data = preg_replace('!^<p>!ms', '', $data);
							if(preg_match('!^(.*?)<br />(.*)$!ms', $data,
								$matches)) {
								$data = trim($matches[2]);
								$subject = trim($matches[1]);
							}

							// Fix up relative URIs:
							//$data = preg_replace("%(href|src)=('|\")(!?[a-z]://)(.*?)(\\2)%xi", '\1=\2http://www.livejournal.com/\3\2', $data);
							
							$entry = array(
								'date' => "$date $time",
								'subject' => $subject, 'data' => $data, 
								'uri' => $entryuri,
								'friend_uri' => $uri
							);
							//print("<!-- Got entry $entryuri: \n");
							//print_r($entry);
							//print("-->\n");
							if(!is_array($days[$date])) {
								$days[$date] = array();
							}
							$days[$date][$time] = $entry;
							if(strlen(trim($data)) > 7) {
								put_cache(array($entry));
							} else {
								mail_entry("aredridel@nbtsc.org", "LiveJournal Entry Breaks Parser... more at 10.", $entryuri, "$page");
							}
						} else {
							print("Couldn't extract $entryuri\n");
							mail_entry("aredridel@nbtsc.org", "LiveJournal Entry Breaks Parser... more at 10.", $entryuri, "$page");
							continue;
						}

					} else {
						continue;
					}
				}
			}
		}
		return collapse_times($days);
	}

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

	function format_lj_datetime($date, $time) {
		return "$date $time";
		list($dow, $month, $day, $year) = split(' +', str_replace(",", ' ', $date));
		$months = array(
			"January" => 1,
			"February" => 2,
			"March" => 3,
			"April" => 4,
			"May" => 5,
			"June" => 6,
			"July" => 7,
			"August" => 8,
			"September" => 9,
			"October" => 10,
			"November" => 11,
			"December" => 12
		);

		$month = $months[$month];
		$day = (int)$day;

		list($time, $partofday) = explode(' ', trim($time));
		if($partofday == 'pm') {
			$time = explode(':', $time);
			$time[0] += 12;
			$time = $time[0] . ":" . $time[1];
		}

		return("$year-$month-$day $time:00");
	}

	function remove_tags($data, $tags = array('[a-z]+')) {
		foreach($tags as $tag) {
			$data = preg_replace("!<$tag([^>]*|[a-z]+=\"[^\"]*\"|[a-z]+='[^']*')*>|</$tag\s*>!ims", '\1', $data);
		}
		return(trim($data));
	}
?>
