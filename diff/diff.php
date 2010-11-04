<?php

	require_once(dirname(__FILE__)."/../phpstdlib.php");

	function diff_dump($diff) {
		print("<table>");
		foreach($diff as $hunk) {
			print("<tr><td colspan='3'>Hunk ".++$hunkcount.":</td></tr>\n");
			foreach($hunk as $item) {
				print("<tr><td>".$item[0]."</td><td><pre>");
				var_dump($item[1]);
				print("</pre></td><td><pre>");
				var_dump($item[2]);
				print("</pre></td><td>{$item[3]},{$item[4]}</tr>\n");
			}
		}
		print("</table>");
	}
	
	function diff_highlight_dump($diff) {
		print("<table>");
		foreach($diff as $item) {
			print("<tr><td>".$item[0]."</td><td><pre>");
			var_dump($item[1]);
			print("</pre></td><td><pre>");
			var_dump($item[2]);
			print("</pre></td><td>{$item[3]},{$item[4]}</tr>\n");
		}
		print("</table>");
	}


	function traverse_sequences($a, $b, $match, $discard, $add, 
		$keygen = NULL, $compare = NULL, $bail = 0) {
		$matchVector = _longestCommonSubsequence($a, $b, $keygen, $compare);
		if(!count($matchVector) or ((1 / (count($a) / count($matchVector))) < $bail)) {
//			print("Bail! ".var_export($a).var_export($b));
			return FALSE; 
		}
		ksort($matchVector);
		
		$lastA = count($a) - 1;
		$lastB = count($b) - 1;
		
		end($matchVector);
		$lastMV = key($matchVector);
		$bi = 0;
		for($ai = 0; $ai <= $lastMV; $ai++) {
		//for($ai = 0; $ai < count($a) - 1; $ai++) {
			if(isset($matchVector[$ai])) {
				while($bi < $matchVector[$ai]) { 
//					print("-a($ai)+b($bi)<br />\n");
					$add($a[$ai], $b[$bi], $ai, $bi++); 
				}
//				print("=\t".$a[$ai]."\n");
//				print("=a($ai),b($bi)<br />");
				$match($a[$ai], $b[$bi], $ai, $bi++);
			} else {
//				print("+a($ai)-b($bi)<br />");
				$discard($a[$ai], $b[$bi], $ai, $bi);
			}
		}

//		print("h(ai) = $ai max(ai) = $lastA<br />  ");
//		print("h(bi) = $bi max(bi) = $lastB<br />  ");
//		print("<pre>");
//		print("matchVector:");print_r($matchVector);
//		print("a:"); print_r($a);
//		print("b:"); print_r($b);
//		print("</pre>");

		while($ai <= $lastA) {
//			print("-\t".$a[$ai]."\n");
			$discard($a[$ai], $b[$bi], $ai++, $bi);
		}
		while($bi <= $lastB) {
//			print("+\t".$b[$bi]."\n");
			$add($a[$ai], $b[$bi], $ai, $bi++);
		}

		return TRUE;
	}

	function LCS($a, $b, $keygen = NULL, $compare = NULL) {
		$retval = array();
		$matchVector = _longestCommonSubsequence($a, $b, $keygen, $compare);
		foreach($matchVector as $i => $v) {
			array_push($retval, $a[$i]);
		}
		return $retval;
	}

	function _longestCommonSubsequence($a, $b, $keygen = NULL, 
		$compare = NULL) {
		if($keygen === NULL) {
			$keygen = create_function('$a', 
				'if(is_object($a)) { 
					if(method_exists($a, "strval")) {
						return $a->strval();
					} else {
						return var_export($a, TRUE);
					}
				} elseif(is_array($a)) {
					return var_export($a, TRUE);
				} else {
					return strval($a);
				}');
		}

		if($compare == NULL) {
			$compare = create_function('$a, $b', 'return $a == $b;');
		}
			
		$aStart = 0;
		$aFinish = count($a) - 1;
		$bStart = 0;
		$bFinish = count($b) - 1;
		$matchVector = array();

		while($aStart <= $aFinish 
			and $bStart <= $bFinish 
			and $compare($keygen($a[$aStart]), $keygen($b[$bStart]))) {
			$matchVector[$aStart++] = $bStart++;
		} 

		while ($aStart <= $aFinish 
			and $bStart <= $bFinish 
			and $compare($keygen($a[$aFinish]), $keygen($b[$bFinish]))) {
			$matchVector[$aFinish--] = $bFinish--;
		}

//		print("Match Vector: "); print_r($matchVector);

		$bMatches = _withPositionsOfInInterval($b, $bStart, $bFinish, $keygen);
		$thresh = array();
		$links = array();
		
//		print("bMatches(low $bStart, high $bFinish): "); print_r($bMatches);

		// What is all this?  This can't be sane:
		for($i = $aStart; $i <= $aFinish; $i++) {
			$ai = $keygen($a[$i]);
			if($bMatches[$ai]) {
				$k = 0;
				$temp = array_reverse($bMatches[$ai]);
				foreach($temp as $j) {
					if($k 
						and $thresh[$k] > $j 
						and $thresh[$k - 1] < $j) {
						$thresh[$k] = $j;
					} else {
						$k = _replaceNextLargerWith(&$thresh, $j, $k);
					}
					if($k >= 0) {
						$links[$k] = array(($k ? $links[$k - 1]: NULL), $i, $j);
					}
				}
			}
		}

//		print("Thresh: "); print_r($thresh);
//		print("Links: "); print_r($links);
//		print("Match Vector: ");print_r($matchVector);

		if(count($thresh)) {
			for($link = end($links); $link; $link = $link[0]) {
//				print("Link: "); print_r($link);
				$matchVector[$link[1]] = $link[2];
//				print("Match Vector: ");print_r($matchVector);
			}
		}

//		print("Match Vector: ");print_r($matchVector);
		ksort($matchVector);
//		print("Match Vector: ");print_r($matchVector);
		return $matchVector;
	}

	function diff($a, $b, $keygen = NULL, $compare = NULL, $bail = 0) {
		global $retval, $hunk;
		$retval = array();
		$hunk = array();

		if(traverse_sequences($a, $b, '_diff_match', '_diff_discard', '_diff_add', 
			$keygen, $compare, $bail)) {
			if(count($hunk)) {
				$retval[] = $hunk;
				unset($hunk);
			}

			$myretval = $retval;
			unset($retval);
			return $myretval;
		} else { 
			return FALSE;
		}
	}
	
	function diff_highlight($a, $b, $keygen = NULL, $compare = NULL, $bail = 0) {
		global $retval;
		$retval = array();

		if(traverse_sequences($a, $b, 
			'_diff_highlight_match', '_diff_highlight_discard', '_diff_highlight_add', 
			$keygen, $compare, $bail)) {
			
			$myretval = $retval;
			unset($retval);
			return $myretval;

		} else {
			return(FALSE);
		}
	}

	function _diff_discard($a, $b, $pa, $pb, $similarity = 0) {
		global $retval, $hunk;
		//if($a == $b) { return _diff_match($a, $b, $pa, $pb, $similarity); }
//		print("discard<br />");
		$hunk[] = array('-', $a, $b, $pa, $pb);
	}

	function _diff_add($a, $b, $pa, $pb, $similarity = 0) {
		global $retval, $hunk;
		//if($a == $b) { return _diff_match($a, $b, $pa, $pb, $similarity); }
//		print("add<br />");
		$hunk[] = array('+', $a, $b, $pa, $pb);
	}

	function _diff_match($a, $b, $pa, $pb, $similarity = 1) {
		global $retval, $hunk;
		if(count($hunk)) {
			$retval[] = $hunk;
			$hunk = array();
		}
//		print("match<br />");
	}

	function _diff_highlight_match($a, $b, $pa, $pb) {
		global $retval;
//		print("match<br />");
		$retval[] = array('=', $a, $b, $pa, $pb);
	}


	function _diff_highlight_add($a, $b, $pa, $pb) {
		global $retval;
		$retval[] = array('+', $a, $b, $pa, $pb);
//		print("add<br />");
	}

	function _diff_highlight_discard($a, $b, $pa, $pb) {
		global $retval;
		$retval[] = array('-', $a, $b, $pa, $pb);
//		print("discard<br />");
	}

	function _withPositionsOfInInterval($a, $start, $end, $keygen) {
		$d = array();
		for($index = $start; $index <= $end; $index++) {
			$element = $keygen($a[$index]);
			if($d[$element]) {
				array_push($d[$element], $index);
			} else {
				$d[$element] = array($index);
			}
		}
		return $d;
	}

	function _replaceNextLargerWith(&$a, $aValue, $high) {
		if(!$high) { $high = (count($a) - 1); }

		if($high == -1 or $aValue > end($a)) {
			array_push($a, $aValue);
			return $high + 1;
		}

		$low = 0;
		while($low <= $high) {
			$index = (int)(($high + $low) / 2);
			$found = $a[$index];
			if($aValue == $found) {
				return -1;
			} elseif($aValue > $found) {
				 $low = $index + 1;
			} else {
				 $high = $index - 1;
			}
		}
		$a[$low] = $aValue;
		return $low;
	}


//	$ta = array('a', 'b', 'c', 'd', 'e');
//	$tb = array('a', 'c', 'd', 'e', 'f', 'g', 'e');
	
//	$lcsmv = _longestCommonSubsequence($ta, $tb);
//	$lcs = LCS($ta, $tb);
//	print("LCS: "); print_r( $lcs );
//	print("LCS Vector: "); print_r($lcsmv);
//	print("A: "); print_r($ta);
//	print("B: "); print_r($tb);

//	$diff = diff($ta, $tb);
//	print("Diff: "); print_r($diff);

?>
