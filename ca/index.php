<?php
	if(!defined('CA_ROOT')) {
		define('CA_ROOT', '/var/lib/openssl');
	}
	
	if(!defined('CA_PASS')) {
		define('CA_PASS', 'makecertificategonow');
	}

	require_once(dirname(__FILE__) .'/../login/login.php');
		
	if(ereg('MSIE', $USER_AGENT)) {
		$outform = 'DER';
	} else {
		$outform = 'PEM';
	}

	chdir(CA_ROOT);

	if($PATH_INFO == '/Login') {
		if(is_logged_in()) {
			http_302($SCRIPT_NAME);
			exit();
		} else {
			require(dirname(__FILE__) . "/../login/index.php");
		}
	} elseif($PATH_INFO == '/NewCertificate') {
		if($email = is_logged_in()) {
			if($REQUEST_METHOD == 'POST') {
				header('Content-type: application/x-x509-user-cert');
				if($f = fopen($filename = '/tmp/'.uniqid('CA-').".spkac", 'w')) {
					$SPKAC = str_replace("\r", '', $SPKAC);
					$SPKAC = str_replace("\n", '', $SPKAC);
					fputs($f, "SPKAC=$SPKAC\n");
					fputs($f, "countryName=US\n");
					fputs($f, "stateOrProvinceName=Oregon\n");
					fputs($f, "organizationName=Not Back To School Camp\n");
					fputs($f, "CN=$CN\n");
					if($OU) {
						fputs($f, "0.OU=$OU\n");
					}
					fputs($f, "emailAddress=$email@nbtsc.org\n");
					fclose($f);
					passthru("CA_PASS=".CA_PASS." openssl ca -batch -passin env:CA_PASS -spkac $filename | openssl x509 -outform $outform");
					unlink($filename);
				} else {
					print("Could not open file $filename");
				}
			} else {
				print(
		"<form action='$PHP_SELF' method='POST' enctype='application/x-www-form-urlencoded'>
			<b>Key size:</b> <keygen keytype='rsa' name='SPKAC' /><br />
			<b>Country:</b> US<br />
			<b>State or Province:</b> Oregon<br />
			<b>Locale:</b> Eugene<br />
			<b>Organization Name:</b> Not Back To School Camp<br />
			<b>Organizational Unit:</b> <input name='OU' type='text' value=''/><br />
			<b>Common Name:</b> <input name='CN' type='text' /><br />
			<b>Email Address:</b> $email@nbtsc.org<br />
			<input type='submit' />
		</form>");
			}
		} else {
			print("Please <a href='$SCRIPT_NAME/Login'>Log In</a> first");
		}
	} elseif($PATH_INFO == '/CA/Key') {
		header('Content-type: application/x-x509-ca-cert');
		passthru("openssl x509 -in /var/lib/openssl/cacert.pem -outform $outform");
	} else {
		print("<a href='$SCRIPT_NAME/CA/Key'>CA Key</a> | ");
		if(is_logged_in()) {
			print("<a href='$SCRIPT_NAME/NewCertificate'>New Certificate</a> | ");
		} else {
			print("<a href='$SCRIPT_NAME/Login'>Log In</a>");
		}
	}
?>
