<?php
	require(dirname(__FILE__).'/../http.php');

	function ob_etag($s) {
		global $_SERVER;
		$etag = md5($s);
		if(strstr($_SERVER['HTTP_IF_NONE_MATCH'], $etag)) {
			http_status(304, "Not Modified");
			return '';
		} else {
			header("ETag: \"$etag\"");
			return $s;
		}
	}

?>
