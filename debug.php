<?php

	if(!defined('DEBUG')) define('DEBUG', FALSE);

	function debug($str) {
		if(DEBUG) {
			if(function_exists('comment')) {
				print(comment($str)."\n");
			} else {
				print("Debug: $str\n");
			}
		}
	}
	
	function print_lined($t) {
		$lines = explode("\n", $t);
		foreach($lines as $line) {
			print(++$i.": ".htmlspecialchars($line)."<br />");
		}
	}

	function vdump($v) {
		ob_start();
		var_dump($v);
		$o = ob_get_contents();
		ob_end_clean();
		return $o;
	}




?>
