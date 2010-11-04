<?php

	require_once(dirname(__FILE__)."/../phpstdlib.php");

	if(!defined('XML_NO_SHORTTAG')) {
		define('XML_NO_SHORTTAG',  TRUE);
	}

	function xml_attrs_to_string($a) {
		if(!is_array($a)) return '';
		foreach($a as $k => $v) {
			$o .= " $k='$v'";
		}
		return $o;
	}

	function xml_struct_val_to_tag($a) {
		extract($a);
		if($tag == 'br'
			or (!XML_NO_SHORTAG and $type == 'complete' 
				and $value == '')) {
			return("<$tag".xml_attrs_to_string($attrs)." />");
		} else {
			if($type == 'complete') {
				return("<$tag".xml_attrs_to_string($attributes).">".$value."</$tag>");
			} elseif($type == 'open') {
				return("<$tag".xml_attrs_to_string($attributes).">");
			} elseif($type == 'close') {
				return("</$tag>");
			} else {
				return ''; //ERROR
			}
		}
	}

	function domxml_attrs($a) {
		if(is_array($a)) {
			foreach($a as $o) {
				$out .= " " .$o->name . "='" . $o->children[0]->content . "'";
			}
			return $out;
		} else {
			return '';
		}
	}

	function domxml_flatten_tree($o) {
		switch($o->type) {
			case XML_TEXT_NODE:
				$out .= $o->content;
				break;
			case XML_ELEMENT_NODE:
				if($o->children) {
					$out .= "<".$o->name.domxml_attrs($o->attributes).">";
					foreach($o->children as $child) {
						$out .= domxml_flatten_tree($child);
					}
					$out .= '</'.$o->name.'>';
				} else {
					$out .= "<".$o->name.domxml_attrs($o->attributes)." />";
				}
				break;
			default:
				if($o->children) {
					foreach($o->children as $child) {
						$out .= domxml_flatten_tree($child);
					}
				}
				break;
		}
		return $out;
	}

	class XMLParser {
		var $parser;
		function XMLParser() {
			$this->parser = xml_parser_create();
			xml_parser_set_option(
				$this->parser, XML_OPTION_CASE_FOLDING, FALSE);
			xml_set_element_handler(
				$this->parser, "startElementhandler", "endElementHandler");
			xml_set_character_data_handler($this->parser, "cdataHandler");
			xml_set_processing_instruction_handler(
				$this->parser, "processingInstructionHandler");
			xml_set_default_handler($this->parser, "defaultHandler");

		}

		function parse($data, $final = TRUE) {
			xml_set_object($this->parser, $this);
			return xml_parse($this->parser, $data, $final);
		}

		function startElementHandler($parser, $tag, $attribs) {
		}

		function endElementHandler($parser, $tag) {
		}

		function cdataHandler($parser, $cdata) {
		}

		function processingInstructionHandler($parser, $cdata) {
		}

		function defaultHandler($parser, $element) {
		}
	}

	class XMLObjectParser extends XMLParser {
		var $stack = array();
		var $tree = array(array());
		var $depth = 0;
		function startElementHandler($parser, $tag, $attribs) {
			array_push($this->stack, new XMLTag($tag, $attribs));
			array_push($this->tree, array());
		}

		function endElementHandler($parser, $tag) {
			$tag = array_pop($this->stack);
			$tag->children = array_pop($this->tree);
			array_push($this->tree[count($this->tree) - 1], $tag);
		}

		function cdataHandler($parser, $cdata) {
			if($cdata = trim($cdata)) {
				$temp = array_pop($this->tree[count($this->tree) - 1]);
				if(!$temp or is_string($temp)) {
					array_push($this->tree[count($this->tree) - 1], "$temp $cdata");
				} else {
					array_push($this->tree[count($this->tree) - 1], $temp);
					array_push($this->tree[count($this->tree) - 1], $cdata);
				}
			}
		}
	}

	class XMLTag {
		var $tag = '';
		var $attribs = array();
		var $children = array();
		function XMLTag($tag, $attribs = array(), $children = array()) {
			$this->tag = $tag;
			$this->attribs = $attribs;
			$this->children = $children;
		}
		
		function strval() {
			//return var_export($this);
			return "<$this->tag".xml_attrs_to_string($this->attribs).">".
				$this->children_str()."</$this->tag>"; 
		}

		function children_str() {
			$children = array();
			if(is_array($this->children)) {
				foreach($this->children as $child) {
					if(is_object($child)) {
						$children[] = trim($child->strval());
					} else {
						$children[] = trim($child);
					}
				}
			}
			return join(' ', $children);
		}

		function tagclass() {
			switch($this->tag) {
				case 'p':
				case 'div':
					return 'paragraph';
				case 'ul':
				case 'ol':
					return 'list';
				case 'h1':
				case 'h2':
				case 'h3':
				case 'h4':
				case 'h5':
				case 'h6':
					return 'heading';
				default:
					return 'misc';
			}
		}
	}
?>
