class Lms::Poll::PollAnswerEssay < ActiveRecord::Base
  # Uncomment following lines if working with legacy database
  # set_table_name 'anketa_odgovor_text'
  # set_primary_key :rezultat, :pitanje
  # alias_attribute :poll_result_id, :rezultat
  # alias_attribute :poll_question_id, :pitanje
  # alias_attribute :answer, :odgovor

  # Uncomment following lines if working with legacy database
  # TABLE_NAME = 'anketa_odgovor_text'
  # POLL_RESULT_ID = TABLE_NAME + '.' + 'rezultat'
  # POLL_QUESTION_ID = TABLE_NAME + '.' + 'pitanje'
  # ANSWER = TABLE_NAME + '.' + 'odgovor'

  # Comment following lines if working with legacy database
  TABLE_NAME = 'lms_poll_poll_answer_essays'
  POLL_RESULT_ID = TABLE_NAME + '.' + 'poll_result_id'
  POLL_QUESTION_ID = TABLE_NAME + '.' + 'poll_question_id'
  ANSWER = TABLE_NAME + '.' + 'answer'

  ALL_COLUMNS = [POLL_RESULT_ID, POLL_QUESTION_ID, ANSWER]
  
  belongs_to :poll_result
  belongs_to :poll_question
end
