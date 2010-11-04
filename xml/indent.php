<?php
	require_once(dirname(__FILE__)."/../ob_capture.php");

	$xml_indent_depth = array(); 
                                                                        
	function xml_indent_startElement($parser, $name, $attrs) {   
		global $xml_indent_depth; 
		print(str_repeat("\t", $xml_indent_depth[$parser]) . "<$name");
		foreach($attrs as $attr => $val) {
			print(" $attr='$val'");
		}
		print(">");
		$xml_indent_depth[$parser]++;
	} 
                                                                         
	function xml_indent_endElement($parser, $name) {
		global $xml_indent_depth;
		$xml_indent_depth[$parser]--;
		print(str_repeat("\t", $xml_indent_depth[$parser])."</$name>");
	}
	
	function xml_indent_cData($parser, $cdata) {
		global $xml_indent_depth;
		print(str_repeat("\t", $xml_indent_depth[$parser]).$cdata);
	}

	function xml_indent($s) {
		$xml_parser = xml_parser_create();
		xml_set_element_handler($xml_parser, "xml_indent_startElement", "xml_indent_endElement");

		ob_start('ob_capture');

		if (!xml_parse($xml_parser, $s, TRUE)) { 
			die(sprintf("XML error: %s at line %d",                            
				xml_error_string(xml_get_error_code($xml_parser)),    
				xml_get_current_line_number($xml_parser)));       
		}

		ob_end_flush();
		$o .= ob_capture_getcap();

		xml_parser_free($xml_parser);
		return $o;
	}

?>
