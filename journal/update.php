<?php

	require_once(dirname(__FILE__)."/../timezone/timezone.php");
	require_once(dirname(__FILE__)."/livejournal.php");

	if($REQUEST_METHOD == 'POST' and is_logged_in() and
		authorized($LOGIN_USERNAME, 'updatejournal')) {
		if(!$contenttype) { $contenttype = 'text/wiki'; }
		if($timezone) { 
			setcookie('timezone', $timezone, 782000); 
			$date = timezone_date($timezone, 'Y-m-d H:i:s', $entrydate);
			print($date);
		} else {
			$timezone = 'Universal';
		}
		$q = "INSERT INTO journal (".
				"date, timezone, subject, data, contenttype, username".
			") VALUES (".
				"'$date', '$timezone', ".($subject ? "'$subject'" : "NULL").
				", '$data', '$contenttype', ".
			"'".JOURNAL_USERNAME."');";

		if(mysql_query($q)) {
			if ($livejournal_submit) {
				insert_livejournal(stripslashes($subject), stripslashes($data), $date);
			}
			http_302($PHP_SELF);
		} else {
			print(mysql_error()."(Query = $q)");
		}
	} else {
		if(!authorized($LOGIN_USERNAME, 'updatejournal')) {
			print('Please log in');
		} else {
			if(!$timezone) { $timezone = 'America/Los_Angeles'; }
			print(form($PHP_SELF, 
				table(
					row2("Date:", 
						hidden('entrydate', time()).
						timezone_date($timezone, 'Y-m-d H:i:s T')).
					row2("Subject:", field('subject')).
					row2("Timezone:", 
						select('timezone', timezones_list(), $timezone)).
					row2('', "<textarea name='data' cols='50' rows='10'>".
					($template != 'default' ? join('', file($template)) : '').
						"</textarea>").
					row2("Content-type:", 
						"<input type='radio' name='contenttype' ".
							"value='text/wiki' / checked='checked'>WikiWiki ".
						"<input type='radio' name='contenttype' ".
							"value='text/html' /> HTML").
					((defined('LIVEJOURNAL_USER') and defined('LIVEJOURNAL_PASSWD')) ? row2("Submit to LiveJournal?", checkbox('livejournal_submit', FALSE)) : '').
					row2('', submit('Post')))));
		}
	}
?>
