class Hrm::Ensemble::Domain < ActiveRecord::Base
  # Uncomment following lines if working with legacy database
  # set_table_name 'oblast'
  # set_primary_key :id
  # alias_attribute :id, :id
  # alias_attribute :institution_id, :institucija
  # alias_attribute :name, :naziv

  # Uncomment following lines if working with legacy database
  # TABLE_NAME = 'oblast'
  # ID = TABLE_NAME + '.' + 'id'
  # INSTITUTION_ID = TABLE_NAME + '.' + 'institucija'
  # NAME = TABLE_NAME + '.' + 'naziv'

  # Comment following lines if working with legacy database
  TABLE_NAME = 'hrm_ensemble_domains'
  ID = TABLE_NAME + '.' + 'id'
  INSTITUTION_ID = TABLE_NAME + '.' + 'institution_id'
  NAME = TABLE_NAME + '.' + 'name'

  ALL_COLUMNS = [ID, INSTITUTION_ID, NAME]
  
  
  belongs_to :institution, :class_name => "Core::Institution"
  has_many :subdomains
end
