<?php

	$_IMAP_ID = 0;
	$_IMAP_KEY = 0;
	$_IMAP_RESPONSES = array();
	$_IMAP_CALLBACKS = array();

	function imap_open_url($u, $options = array('imap','notls')) {
		$t = parse_url($u);
		foreach($t as $k => $v) {
			$t[$k] = rawurldecode($v);
		}
		extract($t);
		extract(imap_parse_path($path));
		if(!$mailbox) {
			$options2 = OP_HALFOPEN;
		}
		$c = imap_open(
			'{'.$host.'/'.join('/', $options).'}'.$mailbox,
			$user, $pass, $options2);
		if(!$c) {
			print_r(imap_errors());
			return FALSE;
		}
		return $c;
	}

	function imap_get_url($u) {
		if(!$c = imap_open_url($u)) {
			return FALSE;
		}
		extract(parse_url($u));
		extract(imap_parse_path($path));
		if($mailbox) {
			if($uid) {
				$msgno = imap_msgno($c, $uid);
				$o = imap_fetchstructure($c, $msgno);
			} else {
				$o = array();
				$n = imap_num_msg($c);
				while($i++ < $n) {
					$o[] = imap_fetchheader($c, $i);
				}
			}
		} else {
			$o = imap_list($c, '{}', '*');
		}
		return $o;
	}

	function imap_parse_path($p) {
		if($p{0} == '/') {
			$p = substr($p, 1);
		}
		if(preg_match('!^(.*)(?:;UIDVALIDITY=(.*))?(?:;UID=(.*))?!i', $p, $m)) {
			$o['mailbox'] = $m[1];
			if($m[2]) $o['uidvalidity'] = $m[2];
			if($m[3]) $o['uid'] = $m[3];
		}
		return $o;
	}

	function IMAP_connect($s, $p = '143') {
		global $_IMAP_KEY;
		$c = fsockopen($s, $p);
		socket_set_blocking($c, FALSE);
		return array('conn' => $c, 'key' => $_IMAP_KEY++, 
			'callbacks' => array(), 'cbdata' => array(), 
			'state' => 'UNAUTHENTICATED');
	}

	function IMAP_select(&$c, $mailbox) {
		$c['mailbox'] = $mailbox;
		return IMAP_command($c, "SELECT $mailbox", 'IMAP_select_handler', $mailbox);
	}

	function IMAP_login(&$c, $user, $credentials, $authtype = 'PLAIN') {
		$id = IMAP_command($c, "LOGIN \"$user\" \"$credentials\"");
		$r = IMAP_wait($c, $id);
		IMAP_clear($c, $id);
		if(strstr($r, 'NO') or strstr($r, 'BAD')) {
			return FALSE;
		} else {
			return TRUE;
		}
	}

	function IMAP_command(&$c, $command, $callback = NULL, $cbdata = NULL) {
		global $_IMAP_ID;
		$id = ++$_IMAP_ID;
		$data = "$id $command\r\n";
		$len = strlen($data);
		if(fwrite($c['conn'], $data, $len) !== FALSE) {
			return -1;
		} else {
			$c['callbacks'][$id] = $callback;
			$c['cbdata'][$id] = $cbdata;
			return $id;
		}
	}

	function IMAP_wait(&$c, $id = NULL) {
		while($l = fgets($c['conn'], 1024)) {
			list($sid, $r) = explode(' ', $l, 2);
			if($c['callbacks'][$sid]) {
				$o = call_user_func($c['callbacks'][$sid], $c, $r,
					$c['cbdata'][$sid]);
			} else {
				$o = IMAP_default_callback($c, $r);
			}
			if(is_null($id) or $id == $sid) return $o;
		}
	}

	function IMAP_default_callback($c, $r) {
		return $r;
	}

	function IMAP_listmessages(&$c) {
		return IMAP_command($c, '');
	}

	function IMAP_clear(&$c, $id) {
		global $_IMAP_RESPONSES;
		unset($_IMAP_RESPONSES[$id]);
	}

	function IMAP_closef(&$c) {
		return IMAP_command($c, 'CLOSE');
	}

	function IMAP_register($id, $callback) {
		global $_IMAP_CALLBACKS;
		$_IMAP_CALLBACKS[$id] = $callback;
	}

	function IMAP_logout(&$c) {
		$id = IMAP_command($c, 'LOGOUT');
		$o = IMAP_wait($c, $id);
		IMAP_clear($c, $id);
		fclose($c['conn']);
		return $o;
	}

?>
