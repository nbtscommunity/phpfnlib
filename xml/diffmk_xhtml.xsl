<?xml version="1.0"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	version="1.0">
	<xsl:output method="xhtml" 
		doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN"
		doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"/>
	<xsl:template match="*[@class]">
		<xsl:choose>
			<xsl:when test="@class = 'add'">
				<ins>
					<xsl:copy>
						<xsl:apply-templates select='node()|@*'/>
					</xsl:copy>
				</ins>
			</xsl:when>
			<xsl:when test="@class = 'del'">
				<del>
					<xsl:copy>
						<xsl:apply-templates select='node()|@*' />
					</xsl:copy>
				</del>
			</xsl:when>
			<xsl:otherwise>
				<xsl:copy>
					<xsl:apply-templates select='node()|@*'/>
				</xsl:copy>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
	<xsl:template match='node()|@*'>
		<xsl:copy>
			<xsl:apply-templates select='node()|@*' />
		</xsl:copy>
	</xsl:template>
</xsl:stylesheet>
