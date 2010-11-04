<?php
	class EventHandler {
		var $logprefix; // Log prefix string
		var $logfile; // Log file fd
		function event($name) {
			$data = func_get_args();
			array_shift($data);
			$name = 'e_'.$name;
			$this->log("Calling event handler $name");
			if(method_exists($this, $name)) {
				return call_user_func_array(array(&$this, $name), $data);
			} else {
				$this->log("No event handler for $name");
			}
		}
		
		function openlog($logfile = '/tmp/log') {
			$this->logfile = fopen($logfile, 'a');
		}
		
		function log($m) {
			if(!$this->logfile) $this->openlog();
			$m = str_replace("\n", "\n".$this->logprefix, $m);
			return fwrite($this->logfile, $this->logprefix.$m."\n");
		}

	}
?>
