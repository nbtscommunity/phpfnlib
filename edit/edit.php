<?php

	require_once(dirname(__FILE__)."/../errors.php");
	require_once(dirname(__FILE__)."/../file.php");

	if(!defined("EDIT_USE_SUOPEN")) {
		define("EDIT_USE_SUOPEN", TRUE);
	}

	function edit_save($file, $data) {
		if(@is_writable($file)) {
			$f = fopen($file, "w");
			fputs($f, $data);
			fclose($f);
			return TRUE;
		} elseif(EDIT_USE_SUOPEN and $f = suopen($file, "w")) {
			fputs($f, $data);
			suclose($f);
			return TRUE;
		} else {
			error("$file isn't writable");
			return FALSE;
		}
	}

	function edit_form($file) {
		global $PHP_SELF;
		if(@is_readable($file)) {
			$data = join('', file($file));
		} elseif(EDIT_USE_SUOPEN and $f = suopen($file, "r")) {
			while(!feof($f)) {
				$data .= fread($f, 16000);
			}
			suclose($f);
		}
		return form($PHP_SELF,
			textarea('data', $data).submit('Save'));
	}

	function is_editable($file) {
		return is_writable($file);
	}

?>
