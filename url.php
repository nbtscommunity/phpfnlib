<?php

	function url_self() {
		global $_SERVER;

		return 'http://'.$_SERVER['HTTP_HOST'].
			($_SERVER['SERVER_PORT'] == 80 ? '' : ':80').
			$_SERVER['REQUEST_URI'];
	}

?>
