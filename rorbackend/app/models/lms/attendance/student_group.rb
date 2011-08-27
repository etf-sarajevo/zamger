class Lms::Attendance::StudentGroup < ActiveRecord::Base
  # Uncomment following lines if working with legacy database
  # set_table_name 'student_labgrupa'
  # alias_attribute :student_id, :student
  # alias_attribute :group_id, :labgrupa
 
  # Uncomment following lines if working with legacy database
  # TABLE_NAME = 'student_labgrupa'
  # STUDENT_ID =  TABLE_NAME + '.' + 'student'
  # GROUP_ID =  TABLE_NAME + '.' + 'labgrupa'

 # Comment following lines if working with legacy database
  TABLE_NAME = 'lms_attendance_student_groups'
  STUDENT_ID =  TABLE_NAME + '.' + 'student_id'
  GROUP_ID =  TABLE_NAME + '.' + 'group_id'

  ALL_COLUMNS = [STUDENT_ID, GROUP_ID]
  
  belongs_to :person, :foreign_key => 'student_id', :class_name => "Core::Person"
  belongs_to :group
end
