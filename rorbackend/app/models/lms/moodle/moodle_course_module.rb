class Lms::Moodle::MoodleCourseModule < ActiveRecord::Base
  establish_connection 'moodle'
  set_table_name Lms::Moodle::DB_PREFIX + '.' + 'course_modules'
  set_primary_key 'id'
  
end
