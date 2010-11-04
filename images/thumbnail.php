<?php

	function image_make_thumbnail($image, $maxx = 100, $maxy = 100) {
		//header("Content-type: image/jpeg");
		/*
		$i = imagick_create();
		imagick_read($i, $image);
		$w = imagick_get_attribute($i, 'width');
		$h = imagick_get_attribute($i, 'height');
		*/

		$i = imagecreatefromjpeg($image);
		$w = imagesx($i);
		$h = imagesy($i);

		$r = (float)$w / (float)$h;
		if($w >= $h) {
			$nw = $maxx;
			$nh = $maxy / $r;
		} else {
			$nh = $maxy;
			$nw = $maxx * $r;
		}

		$o = imagecreatetruecolor($nw, $nh);
		imageinterlace($o, true);
		imagecopyresampled($o, $i, 0, 0, 0, 0, $nw, $nh, $w, $h); 

		imagejpeg($o);

		/*

		$o = imagick_copy_sample($i, $nw, $nh);
		imagick_dump($o, "JPEG");
		*/
	}
?>
