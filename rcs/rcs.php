<?php

	require_once(dirname(__FILE__).'/../errors.php');
	require_once(dirname(__FILE__).'/../exec.php');
	require_once(dirname(__FILE__).'/../file.php');

	function rcs_store($filename, $data, $logmessage = '') {
		$efn = escapeshellarg($filename);
		if(file_exists(rcs_filename($filename)) 
			and do_exec("co -f -l $efn", &$out) != 0) {
			error('Could not check out file:' . join("<br />", $out));
			return E_SERVFAIL;
		} else {
			if(!file_exists(rcs_filename($filename))) {
				do_exec("rcs -i -U $filename");
			}
			if(!succeeds($e = file_store($filename, $data))) {
				return $e;
			} else {
				if($logmessage) {
					$elm = escapeshellarg($logmessage);
				} else {
					$elm = "'No message'";
				}
				if(do_exec("ci -u -m$elm $efn", &$out) == 0) {
					chmod($filename, 0666);
					return E_SUCCESS;
				} else {
					error("Could not checkin file: ". join("<br />" ,$out));
					return E_SERVFAIL;
				}
			}
		}
	}
	
	function rcs_version_dec($version) {
		$temp = explode('.', $version);
		$i = count($temp) - 1;
		if(--$temp[$i] < 1) {
			$temp[$i] = 1;
			if($i > 3) {
				array_pop($temp);
				array_pop($temp);
			}
		}
		$prev = join('.', $temp);
		return $prev;
	}
	
	function rcs_version_inc($version) {
		$temp = explode('.', $version);
		$i = count($temp) - 1;
		++$temp[$i];
		$next = join('.', $temp);
		return $next;
	}


	function rcs_filename($filename) {
		if(is_file($fn = dirname($filename)."/RCS/".basename($filename).",v")) {
			return $fn;
		} else {
			return $filename.",v";
		}
	}

	function rcs_find_last_revision($filename) {
		$f = popen("rlog ".escapeshellarg($filename)." 2>/dev/null", 'r');
		$revisions = array();
		while(!feof($f) and count($revisions) < 2) {
			$line = fgets($f, 200);
			if(preg_match('/^revision ([0-9.]+)/', $line, $matches)) {
				$revisions[] = $matches[1];
			}
		}
		while(!feof($f)) fgets($f, 4096);
		pclose($f);
		list($current, $last) = $revisions;
		return ($last ? $last : 'Current');

	}

	/* Returns a two-dimensional associative array containing info about
	 * revisions. The first-level array keys are revision numbers (versions).
	 * The second-level keys are various attributes pertaining to that revision,
	 * e.g. 'message' or 'date'.
	 */
	function rcs_get_revisions($filename) {
		$logging = NULL;
		$revision = NULL;
		$count = 0;
		if(is_dir($filename)) return Array();
		if($f = popen("rlog ".escapeshellarg($filename).' 2>/dev/null', 'r')) {
			$revisions = array();
			while(!feof($f) and $count++ < 1000) {
				$line = fgets($f, 1024);
				if(preg_match('/^revision (.*)$/', $line, $matches)) {
					$revision = $matches[1];
					$revisioninfo = array();
				} elseif(preg_match('/date:([^;]*).*author:.*state:.*/', $line, $matches)) {
					$logging = TRUE;
					$log = '';
					$revisioninfo['date'] = $matches[1];
				/* Check that $revision is non-empty, or we'll add a
				 * blank array element in $revisions */
				} elseif($revision and preg_match('/^[-=]+$/', $line)) {
					$logging = FALSE;
					$revisioninfo['message'] = $log;
					$revisions[$revision] = $revisioninfo;
				} else {
					if($logging) $log .= $line;
				}
			}
			pclose($f);
			return $revisions;
		} else {
			return E_SERVFAIL;
		}
	}
	
	function rcs_get_symbolic_names($filename) {
		if($f = popen("rlog ".escapeshellarg($filename).' 2>/dev/null', 'r')) {
			$o = array();
			while(!feof($f) and trim(fgets($f, 1024)) != 'symbolic names:');
			while(!feof($f) and $line = fgets($f, 1024)) {
				if($line{0} != "\t") break;
				list($k, $v) = explode(': ', trim($line), 2);
				$o[$k] = $v;
			}
			pclose($f);
			return $o;
		} else {
			return E_SERVFAIL;
		}
	}

	function rcs_apply_tag($filename, $version, $tag) {
		if(system("rcs -n".escapeshellarg($tag).":".
				escapeshellarg($version)." ".
				escapeshellarg($filename)) == 0) {
			return E_SUCCESS;
		} else {
			return E_SERVFAIL;
		}
	}

	function rcs_load($filename, $revision = 'Current') {
		if($revision == 'Last') $revision =
			rcs_find_last_revision($filename);
		$erv = escapeshellarg($revision);
		$efn = escapeshellarg($filename);
		if($revision == 'Current') {
			return file_load($filename);		
		} else {
			if(do_exec("co -q -p$erv $efn", &$out) == 0) {
				return(join("\n", $out));
			} else {
				error("Can't retrieve version $revision of $filename");
				return E_SERVFAIL;
			}
		}
	}

	function rcs_dereference_version($filename, $version) {
		$revisions = rcs_get_revisions($filename);
		if($version == 'Current') {
			if(count($revisions)) {
				return key($revisions);
			} else {
				return '1.1';
			}
		} elseif ($version == 'Last') {
			array_shift($revisions);
			if(key($revisions)) {
				return key($revisions);
			} else {
				return '1.1';
			}
		} else {
			return $version;
		}
	}

	function rcs_revisions($filename) {
		$efn = escapeshellarg($filename);
		if(do_exec("rlog $efn 2>/dev/null", &$out) == 0) {
			$r = array();
			foreach($out as $i => $v) {
				if(preg_match('/^revision\s+((\d+\.)*\d+)$/', $v, $m)) {
					$rev = $m[1];
					$r[$rev] = '';
				} elseif(preg_match('/^date:/', $v)) {
				} elseif(strspn($v, '-') == strlen(trim($v))) {
					unset($rev);
				} elseif(strspn($v, '=') == strlen(trim($v))) {
					unset($rev);
				} else {
					if($rev and trim($v) != 'No' and trim($v) != 'No message') {
						$r[$rev] .= $v;
					}
				}
			}
			array_shift($r);
			return $r;
		} else { 
			return E_SERVFAIL;
		}
	}

	function rcs_cmpver($a, $b) {
		if($a == $b) return 0;
		$a = explode('.', $a);
		$b = explode('.', $b);
		while($c = array_shift($a)) {
			$d = array_shift($b);
			if($c != $d) return $c - $d;
		}
		return 0;
	}

?>
