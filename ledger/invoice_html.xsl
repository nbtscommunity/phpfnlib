<?xml version="1.0"?>
<xsl:transform xmlns:xsl='http://www.w3.org/1999/XSL/Transform' version='1.0'>
	<xsl:output method='xml' indent='yes' />
	<xsl:template match='/invoice'>
		<html>
			<head>
				<title>Invoice for 
					<xsl:value-of select='customer/name/text()'/>; 
					<xsl:value-of select='date/text()'/>
				</title>
			</head>
			<body>
				<h1>Invoice</h1>
				<xsl:apply-templates select='customer'/>
				<h2>Items</h2>
				<table>
					<tr><th>Quantity</th><th>Description</th><th>Price</th><th>Unit</th><th>Total</th></tr>
					<xsl:apply-templates select='item'/>
					<tr>
						<th colspan='4'>Total</th>
						<td align='right'>
							<xsl:variable name="totals">
								<xsl:for-each select="item">
									<total>
										<xsl:value-of
										select=".//price/text()*.//quantity/text()"/>
									</total>
								</xsl:for-each>
							</xsl:variable>
							<xsl:value-of select="format-number(sum($totals/*), '0.00')"/>
						</td>
					</tr>
				</table>
			</body>
		</html>
	</xsl:template>
	<xsl:template match='customer'>
		<h2>Customer</h2>
		<xsl:apply-templates/>
	</xsl:template>
	<xsl:template match='customer/name' priority='1'>
		<xsl:value-of select='.'/><br/>
	</xsl:template>
	<xsl:template match='customer/address' priority='1'>
		<address>
			<xsl:copy>
				<xsl:apply-templates/>
			</xsl:copy>
		</address>
	</xsl:template>
	<xsl:template match='address/street' priority='1'>
		<xsl:value-of select='.'/><br/>
	</xsl:template>
	<xsl:template match='address/*'>
		<xsl:value-of select='.'/>
	</xsl:template>
	<xsl:template match='customer/*'>
		<xsl:value-of select='local-name(.)'/>:
		<xsl:value-of select='.'/><br/>
	</xsl:template>
	<xsl:template match='invoice/item'>
		<tr>
			<td><xsl:value-of select='quantity'/></td>
			<td>
			<!--	<xsl:if test='count(.//part) = 1'> -->
					<xsl:value-of select='.//part|service/description'/>
			<!--	</xsl:if> -->
			</td>
			<td align='right'><xsl:value-of select='format-number(sum(.//price), "0.00")'/></td>
			<td><xsl:value-of select='.//unit'/></td>
			<td align='right'><xsl:value-of select='format-number(sum(.//price) * quantity, "0.00")'/></td>
		</tr>
	</xsl:template>
	<xsl:template match='*'/>
</xsl:transform>
