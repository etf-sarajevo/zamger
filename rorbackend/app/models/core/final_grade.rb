class Core::FinalGrade < ActiveRecord::Base
  # Uncomment following lines if working with legacy database
  # set_table_name 'konacna_ocjena'
  # set_primary_key :student, :predmet
  # alias_attribute :student_id, :student
  # alias_attribute :course_unit_id, :predmet
  # alias_attribute :academic_year_id, :akademska_godina
  # alias_attribute :grade, :ocjena
  # alias_attribute :date, :datum
  # alias_attribute :document_id, :odluka

  # Uncomment following lines if working with legacy database
  # TABLE_NAME = 'konacna_ocjena'
  # STUDENT_ID = TABLE_NAME + '.' + 'student'
  # COURSE_UNIT_ID = TABLE_NAME + '.' + 'predmet'
  # ACADEMIC_YEAR_ID = TABLE_NAME + '.' + 'akademska_godina'
  # GRADE = TABLE_NAME + '.' + 'ocjena'
  # DATE = TABLE_NAME + '.' + 'datum'
  # DOCUMENT_ID = TABLE_NAME + '.' + 'odluka'

  # Comment following lines if working with legacy database
  TABLE_NAME = 'core_final_grades'
  STUDENT_ID = TABLE_NAME + '.' + 'student_id'
  COURSE_UNIT_ID = TABLE_NAME + '.' + 'course_unit_id'
  ACADEMIC_YEAR_ID = TABLE_NAME + '.' + 'academic_year_id'
  GRADE = TABLE_NAME + '.' + 'grade'
  DATE = TABLE_NAME + '.' + 'date'
  DOCUMENT_ID = TABLE_NAME + '.' + 'document_id'

  ALL_COLUMNS = [STUDENT_ID, COURSE_UNIT_ID, ACADEMIC_YEAR_ID, GRADE, DATE, DOCUMENT_ID]
  
  belongs_to :student
  belongs_to :course_unit
  belongs_to :academic_year
  has_one :document
  
  validates_inclusion_of :grade, :in => [5, 6, 7, 8, 9, 10]


end
