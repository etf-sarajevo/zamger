class Lms::Poll::PollResultController < ApplicationController
  # get "/lms/poll/PollResult/:id", :controller => "Lms::Poll::PollResult", :action => "show"
  def show
    poll_result = (Lms::Poll::PollResult).find(params[:id])
    respond_with(poll_result)
  end
  
  # get "/lms/poll/PollResult/fromHash", :controller => "Lms::Poll::PollResult", :action => "from_hash"
  def from_hash
    poll_result = (Lms::Poll::PollResult).from_hash(params[:unique_id])
    respond_with(poll_result)
  end
  
  # get "/lms/poll/PollResult/fromStudentAndPoll", :controller => "Lms::Poll::PollResult", :action => "from_student_and_poll"
  def from_student_and_poll
    poll_result = (Lms::Poll::PollResult).from_student_and_poll(params[:poll_id], params[:student_id])
  end
  
  # put "/lms/poll/PollResult", :controller => "Lms::Poll::PollResult", :action => "create"
  def create
    poll_result = (Lms::Poll::PollResult).new(params)
    
    respond_create(poll_result.save)
  end
  
  # post "/lms/poll/PollResult/:id", :controller => "Lms::Poll::PollResult", :action => "update"
  def update
    poll_result = (Lms::Poll::PollResult).find(params[:id])
    poll_result.attributes=params
    respond_save(poll_result.save)
  end

end
