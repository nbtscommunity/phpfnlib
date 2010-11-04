<?php

	if(!is_array($OB_ROOT)) {
		$OB_ROOT = array();
	}

	function ob_root_start($root) {
		global $OB_ROOT;
		if($root[strlen($root) - 1] != '/') {
			$root .= '/';
		}
		array_push($OB_ROOT, $root);
		ob_start('ob_root');
	}

	function ob_root($s) {
		global $OB_ROOT, $PHP_SELF, $PATH_INFO;
		$self = $PHP_SELF;
		if($root = array_pop($OB_ROOT)) {
			$root2=$root;
			if(preg_match('!^'.preg_quote($root).'(.*)!', dirname($self).'/', $matches)) {
				$rem = $matches[1];
				$n = count(explode('/', $rem));
				$root = str_repeat('../', $n - 1);
			}
			//$o .= '<font color="white">'.$self.",\n".dirname($self).",\n".$root2.",\n".$rem;
			if(preg_match_all($re = "#(href=|src=|action=)(\"|')/(.*?)\\2#", $s, $m, PREG_MATCH_ORDER)) {
				$parts = preg_split($re, $s);
				$i = 0;
				foreach($parts as $part) {
					$o .= $part;
					$o .= $m[1][$i].$m[2][$i].($m[3][$i]?$root.$m[3][$i]:'').$m[2][$i];
					$i++;
				}
				return $o;
			} else {
				return $s;
			}
		} else {
			return $s;
		}
	}

?>
