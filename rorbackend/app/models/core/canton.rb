class Core::Canton < ActiveRecord::Base
  # Uncomment following lines if working with legacy database
  # set_table_name 'kanton'
  # set_primary_key :id
  # alias_attribute :id, :id
  # alias_attribute :name, :naziv
  # alias_attribute :short_name, :kratki_naziv


  # Uncomment following lines if working with legacy database
  # TABLE_NAME = 'kanton'
  # ID = TABLE_NAME + '.' + 'id'
  # NAME = TABLE_NAME + '.' + 'naziv'
  # SHORT_NAME = TABLE_NAME + '.' + 'kratki_naziv'

  # Comment following lines if working with legacy database
  TABLE_NAME = 'core_cantons'
  ID = TABLE_NAME + '.' + 'id'
  NAME = TABLE_NAME + '.' + 'name'
  SHORT_NAME = TABLE_NAME + '.' + 'short_name'

  ALL_COLUMNS = [ID, NAME, SHORT_NAME]
  
  validates_presence_of :name, :short_name
end
