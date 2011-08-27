class Core::CourseUnit < ActiveRecord::Base
  # Uncomment following lines if working with legacy database
  # set_table_name 'predmet'
  # set_primary_key :id
  # alias_attribute :id, :id
  # alias_attribute :code, :sifra
  # alias_attribute :name, :naziv
  # alias_attribute :short_name, :kratki_naziv
  # alias_attribute :institution_id, :institucija
  # alias_attribute :course_unit_type_id, :tippredmeta
  # alias_attribute :ects, :ects

  # Uncomment following lines if working with legacy database
  # TABLE_NAME = 'predmet'
  # ID = TABLE_NAME + '.' + 'id'
  # CODE = TABLE_NAME + '.' + 'sifra'
  # NAME = TABLE_NAME + '.' + 'naziv'
  # SHORT_NAME = TABLE_NAME + '.' + 'kratki_naziv'
  # INSTITUTION_ID = TABLE_NAME + '.' + 'institucija'
  # COURSE_UNIT_TYPE_ID = TABLE_NAME + '.' + 'tippredmeta'
  # ECTS = TABLE_NAME + '.' + 'ects'

  # Comment following lines if working with legacy database
  TABLE_NAME = 'core_course_units'
  ID = TABLE_NAME + '.' + 'id'
  CODE = TABLE_NAME + '.' + 'code'
  NAME = TABLE_NAME + '.' + 'name'
  SHORT_NAME = TABLE_NAME + '.' + 'short_name'
  INSTITUTION_ID = TABLE_NAME + '.' + 'institution_id'
  COURSE_UNIT_TYPE_ID = TABLE_NAME + '.' + 'course_unit_type_id'

  ALL_COLUMNS = [ID, CODE, NAME, SHORT_NAME, INSTITUTION_ID, COURSE_UNIT_TYPE_ID]
  
  has_many :course_unit_years
  has_many :course_offerings
  has_many :portfolios
  has_many :groups, :class_name => "Lms::Attendance::Group"
  has_many :exams, :class_name => "Lms::Exam::Exam"
  has_many :course_unit_years
  has_many :course_unit_type_scoring_elements
  belongs_to :course_unit_type
  has_many :final_grades
  
  validates_presence_of :core, :name, :short_name, :course_unit_type_id, :ects
  
end
