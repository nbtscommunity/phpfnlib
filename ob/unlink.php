<?php

	if(!is_array($OB_UNLINK)) {
		$OB_UNLINK = array();
	}

	function ob_unlink_start($unlink) {
		global $OB_UNLINK;
		if($unlink[strlen($unlink) - 1] != '/') {
			$unlink .= '/';
		}
		array_push($OB_UNLINK, $unlink);
		ob_start('ob_unlink');
	}

	function ob_unlink($s) {
		// FIXME  -- broken
		global $OB_UNLINK;
		if($unlink = array_pop($OB_UNLINK)) {
			return preg_replace("#<a.*?$unlink.*?>(.*?)</a>#s",
			'\2', $s);
		} else {
			return $s;
		}
	}

?>
