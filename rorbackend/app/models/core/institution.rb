class Core::Institution < ActiveRecord::Base
  # Uncomment following lines if working with legacy database
  # set_table_name 'institucija'
  # set_primary_key :id
  # alias_attribute :id, :id
  # alias_attribute :name, :naziv
  # alias_attribute :parent, :roditelj
  # alias_attribute :short_name, :kratki_naziv

  # Uncomment following lines if working with legacy database
  # TABLE_NAME = 'institucija'
  # ID = TABLE_NAME + '.' + 'id'
  # NAME = TABLE_NAME + '.' + 'naziv'
  # PARENT = TABLE_NAME + '.' + 'roditelj'
  # SHORT_NAME = TABLE_NAME + '.' + kratki_naziv'

  # Comment following lines if working with legacy database
  TABLE_NAME = 'core_institutions'
  ID = TABLE_NAME + '.' + 'id'
  NAME = TABLE_NAME + '.' + 'name'
  PARENT = TABLE_NAME + '.' + 'parent'
  SHORT_NAME = TABLE_NAME + '.' + 'short_name'

  ALL_COLUMNS = [ID, NAME, PARENT, SHORT_NAME]
  
  has_many :programmes
  has_one :parent, :class_name => "Core::Institution"
  
  validates_presence_of :name, :short_name
  
end
