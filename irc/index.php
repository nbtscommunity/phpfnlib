<?php
	require_once(dirname(__FILE__)."/../html.php");
	require_once(dirname(__FILE__)."/../http.php");
	require_once(dirname(__FILE__)."/../mysql.php");
	require_once(dirname(__FILE__)."/send.php");

	$db = mysql_connect('localhost', 'irc', 'irc');
	mysql_select_db('irc', $db);

	if($REQUEST_METHOD == 'POST' and !$PATH_INFO and $nickname) {
		$NICKNAME = trim($nickname);
//		chdir(dirname(__FILE__));
//		system("NICKNAME=$NICKNAME ./daemon > /dev/null &");
		http_302($SCRIPT_NAME."/Frameset?NICKNAME=$NICKNAME");
		exit("Redirect");
	}

	if(!$NICKNAME and !$PATH_INFO) {
		print(form($SCRIPT_NAME, table(
			row2("Nickname:","<input type='text' name='nickname' />").
			//row2("Username:","<input type='text' name='username' />").
			row2("", "<input type='submit' value='connect' />")
			)));
	} else {

		$PATH_INFO = substr($PATH_INFO, 1);
		$parts = explode('/', $PATH_INFO);

		if($parts[0] == 'Windows') {
			if($parts[1]) {
				$window = $parts[1];
				print("<html><head><title>IRC: $window</title></head>
						<frameset rows='*,50' border='0' name='window'>
							<frame src='http://$SERVER_NAME:8081$SCRIPT_NAME/Queue/".purlencode($window)."?NICKNAME=$NICKNAME' border='0' name='scroller' />
						<frame src='$SCRIPT_NAME/Inputbox/".purlencode($window)."?NICKNAME=$NICKNAME' border='0' scrolling='no' name='inputbox' />
					</frameset>
				</html>\n\n");
			} else {
				require(dirname(__FILE__)."/windows.php");
			}
		} elseif($parts[0] == 'Frameset') {
			print(
				"<html>
					<head><title>NBTSC Web IRC</title></head>
					<frameset rows='50,*' border='0'>
						<frameset cols='*,70' border='0'>
							<frame src='$SCRIPT_NAME/Windows?NICKNAME=$NICKNAME' name='windowlist' border='0' />
							<frame src='http://$SERVER_NAME:8080$SCRIPT_NAME/Daemon?NICKNAME=$NICKNAME' name='daemon' border='0' />
						</frameset>
						<frame src='$SCRIPT_NAME/Windows/Status?NICKNAME=$NICKNAME' name='window' border='0' />
					</frameset>
				</html>\n\n");
		} elseif($parts[0] == 'Queue') {
			if($parts[1]) {
				$window = $parts[1];
				require(dirname(__FILE__)."/read.php");
			} else {
				print("please select a window");
			}
		} elseif($parts[0] == 'Inputbox') {
			if($parts[1]) {
				$window = $parts[1];
				if($REQUEST_METHOD == 'POST') {
					sendtoirc($NICKNAME, $window, $input);
#					print("Sent $input; click <a href='$PHP_SELF'>here</a> to continue");
					http_302(purlencode($PHP_SELF)."?NICKNAME=$NICKNAME");
					exit();
				} else {
					require(dirname(__FILE__)."/write.php");
				}
			} else {
				print("please select a window");
			}
		} elseif($parts[0] == 'Daemon') {
			chdir(dirname(__FILE__));
			system("NICKNAME=$NICKNAME ./daemon > /dev/null &");
			echo('Connected');
		} elseif ($parts[0] == 'Logoff') {
			sendtoirc($NICKNAME, $window, 'QUIT :Logging off');
			http_302($SCRIPT_NAME);
			exit();
		} else {
			http_404();
			exit();
		}
	}
?>
