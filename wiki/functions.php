<?php
	
	require_once(dirname(__FILE__)."/../utf8.php");
	
	if(!defined('WIKI_SPLITWIKIWORDS')) {
		define('WIKI_SPLITWIKIWORDS', FALSE);
	}

	if(!defined('WIKI_SPLITWIKITITLES')) {
		define('WIKI_SPLITWIKITITLES', TRUE);
	}

	function wiki_format_wikiword($s, $title=FALSE) {
		if(WIKI_SPLITWIKIWORDS or ($title and WIKI_SPLITWIKITITLES)) {
			return trim(preg_replace('/([A-Z])/', ' \1', $s));
		} else {
			return $s;
		}
	}
	
	function wiki_link($target, $text, $unknown = FALSE, $version = 'Current', $fullurl = false) {
		global $WIKI_PAGEDIR, $WIKI_REPOSITORY, $_SERVER;
		if($fullurl) {
			$prefix = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'].'/';
		}
		$cur = $version == 'Current';
		if($cur) {
			$realversion = key(rcs_get_revisions($WIKI_PAGEDIR.$target));
		} else {
			$realversion = $version;
		}
		return(
			($unknown ? "<span class='wikiwordunknown'>$text" : "").
			'<a href="'.$prefix.pqurlencode($target).
				($realversion?";$realversion":'').
				'"'.($realversion ? 
					' title="'.$text." v$realversion".
						($cur ? ' (Current)':'').'"': '').
				($unknown ? '' : ' class="wikiword"').
				'>'.
				($unknown? '?' : $text).'</a>'.
				($unknown? '</span>' : ''));
	}

	
	function wiki_split_wikiwords($wikiword) {
		if(strlen($wikiword) and $wikiword{0} == '#') return $wikiword;
		if(strtoupper($wikiword) != $wikiword) {
			$swikiword = preg_replace('#(?!^)([A-Z])#', ' \1', $wikiword);
		} else {
			$swikiword = join('. ', preg_split('/(?!^)/', $wikiword));
		}
		return $swikiword;
	}

?>
