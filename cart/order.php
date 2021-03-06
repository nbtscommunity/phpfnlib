<?php
	function cart_order_format($order, $editable = array()) {
		if(!is_array($editable)) {
			$editurl = $editable;
			$editable = array();
		}
		foreach($order as $k => $item) { 
			$o .= tr(
				td((in_array('qty', $editable) ? 
					"<input type='text' name='items[$k][qty]' size='3' value='".$item['qty']."' />"
				: $item['qty'])).td($item['name']).
				td(format_currency($item['price'])).
				td(format_currency($item['price'] * $item['qty'])));
		}
		if($o) {
			return html_dl(dt("Your Order").dd("<table>".tableheaders('Qty',
			'Description', 'Price', 'Total').$o."</table>").
			($editurl?hyperlink($editurl, "Change"):""));
		} else {
			return '';
		}
	}

	function cart_item($name, $price, $qty) {
		return compact('name', 'price', 'qty');
	}

	function cart_order_new($items, $customer) {
		if(mysql_query("INSERT INTO orders (customerid) VALUES ('$customer')")) {
			$r = mysql_query("SELECT last_insert_id() AS id");
			$r = mysql_fetch_all($r);
			$id = $r[0]['id'];
			foreach($items as $item) {
				extract($item);
				if(!mysql_query("INSERT INTO order_items 
					(quantity, price, description, orderid) 
					VALUES ($qty, $price, '                                                                                                                                      