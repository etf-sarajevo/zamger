class Core::Ethnicity < ActiveRecord::Base
  # Uncomment following lines if working with legacy database
  # set_table_name 'nacionalnost'
  # set_primary_key :id
  # alias_attribute :id, :id
  # alias_attribute :name, :naziv

  # Uncomment following lines if working with legacy database
  # TABLE_NAME = 'nacionalnost'
  # ID = TABLE_NAME + '.' + 'id'
  # NAME = TABLE_NAME + '.' + 'naziv'

  # Comment following lines if working with legacy database
  TABLE_NAME = 'core_ethnicities'
  ID = TABLE_NAME + '.' + 'id'
  NAME = TABLE_NAME + '.' + 'name'

  ALL_COLUMNS = [ID, NAME]
  
  validates_presence_of :name
end
