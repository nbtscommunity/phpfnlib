<?php
	function purlencode($str) {
		return(str_replace("%2F", "/", rawurlencode($str)));
	}
	
	function pqurlencode($str) {
		return(str_replace(
			array('%2F', '%3F', '%3A', '%7E', '%3B', 
				'%3D', '%26', '%21', '%2A'), 
			array('/', '?', ':', '~', ';', 
				'=', '&', '!', '*'), 
			rawurlencode($str)));
	}
?>
