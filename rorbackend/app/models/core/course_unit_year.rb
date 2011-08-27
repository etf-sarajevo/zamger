class Core::CourseUnitYear < ActiveRecord::Base
  # Uncomment following lines if working with legacy database
  # set_table_name 'akademska_godina_predmet'
  # set_primary_key :akademska godina, :predmet
  # alias_attribute :course_unit_id, :predmet
  # alias_attribute :academic_year_id, :akademska godina
  # alias_attribute :course_unit_type_id, :tippredmeta
  
  # Uncomment following lines if working with legacy database
  # TABLE_NAME = 'akademska_godina_predmet'
  # COURSE_UNIT_ID = TABLE_NAME + '.' + 'predmet'
  # ACADEMIC_YEAR_ID = TABLE_NAME + '.' + 'akademska godina'
  # COURSE_UNIT_TYPE_ID = TABLE_NAME + '.' + 'tippredmeta'
  
  # Comment following lines if working with legacy database
  TABLE_NAME = 'core_course_unit_years'
  COURSE_UNIT_ID = TABLE_NAME + '.' + 'course_unit_id'
  ACADEMIC_YEAR_ID = TABLE_NAME + '.' + 'academic_year_id'
  COURSE_UNIT_TYPE_ID =  TABLE_NAME + '.' + 'course_unit_type_id'

  ALL_COLUMNS = [COURSE_UNIT_ID, ACADEMIC_YEAR_ID, COURSE_UNIT_TYPE_ID]
  
  belongs_to :course_unit
  belongs_to :academic_year
  belongs_to :course_unit_type
  
  validates_presence_of :course_unit_id, :academic_year_id, :course_unit_type_id
  
  
  def self.from_course_and_year(course_unit_id, academic_year_id)
    select_columns = [(Core::CourseUnitYear)::COURSE_UNIT_ID, (Core::CourseUnitYear)::ACADEMIC_YEAR_ID, (Core::CourseUnitYear)::COURSE_UNIT_TYPE_ID, (Core::Scoring)::ID, (Core::Scoring)::NAME]
    course_unit_year = (Core::CourseUnitYear).where(:course_unit_id => course_unit_id, :academic_year_id => academic_year_id).includes(:scoring).select(select_columns).first
    
    return course_unit_year
  end
  
  def self.teacher_access_level(teacher_id, course_unit_id, academic_year_id)
    access_level = (Core::AccessLevel).where(:teacher_id => teacher_id, :course_unit_id => course_unit_id, :academic_year_id => academic_year_id).select((Core::AccessLevel)::ACCESS_LEVEL).first
    
    return access_level
  end
end
