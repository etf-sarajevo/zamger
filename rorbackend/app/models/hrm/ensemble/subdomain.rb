class Hrm::Ensemble::Subdomain < ActiveRecord::Base
  # Uncomment following lines if working with legacy database
  # set_table_name 'podoblast'
  # set_primary_key :id
  # alias_attribute :id, :id
  # alias_attribute :domain_id, :oblast
  # alias_attribute :name, :naziv

  # Uncomment following lines if working with legacy database
  # TABLE_NAME = 'podoblast'
  # ID = TABLE_NAME + '.' + 'id'
  # DOMAIN_ID = TABLE_NAME + '.' + 'oblast'
  # NAME = TABLE_NAME + '.' + 'naziv'

  # Comment following lines if working with legacy database
  TABLE_NAME = 'hrm_ensemble_subdomains'
  ID = TABLE_NAME + '.' + 'id'
  DOMAIN_ID = TABLE_NAME + '.' + 'domain_id'
  NAME = TABLE_NAME + '.' + 'name'

  ALL_COLUMNS = [ID, DOMAIN_ID, NAME]
  
  
  belongs_to :domain
end
