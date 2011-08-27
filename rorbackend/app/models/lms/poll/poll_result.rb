class Lms::Poll::PollResult < ActiveRecord::Base
  # Uncomment following lines if working with legacy database
  # set_table_name 'anketa_rezultat'
  # set_primary_key :id
  # alias_attribute :id, :id
  # alias_attribute :poll_id, :anketa
  # alias_attribute :time, :vrijeme
  # alias_attribute :closed, :zavrsena
  # alias_attribute :course_unit_id, :predmet
  # alias_attribute :unique_id, :unique_id
  # alias_attribute :academic_year_id, :akademska_godina
  # alias_attribute :programme_id, :studij
  # alias_attribute :semester, :semestar

  # Uncomment following lines if working with legacy database
  # TABLE_NAME = 'anketa_rezultat'
  # ID = TABLE_NAME + '.' + 'id'
  # POLL_ID = TABLE_NAME + '.' + 'anketa'
  # TIME = TABLE_NAME + '.' + 'vrijeme'
  # CLOSED = TABLE_NAME + '.' + 'zavrsena'
  # COURSE_UNIT_ID = TABLE_NAME + '.' + 'predmet'
  # UNIQUE_ID = TABLE_NAME + '.' + 'unique_id'
  # ACADEMIC_YEAR_ID = TABLE_NAME + '.' + 'akademska_godina'
  # PROGRAMME_ID = TABLE_NAME + '.' + 'studij'
  # SEMESTER = TABLE_NAME + '.' + 'semestar'

  # Comment following lines if working with legacy database
  TABLE_NAME = 'lms_poll_poll_results'
  ID = TABLE_NAME + '.' + 'id'
  POLL_ID = TABLE_NAME + '.' + 'poll_id'
  TIME = TABLE_NAME + '.' + 'time'
  CLOSED = TABLE_NAME + '.' + 'closed'
  COURSE_UNIT_ID = TABLE_NAME + '.' + 'course_unit_id'
  UNIQUE_ID = TABLE_NAME + '.' + 'unique_id'
  ACADEMIC_YEAR_ID = TABLE_NAME + '.' + 'academic_year_id'
  PROGRAMME_ID = TABLE_NAME + '.' + 'programme_id'
  SEMESTER = TABLE_NAME + '.' + 'semester'

  ALL_COLUMNS = [ID, POLL_ID, TIME, CLOSED, COURSE_UNIT_ID, UNIQUE_ID, ACADEMIC_YEAR_ID, PROGRAMME_ID, SEMESTER]
  
  
  belongs_to :course_unit, :class_name => "Core::CourseUnit"
  belongs_to :academic_year, :class_name => "Core::AcademicYear"
  belongs_to :programme, :class_name => "Core::Programme"
  
  
  def self.from_hash(unique_id)
    poll_result = (Lms::Poll::PollResult).where(:unique_id => unique_id)
    return poll_result
  end
  
  
  def self.from_student_and_poll(poll_id, student_id)
    poll_results = (Lms::Poll::PollResult).where(:poll_id => poll_id, :student_id => student_id)
    # TODO Gdje je kolona student??
    return poll_results
  end
  
  
  
end
