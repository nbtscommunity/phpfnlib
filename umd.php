<?php
	require_once(dirname(__FILE__)."/su/suexec.php");
	require_once(dirname(__FILE__)."/sockets/tranceiver.php");

	class UMDDaemon extends Tranceiver {
		var $timeout = 120; // Daemon idle timeout
		var $logprefix = '[daemon] ';

		function UMDDaemon($sockaddr) {
			$this->sockaddr = $sockaddr;
			$this->openlog('/tmp/umd'.basename($sockaddr).'.log');
			unlink($this->sockaddr);
			$this->file = __FILE__;
			$listen = socket_create(AF_UNIX, SOCK_STREAM, 0) 
				or die('Cannot create socket');
			socket_bind($listen, $this->sockaddr) 
				or die('Cannot bind');
			socket_listen($listen) 
				or die('Cannot listen');
			$this->register_listener($listen);
			posix_setsid();
		}

		function run() {
			$this->log("Running on ".$this->sockaddr);
			while(true) $this->wait();
		}
		
		function register_socket(&$s) {
			$this->othersockets[(string)$s] &= $s;
		}

		function terminate() {
			exit();
		}

		function initclient(&$s) {
			$this->log("Initializing client on $s");
		}

		function e_socket(&$s) {
			if(!$in = socket_read($s, 1024)) {
				if(socket_last_error() == 0) {
					unset($this->othersockets[(string)$s]);
					return $this->log("Event socket closed");
				} else {
					return $this->log(socket_strerror(socket_last_error()));
				}
			} else {
				return $this->log("Socket event: $in");
			}
		}

		function error(&$client, $e) {
			return $this->log("Error: $e");
		}

		function signal(&$client, $s) {
			return $this->log("Signal: $s");
		}

	}

	class UMDClient extends Tranceiver {
		var $serverclass; // Class of server
		var $sockaddr; // Address of socket
		var $logprefix = '[client] ';

		function UMDClient($sockaddr) {
			$this->openlog('/tmp/umd'.basename($sockaddr).'.log');
			$this->sockaddr = $sockaddr;
			if(!$this->file) $this->file = __FILE__;
			if(!$this->serverclass) $this->serverclass =
				str_replace('client', 'daemon', get_class($this));
			if(!$this->serversocket = socket_create(AF_UNIX, SOCK_STREAM, 0))
				return FALSE;
			$tries = 3;
			while($tries--) {
				if(!@socket_connect($this->serversocket, $this->sockaddr)) {
					print(socket_strerror($e = socket_last_error())."\n");
					switch($e) {
						case 2:
						case 111:
							$this->spawn_server();
							break;
					}
				} else {
					break;
				}
			}
			$this->register_socket($this->serversocket);
		}

		function close() {
			return socket_close($this->serversocket);
		}
		
		function spawn_args() {
			return $this->sockaddr;
		}

		function spawn_server() {
			print('Spawning '.$this->serverclass.' on '.$this->sockaddr."\n");
			$startupcode = '<?php '.
				'require_once("'.$this->file.'");'.
				'$server = new '.$this->serverclass.'("'.addslashes($this->spawn_args()).'");'.
				'$server->run();'.
			'?>';
			$cmd = 'echo '.  escapeshellarg($startupcode).
				' | php -q >>/tmp/umd'.basename($this->sockaddr).'.log 2>&1 &';
			print('running '.$cmd."\n");
			if(!shell_exec($cmd)) {
		//		print("exec failed\n");
			}
			sleep(1);
		}
	}

?>
