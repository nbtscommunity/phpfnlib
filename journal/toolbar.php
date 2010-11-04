<?php
	$toolbar = array();
	if($prevdate) {
		$toolbar[] = "<a href='$SCRIPT_URI/$prevdate'>Back to ".
			str_replace('-', '/', $prevdate)."</a>";
	}

	if(JOURNAL_DISPLAYMODE != 'oneentry') {
		$toolbar[] = "<a href='$SCRIPT_URI/Current'>Journal</a>"; 
		$toolbar[] = "<a href='$SCRIPT_URI/Current/Friends'>Friends</a>";
		if(defined("JOURNAL_LIVEJOURNAL")) {
			$toolbar[] = "<a href='$SCRIPT_URI/Current/LiveJournal'>My LiveJournal</a>";
		}
	}

	if(!is_logged_in()) {
		$toolbar[] = "<a href='$SCRIPT_URI/Login'>Log In</a>";
	} else {
		if(authorized(login_get_username(), 'updatejournal', AUTH_PROBE)) {
			$toolbar[] = "<a href='$SCRIPT_URI/Update'>Update Journal</a>";
			$toolbar[] = "<a href='$SCRIPT_URI/Manage'>Manage Journal</a>";
		} 
	}

	if(JOURNAL_DISPLAYMODE != 'oneentry') {
		if($nextdate) {
				$toolbar[] = "<a href='$SCRIPT_URI/$nextdate'>Next is ".
				str_replace('-', '/', $nextdate)."</a>";
		}
	}

//	print("\nUN={".login_get_username()."}\n");
//  print_r($_SESSION);
//	print($LOGIN_USERNAME);

	if(count($toolbar) > 0) {
		$toolbar = join(' | ', $toolbar);
		print("<div class='toolbar'><hr />$toolbar</div>");
	}
?>
