
require 'filename'

class RCSFile < FileName

	class RCSVersion
		def initialize(str)
			@str = str
		end
		def to_s
			@str
		end
		def prev
			parts = @str.split('.')
			last = parts.pop.to_i
			last -= 1
			parts << last
			self.class.new(parts.join('.'))
		end
	end
	def headversion
		rcsfile = File.dirname(path) + '/RCS/' + basename + ',v'
		in_retry = false
		begin
			File.open(rcsfile) do |f|
				return RCSVersion.new(/head\s+(.*);/.match(f.gets)[1])
			end
		rescue 
			if(!in_retry)
				in_retry = true
				rcsfile = path + ',v'
				retry
			end
		end
		return RCSVersion.new('1.1')
	end
end
