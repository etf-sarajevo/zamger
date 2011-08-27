class Core::CourseUnitType < ActiveRecord::Base
  # Uncomment following lines if working with legacy database
  # set_table_name 'tippredmeta'
  # set_primary_key :id
  # alias_attribute :id, :id
  # alias_attribute :name, :naziv

  # Uncomment following lines if working with legacy database
  # TABLE_NAME = 'tippredmeta'
  # ID = TABLE_NAME + '.' + 'id'
  # NAME = TABLE_NAME + '.' + 'naziv'

  # Comment following lines if working with legacy database
  TABLE_NAME = 'core_course_unit_types'
  ID = TABLE_NAME + '.' + 'id'
  NAME = TABLE_NAME + '.' + 'name'

  ALL_COLUMNS = [ID, NAME]
  
  has_many :course_unit_type_scoring_elements
  has_many :course_unit_years
  
  validates_presence_of :name
  validates_length_of :name, :maximum => 50
end
