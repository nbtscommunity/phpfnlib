<?php
	
	require_once(dirname(__FILE__).'/../debug.php');

	function xml_select($xpath, $file) {
		$xsl = "<?xml version='1.0'?>\n".
			"<xsl:transform version='1.0' ".
				"xmlns:xsl='http://www.w3.org/1999/XSL/Transform'>\n".
				"<xsl:template match='/'>\n".
					"<resultset>\n".
						"<xsl:apply-templates select='$xpath'/>\n".
					"</resultset>\n".
				"</xsl:template>\n".
				"<xsl:template match='node()|@*'>\n".
					"<xsl:copy>\n".
						"<xsl:apply-templates select='node()|@*'/>\n".
					"</xsl:copy>\n".
				"</xsl:template>\n".
			"</xsl:transform>\n";
		$e = xslt_create();
		$o = xslt_process($e, $file, 'arg:/xsl', NULL, array('xsl' => $xsl),
			array('path' => $xpath));
		if(!$o) {
			print_lined($xsl);
		}
		xslt_free($e);
		return $o;
	}
?>
