class Core::ScienceLevel < ActiveRecord::Base
  # Uncomment following lines if working with legacy database
  # set_table_name 'naucni_stepen'
  # set_primary_key :id
  # alias_attribute :id, :id
  # alias_attribute :name, :naziv
  # alias_attribute :title, :titula

  # Uncomment following lines if working with legacy database
  # TABLE_NAME = 'naucni_stepen'
  # ID = TABLE_NAME + '.' + 'id'
  # NAME = TABLE_NAME + '.' + 'naziv'
  # TITLE = TABLE_NAME + '.' + 'titula'
 
  # Comment following lines if working with legacy database
  TABLE_NAME = 'core_science_levels'
  ID = TABLE_NAME + '.' + 'id'
  NAME = TABLE_NAME + '.' + 'name'
  TITLE = TABLE_NAME + '.' + 'title'

  ALL_COLUMNS = [ID, NAME, TITLE]
  
  validates_presence_of :name, :title
end
