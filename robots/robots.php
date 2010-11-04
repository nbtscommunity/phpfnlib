<?php
	require_once(dirname(__FILE__)."/../mysql.php");
	require_once(dirname(__FILE__)."/../errors.php");

	if(!defined("ROBOTS_SOURCE_TYPE"))
		define("ROBOTS_SOURCE_TYPE", "sql");

	if(!defined("ROBOTS_SOURCE_FILE"))
		define("ROBOTS_SOURCE_FILE", "/home/sites/community.nbtsc.org/robots.txt");

	if(!defined("ROBOTS_DATABASE")) {
		define("ROBOTS_DATABASE", "nbtsc");
	}

	function robots_exclude($useragent, $path) {
		if(ROBOTS_SOURCE_TYPE == 'file') {
			if($f = fopen(ROBOTS_SOURCE_FILE, "a")) {
				fputs("\nDisallow: $path\n");
				fclose($f);
				return E_SUCCESS;
			} else {
				return error("File open failed");
			}
		} else {
			if(!$db = mysql_connect('localhost', 'nobody', '')) {
				return error(mysql_error(), E_SERVFAIL);
			}
			mysql_select_db(ROBOTS_DATABASE, $db);
			$useragent = addslashes($useragent);
			$path = addslashes($path);
			if(mysql_query("INSERT INTO robots (useragent, path, sense)
				VALUES('$useragent', '$path', 'disallow');")) {
				return E_SUCCESS;
			} else {
				return error("Couldn't insert into robots table", E_SERVFAIL);
			}
		}
	}
?>
