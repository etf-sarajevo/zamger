class Core::Place < ActiveRecord::Base
  # Uncomment following lines if working with legacy database
  # set_table_name 'mjesto'
  # set_primary_key :id
  # alias_attribute :id, :id
  # alias_attribute :name, :naziv
  # alias_attribute :municipality_id, :opcina

  # Uncomment following lines if working with legacy database
  # TABLE_NAME = 'mjesto'
  # ID = TABLE_NAME + '.' + 'id'
  # NAME = TABLE_NAME + '.' + 'naziv'
  # MUNICIPALITY_ID = TABLE_NAME + '.' + 'opcina'
  # COUNTRY_ID = TABLE_NAME + '.' + 'drzava'

  # Comment following lines if working with legacy database
  TABLE_NAME = 'core_places'
  ID = TABLE_NAME + '.' + 'id'
  NAME = TABLE_NAME + '.' + 'name'
  MUNICIPALITY_ID = TABLE_NAME + '.' + 'municipality_id'
  COUNTRY_ID = TABLE_NAME + '.' + 'country_id'

  ALL_COLUMNS = [ID, NAME, MUNICIPALITY_ID, COUNTRY_ID]
  
  belongs_to :municipality
  belongs_to :country
  
  validates_presence_of :name, :municipality, :country
end
