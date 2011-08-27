class Lms::Homework::ProgrammingLanguage < ActiveRecord::Base
  # Uncomment following lines if working with legacy database
  # set_table_name 'programskijezik'
  # set_primary_key :id
  # alias_attribute :id, :id
  # alias_attribute :name, :naziv
  # alias_attribute :geshi, :geshi
  # alias_attribute :extension, :ekstenzija

  # Uncomment following lines if working with legacy database
  # TABLE_NAME = 'programskijezik'
  # ID = TABLE_NAME + '.' + 'id'
  # NAME = TABLE_NAME + '.' + 'naziv'
  # GESHI = TABLE_NAME + '.' + 'geshi'
  # EXTENSION = TABLE_NAME + '.' + 'ekstenzija'

  # Comment following lines if working with legacy database
  TABLE_NAME = 'lms_homework_programming_languages'
  ID = TABLE_NAME + '.' + 'id'
  NAME = TABLE_NAME + '.' + 'name'
  GESHI = TABLE_NAME + '.' + 'geshi'
  EXTENSION = TABLE_NAME + '.' + 'extension'

  ALL_COLUMNS = [ID, NAME, GESHI, EXTENSION]
  
  validates_presence_of :name, :geshi, :extension
end
