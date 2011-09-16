class Lms::Poll::PollQuestionController < ApplicationController
  #  get "/lms/poll/PollQuestion/:id", :controller => "Lms::Poll::PollQuestion", :action => "show"
  def show
    poll_question = (Lms::Poll::PollQuestion).find(params[:id])
    respond_with_object(poll_question)
  end
  
  # get "/lms/poll/PollQuestion/getAllForPoll", :controller => "Lms::Poll::PollQuestion", :action => "get_all_for_poll"
  def get_all_for_poll
    poll_questions = (Lms::Poll::PollQuestion).get_all_for_poll(params[:poll_id])
    respond_with_object(poll_questions)
  end
  
  # post "/lms/poll/PollQuestion/:id/setAnswerRank", :controller => "Lms::Poll::PollQuestion", :action => "set_answer_rank"
  def set_answer_rank
      respond_create((Lms::Poll::PollQuestion).set_answer_rank(params[:type_id], params[:response], params[:id], params[:poll_result_id], params[:poll_question_choice_id]))
  end
  
  # post "/lms/poll/PollQuestion/:id/setAnswerEssay", :controller => "Lms::Poll::PollQuestion", :action => "set_answer_essay"
  def set_answer_essay
    respond_create((Lms::Poll::PollQuestion).set_answer_essay(params[:type_id], params[:id], params[:poll_result_id], params[:answer]))
  end
  
  # post "/lms/poll/PollQuestion/:id/setAnswerChoice", :controller => "Lms::Poll::PollQuestion", :action => "set_answer_choice"
  def set_answer_choice
    # TODO Tabela anketa_odgovor_izbori???
  end

end
