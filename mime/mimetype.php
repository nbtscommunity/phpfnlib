<?php

	function parse_content_type($type) {
		if(!$type) {
			return array('type' => 'text', 'subtype' => 'plain',
				'Content-type' => 'text/plain');
		}
		if(preg_match('!^(\w+/\w+);\s*(.*)$!', $type, $matches)) {
			$type = $matches[1];
			$params = $matches[2];
			if(preg_match_all('!(\w+)=((").*?\1|\w+)!', $params, $matches)) {
				$params = array();
				foreach($matches as $match) {
					$params[$match[1]] = $match[2];
				}
			}
		}
		list($type,$subtype) = explode('/', $type);
		$out = array(
			'type' => $type, 'subtype' => $subtype, 
			'Content-type' => "$type/$subtype");
		if(is_array($params)) {
			$out['params'] = $params;
		}
		return $out;
	}

?>
