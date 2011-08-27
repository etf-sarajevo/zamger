class Lms::Project::Project < ActiveRecord::Base
  # Uncomment following lines if working with legacy database
  # set_table_name 'projekat'
  # set_primary_key :id
  # alias_attribute :id, :id
  # alias_attribute :name, :naziv
  # alias_attribute :course_unit_id, :predmet
  # alias_attribute :academic_year_id, :akademska_godina
  # alias_attribute :description, :opis
  # alias_attribute :note, :biljeska
  # alias_attribute :time, :vrijeme

  # Uncomment following lines if working with legacy database
  # TABLE_NAME = 'projekat'
  # ID = TABLE_NAME + '.' + 'id'
  # NAME = TABLE_NAME + '.' + 'naziv'
  # COURSE_UNIT_ID = TABLE_NAME + '.' + 'predmet'
  # ACADEMIC_YEAR_ID = TABLE_NAME + '.' + 'akademska_godina'
  # DESCRIPTION = TABLE_NAME + '.' + 'opis'
  # NOTE = TABLE_NAME + '.' + 'biljeska'
  # TIME = TABLE_NAME + '.' + 'vrijeme'

  # Comment following lines if working with legacy database
  TABLE_NAME = 'lms_project_projects'
  ID = TABLE_NAME + '.' + 'id'
  NAME = TABLE_NAME + '.' + 'name'
  COURSE_UNIT_ID = TABLE_NAME + '.' + 'course_unit_id'
  ACADEMIC_YEAR_ID = TABLE_NAME + '.' + 'academic_year_id'
  DESCRIPTION = TABLE_NAME + '.' + 'description'
  NOTE = TABLE_NAME + '.' + 'note'
  TIME = TABLE_NAME + '.' + 'time'

  ALL_COLUMNS = [ID, COURSE_UNIT_ID, ACADEMIC_YEAR_ID, DESCRIPTION, NOTE, TIME]
  
  belongs_to :course_unit, :class_name => "Core::CourseUnit"
  belongs_to :academic_year, :class_name => "Core::AcademicYear"
  has_many :project_students
  
  
  def self.from_member_and_course(course_unit_id, academic_year_id)
    project = (Lms::Project::Project).joins(:project_students).where(:course_unit_id => course_unit_id, :academic_year_id => academic_year_id, (Lms::Project::ProjectStudent).STUDENT_ID => student_id).first
    return project
  end
  
  
  def self.get_all_for_course(course_unit_id)
    pojects = (Lms::Project::Project).where(:course_unit_id => course_unit_id)
    return projects
  end
  
  
  def self.is_member(id, student_id)
    num_results = (Lms::Project::ProjectStudent).where(:student_id => student_id, :project_id => id).count
    member = true
    if num_results == 0
      member = false
    end
    return member
  end
  
  
  def self.get_members(id)
    select_columns = [(Core::People).ID, (Core::People).NAME, (Core::People).SURNAME, (Core::People).STUDENT_ID_NUMBER]
    members = (Lms::Project::ProjectStudent).joins(:student).where(:project_id => id).select(select_columns)
    return members
  end
  
  
  def self.add_member(id, student_id)
    project_student = (Lms::Project::ProjectStudent).new(:student_id => student_id, :project_id => id)
    return project_student.save
  end
  
  
  def self.delete_member(id, student_id)
    member = (Lms::Project::ProjectStudent).delete_all([:student_id => student_id, :project_id => id])
    raise ActiveRecord::RecordNotFound if member == 0
    return true
  end
  
  
end
