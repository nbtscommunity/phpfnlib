#! /usr/bin/ruby

require 'date'

$: << File.dirname(__FILE__)
require 'filename'
require 'rcsfile'
require 'utilities'

repository = ARGV[0]
selfname = ARGV[1]
searchterms = ARGV[2..-1]

if repository.nil? or repository.empty? or searchterms.nil? or searchterms.empty? or selfname.nil? or selfname.empty?
	$stderr.puts "usage: #{$0} repository selfuri searchterms\n"
	exit 1
end

searchterms = searchterms.map {|f| Regexp.new(f)}

dir = Dir.new(repository)
answers = []
entries = dir.reject{|f| f[0,1] == '.' or f == 'RCS' }.each do |f| 
	begin
		text = IO.readlines(File.join(repository, f))
		a = ''
		flag = true
		searchterms.each do |term| 
			a = text.grep(term)
			if a.nil? or a.empty?
				flag = false
			end
		end
		answers << [f, a] if flag
	rescue 
		next
	end
end

count = 0
answers.each do |r|
		key, val = *r
	if !val.nil? and !key.nil?
		puts "<h2><a href='#{selfname.htmlencode}/#{key.htmlencode}'>#{key}</a></h2>"
		puts "<p>#{val.inject("") { |m,n| m + n }.htmlencode }</p>"
	end
end
