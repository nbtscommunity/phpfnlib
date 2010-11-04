<?php

	if(!defined("XSL_PROC_MAILTO")) define("XSL_PROC_MAILTO", false);

	if(!defined("XSL_DEFAULT_STYLESHEET")) {
		define("XSL_DEFAULT_STYLESHEET", 
'<?xml version="1.0"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:output method="xhtml" 
	doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN"
	doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"/>
	<xsl:template match="comment()|node()|@*"><xsl:copy><xsl:apply-templates/></xsl:copy></xsl:template>
</xsl:stylesheet>');
	}
	
	if(!defined("XSL_APPLYCSS_STYLESHEET")) {
		define("XSL_APPLYCSS_STYLESHEET", 
'<?xml version="1.0"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:param name="cssfile"/>
<xsl:output method="xhtml" 
	doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN"
	doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"/>
	<xsl:template match="/">
		<xsl:copy>
			<xsl:apply-templates select="node()|@*|comment()"/>
		</xsl:copy>
	</xsl:template>
	<xsl:template match="head">
		<xsl:copy>
			<xsl:element name="link">
				<xsl:attribute name="href"><xsl:value-of select="$cssfile"/></xsl:attribute>
				<xsl:attribute name="type">text/css</xsl:attribute>
				<xsl:attribute name="rel">StyleSheet</xsl:attribute>
			</xsl:element>
			<xsl:apply-templates select="comment()|node()|@*"/>
		</xsl:copy>
	</xsl:template>
	<xsl:template match="comment()|node()|@*"><xsl:copy><xsl:apply-templates select="comment()|node()|@*"/></xsl:copy></xsl:template>
</xsl:stylesheet>');
	}

	function xml_apply_stylesheet($s) {
		$debugging = false;
		$message = '';
		if(preg_match_all("/<\?xml-stylesheet[^>]+?\?>/ms", $s, $matches, PREG_SET_ORDER)) {
			foreach($matches as $m) {
				$pi = $m[0];
				if(preg_match_all('/\\s+(([a-zA-Z]+)=([\'"])(.*?)\\3)'.
					'/', $pi, $matches2, PREG_SET_ORDER)) {
					$attrs = array();
//					$message .= "<pre>".array_dump($matches)."</pre>";
					foreach($matches2 as $a) {
						$attrs[$a[2]] = $a[4];
						//$s .= "<pre>".$a[2]."=".$a[4]."</pre>";
					}
					if(!$attrs['href']) continue;
					if(!$attrs['type']) $attrs['type'] = 'text/xsl';
					if($attrs['type'] == 'text/css') {
						$stylesheets[] = array(
							'href' => 'arg:/xsl',
							'xsl' => XSL_APPLYCSS_STYLESHEET,
							'type' => $attrs['type'],
							'params' => array(
								'cssfile' => $attrs['href']));
					} elseif($attrs['type'] == 'debug') {
						$debugging = TRUE;
						break;
					} else {
						$stylesheets[] = array(
							'href' => $attrs['href'],
							'type' => $attrs['type']);
					}
				} 
			} 
			/* $pi = preg_replace("/<\?xml-stylesheet (.*)\?>/", '\1', $pi);
			$stylesheet = preg_replace("/href=(.)(.*)\\1/", '\2', $pi); */
		} else {
			return $s;
		}

		
		$s = preg_replace("/<\?xml-stylesheet[^>]+?\?>/ms", '', $s);

		if($debugging) return '<pre>'.htmlspecialchars($s).'</pre>';
		
		if(count($stylesheets) == 0) 
			$stylesheets[] = array(
				'href' => 'arg:/xsl',
				'type' => 'text/xsl',
				'xsl' => XSL_DEFAULT_STYLESHEET);

		$input = $s;
//		$message .= 'md5-'.md5($input)."<br />";
		foreach($stylesheets as $stylesheet) {
			$xsl = new DomDocument;
			$xsl->load($stylesheet['href']);
			$xml = new DomDocument;
			@$xml->loadXML($input);
			if(!isset($stylesheet['params'])) $stylesheet['params'] = array();
			$proc =  new XSLTProcessor;
			$proc->importStyleSheet($xsl);
			foreach($stylesheet['params'] as $param => $val) {
				$proc->setparameter('', $param, $val);
			}

			if(!$result = $proc->transformToXML($xml)) {
				$message .= "Error applying ${stylesheet['href']}: ".
					/*xslt_error($xslt_engine)."\n".*/'';
				$message .= "Failed on:<pre>".wordwrap(htmlspecialchars($input))."</pre>";
					$f = fopen("/tmp/dead.xml", "w");
					fputs($f, $input);
					fclose($f);
					system("xmllint /tmp/dead.xml > /tmp/xmllint.log 2>&1");
				if(XSL_PROC_MAILTO) {
					foreach($_SERVER as $k => $v) {
						$mailmessage .= "$k = $v\n";
					}
					$mailmessage .= $message;
					$message = 'Stylesheet failed';
					$result = $input;
					mail(XSL_PROC_MAILTO, 
						'XML Stylesheet Failed ('.$_SERVER['REQUEST_URI'].')',
						html_entity_decode($mailmessage));
				}
			} else {
				$message .= "<!-- applied ${stylesheet['href']} -->\n";
//				$message .= 'md5-'.md5($input)."\n";
				$input = $result;
			}
			#xslt_free($xslt_engine);
		}
		return $result.$message;
	}

	function xml_apply_external_stylesheet($xml, $style) {
		$xslt_engine = new XSLTProcessor();
		$xslt_engine->importStyleSheet(DomDocument::loadXML($style));
		if($xmlo = DomDocument::loadXML($xml)) {
			if(!$result = $xslt_engine->transformToXML($xmlo)) {
			//	$result = "<!-- Error applying stylesheet:
			//	".xslt_error($xslt_engine)." -->".$xml;
			}
		} else {
			return false;
		}
		return $result;
	}

	function array_dump($a) {
		$s .= "Array {\n";
		foreach($a as $k => $e) {
			$s .= "[$k] => ";
			if(is_array($e)) {
				$s .= array_dump($e);
			} else {
				$s .= $e;
			}
			$s .= "\n";
		}
		$s .= "}\n";
		return $s;
	}

?>
