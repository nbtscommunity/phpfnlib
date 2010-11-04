<?php

	function mysql_fetch_all($result) {
		if(!$result) {
			print(mysql_error());
			return array();
		}
		$o = array();
		while($row = mysql_fetch_assoc($result)) {
			array_push($o, $row);
		}
		return $o;
	}

	if(!defined("MYSQL_USERNAME")) define('MYSQL_USERNAME', 'Default');
	if(!defined("MYSQL_PASSWORD")) define('MYSQL_PASSWORD', 'password');
	if(!defined("MYSQL_DEFAULT_HOST")) 
		define('MYSQL_DEFAULT_HOST', 'localhost');


	
?>
