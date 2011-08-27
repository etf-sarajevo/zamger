class Core::Curriculum < ActiveRecord::Base
  # Uncomment following lines if working with legacy database
  # set_table_name 'plan_studija'
  # alias_attribute :for_year, :godina_vazenja
  # alias_attribute :programme_id, :studij
  # alias_attribute :semester, :semestar
  # alias_attribute :course_unit_id, :predmet
  # alias_attribute :mandatory, :obavezan

  # Uncomment following lines if working with legacy database
  # TABLE_NAME = 'plan_studija'
  # FOR_YEAR = TABLE_NAME + '.' + 'godina_vazenja'
  # PROGRAMME_ID = TABLE_NAME + '.' + 'studij'
  # SEMESTER = TABLE_NAME + '.' + 'semestar'
  # COURSE_UNIT_ID = TABLE_NAME + '.' + 'predmet'
  # MANDATORY = TABLE_NAME + '.' + 'obavezan'

  # Comment following lines if working with legacy database
  TABLE_NAME = 'core_curriculums'
  FOR_YEAR = TABLE_NAME + '.' + 'for_year'
  PROGRAMME_ID = TABLE_NAME + '.' + 'programme_id'
  SEMESTER = TABLE_NAME + '.' + 'semester'
  COURSE_UNIT_ID = TABLE_NAME + '.' + 'course_unit_id'
  MANDATORY = TABLE_NAME + '.' + 'mandatory'

  ALL_COLUMNS = [FOR_YEAR, PROGRAMME_ID, SEMESTER, COURSE_UNIT_ID, MANDATORY]
  
  has_many :enrollments
  belongs_to :academic_year, :foreign_key => "for_year", :class_name => "Core::AcademicYear"
  belongs_to :programme
  belongs_to :course_unit
  validates_presence_of :for_year, :programme_id, :semester, :course_unit_id, :mandatory
  
end
