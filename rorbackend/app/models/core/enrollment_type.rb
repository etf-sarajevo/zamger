class Core::EnrollmentType < ActiveRecord::Base
  # Uncomment following lines if working with legacy database
  # set_table_name 'nacin_studiranja'
  # set_primary_key :id
  # alias_attribute :id, :id
  # alias_attribute :name, :naziv

  # Uncomment following lines if working with legacy database
  # TABLE_NAME = 'nacin_studiranja'
  # ID = TABLE_NAME + '.' + 'id'
  # NAME = TABLE_NAME + '.' + 'naziv'

  # Comment following lines if working with legacy database
  TABLE_NAME = 'core_enrollment_types'
  ID = TABLE_NAME + '.' + 'id'
  NAME = TABLE_NAME + '.' + 'name'

  ALL_COLUMNS = [ID, NAME]
  
  validates_presence_of :name
end
