class Lms::Attendance::TeacherGroup < ActiveRecord::Base
  # Uncomment following lines if working with legacy database
  # set_table_name 'ogranicenje'
  # alias_attribute :teacher_id, :nastavnik
  # alias_attribute :group_id, :labgrupa

  # Uncomment following lines if working with legacy database
  # TABLE_NAME = 'ogranicenje'
  # TEACHER_ID = TABLE_NAME + '.' + 'nastavnik'
  # GROUP_ID = TABLE_NAME + '.' + 'labgrupa'

  # Comment following lines if working with legacy database
  TABLE_NAME = 'lms_attendance_teacher_groups'
  TEACHER_ID = TABLE_NAME + '.' + 'teacher_id'
  GROUP_ID = TABLE_NAME + '.' + 'group_id'

  ALL_COLUMNS = [TEACHER_ID, GROUP_ID]
  
  belongs_to :person, :foreign_key => 'teacher_id', :class_name => "Core::Person"
  belongs_to :group
end
