<?php

	if(!defined('XML_DIFF_ENGINE')) define('XML_DIFF_ENGINE', 'diffmk');

	function xmldiff_external($a, $b) {
		$fna = tempnam('/tmp', 'xmldi');
		$fa = fopen($fna, 'w');
		fwrite($fa, $a, strlen($a));
		fclose($fa);	

		$fnb = tempnam('/tmp', 'xmldi');
		$fb = fopen($fnb, 'w');
		fwrite($fb, $b, strlen($b));
		fclose($fb);	
		if(XML_DIFF_ENGINE == 'xmldiff') {
			$result = `xmldiff -x $fna $fnb`;
			$tmp = fopen("/tmp/xmldiff.tmp", "w");
			fwrite($tmp, $result, strlen($result));
			fclose($tmp);
		} elseif(XML_DIFF_ENGINE == 'diffmk') {
			$ofn = tempnam('/tmp', 'xmldi');
			system("LANG=en_US.UTF-8 diffmk --nousechanged --doctype xhtml --output $ofn $fna $fnb >/dev/null");
			$result = join('', file($ofn));
			unlink($ofn);
			/*
			print("Result: ");
			print(htmlentities($result));
			print("Prev: ");
			print(htmlentities(join('', file($fna))));
			print("Curr: ");
			print(htmlentities(join('', file($fnb))));
			*/
		} else {
			$result = 'Unknown diff engine';
		}
		
		/*
		$result = 'File A: <pre>'.
			htmlspecialchars(join('', file($fna))).'</pre>'.$result;
		$result = 'File B: <pre>'.
			htmlspecialchars(join('', file($fnb))).'</pre>'.$result;
		*/

		unlink($fna);
		unlink($fnb);

		return $result;

	}

?>
