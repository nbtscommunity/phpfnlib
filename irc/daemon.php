<?php
	
	require_once(dirname(__FILE__)."/../mysql.php");
	require_once(dirname(__FILE__)."/irc.php");

	// IO Slave Daemon

	ignore_user_abort(TRUE);
	set_time_limit(60);

	$db = mysql_connect('localhost', 'irc', 'irc');
	mysql_select_db('irc', $db);

	mysql_query("DELETE FROM queue WHERE nickname = '$NICKNAME'");
	mysql_query("DELETE FROM outgoing WHERE nickname = '$NICKNAME'");
	mysql_query("DELETE FROM windows WHERE nickname = '$NICKNAME'");

	queue('Status', 'Test....');
	$windows = get_windows();

	if(!$NICKNAME) {
		exit();
	}

	$ircserver = 'femme.sapphite.org';

	if($irc = fsockopen($ircserver, 6667)) {
		socket_set_blocking($irc, FALSE);
		fputs($irc, "NICK $NICKNAME\n");
		fputs($irc, "USER $NICKNAME 1 1 1 1\n");
		while(!feof($irc)) {
			set_time_limit(60);
			$r = fgets($irc, 512);
			if($r) {
				print("< ".$r);
				$msg = irc_split_message($r);
				extract($msg);
				if($command == 'PING') {
					fputs($irc, ":$NICKNAME PONG :$message\n");
				} elseif($dest 
					and (strtolower($dest) != strtolower(format_nick($origin)))
					and (strtolower($origin) != $ircserver)
					and ($dest != 'AUTH')
					and ($command != 'QUIT')
					) {
					if(strtolower($dest) != strtolower($NICKNAME)) {
						queue($dest, $r);
					} else {
						queue(format_nick($origin), $r);
					}
				} else {
					queue('Status', $r);
				}
			}
			$r = get_outgoing();
			foreach($r as $row) {
				fputs($irc, $row['data']."\n");
				print("> ".$row['data']."\n");
			}
			usleep(50000);
		}
	} else {
		//FIXME -- shutdown web client with QUIT in queue
	}

	queue('Status', 'Connection closed', 'quit');

?>
