<?php

	function array_put_first($a, $prefix) {
		$t = array();
		foreach($a as $k => $v) {
			if(strstr($v, $prefix)) {
				$t[] = $v;
				unset($a[$k]);
			}
		}
		return array_merge($t, $a);
	}

	function asort2D(&$array, $field) {
		$temp = array();
		foreach($array as $key => $row) {
			$temp[$key] = $row[$field];
		}
		asort($temp);
		$temp2 = array();
		foreach($temp as $key => $val) {
			$temp2[$key] = $array[$key];
		}
		$array = $temp2;
		return $temp2;
	}

	function arsort2D(&$array, $field) {
		$temp = array();
		foreach($array as $key => $row) {
			$temp[$key] = $row[$field];
		}
		arsort($temp);
		$temp2 = array();
		foreach($temp as $key => $val) {
			$temp2[$key] = $array[$key];
		}
		$array = $temp2;
		return $temp2;
	}

?>
