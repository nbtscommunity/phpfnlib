<?php

	define('DEBUG', true);

	require_once(dirname(__FILE__)."/../debug.php");

	function get_cached($uri) {
		$uri = addslashes($uri);
		$r = mysql_fetch_all(mysql_query("SELECT * FROM cache WHERE uri = '$uri'"));

		if(is_Array($r)) {
			return array_shift($r);
		}
	}

	function put_cache($array) {
		foreach($array as $entry) {
			foreach($entry as $key => $value) {
				$$key = addslashes($value);
			}
			$r = mysql_query($q = "REPLACE INTO cache (friend_uri, uri, date, subject, body) VALUES ('$friend_uri', '$uri', '$date', '$subject', '$data');");
			if(!$r) {
				print("Caching $uri:");
				print("$q: " . mysql_error()."\n");
			}
				
		}
	}

	require_once("$dir". "livejournal.php");
	require_once("$dir". "diaryland.php");
	require_once("$dir". "opendiary.php");
	require_once("$dir". "get_friend_ljk.php");
	require_once("$dir". "mysql_connect.php");
	require_once("$dir". "../mysql.php");
	require_once("$dir". "../http.php");


	if(!defined('JOURNAL_LIMITPERFRIEND')) {
		define('JOURNAL_LIMITPERFRIEND', 6);
	}

	if(!is_array($friends)) {
		if(defined('JOURNAL_USERNAME')) {
			$whereclause = "WHERE username ='" .JOURNAL_USERNAME ."'";
		}
		$q = "SELECT DISTINCT friend, uri, rand() as a FROM friends $whereclause order by a";
		$r = mysql_query($q);
		if($r and mysql_num_rows($r) > 0) {
			$r = mysql_fetch_all($r);
		}
	} else {
		$r = $friends;
	}

	function get_friend($friend, $uri) {
		$u = parse_url($uri);
		flush();
		if(ereg('livejournal.com', $u['host']) 
			or (ereg('sampledata.*livejournal', $uri))) {
			return get_friend_livejournal($uri);
		} elseif (ereg('diaryland.com', $u['host'])) {
			return get_friend_diaryland($uri);
		} elseif (ereg('opendiary.com', $u['host'])) {
			return get_friend_opendiary($uri);
		} else {
			return get_friend_ljk($uri);
		}
	}

	if($debugfriend) {
		print("Getting journal...\n");
		$r =get_friend("debug", $debugfriend);
		print_r($r);
		exit(0);
	} 

	if(count($r) > 0 and is_array($r)) {
		$friends = array();
		foreach($r as $friendrow) {
			extract($friendrow);
			flush();
			$r = get_friend($friend, $uri);
//			if(!is_array($r)) {
//				print("Getting $friend's journal from $uri...");
//				print(count($r) . " found\n");
//				flush();
//			}
		}
	/*} else {
		print("No friend entries!");*/
	}

	function mail_entry($to, $subject, $uri, $page) {
		$r = mysql_query("select * from debug where uri = '$uri'");
		$r = mysql_fetch_all($r);

		if(count($r) == 0) {
			mail($to, $subject, $uri."\n".$page);
			print("Mailing $to about it...\n");
			mysql_query("insert into debug (uri) values('$uri')");
		/*} else {
			print("Already mailed $to\n");*/
		}
	}
?>
