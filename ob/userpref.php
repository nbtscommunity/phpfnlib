<?php

	require_once(dirname(__FILE__)."/../xml/apply-stylesheet.php");
	require_once(dirname(__FILE__)."/../http.php");

	if(!defined("STYLESHEET_BASE")) define('STYLESHEET_BASE', '/style/');

	function ob_userpref($s) {
		global $_COOKIE;
		$st = $s;
		if(isset($_COOKIE['style'])) { 
			$styles = explode(',', $_COOKIE['style']);
		} elseif(STYLESHEET) {
			$styles = array(STYLESHEET);
		} else {
			$styles = array();
		}

		foreach($styles as $style) {
			if($style == 'debug') {
				$o .= '<?xml-stylesheet href="debug" type="debug"?>';
				continue;
			}
			if($style == 'none') {
				continue;
			}
			if($style{0} != '/') $style = STYLESHEET_BASE."$style";
			if(!strstr("$style", ".")) $style=strtolower($style).".css";
			if(preg_match('/xsl$/', $style)) {
				$o .= "<?xml-stylesheet href='$style' type='text/xsl'?>\n";
			} else {
				$o .= "<?xml-stylesheet href='".fs2ht($style)."' type='text/css' ?>\n";
			}
		} 
		
		list($xmldecl, $rest) = explode("\n", $st, 2);
		if(preg_match('/^<\?xml\\s[^>]*\?>/', $xmldecl)) {
			$st = $rest;
		} else {
			$xmldecl = "<?xml version='1.0' encoding='UTF-8'?>\n";
		}
		return $xmldecl.$o.$st;

	}
?>
