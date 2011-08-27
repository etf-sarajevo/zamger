class Lms::Moodle::MoodleId < ActiveRecord::Base
  # Uncomment following lines if working with legacy database
  # set_table_name 'moodle_predmet_id'
  # alias_attribute :course_unit_id, :predmet
  # alias_attribute :academic_year_id, :akademska_godina
  # alias_attribute :moodle_id, :moodle_id

  # Uncomment following lines if working with legacy database
  # TABLE_NAME = 'moodle_predmet_id'
  # COURSE_UNIT_ID = TABLE_NAME + '.' + 'predmet'
  # ACADEMIC_YEAR_ID = TABLE_NAME + '.' + 'akademska_godina'
  # MOODLE_ID = TABLE_NAME + '.' + 'moodle_id'

  # Comment following lines if working with legacy database
  TABLE_NAME = 'lms_moodle_moodle_ids'
  COURSE_UNIT_ID = TABLE_NAME + '.' + 'course_unit_id'
  ACADEMIC_YEAR_ID = TABLE_NAME + '.' + 'academic_year_id'
  MOODLE_ID = TABLE_NAME + '.' + 'moodle_id'

  ALL_COLUMNS = [COURSE_UNIT_ID, ACADEMIC_YEAR_ID, MOODLE_ID]
  
  belongs_to :course_unit
  belongs_to :academic_year
  
  
  def self.get_moodle_id(course_unit_id, academic_year_id)
    moodle_id = (Lms::Moodle::MoodleId).where(:course_unit_id => course_unit_id, :academic_year_id => academic_year_id).select(:moodle_id).first
      
    return moodle_id
  end
end
