<?php

	function ob_start_first($handler) {
		$handlers = ob_list_handlers();
		$contents = Array();
		foreach($handlers as $k => $h) {
			$contents[$k] = ob_get_contents();
			ob_end_clean();
		}
		ob_start($handler);
		foreach($handlers as $k => $h) {
			ob_start($h);
			print($contents[$k]);
		}
	}
?>
			
