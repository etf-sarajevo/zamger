class Core::AccessLevel < ActiveRecord::Base
  # Uncomment following lines if working with legacy database
  # set_table_name 'nastavnik_predmet'
  # set_primary_key :nastavnik, :akademska_godina, :predmet
  # alias_attribute :person_id, :nastavnik
  # alias_attribute :course_unit_id, :predmet
  # alias_attribute :academic_year_id, :akademska_godina
  # alias_attribute :access_levels, :nivo_pristupa

  # Uncomment following lines if working with legacy database
  # TABLE_NAME = 'nastavnik_predmet'
  # PERSON_ID = TABLE_NAME + '.' + 'nastavnik'
  # COURSE_UNIT_ID = TABLE_NAME + '.' + 'predmet'
  # ACADEMIC_YEAR_ID = TABLE_NAME + '.' + 'akademska_godina'
  # ACCESS_LEVELS = TABLE_NAME + '.' + 'nivo_pristupa'

  # Comment following lines if working with legacy database
  TABLE_NAME = 'core_access_levels'
  PERSON_ID = TABLE_NAME + '.' + 'person_id'
  COURSE_UNIT_ID = TABLE_NAME + '.' + 'course_unit_id'
  ACADEMIC_YEAR_ID = TABLE_NAME + '.' + 'academic_year_id'
  ACCESS_LEVELS = TABLE_NAME + '.' + 'access_levels'

  ALL_COLUMNS = [PERSON_ID, COURSE_UNIT_ID, ACADEMIC_YEAR_ID, ACCESS_LEVELS]
  
  belongs_to :person
  belongs_to :academic_year
  belongs_to :course_unit
end
