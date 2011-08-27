class Core::Programme < ActiveRecord::Base
  # Uncomment following lines if working with legacy database
  # set_table_name 'studij' 
  # set_primary_key :id
  # alias_attribute :id, :id
  # alias_attribute :name, :naziv
  # alias_attribute :short_name, :kratkinaziv
  # alias_attribute :final_semester, :zavrsni_semestar
  # alias_attribute :institution_id, :institucija
  # alias_attribute :accepts_students, :moguc_upis
  # alias_attribute :type_id, :tipstudija
  # alias_attribute :precondition, :preduslov

  # Uncomment following lines if working with legacy database
  # TABLE_NAME = 'studij'
  # ID = TABLE_NAME + '.' + 'id'
  # NAME = TABLE_NAME + '.' + 'naziv'
  # SHORT_NAME = TABLE_NAME + '.' + 'kratkinaziv'
  # FINAL_SEMESTER = TABLE_NAME + '.' + 'zavrsni_semestar'
  # INSTITUTION_ID = TABLE_NAME + '.' + 'institucija'
  # ACCEPTS_STUDENTS = TABLE_NAME + '.' + 'moguc_upis'
  # TYPE_ID = TABLE_NAME + '.' + 'tipstudija'
  # PRECONDITION = TABLE_NAME + '.' + 'preduslov'

  # Comment following lines if working with legacy database
  TABLE_NAME = 'core_programmes'
  ID = TABLE_NAME + '.' + 'id'
  NAME = TABLE_NAME + '.' + 'name'
  SHORT_NAME = TABLE_NAME + '.' + 'short_name'
  FINAL_SEMESTER = TABLE_NAME + '.' + 'final_semester'
  INSTITUTION_ID = TABLE_NAME + '.' + 'institution_id'
  ACCEPTS_STUDENTS = TABLE_NAME + '.' + 'accepts_students'
  TYPE_ID = TABLE_NAME + '.' + 'type_id'
  PRECONDITION = TABLE_NAME + '.' + 'precondition'

  ALL_COLUMNS = [ID, NAME, SHORT_NAME, FINAL_SEMESTER, INSTITUTION_ID, ACCEPTS_STUDENTS, TYPE_ID, PRECONDITION]
  
  has_many :course_offerings
  has_many :enrollments
  belongs_to :institution
  belongs_to :programme_type, :foreign_key => :type_id
  
  validates_presence_of :name, :short_name, :accepts_students, :type_id, :precondition
  
  
  def self.from_id(id)
    select_columns = (Core::Programme)::ALL_COLUMNS | (Core::ProgrammeType)::ALL_COLUMNS
    programme = (Core::Programme).joins(:programme_type).where((Core::Programme)::ID => id).select(select_columns).first
    
    return programme
  end
end
