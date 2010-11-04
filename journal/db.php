<?php

	function pg_fetch_all($result) {
		$o = array();
		for($i = 0; $i = pg_numrows($result); $i++) {
			$o[$i] = pg_result($result, $i);
		}
		return $o;
	}

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

	
?>
