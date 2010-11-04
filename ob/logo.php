<?php
	ob_start('ob_logo');

	function ob_logo($s) {
		global $OB_LOGO;
		if($OB_LOGO and preg_match($re='/(<body[^>]*>)/ims', $s)) {
			return preg_replace($re, '\1'.$OB_LOGO, $s, 1);
		} else {
			return $s;
		}
	}
?>
