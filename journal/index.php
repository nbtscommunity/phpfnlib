<?php 
	require(dirname(__FILE__)."/../versioncompat.php");

	ob_start();
	$JOURNAL_DIR = dirname(__FILE__);
	require(dirname(__FILE__)."/../ob/encoding.php");

	require_once(dirname(__FILE__)."/../html.php");

	print(doctype("XHTML/1.0"));

?>
<html>
	<head>
<?php

	/* Journal Options
	 *
	 * Options:
	 *  JOURNAL_LIMITPERFRIEND -- integer -- limit of entries to get from
	 *  	each friend.
	 *  JOURNAL_DISPLAYMODE -- ['normal' | 'oneentry'] -- if oneentry, then
	 *  	display only one, the latest, entry. 
	 *  JOURNAL_USERNAME -- username to retrieve entries for from the
	 *  	database. No default, but tries to figure out as best it can.
	 *  JOURNAL_USECSS -- string or NULL -- if true, is a filename of
	 *  	a CSS file to use to control display, relative to $SCRIPT_NAME
	 *	JOURNAL_TITLE -- string or NULL -- if true, is a title to display in
	 *		the HTML title of the page
	 *	JOURNAL_SIDEBARS -- integer -- number of sidebars to show.
	 *		0 disables.
	 */

	if(preg_match('!~(.*?)/!', $REQUEST_URI, $matches)) {
		define("JOURNAL_USERNAME", $matches[1]);
	} else {
		define("JOURNAL_USERNAME", "username");
	}

	define('USERNAME', JOURNAL_USERNAME);

	if(!defined('JOURNAL_TITLE')) {
		if($PATH_INFO == '/Current/Friends') {
			print('<title>' . ($title = ucwords(JOURNAL_USERNAME) . "'s Friends") ."</title>\n");
		} else {
			print('<title>' . ($title = ucwords(JOURNAL_USERNAME) . "'s Journal"). "</title>\n");
		}
	} else {
		print('<title>'. JOURNAL_TITLE.'</title>');
		$title = JOURNAL_TITLE;
	}

	if(!defined("JOUNRAL_LIMITPERFRIEND")) {
		define("JOURNAL_LIMITPERFRIEND", "4");
	}
	
	if(!defined("JOUNRAL_SIDEBARS")) {
		define("JOURNAL_SIDEBARS", 2);
	}
	
	if(!defined("JOURNAL_DISPLAYMODE")) {
		define("JOURNAL_DISPLAYMODE", 'normal');
	}

	if(!defined("JOURNAL_USECSS")) {
		if(file_exists($css_filename = dirname($SCRIPT_FILENAME)."/journal.css")) {
			define("JOURNAL_USECSS", $css_filename);
			print("<link rel='StyleSheet' type='text/css' href='". dirname($SCRIPT_NAME). "/journal.css' />\n");
		} elseif(file_exists($css_filename = dirname($SCRIPT_FILENAME)."/style/journal.css")) {
			define("JOURNAL_USECSS", $css_filename);
			print("<link rel='StyleSheet' type='text/css' href='".dirname($SCRIPT_NAME)."/style/journal.css' />");
		} else {
			define("JOURNAL_USECSS", FALSE);
		}
	}
	
	require_once(dirname(__FILE__)."/row_to_html.php");
//	require_once(dirname(__FILE__)."/db.php");
	require_once(dirname(__FILE__)."/../login/login.php");
	require_once(dirname(__FILE__)."/../http.php");
	
	if(!$journal_db = mysql_connect("localhost", "ljk", "ljk")) {
		print(mysql_error());
		exit();
	}
	mysql_select_db("ljk", $journal_db);

	$messageboard_db =& $journal_db;

	if($QUERY_STRING) { $QUERY_STRING =	'?' . $QUERY_STRING; }
	$SCRIPT_URI = preg_replace('!'.preg_quote($PATH_INFO).'$!', 
		'', $PHP_SELF);
	
	if($action == 'login' and $REQUEST_METHOD == 'POST') {
		if(is_logged_in()) {
			http_302($SCRIPT_URI);
		} else {
			print(p("Invalid username or password.  Please try again."));
		}
	}

	unset($mode);
	require_once(dirname(__FILE__)."/paths.php");
	
	if(!$mode) {
		if($REQUEST_METHOD == 'GET') {
			$mode = 'read';
		} else {
			$mode = 'post';
		}
	}

?>
	</head>
	<body>
		<div class='header'>
<?php
	
	print('<h1>'. $title.'</h1>');
	print("</div>");
	
	print("<div class='pagecontent' id='$mode'>");
	include(dirname(__FILE__)."/$mode.php");
	print("</div>");
	
	include(dirname(__FILE__)."/toolbar.php");
	if($HTTP_COOKIE_VARS['debug'] == 'true') {
		var_dump($LOGIN_USERNAME);
		print('login username():'.login_get_username()."<br />");
		var_dump($HTTP_SESSION_VARS);
		var_dump($PHPSESSID);
	}
?>
	</body>
</html>
