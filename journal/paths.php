<?php

	require_once(dirname(__FILE__)."/../login/login.php");

	unset($date);
	unset($time);
	unset($mode);

	if($PATH_INFO and $PATH_INFO != '/') {
		$paths = explode('/', $PATH_INFO);
	//	array_shift($paths);
		if(preg_match('!\d{4}-\d{2}-\d{2}!', $paths[1], $matches)) {
			$date = $matches[0];
			if(preg_match('!\d{2}:\d{2}(:\d{2})?!', $paths[2], $matches)) {
				$time = $matches[0];
			}
			if($paths[3] == 'Comment') {
				$mode = 'post';
			} elseif(!$paths[3]) {
				$mode = 'read';
			}
		} elseif($paths[1] == 'Current') {
			if($paths[2] == 'Friends') {
				$mode = 'friends';
			} elseif($paths[2] == 'LiveJournal' and
				defined("JOURNAL_LIVEJOURNAL")) {
				$mode = 'friends';
				$friends = array(array('uri' => JOURNAL_LIVEJOURNAL, 'friend' => 'My LiveJournal'));
			} elseif($paths[2] == '') {
				$mode = 'read';
			}
		} elseif($paths[1] == 'Update') {
			$mode = 'update';
			if($paths[2]) {
				$template = $paths[2];
			} else {
				$template = 'default';
			}
		} elseif($paths[1] == 'Manage') {
			$mode = 'manage';
			if($paths[2] =='Friends') {
				if($paths[3] == 'Add') {
					$mode = 'addfriend';
				} elseif($paths[3] == 'Cache') {
					$mode = 'cache_friends';
				}
			} elseif($paths[2] == 'Sidebar') {
				if($paths[3] == 'Add') {
					$mode = 'addsidebar';
				} elseif($paths[3] == 'Edit') {
					$mode = 'editsidebar';
					$sidebar = $paths[4];
				}
			}
		} elseif($paths[1] == 'Sidebars' and $paths[2]) {
			$mode = 'sidebar';
			$sidebar = $paths[2];
		} elseif($paths[1] == 'Login') {
			require("$JOURNAL_DIR/../login/index.php");
			exit();
		} elseif($paths[1] == 'Auto') {
			$mode = 'auto';
			require("$JOURNAL_DIR/auto.php");
		} 
			
		if(!$mode) {
			// $mode = 'read';
			$mode = 'error';
			http_404();
			print('Not found.. sorry.');
			exit();
		}
	} else {
		$mode = 'read';
	}

?>
