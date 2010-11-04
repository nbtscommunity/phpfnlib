<?php

	require_once(dirname(__FILE__)."/diff.php");
	require_once(dirname(__FILE__)."/../xml/xml.php");
	
	function xhtml_diff_highlight($a, $b, $bail = 0) {
		$parser = new XMLObjectParser();
		$parser->parse("<difference set='a'>$a</difference>");
		$atree = $parser->tree;
		while(is_array($atree[0]) and count($atree[0]) == 0) {
			array_shift($atree);
		}
		//$atree = $atree[0][0]->children;
		//print("Tree A: "); var_dump($atree);

		$parser = new XMLObjectParser();
		$parser->parse("<difference set='b'>$b</difference>");
		$btree = $parser->tree;
		while(is_array($btree[0]) and count($btree[0]) == 0) {
			array_shift($btree);
		}
		//$btree = $btree[0][0]->children;
		//print("Tree B: "); var_dump($btree);

		while($b = array_shift($btree)) {
			$a = array_shift($atree);
			print_r($a);
			print_r($b);
		//	if(!$a) $a = array();
		//	if(!$b) $b = array();
			$a = xhtml_child_split($a);
			while(!$a[0]) array_shift($a);
			$b = xhtml_child_split($b);
			while(!$b[0]) array_shift($b);
			$o .= xml_diff_tree($a, $b, $bail);
		}

		return $o;

	}

	function xml_diff_tree($atree, $btree, $bail) {

		if(!$diff = diff_highlight($atree, $btree, NULL, "xhtml_compare_tag", $bail)) {
			return "<del>$a</del><ins>$b</ins>";
		}

//		diff_highlight_dump($diff);
		
		$s = array();
		while($item = array_shift($diff)) {
			if($item[0] == '-') {
				if(is_object($item[1]) and is_object($item[2])) {
					// Do word-by-word compare here
					$t = xhtml_diff_highlight($item[1]->children_str(), $item[2]->children_str(), 0.2);
					$s[] = "<{$item[2]->tag}>$t</{$item[2]->tag}>";
					// Shift to eat up the add, since we show both here.
					array_shift($diff);
				} elseif(is_object($item[1])) {
					$s[] = '<del>'.$item[1]->strval().'</del>';
				} else {
					$s[] = '<del>'.$item[1].'</del>';
				}
			} elseif($item[0] == '+') {
				if(is_object($item[1]) and is_object($item[2])) {
					// Do word-by-word compare here
					$t = xhtml_diff_highlight($item[1]->children_str(), $item[2]->children_str(), 0.2);
					$s[] = "<{$item[2]->tag}>$t</{$item[2]->tag}>";
					// Don't shift here, since a: we shouldn't get here and
					// b: adds come after discards.
				} elseif(is_object($item[2])) {
					$s[] = '<ins>'.$item[2]->strval().'</ins>';
				} else {
					$s[] = '<ins>'.$item[2].'</ins>';
				}
			} else {
				if(is_object($item[1])) {
					$s[] = $item[1]->strval();
				} else {
					$s[] = $item[1];
				}
			}
		}
		return join(' ', $s);
	//	return table(tr(td($a).td($b)));
	}

	function xhtml_child_split($atree) {
		$temp = array();
		foreach($atree as $key => $value) {
			if(is_string($value)) {
				array_splice($temp, count($temp), 0, preg_split('/\s+/', $value));
			} else {
				$temp[] = $value;
			}
		}
		return $temp;
	}

	function xhtml_compare_tag($a, $b) {
		if(!is_object($a) or !is_object($b)) {
			return $a == $b;
		}
		if($a->tagclass() != $b->tagclass()) return FALSE;
		if($a->tagclass() == 'paragraph') {
			// compare text of contents	
			$atext = preg_split('/\s+/', strip_tags(join(' ', $a->children_str())));
			$btext = preg_split('/\s+/', strip_tags(join(' ', $b->children_str())));
			return $atext == $btext;
			/*
			return (count(LCS($newa, $newb)) / count($newa));
			*/
		} elseif($a->tagclass() == 'list') {
			// compare subtree
			return $a->children == $b->children;
			/*
			return (count(LCS($a->children, $b->children, NULL, 'xhtml_compare_tag')) 
				/ count($a->children));
			*/
		} else {
			 return $a == $b;
		}
	}

?>
