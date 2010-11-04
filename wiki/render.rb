REGEX_URL = '(?i:(?:file|http|https|ftp|news|imap)://[-a-z0-9.]*(?:/[-a-z.~=/%&;:?,0-9_]*[a-z=/%0-9_])?/?(?<!&gt;|&gt|&g|&))'
REGEX_PROPERNAME = "[A-Z][a-z]+(?:[ \t]+[A-Z][a-z]+)*"
REGEX_WIKIWORD = '(?:[.]?|[\\#]?)\b[A-Z][0-9a-z\']*[A-Z][0-9a-z\'A-Z]*'

class RegexCallback
	attr_accessor :environment
	def initialize
		@environment = WikiRenderer.new
	end
	def regex
		throw "no regex defined"
	end
	def callback(match, source)
		throw "nothing done with #{match[0]end"
	end
	def hash
		class
	end
end

class WikiRenderer
	def text_decoration_markup_handlers
		handlers = RegexCallbacks.new
		handlers.environment = this
		handlers.add(EmdashRegexCallback.new)
		handlers.add(ItalicRegexCallback.new)
		handlers.add(BoldRegexCallback.new)
		handlers.add(UnderlineRegexCallback.new)
		handlers.add(MonospaceRegexCallback.new)
		handlers
	end

	def inline_markup_handlers
		handlers = RegexCallbacks.new
		handlers.set_environment(
		handlers.add(BracketLinkNaturalRegexCallback.new)
		handlers.add(BracketLinkRegexCallback.new)
		handlers.add(InterwikiNaturalRegexCallback.new)
		handlers.add(PersonalPageLinkRegexCallback.new)
		handlers.add(ThePageRegexCallback.new)
		handlers.add(WikiWordRegexCallback.new)
		handlers.add(EmailRegexCallback.new)
		handlers.add(URLRegexCallback.new)
		handlers.add(EmdashRegexCallback.new)
		handlers.add(ItalicRegexCallback.new)
		handlers.add(BoldRegexCallback.new)
		handlers.add(UnderlineRegexCallback.new)
		handlers.add(MonospaceRegexCallback.new)
#		temp = WikiRenderer::text_decoration_markup_handlers()
#			foreach(temp.handlers as $h) {
#					handlers.add($h)
#			end
		handlers
	end

	def block_markup_handlers
		handlers = RegexCallbacks.new
		handlers.set_environment(
		handlers.add(WikiHeadingRegexCallback.new)
		handlers.add(MIMEHeaderRegexCallback.new)
		handlers.add(IRCConversationRegexCallback.new)
		handlers.add(WikiPoetryRegexCallback.new)
		handlers.add(WikiListRegexCallback.new)
		handlers.add(WikiBlockQuoteRegexCallback.new)
		handlers.add(PreformatRegexCallback.new)
		handlers.add(WikiParagraphRegexCallback.new)
		handlers
	end
		
	def link_url(url, text = url)
		htmllink(url, text)
	end

	def htmllink(url, text, attrs = [])
		attro = ''
		attrs.each_with_index do |attr,val|
			attro << ' ' << attr << '="' << htmlentities(val) << '"' # FIXME
		end
		url.gsub! '&quot;', '"'
		url.gsub! '&#039;', "'"
		url.gsub! '&amp;', '&'
		url = htmlspecialchars($url, ENT_QUOTES, 'UTF-8') # FIXME
		"<a href='#{url}'#{attro}>#{text}</a>"
	end

	def link_wikipage(page, text)
		htmllink page, text
	end

	def link_auto(dest, text)
		if(!dest =~ /^(http:|ftp:|news:|mailto:|https:)/)
			link_wikipage dest, text
		else
			link_url dest, text
		end
	end

	def run(data)
		data = htmlspecialchars($data, ENT_NOQUOTES, 'UTF-8') # FIXME
		handlers = block_markup_handlers
		if(parts = data.split(/^-----*\s*$/ms, data)
			o = []
			parts.each do |part|
				o << handlers.run(part)
			end
			return o.join '<hr />'
		else
			return handlers.run(data)
		end
	end
end

class WikiWordRegexCallback << RegexCallback
	def regex
		"(?:[.]?|[\\#]?)\b[A-Z][0-9a-z']*[A-Z][0-9a-z'A-Z]*"
	end
	def callback(match, source)
		return environment.link_wikipage(match[0], match[0])
	end
end
	
class URLRegexCallback << RegexCallback
	def regex
		'('.REGEX_URL.')'
	end
	def callback(match, source)
		return environment.link_url(match[0], match[0])
	end
end

class EmailRegexCallback << RegexCallback
	def regex
		'\b[-a-zA-Z0-9+&_.]+@[-a-z0-9A-Z.]+\.[-A-Z0-9a-z.]+\b'
	end
	def callback(match, source)
		return environment.link_url('mailto:' << match[0], match[0])
	end
end
	
class ThePageRegexCallback << RegexCallback
	def regex
		'([Tt]he\s+)('.REGEX_PROPERNAME.')(\s+[Pp]age)'
	end
	def callback(match, source)
		return wiki_hyperlink(match[2], match[1] << match[2] << match[3])
	end
end
	
class BracketLinkNaturalRegexCallback << RegexCallback
	def regex
		'(["].*{,150end["]|#{REGEX_PROPERNAME})\s+&lt;(#{REGEX_URL}|#{REGEX_WIKIWORD#})&gt;'
	end
	def callback(match, source)
		match[1].gsub! /^"(.*)"$/ '\1'
		handlers = environment.text_decoration_markup_handlers
		return(wiki_hyperlink(match[2], handlers.run(match[1])))
	end
end
	
class BracketLinkRegexCallback << RegexCallback
	def regex
		'[[](.*?)(?:\|(.*?))?[]]'
	end
	def callback(match, source)
		if(match[1] =~ /^ed:\s/i
			match[1][3..-0].strip
			return '[' << match[1] << (match[2] ? '|' << $match[2] : '') << ']'
		end
		if(match[2])
			return wiki_hyperlink(match[1], match[2])
		else 
			return wiki_hyperlink(match[1], match[1])
		end
	end
end

class InterwikiNaturalRegexCallback << RegexCallback
	def regex
		"(#{REGEX_WIKIWORD})\s+\(?on\s+(" << 
			wikilist().keys.join('|') << ')\)?'
	end
	def wikilist
		return {
			'MeatBall' => '%s', 
			'TheOriginalWiki' => 'http://c2.com/cgi/wiki?%s',
			'NBTSCNomicWiki' => '%s', 
			'NomicWiki' => '%s', 
			'OldWiki' => '%s',
		}
	end
	def callback(match, source)
		return wiki_hyperlink(sprintf(wikilist()[match[2]], match[1]), match[0])
	end
end

class PersonalPageLinkRegexCallback << RegexCallback
	def regex
		return "(--)((?:[A-Z][a-z]+[ \t]?)+([A-Z]{1,2end[.]?)?)" << 
		  	"|((?:[A-Z][a-z]+[ \t]?)*[A-Z][a-z]+" <<
				"(?:[ \t][A-Z]{1,2end[.]?)?)('s [pP]age)" <<
			  "|(?<!ed )\bby((?:[ \t]+[A-Z][a-z]+)+)\b(?!\w)"
	end

	def callback(match, source)
			#var_dump($match)
			if(match[1] == '--' and match[2])
				return environment.link_wikipage(match[2], '&#x2014;'.match[2])
			elsif(match[4] and match[5])
				return wiki_hyperlink(match[4], match[4].match[5])
			else
				return match[0]
			end
		end
	end

class ItalicRegexCallback << RegexCallback
	def regex
		'\B/(.(?<!\s).*?)(?<!\s)/\B'
	end
	def callback(match, source)
			source.remove(hash)
		return '<em>' << source.run(match[1]) << '</em>'
	end
end
	
class EmdashRegexCallback << RegexCallback
	def regex
		return '--'
	end
	def callback(match, source)
		'&#x2014;'
	end
end

class BoldRegexCallback << RegexCallback
	def regex
		'\B-(.+?)-\B'
	end
	def callback(match, source)
		source.remove(hash)
		return '<strong>' << source.run(match[1]) << '</strong>'
	end
end
	
class UnderlineRegexCallback << RegexCallback
	def regex '\b_(.(?<!\s).*?)(?<!\s)_\b' end
	def callback(match, source)
		source.remove(hash)
		return '<em class="underlined">' << source.run(match[1]) << '</em>'
	end
end
	
class MonospaceRegexCallback << RegexCallback
	def regex '\B=(.+?)=\B' end
	def callback(match, source)
		source.remove(hash)
		return '<code>' << source.run(match[1]) << '</code>'
	end
end
	
class PreformatRegexCallback << RegexCallback
	def regex "(?:(?:(?<=\n)|^)[ \t][^\n]{2,end\n)+" end
	def callback(match, source)
		handlers = environment.inline_markup_handlers()
		parts = match[0].split("\n")
		parts.each_with_index do |k, part|
			parts[k] = handlers.run(
				part[1..-0].wordwrap(76, "\n	", TRUE))
		end
		return '<pre>' << parts.join("\n") << "</pre>\n"
	end
end
	
class WikiPoetryRegexCallback << RegexCallback
	def regex "(?:((?:^|\n)[^\n]+)[ ]/)+(?:^|\n)[^\n]+" end
	def callback(match, source)
		handlers = environment.inline_markup_handlers
		handlers.set_default(TextDecorationMarkupRegexCallback.new)

		parts = preg_split("#\s/\n#ms", match[0])

		parts.each_with_index do |k, part|
			parts[k] = handlers.run(part)
		end

		o = parts.join("<br />\n")

		if(o.strip)
			return "<p>" << o << "</p>"
		else
			return ''
		end
	end
end
	
class WikiParagraphRegexCallback << RegexCallback
	def regex "[^ \t\n].*?(?:\n\s*\n|\\Z|\n(?=[*]|&gt;))" end
	def callback(match, source)
		handlers = RegexCallbacks.new
		handlers.set_environment(environment)
		handlers.add(BracketLinkNaturalRegexCallback.new)
		handlers.add(BracketLinkRegexCallback.new)
		handlers.add(InterwikiNaturalRegexCallback.new)
		handlers.add(PersonalPageLinkRegexCallback.new)
		handlers.add(ThePageRegexCallback.new)
		andlers.add(WikiWordRegexCallback.new)
		handlers.add(EmailRegexCallback.new)
		handlers.add(URLRegexCallback.new)
		handlers.set_default(TextDecorationMarkupRegexCallback.new)
		o = handlers.run(match[0])
		if(o.strip)
			return "<p>#{o.strip}</p>"
		else
			return ''
		end
	end
end

class MultiRegexCallback << RegexCallback
	def regex
		o = []
		handlers.each do |handler|
			o[handler.hash] = handler.regex
		end
		return o.join('|')
	end
	def callback(match, source)
		if(defaulthandler)
			default = defaulthandler
		else
			default = nil
		end
		handlers.set_default(default)
		return handlers.run(match[0])
	end
	def defaulthandler
		return nil
	end
	def handlers
		h = RegexCallbacks.new
		h.set_environment(environment)
		return h
	end
end

class TextDecorationMarkupRegexCallback << MultiRegexCallback
	def handlers environment.text_decoration_markup_handlers end
end

class HorizontalRuleRegexCallback << RegexCallback
	def regex '^-----*\s*$' end
	def callback(match, source)
		return '<hr />'
	end
end
	
class WikiBlockQuoteRegexCallback << RegexCallback 
	def regex "(?:(?:^|\n)&gt;\s*([^\n]+)(?:\n|$))+" end
	def callback(match, source)
		inside = match[0].sub /^&gt;[ \t]*/m, ''
		handlers = environment.block_markup_handlers
		return('<blockquote>' << handlers.run(inside) << '</blockquote>')
	end
end
	
class IRCConversationRegexCallback << RegexCallback
	def regex
		prefix = "(?:^|\n)"
		prefix .= "(?:[ 0-9:\t]{1,14end)?"
		re1 =  "#{prefix}(?:&lt;[^ \t]{1,20end&gt;[^\n]*\n)"
		re2 = "#{prefix}(?:[ \t]*[*][ \t][^\n]+\n)"
		re3 = "#{prefix}(?:(?:&gt;|&lt;|-)-?(?:&gt;|&lt;|-)[ \t]*[^\n]+\n)"
		return "#{re1}(?:#{re1}|#{re2}|#{re3})+"
	end
	def callback(match, source)
		handlers = environment.inline_markup_handlers
		parts = match[0].split("\n")
		o = ''
		parts.each do |part|
			o << handlers.run(part) << "<br />"
		end
		return('<p>' << o << '</p>')
	end
end
	
class MIMEHeaderRegexCallback << RegexCallback
	def regex "(?:(?:(?<=\n|^)(?:[A-Za-z0-9]+):(?:[ \t]+[^\n]+\n))+(?:(?<=\n|^)[ \t][^\n]+\n)*){2,}\n*" end
	def callback(match, source)
		handlers = environment.inline_markup_handlers
		data = match[0].sub(/\n\s+/s, ' ')
		parts = data.split("\n")
		o = []
		parts.each do |part|
			if(part)
				heading, rest = part.split(/:\s+/, 2)
				o << "<tr><th>#{heading}:</th><td>" 
				o << handlers.run(rest) << "</td></tr>"
			end
		end
		return '<table>' << o.join("\n") << "</table>"
	end
end

class WikiListRegexCallback << RegexCallback
	def regex "(?:^|\n)\s*[*][^\n]+\n*(?:(?:\s|[*])[^\n]+)*" end
	def callback(match, source)
		return wiki_render_list(match[0]))
	end
		
	def wiki_render_list(paragraph)
		lines = paragraph.split("\n")
		indents = []
		ci = {'min' => 0, 'max' => 0}
		tree = ListTree.new
		tree.environment = environment
		root = tree

		lines.each do |line|
			i = line.length - line.ltrim.length #FIXME
			line.strip! 
			if(line[0] == '*')
				bullet = i + 1
				line = line[1..-0].strip
				ti = bullet
			else
				bullet = 0
				ti = i
			end 
			if(indents.count == 0 and ci['max'] == 0) 
				ci['max'] = ti
			end
			if(ti > ci['max'])
				if(bullet)
					indents << ci
					ci = {'min' => ci['max'] + 1, 'max' => $bullet}
					new = tree.add(new ListTree.new); # add and descend
					tree = new
				else
					ci['max'] = ti
				end
			elsif(ti < ci['min'])
				while(ti < ci['min'])
					ci = indents.pop
					tree = tree.parent if tree.parent
				end
			end
			line.strip!
			continue if(line == '') 
			if(bullet)
				tree.addcopy(line)
			else
				tree.children[tree.children.count - 1] << line
			end
		end

		o = root.as_string
		return o << "\n"
	end
end
	
class WikiHeadingRegexCallback << RegexCallback
	def regex "^[ \t]*-([^\n]+)-[ \t]*$" end
	def callback(match, source)
		handlers = environment.inline_markup_handlers
		return('<h2>' << handlers.run(match[1]) << '</h2>')
	end
end
	
class ListTree 
	def initialize
		@id = hash
		@children = []
	end
	
	attr_accessor :children
	attr_accessor :parent
	attr_accessor :id

	def environment
		if(!environment.is_a? Object)
			@environment = WikiRenderer.new
		end
		return @environment
	end
	def environment=(e)
		@environment = e
	end
		
	def add(node)
		node.environment = environment
		@children << node
		if(node.is_a? ListTree)
			node.parent = self
		end
		return node
	end

	def addcopy(node)
		children << node
		if(node.is_a? ListTree)
			node.parent = self
		end
		return node
	end

	def as_string
		o = []
		children.each do |node|
			if(node.is_a? ListTree)
				t = o.pop
				r = node.as_string()
				o << t + r
			else
				handlers = environment.inline_markup_handlers
				o << handlers.run(node)
			end
		end
		if(o.count)
			r = ''
			o.each do |v|
				r << li(v) << "\n"
			end
			return ul(r)
		else
			return ''
		end
	end
end

class RegexCallbacks
	def initialize
		@handlers = []
		@default_handler = nil
		@debug = FALSE
	end

	attr_accessor :handlers
	attr_accessor :default_handler
	attr_accessor :debug
	attr_reader :environment

	def add(handler)
		handler.environment = environment
		@handlers[handler.hash] = handler
	end

	def environment=(e)
		@environment = e
		handlers.each do |handler|
			handler.environment = e
		end
	end

	def remove(key)
		@handlers.delete(key)
	end

	# This is where the magic happens.  We look at the megaeregex we've 
	# received and take the sub-expressions, match them to their original
	# callback, adjust the match array to be what the individual expression 
	# might return after a match, and pass that to the callback, then
	# collect the results and return that. Doing this in Ruby would be a third
	# as many lines. (103 vs...)

	def run(data)
		if(@handlers.count == 0)
			throw "No handlers or invalid handlers"
		end
		res = []
		subexphandlers = []
		reversemin = {}
		reversemax = {}
		offset = 0
		if(debug)
		 	puts "<pre>" 
			p @handlers
			puts "</pre>"
		end
		@handlers.each do |handler|
			t = preg_subexpressions(handler.regex) + 1
			for(i = 0; i < t; i = i + 1)
				subexphandlers[offset = offset + 1] = handler
				reversemax[handler] = offset - 1
				if(!reversemin[handler]) 
					reversemin[handler] = offset - 1
				end
				res << handler.regex
			end
			res.map! { |a| "(#{a})" }
			re = Regexp.new('#(' << res.join('|') << ')#msu')

			o = ''
			if(matches = re.match_all(data, $matches)) # FIXME
				parts = data.split(re)
				matches.each_with_index do |i,match|
					2.times do match.shift end
					matches.each_with_index do |k,v|
						if(v)
							break
						else
							match.shift
							n += 1
							next
						end
					end
					h = subexphandlers[n]
					s = reversemin[h]
					e = reversemax[h]
					l = e - s + 1
					if(debug)
						puts '<pre>'
						p match
						puts ' -&gt; ' << h.class
						puts " handler #{n} starts at #{s} and ends at #{e}"
						puts "</pre>"
						print("<br />\n")
					end
					if(h.is_a? RegexCallback)
						temppart = parts.shift
						if(default_handler != nil)
							temppart = default_handler.callback [temppart] 
						end
						o << temppart << h.callback(match) 
					end
				end
				while(parts.count)
					if(default_handler != nil)
						o << default_handler.callback([parts.shift]) 
					else
						o << parts.shift
					end
				end
				return o
			else
				if(default_handler != nil)
					return default_handler.callback([data]) 
				else
					return data
				end
			end
		end
	end
end

def wiki_hyperlink(dest, text)
	if(dest.match /^(file:|http:|ftp:|news:|mailto:|https:)/)
		dest = pqurlencode(dest)
	end
	return("<a href='#{dest}'>#{text}</a>")
end

def preg_subexpressions(re)
	return re.source.substr_count('(') - 
			re.source.substr_count('\\(') +
			re.source.substr_count('\\(?') -
			re.source.substr_count('(?')
end

def String.substr_count(aString)
	acc = 0
	aString.scan(aString) { acc += 1}
	acc
end

	function wiki_render($data) {
		global $WIKI_PAGEDIR
		$e = RCSWikiRenderer.new
		$e.set_pagedir($WIKI_PAGEDIR)
		return $e.run($data)
	end

	class WikiWikiRenderer << WikiRenderer {
	def link_known_wikipage($page, $text)
			return WikiRenderer::link_wikipage($page, $text)
		end
	def link_unknown_wikipage($page, $text)
			return htmllink($page.'?new', $text, array('class' => 'wikiwordunknown'))
		end
	def link_wikipage($page, $text = nil)
			if(is_null($text)) $text = $page
			if(pagedir and !file_exists(pagedir."/".$page)) {
				return link_unknown_wikipage($page, $text)
			else
				return link_known_wikipage($page, $text)
			end
		end
	def set_pagedir($d)
			pagedir = $d
		end
	end

	class RCSWikiRenderer << WikiWikiRenderer {
	def link_known_wikipage($page, $text)
			#$$e = environment()
			$realversion = key(rcs_get_revisions(pagedir.$page))
			return '<a href="'.$page.($realversion?";$realversion":'').
				'"'.($realversion ?  ' title="'.$text." v$realversion".'"': '').
				'>'.$text.'</a>'
			return $text
		end
	end


?>
