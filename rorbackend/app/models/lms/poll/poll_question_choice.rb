class Lms::Poll::PollQuestionChoice < ActiveRecord::Base
  # Uncomment following lines if working with legacy database
  # set_table_name 'anketa_izbori_pitanja'
  # set_primary_key :id
  # alias_attribute :id, :id
  # alias_attribute :poll_question_id, :pitanje
  # alias_attribute :choice, :izbor

  # Uncomment following lines if working with legacy database
  # TABLE_NAME = 'anketa_izbori_pitanja'
  # ID = TABLE_NAME + '.' + 'id'
  # POLL_QUESTION_ID = TABLE_NAME + '.' + 'pitanje'
  # CHOICE = TABLE_NAME + '.' + 'izbor'

  # Comment following lines if working with legacy database
  TABLE_NAME = 'lms_poll_poll_question_choices'
  ID = TABLE_NAME + '.' + 'id'
  POLL_QUESTION_ID = TABLE_NAME + '.' + 'poll_question_id'
  CHOICE = TABLE_NAME + '.' + 'choice'

  ALL_COLUMNS = [ID, POLL_QUESTION_ID, CHOICE]
  
  belongs_to :poll_question
end
