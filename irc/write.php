<?php
	print("<form action='".purlencode($PHP_SELF)."' method='POST' name='inputform'>");
	print("$NICKNAME: <input type='text' size='50' name='input' />");
	print("<input type='hidden' name='NICKNAME' value='$NICKNAME' />");
	print("<input type='submit' value='send' />");
	print(
		"<script language='javascript'> ".
			"document.forms['inputform'].input.focus(); ".
		"</script>"
	);
	print("</form>");
?>
