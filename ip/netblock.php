<?php

	function ip_in_block($ip, $block) {
		$octs = explode('.', $ip);
		$ip = ((int)$octs[0] << 24) + ((int)$octs[1] << 16) + ((int)$octs[2] << 8) + ((int)$octs[3]);
			
		list($block, $mask) = explode('/', $block);
		$mask = (int)$mask;
		$octs = explode('.', $block);
		$block = ((int)$octs[0] << 24) + ((int)$octs[1] << 16) + ((int)$octs[2] << 8) + ((int)$octs[3]);

		$mask = ((0xffffffff << $mask));

		if(($ip & $mask) == ($block & $mask)) {
			return 1;
		} else {
			return 0;
		}
	}

?>
