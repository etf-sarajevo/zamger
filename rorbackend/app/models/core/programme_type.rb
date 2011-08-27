class Core::ProgrammeType < ActiveRecord::Base
  # Uncomment following lines if working with legacy database
  # set_table_name 'tipstudija'
  # set_primary_key :id
  # alias_attribute :id, :id
  # alias_attribute :name, :naziv
  # alias_attribute :cycle, :ciklus
  # alias_attribute :duration, :trajanje
  # alias_attribute :accepts_students, :moguc_upis

  # Uncomment following lines if working with legacy database
  # TABLE_NAME = 'tipstudija'
  # ID = TABLE_NAME + '.' + 'id'
  # NAME = TABLE_NAME + '.' + 'naziv'
  # CYCLE = TABLE_NAME + '.' + 'ciklus'
  # DURATION = TABLE_NAME + '.' + 'trajanje'
  # ACCEPTS_STUDENTS = TABLE_NAME + '.' + 'moguc_upis'

  # Comment following lines if working with legacy database
  TABLE_NAME = 'core_programme_types'
  ID = TABLE_NAME + '.' + 'id'
  NAME = TABLE_NAME + '.' + 'name'
  CYCLE = TABLE_NAME + '.' + 'cycle'
  DURATION = TABLE_NAME + '.' + 'duration'
  ACCEPTS_STUDENTS = TABLE_NAME + '.' + 'accepts_students'

  ALL_COLUMNS = [ID, NAME, CYCLE, DURATION, ACCEPTS_STUDENTS]
  
  validates_presence_of :name, :cycle, :duration, :accepts_students
  
end
