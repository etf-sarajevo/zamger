class Lms::Poll::PollQuestionType < ActiveRecord::Base
  # Uncomment following lines if working with legacy database
  # set_table_name 'anketa_tip_pitanja'
  # set_primary_key :id
  # alias_attribute :id, :id
  # alias_attribute :type, :tip
  # alias_attribute :choice_exists, :postoji_izbor
  # alias_attribute :answers_table, :tabela_odgovora

  # Uncomment following lines if working with legacy database
  # TABLE_NAME = 'anketa_tip_pitanja'
  # ID = TABLE_NAME + '.' + 'id'
  # TYPE = TABLE_NAME + '.' + 'tip'
  # CHOICE_EXISTS = TABLE_NAME + '.' + 'postoji_izbor'
  # ANSWERS_TABLE = TABLE_NAME + '.' + 'tabela_odgovora'

  # Comment following lines if working with legacy database
  TABLE_NAME = 'lms_poll_poll_question_types'
  ID = TABLE_NAME + '.' + 'id'
  TYPE = TABLE_NAME + '.' + 'type'
  CHOICE_EXISTS = TABLE_NAME + '.' + 'choice_exists'
  ANSWERS_TABLE = TABLE_NAME + '.' + 'answers_table'

  ALL_COLUMNS = [ID, TYPE, CHOICE_EXISTS, ANSWERS_TABLE]
end
