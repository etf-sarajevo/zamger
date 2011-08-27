class Lms::Moodle::MoodleResource < ActiveRecord::Base
  establish_connection 'moodle'
  set_table_name Lms::Moodle::DB_PREFIX + '.' + 'resource'
  set_primary_key 'id'
end
