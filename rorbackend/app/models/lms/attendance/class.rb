class Lms::Attendance::Class < ActiveRecord::Base
  # Uncomment following lines if working with legacy database
  # set_table_name 'cas'
  # set_primary_key :id
  # alias_attribute :id, :id
  # alias_attribute :date, :datum
  # alias_attribute :time, :vrijeme
  # alias_attribute :teacher_id, :nastavnik
  # alias_attribute :group_id, :labgrupa
  # alias_attribute :scoring_element_id, :komponenta

  # Uncomment following lines if working with legacy database
  # TABLE_NAME = 'cas'
  # ID = TABLE_NAME + '.' + 'id'
  # DATE = TABLE_NAME + '.' + 'datum'
  # TIME = TABLE_NAME + '.' + 'vrijeme'
  # TEACHER_ID = TABLE_NAME + '.' + 'nastavnik'
  # GROUP_ID = TABLE_NAME + '.' + 'labgrupa'
  # SCORING_ELEMENT_ID = TABLE_NAME + '.' + 'komponenta'

  # Comment following lines if working with legacy database
  TABLE_NAME = 'lms_attendance_classes'
  ID = TABLE_NAME + '.' + 'id'
  DATE = TABLE_NAME + '.' + 'date'
  TIME = TABLE_NAME + '.' + 'time'
  TEACHER_ID = TABLE_NAME + '.' + 'teacher_id'
  GROUP_ID = TABLE_NAME + '.' + 'group_id'
  SCORING_ELEMENT_ID = TABLE_NAME + '.' + 'scoring_element_id'

  ALL_COLUMNS = [ID, DATE, TIME, TEACHER_ID, GROUP_ID, SCORING_ELEMENT_ID]
  
  has_many :attendances
  has_one :teacher, :class_name => "Core::Person"
  belongs_to :group
  
  validates_presence_of :date, :time, :scoring_element_id
  
  def self.from_id(id)
    select_columns = [(Lms::Attendance::Class)::DATE, (Lms::Attendance::Class)::TIME, (Lms::Attendance::Class)::TEACHER_ID, (Lms::Attendance::Class)::GROUP_ID, (Lms::Attendance::Class)::SCORING_ELEMENT_ID, (Lms::Attendance::Group)::NAME, (Lms::Attendance::Group)::COURSE_UNIT_ID, (Lms::Attendance::Group)::ACADEMIC_YEAR_ID]
    
    class_t = (Lms::Attendance::Class).where(:id => id).includes(:group).select(select_columns).first
    
    return class_t
  end
  
  def self.from_group_and_scoring_element(group_id, scoring_element_id)
    class_t = (Lms::Attendance::Class).where(:group_id => group_id, :scoring_element_id => scoring_element_id).select([:id, :date, :time, :teacher_id]).order(:time).first
    
    return class_t
  end
end
