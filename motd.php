<?php
	
	if(!defined('WIKI_WIKIWORDS')) define('WIKI_WIKIWORDS', FALSE);
	
	require_once(dirname(__FILE__)."/html.php");
	require_once(dirname(__FILE__)."/wiki/render.php");

	if(!defined('MOTD_FILE')) define('MOTD_FILE', '/etc/motd');

	function motd() {
		$motd = join('', file(MOTD_FILE));
		$stat = stat(MOTD_FILE);
		$mtime = $stat['mtime'];
		if($mtime > time() - (60 * 60 * 24 * 7) and trim($motd)) {
			return heading(2, "Message of the Day").
				"<blockquote>".wiki_render(
					date('M d, Y @ H:i', $mtime)."\n\n".
					$motd)."</blockquote>";
		}
	}

?>
