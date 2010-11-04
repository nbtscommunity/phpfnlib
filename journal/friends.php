<?php
	
	if(defined("JOURNAL_FRIENDSENTRIES")) {
		$limit = JOURNAL_FRIENDSENTRIES;
	} else {
		$limit = 25;
	}

	function get_cached_friends($username, $limit = 0) {
		if(!$limit) $limit = JOURNAL_FRIENDSENTRIES;
		$r = mysql_fetch_all(mysql_query(
			"SELECT * FROM cache 
				INNER JOIN friends ON (friends.uri = cache.friend_uri)
				WHERE friends.username = '$username' 
				ORDER BY cache.date DESC 
				LIMIT $limit"));
		foreach($r as $key => $row) {
			$r[$key]['data'] = $row['body'];
			if(ereg('opendiary.com', $r[$key]['friend_uri'])) {
				$r[$key]['friend'] .= '(OD)';
			} elseif(ereg('diaryland.com', $r[$key]['friend_uri'])) {
				$r[$key]['friend'] .= '(DL)';
			} elseif(ereg('livejournal.com', $r[$key]['friend_uri'])) {
				$r[$key]['friend'] .= '(LJ)';
			}
			unset($r[$key]['body']);
		}
		return $r;
	}

	$friendsentries = get_cached_friends(JOURNAL_USERNAME, $limit);

	if(count($friendsentries) > 0 and is_array($friendsentries)) {
		print(format_rows($friendsentries, $limit));
	} else {
		print("No friend entries, sadly");
	}
?>
