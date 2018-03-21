require 'csv'
require 'erb'

def rows
  CSV.parse(File.read('tablet-compatability.csv'))
end

def template
  File.read('template.erb')
end

class CsvToTable
  include ERB::Util
  attr_accessor :rows, :template

  def initialize(rows, template)
    @rows = rows
    @template = template
  end

  def render
    ERB.new(@template).result(binding)
  end

  def save(file_path)
    File.write(file_path, render)
  end
end

table_out = CsvToTable.new(rows, template)
table_out.save('tablet-compatability.html')
