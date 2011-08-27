class Core::Enrollment < ActiveRecord::Base
  # Uncomment following lines if working with legacy database
  # set_table_name 'student_studij'
  # set_primary_key :student, :studij, :semestar, :akademska godina
  # alias_attribute :student_id, :student
  # alias_attribute :programme_id, :studij
  # alias_attribute :semester, :semestar
  # alias_attribute :academic_year_id, :akademska_ godina
  # alias_attribute :enrollment_type_id, :nacin_studiranja
  # alias_attribute :repeat, :ponovac
  # alias_attribute :curriculm_id, :plan_studija
  # alias_attribute :document_id, :odluka

  # Uncomment following lines if working with legacy database
  # TABLE_NAME = 'student_studij'
  # STUDENT_ID = TABLE_NAME + '.' + 'student'
  # PROGRAMME_ID = TABLE_NAME + '.' + 'studij'
  # SEMESTER = TABLE_NAME + '.' + 'semestar'
  # ACADEMIC_YEAR_ID = TABLE_NAME + '.' + 'akademska_godina'
  # ENROLLMENT_TYPE_ID = TABLE_NAME + '.' + 'nacin_studiranja'
  # REPEAT = TABLE_NAME + '.' + 'ponovac'
  # CURRICULM_ID = 'plan_studija'
  # DOCUMENT_ID = 'odluka'

  # Comment following lines if working with legacy database
  TABLE_NAME = 'core_enrollments'
  STUDENT_ID = TABLE_NAME + '.' + 'student_id'
  PROGRAMME_ID = TABLE_NAME + '.' + 'programme_id'
  SEMESTER = TABLE_NAME + '.' + 'semester'
  ACADEMIC_YEAR_ID = TABLE_NAME + '.' + 'academic_year_id'
  ENROLLMENT_TYPE_ID = TABLE_NAME + '.' + 'enrollment_type_id'
  REPEAT = TABLE_NAME + '.' + 'repeat'
  CURRICULM_ID = TABLE_NAME + '.' + 'curriculm_id'
  DOCUMENT_ID = TABLE_NAME + '.' + 'document_id'

  ALL_COLUMNS = [STUDENT_ID, PROGRAMME_ID, SEMESTER, ACADEMIC_YEAR_ID, ENROLLMENT_TYPE_ID, REPEAT, CURRICULM_ID, DOCUMENT_ID]
  
  belongs_to :student
  belongs_to :programme
  belongs_to :academic_year
  belongs_to :curriculum
  belongs_to :document
  has_one :enrollment_type
  
  validates_presence_of :student_id, :programme_id, :academic_year_id, :enrollment_type_id
  
  
  def self.get_current_for_student(student_id)
    current_year = (Core::AcademicYear).current_year  # Current academic year
    raise ActiveRecord::RecordNotFound if (current_year == nil)
    enrollment = (Core::Enrollment).where(:student_id => student_id, :academic_year_id => current_year.id).order(:semester + " DESC").first
      
      return enrollment
  end
  
  
  def self.get_all_for_student(student_id)
    enrollments = (Core::Enrollment).where(:student_id => student_id).order(:academic_year_id, :semester)
    
    return enrollments
  end
end
