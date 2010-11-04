
class FileName
	def initialize(path)
		@path = path
		@stat = File.stat(path)
	end
	attr_reader :path, :stat
	def mtime
		stat.mtime
	end
	def basename
		File.basename(@path)
	end
	def directory?
		File.directory?(@path)
	end
end
