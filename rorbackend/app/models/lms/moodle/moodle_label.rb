class Lms::Moodle::MoodleLabel < ActiveRecord::Base
  establish_connection 'moodle'
  set_table_name Lms::Moodle::DB_PREFIX + '.' + 'label'
  set_primary_key 'id'
end
