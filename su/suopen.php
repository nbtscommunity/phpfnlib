<?php
	define("SU_SELF", 0);

	function suopen($file, $mode, $user = SU_SELF, $credentials = '') {
		if($user == SU_SELF) $user = get_current_user();
		if($mode == 'w') {
			$p = popen(sucmd("dd of=".escapeshellarg($file), $user), "w");
			fwrite($p, $credentials);
			return $p;
		} elseif($mode == 'a') {
			$p = popen(sucmd("cat >> ".escapeshellarg($file), $user), "w");
			fwrite($p, $credentials);
			return $p;
		} elseif($mode == 'r') {
			$p = popen(sucmd("cat ".escapeshellarg($file), $user), "r");
			fwrite($p, $credentials);
			return $p;
		}
	}

	function sucmd($command, $user, $suidprog = 'su') {
		if($suidprog == 'su') {
			return "/bin/su $user -c $command";
		} elseif($suidprog == 'sudo') {
			return "sudo -u $user $command";
		} else {
			return $command;
		}
	}

	function suclose($p) {
		pclose($p);
	}

?>
