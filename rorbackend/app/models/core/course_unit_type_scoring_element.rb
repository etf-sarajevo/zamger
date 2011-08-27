class Core::CourseUnitTypeScoringElement < ActiveRecord::Base
  # Uncomment following lines if working with legacy database
  # set_table_name 'tippredmeta_komponenta'
  # alias_attribute :course_unit_type_id, :tippredmeta
  # alias_attribute :scoring_element_id, :komponenta

  # Uncomment following lines if working with legacy database
  # TABLE_NAME = 'tippredmeta_komponenta'
  # COURSE_UNIT_ID = TABLE_NAME + '.' + 'tippredmeta'
  # SCORING_ELEMENT_ID = TABLE_NAME + '.' + 'komponenta'
  
  # Comment following lines if working with legacy database
  TABLE_NAME = 'core_course_unit_type_scoring_elements'
  COURSE_UNIT_TYPE_ID = TABLE_NAME + '.' + 'course_unit_type_id'
  SCORING_ELEMENT_ID = TABLE_NAME + '.' + 'scoring_element_id'

  ALL_COLUMNS = [COURSE_UNIT_TYPE_ID, SCORING_ELEMENT_ID]
  
  
  belongs_to :course_unit_type
  belongs_to :scoring_element
end
