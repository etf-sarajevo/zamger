class Lms::Project::ProjectParams < ActiveRecord::Base
  # Uncomment following lines if working with legacy database
  # set_table_name 'predmet_projektni_parametri'
  # set_primary_key :predmet, :akademska_godina
  # alias_attribute :course_unit_id, :predmet
  # alias_attribute :academic_year_id, :akademska_godina
  # alias_attribute :min_teams, :min_timova
  # alias_attribute :max_teams, :max_timova
  # alias_attribute :min_team_members, :min_clanova_tima
  # alias_attribute :max_team_members, :max_clanova_tima
  # alias_attribute :locked, :zakljucani_projekti

  # Uncomment following lines if working with legacy database
  # TABLE_NAME = 'predmet_projektni_parametri'
  # COURSE_UNIT_ID = TABLE_NAME + '.' + 'predmet'
  # ACADEMIC_YEAR_ID = TABLE_NAME + '.' + 'akademska_godina'
  # MIN_TEAMS = TABLE_NAME + '.' + 'min_timova'
  # MAX_TEAMS = TABLE_NAME + '.' + 'max_timova'
  # MIN_TEAM_MEMBERS = TABLE_NAME + '.' + 'min_clanova_tima'
  # MAX_TEAM_MEMBERS = TABLE_NAME + '.' + 'max_clanova_tima'
  # LOCKED = TABLE_NAME + '.' + 'zakljucani_projekti'

  # Comment following lines if working with legacy database
  TABLE_NAME = 'lms_project_project_params'
  COURSE_UNIT_ID = TABLE_NAME + '.' + 'course_unit_id'
  ACADEMIC_YEAR_ID = TABLE_NAME + '.' + 'academic_year_id'
  MIN_TEAMS = TABLE_NAME + '.' + 'min_teams'
  MAX_TEAMS = TABLE_NAME + '.' + 'max_teams'
  MIN_TEAM_MEMBERS = TABLE_NAME + '.' + 'min_team_members'
  MAX_TEAM_MEMBERS = TABLE_NAME + '.' + 'max_team_members'
  LOCKED = TABLE_NAME + '.' + 'locked'

  ALL_COLUMNS = [COURSE_UNIT_ID, ACADEMIC_YEAR_ID, MIN_TEAMS, MAX_TEAMS, MIN_TEAM_MEMBERS, MAX_TEAM_MEMBERS, LOCKED]
  
  belongs_to :course_unit, :class_name => "Core::CourseUnit"
  belongs_to :academic_year, :class_name => "Core::AcademicYear"
  
  
  def self.from_course(course_unit_id, academic_year_id)
    project_params = (Lms::Project::ProjectParams).where(:course_unit_id => course_unit_id, :academic_year_id => academic_year_id)
    
    return project_params
  end
  
  
end
