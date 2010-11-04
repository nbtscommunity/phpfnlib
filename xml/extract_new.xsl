<?xml version="1.0" encoding="utf-8" ?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns=''>
<xsl:output method="xml"
    doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN"
    doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"/>
	<xsl:template match='/'><html><head><title><xsl:apply-templates select='/html/head/title/text()'/></title></head><body><xsl:apply-templates select='/html/body/*//ins'/></body></html></xsl:template>
<xsl:template match='ins'><xsl:apply-templates select='node()'/></xsl:template>
<xsl:template match="@*[@class='wikiwordunknown']|node()[name() != 'ins']"><xsl:copy><xsl:apply-templates select='node()|@*[@class="wikiwordunknown"]'/></xsl:copy></xsl:template>
</xsl:stylesheet>
