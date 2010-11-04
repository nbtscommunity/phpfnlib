<?php

	require_once(dirname(__FILE__).'/../umd.php');

	class IRCCDaemon extends UMDDaemon {
		var $ircserversocket;
		var $ircserver; // Hostname of server
		var $ircserverport; // Port of server
		function IRCCDaemon($user) {
			UMDDaemon::UMDDaemon('/tmp/.php-proxy-'.$user);
			$this->file = __FILE__;
		}

		function recvmsg($m, &$client) {
			$n = unserialize($m);
			if(!$n) {
				$this->log("Malformed message: $m");
			} else {
				$this->log("Message: $m");
			}
			extract($n);

			call_user_func_array(array($this,'event'),
				array_merge($event.'_client', $args));
		}

		function msgend() { return '#$#$'; }
		
		function initclient(&$s) {
			UMDDaemon::initclient($s);
			$this->sendmsg(serialize($this->channels), $s);
		}

		function e_connect_client($server, $port = 6667) {
			$this->ircserver = $server;
			$this->ircserverport = (int)$port;
			$this->ircserversocket = socket_create(
				AF_INET, SOCK_STREAM, SOL_TCP);
			if(!socket_connect($this->ircserversocket, 
				$this->ircserver, $this->ircserverport)) {
				$this->log("Could not connect to IRC server (".
					$this->ircserver.":".$this->ircserverport.
					"): ".
					socket_strerror(socket_last_error()));
			}
			$this->register_socket($this->ircserversocket);
			$this->send_to_irc("USER testing polis $server :Testing User");
			$this->flush();
			$this->send_to_irc("NICK Testing");
			$this->flush();
			while(!$this->alive) $this->wait();
		}

		function e_join_client($channel) {
			$this->send_to_irc("JOIN $channel");
		}

		function e_part_client($channel) {
			$this->send_to_irc("PART $channel");
		}

		function e_privmsg($channel, $message) {
			$this->send_to_irc("PRIVMSG $dest :$message");
		}
		
		function e_notice($channel, $message) {
			$this->send_to_irc("NOTICE $dest :$message");
		}

		function send_to_irc(&$s) {
			if(!is_resource($this->ircserversocket)) {
				$this->log("IRC not connected!");
				return FALSE;
			}
			$this->log("Sending to IRC(".$this->ircserversocket."): $s");
			$this->queue[(string)$this->ircserversocket] .=  "$s\n";
			$this->queuesockets[(string)$this->ircserversocket] &=
				$this->ircserversocket;
		}

		function e_socket(&$s) {
			static $buffer;
			$in = socket_read($s, 512);
			if($in === FALSE) {
				if(socket_last_error()) {
					unset($this->othersockets[$s]);
					$this->log(socket_strerror(socket_last_error()));
					$this->terminate();
				} else {
					return TRUE;
				}
			} else {
				$buffer .= $in;
				while(strstr($buffer, "\n")) {
					list($line, $buffer) = explode("\n", $buffer, 2);
					$this->parse_irc($line);
				}
				return TRUE;
			}
		}

		function terminate() {
			$this->log("Terminating");
			if($this->ircserversocket) socket_close($this->ircserversocket);
			UMDDaemon::terminate();
		}

		function parse_irc($line) {
			$this->alive = TRUE;
			$this->log("From IRC: $line");
			if($line{0} == ':') {
				$from = substr($line, 1, strpos($line, ' '));
				$line = substr($line, strpos($line, ' ') + 1);
			}
			$command = substr($line, 0, strpos($line, ' '));
			$line = substr($line, strpos($line, ' ') + 1);
			$this->log("IRC: From = $from; Command = $command; Rest = $line");
			if($command == 'PING') {
				$this->send_to_irc('PONG :Testing castle.nbtsc.org');
			} elseif($command == 'ERROR') {
				$this->terminate();
			} elseif($command == 'JOIN') {
				$this->event('join', $from, substr($line, 1));
			} elseif($command == 'PART') {
				$this->event('part', $from, substr($line, 1));
			} elseif($command == '324') {
				$this->event('channel_mode', $from, $line);
			} elseif($command == '353') {
				$this->event('namelist', $from, substr($line, 1));
			} elseif($command == '366') {
				$this->event('namelist_end', $from, substr($line, 1));
			}
		}

	}

	class IRCCClient extends UMDClient {
		function IRCCClient($user) {
			if(!$this->file) $this->file = __FILE__;
			$this->user = $user;
			UMDClient::UMDClient('/tmp/.php-proxy-'.$user);
		}

		function spawn_args() {
			return $this->user;
		}
		
		function msgend() { return '#$#$'; }

		function command($command) {
			$args = func_get_args();
			array_shift($args);
			$m = array('event' => $command, 'args' => $args);
			$this->sendmsg(serialize($m), $this->serversocket);
		}
	}
?>
