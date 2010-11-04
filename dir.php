<?php 

	function get_dir($d) {
		$o = array();
		$dir = opendir($d);
		while($e = readdir($dir)) {
			if($e{0} != '.') $o[$e] = $e;
		}
		return array_keys($o);
	}

	function dir_hidden_files($val) {
		if($val{0} == 0) {
			return false; 
		} else {
			return true;
		}
	}

?>
