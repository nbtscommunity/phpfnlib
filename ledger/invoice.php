<?php
	function invoice_render($invoice) {
		return xml_apply_external_stylesheet($invoice, join('',
		file(dirname(__FILE__)."/invoice_html.xsl")));

	}
?>
