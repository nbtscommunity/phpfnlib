<?php
	function ob_exit() {
		while(ob_get_level()) ob_end_clean();
		exit();
	}
?>
