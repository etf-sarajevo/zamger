class Lms::Poll::PollQuestion < ActiveRecord::Base
  # Uncomment following lines if working with legacy database
  # set_table_name 'anketa_pitanje'
  # set_primary_key :id
  # alias_attribute :id, :id
  # alias_attribute :poll_id, :anketa
  # alias_attribute :poll_question_type_id, :tip_pitanja
  # alias_attribute :text, :tekst

  # Uncomment following lines if working with legacy database
  # TABLE_NAME = 'anketa_pitanje'
  # ID = TABLE_NAME + '.' + 'id'
  # POLL_ID = TABLE_NAME + '.' + 'anketa'
  # POLL_QUESTION_TYPE_ID = TABLE_NAME + '.' + 'tip_pitanja
  # TEXT = TABLE_NAME + '.' + 'tekst'
 
  # Comment following lines if working with legacy database
  TABLE_NAME = 'lms_poll_poll_questions'
  ID = TABLE_NAME + '.' + 'id'
  POLL_ID = TABLE_NAME + '.' + 'poll_id'
  POLL_QUESTION_TYPE_ID = TABLE_NAME + '.' + 'poll_question_type_id'
  TEXT = TABLE_NAME + '.' + 'text'

  ALL_COLUMNS = [ID, POLL_ID, POLL_QUESTION_TYPE_ID, TEXT]
  
  belongs_to :poll
  has_one :poll_question_type
  
  
  def self.get_all_for_poll(poll_id)
    poll_questions = (Lms::Poll::PollQuestion).where(:poll_id => poll_id)
    return poll_questions
  end
  
  
  def self.set_answer_rank(type_id, response, id, poll_result_id, poll_question_choice_id)
    raise ArgumentError if (type_id != 1)
    
    if (response > 0)
      answer_rank = (Lms::Poll::PollAnswerRank).new(:poll_result_id => poll_result_id, :poll_question_id => id, :poll_question_choice_id => poll_question_choice_id)
      return answer_rank.save
    end
  end
  
  def self.set_answer_essay(type_id, id, poll_result_id, answer)
    raise ArgumentError if (type_id != 1)
    
    answer_essay = (Lms::Poll::PollAnswerEssay).new(:poll_result_id => poll_result_id, :poll_question_id => id, :answer => answer)
    
    return answer_essay.save
  end
  
end
