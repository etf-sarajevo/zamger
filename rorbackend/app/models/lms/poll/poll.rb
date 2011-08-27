class Lms::Poll::Poll < ActiveRecord::Base
  # Uncomment following lines if working with legacy database
  # set_table_name 'anketa_anketa'
  # set_primary_key :id
  # alias_attribute :id, :id
  # alias_attribute :open_date, :datum_otvaranja
  # alias_attribute :close_date, :datum_zatvaranja
  # alias_attribute :name, :naziv
  # alias_attribute :description, :opis
  # alias_attribute :active, :aktivna
  # alias_attribute :editable, :editable
  # alias_attribute :academic_year_id, ;akademska_godina

  # Uncomment following lines if working with legacy database
  # TABLE_NAME = 'anketa_anketa'
  # ID = TABLE_NAME + '.' + 'id'
  # OPEN_DATE = TABLE_NAME + '.' + 'datum_otvaranja'
  # CLOSE_DATE = TABLE_NAME + '.' + 'datum_zatvaranja'
  # NAME = TABLE_NAME + '.' + 'naziv'
  # DESCRIPTION = TABLE_NAME + '.' + 'opis'
  # ACTIVE = TABLE_NAME + '.' + 'aktivna'
  # EDITABLE = TABLE_NAME + '.' + 'editable'
  # ACADEMIC_YEAR_ID = TABLE_NAME + '.' + 'akademska_godina'

  # Comment following lines if working with legacy database
  TABLE_NAME = 'lms_poll_polls'
  ID = TABLE_NAME + '.' + 'id'
  OPEN_DATE = TABLE_NAME + '.' + 'open_date'
  CLOSE_DATE = TABLE_NAME + '.' + 'close_date'
  NAME = TABLE_NAME + '.' + 'name'
  DESCRIPTION = TABLE_NAME + '.' + 'description'
  ACTIVE = TABLE_NAME + '.' + 'active'
  EDITABLE = TABLE_NAME + '.' + 'editable'
  ACADEMIC_YEAR_ID = TABLE_NAME + '.' + 'academic_year_id'

  ALL_COLUMNS = [ID, OPEN_DATE, CLOSE_DATE, NAME, DESCRIPTION, ACTIVE, EDITABLE, ACADEMIC_YEAR_ID]
  
  belongs_to :academic_year, :class_name => "Core::AcademicYear"
end
