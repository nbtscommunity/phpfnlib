<?php

	require_once(dirname(__FILE__)."/../dir.php");
	require_once(dirname(__FILE__)."/../html.php");
	require_once(dirname(__FILE__)."/../http.php");
	require_once(dirname(__FILE__)."/../wiki/render.php");
	require_once(dirname(__FILE__)."/../wiki/functions.php");
	require_once(dirname(__FILE__)."/../purlencode.php");

	if(!defined('ALBUM_MODE')) define('ALBUM_MODE', 'thumbnails');
	if(!defined('ALBUM_EMBEDDED')) define('ALBUM_EMBEDDED', false);
	if(!defined('ALBUM_TITLES')) define('ALBUM_TITLES', true);
	if(!defined('STYLESHEET_BASE')) define('STYLESHEET_BASE', FALSE);
	if(!defined('STYLESHEET')) define('STYLESHEET', STYLESHEET_BASE."/style.css");
	if(!defined('HOMEPAGE')) define('HOMEPAGE', false);
	if(!defined('ALBUM_RESIZE')) define('ALBUM_RESIZE', false);

	if($_SERVER['ORIG_PATH_INFO']) {
		if($_SERVER['PATH_INFO']) {
			$_SERVER['SCRIPT_NAME'] = substr($_SERVER['ORIG_PATH_INFO'], 0, -strlen($_SERVER['PATH_INFO']));
		} else {
			$_SERVER['SCRIPT_NAME'] = $_SERVER['ORIG_PATH_INFO'];
		}
		$_SERVER['PHP_SELF'] = $_SERVER['ORIG_PATH_INFO'];
	}

	function php_self() {
		return purlencode($_SERVER['PHP_SELF']);
	}
	function script_name() {
		return purlencode($_SERVER['SCRIPT_NAME']);
	}
	
	if($image = $_GET['thumb']) {
		while(ob_get_level()) ob_end_clean();
		header("Content-type: image/jpeg");
		/*
		$i = imagick_create();
		imagick_read($i, $image);
		$w = imagick_get_attribute($i, 'width');
		$h = imagick_get_attribute($i, 'height');
		*/

		$i = imagecreatefromjpeg(dirname($_SERVER['SCRIPT_FILENAME'])."/".stripslashes($image));
		$w = imagesx($i);
		$h = imagesy($i);

		$r = (float)$w / (float)$h;
		if($w >= $h) {
			$nw = 100;
			$nh = 100 / $r;
		} else {
			$nh = 100;
			$nw = 100 * $r;
		}

		$o = imagecreatetruecolor($nw, $nh);
		imageinterlace($o, true);
		imagecopyresampled($o, $i, 0, 0, 0, 0, $nw, $nh, $w, $h); 
		imagejpeg($o);

		/*

		$o = imagick_copy_sample($i, $nw, $nh);
		imagick_dump($o, "JPEG");
		*/
	} elseif($image = $_GET['resize']) {
		while(ob_get_level()) ob_end_clean();
		header("Content-type: image/jpeg");
		/*
		$i = imagick_create();
		imagick_read($i, $image);
		$w = imagick_get_attribute($i, 'width');
		$h = imagick_get_attribute($i, 'height');
		*/

		if(!substr_count($_GET['size'], 'x')) {
			$size = '640x480';
		} else {
			$size = $_GET['size'];
		}

		list($targetx, $targety) = explode('x', $size);

		$i = imagecreatefromjpeg(dirname($_SERVER['SCRIPT_FILENAME'])."/".stripslashes($image));
		$w = imagesx($i);
		$h = imagesy($i);

		$rw = $targetx / $w;
		$rh = $targety / $h;

		if($rh >= 1 and $rw >= 1) {
			$nw = $w;
			$nh = $h;
		} else {
			if($rw < $rh) {
				$r = $rw;
			} else {
				$r = $rh;
			}

			$nw = (int)($w * $r);
			$nh = (int)($h * $r);
		}

		$o = imagecreatetruecolor($nw, $nh);
		imageinterlace($o, true);
		imagecopyresampled($o, $i, 0, 0, 0, 0, $nw, $nh, $w, $h); 
		imagejpeg($o);

		/*

		$o = imagick_copy_sample($i, $nw, $nh);
		imagick_dump($o, "JPEG");
		*/
	} elseif($image = $_GET['show']) {
		if(ALBUM_RESIZE) {
			$ei = php_self().'?resize='.rawurlencode($image).'&size='.ALBUM_RESIZE;
		} else {
			$ei = rawurlencode($image);
		}
		if(!ALBUM_EMBEDDED) {
		print(doctype("XHTML/1.0")."\n");
		?>
<html>
	<head>
		<title><?php print($image); ?></title>
		<style type='text/css'>
			img { /* margin-left: auto; margin-right: auto; */ }
			div.image { width: 100%; text-align: center; }
		</style>
		<meta http-equiv='Content-type' value='text/html; charset=UTF-8' />
		<?php 
			if(STYLESHEET_BASE) {
				$ss = STYLESHEET_BASE.'/style.css';
				print("<link rel='StyleSheet' href='$ss' type='text/css'/>");
			}
		?>
	</head>
	<body>
	<?php } ?>
		<div class='image'><?php 
		print("<img src=\"$ei\" alt=\"$image\" />");
		$text = @join('', @file(preg_replace('/(jpg|gif|png)$/', 'txt', $image)));
		if($text) {
			print(wiki_render($text));
		}
	?></div>
	<?php if(!ALBUM_EMBEDDED) { ?>
		</body>
</html>
		<?php
		} 
	} elseif($_SERVER['PATH_INFO'] == '/slideshow' or 
		(!$_SERVER['PATH_INFO'] and ALBUM_MODE=='slideshow') ) {
			if(!ALBUM_EMBEDDED) {
		print(doctype("XHTML/1.0")."\n");
		?>
<html>
	<head>
		<title>Photos</title>
		<style type='text/css'>
			img { border-style: none; }
			div.image { padding: 5px; width: 110px; height: 120px;
				font-size: 10px; text-align: center; float: left;
				margin-right: 10px; } 
		</style>
		<meta http-equiv='Content-type' value='text/html; charset=UTF-8' />
		<?php 
			if(STYLESHEET_BASE) {
				$ss= STYLESHEET_BASE.'/style.css';
				print("<link rel='StyleSheet' href='$ss' type='text/css'/>");
			}
		?>
	</head>
	<body>
	<?php
		}
		$d = get_dir($n = dirname($_SERVER['SCRIPT_FILENAME']));
		asort($d);
		
		$dir = array();
		foreach($d as $e) {
			if(preg_match('/jpg|jpeg$/i', $e)) {
				$dir[] = $e;
			}
		}
		
		if($_GET['seq'] < count($dir)) {
			$seq = (int)$_GET['seq'];
		} else {
			$seq = 0;
		}
		
		print("<div class='navigation'>");
		if($seq > 0) {
			print(" <a href='".php_self()."?seq=".($seq - 1)."'>".
				"Previous · ".
			"</a> ");
		} else {
			print(" Previous · ");
		}

		if(count($dir) > 1) {
			print(" <a href='".script_name()."/thumbnails'>Index</a>" );
		}

		if(HOMEPAGE) {
			print(" · <a href='".HOMEPAGE."'>Home</a>" );
		}

		if($seq < (count($dir) - 1)) {
			print(" · <a href='".php_self()."?seq=".($seq + 1)."'>".
				"Next".
			"</a> ");
		} else {
			print(" · Next ");
		}
		print("</div>");
		
		if(ALBUM_RESIZE) {
			$ei = php_self().'?resize='.rawurlencode($dir[$seq]).'&size='.ALBUM_RESIZE;
			print("<img src=\"$ei\" alt=\"$image\" /><br />");
		} else {
			$ei = dirname(script_name())."/".rawurlencode($dir[$seq]);
			print("<img src=\"$ei\" alt=\"$image\" /><br />");
		}

	
		if(!ALBUM_EMBEDDED) {

	?>
	</body>
</html> 
<?php
		}
	} elseif($_SERVER['PATH_INFO'] == '/thumbnails' or 
		(!$_SERVER['PATH_INFO'] and ALBUM_MODE=='thumbnails') ) {
			if(!ALBUM_EMBEDDED) {
		print(doctype("XHTML/1.0")."\n");
		?>
<html>
	<head>
		<title>Photos</title>
		<style type='text/css'>
			img { border-style: none; }
			div.image { padding: 5px; width: 110px; height: 120px;
				font-size: 10px; text-align: center; float: left;
				margin-right: 10px; } 
		</style>
		<meta http-equiv='Content-type' value='text/html; charset=UTF-8' />
		<?php 
			if(STYLESHEET_BASE) {
				$ss= STYLESHEET_BASE."/style.css";
				print("<link rel='StyleSheet' href='$ss' type='text/css'/>");
			}
		?>
	</head>
	<body>
	<?php
		}
		$d = get_dir($n = dirname($_SERVER['SCRIPT_FILENAME']));
		asort($d);
		
		if(is_file($n."/Commentary")) {
			print(wiki_render(join('', file($n."/Commentary"))));
		}
		
		$i = 0;
		foreach($d as $e) {
			if(preg_match('/jpg|jpeg$/i', $e)) {
				$ee = rawurlencode($e);
				$e = wiki_split_wikiwords(preg_replace('/[.][a-zA-Z]+$/', '', $e));
				print("<div class='image'><a href='".script_name()."/slideshow?seq=".($i)."'><img src='".php_self()."?thumb=$ee' alt='$e' /><br />$e</a></div>");
				$i++;
			} elseif(is_dir($n.'/'.$e)) {
				print("<div class='image'><a href='$e'>$e</a></div>");
			}
		}
		if(!ALBUM_EMBEDDED) {
	?>
	</body>
</html>
		<?php
			}
	} elseif($_SERVER['PATH_INFO'] == '/framed' or 
		(!$_SERVER['PATH_INFO'] and ALBUM_MODE=='framed') ) {
		print(doctype("XHTML/1.0 Frameset")."\n");
		?>
<html>
	<head>
		<title>Photo Album</title>
		<meta http-equiv='Content-type' value='text/html; charset=UTF-8' />
	</head>
	<frameset cols='150,*'>
		<frame src='<?php print(script_name().'/index-frame'); ?>' name='index' />
		<frame src='<?php print(script_name().'/view-frame'); ?>' name='view' />
	</frameset>
</html>
	<?php
	} elseif($_SERVER['PATH_INFO'] == '/index-frame') {
		print(doctype("XHTML/1.0")."\n");
	?>
<html>
	<head><title>Index</title>
		<meta http-equiv='Content-type' value='text/html; charset=UTF-8' />
		<?php 
			if(STYLESHEET_BASE) {
				$ss= STYLESHEET_BASE."/style.css";
				print("<link rel='StyleSheet' href='$ss' type='text/css'/>");
			}
		?>
	</heaD>
	<body>
<?php
	$d = get_dir($n = dirname($_SERVER['SCRIPT_FILENAME']));
	asort($d);

	foreach($d as $e) {
		if(preg_match('/jpg|jpeg$/i', $e)) {
			$ee = rawurlencode($e);
//			print("<div class='image'><a target='view' href='".script_name()."?show=$ee'><img src='".php_self()."?thumb=$ee' alt='$e' /><br />$e</a></div>");
			print("<p><a target='view' href='".script_name()."?show=$ee'>".preg_replace('/.jpeg$|.jpg$/i', '', $e)."</a></p>");
		} elseif(is_dir($n.'/'.$e) and file_exists($n.'/'.$e.'/index.php')) {
			print("<p><a href='../$e/index.php/index-frame'>$e</a></p>");
		} elseif(is_dir($n.'/'.$e)) {
			print("<p><a href='../$e/'>$e</a></p>");
		}
	}
?>
	</body>
</html>
	<?php
	} elseif($_SERVER['PATH_INFO'] == '/view-frame') {
		print(doctype("XHTML/1.0")."\n");
	?>
<html>
	<head><title></title>
		<meta http-equiv='Content-type' value='text/html; charset=UTF-8' />
		<?php 
			if(STYLESHEET_BASE) {
				$ss= STYLESHEET_BASE."/style.css";
				print("<link rel='StyleSheet' href='$ss' type='text/css'/>");
			}
		?>
	</head>
	<body><?php $text = @join('', @file('main.txt')); print(wiki_render($text)); ?></body>
</html>
	<?php
	}
?>
