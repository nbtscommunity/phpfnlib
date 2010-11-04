<?php

	function do_expect($script, $fds) {
		foreach($fds as $k => $fd) {
			stream_set_blocking($fd, FALSE);
		}
		$timeout = 3;
		foreach($script as $lineno => $line) {
			unset($action);
			unset($fail);
			unset($pattern);
			unset($fd);
			unset($current);
			extract($line);
			if(!isset($fd)) $fd = 1;
			if($pattern == 'timeout') {
				$timeout = $value;
				continue;
			} elseif($pattern{0} == '/') {
				$start = time();
				$end = 0;
				while(($end - $start) <= $timeout) {
					$t = $timeout;
					$n = stream_select($r = array($fds[$fd]), $w = NULL, 
						$e = NULL, $t);
					if($n === false) {
						return false;
					} else {
//						print("selected $n<br />\n"); flush();
						$end = time();
						$d = $timeout - ($end - $start);
						if($n == 0) {
							if(isset($fail)) {
								$r = eval($fail);
								if($r !== NULL) return $r;
							} 
							break;
						} else {
							$current .= fread($r[0], 16); 
//							print("have $current; $d of $timeout remain<br />\n"); flush();
							if(preg_match($pattern, $current)) {
//								print("matched $pattern<br />"); flush();
								if(isset($action)) {
									$r = eval($action);
									if($r !== NULL) return $r;
								} 
								break;
							}
						}
					}
				}
			}
		}
		return 0;
	}

?>
