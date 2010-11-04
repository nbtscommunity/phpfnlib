<?php

	if(!is_array($OB_EA)) {
		$OB_EA = array();
	}

	function ob_ea_start($ea) {
		global $OB_EA;
		array_push($OB_EA, $ea);
		ob_start('ob_ea');
	}

	function ob_ea($s) {
		global $OB_EA;
		if($ea = array_pop($OB_EA)) {
			return preg_replace("#(href=|src=|action=)(\"|')([^\"']*?/)?([^.\"']*?)\\2#m",
			'\1\2\3\4'.$ea.'\2', $s);
		} else {
			return $s;
		}
	}

?>
