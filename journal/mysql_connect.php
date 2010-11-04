<?php
	if(!$journal_db) {
		$journal_db = mysql_connect('localhost', 'ljk', 'ljk');
		mysql_select_db('ljk', $journal_db);
	}
?>
