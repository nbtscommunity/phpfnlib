<?php
	require_once(dirname(__FILE__)."/../events/handler.php");
	require_once(dirname(__FILE__)."/../debug.php");

	class BIDIQueue {
		var $obj;
		var $in = array();
		var $out = array();
		function send($m) {
			$this->out[] = $m;
		}

		function recv($m) {
			$this->in[] = $m;
		}

		function has_outgoing() { return count($this->out); }
		function has_messages() { return count($this->in); }
	}

	class Tranceiver extends EventHandler {
		var $listeners; // Listening socket
		var $server; // Server socket
		var $queue = array(); // Data to be written
		var $clients = array(); // Client sockets
		var $othersockets = array(); // Other sockets to select

		function register_listener(&$s) {
			$this->listeners[(string)$s] &= $s;
		}

		function register_socket(&$s) {
			$q = new BIDIQueue();
			$q->obj &= $s;
			$this->queue[] &= $q;
		}

		function accept(&$s) {
			$new = socket_accept($s);
			$this->register_socket($new);
		}

		function wait() {
			$this->log("Waiting...");
			$r = array();
			$w = array();
			foreach($this->queue as $q) {
				$r[] &= $q->obj;
				if($q->has_outgoing()) {
					$w[] &= $q->obj;
				}
			}
			foreach($this->listeners as $s) {
				$r[] = $s;
			}
			//if(!count($w)) $w = NULL;
			//if(!count($r)) $r = NULL;
			//if(is_null($r) and is_null($w)) return FALSE;
			$this->log("Readers: ".vdump($r));
			$this->log("Writers: ".vdump($w));
			$v = socket_select($r, $w, $e = NULL, 120);
			$this->log("v = $v");
			if($v == 0) $this->terminate();
			if($w) {
				$this->do_queue_writes($w);
			}
			
			if($r) {
				foreach($r as $s) {
					$this->log("Event on $s");
					if($this->listeners[(string)$s]) {
						$this->log("Accepting client on $s");
						$this->accept($s);
					} elseif(isset($this->queue[(string)$s])) {
						$in = socket_read($s, 1024);
						$this->log("Read $in from $s");
						if($in) {
							$this->queue[(string)$s]->recv($in);
							$this->event('socket', $this->queue[(string)$s]);
						} else {
							switch(socket_last_error()) {
								case 11: 
									continue;
								default: 
									$this->error($s, 
										socket_strerror(socket_last_error())); 
								case 0:
								case 32:
								case 107:
									if($in === FALSE) {
										$this->removesocket($s);
									}
									$this->signal($s, 'Closed Connection');
							}
						}
					} else {
						$this->log("Bogus socket: $s");
					}
				}
			}
		}

		function terminate() {
			exit();
		}

		function removesocket(&$socket) {
			foreach($this->clients as $k => $s) {
				if($s == $socket) {
					unset($this->clients[$k]);
				}
			}
			if($this->listen == $socket) {
				$this->log("Listening socket closed");
				unset($this->listen);
			}
			if($this->server == $socket) {
				$this->log("Server socket closed");
				unset($this->server);
			}
		}
		
		function flush() {
			while(count($this->queuesockets) > 0) {
				$w = array();
				foreach($this->queuesockets as $k => $s) {
					if(is_resource($s) and $this->queue[(string)$s]) {
						$w[] = $s;
					} else {
						$this->log("Bogus socket $s");
					}
				}
				$v = socket_select($r = NULL, $w, $e = NULL, 0);
				$this->do_queue_writes($w);
			}
		}

		function do_queue_writes(&$w) {
			foreach($w as $s) {
				$q &= $this->queue[(string)$s]; 
				$out = array_shift($q->out);
				$this->log("From queue to $s: ".trim($out));
				$n = socket_write($s, $out);
				$out = substr($out, $n);
				if($out) {
					array_unshift($q->out, $out);
				}
			}
		}
		
		function recv($m, &$socket) {
			static $buffer;
			$buffer .= $m;
			while(strstr($buffer, $this->msgend())) {
				list($m, $buffer) = explode($this->msgend(), $buffer, 2);
				$this->recvmsg($m, $socket);
			}
		}

		function msgend() { return "\0"; }

		function recvmsg($m, &$socket) {
			$this->log("Message from $socket: $m");
		}

		function send($message, &$peer) {
			if(!is_resource($peer)) {
				$this->log("send(): \"$peer\" is not a resource; message is $message");
				return FALSE;
			}
			$this->log("Sending to $peer: $message");
			$this->queue[(string)$peer]->send($message);
		}

		function sendmsg($message, &$peer) {
			$this->send($message.$this->msgend(), $peer);
		}

	}
?>
