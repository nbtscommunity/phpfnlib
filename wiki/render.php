<?php

	require_once(dirname(__FILE__)."/../html.php");
	require_once(dirname(__FILE__)."/../rcs/rcs.php");

	if(!defined("WIKI_WIKIWORDS")) define("WIKI_WIKIWORDS", true);
	if(!defined("WIKI_MADLIBLINKS")) define("WIKI_MADLIBLINKS", false);
	if(!defined("WIKI_FOOTNOTES")) define("WIKI_FOOTNOTES", true);

	function myprint_r($var) {
		print("<pre>");
		print_r($var);
		print("</pre>");
	}

	if(!defined('WIKI_REPLY')) { define('WIKI_REPLY', false); }
	

	define('REGEX_EMAIL', '[a-zA-Z0-9_+-]+@[a-zA-Z0-9-.]+');
	define('REGEX_URL', 
		'(?x:
			(?<!&lt;)(?i:(?:(?:file|http|https|ftp|news|imap)://[-a-z0-9.]*(?:/[-a-z.~=/%&;:?,0-9_+]*[-a-z=/%0-9_])?/?)(?<!&amp|&am|&a|&)|(?:(?:mailto|callto):'.REGEX_EMAIL.')(?<!&gt;|&gt|&g|&)))');
	define('REGEX_URL_ENCAPS', 
		'(?xi:
			&lt;
			(?:
				(?:(?:file|http|https|ftp|news|imap)://[-a-z0-9.]*
					(?:/[-a-z.~=/%&;:?,0-9_+]*[-a-z=/%0-9_])?/?
				)
				|
				(?:
					(?:mailto|callto):'.REGEX_EMAIL.'
				)
			)
			&gt;
		)');
	define('REGEX_PROPERNAME', 
		"[A-Z][-a-zA-Z0-9_']*[a-z0-9]+(?:[ \t]+(?:and|[A-Z][a-zA-Z']+|[A-Z](?:[-][&A-Z])*[.]))*");
	define('REGEX_WIKIWORD', 
		'(?:[.]?|[\\#]?)\b[A-Z][0-9a-z_\']*[A-Z][0-9a-z_\'A-Z]*');
	define('WIKI_PARAGRAPH_END', '(?:\n\s*\n|\\Z|\n(?=[*]|&gt;|\[[0-9]+\]))');
	
	class RegexCallback {
		var $environment;
		function regex() {
			trigger_error(get_class($this)." has no regex defined", E_USER_WARNING);
			return '';
		}
		function callback($match, $source) {
			trigger_error(get_class($this)." does nothing with ${match[0]}", E_USER_WARNING);
			return $match[0];
		}
		function is_linker() {
			return false;
		}
		function hash() {
			return get_class($this);
		}
		function set_environment($e) {
			$this->environment = $e;
		}
		function environment() {
			if(!is_object($this->environment)) {
				$this->environment = new WikiRenderer();
				print("!");
				print("<!--");
				$bt = debug_backtrace();
				foreach($bt as $v) {
					print($v['file'].":".$v['line']." in ".$v['function']."\n");
				}
				print("-->");
			}
			return $this->environment;
		}
	}

	class WikiRenderer {
		function text_decoration_markup_handlers($linkers = true) {
			$handlers = new RegexCallbacks();
			$handlers->set_environment($this);
			$handlers->add(new EmdashRegexCallback());
			$handlers->add(new ItalicRegexCallback());
			$handlers->add(new BoldRegexCallback());
			$handlers->add(new QuoteRegexCallback());
			$handlers->add(new UnderlineRegexCallback());
			$handlers->add(new SuperscriptRegexCallback());
			$handlers->add(new MonospaceRegexCallback());
			if(WIKI_WIKIWORDS) $handlers->add(new WikiWordRegexCallback());
			return $handlers;
		}
		
		function reply_link($text) {
			if(WIKI_REPLY) {
				return(" <a class='reply' href='".$_SERVER['PHP_SELF']."?reply&amp;to=".htmlentities(pqurlencode(num_ref($text, 'reply-to'.$this->uniq)), ENT_QUOTES)."'>Reply</a>");
			} else {
				return "";
			}
		}

		function inline_markup_handlers($remove = array()) {
			$handlers = new RegexCallbacks();
			$handlers->set_environment($this);
			if(WIKI_MADLIBLINKS)
				$handlers->add(new MadLibLinkRegexCallback());
			$handlers->add(new BracketLinkNaturalRegexCallback());
			if(!isset($remove['bracketlinks'])) $handlers->add(new BracketLinkRegexCallback());
			$handlers->add(new InterwikiNaturalRegexCallback());
			$handlers->add(new PersonalPageLinkRegexCallback());
			$handlers->add(new ThePageRegexCallback());
			$handlers->add(new EmailRegexCallback());
			$handlers->add(new URLRegexCallback());
			$handlers->add(new EmdashRegexCallback());
			$handlers->add(new ItalicRegexCallback());
			$handlers->add(new BoldRegexCallback());
			$handlers->add(new QuoteRegexCallback());
			$handlers->add(new UnderlineRegexCallback());
			$handlers->add(new SuperscriptRegexCallback());
			if(WIKI_WIKIWORDS) $handlers->add(new WikiWordRegexCallback());
			$handlers->add(new MonospaceRegexCallback());
#			$temp = $this->text_decoration_markup_handlers();
#			foreach($temp->handlers as $h) {
#					$handlers->add($h);
#			}
			return $handlers;
		}

		function block_markup_handlers() {
			$handlers = new RegexCallbacks();
			$handlers->set_environment($this);
			$handlers->add(new MusicRegexCallback());
			$handlers->add(new WikiHeadingRegexCallback());
			$handlers->add(new MIMEHeaderRegexCallback());
			$handlers->add(new FootnoteRegexCallback());
			$handlers->add(new IRCConversationRegexCallback());
			$handlers->add(new WikiPoetryRegexCallback());
			$handlers->add(new WikiListRegexCallback());
			$handlers->add(new WikiBlockQuoteRegexCallback());
			$handlers->add(new CodeRegexCallback());
			$handlers->add(new PreformatRegexCallback());
			$handlers->add(new WikiParagraphRegexCallback());
			return $handlers;
		}
		
		function link_url($url, $text = NULL) {
			if(is_null($text)) {
				$text = $url;
			}
			return $this->htmllink($url, $text);
		}

		function htmllink($url, $text, $attrs = array()) {
			$attro = '';
			foreach($attrs as $attr => $val) {
				$attro .= ' '.$attr.'="'.htmlentities($val).'"';
			}
			$url = str_replace(
				array('&quot;', '&#039;', '&amp;'), array('"', "'", '&'), $url);
			$url = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
			return "<a href='$url'$attro>$text</a>";
		}

		function link_known_wikipage($page, $text) {
			return $this->htmllink($page, $text);
		}
		function link_wikipage($page, $text) {
			return $this->htmllink($page, $text);
		}

		function wiki_hyperlink($dest, $text) {
			if(preg_match('/^(file:|http:|ftp:|news:|mailto:|https:)/', $dest)) {
				return("<a href='$dest'>$text</a>");
			} 
			if($dest{0} == '/') {
				$dest = pqurlencode($dest);
				return("<a href='$dest'>$text</a>");
			} else { // Is relative to wiki
				return($this->link_wikipage($dest, $text));
			}	
		}

		function run($data) {
			$data = htmlspecialchars($data, ENT_NOQUOTES, 'UTF-8');
			$handlers = $this->block_markup_handlers();
			if($subparts = preg_split('/^-----*\s*$/ms', $data)) {
				$o = array();
				foreach($subparts as $subpart) {
					$o2 = array();
					$parts = preg_split("/(?:\n\s*){2}\n/", $subpart);
					foreach($parts as $part) {
						$handlers->set_environment($this);
						$t = trim($handlers->run($part));
						if($t)
						$o2[] = "<div class='section'>$t</div>";
					}
					$o[] = join("\n", $o2);
				}
				return join('<hr />', $o);
			} else {
				return $handlers->run($data);
			}
		}
	}

	class WikiWordRegexCallback extends RegexCallback {
		function regex() {
			return "(?<![/-_])(?:[.]?|[\\#]?)\b[A-Z][0-9a-z_']*[A-Z][0-9a-z_'A-Z]*";
		}
		function is_linker() { return true; }
		function callback($match, $source) {
			if (preg_match("/^(Mac|Mc|De|D')[A-Z][a-z]*$/", $match[0]))
				return $match[0];
			if ($match[0]{0} == '.') return substr($match[0], 1);
			$dest = $match[0];
			return $this->environment()->wiki_hyperlink($dest, $match[0]);
		}
	}
	
	class URLRegexCallback extends RegexCallback {
		function regex() {
			return '('.REGEX_URL.')';
		}
		function is_linker() { return true; }
		function callback($match, $source) {
			$e = $this->environment();
			return $e->link_url($match[0], $match[0]);
		}
	}

	class EmailRegexCallback extends RegexCallback {
		function regex() {
			return '\b[-a-zA-Z0-9+&_.]+@[-a-z0-9A-Z.]+\.[-A-Z0-9a-z.]+\b';
		}
		function callback($match, $source) {
			$e = $this->environment();
			if(!is_logged_in()) {
				$dest = str_replace(array('@', '.'), array(' at ',
				'dot'), $match[0]);
			} else {
				$dest = $match[0];
			}
			return $e->link_url('mailto:'.$dest, $dest);
		}
	}
	
	class ThePageRegexCallback extends RegexCallback {
		function regex() {
			return '([Tt]he\s+)('.REGEX_PROPERNAME.')(\s+[Pp]age)';
		}
		function callback($match, $source) {
			return $this->environment()->wiki_hyperlink($match[2], $match[1].$match[2].$match[3]);
		}
	}

	class MusicRegexCallback extends RegexCallback {
		function regex() {
			return "[\\\\](score|notes)\\s\\{.*\\}";
		}
		function pre() {
			return '\version "2.6.5"
\header { tagline = "" }
\paper  {
	pagenumber = no
	raggedright = ##t
	indent = 0.0\mm
}
';
		}
		function post() {
		}
		function callback($match, $source) {
			if($match[1] == 'score') {
				return render_lilypond_cached($this->pre().$match[0]);
			} else {
				return render_lilypond_cached($this->pre().'\score { ' . $match[0] .  ' } ');
			}
		}
	}

	define("LILYPOND_CACHEDIR", "/tmp/lilypond/");
	define("LILYPOND_WEBROOT", "/lilypond/");

	function render_lilypond_cached($s) {
		$md5 = md5($s);
		if(is_file(LILYPOND_CACHEDIR.$md5.'.png')) {
			return "<img src='".LILYPOND_WEBROOT."/$md5.png' alt='Music' />";
		} elseif(is_file(LILYPOND_CACHEDIR.$md5.'.txt')) {
			return join('', @file(LILYPOND_CACHEDIR.$md5.'.txt'));
		} else {
			if(!is_dir(LILYPOND_CACHEDIR)) mkdir(LILYPOND_CACHEDIR);
			$f = fopen(LILYPOND_CACHEDIR."$md5.ly", "w");
			fputs($f, $s, strlen($s));
			fclose($f);
			system("cd ".LILYPOND_CACHEDIR."; lilypond --png --preview $md5.ly > $md5.txt 2>&1" );
			system("cd ".LILYPOND_CACHEDIR."; mogrify -trim $md5*.png >> $md5.txt 2>&1" );
			return "<img src='".LILYPOND_WEBROOT."/$md5.png' alt='Music' />";
		}
		return "Music: ".md5($s)."<pre>$s</pre>";
	}
	
	class BracketLinkNaturalRegexCallback extends RegexCallback {
		function regex() {
			return '(["]\w[^"]{1,150}?[\w,.?!]["][,.!?]?|'.REGEX_PROPERNAME.')\s+'.
				'&lt;('.REGEX_URL.'|'."[^ \t\n]*".'|'.REGEX_WIKIWORD.'|'.REGEX_EMAIL.'(?:/'.REGEX_WIKIWORD.')*'.')&gt;|"('.REGEX_PROPERNAME."[\t ]".REGEX_PROPERNAME.')"';
		}
		function is_linker() { return true; }
		function callback($match, $source) {
			//var_dump($match); print("<br />");
			$e = $this->environment();
			if($match[1]) {
				$match[1] = trim($match[1], '"');
				$handlers = $e->text_decoration_markup_handlers();
				$handlers->remove_linkers();
				if(preg_match('!http:|mailto:|https:|gopher:!', $match[2])) {
					return($this->environment()->wiki_hyperlink($match[2], $handlers->run($match[1])));
				} elseif(preg_match('!^'.REGEX_EMAIL.'$!', $match[2])) {
					return($this->environment()->wiki_hyperlink(preg_replace('/\s/', '', 'mailto:'.$match[2]), $match[1]));
				} else {
					return($e->link_wikipage($match[2], $handlers->run($match[1])));
				}
			} elseif($match[3]) {
				if($match[3]{0} == '.') {
					return '<q>'.substr($match[3],1).'</q>';
				}
				if(preg_match('!http:|mailto:|https:|gopher:!', $match[3])) {
					return("<q>".$this->environment()->wiki_hyperlink(preg_replace('/\s/', '', $match[3]), $match[3])."</q>");
				} elseif(preg_match('!^'.REGEX_EMAIL.'$!', $match[3])) {
					return("<q>".$this->environment()->wiki_hyperlink(preg_replace('/\s/', '', 'mailto:'.$match[3]), $match[3])."</q>");

				} else {
					return("<q>".$e->link_wikipage(preg_replace('/\s/', '', $match[3]), $match[3])."</q>");
				}
			} else {
				return $match[0];
			}
		}
	}
	
	class BracketLinkRegexCallback extends RegexCallback {
		function regex() {
			return '[[](.*?)(?:\|(.*?))?[]]';
		}
		function is_linker() { return true; }
		function callback($match, $source) {
			$e = $this->environment();
			$h = $e->inline_markup_handlers();
			if(preg_match('/^ed:\s/i', $match[1])) {
				$match[1] = trim(substr($match[1], 3));
				return '['.$h->run($match[1]).($match[2] ? '|'.$match[2] : ''). ']';
			} elseif(preg_match('/^image:\s/i', $match[1])) {
				$m = explode(' ', $match[1], 3);
				return '<img src="'.$m[1].'" style="'.$m[2].'" alt="'.$m[1].'" />';
			} elseif((string)(int)$match[1] == $match[1]) {
				return '<sup><a href="#footnote-'.$match[1].'">'.$match[1].'</a></sup>';
			}
			if(isset($match[2]) and $match[2]) {
				return $this->environment()->wiki_hyperlink($match[1], $match[2]);
			} else { 
				return $this->environment()->wiki_hyperlink($match[1], $match[1]);
			}
		}
	}

	class FootnoteRegexCallback extends RegexCallback {
		function regex() {
			return '^\[([0-9]+)\](.+?)'.WIKI_PARAGRAPH_END;
		}
		function is_linker() { return true; }
		function callback($match, $source) {
			$e = $this->environment();
			$handlers = $e->inline_markup_handlers();
			return '<p><a name="footnote-'.$match[1].'"></a><sup>'.$match[1].'</sup>'.$handlers->run($match[2]).'</p>';
		}
	}

	
	class MadLibLinkRegexCallback extends RegexCallback {
		function regex() {
			return '[{](.*?)[}]';
		}
		function is_linker() { return true; }
		function callback($match, $source) {
			$e = $this->environment();
			return $e->link_wikipage($match[1], madlib_get_entry($match[1]));
		}
	}

	function madlib_get_entry($file) {
		$page = @wiki_load($file, 'Current');
		if(!$page['body']) return $file;
		$lines = explode("\n", $page['body']);
		while(!$line and ++$count < 10){
			$line = $lines[array_rand($lines)];
		}
		return htmlentities($line);
	}

	class InterwikiNaturalRegexCallback extends RegexCallback {
		function regex() {
			return '('.REGEX_WIKIWORD.')\s+\(?on\s+('.join(array_keys($this->wikilist()), '|').')\)?';
		}
		function is_linker() { return true; }
		function wikilist() {
			return array(
				'MeatBall' => '%s', 
				'TheOriginalWiki' => 'http://c2.com/cgi/wiki?%s',
				'NBTSCNomicWiki' => '%s', 
				'NomicWiki' => '%s', 
				'OldWiki' => '%s',
			);
		}
		function callback($match, $source) {
			$wl = $this->wikilist();
			return $this->environment()->wiki_hyperlink(sprintf($wl[$match[2]], $match[1]), $match[0]);
		}
	}

	class PersonalPageLinkRegexCallback extends RegexCallback {
		function regex() {
			/*return "(--|—)((?:[A-Z][a-z]+[ \t]??)+".
				"([ ]??[A-Z][a-z]+|[ ]??[A-Z]{1,2}[.]?)?)".
		  	"|((?:[A-Z][a-z]+[ \t]?)*[A-Z][a-z]+".
				"(?:[ \t][A-Z]{1,2}[.]?)?)('s [pP]age)".
			  "|(?<!ed )\bby((?:[ \t]+[A-Z][a-z]+)+)\b(?!\w)";
				*/
			return "(--)(".REGEX_PROPERNAME.")".
		  	"|(".REGEX_PROPERNAME.")((:?(?<=s)'|'s)\s+[pP]age)".
			  "|((?<!ed )\bby )(".REGEX_PROPERNAME.")";
		}
	
		function is_linker() { return true; }

		function callback($match, $source) {
			//var_dump($match); print("<br />");
			$e = $this->environment();
			if($match[1] == '--' and  $match[2]) {
				return $e->link_wikipage($match[2], '&#x2014;'.$match[2]);
			} elseif($match[3] and $match[4]) {
				return $e->link_wikipage($match[3], $match[3].$match[4]);
			} elseif($match[6] and $match[7]) {
				return $match[6].$e->link_wikipage($match[7], $match[7]);
			} else {
				return $match[0];
			}
		}
	}

	class ItalicRegexCallback extends RegexCallback {
		function regex() {
			return '\B/(.(?<!\s).*?)(?<!\s)/(?:\s+('.REGEX_URL_ENCAPS.')|(?=[ \t.;?!),]|$))';
		}
		function callback($match, $source) {
			$source->remove($this->hash());
			if(!isset($match[2])) $match[2] = '';
			if(trim($match[2])) $source->remove_linkers();
			return '<em>'.wiki_maybelink($source->run($match[1]), $match[2]).'</em>';
		}
	}
	
	class EmdashRegexCallback extends RegexCallback {
		function regex() {
			return '--';
		}
		function callback($match, $source) {
			return '&#x2014;';
		}
	}

	class BoldRegexCallback extends RegexCallback {
		function regex() {
			return '\B-((?!=\s|-).*?(?<!\s|-))-(?:\s+('.REGEX_URL_ENCAPS.')|\B)';
		}
		function callback($match, $source) {
			$source->remove($this->hash());
			if(!isset($match[2])) $match[2] = '';
			if(trim($match[2])) $source->remove_linkers();
			return wiki_maybelink('<strong>'.$source->run($match[1]).'</strong>', $match[2]);
		}
	}

	function wiki_maybelink($text, $url) {
		if(trim($url)) {
			return '<a href="'.preg_replace('/^&lt;(.*)&gt;$/', '\1', $url).'">'.$text.'</a>';
			
		} else {
			return $text;
		}
	}
	
	class UnderlineRegexCallback extends RegexCallback {
		function regex() {
			return '\b_(.(?<!\s).*?)(?<!\s)_(?:\s+('.REGEX_URL_ENCAPS.')|\b)';
		}
		function callback($match, $source) {
			if(trim(@$match[2])) $source->remove_linkers();
			$source->remove($this->hash());
			return wiki_maybelink($source->run($match[1]),@$match[2]);
		}
	}
	
	class QuoteRegexCallback extends RegexCallback {
		function regex() {
			return '\B"(.(?<!\s).*?)(?<!\s)"(?:\s+('.REGEX_URL_ENCAPS.')|\B)';
		}
		function callback($match, $source) {
			$source->remove($this->hash());
			if(!isset($match[2])) $match[2] = '';
			if(trim($match[2])) $source->remove_linkers();
			return wiki_maybelink('<q>'.$source->run($match[1]).'</q>', $match[2]);
		}
	}
	
	class SuperscriptRegexCallback extends RegexCallback {
		function regex() {
			return '\^(.(?<!\s).*?)(?<!\s)(\^|(?=[,. ]))';
		}
		function callback($match, $source) {
			$source->remove($this->hash());
			return '<sup>'.$source->run($match[1]).'</sup>';
		}
	}
	
	class MonospaceRegexCallback extends RegexCallback {
		function regex() {
			return '\B=([A-Z0-9a-z/-].+?)=\B';
		}
		function callback($match, $source) {
			$source->remove($this->hash());
			return '<code>'.$source->run($match[1]).'</code>';
		}
	}
	
	class PreformatRegexCallback extends RegexCallback {
		function regex() {
			return "(?:(?:(?<=\n)|^)[ \t][^\n]{2,}\n)+";
		}
		function callback($match, $source) {
			$e = $this->environment();
			$handlers = $e->inline_markup_handlers();
			$parts = explode("\n", $match[0]);
			foreach($parts as  $k => $part) {
				$parts[$k] = $handlers->run(
					wordwrap(substr($part, 1), 76, "\n    ", TRUE));
			}
			return '<pre>'.join("\n", $parts)."</pre>\n";
		}
	}
	
	class CodeRegexCallback extends RegexCallback {
		function regex() {
			return "(?:^class\s|^module\s|^def\s|^[^\n]+\sdo\s+[a-z|$]+\s).*?(?:^end[^\n]*)|(?:Ruby:\n(?:^\#[^\n]+(?:\n|$))+)|(?:&lt;\?php.*\?&gt;)";
		}
		function callback($match, $source) {
			$e = $this->environment();
			$descriptorspec = array(
				0 => array("pipe", "r"), 
				1 => array("pipe", "w"),
				2 => array("file", "/tmp/error-output.txt", "a")
			);
			$in = html_entity_decode($match[0]);
			if(preg_match("/<\?php/", $in)) {
				$lang = 'php3';
			} else {
				$lang = 'ruby';
			}

			return '<pre><code>'.htmlentities($in).'</code></pre>';
			$process = proc_open("source-highlight -t 3 -f xhtml -s $lang", $descriptorspec, $pipes);
			$o = '';
			if (is_resource($process)) {
				fwrite($pipes[0], $in);
				fclose($pipes[0]);

				while (!feof($pipes[1])) {
					$o .= fgets($pipes[1], 1024);
				}
				fclose($pipes[1]);
				$rv = proc_close($process);
				return "$o";
			}

			return '<p><small>[Error formatting code]</small></p><pre>'.$match[0]."</pre>\n";
		}
	}
	
	class WikiPoetryRegexCallback extends RegexCallback {
		function regex() {
			return "(?:((?:^|\n)[^\n]*)[ ]/)+(?:^|\n)[^\n]+";
			return "(?:(?:^|\n)[ \t]+/\n)+(?:(?:^|\n)+(?=\n|$))";
		}
		function callback($match, $source) {
			$e = $this->environment();
			$handlers = $e->inline_markup_handlers();
			$handlers->set_default(new TextDecorationMarkupRegexCallback());

			$parts = preg_split("#\s/\n#ms", $match[0]);

			foreach($parts as $k => $part) {
				$parts[$k] = $handlers->run($part);
			}

			$o = join("<br />\n", $parts);

			if($o = trim($o)) {
				return "<p class='poetry'>".join('<br />', $parts)."</p>";
			} else {
				return '';
			}
		}
	}
	class WikiParagraphRegexCallback extends RegexCallback {
		function regex() {
			return "[^ \t\n].*?".WIKI_PARAGRAPH_END;
		}
		function callback($match, $source) {
			$handlers = new RegexCallbacks();
			$handlers->set_environment($this->environment());
			if(WIKI_MADLIBLINKS) $handlers->add(new MadLibLinkRegexCallback());
			$handlers->add(new BracketLinkNaturalRegexCallback());
			$handlers->add(new BracketLinkRegexCallback());
			$handlers->add(new InterwikiNaturalRegexCallback());
			$handlers->add(new PersonalPageLinkRegexCallback());
			$handlers->add(new ThePageRegexCallback());
			$handlers->set_default(new TextDecorationMarkupRegexCallback());
			$handlers->add(new EmailRegexCallback());
			$handlers->add(new URLRegexCallback());
			//if(WIKI_WIKIWORDS) $handlers->add(new WikiWordRegexCallback());
			$o = $handlers->run($match[0]);
			$sum = num_ref($match[0], $this->environment->uniq);
			if(isset($this->environment()->notifies[$sum])) {
				$u = call_user_func($this->environment()->notifies[$sum], $match[0]);
			} else {
				$u = '';
			}
			if($o = trim($o)) {
				return "<p>$o".(WIKI_REPLY?$this->environment()->reply_link($match[0]):"")."</p>$u";
			} else {
				return $u;
			}
		}
	}

	class MultiRegexCallback extends RegexCallback {
		function regex() {
			$o = array();
			foreach($this->handlers() as $handler) {
				$o[get_class($handler)] = $handler->regex();
			}
			return join('|', $o);
		}
		function callback($match, $source) {
			if($this->defaulthandler()) {
				$default = $this->defaulthandler();
			} else {
				$default = NULL;
			}
			$handlers = $this->handlers();
			$handlers->set_default($default);
			return $handlers->run($match[0]);
		}
		function defaulthandler() {
			return FALSE;
		}
		function handlers() {
			$h = new RegexCallbacks();
			$h->set_environment($this->environment());
			return $h;
		}
	}

	class TextDecorationMarkupRegexCallback extends MultiRegexCallback {
		function handlers() {
			$e = $this->environment();
			$handlers = $e->text_decoration_markup_handlers();

			return $handlers;
		}
	}

	class HorizontalRuleRegexCallback extends RegexCallback {
		function regex() {
			return '^-----*\s*$';
		}
		function callback($match, $source) {
			return '<hr />';
		}
	}
	
	class WikiBlockQuoteRegexCallback extends RegexCallback {
		function regex() {
			return "(?:(?:^|\n)&gt;\s*([^\n]+)(?:\n|$))+";
		}
		function callback($match, $source) {
			$inside = preg_replace("/^&gt;[ \t]*/m", '', $match[0]);
			$e = $this->environment();
			$handlers = $e->block_markup_handlers();
			return('<blockquote>'.$handlers->run($inside).'</blockquote>');
		}
	}
	
	class IRCConversationRegexCallback extends RegexCallback {
		function regex() {
			$prefix = "(?:^|\n)";
			$prefix .= "(?:[][ 0-9:\t]{1,14})?";
			$re1 =  "$prefix(?:&lt;[^ \t]{1,20}&gt;([^\n]*\n|[^\n]+\w[^\n]+\n)+)";
			$re2 = "$prefix(?:[ \t]*[*][ \t]([^\n]+\n|[^\n]+\n\w[^\n]+\n)+)";
			$re3 = "$prefix(?:(?:&gt;|&lt;|-)-?(?:&gt;|&lt;|-)[ \t]*[^\n]+\n)";
			return "$re1(?:$re1|$re2|$re3)+";
		}
		function callback($match, $source) {
			$e = $this->environment();
			$handlers = $e->inline_markup_handlers(array('bracketlinks' => true));
			$parts = explode("\n", $match[0]);
			foreach($parts as $part) {
				$o .= $handlers->run($part)."<br />";
			}
			return('<p>'.$o.'</p>');
		}
	}
	
	class MIMEHeaderRegexCallback extends RegexCallback {
		function regex() {
			return "(?:(?:(?<=\n|^)(?:[A-Za-z0-9, ']+):(?:[ \t]+[^\n]+\n))+".
				"(?:(?<=\n|^)[ \t][^\n]+\n)*){2,}\n*";
		}
		function callback($match, $source) {
			$e = $this->environment();
			$handlers = $e->inline_markup_handlers();
			$data = preg_replace("/\n\s+/s", ' ', $match[0]);
			$parts = explode("\n", $data);
			$o = array();
			foreach($parts as $part) {
				if($part) {
					list($heading, $rest) = preg_split('/:\s+/', $part, 2);
					$o[] = "<tr><th>$heading:</th><td>".$handlers->run($rest)."</td></tr>";
				}
			}
			return('<table>'.join("\n", $o)."</table>");
		}
	}

	class WikiListRegexCallback extends RegexCallback {
		function regex() {
			return "(?:^|\n)\s*[*][^\n]+\n*(?:(?:\s|[*])[^\n]+)*";
		}
		function callback($match, $source) {
			return($this->wiki_render_list($match[0]));
		}
		
		function wiki_render_list($paragraph) {
			$temp = trim($paragraph);
			$lines = explode("\n", $paragraph);
			$indents = array();
			$ci = array('min' => 0, 'max' => 0);
			$tree =& new ListTree();
			$tree->set_environment($this->environment());
			$root =& $tree;

			foreach($lines as $line) {
				$i = strlen($line) - strlen(ltrim($line));
				$line = trim($line);
				if(strlen($line) and $line{0} == '*') {
					$bullet = $i + 1;
					$line = trim(substr($line, 1));
					$ti = $bullet;
				} else {
					$bullet = false;
					$ti = $i;
				}
				if(count($indents) == 0 and $ci['max'] == 0) $ci['max'] = $ti;
				if($ti > $ci['max']) {
					if($bullet) {
						$indents[] = $ci;
						$ci = array('min' => $ci['max'] + 1, 'max' => $bullet);
						$tree = $tree->add(new ListTree()); // add and descend
					} else {
						$ci['max'] = $ti;
					}
				} elseif($ti < $ci['min']) {
					while($ti < $ci['min'])  {
						$ci = array_pop($indents);
						if($tree->parent) $tree = $tree->parent;
					}
				}
				$line = trim($line)." ";
				if($line == " ") continue;
				if($bullet) {
					$tree->addcopy($line);
				} else {
					end($tree->children);
					if(is_object($tree->children[key($tree->children)])) {
						$tree->add($line);
					} else {
						$tree->children[key($tree->children)] .= $line;
					}
				}
			}

			$o = $root->as_string();
			return $o."\n";
		}
	}
	
	class WikiHeadingRegexCallback extends RegexCallback {
		function regex() {
			return "^[ \t]*-([^\n]+)-[ \t]*$";
		}
		function callback($match, $source) {
			$e = $this->environment();
			$handlers = $e->inline_markup_handlers();
			return('<h2>'.$handlers->run($match[1]).'</h2>');
		}
	}

	
	class ListTree {
		function ListTree() {
			$this->id = uniqid(get_class($this));
		}

		var $environment;
		function environment() {
			if(!is_object($this->environment)) {
				$this->environment = new WikiRenderer();
				print("?");
			}
			return $this->environment;
		}
		function set_environment($e) {
			$this->environment = $e;
		}
		
		var $children = array();
		function add($node) {
			if(is_object($node)) {
				$node->environment = $this->environment();
			}
			$this->children[] = $node;
			if(is_a($node, 'ListTree')) {
				$node->parent = $this;
			}
			return $node;
		}

		function addcopy($node) {
			if(is_object($node)) {
				$this->children[] = clone $node;
			} else {
				$this->children[] = $node;
			}
			if(is_a($node, 'ListTree')) {
				$node->parent = $this;
			}
			return $node;
		}

		function as_string() {
			$o = array();
			end($this->children);
			$lastkey = key($this->children);
			foreach($this->children as $key => $node) {
				if(is_object($node)) {
					$t = array_pop($o);
					$r = $node->as_string();
					$this->last_node = $r;
					$o[] = $t." ".$r;
				} else {
					$e = $this->environment();
					$handlers = $e->inline_markup_handlers();
					$t = $handlers->run($node);
					$this->last_node = $t;
					if(!$key == $lastkey) {
						num_ref($this->last_node, $this->environment->uniq);
						if(WIKI_REPLY) $this->environment->reply_link($this->last_node);
					}

					$o[] = $t;
					
					//$o[] = wiki_render_simple($node);
				}
			}
			if(count($o)) {
				$r = '';
				foreach($o as $v) {
					$r .= li($v)."\n";
				}
				$sum = num_ref($this->last_node, $this->environment->uniq);
				if(isset($this->environment->notifies[$sum])) {
					$u = call_user_func($this->environment->notifies[$sum], $this->last_node);
				} else {
					$u = '';
				}
				if(WIKI_REPLY) {
					return ul($r).$u.$this->environment->reply_link($this->last_node);
				} else {
					return ul($r).$u;
				}
			} else {
				return '';
			}
		}
	}

	class RegexCallbacks {
		var $handlers = array();
		var $default_handler = NULL;
		var $debug = FALSE;
		var $environment;

		function debug($newval) {
			$this->debug = $newval;
		}

		function add($handler) {
			$handler->set_environment($this->environment());
			$this->handlers[$handler->hash()] = $handler;
		}

		function set_default($handler) {
			$this->default_handler = $handler;
			if(is_object($handler)) $this->default_handler->set_environment($this->environment);
		}
		
		function set_environment($e) {
			$this->environment = $e;
			foreach($this->handlers as $handler) {
				$handler->set_environment($e);
			}
		}
		function environment() {
			return $this->environment;
		}

		function remove($key) {
			if(isset($this->handlers[$key])) {
				unset($this->handlers[$key]);
				return TRUE;
			} else {
				return FALSE;
			}
		}

		function remove_linkers() {
			$torm = Array();
			foreach($this->handlers as $handler) {
				if($handler->is_linker()) {
					$torm[] = $handler->hash();
				}
			}
			foreach($torm as $handler) {
				$this->remove($handler);
			}
		}

		/* This is where the magic happens.  We look at the megaeregex we've 
		 * received and take the sub-expressions, match them to their original
		 * callback, adjust the match array to be what the individual expression 
		 * might return after a match, and pass that to the callback, then
		 * collect the results and return that. Doing this in Ruby would be a third
		 * as many lines.
		 */
		function run($data) {
			if(!is_array($this->handlers) or count($this->handlers) == 0) {
				trigger_error("No handlers or invalid handlers", E_USER_ERROR);
			}
			foreach($this->handlers as $handler) {
				$handler->set_environment($this->environment());
			}
			$res = array();
			$subexphandlers = array();
			$reversemin = array();
			$reversemax = array();
			$offset = 0;
			if($this->debug) {
			 	print("<pre>"); 
				print_r($handlers); 
				print("</pre>");
			}
			foreach($this->handlers as $handler) {
				$t = preg_subexpressions($handler->regex()) + 1;
				for($i = 0; $i < $t; $i++) {
					$subexphandlers[$offset++] = $handler;
					$reversemax[get_class($handler)] = $offset - 1;
					if(!isset($reversemin[get_class($handler)])) 
						$reversemin[get_class($handler)] = $offset - 1;
				}
				$res[] = $handler->regex();
			}
			if($this->debug and FALSE) {
				print('<pre>');
				ob_start('htmlentities');
				print_r($res);
				ob_end_flush();
				print('</pre>');
			}
			$res = array_map(create_function('$a', 'return "($a)";'), $res);
			$re = '#('.join($res, '|').')#msu';

			if($this->debug and FALSE) {
				print("<pre>Offsets:\n");
				ob_start('htmlentities');
				print_r($subexphandlers);
				ob_end_flush();
				print('</pre>');
			}

			$o = '';
			// $debug = fopen('/tmp/regex', 'a'); fputs($debug, $re); fclose($debug);
			if(preg_match_all($re, $data, $matches, PREG_SET_ORDER)) {
				if($this->debug and FALSE) {
					print("<pre>Matches:\n");
					ob_start('htmlentities');
					print_r($matches);
					ob_end_flush();
					print('</pre>');
				}
				$parts = preg_split($re, $data);
				foreach($matches as $i => $match) {
					array_shift($match);
					array_shift($match);
					#$n = count($match) - 1;
					#$match = array_filter($match, create_function('$n', 'return $n;'));
					$n = 0;
					while(list($k, $v) = each($match)) {
						if($v) {
							break;
						} else {
							array_shift($match);
							$n++;
							continue;
						}
					}
					$h = $subexphandlers[$n];
					$s = $reversemin[get_class($h)];
					$e = $reversemax[get_class($h)];
					$l = $e - $s + 1;
					if($this->debug) {
						print('<pre>');print_r($match);print(' -&gt; '.get_class($h));
						print(" handler $n starts at $s and ends at $e </pre>");
						print("<br />\n");
					}
					if(is_object($h)) {
						$temppart = array_shift($parts);
						if(!is_null($this->default_handler)) {
							$temppart = 
								$this->default_handler->callback(array(0 => $temppart), $this);
						}
						$o .= $temppart.$h->callback($match, $this);
					}
				}
				while(count($parts)) {
					if(!is_null($this->default_handler)) {
						$o .= $this->default_handler->callback(
							array(0 => array_shift($parts)), $this);
					} else {
						$o .= array_shift($parts);
					}
				}
				return $o;
			} else {
				if(!is_null($this->default_handler)) {
					return $this->default_handler->callback(array(0 => $data), $this);
				} else {
					return $data;
				}
			}
		}
	}


	function preg_subexpressions($re) {
		return substr_count($re, '(') - 
			substr_count($re, '\\(') +
			substr_count($re, '\\(?') -
			substr_count($re, '(?');
	}

	$_wiki_render_sequence = 0;

	function wiki_render($data) {
		global $_wiki_render_sequence;
		$_wiki_render_sequence++;
		return wiki_render_with_notify($data, Array(), $_wiki_render_sequence);
	}

	function wiki_render_with_notify($data, $callbacks=Array(), $uniq = 1) {
		global $WIKI_PAGEDIR;
		$e = new RCSWikiRenderer();
		$e->uniq = $uniq;
		$e->set_notifies($callbacks);
		$e->set_pagedir($WIKI_PAGEDIR);
		return $e->run($data);
	}

	class WikiWikiRenderer extends WikiRenderer {
		function link_unknown_wikipage($page, $text) {
			return $this->htmllink($page.'?new', $text, array('class' => 'wikiwordunknown'));
		}

		function link_wikipage($page, $text = NULL) {
			if(is_null($text)) $text = $page;
			if(is_dir($this->pagedir.$page)) 
				return $this->link_known_wikipage($page,$text);
			if($this->pagedir and !file_exists($this->pagedir."/".$page)) {
				return $this->link_unknown_wikipage($page, $text);
			} else {
				return $this->link_known_wikipage($page, $text);
			}
		}

		function set_pagedir($d) {
			$this->pagedir = $d;
		}
		function set_notifies($c) {
			$this->notifies = $c;
		}
	}

	class RCSWikiRenderer extends WikiWikiRenderer {
		function link_known_wikipage($page, $text) {
			#$$e = $this->environment();
			if(is_dir($this->pagedir.$page)) return parent::link_known_wikipage($page, $text);
			$t = rcs_get_revisions($this->pagedir.$page);
			$realversion = key($t);
			return '<a href="'.pqurlencode($page).($realversion?";$realversion":'').
				'"'.($realversion ?  ' title="'.$text." v$realversion".'"': '').
				'>'.$text.'</a>';
			return $text;
		}
	}


	define('NUM_REF_N', 200);

	function num_ref($text, $uniq = 0) {
		static $h = Array();
		$text = trim($text);
		if(strlen($text) > NUM_REF_N) {
			$text = substr($text, -NUM_REF_N, NUM_REF_N);
		}
		if(!isset($h[$uniq])) $h[$uniq] = Array();
		if(isset($h[$uniq][$text])) {
			$h[$uniq][$text]++;
		} else {
			$h[$uniq][$text] = 1;
		}
		$r = $h[$uniq][$text].','.$text;
		return($r);
	}

	function sum_seq($text, $uniq = 1) {
		static $h = Array();
		$text = trim($text);
		if(!isset($h[$uniq])) $h[$uniq] = Array();
		$s = md5($text);
		if(isset($h[$uniq][$s])) {
			$h[$uniq][$s]++;
		} else {
			$h[$uniq][$s] = 1;
		}
		$r = $s.$h[$uniq][$s];
		return($r);
	}
?>
