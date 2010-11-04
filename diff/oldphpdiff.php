<?php

	print(textdiff("The quick brown fox jumped over the lazy dogs", "The
	Quick Brown Fox jumped over the lazy dogs"));

	function textdiff($a, $b) {
		return difflist2html(list_diff(
			preg_split("#\\s+#", $a), preg_split("#\\s+#", $b)));
	}

	function difflist2html($l) {
		$state = 'same';
		$a = array();
		foreach($l as $e) {
			if($e['rel'] != $state) {
				$o .= join(' ', $a);
				$a = array();
				if($state == 'deleted') {
					$o .= '</s> ';
				} elseif($state == 'added') {
					$o .= "</ins> ";
				}
				$state = $e['rel'];
				if($state == 'deleted') {
					$o .= ' <s>';
				} elseif($state == 'added') {
					$o .= " <ins>";
				}
			}
			$a[] = $e['data'];
		}

		$o .= join(' ', $a);

		if($state != 'same') {
			if($state == 'deleted') {
				$o .= '</s>';
			} elseif($state == 'added') {
				$o .= "</ins>";
			}
		}
		return($o);
	}
	

	function listfmt($a) {
		return(join(', ', $a));
	}

	function list_diff($a, $b) {
//		print("a: ".listfmt($a)."<br />\n");
//		print("b: ".listfmt($b)."<br />\n");
		$o = array();

		if(count($a) == 0) {
			foreach($b as $new) {
				array_push($o, added($new));
			}
			return $o;
		}
		
		$l = reset($a);
		$r = reset($b);
//		print("Comparing $l and $r<br />\n");
		if($l == $r) {
			array_push($o, same($l));
			array_shift($a);
			array_shift($b);
		} else {
//			print("$l and $r are different; resynching\n");
			list($added, $remain) = find_best_match($a, $b);
			if(count($added) > 0) {
				foreach($added as $new) {
					array_push($o, added($new));
				}
				$b = $remain;
//				print("Found ${added[0]}; ". count($remain). " Remain\n");
			} else {
				array_push($o, deleted($l));
				array_shift($a);
		//		array_shift($b);
			}
		}
		$n = list_diff($a, $b);
		foreach($n as $new) {
			$o[] = $new;
		}
		return $o;
	}

	function find_best_match($a, $b, $tries = 1) {
		if(count($a) == 0) {
			return array(array(), $b);
		}
		$l = array_shift($a);
//		print("Finding match for $l\n");
		foreach($b as $k => $v) {
			if($v == $l and (++$try == $tries)) break;
		}
		if(!is_null($b[$k + 1]) and $b[$k + 1] == $a[0]) { // alter this for match-quality changes
//			print("Found good match ". $b[$k] . " followed by ". $b[$k + 1]);
			return array(
				array_slice($b, 0, $k),
				array_slice($b, $k, count($b)));
		} else {
			if($v == count($b)) {
				return array(array(), $b);
			} else {
				list($added, $remain) = find_best_match($a, $b, $tries + 1);
				if(count($added) > 0) {
					return array($added, $remain);
				} else {
					return array(array(), $b);
				}
			}
		}
	}

	function same($s) {
//		print("Same: $s<br />\n");
		return array('rel' => 'same', 'data' => $s);
	}

	function added($s) {
//		print("Added: $s<br />\n");
		return array('rel' => 'added', 'data' => $s);
	}
	
	function deleted($s) {
//		print("Delete: $s<br />\n");
		return array('rel' => 'deleted', 'data' => $s);
	}
?>
