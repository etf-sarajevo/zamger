class Hrm::Ensemble::Rank < ActiveRecord::Base
  # Uncomment following lines if working with legacy database
  # set_table_name 'zvanje'
  # set_primary_key :id
  # alias_attribute :id, :id
  # alias_attribute :name, :naziv
  # alias_attribute :title, :titula

  # Uncomment following lines if working with legacy database
  # TABLE_NAME = 'zvanje'
  # ID = TABLE_NAME + '.' + 'id'
  # NAME = TABLE_NAME + '.' + 'naziv'
  # TITLE = TABLE_NAME + '.' + 'titula'

  # Comment following lines if working with legacy database
  TABLE_NAME = 'hrm_ensemble_ranks'
  ID = TABLE_NAME + '.' + 'id'
  NAME = TABLE_NAME + '.' + 'name'
  TITLE = TABLE_NAME + '.' + 'title'

  ALL_COLUMNS = [ID, NAME, TITLE]
  
end
