<?php

	require_once(dirname(__FILE__) . "/errors.php");

	function file_load($filename) {
		if(is_array($f = @file($filename))) {
			return join('', $f);
		} else {
			error("Could not read file $filename");
			return E_SERVFAIL;
		}
	}

	function file_store($filename, $data) {
		if ($f = fopen($filename, "a+")) {
			if(flock($f, LOCK_EX)) {
				if(ftruncate($f, 0)) {
					if(fwrite($f, $data, strlen($data))) {
						flock($f, LOCK_UN);
						fclose($f);
						return E_SUCCESS;
					} else {
						error('Could not write file');
						return E_SERVFAIL;
					}
				} else {
					error('Could not truncate file');
					return E_SERVFAIL;
				}
			} else {
				error('Could not lock file');
				return E_SERVFAIL;
			}
		} else {
			error('Could not open file for writing');
			return E_SERVFAIL;
		}
	}

?>
