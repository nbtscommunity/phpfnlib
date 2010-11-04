<?php
	require("xml.php");

	$data = "<html><head><title>Hello!</title></head><body bgcolor='foo'><p> This is a paragraph </p><p> this is another</p><ul><li>Test!</li></ul></body></html>";
//	$data = "<html><head><title>Hello!</title></head></html>";
	

	$parser = new XMLObjectParser();
	$parser->parse($data);
	print_r($parser->tree);

?>
