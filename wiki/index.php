<?php 

	if(!defined("WIKI_UTF8")) {
		define("WIKI_UTF8", FALSE);
	}

	$form = false;

	srand(time());

	# What is this talk of 'release'? Klingons do not make software
	# 'releases'.	Our software 'escapes' leaving a bloody trail of
	# designers and quality assurance people in its wake.
	
	if(!defined("WIKI_HEADINGLEVEL")) {
		define("WIKI_HEADINGLEVEL", 2);
	}
	
	if(!defined("WIKI_AUTOUPDATE")) {
		define("WIKI_AUTOUPDATE", false);
	}
	
	if(!defined("WIKI_BLACKLIST")) {
		define("WIKI_BLACKLIST", '/tmp/wiki.blacklist');
	}

	if(!defined("WIKI_RECENTCHANGES_EXTERNAL")) {
		define("WIKI_RECENTCHANGES_EXTERNAL", false);
	}
	if(!defined("WIKI_ANONYMOUS_EDIT")) define("WIKI_ANONYMOUS_EDIT", TRUE);
	if(!defined("WIKI_ANONYMOUS_CONTROLS")) 
		define("WIKI_ANONYMOUS_CONTROLS", TRUE);
			
	Header('Content-type: '.
		user_agent_preferred_type('application/xhtml+xml', 'text/html').
		'; charset=utf-8');

	if(isset($_SERVER['HTTP_REFERER']) and preg_match('/google.*edit/', $_SERVER['HTTP_REFERER'])) {
		wiki_blacklist($_SERVER['REMOTE_ADDR'], 'searching google for edit pages');
	}

	if(isset($_REQUEST['donotenter']) and trim($_REQUEST['donotenter'])) {
		wiki_blacklist($_SERVER['REMOTE_ADDR'], 'entered text in the do not enter box');
	}



	function user_agent_preferred_type() {
		global $_SERVER;
		$args = func_get_args();
		$preferred_mime = user_agent_parse_accept();
		$this_preferred = array();
		unset($preferred_mime['application/*']);
		if($preferred_mime['*/*']) { // Sorry, IE
			 return array_pop($args); 
		}
		foreach($args as $arg) {
			if($preferred_mime[$arg]) {
				$this_preferred[$arg] = $preferred_mime[$arg];
			} elseif($preferred_mime[preg_replace('#/.*#', '/*', $arg)]) {
				$this_preferred[$arg] = $preferred_mime[preg_replace('#/.*#', '/*', $arg)];
			} else {
				$this_preferred[$arg] = 0;
			}
		}
		arsort($this_preferred);
		$t = array_flip($this_preferred);
		return array_shift($t);
	}

	function user_agent_parse_accept() {
		global $_SERVER;
		$accepts = explode(',', $_SERVER['HTTP_ACCEPT']);
		$q = 1000;
		$oaccepts = array();
		foreach($accepts as $accept) {
			$aq = '';
			if(strstr($accept, ';'))
				list($accept, $aq) = explode(';', $accept);
			if($aq) {
				list(,$aq) = explode('=', $aq);
				$q = (int)((float)$aq * 1000);
			}
			$oaccepts[trim($accept)] = $q;
		}
		asort($oaccepts);
		return $oaccepts;
	}
	
	require(dirname(__FILE__)."/../versioncompat.php");
	require_once(dirname(__FILE__)."/../date.php");

	require_once(dirname(__FILE__)."/../ip/netblock.php");

	if(realpath(__FILE__) == realpath($SCRIPT_FILENAME)) {
		require_once(dirname(__FILE__)."/../ob/logo.php");
		require_once(dirname(__FILE__)."/../ob/html.php");
	}
		
	require_once(dirname(__FILE__)."/../ob/userpref.php");
	require_once(dirname(__FILE__)."/../ob/exit.php");

	require_once(dirname(__FILE__)."/../http.php");
	require_once(dirname(__FILE__)."/../html.php");
	require_once(dirname(__FILE__)."/../mime/mimefile.php");
	require_once(dirname(__FILE__)."/../mime/format.php");
	require_once(dirname(__FILE__)."/../mime/header.php");
	require_once(dirname(__FILE__)."/../rcs/rcs.php");
	require_once(dirname(__FILE__)."/../diff/xmldiff.php");
	require_once(dirname(__FILE__)."/../edit/edit.php");
	require_once(dirname(__FILE__)."/../array.php");
	require_once(dirname(__FILE__)."/../ob/start_first.php");
	require_once(dirname(__FILE__)."/../robots/robots.php");

	if(!defined("WIKI_DEFAULTPAGENAME")) {
		define("WIKI_DEFAULTPAGENAME", "NBTSWikiWiki");
	}

	if(!defined("WIKI_DEFAULTSUBPAGENAME")) {
		define("WIKI_DEFAULTSUBPAGENAME", WIKI_DEFAULTPAGENAME);
	}

	if(!defined("WIKI_VERIFYWIKIWORDS")) {
		define('WIKI_VERIFYWIKIWORDS', dirname($SCRIPT_FILENAME));
	}

	if(!defined("WIKI_PAGEDIR")) {
		define('WIKI_PAGEDIR', dirname($SCRIPT_FILENAME).'/');
	}
		
	$WIKI_PAGEDIR = WIKI_PAGEDIR;
	if(isset($t) and $t{count($t) - 1} != '/') {
		$WIKI_PAGEDIR .= '/';
	}

	if(!defined("WIKI_UNKNOWN_WIKIWORD_URL_SUFFIX")) {
		define("WIKI_UNKNOWN_WIKIWORD_URL_SUFFIX", '?new');
	}

	if(!defined("WIKI_LOGINS")) {
		define("WIKI_LOGINS", true);
	}

	if(!defined("WIKI_TRACK_VERSIONS")) define("WIKI_TRACK_VERSIONS", true);
	
	function ob_wiki_add_header($s) {
		return preg_replace('!</head>!', 
			"<script type='text/javascript' src='".$_SERVER['SCRIPT_NAME']."?script'></script>
			<link rel='StyleSheet' href='".$_SERVER['SCRIPT_NAME']."?style' />
			</head>", 
			$s);
		
	}

	function ob_wiki_add_title($s) {
		global $TITLE, $TITLE2;
		$title = $TITLE;
		if($TITLE2) $title .= ' ('.$TITLE2.')';
		if(preg_match('/<title>/', $s)) {
			return preg_replace('!<title>.*</title>!ms', '<title>'.$title.'</title>', $s);
		} else {
			return preg_replace('!</head>!ms', '<title>'.$title.'</title></head>', $s);
		}
	}

	ob_start_first('ob_wiki_add_header');
	ob_start_first('ob_wiki_add_title');

	if($_SERVER['REQUEST_METHOD'] == 'POST') {
		while(ob_get_level()) ob_end_clean();
	}

	$wikidoctype = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';

	$OB_LOGO = '';

	define("WIKI_WIKIWORDS", TRUE);
	require_once(dirname(__FILE__)."/render.php");
	require_once(dirname(__FILE__)."/functions.php");
	require_once(dirname(__FILE__)."/../motd.php");

	define("LOGIN_STYLE", 'http');
	require_once(dirname(__FILE__)."/../login/login.php");
	


	if(WIKI_TRACK_VERSIONS and isset($_COOKIE['versions'])) {
		$seen_versions = unserialize(stripslashes($_COOKIE['versions']));
	}

	if($REQUEST_METHOD == 'POST') {
		if(isset($_POST['tag'])) {
			$action = 'tag';
		} elseif(isset($_POST['reply_to'])) {
			$action = 'save_reply';
		} else {
			$action = 'save';
		}
	} elseif($QUERY_STRING == 'login') {
		setcookie('login', 'true', time() + (7*24*60*60), '/');
		http_302($_SERVER['SCRIPT_NAME'].$PATH_INFO);
		ob_exit();
	} elseif($QUERY_STRING == 'random') {
		require_once(dirname(__FILE__)."/../html/random.php");
		header("Location: ".html_random_link(
			join('', file('http://'.$_SERVER['SERVER_NAME'].$_SERVER['SCRIPT_NAME'].'?list'))));
		ob_exit();
	} elseif($QUERY_STRING == 'logout') {
		setcookie('login', '', time() - 84000, '/');
		logout();
		//http_302($_SERVER['SCRIPT_NAME'].$PATH_INFO);
		while(ob_get_level()) ob_end_clean();
		print(doctype('XHTML/1.0').html(head(title("Log Out")).body("You have been logged out &#x2014; sorta.  Close your browser to really log out.")));
		exit();
	} elseif(isset($_GET['setstyle'])) {
		setcookie('style', $_GET['setstyle'], time() + 84000, $_SERVER['SCRIPT_NAME']);
		http_302($_SERVER['SCRIPT_NAME'].$PATH_INFO);
		ob_exit();
	} elseif($QUERY_STRING == 'unsetstyle') {
		setcookie('style', '', time() + 84000, $_SERVER['SCRIPT_NAME']);
		http_302($_SERVER['SCRIPT_NAME'].$PATH_INFO);
		ob_exit();
	} elseif($QUERY_STRING == 'tag') {
		$action = 'tag';
	} elseif($QUERY_STRING == 'edit') {
		$action = 'edit';
	} elseif($QUERY_STRING == 'new') {
		$action = 'new';
	} elseif($QUERY_STRING == 'revisions') {
		$action = 'revisions';
	} elseif($QUERY_STRING == 'style') {
		header('Content-type: text/css');
		while(ob_get_level()) ob_end_clean();
		readfile(dirname(__FILE__).'/intrinsic.css');
		exit();
	} elseif($QUERY_STRING == 'script') {
		header('Content-type: text/javascript');
		while(ob_get_level()) ob_end_clean();
		readfile(dirname(__FILE__).'/addreplyhandler.js');
		exit();
	} elseif(isset($_GET['search'])) {
		$action = 'search';
		#		if(!substr($PATH_INFO, 1).'/' == $WIKI_REPOSITORY) { 
			#		http_302($_SERVER['SCRIPT_NAME'].'/'.substr($WIKI_REPOSITORY, 0, -1)."?search=".rawurlencode($_GET['search']));
			#	ob_exit(); 
			#} 
	} elseif($QUERY_STRING == 'recentchanges') {
		$action = 'recentchanges';
		$WIKI_REPOSITORY = get_repository($PATH_INFO);
		if(!substr($PATH_INFO, 1).'/' == $WIKI_REPOSITORY) { 
			http_302($_SERVER['SCRIPT_NAME'].'/'.substr($WIKI_REPOSITORY, 0, -1)."?recentchanges");
			ob_exit(); 
		} 
	} elseif($QUERY_STRING == 'recentchanges/long') {
		$action = 'recentchanges';
		$form = 'long';
		if(!is_dir($WIKI_PAGEDIR.$PATH_INFO)) { 
		    http_302($_SERVER['SCRIPT_NAME']."?recentchanges/long"); 
			ob_exit();
		}
	} elseif($QUERY_STRING == 'recentchanges/one') {
		$action = 'recentchanges';
		$form = 'one';
		if(!is_dir($WIKI_PAGEDIR.$PATH_INFO)) { 
		    http_302($_SERVER['SCRIPT_NAME']."?recentchanges/one"); 
			ob_exit();
		}
	} elseif($QUERY_STRING == 'recentchanges/rss') {
		$action = 'recentchanges';
		$form = 'rss';
		if(!is_dir($WIKI_PAGEDIR.$PATH_INFO)) { 
		    http_302($_SERVER['SCRIPT_NAME']."?recentchanges/rss"); 
			ob_exit();
		}
	} elseif($QUERY_STRING == 'archives') {
		$action = 'archives';
	} elseif($QUERY_STRING == 'list/text') {
		$action = 'list';
		$form = 'text';
		if(!is_dir($WIKI_PAGEDIR.$PATH_INFO)) { 
			http_302($_SERVER['SCRIPT_NAME']."?list"); 
			ob_exit();
		}
	} elseif($QUERY_STRING == 'list') {
		$action = 'list';
		if(!is_dir($WIKI_PAGEDIR.$PATH_INFO)) { 
			http_302($_SERVER['SCRIPT_NAME']."?list"); 
			ob_exit();
		}
	} elseif($QUERY_STRING == 'list/long') {
		$action = 'list';
		$form = 'long';
		if(!is_dir($WIKI_PAGEDIR.$PATH_INFO)) { 
			http_302($_SERVER['SCRIPT_NAME']."?list/long"); 
			ob_exit();
		}
	} elseif($QUERY_STRING == 'info') {
		$action = 'info';
	} elseif($QUERY_STRING == 'update-script') {
		while(ob_get_level()) ob_end_clean();
		header('Content-type: application/x-javascript');
		readfile(dirname(__FILE__).'/update.js');
		exit();
	} elseif(isset($_GET['reply'])) {
		$action = 'reply';
	} elseif(isset($_GET['as'])) {
		$action = 'view';
	} elseif(!$QUERY_STRING) {
		$action = 'view';
	} else {
		die("Invalid action.  Try again, please. <br />$QUERY_STRING");
	}

	if(WIKI_LOGINS and 
		((isset($_COOKIE['login']) and $_COOKIE['login'] == 'true') 
			or (isset($_SERVER['PHP_AUTH_USER']) and $_SERVER['PHP_AUTH_USER'] != ''))) {
		login_do_http_auth();
		if(!is_logged_in()) {
			http_401();
			while(ob_get_level()) ob_end_clean();
			print(doctype('XHTML/1.0').
				html(head(title("Log In Cancelled")).
				body("Go to the <a href='".$_SERVER['SCRIPT_NAME']."?logout'>Logout
				page</a> to not log in.")));
			exit();
		} else {
			//$s = ob_get_contents(); ob_end_clean();
			//ob_start("ob_userpref");
			//print($s);
		}
		header("Cache-control: private");
		session_cache_limiter("private_no_expire");
	} else {
		header("Cache-control: public");
		session_cache_limiter("public");
	}
						
	if(get_magic_quotes_gpc()) {
		$PATH_INFO = stripslashes($PATH_INFO);
		$_SERVER['PHP_SELF'] = stripslashes($_SERVER['PHP_SELF']);
	}

	$SELF_NOVERSION = preg_replace('/;.*$/', '', $_SERVER['PHP_SELF']);

	if($PATH_INFO == '/') $PATH_INFO = '';

	if($PATH_INFO or $action == 'recentchanges' or $action == 'list' or $action == 'search') {
		$WIKI_REPOSITORY = get_repository($PATH_INFO);
		$pagename = get_pagename($PATH_INFO);
		$WIKI_PAGEDIR = preg_replace(
			'!/+!', '/', $WIKI_PAGEDIR.$WIKI_REPOSITORY.'/');
		if(!$pagename and $action != 'recentchanges' and $action != 'list' and $action != 'search') {
			http_302($_SERVER['SCRIPT_NAME'].'/'.$WIKI_REPOSITORY.WIKI_DEFAULTSUBPAGENAME);
			ob_exit();
		} elseif($pagename and $action == 'recentchanges') {
			$temp = substr($WIKI_REPOSITORY, 0, -1);
			http_302($_SERVER['SCRIPT_NAME'].($temp?'/'.$temp:'').'?recentchanges');
			ob_exit();
		}
	} else {
		http_302($_SERVER['SCRIPT_NAME']."/".WIKI_DEFAULTPAGENAME); 
	}


	function get_pagename($PATH_INFO) {
		global $WIKI_PAGEDIR, $WIKI_REPOSITORY;
		$PATH_INFO = substr($PATH_INFO, 1);
		$parts = explode(';', $PATH_INFO);
		if($WIKI_REPOSITORY == $PATH_INFO.'/') return '';
		if(isset($parts[1])) {
			return basename($parts[0]); // strip off ;version
		} else {
			return basename($PATH_INFO);
		}
	}

	function get_repository($PATH_INFO) {
		global $WIKI_PAGEDIR;
		$PATH_INFO = substr($PATH_INFO, 1);
		$parts = explode(';', $PATH_INFO);
		if(isset($parts[1])) {
			$dn = $parts[0]; // strip off ;version
		} else {
			$dn = $PATH_INFO;
		}
		while(!is_dir($WIKI_PAGEDIR.$dn) and $dn != '.' and $dn !='') 
			$dn = dirname($dn);
		if($dn == '.' or $dn == '') {
			return '';
		} else {
			return $dn.'/';
		}
	}

	function get_version($PATH_INFO) {
		$PATH_INFO = substr($PATH_INFO, 1);
		$parts = explode(';', $PATH_INFO);
		if(isset($parts[1])) {
			return $parts[1]; // strip off page name
		} else {
			return 'Current';
		}
	}

	$version = get_version($PATH_INFO);

	if(!empty($WIKI_REPOSITORY) and $WIKI_REPOSITORY{0} == '.') {
		$WIKI_REPOSITORY_TITLE = '[Hidden] '.ucfirst(substr($WIKI_REPOSITORY, 1));
	} else {
		$WIKI_REPOSITORY_TITLE = ucfirst($WIKI_REPOSITORY);
	}
	$TITLE = ($WIKI_REPOSITORY_TITLE?substr($WIKI_REPOSITORY_TITLE, 0, -1).": ":'').
		wiki_split_wikiwords($pagename);
	if($action == 'recentchanges') $TITLE .= 'Recent Changes';
	if($action == 'search') $TITLE = 'Search for '.$_GET['search'];

	if(strstr($version, ':')) {
		list($initialversion, $version) = explode(':', $version, 2);
	}

	$Current = rcs_dereference_version($WIKI_PAGEDIR.$pagename, 'Current');
	$Last = rcs_dereference_version($WIKI_PAGEDIR.$pagename, 'Last');

	if($version == 'Current' or $version == 'Last') {
		$version = rcs_dereference_version($WIKI_PAGEDIR.$pagename, $version);
	}
	if(isset($initialversion) and ($initialversion == 'Current' or $initialversion == 'Last')) {
		$initialversion = rcs_dereference_version($WIKI_PAGEDIR.$pagename,
		$initialversion);
	}

	if(isset($initialversion)) {
		if($action == 'view') {
			if($initialversion == $version) {
				$TITLE2 = 'original version';
				unset($initialversion);
			} else {
				$TITLE2 = "diff of $initialversion and $version";
			}
		}
		if(!succeeds($initialpage = wiki_load($pagename, $initialversion))) {
			http_404();
			while(ob_get_level()) ob_end_clean();
			#include("http://community.nbtsc.org/missing/index.php");
			exit();
			//print(errmsg());
			//http_404();
			//exit();
		}

		//print(md5($initialpage['body'])."<br />".md5($page['body']));
	} else {
		if($action == 'view') $TITLE2 = "v$version";
	}
		
	$revisions = array_keys(rcs_get_revisions($WIKI_PAGEDIR."$pagename"));
	$real_version = $version;

	if($pagename == 'SearchPage') {
		http_302($_SERVER['SCRIPT_NAME'].'/'.$WIKI_REPOSITORY."?search"); 
		ob_exit(); 
	}
	if($pagename == 'RecentChanges') {
		http_302($_SERVER['SCRIPT_NAME'].'/'.$WIKI_REPOSITORY."?recentchanges"); 
		ob_exit(); 
	}
	if($pagename == 'ListPages' or $pagename == 'ThePageToEverything' or $pagename == 'PageToEverything') {
		http_302($_SERVER['SCRIPT_NAME'].'/'.$WIKI_REPOSITORY."?list/long"); 
		ob_exit();
	}

	function replyform($body, $to) {
		return wiki_render_with_notify($body, Array($to => 'replyform_handler'));
	}

	function replyform_handler($data) {
		return form($_SERVER['PHP_SELF'],
			"<small>Careful with this. It's not fully tested.</small>".br.
			hidden('reply_to', htmlspecialchars($_GET['to'], ENT_QUOTES, 'UTF-8')).textarea('reply').'<textarea name="donotenter" class="donotenter"></textarea>'.br.
			submit('Reply'));
	}
	
	function editform($body = '', $header = array(), $version = '') {
		if(!is_array($header)) {
			$header = mime_parse_header($header);
		}
		return form($_SERVER['PHP_SELF'], 
			(@$header['robots'] == "NoIndex" ?
				"<em>This page does not get indexed by search engines</em>" :
				"").br.
				"<h2>The page</h2>".
			textarea('data', $body).br.
			"<textarea name='donotenter' class='donotenter'></textarea>".
			"<h2>A note about this edit:</h2> ".textarea('logmessage', (is_logged_in()?'Posted by '.ucfirst(login_get_username()):'')).br.
			(@$header['robots'] != "NoIndex" ?
				checkbox('noindex', FALSE).
					" Don't index this page in search engines".br:
				"").
			hidden('srcversion', $version).
			submit('Save')."for copy and paste: á é í ó ú ü / Á É Í Ó Ú Ü / ç Ç ñ Ñ / ★ ♠ ♣ ♥ ♦ / ¿ ¡ / ¢ £ / © / đ Đ þ Þ ø Ø / ß å Å æ Æ œ Œ / ∞ ≠ ≈ ≢ ≡ / ␄ ␛ / ⑆ ⑇ ⑈ ⑉ / ☢ ☣ / ☥ ☪ ☮ ☯ / ♀ ♂ ☾ ☿ ♁ ♃ ♄ ♅ ♆ ♇ / ♈ ♉ ♊ ♋ ♌ ♍ ♎ ♐ ♏ ♑ ♒ ♓");
	}

	if($TITLE) {
		ob_start('wiki_add_title');
	}
	if($action == 'new') {
		if(!is_file(rcs_filename($WIKI_PAGEDIR."$pagename"))) {
			exec("rcs -i ".escapeshellarg($WIKI_PAGEDIR."$pagename")." 2>&1 < /dev/null", $output, $retval);
			if($retval != 0) {
				http_500();
				while(ob_get_level()) ob_end_clean();
				print("An error occurred creating the page: ".join('', $output));
				ob_exit();
			}
		}
		if(is_file($WIKI_PAGEDIR."$pagename")) {
			http_302($_SERVER['PHP_SELF']."?edit");
			ob_exit();
		} else {
			print(editform());
		}
	} elseif($action == 'save_reply') {
		if(get_magic_quotes_gpc()) {
			$reply = stripslashes($reply);
			$logmessage = stripslashes($logmessage);
			$reply_to = stripslashes($reply_to);
		}
		$page = wiki_load($pagename, $version);
		list($nth, $str) = explode(',', $reply_to, 2);
		$parts = preg_split('/(?<='.preg_quote($str, '/')."\n)/ms", $page['body']);
		
		# FIXME: Handle nesting. wiki_render's callback doesn't give enough
		array_splice($parts, $nth, 0, 
			array(
				preg_replace('/^/', "\n\n".
					str_repeat("> ", 
						substr_count('>', $str) + 1 
					), $reply
				)."\n"
			)
		);

		$data = join('', $parts);

		if(!trim($data)) {
			die("Something bad happened there... A bug!");
		}
		
		if(wiki_save_page($pagename, $data, $logmessage, $headers, $noindex)) {
				http_302($_SERVER['SCRIPT_NAME'].  
					preg_replace('/(;.*$|$)/', '', pqurlencode($PATH_INFO)).
					";".rcs_version_inc($Current));
				ob_exit();
//			} else {
//				print("Somebody saved the page before you.  You should
//				probably make sure you didn't cover over what they wrote.
//				($srcversion < {$revisions[2]}). Your stuff is saved,
//				though, and theirs isn't lost.");
//			}
		} else {
			while(ob_get_level()) ob_end_clean();
			die("An error occurred whilst saving the page: ".errmsg());
		}


	} elseif($action == 'save') {
		if(get_magic_quotes_gpc()) {
			$data = stripslashes($data);
			$logmessage = stripslashes($logmessage);
		}
		$badtext = @file(dirname(__FILE__)."/badtext.txt");
		if(!$_SERVER['HTTP_REFERER'] and substr_count($data, 'http:') > 40) {
			// wiki_blacklist($_SERVER['REMOTE_ADDR'], 'no referer and a lot of links');
		}
		if(substr_count($logmessage, 'http:') > 0 and !trim($data)) {
			wiki_blacklist($_SERVER['REMOTE_ADDR'], 'blanked page and spammed log');
		}
		foreach($badtext as $badline) {
			$badline = trim($badline);
			if(preg_match("#$badline#", $data)) {
				wiki_blacklist($_SERVER['REMOTE_ADDR'], "matched $badline");
			}
		}
		/*
		if(!($t = recode('UTF-7..UTF-8', recode('UTF-8..UTF-7', $data))) == $data) {
			print("Invalid data: $t</body></html>");
			exit();
		}
		*/

		if(wiki_save_page($pagename, $data, $logmessage, $headers, $noindex)) {
				http_302($_SERVER['SCRIPT_NAME'].  
					preg_replace('/(;.*$|$)/', '', pqurlencode($PATH_INFO)).
					";".rcs_version_inc($Current));
				ob_exit();
//			} else {
//				print("Somebody saved the page before you.  You should
//				probably make sure you didn't cover over what they wrote.
//				($srcversion < {$revisions[2]}). Your stuff is saved,
//				though, and theirs isn't lost.");
//			}
		} else {
			while(ob_get_level()) ob_end_clean();
			die("An error occurred whilst saving the page: ".errmsg());
		}
	} elseif($action == 'tag') {
		if($_POST['tag']) {
			if($version == $Current) {
				$revisions = array_keys(rcs_get_revisions($WIKI_PAGEDIR."$pagename"));
				$version = array_shift($revisions);
			}
			if(succeeds(rcs_apply_tag( $WIKI_PAGEDIR."$pagename",
				$version, $_POST['tag']))) {
				print("Tag added");
			} else {
				print("An error occurred processing the tag");
			}
		} else {
			print("<p>A tag is just a name for a revision of a page &#x2014;
			<code>Archive1</code> or <code>Archive53</code> are good names
			for tags.</p>");

			print("<p>You will be marking <em>$pagename v$version</em> with the tag you enter.</p>");

			$names = rcs_get_symbolic_names($WIKI_PAGEDIR."$pagename");
			if(count($names)) {
				print("<p>These tags have been used: ".join(', ',
				array_keys($names)).'</p>');
			} else {
				print("<p>There are no tags on this page yet.</p>");
			}
			
			print(form($_SERVER['PHP_SELF']."?tag", "<label for='tag'>Tag: </label>".field('tag').submit("Add Tag")));
		}
	} elseif($action == 'archives') {
		$names = rcs_get_symbolic_names($WIKI_PAGEDIR."$pagename");
		print("<dl>");
		foreach($names as $name => $version) {
			print("<dt>".hyperlink($_SERVER['PHP_SELF'].";$name",
			$name)."</dt><dd>$name: $version</dd>");
		}
		print("</dl>");
	} elseif($action == 'search') {
			passthru("ruby ".escapeshellarg(dirname(__FILE__)."/search.rb")." ".WIKI_PAGEDIR." ".$_SERVER['SCRIPT_NAME']." ".$_GET['search']);
	} elseif($action == 'recentchanges') {
		if($form == 'rss') {
			while(ob_get_level()) {
				ob_end_clean(); 
			}
			header('Content-type: text/xml');
		}
		if(!isset($recentchanges)) {
			if($form == 'rss') {
				$recentchanges = 5;
			} elseif($form == 'one') {
				$recentchanges = 1;
			} else {
				$recentchanges = 7;
			}
		}
		if($form != 'rss' and WIKI_RECENTCHANGES_EXTERNAL) {
			passthru("ruby ".escapeshellarg(dirname(__FILE__)."/recentchanges.rb")." ".WIKI_PAGEDIR.($form=='long'?" --long":""));
		} else {
			if($form != 'rss') {
				print(motd());
				print(hyperlink("$SELF_NOVERSION?random", "Random Page"));
				if($form != 'one' and WIKI_AUTOUPDATE) {
					print('<script type="text/javascript" src="'.
						$_SERVER['SCRIPT_NAME'].'?update-script'.
					'" />');
				} elseif($form == 'one') {
					while(ob_get_level() > 1) ob_end_clean();
				}
			}

			$dir = opendir($WIKI_PAGEDIR);
			while($dent = readdir($dir)) {
				if($dent{0} != '.') {
					$dirs[$dent] = @stat($d=$WIKI_PAGEDIR."$dent");
				}
			}
			arsort2D($dirs, 9);
	//			print("<pre>"); print_r($dirs); print("</pre>");
			$lastdate = 0;
			$items = array();
			$itemptrs = array();
			foreach($dirs as $entry => $stats) {
				if(substr($entry, -2) != ',v' 
					and (
						($entry{0} >= 'A' and $entry{0} <= 'Z')
						or ($entry{0} >= '0' or $entry{0} <= '9')
					)
					and $entry != 'RCS'
					and $entry != '..'
					and $entry != '.'
				) {
					$date = date('Y-m-d', $stats[9]);
					$dates = 0;
					if($date != $lastdate) {
						if($form != 'rss') {
							if(count($items)) print(ul(join('', $items)));
							$items = array();
						}
						if(++$dates > $recentchanges) break;
						if($form != 'rss') {
							print(heading(WIKI_HEADINGLEVEL, 
								date('F d, Y', $stats[9])));
						}
						
					}
					if($form == 'rss') {
						$temp = rcs_get_revisions($WIKI_PAGEDIR.$entry);
						$ver = key($temp);
						$items[] =
						"<item rdf:about='http://".$_SERVER['SERVER_NAME'].
							$_SERVER['SCRIPT_NAME']."/".purlencode($entry).($ver?";$ver":"")."'>
							<title>$entry</title>
							<link>http://".$_SERVER['SERVER_NAME'].
								$_SERVER['SCRIPT_NAME']."/".purlencode($entry).($ver?";$ver":"")."</link>
							<dc:date>".ts_2_iso8601($stats[9])."</dc:date>
							<description/>
						</item>";
						$itemptrs[] = "<rdf:li rdf:resource='http://".
							$_SERVER['SERVER_NAME'].
							$_SERVER['SCRIPT_NAME']."/".purlencode($entry).($ver?";$ver":"")."'/>";
					} else {
						if(defined('RECENTCHANGES_HACK')) {
							if($temp = 
								wiki_recentchanges_hack(date('Y-m-d', $stats[9]))) {
								$items[] = $temp;
							}
						}

						if(is_dir($WIKI_PAGEDIR.$entry)) {
							$items[] =
							(li(hyperlink(
								$_SERVER['SCRIPT_NAME'].'/'.
									$WIKI_REPOSITORY."$entry?recentchanges",
								$entry.'/').
								($form=='long'?date(' H:i:s T', $stats[9]):"")));
						} else {
							$ver = rcs_get_revisions($WIKI_PAGEDIR.$entry);
							$ver = key($ver);
							$items[] =
							li(wiki_link($_SERVER['SCRIPT_NAME'].'/'.$WIKI_REPOSITORY.$entry, $entry, false, $ver).
								($form=='long'?date(' H:i:s T', $stats[9]):""));
						}
					}
					$lastdate = $date;
				}
			}

			if($form == 'rss') {
				print(
					"<?xml version='1.0' encoding='UTF-8'?>\n".
					'<?xml-stylesheet title="CSS_formatting" type="text/css" href="/style.css" ?>'.'<?xml-stylesheet title="XSL_formatting" type="text/xsl" href="/rss_html.xsl"?>'.
	/*				"<?xml-stylesheet href='/rss_html.xsl' type='text/xsl'?>\n".
	*/
					"<rdf:RDF xmlns:rdf='http://www.w3.org/1999/02/22-rdf-syntax-ns#' xmlns='http://purl.org/rss/1.0/' xmlns:dc='http://purl.org/dc/elements/1.1/'>
						<channel rdf:about='http://community.nbtsc.org/wiki'>
							<title>NBTSWikiWiki</title>
							<link>http://community.nbtsc.org/wiki?recentchanges</link>
							<generator>Self</generator>
							<description>Not Back To School Camp Wiki</description>
							<language>en-US</language>
							<items>
								<rdf:Seq>
									".join("", $itemptrs)."
								</rdf:Seq>
							</items>
						</channel>
						".join("", $items)."
					</rdf:RDF>");
				exit();
			} else {
				if(count($items)) print(ul(join('', $items)));
			}
		}
	} elseif($action == 'list') {
		if($form == 'text') {
			header('Content-type: text/plain');
			while(ob_get_level()) ob_end_clean();
			$d = opendir($WIKI_PAGEDIR);
			while($e = readdir($d)) {
				if($e{0} == '.') continue;
				print($e ."\n");
			}
			flush();
			exit();
		}
		$TITLE .= 'List All Pages';
		$d = opendir($WIKI_PAGEDIR);
		$entries = array();
		while($e = readdir($d)) {
			if(!preg_match('/^\.|,v$/', $e) and is_file($WIKI_PAGEDIR."$e")) {
				$entries[] = $e;
			}
		}
		sort($entries);
		$out = array();
		foreach($entries as $e) {

			if($form == 'long') $stats = stat($WIKI_PAGEDIR."$e");
				
			if($lastentry{0} != $e{0}) {
				if(count($out) > 0) {
					print(ul(join('', $out)));
					$out = array();
				}
				print("<h3>".$e{0}."</h3>");
			}
			$out[] = li(hyperlink($_SERVER['SCRIPT_NAME']."/$WIKI_REPOSITORY$e",
			$e).($form=='long'?date(' Y/M/d H:i:s T', $stats[9]):""));
			$lastentry = $e;
		}
		if(count($out) > 0) {
			print(ul(join('', $out)));
		}
		unset($out);
	} elseif($action == 'revisions') {
		$revisions = rcs_get_revisions($WIKI_PAGEDIR."$pagename");
		$tags = array_flip(rcs_get_symbolic_names($WIKI_PAGEDIR."$pagename"));
		print("<dl>");
		foreach($revisions as $revision => $rinfo) {
			print("<dt>".
				hyperlink(preg_replace('/;.*$/', '', $_SERVER['PHP_SELF']).
					";$revision", $revision).
				(isset($tags[$revision])?" A.K.A.
				".hyperlink($SELF_NOVERSION.";".$tags[$revision],
					$tags[$revision]):'').
				"</dt>".
				"<dd>".date("Y/m/d H:i:s T (O)", strtotime($rinfo['date'].' GMT'))."<br />".
					wiki_render($rinfo['message']."\n")."</dd>");
		}
		print("</dl>");
	} elseif($action == 'info') {
		print("SCRIPT_NAME = ".$_SERVER['SCRIPT_NAME']);
		print("PHP_SELF = ".$_SERVER['PHP_SELF']);
		phpinfo();
	} elseif(succeeds($page = wiki_load($pagename, $version))) {
		if(isset($page['mtime']) and $action != 'edit')
			header("Last-Modified: ".gmdate('r', $page['mtime']));
		if($action == 'view') {
			if(WIKI_TRACK_VERSIONS and isset($_COOKIE['autodiff'])) {
				if($seen_versions[$pagename] and !$initialpage) {
					$initialversion = $seen_versions[$pagename];
					if(rcs_cmpver($initialversion, $real_version) == -1) {
						http_302($_SERVER['SCRIPT_NAME'].$PATH_INFO.";$initialversion:Current");
						ob_exit();
					}
				}
				$seen_versions[$pagename] = $real_version;
				setcookie('versions', serialize($seen_versions), 
					time() + 30*24*60*60, '/');
			}

			if(isset($_GET['as']) and $_GET['as'] == 'text/plain') {
				while(ob_get_level()) { ob_end_clean(); }
				header('Content-type: text/plain');
				print($page['body']);
				exit();
			}
			if(isset($initialpage)) {
				/* 
				print(xhtml_diff_highlight(wiki_render($initialpage['body']), 
					wiki_render($page['body'])));
				*/
				/*require_once(dirname(__FILE__)."/../xml/xmldiff_ext.php");
				$html = xml_apply_external_stylesheet(
							$o = xmldiff_external(
								$wikidoctype.html(wiki_html_head('').body(
									wiki_render($initialpage['body']."\n"))),
								$wikidoctype.html(wiki_html_head('').body(
									wiki_render($page['body']."\n")))),
							join('',file(dirname(__FILE__).
								"/../xml/diffmk_xhtml.xsl")));
				*/

				$fn1 = tempnam('/tmp', 'wiki');
				$f1 = fopen($fn1, 'w');
				fputs($f1, html(body(wiki_render($initialpage['body']."\n"))));
				fclose($f1);

				$fn2 = tempnam('/tmp', 'wiki');
				$f2 = fopen($fn2, 'w');
				fputs($f2, html(body(wiki_render($page['body']."\n"))));
				fclose($f2);

				$p = popen("xhtmldiff $fn1 $fn2", 'r');
				$html = '';
				while(!feof($p)) {
					$html .= fread($p,1024);
				}
				pclose($p);
				unlink($fn1);
				unlink($fn2);
				/* print("HTML: ".htmlentities($html)); */
				$html = preg_replace("#^.*<body>(.*)</body>.*$#mis", '\\1', $html);
				print($html);
			} else {
				if($page) {
					print(wiki_render($page['body']."\n"));
				} else {
					http_404();
					while(ob_get_level()) ob_end_clean();
					include("http://community.nbtsc.org/missing/index.php");
					exit();
				}
			}
			print("<hr /><p>");
			$links = array();
			if(WIKI_ANONYMOUS_CONTROLS or is_logged_in()) {
				if(WIKI_ANONYMOUS_EDIT or is_logged_in()) {
					if(is_editable($WIKI_PAGEDIR."$pagename")) {
						$links[] = (hyperlink($_SERVER['PHP_SELF']."?edit", "Edit This Page"));
					}
				}


				$prev = rcs_version_dec(isset($initialversion) ? $initialversion :
				$version);
				if(isset($initialversion))
					$a = rcs_version_dec($initialversion);
				$b = rcs_version_dec($version);
				
				if(isset($initialversion)) {
					$links[] = (hyperlink($SELF_NOVERSION.";$a:$b", 'Previous Changes'));
					$links[] = (hyperlink($SELF_NOVERSION.";$version", 
						"Hide Changes"));
				} else {
					$links[] = (hyperlink($SELF_NOVERSION.";$Last:$Current", 
						"Show Changes"));
				}
			}
			if(count(rcs_get_symbolic_names($WIKI_PAGEDIR."$pagename"))) {
				$links[] = (hyperlink($SELF_NOVERSION."?archives", "Archives"));
			}
			if(WIKI_ANONYMOUS_CONTROLS or is_logged_in()) {
				$links[] = (hyperlink($SELF_NOVERSION.";".($b?$b:$version)."?tag", "Add Archive Tag"));
				$links[] = (hyperlink($_SERVER['PHP_SELF']."?revisions", "Revisions"));
				$links[] = (hyperlink($_SERVER['SCRIPT_NAME']."?random", "Random Page"));
			}
			if($version != $Current) {
				$links[] = (hyperlink($SELF_NOVERSION.";Current", "Current Version"));
			}
			$links[] = hyperlink($_SERVER['SCRIPT_NAME'].'?list', "List of Pages");
			$links[] = hyperlink($_SERVER['SCRIPT_NAME'].'?recentchanges', "Recent Changes");
			$links[] = (hyperlink($_SERVER['SCRIPT_NAME'], "Main Page"));
			if(WIKI_LOGINS) {
				if(!is_logged_in()) {
					$links[] = (hyperlink($_SERVER['PHP_SELF']."?login", "Log in"));
				} else {
					$links[] = (hyperlink($_SERVER['PHP_SELF']."?logout", "Log Out"));
					//$links[] = ("Close your browser to log out");
				}
			}
			print("</p>");
			print("<p>".join(' ', $links)."</p>");
			print("<form action='".pqurlencode($_SERVER['PHP_SELF'])."' method='get'><p><label>Search&#xA0;<input type='text' name='search' size='10' /></label></p></form>");
		} elseif($action == 'reply') {
			if(get_magic_quotes_gpc()) {
				$_GET['to'] = stripslashes($_GET['to']);
			}
			print(replyform($page['body'], $_GET['to']));
		} elseif($action == 'edit') {
			if($version != $Current) {
				print("<p><em><strong>Warning:</strong> You're not editing the most recent version
				of this page.   If you make changes here, you'll save over
				something that was written since the page you're reading. Be
				nice and go to the <a
				href='".rawurlencode($pagename).";$Current?edit'>current
				version</a> and edit
				that.</em></p><p><em>If you've already typed something, make
				a copy, don't hit save, go find where you're supposed to put
				it, and then save it there!</em></p>");
			}
			print(editform($page['body'], $page['header'],
				rcs_dereference_version($WIKI_PAGEDIR."$pagename", $version)));
			if($version != $Current) {
				print("<p><em><strong>Warning</strong>, this isn't the current
				version of the page!</em></p>");
			}
		}  else {
			die("Error. Unknown Action");
		}
	} else {
		http_404();
		print(errmsg());
		exit();
	}

	function wiki_load($pagename, $version = 'Current') {
		global $WIKI_PAGEDIR;
		if(succeeds($page = rcs_load($WIKI_PAGEDIR."$pagename", $version))) {
			$page = mime_parse($page);
		}
		if($version == 'Current') {
			$page['stat'] = stat($WIKI_PAGEDIR.$pagename);
			$page['mtime'] = $page['stat'][9];
		}
		return $page;
	}

	function wiki_add_title($s) {
		global $TITLE, $TITLE2;
		$t = $TITLE.($TITLE2?" ($TITLE2)":'');
		if($s and strstr('<html', $s)) {
			return str_replace('<body>', '<body><h1>'.$t.'</h1>', $s);
		} else {
			return heading(1, $t).$s;
		}
	}
		
	function wiki_save_page($pagename, $data, $logmessage = 'No message', $headers = Array(), $noindex = false) {
		global $WIKI_REPOSITORY, $_SERVER, $WIKI_PAGEDIR, $PATH_INFO, $currentver, $revisions;
		$blacklist = file(WIKI_BLACKLIST);
		foreach($blacklist as $b) {
			$b = trim($b);
			if(
				(preg_match('!/!', $b) 
					&& ip_in_block($_SERVER['REMOTE_ADDR'], $b))
				|| $_SERVER['REMOTE_ADDR'] == $b
			) {
					mail('aredridel@nbtsc.org', "Wiki Spam Post from ".$_SERVER['REMOTE_ADDR'], "Page: $pagename\nData:\n$data\n\nLog Message: $logmessage\n");
					sleep(60);
					return true;
			}
		}

		$headers = array('title' => $pagename);
		if($noindex) {
			if(!succeeds(robots_exclude("*", $_SERVER['SCRIPT_NAME']."/$WIKI_REPOSITORY".$pagename))) {
				die("Robots exclude error: ".errmsg());
			}
			$headers['robots'] = 'NoIndex';
		}
		if($previous = wiki_load($pagename, 'Current')) {
			$headers = array_merge(mime_parse_header($previous['header']),
				$headers);
		}
		if(succeeds(rcs_store($WIKI_PAGEDIR."$pagename",
				mime_make_header($headers)."\n\n".str_replace("\r\n", "\n", $data),
				(is_logged_in()?"by ".$_SERVER['REMOTE_ADDR']."\n":"").$logmessage))) {
			$revisions = array_keys(rcs_get_revisions($WIKI_PAGEDIR."$pagename"));
			$currentver = array_shift($revisions);
//			if($revisions[2] == $srcversion and $srcversion) {
				//FIXME: rcs_version_inc is a hack -- should check to see
				// what revision came up.  Some pages get saved with no
				// changes, see...
			return true;
		} else { 
			return false; 
		}
	}

	function wiki_blacklist($ip, $reason) {
		mail('aredridel@nbtsc.org', "Wiki Spammer", "adding $ip because $reason");
		$f = fopen(WIKI_BLACKLIST, 'a');
		fputs($f, $ip."\n");
		fclose($f);
	}

?>
