<?php

	require_once('wiki-renderer.php');

	class Test {
		function run() {
			return FALSE;
		}
		function title() {
			return get_class($this);
		}
	}

	class ComparisonTest extends Test {
		function f($data) {
			return FALSE;
		}
		function run() {
			$result = $this->f($this->data);
			if($result != $this->expected) {
				return "$result != " . $this->expected;
			} else {
				return TRUE;
			}
		}
	}

	class FunctionTest extends ComparisonTest {
		function FunctionTest($f, $data, $expected) {
			$this->f = $f;
			$this->data = $data;
			$this->expected = $expected;
		}
		function f($data) {
			$f = $this->f;
			return $f($data);
		}
		function title() {
			return get_class($this)." function ".$this->f;
		}
	}

	class StrstrTest extends FunctionTest {
		function StrstrTest($f, $data, $strstr) {
			$this->f = $f;
			$this->data = $data;
			$this->strstr = $strstr;
		}
		function run() {
			$f = $this->f;
			$result = $f($this->data);
			if(strstr($result, $this->strstr)) {
				return "$result contains ".$this->strstr;
			} else {
				return TRUE;
			}
		}
	}

	class Tester {
		var $tests = array();
		function add($test) {
			$this->tests[] = $test;
		}
		function run() {
			foreach($this->tests as $test) {
				$result = $test->run();
				print("<p>");
				print($test->title().": ");
				if($result !== TRUE) {
					print("<span style='color: red;'>Fail: ".htmlentities($result)."</span>");
				} else {
					print("<span style='color: green;'>Success</span>");
				}
				print("</p>");
			}
		}
	}

	$tester = new Tester();
	$tester->add(new FunctionTest('wiki_render', 'AWikiWord', "<p><a href='AWikiWord'>AWikiWord</a></p>"));
	$tester->add(new StrstrTest('wiki_render', '- not bold -', "<strong>"));

	$tester->run();

?>
