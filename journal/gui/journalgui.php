<?php

	$ui = new GladeXML('journalgui.glade');
	$window = $ui->get_widget("window1");
	$window->show_all();

	gtk::main();

?>
