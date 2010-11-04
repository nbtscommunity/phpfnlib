<?php
	if(!function_exists('var_export')) {
		function var_export($var, $return = TRUE) {
			global $___phpstdlib_capture;
			if($return) {
				ob_start('___phpstdlib_capture');
			}
			var_dump($var);
			if($return) {
				ob_end_flush();
				$retval = $___phpstdlib_capture;
				unset($___phpstdlib_capture);
				return $retval;
			}
		}

		function ___phpstdlib_capture($s) {
			global $___phpstdlib_capture;
			$___phpstdlib_capture = $s;
		}
	}
?>
