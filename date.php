<?php

	require_once(dirname(__FILE__)."/timezone/timezone.php");

	if(!defined('DATE_DATEFORMAT')) {
		define('DATE_DATEFORMAT', 'Y/m/d');
	}

	if(!defined('DATE_TIMEFORMAT')) {
		define('DATE_TIMEFORMAT', 'H:i T');
	}

	function format_time($time, $timezone = '') {
		return timezone_date($timezone, DATE_TIMEFORMAT, $time);
		
		//global $timeformat;
		//if(!$timeformat) {
		//	$timeformat = '%h:%m';
		//}
		//$time = mktime($time);
		//return(date($time, $timeformat));
		return(preg_replace("!(\d+:\d+):\d+!", '\1', $time));
	}

	function format_date($date, $timezone = '') {
		return timezone_date($timezone, DATE_DATEFORMAT, $date);
	}

	function date_rfc2822($d) {
		return date('r', $d);
	}

	function rfc2822_2_ts($d) {
		$months = array(
			01 => 'Jan', 'Feb', 'Mar', 'Apr', 'May',
			'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');
		$months =  array_flip($months);
		if(preg_match('/(Mon|Tue|Wed|Thu|Fri|Sat|Sun), '.
			'([0-9]{,2}) '.
			'(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec) '.
			'([0-9]{4}) '.
			'([0-9]{2}):([0-9]{2}):([0-9]{2}) '.
			'([-+]?)[0-9]{2})([0-9]{2})', $d, $m)) {
			list($all, $dow, $day, $month, $year, 
				$hour, $minute, $second, $zone_s, $zone_h, $zone_m) = $m;
			if($zone_s != '-') {
				$zone_h = 0 - $zone_h;
				$zone_m = 0 - $zone_m;
			}
			$ts = gmmktime($hour - $zone_h, $minute - $zone_m, $second, 
				$months[$month], $day, $year);
		} else {
			return 0;
		}
	}

	function ts_2_iso8601($ts) {
		return gmdate("Y-m-d\TH:i:s\Z", $ts);
	}

	function iso8601_2_ts($d) {
		if(preg_match('/'.
			'([0-9]{4})'.
			'(?:-([0-9]{2})'.
			'(?:-([0-9]{2})'.
			'(?:T([0-9]{2}):([0-9]{2})(?:([0-9]{2}))?'.
			'(?:([+-])([0-9]{2}):([0-9]{2})|Z)?)?)?)?', $d, $m)) {
			list($all, $year, $month, $day,
				$hour, $minute, $second, $zone_s, $zone_h, $zone_m) = $m;
			if($zone_s != '-') {
				$zone_h = 0 - $zone_h;
				$zone_m = 0 - $zone_m;
			}
			$ts = gmmktime($hour - $zone_h, $minute - $zone_m, $second, 
				$months[$month], $day, $year);
		} else {
			return 0;
		}
	}

?>
