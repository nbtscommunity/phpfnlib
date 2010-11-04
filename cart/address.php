<?php

	require(dirname(__FILE__)."/../html.php");

	function cart_addressform($ci) {
		extract($ci);
		return 
			html_dl(
				"<h2>Billing Information</h2>".
				dt("Name").
				dd(field("billname", $billing['name'])).
				dt("Address").
				dd(addresstextarea("billaddress", $billing['address'])).
				dt("Phone Number").
				dd(field("billphone", $billing['phone']))).
			html_dl(
				"<h2>Shipping Information</h2>".
				dt("Name").
				dd(field("destname", $shipping['name'])).
				dt("Address").
				dd(addresstextarea("destaddress", $shipping['address'])).
				dt("Phone Number").
				dd(field("destphone", $billing['phone']))).
			html_dl(
				dd(submit()));
	}

	function format_address($title, $address, $editurl = FALSE) {
		extract($address);
		return(html_dl(dt($title).dd(address("$name<br />".nl2br($address)."<br
		/>$phone")).($editurl?hyperlink($editurl, "Change"):"")));
	}

?>
