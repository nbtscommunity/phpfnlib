<?php
	function load_file($filename) {
		$file = @file($filename);
		if(is_array($file)) {
			return join('', $file);
		} else {
			return NULL;
		}
	}

	function load_mimefile($filename) {
		$file = load_file($filename);
		if($file === NULL) { return NULL; }

		return mime_parse($file);
	}


	function mime_parse($data) {
		list($header, $body) = @explode("\n\n", str_replace("\r","\n",$data), 2);
		return array('header' => $header, 'body' => $body);
	}
		
?>
