<?php

	function mime_parse_header($s) {
		$lines = explode("\n", str_replace("\r", "", $s));
		$o = array();
		foreach($lines as $line) {
			if(strspn($line, " \t")) {
				$o[count($o) - 1] .= str_replace("\t", " ", $line);
			} else {
				$o[] = $line;
			}
		}
		$lines = array();
		foreach($o as $line) {
			$temp = explode(":", $line, 2);
			if(empty($temp[0])) continue;
			$lines[strtolower($temp[0])] = trim($temp[1]);
		}
		return $lines;
	}

	function mime_make_header($headers) {
		// Fixme: Can't handle multiple headers with same name yet.
		$o = array();
		foreach($headers as $header => $value) {
			$o[] = ucfirst($header).": ".wordwrap($value, 74, "\n\t", 1);
		}
		return join("\n", $o);
	}
?>
