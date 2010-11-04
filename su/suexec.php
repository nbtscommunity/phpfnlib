<?php
	require_once(dirname(__FILE__)."/../expect.php");

	function suexec($u, $c, $p) {
		$ds = array(
			0 => array('pipe', "r"),
			1 => array('pipe', 'w'),
			2 => array('pipe', 'w')
		);

		print("running $c\n");

		if($u == posix_getlogin()) {
			return exec('sh -c '.escapeshellarg($c));
		}
			
		if(is_resource($su = proc_open(
			"su $u -c ".escapeshellarg($c), 
			$ds, $pipes))) {

			$r = do_expect(array(
				array('pattern' => 'timeout', 'value' => 3),
				array('pattern' => '/Password:/', 
					'action' => 
						'fwrite($fds[0], "'.$p.'"); '.
						'return TRUE;',
					'fd' => 2)
				),
				$pipes);

			foreach($pipes as $k => $p) {
				fclose($pipes[$k]);
			}
			proc_close($su);

			return $r;
		} else {
			return FALSE;
		}
	}
?>
