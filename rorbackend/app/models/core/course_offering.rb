class Core::CourseOffering < ActiveRecord::Base
  # Uncomment following lines if working with legacy database
  # set_table_name 'ponudakursa'
  # set_primary_key :id
  # alias_attribute :id, :id
  # alias_attribute :course_unit_id, :predmet
  # alias_attribute :academic_year_id, :akademska_godina
  # alias_attribute :programme_id, :studij
  # alias_attribute :semester, :semestar
  # alias_attribute :mandatory, :obavezan

  # Uncomment following lines if working with legacy database
  # TABLE_NAME = 'ponudakursa'
  # ID = TABLE_NAME + '.' + 'id'
  # COURSE_UNIT_ID = TABLE_NAME + '.' + 'predmet'
  # ACADEMIC_YEAR_ID = TABLE_NAME + '.' + 'akademska_godina'
  # PROGRAMME_ID = TABLE_NAME + '.' + 'studij'
  # SEMESTER = TABLE_NAME + '.' + 'semestar'
  # MANDATORY = TABLE_NAME + '.' + 'obavezan'

  # Comment following lines if working with legacy database
  TABLE_NAME = 'core_course_offerings'
  ID = TABLE_NAME + '.' + 'id'
  COURSE_UNIT_ID = TABLE_NAME + '.' + 'course_unit_id'
  ACADEMIC_YEAR_ID = TABLE_NAME + '.' + 'academic_year_id'
  PROGRAMME_ID = TABLE_NAME + '.' + 'programme_id'
  SEMESTER = TABLE_NAME + '.' + 'semester'
  MANDATORY = TABLE_NAME + '.' + 'mandatory'

  ALL_COLUMNS = [ID, COURSE_UNIT_ID, ACADEMIC_YEAR_ID, PROGRAMME_ID, SEMESTER, MANDATORY]
  
  belongs_to :course_unit
  belongs_to :academic_year
  belongs_to :programme
  has_many :portfolios
  has_many :scoring_element_scores
  validates_presence_of :course_unit_id, :academic_year_id, :programme_id, :semester, :mandatory
  
  
  def self.get_courses_offered(academic_year_id, programme_id, semester)
    query_parameters = {}
    query_parameters["academic_year_id"] = academic_year_id if academic_year_id != '0'
    query_parameters["programme_id"] = :programme_id if programme_id != '0'
    query_parameters["semester"] = semester if semester != '0'
    
    select_columns = [(Core::CourseOffering)::ID, (Core::CourseOffering)::COURSE_UNIT_ID, (Core::CourseOffering)::ACADEMIC_YEAR_ID, (Core::CourseOffering)::PROGRAMME_ID, (Core::CourseOffering)::SEMESTER, (Core::CourseOffering)::MANDATORY, (Core::CourseUnit)::NAME, (Core::CourseUnit)::CODE]
    courses_offered = (Core::CourseOffering).where(query_parameters).joins(:course_unit).select(select_columns)
    
    return courses_offered
  end
end
