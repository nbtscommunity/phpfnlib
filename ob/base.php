<?php

	if(!is_array($OB_BASE)) {
		$OB_BASE = array();
	}

	function ob_base_start($base) {
		global $OB_BASE;
		if($base[strlen($base) - 1] != '/') {
			$base .= '/';
		}
		array_push($OB_BASE, $base);
		ob_start('ob_base');
	}

	function ob_base($s) {
		global $OB_BASE;
		if($base = array_pop($OB_BASE)) {
			return preg_replace("#(href=|src=)(\"|')(?!https?://|/)#",
			'\1\2'.$base, $s);
		} else {
			return $s;
		}
	}

?>
