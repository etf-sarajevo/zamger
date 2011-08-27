class Hrm::Ensemble::EngagementStatus < ActiveRecord::Base
  # Uncomment following lines if working with legacy database
  # set_table_name 'angazman_status'
  # set_primary_key :id
  # alias_attribute :id, :id
  # alias_attribute :name, :naziv

  # Uncomment following lines if working with legacy database
  # TABLE_NAME = 'angazman_status'
  # ID = TABLE_NAME + '.' + 'id'
  # NAME = TABLE_NAME + '.' + 'naziv'

  # Comment following lines if working with legacy database
  TABLE_NAME = 'hrm_ensemble_engagement_statuses'
  ID = TABLE_NAME + '.' + 'id'
  NAME = TABLE_NAME + '.' + 'name'

  ALL_COLUMNS = [ID, NAME]

  validates_presence_of :name
end
