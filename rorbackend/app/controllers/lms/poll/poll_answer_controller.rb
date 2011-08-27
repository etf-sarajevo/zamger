class Lms::Poll::PollAnswerController < ApplicationController
  # get "/lms/poll/PollAnswer/forQuestion", :controller => "Lms::Poll::PollQuestion", :action => "for_question"
  def for_question
    poll_question_choices = (Lms::Poll::PollQuestionChoice).for_question(params[:poll_question_id])
    respond_with_object(poll_question_choices)
  end

end
