<?php

	require_once(dirname(__FILE__)."/../mysql.php");
	require_once(dirname(__FILE__)."/../http.php");

	while(ob_get_level()) ob_end_clean();

	$db = mysql_connect('localhost', 'nobody', '');
	mysql_select_db('nbtsc', $db);
	if($USER_AGENT) {
		$ua = addslashes($USER_AGENT);
		$q = "SELECT * FROM robots WHERE useragent = '$ua' 
			OR useragent = '*'";
	} else {
		$q = "SELECT * FROM robots ORDER BY useragent";
	}

	header('Content-type: text/plain; charset=UTF-8');

	if($r = mysql_query($q, $db)) {
		if(count($r = mysql_fetch_all($r))) {
			foreach($r as $row) {
				$ua = ($row['useragent'] ? $row['useragent'] : '*');
				if(is_array($robots[$ua])) {
					$robots[$ua][] =
						array('path'=>$row['path'], 'sense' =>$row['sense']);
				} else {
					$robots[$ua] = array(
						array('path'=>$row['path'], 'sense' =>$row['sense']));
				}
			}

			if($USER_AGENT) {
				print("User-Agent: *\n");
				foreach($robots as $ua => $robot) {
					foreach($robot as $rule) {
						print(($rule['sense'] == 'allow' ? 
							'Allow: ' : 'Disallow: ').$rule['path']."\n");
					}
				}
			} else {
				foreach($robots as $useragent => $rules) {
					print("\nUser-Agent: $useragent\n");
					foreach($rules as $rule) {
						print(($rule['sense'] == 'allow' ?
                            'Allow: ' : 'Disallow: ').$rule['path']."\n");
					}
				}
			}
		} else {
			http_404();
			exit();
		}
	} else {
		http_500();
		print("Error: ".mysql_error());
		exit();
	}
	

?>
