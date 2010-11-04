<?php
	ob_end_clean();

	if(!$action) $action = 'latest';

	//$XMLPREFIX = 'journal:';

	if($action == 'latest') {
		//header("Content-type: application/x-php-serialized");
		header("Content-type: text/plain");
		if(!$limit) { $limit = 6; }
		$q = "SELECT date, subject, data, contenttype FROM journal WHERE username = '".JOURNAL_USERNAME."' ORDER BY date DESC LIMIT $limit";
		$r = mysql_query($q);

		if($r) {
			$r = mysql_fetch_all($r);
			$o = array();
			foreach($r as $row) {
				$o[] = xmlize_entry($row);
			}
			print("<${XMLPREFIX}journal>\n".join("\n",$o)."</${XMLPREFIX}journal>\n");
		}
	}

	exit();

	function xmlize_entry($e) {
		global $XMLPREFIX;
		extract($e);
		return("<${XMLPREFIX}entry time='$date'>\n".
			xml_subject($subject).
			"\t<${XMLPREFIX}content type='$contenttype'>".base64_encode($data)."</${XMLPREFIX}content>\n".
			"</${XMLPREFIX}entry>\n");
	}

	function xml_subject($s) {
		global $XMLPREFIX;
		if($s) return "\t<${XMLPREFIX}subject>".base64_encode($s)."</${XMLPREFIX}subject>\n";
	}
	
?>
