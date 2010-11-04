#! /usr/bin/ruby

require 'date'

$: << File.dirname(__FILE__)
require 'filename'
require 'rcsfile'
require 'utilities'

class RCSFile::RCSVersion
	def changedlink(e)
		if prev.to_s != '1.0'
			if prev.to_s == '-1'
				"<a href='wiki/#{e.basename.urlencode.htmlencode}?new'>despammed</a>"
			else
				"<a href='wiki/#{e.basename.urlencode.htmlencode};#{prev}:#{self}'>changed</a>"
			end
		else
			"<a href='wiki/#{e.basename.urlencode.htmlencode};#{self}'>created</a>"
		end
	end
end


repository = ARGV[0]

if ARGV[1] == '--long'
	long = true
else
	long = false
end

if repository.nil? or repository.empty?
	$stderr.puts "usage: #{$0} repository [--long]\n"
	exit 1
end

dir = Dir.new(repository)
entries = dir.reject{|f| f[0,1] == '.' or f == 'RCS' }.collect { |f| RCSFile.new(dir.path + '/' + f) }.sort { |a,b| a.mtime <=> b.mtime }

lists = Hash.new { |h,k| h[k] = Array.new }
entries.each do |dent|
	lists[Date.civil(*(dent.mtime.strftime('%Y %m %d').split(' ').map{|s| s.to_i}))] << dent
end

count = 0
lists.keys.sort!.reverse_each do |k|
	puts "<h2>#{Date::MONTHNAMES[k.month]} #{k.day}, #{k.year}</h2>"
	puts "<ul>"
	lists[k].reverse_each do |e| 
		if e.directory?
			puts "<li><a href='wiki/#{e.basename.urlencode.htmlencode}/'>#{e.basename}/</a> #{if long then e.mtime.to_s else "" end}</li>" 
		else
			v = e.headversion
			puts "<li><a href='wiki/#{e.basename.urlencode.htmlencode};#{v}'>#{e.basename.gsub(/([A-Z])/, ' \1')}</a>, #{v.changedlink(e)} at #{e.mtime.strftime("%H:%M");}.</li>" 
		end
	end
	puts "</ul>"
	break if (count += 1) > 7
end
