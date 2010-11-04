<?php

	require_once(dirname(__FILE__)."/../html.php");
	require_once(dirname(__FILE__)."/../http.php");
	require_once(dirname(__FILE__)."/login.php");
	require_once(dirname(__FILE__)."/../auth/authentication.php");

	function login_get_tokens() {
		$header = getallheaders();
		$header = $header['Authenticate'];
		if(preg_match('/opaque="([^"]*)"/', $header, $matches)) {
			return unldapify(base64_decode($matches[1]));
		}
	}
	$LOGIN_TOKENS = login_get_tokens();

	if(LOGIN_STYLE == 'form') {
		session_register('LOGIN_USERNAME');
		session_register('LOGIN_PASSWORD');
		session_cache_limiter('private_no_cache');
		//session_cache_limiter('public');
			
		function show_login($message = '') {
			global $_SERVER;
			print(body(form($_SERVER['PHP_SELF'], 
				$message.
				table(
					row2("Username:", field("username")).
					row2("Password:", password_field("password")).
					row2('', submit('Log In'))))));
		}

		if(isset($_POST['username'])) {
			if(
				succeeds(authenticate(
					$_POST['username'],
					$_POST['password'], AUTH_VERIFY))) { 
				if(authorized($_POST['username'], 
					LOGIN_SERVICE, AUTH_VERIFY)) {

					session_start();
					$LOGIN_USERNAME = $_POST['username'];
					$LOGIN_PASSWORD = $_POST['password'];
					$_SESSION['LOGIN_USERNAME'] = $LOGIN_USERNAME;
					$_SESSION['LOGIN_PASSWORD'] = $LOGIN_PASSWORD;

					http_302($SCRIPT_NAME);
					exit();
				} else {
					$message = "You are not authorized for this operation";
					unset($_SESSION['LOGIN_USERNAME']);
					unset($_SESSION['LOGIN_PASSWORD']);
					unset($LOGIN_PASSWORD);
					unset($LOGIN_USERNAME);
				} 
			} else {
				$message = "Login Failed";
			}
		}

		if($message) {
			unset($LOGIN_USERNAME);
			unset($LOGIN_PASSWORD);
			session_destroy();
		}
	
		if(__FILE__ == $SCRIPT_FILENAME)
			print("<html><head><title>Login</title></head><body>");

		if(!is_logged_in()) {
			show_login($message);
		} else {
			print("Logged in successfully");
		}
		
		if(__FILE__ == $SCRIPT_FILENAME) print("</body></html>");
	} elseif(LOGIN_STYLE == 'http') {
		login_do_http_auth();
		if(!is_logged_in()) {
			http_401();
			print("Not logged in.");
			exit();
		} else {
			//$s = ob_get_contents(); ob_end_clean();
			//ob_start("ob_userpref");
			//print($s);
		}
	} elseif(LOGIN_STYLE == 'cookiehttp') {
		if($_POST['logout']) {
			setcookie('login', '', time() + 84000, $SCRIPT_NAME);
			is_logged_in(false);
			http_401();
		} elseif($_COOKIE['login'] or $_POST['login']) {
			setcookie('login', 'true', time() + 84000, $SCRIPT_NAME);
			login_do_http_auth();
			if(!is_logged_in()) {
				http_401();
				print("Not logged in.");
				exit();
			} else {
				print(form($_SERVER['PHP_SELF'], 
					submit('Log Out', 'logout')));	
				//$s = ob_get_contents(); ob_end_clean();
				//ob_start("ob_userpref");
				//print($s);
			}
		} else {
			print(form($_SERVER['PHP_SELF'], submit('Log In', 'login')));
		}
	} else {
		die("Foobie bletch, Mon Signor!");
	}

			
?>
