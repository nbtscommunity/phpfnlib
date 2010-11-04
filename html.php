<?php
	require_once(dirname(__FILE__).'/purlencode.php');

	define('br', "<br />\n");
	define('hr', "<hr />\n");

	function tabular($d) {
		print("<tr><td>". str_replace("\t", "</td><td>", $d). "</td></tr>\n");
	}

	function div($class, $contents) {
		return("\n<div class='$class'>" . $contents . "\n</div>");
	}

	function heading($level = 1, $data) {
		return("\n<h$level>" . $data . "\n</h$level>");
	}

	function preformat($s) {
		return("<pre>$s</pre>");
	}

	function checkbox($name, $value) {
		return("<input type='checkbox' name='$name' value='TRUE' " . ($value ? "checked='checked'" : '') . " />");
	}

	function radiogroup($name, $values, $default = NULL, $separator = '<br />') {
		if(count($values) == 0) return '';
		$value = array_shift($values);
		return "<input name='$name' type='radio' value='$value'".
		($value == $default ? " checked='checked'" : "").
		" />$value$separator".radiogroup($name,$values,$default,$separator);
	}

	function p($s) {
		return "<p>$s</p>";
	}

	function fieldset($name, $s) {
		return "<fieldset><legend>$name</legend>$s</fieldset>";
	}

	function button($name, $text, $value = NULL) {
		if(is_null($value)) $value = $text;
		return("<button name='$name' value='$value' type='submit'>$text</button>");
	}

	function doctype($s) {
		static $types = array(
			"XHTML/1.0" => "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0
			Strict//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">",
			"XHTML/1.0 Frameset" => "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Frameset//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">");
			return $types[$s];
	}

	function form($action, $content, $enctype = '') {
		$method = 'POST';
		$action = pqurlencode($action);
		if($enctype) { 
			$enctype = "enctype='$enctype'";
		}
			
		return("\n<form action='$action' accept-charset='UTF-8' method='$method' $enctype>"
		. $content . "\n</form>");

	}

	function javascript($s) {
		return "<script language='JavaScript'> $s </script>";
	}

	function body($s) {
		return '<body>'.$s.'</body>';
	}

	function html($s) {
		return '<html>'.$s.'</html>';
	}

	function head($s) {
		return '<head>'.$s.'</head>';
	}
	
	function title($s) {
		return '<title>'.$s.'</title>';
	}

	function stylesheet($filename) {
		if(is_file($filename)) {
			return "<link rel='StyleSheet' href='$filename' type='text/css' />";
		} else {
	//		return "Stylesheet not found!";
		}
	}

	function hyperlink($destination, $content, $encode = true) {
		if($encode) $destination = htmlspecialchars(pqurlencode($destination));
		$content = htmlspecialchars(trim($content));
		return("<a href='$destination'>$content</a>");
	}

	function popup_hyperlink($destination = '', $content, $attribs = array()) {
		global $PHP_SELF;
		if(!$destination) {
			$destination = $PHP_SELF;
		}
		if(count($attribs) > 0) {
			foreach($attribs as $k => $a) {
				$attribs[$k] = "$k=$a";
			}
			$features = ',"'.join(",",$attribs).'"';
		} else {
			$features = '';
		}
		return("\n<a href='$destination' ".
			"onClick='window.open(\"$destination\",null$features); return false;' ".
			"target='_new'>$content</a>");
	}
	
	function textarea($key, $value = '', $cols = 60, $rows = 10) {
		return("\n<textarea name='$key' cols='$cols' rows='$rows' wrap='virtual'>".htmlspecialchars($value, NULL, "UTF-8")."</textarea>");
	}

	function fileupload($name) {
		return "<input name='$name' type='file' />";
	}

	function addresstextarea($key, $value='') {
		return textarea($key, $value, 3, 40);
	}

	function address($address) {
		return "<address>".$address."</address>";
	}

	function field($key, $value = '', $type='text') {
		if($type == 'textarea') {
			return(textarea($key, $value));
		} else {
			return(
				"\n<input type='$type' class='$type' name='$key' ".
				"value='$value' />"
			);
		}
	}

	function hidden($key, $value) {
		return(field($key, $value, 'hidden'));
	}

	function textinput($key, $value, $size = 8) {
		return("<input type='text' name='$key' value='$value' size='$size' />");
	}

	function password_field($key, $value = '') {
		return(field($key, $value, 'password'));
	}
	
	function staticfield($key, $value) {
		return($value. field($key, $value, 'hidden'));
	}

	function image($file, $alt = "") {
		return("<img src='".pqurlencode($file)."' alt='$alt' />");
	}

	/* function hoverimagelink($url, $lowfile, $hifile, $alt = '') {
		return("<a href='$url' id='".($id=html_id())."'>".
			"<img src='$lowfile' alt='$alt' /></a>".
			"<script type='text/javascript'></script>"));
	} */

	function html_id() {
		global $_HTML_CURRENT_ID;
		return ++$_HTML_CURRENT_ID;
	}

	function table($contents) {
		return("\n<table border='0'>" . $contents . "\n</table>");
	}

	# "Alignment" table -- no space between cells, so it's good for aligning stuff transparently, such as sidebars (or so Rick says)
	function atable($contents) {
		return("\n<table border='0' cellspacing='0' cellpadding='0'>" . $contents . "\n</table>");
	}
	
	function table_spaced($contents, $cellspacing = 4, $cellpadding = 4) {
		return("\n<table border='0' cellspacing='$cellspacing' cellpadding='$cellpadding'>" . $contents . "\n</table>");
	}

	function tr($contents) {
		return("\n<tr>" . $contents . "\n</tr>");
	}

	function tgroup($contents) {
		return("\n<span class='tgroup'>" . $contents . "\n</span>");
	}

	function td($contents) {
		if(is_int($contents) or is_float($contents) or ($contents[0] == '$')) {
			$class=" class='numeric'";
		} else {
			$class='';
		}
		return("\n<td valign='top'$class>" . $contents . "\n</td>");
	}

	function spantd($span = 1, $contents) {
		return("\n<td valign='top' colspan='$span'>" . $contents . "\n</td>");
	}

	function rtd($contents) {
		return("<td valign='top' align='right'>$contents</td>");
	}

	function th($contents, $align='center') {
		return("\n<th align='$align'>" . $contents . "\n</th>");
	}

	function ul($data) {
		return('<ul>' . $data . '</ul>');
	}
	
	function ol($data) {
		return('<ol>' . $data . '</ol>');
	}
	
	function li($data) {
		if($data) {
			return('<li>' . $data . '</li>');
		} else {
			return '';
		}
	}

	function html_dl($s) {
		return('<dl>'.$s.'</dl>');
	}

	function dt($s) {
		return('<dt>'.$s.'</dt>');
	}

	function dd($s) {
		return('<dd>'.$s.'</dd>');
	}

	function comment($str) {
		return("<!-- $str -->\n");
	}

	function tableheaders() {
		$args = func_get_args();
		foreach($args as $heading) {
			$o .= th($heading);
		}
		return $o;
	}


	function row2($c1, $c2) {
		return tr(td($c1).td($c2));
	}
	
	function row1($c1) {
		return tr(td($c1));
	}
	
	function hrow2($c1, $c2) {
		return tr(th($c1, 'left').td($c2));
	}

	function select($name, $options = array(), $default = NULL) {
		$s = array();
		foreach($options as $value => $option) {
			if(!$option) $option = $value;
			if($option == $default) {
				$s[] = "<option value='$value' selected='selected'>$option</option>";
			} else {
				$s[] = "<option value='$value'>$option</option>";
			}
		}
		return "<select name='$name'>".join('', $s)."</select>";
	}
	
	function submit($value = 'Submit', $name = NULL) {
		return("<input type='submit' value='$value' ".(
			is_null($name) ? "" : "name='$name'"
		)." />");
	}
?>
