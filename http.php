<?php

	require_once(dirname(__FILE__)."/debug.php");
	require_once(dirname(__FILE__)."/ob/exit.php");

	$__HTTP_STATUS_CALLED = FALSE;

	function http_status($code, $message) {
		global $__HTTP_STATUS_CALLED;
		if($__HTTP_STATUS_CALLED == TRUE) {
			trigger_error("http_status has already been called with $__HTTP_STATUS_CALLED");
		} else {
			$__HTTP_STATUS_CALLED = TRUE;
			header("HTTP/1.1 $code"); # $message");
		}
	}

	function http_302($uri, $message = 'Permanently Moved') {
		global $_SERVER;
		if(!@$_SERVER['FCGI_ROLE']) http_status(302, $message);
		/*
		if($uri{0} == '/') {
			$uri = "http://".$_SERVER['HTTP_HOST'].($_SERVER['HTTP_PORT'] != 80 ? ":".$_SERVER['HTTP_PORT'] : "").$uri;
		}
		*/
		/*
		while(ob_get_level()) ob_end_clean();
		phpinfo();
		exit();
		*/
		header("Location: $uri");
		ob_exit();
	}

	function http_404($message = 'Not Found') {
		http_status(404, $message);
	}

	function http_401($realm = 'default', $auth_type = 'Basic', 
		$opaque = NULL, $message = 'Unauthorized') {
		http_status(401, $message);
		header("WWW-Authenticate: $auth_type realm=\"$realm\"" .
			($opaque ? ' opaque="'.base64_encode(ldapify($opaque)).'"'
				:'')
		);
	}

	function ldapify($array, $sep = ' ') {
		/* Warning: function name will change */
		foreach($array as $key => $val) {
			$out[] = "$key=\"" . addslashes($val) . '"';
		}
		return join($sep, $array);
	}

	function unldapify($string) {
		$out = array();
		preg_match_all('/(\w+)="([^"]*)"/', $string, 
			$matches, PREG_SET_ORDER);
		foreach($matches as $match) {
			$out[$match[0]] = $match[1];
		}
		return $out;
	}
	
	function fs2ht($s) {
		global $_SERVER;
		
		if($s{0} != '/') {
			return $s;
		} else {
			if(strpos($s, 
				$b = dirname($_SERVER['SCRIPT_FILENAME']).'/') === 0) {
				return substr($s, strlen($b) - 1); 
			}
			$s = preg_replace('#^'.preg_quote($_SERVER['DOCUMENT_ROOT'])."/#",
				'/', $s);
			return $s;
		}
	}

	function http_403($message = 'Forbidden') {
		http_status(403, $message);
	}
	
	function http_500($message = 'Server Error') {
		http_status(500, $message);
	}

	function get($uri, $refer = NULL) {
		if(function_exists('curl_init')) {
			debug("getting $uri with curl");
			$session = curl_init();
			curl_setopt($session, CURLOPT_URL, $uri);
			#curl_setopt($session, CURLOPT_NOPROGRESS, 0);
			curl_setopt($session, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($session, CURLOPT_TIMEOUT, 30);
			if($refer) {
				curl_setopt($session, CURLOPT_REFERER, $refer);
			}
			if(defined("HTTP_USERAGENT")) {
				curl_setopt($session, CURLOPT_USERAGENT, HTTP_USERAGENT);
			}

			$o = curl_exec($session);
			if(function_exists('curl_errno')) {
#				if(curl_errno($session) > 0) {
#					print(curl_error($session)."\n");
#				}
			}
			curl_close($session);
			return $o;
		} else {
			debug("Getting $uri with external curl");
			if($f = popen("curl -s ".escapeshellarg($uri), "r")) {
				while(!feof($f)) {
					$o .= fread($f, 1024);
				}
				pclose($f);
			}
			return $o;

			// Obs:
			debug("getting $uri with file()");
			$o = file($uri);
			if(count($o) > 0) {
				return join('', $file);
			} else {
				return '';
			}
		}
	}

	function post($uri, $data) {
		$o = array();
		foreach($data as $field => $val) {
			$o[] = rawurlencode($field).'='.rawurlencode($val);
		}
		$o = join('&', $o);
		$session = curl_init();
		curl_setopt($session, CURLOPT_URL, $uri);
		curl_setopt($session, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($session, CURLOPT_TIMEOUT, 30);
		if(defined("HTTP_USERAGENT")) {
			curl_setopt($session, CURLOPT_USERAGENT, HTTP_USERAGENT);
		}
		curl_setopt($session, CURLOPT_POST, 1);
		curl_setopt($session, CURLOPT_POSTFIELDS, $o); 
		$o = curl_exec($session);
		curl_close($session);
		return $o;
	}

?>
