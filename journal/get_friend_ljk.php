<?php

	$currentelement = array();
								
	function startElement($parser, $name, $attrs) {
		global $currententry, $entries, $currentelement;
		if($name == 'entry') {
			$currententry = array('date' => $attrs['time']);
		} elseif($name == 'content') {
			$currententry['contenttype'] = $attrs['type'];
		}
		$currentelement[] = $name;

	}
											
	function endElement($parser, $name) {
		global $currententry, $entries, $currentelement, $cdatas;
		if($name == 'entry') {
			$entries[] = $currententry;
		} elseif($name == 'subject') {
			$currententry['subject'] = base64_decode($cdatas['subject']);
		} elseif($name == 'content') {
			$currententry['data'] = base64_decode($cdatas['content']);
		}
		unset($cdatas[end($currentelement)]);
		array_pop($currentelement);
	}

	function cdata($parser, $data) {
		global $currententry, $entries, $currentelement, $cdatas;
		$ce = end($currentelement);
		$cdatas[$ce] .= $data;
	}
																			
	function get_friend_ljk($uri) {
		global $entries, $cdatas;
		$entries = array();
		$cdatas = array();
		$x = xml_parser_create();
		if($uri[strlen($uri)] != '/') {
			$nuri = $uri.'/';
		} else { 
			$nuri = $uri; 
		}
		$f = get($nuri."Auto?limit=".JOURNAL_LIMITPERFRIEND."&action=latest");
		xml_parser_set_option($x, XML_OPTION_CASE_FOLDING, FALSE);
		xml_set_element_handler($x, "startElement", "endElement");
		xml_set_character_data_handler($x, "cdata");
		xml_parse($x, $f, TRUE);
		$e = $entries;
		unset($entries);
		unset($cdatas);
		foreach($e as $k => $entry) {
			$e[$k]['friend_uri'] = $uri;
			$e[$k]['uri'] = $uri."/".$e[$k]['date'];
		}
		put_cache($e);
		return($e);
	}
?>
