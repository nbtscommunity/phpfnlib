
class String
	def htmlencode
		self.gsub('&', '&amp;').gsub("'", "&apos;").gsub('"', '&quot;').gsub('<', '&lt;').gsub('>', '&gt;')
	end

	def urlencode
		self.gsub("'", '%27')
	end
end
