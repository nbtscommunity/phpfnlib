<?php

	function utf8urlencode($s) {
		return(rawurlencode(utf8_encode($s)));
	}
	
	function utf8purlencode($s) {
		return(purlencode(utf8_encode($s)));
	}

?>
