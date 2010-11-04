<?php

	$LJ_SERVER = "www.livejournal.com"
	$LJ_PORT = 80;
	$LJ_TIMEOUT = 15;

	$user = urlencode("");
	$md5passwd = urlencode(md5(""));
	$security = "usemask";
	$mask = 1;
	
	function insert_livejournal($subject, $data, $date) {
		$subject = urlencode($subject);

		# Convert CRLF and CR to LF
		$data = preg_replace("/\r\n/", "\n", $data);
		$data = preg_replace("/\r/", "\n", $data);
		$data = urlencode($data);

		$time = localtime(strtotime($date), 1);
		$year = $time['tm_year']+1900;
		$month = $time['tm_mon'];
		$day = $time['tm_day'];
		$hour = $time['tm_hour'];
		$minute = $time['tm_min'];

		$mode = "postevent";
		# Line endings in the $data are LF
		$lineendings = urlencode("\n");

		$request = "mode=$mode&user=$user&hpassword=$md5passwd&subject=$subject&event=$data&lineendings=$lineendings&year=$year&mon=$month&day=$day&hour=$hour&min=$minute&security=$security&allowmask=$mask";
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
?>
