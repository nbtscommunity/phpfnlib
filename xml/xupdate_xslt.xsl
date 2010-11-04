<?xml version="1.0"?>
<xsl:stylesheet 
		version="1.0"
		xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
		xmlns:xupdate="http://www.xmldb.org/xupdate"
		xmlns:g-xsl="http://www.w3.org/1999/XSL/TransformAlias"
		>

<xsl:namespace-alias stylesheet-prefix="g-xsl" result-prefix="xsl"/>

<!-- Root Template -->
<xsl:template match="/"><xsl:apply-templates/></xsl:template>
<xsl:template match="/xupdate:modifications">
	<xsl:comment>Generated XSLT by xupdate2xslt</xsl:comment>
	<g-xsl:stylesheet 
 		xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
		version="1.0">
		<xsl:for-each select="namespace::*">
			<!--<xsl:attribute name="{name()}" namespace="http://www.w3.org/XML/1998/namespace"><xsl:value-of select="."/></xsl:attribute>-->
			<xsl:copy/>
		</xsl:for-each>
		<g-xsl:template match="@*|node()">
			<g-xsl:copy>
				<g-xsl:apply-templates select="@*|node()"/>
			</g-xsl:copy>
		</g-xsl:template>
		<xsl:apply-templates/>
	</g-xsl:stylesheet>
</xsl:template>

<!-- identity transform -->
<xsl:template match="@*|node()">
	<xsl:copy>
		<xsl:apply-templates select="@*|node()"/>
	</xsl:copy>
</xsl:template>

<!--<xsl:template match="@*" mode="ns"><xsl:attribute name="{name()}"><xsl:value-of select="."/></xsl:attribute></xsl:template>-->

<!-- Append as child -->
<xsl:template match="xupdate:append">
	<g-xsl:template>
		<xsl:attribute name="match"><xsl:value-of select="@select"/></xsl:attribute>
		<g-xsl:variable name='pos'><xsl:attribute name='select'><xsl:choose><xsl:when test='@child'><xsl:value-of select='@child'/></xsl:when><xsl:otherwise>last()</xsl:otherwise></xsl:choose></xsl:attribute></g-xsl:variable>
		<g-xsl:copy>
			<g-xsl:apply-templates select="@*"/>
			<g-xsl:apply-templates select="node()[position() &lt;= ($pos + 1)]"/>
			<xsl:apply-templates/>	
			<g-xsl:apply-templates select="node()[position() &gt; ($pos + 1)]"/>
		</g-xsl:copy>
	</g-xsl:template>
</xsl:template>

<xsl:template match="xupdate:insert-before">
	<g-xsl:template>
		<xsl:attribute name="match"><xsl:value-of select="@select"/></xsl:attribute>
		<g-xsl:copy>
			<g-xsl:apply-templates select="@*"/>
			<xsl:apply-templates/>
			<g-xsl:apply-templates select="node()"/>
		</g-xsl:copy>	
	</g-xsl:template>
</xsl:template>
<xsl:template match="xupdate:insert-after">
	<g-xsl:template>
		<xsl:attribute name="match"><xsl:value-of select="@select"/></xsl:attribute>
		<g-xsl:copy>
			<g-xsl:apply-templates select="@*"/>
			<g-xsl:apply-templates select="node()"/>
			<xsl:apply-templates/>
		</g-xsl:copy>	
	</g-xsl:template>
</xsl:template>

<xsl:template match="xupdate:delete">
	<g-xsl:template>
		<xsl:attribute name="match"><xsl:value-of select="@select"/></xsl:attribute>
	</g-xsl:template>
</xsl:template>

<xsl:template match="xupdate:update">
	<g-xsl:template>
		<xsl:attribute name="match"><xsl:value-of select="@select"/></xsl:attribute>
		<g-xsl:copy>
			<g-xsl:apply-templates select='@*'/>
			<xsl:apply-templates/>
		</g-xsl:copy>
	</g-xsl:template>
</xsl:template>

<xsl:template match="xupdate:replace">
	<g-xsl:template>
		<xsl:attribute name="match"><xsl:value-of select="@select"/></xsl:attribute>
		<xsl:apply-templates/>
	</g-xsl:template>
</xsl:template>

<xsl:template match="xupdate:variable">
	<g-xsl:variable name="{@name}">
		<xsl:if test="@select"><xsl:attribute name="select"><xsl:value-of select="@select"/></xsl:attribute></xsl:if>
		<xsl:apply-templates/>
	</g-xsl:variable>
</xsl:template>

<xsl:template match="xupdate:if">
	<g-xsl:if>
		<xsl:if test='@test'><xsl:attribute name="test"><xsl:value-of select='@test'/></xsl:attribute></xsl:if>
		<xsl:apply-templates/>
	</g-xsl:if>
</xsl:template>

<xsl:template match="xupdate:value-of">
	<g-xsl:value-of select="{@select}"/>
</xsl:template>

<xsl:template match="xupdate:copy-of">
	<g-xsl:copy-of select="{@select}"/>
</xsl:template>

<xsl:template match="xupdate:element">
	<g-xsl:element name="{@name}"><xsl:apply-templates select='@*|node()'/></g-xsl:element>
</xsl:template>

<xsl:template match="xupdate:attribute">
	<g-xsl:attribute name="{@name}"><xsl:apply-templates select='@*|node()'/></g-xsl:attribute>
</xsl:template>

<xsl:template match="xupdate:text">
	<g-xsl:text name="{@name}"><xsl:apply-templates select='@*|node()'/></g-xsl:text>
</xsl:template>

<xsl:template match="xupdate:comment">
	<g-xsl:comment><xsl:apply-templates select='@*|node()'/></g-xsl:comment>
</xsl:template>

<xsl:template match="xupdate:processing-instruction">
	<g-xsl:processing-instruction name="{@name}"><xsl:apply-templates select='@*|node()'/></g-xsl:processing-instruction>
</xsl:template>

</xsl:stylesheet>
