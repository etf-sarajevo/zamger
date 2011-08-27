class Lms::Poll::PollAnswerRank < ActiveRecord::Base
  # Uncomment following lines if working with legacy database
  # set_table_name 'anketa_odgovor_rank'
  # set_primary_key :rezultat, :pitanje, :izbor_id
  # alias_attribute :poll_result_id, :rezultat
  # alias_attribute :poll_question_id, :pitanje
  # alias_attribute :poll_question_choice_id, :rezultat

  # Uncomment following lines if working with legacy database
  # TABLE_NAME = 'anketa_odgovor_rank'
  # POLL_RESULT_ID = TABLE_NAME + '.' + 'rezultat'
  # POLL_QUESTION_ID = TABLE_NAME + '.' + 'pitanje'
  # POLL_QUESTION_CHOICE_ID = TABLE_NAME + '.' + 'rezultat'

  # Comment following lines if working with legacy database
  TABLE_NAME = 'lms_poll_poll_answer_ranks'
  POLL_RESULT_ID = TABLE_NAME + '.' + 'poll_result_id'
  POLL_QUESTION_ID = TABLE_NAME + '.' + 'poll_question_id'
  POLL_QUESTION_CHOICE_ID = TABLE_NAME + '.' + 'poll_question_choice_id'

  ALL_COLUMNS = [POLL_RESULT_ID, POLL_QUESTION_ID, POLL_QUESTION_CHOICE_ID]
  
  
  belongs_to :poll_question
  belongs_to :poll_result
  belongs_to :poll_question_choice
  
  
  def self.for_question(poll_question_id)
    poll_question_choices = (Lms::Poll::PollQuestionChoice).where(:poll_question_id => poll_question_id)
    return poll_question_choices
  end
  
end
