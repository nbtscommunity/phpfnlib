<?php

	require(dirname(__FILE__)."/../url.php");

	if(!defined('COUNTER_DATASTORE')) 
		define('COUNTER_DATASTORE', '/var/tmp/counter-data');

	function count_current_page() {
		if(!is_dir(COUNTER_DATASTORE)) mkdir(COUNTER_DATASTORE);

		$self = rawurlencode(url_self());
		$file=COUNTER_DATASTORE.'/'.$self;

		if(is_file($file) and is_readable($file)) {
			//Changed by Erek because this didn't work.
			// array_shift takes the array as a reference, and a function doesn't make a very good reference.
			//$cur = array_shift(file($file));
			$cur = file_get_contents($file);
		} else {
			$cur = 0;
		}

		$f = fopen($file, 'w');
		fputs($f, ++$cur);
		fclose($f);

		return $cur;
	}

?>
