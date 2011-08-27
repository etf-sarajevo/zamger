class Hrm::Ensemble::Engagement < ActiveRecord::Base
  # Uncomment following lines if working with legacy database
  # set_table_name 'angazman'
  # set_primary_key :predmet, :akademska_godina, :osoba
  # alias_attribute :course_unit_id, :predmet
  # alias_attribute :academic_year_id, :akademska_godina
  # alias_attribute :person_id, :osoba
  # alias_attribute :engagement_status_id, :angazman_status

  # Uncomment following lines if working with legacy database
  # TABLE_NAME  'angazman'
  # COURSE_UNIT_ID = TABLE_NAME + '.' + 'predmet'
  # ACADEMIC_YEAR_ID = TABLE_NAME + '.' + 'akademska_godina'
  # PERSON_ID = TABLE_NAME + '.' + 'osoba'
  # ENGAGEMENT_STATUS_ID = TABLE_NAME + '.' + 'angazman_status'

  # Comment following lines if working with legacy database
  TABLE_NAME = 'hrm_ensemble_engagements'
  COURSE_UNIT_ID = TABLE_NAME + '.' + 'course_unit_id'
  ACADEMIC_YEAR_ID = TABLE_NAME + '.' + 'academic_year_id'
  PERSON_ID = TABLE_NAME + '.' + 'person_id'
  ENGAGEMENT_STATUS_ID = TABLE_NAME + '.' + 'engagement_status_id'

  ALL_COLUMNS = [COURSE_UNIT_ID, ACADEMIC_YEAR_ID, PERSON_ID, ENGAGEMENT_STATUS_ID]
  
  belongs_to :engagement_status
  belongs_to :academic_year, :class_name => "Core::AcademicYear"
  belongs_to :person, :class_name => "Core::Person"
  belongs_to :course_unit, :class_name => "Core::CourseUnit"
  
  validates_presence_of :course_unit_id, :academic_year_id, :person_id, :engagement_status_id
  
  
  def self.from_teacher_and_course(person_id, course_unit_id, academic_year_id)
    engagement = (Hrm::Ensemble::Engagement).includes(:engagement_status).where(:person_id => person_id,  :course_unit_id => course_unit_id, :academic_year_id => academic_year_id).select((Hrm::Ensemble::EngagementStatus)::ID, (Hrm::Ensemble::EngagementStatus)::NAME).first
    
    return engagement
  end
  
  def self.get_teachers_on_course(course_unit_id, academic_year_id)
    select_columns = [(Hrm::Ensemble::Engagement)::PERSON_ID, (Hrm::Ensemble::Engagement)::ID, (Hrm::Ensemble::Engagement)::NAME, (Core::Person)::NAME, (Core::Person)::SURNAME, (Core::ScienceLevel)::TITLE, (Core::ProfessionalLevel)::TITLE]
    engagements = (Hrm::Ensemble::Engagement).includes(:engagement_status, :person => [:science_level, :professional_level]).where(:academic_year_id => academic_year_id, :course_unit_id => course_unit_id).select(select_columns).order((Hrm::Ensemble::EngagementStatus)::ID)
    
    return engagements
  end
end
