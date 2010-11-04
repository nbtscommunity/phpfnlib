<?php
	if(function_exists('iconv_set_encoding')) {
		iconv_set_encoding('internal_encoding', 'UTF-8');
		if(in_array('UTF-8', split(', ?', $HTTP_ACCEPT_CHARSET))) {
			iconv_set_encoding('output_encoding', $charset='UTF-8');
		} else {
			iconv_set_encoding('output_encoding', $charset='ISO-8859-1');
		}
		ob_start('ob_iconv_handler'); 
	} else {
		$charset = 'UTF-8';
	}
	header("Content-type: text/html; charset=$charset");

//	if(in_array('gzip', split(', ?',$HTTP_ACCEPT_ENCODING))) {
//		ob_start('ob_gzhandler');
//	}
?>
