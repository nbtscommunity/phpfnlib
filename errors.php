<?php

	define('E_SUCCESS', 200);
	define('E_NOTFOUND', 404);
	define('E_FORBIDDEN', 403);
	define('E_SERVFAIL', 500);
	define('E_NOTIMPLEMENTED', 510);
	define('E_FATAL', 599);

	$ERRORS_STACK = array();

	function error($message, $CODE = E_UNKNOWN) {
		global $ERRORS_STACK;
		array_push($ERRORS_STACK, $message);
		return $CODE;
	}

	function errmsg($err = NULL) {
		global $ERRORS_STACK;
		static $ERRORS = array(
			E_NOTIMPLEMENTED => 'Function not implemented',
			E_NOTFOUND => 'Not found', 
			E_SUCCESS => 'Success', 
			E_FORBIDDEN => 'Access denied',
			E_SERVFAIL => 'Server Failure'
		);
		return ($err?$ERRORS[$err]:'').array_pop($ERRORS_STACK);;
	}

	function succeeds($err) {
		return defuzz_error($err, 200, 299);
	}

	function fatal($err) {
		return defuzz_error($err, 500, 599);
	}

	function defuzz_error($err, $low, $high) {
		if(is_int($err)) {
			if($err >= $low and $err <= $high) {
				return TRUE;
			} else {
				return FALSE;
			}
		} else {
			return $err;
		}
	}

?>
