<?php

	require_once(dirname(__FILE__)."/xml.php");

	class block_add_inline_tag_parser extends xmlparser {
		var $btags = array('p', 'div', 'ol', 'ul', 'li', 'dl', 'dd', 'dt', 
			'table', 'tr', 'td', 'th');
		var $stack = array();
		var $tstack = array();
		function block_add_inline_tag_parser($tag) {
			xmlparser::xmlparser();
			$this->tag = $tag;
		}

		function startElementHandler($parser, $name, $attrs) {
			while($e = array_pop($this->tstack)) {
				array_push($this->stack, array($e[1], $e[2], 'end'));
			}
			array_push($this->stack, array($name, $attrs, 'start'));
			if(in_array($this->btags, $name)) {
				array_push($this->stack, array($this->tag, NULL, 'start'));
				array_push($this->tstack, array($this->tag, NULL));
			}
		}

		function endElementHandler($name) {
			if(in_array($this->btags, $name)) {
				while($e = array_pop($this->tstack)) {
					array_push($this->stack, array($e[1], $e[2], 'end'));
				}
			}
			array_push($this->stack, array($name, $attrs, 'end'));
		}

		function cdataHandler($cdata) {
			array_push($this->stack, $cdata);
		}

		function html() {
			foreach($this->stack as $e) {
				if(is_array($e)) {
					if($e[3] == 'start') {
						$o .= "<".$e[1].($e[2]?" ".xml_attrs_to_string($e[2]):"").">";
					} elseif($e[3] == 'end') {
						$o .= "</".$e[1].">";
					}
				} else {
					$o .= $e;
				}
			}
			return $o;
		}
	}

	function block_add_inline_tag($s, $t) {
		$parser = new  block_add_inline_tag_parser($t);
		$parser->parse($s);
		return($parser->html());
	}
?>
