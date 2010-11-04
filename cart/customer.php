<?php

	require_once(dirname(__FILE__)."/../mysql.php");

	function cart_customer_new($ci) {
		extract($ci);
		$id = FALSE;
		if(mysql_query("INSERT INTO customers (name) VALUES ('".
			$shipping['name'].
		"')")) {
			if($r = mysql_query("SELECT last_insert_id() AS id")) {
				$r = mysql_fetch_all($r);
				$id = $r[0]['id'];
				extract($shipping);
				if(!mysql_query("INSERT INTO addresses 
					(type, customerid,  name, address, phone) VALUES 
					('shipping', '$id', '$name', '$address', '$phone')")) {
					print(mysql_error());
				}

				extract($billing);
				if(!mysql_query("INSERT INTO addresses 
					(type, customerid, name, address, phone) VALUES 
					('billing', '$id', '$name', '$address', '$phone')")) {
					print(mysql_error());
				}
			} else {
				print(mysql_error());
			}
		} else {
			print(mysql_error());
		}
		return $id;
	}

?>
