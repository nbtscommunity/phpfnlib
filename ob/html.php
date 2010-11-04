<?php
	require_once(dirname(__FILE__)."/encoding.php");

	ob_start('ob_htmlwrapper');

	function ob_htmlwrapper($s) {
		global $TITLE;
		return "

<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">
<html>
	<head>
		<title>$TITLE</title>
	</head>
	<body>
	$s
	</body>
</html>";
	}
?>
