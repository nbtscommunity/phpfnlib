<?php

	require_once(dirname(__FILE__)."/../xml/stylesheet.php");

	function html_random_link($html) {
		static $stylesheet = '<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
			<xsl:output method="xml" />
			<xsl:template match="/">
				<xsl:apply-templates select="//a"/>
			</xsl:template>
			<xsl:template match="a">
				<xsl:value-of select="@href"/><xsl:text>
</xsl:text>
			</xsl:template>
		</xsl:stylesheet>';

		$list = explode("\n", xml_apply_external_stylesheet($html, $stylesheet));

		return $list[array_rand($list)];
	}

?>
