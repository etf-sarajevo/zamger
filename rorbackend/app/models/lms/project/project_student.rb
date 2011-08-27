class Lms::Project::ProjectStudent < ActiveRecord::Base
  # Uncomment following lines if working with legacy database
  # set_table_name 'student_projekat'
  # set_primary_key :student, :projekat
  # alias_attribute :student_id, :student
  # alias_attribute :project_id, :projekat

  # Uncomment following lines if working with legacy database
  # TABLE_NAME = 'student_projekat'
  # STUDENT_ID = TABLE_NAME + '.' + 'student'
  # PROJECT_ID = TABLE_NAME + '.' + 'projekat'

  # Comment following lines if working with legacy database
  TABLE_NAME = 'project_project_students'
  STUDENT_ID = TABLE_NAME + '.' + 'student_id'
  PROJECT_ID = TABLE_NAME + '.' + 'project_id'

  ALL_COLUMNS = [STUDENT_ID, PROJECT_ID]
  
  belongs_to :student, :class_name => "Core::Person"
  belongs_to :project
end
