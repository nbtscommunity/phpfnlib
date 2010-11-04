<?php

	if(!defined("ZONEINFO_PATH")) {
		define("ZONEINFO_PATH", "/usr/share/zoneinfo");
	}

	function timezone_time($zone, $date = 0) {
		$tz = getenv("TZ");
		putenv("TZ=$zone");
		if($date == 0) {
			$ret = localtime(time(), TRUE);
		} else {
			$ret =  localtime($date, TRUE);
		}
		putenv("TZ=$tz");
		return $ret;
	}

	function timezone_date($zone, $format, $stamp = 0) {
		$tz = getenv("TZ");
		putenv("TZ=$zone");
		if($stamp == 0) {
			$ret = date($format, time());
		} else {
			$ret = date($format, $stamp);
		}
		putenv("TZ=$tz");
		return $ret;
	}
	
	function timezone_mktime($zone, $hours, $minutes, $seconds, 
		$months = 0, $days = 0, $years = 0) {
		$tz = getenv("TZ");
		putenv("TZ=$zone");
		$ret = mktime($hours, $minutes, $seconds, $months, $days, $years);
		putenv("TZ=$tz");
		return $ret;
	}

	function timezone_guess() {
		global $REMOTE_ADDR;
		if(preg_match('/\d+\.\d+\.\d+\.\d+/', $REMOTE_ADDR)) {
			$REMOTE_HOST = gethostbyaddr($REMOTE_ADDR);
		} else {
			$REMOTE_HOST = $REMOTE_ADDR;
		}

		$parts = explode('.', $REMOTE_HOST);
		$tld = array_pop($parts);
		$sld = array_pop($parts);

		$tld = strtolower(strrchr($REMOTE_HOST, '.'));
		if($tld == 'com' or $tld == 'net' or $tld == 'org') {
			return 'America/';
		} else {
			switch($tld) {
				case 'au': return 'Australia/';
				case 'us': 
					switch($sld) {
						case 'nm':
						case 'wy':
						case 'co': return 'America/Denver';
						case 'az': return 'America/Phoenix';
						case 'me':
						case 'nh':
						case 'nj':
						case 'ga':
						case 'ky':
						case 'ny': return 'America/New_York';
						case 'tx': return 'America/Dallas';
						case 'ca':
						case 'or':
						case 'wa': return 'America/Los_Angeles';
						default: return 'America/';
					}
				case 'pl': return 'Poland/Warsaw';
				case 'uk': return 'Europe/London';
				case 'ie': return 'Europe/Dublin';
				case 'fr': return 'Europe/Paris';
				default: return FALSE;
			}
		}
		return FALSE;
	}

	function timezones_list($scheme = 'posix') {
		return _timezones_list(ZONEINFO_PATH."/$scheme");
	}
	
	function _timezones_list($path = ZONEINFO_PATH, $prefix = '') {
		$zones = array();
		$d = opendir($path);
		while($dent = readdir($d)) {
			if($dent{0} == '.') continue;
			if(is_dir($path."/$dent")) {
				$temp = _timezones_list($path."/$dent", "$prefix$dent/");
				array_splice($zones, count($zones), 0, $temp);
			} else {
				$zones[] = $prefix.$dent;
			}
		}
		asort($zones);
		return $zones;
	}

?>
