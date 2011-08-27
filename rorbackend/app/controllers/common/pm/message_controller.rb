class Common::Pm::MessageController < ApplicationController
  # get "message/:id", :controller => "Common::Pm:Message", :action => "show"
  def show
    message = (Common::Pm::Message).find(params[:id])
    respond_with_object(message)
  end
  
  # get "message/:id/forPerson", :controller => "Common::Pm:Message", :action => "for_person"
  def for_person
    result = (Common::Pm::Message).for_person(params[:id], params[:person_id], params[:is_student])
    respond_with_object(result)
  end
  
  # post "message/send", :controller => "Common::Pm:Message", :action => "send"
  def send
    respond_save((Common::Pm::Message).send_o(params[:to_id], params[:from_id], params[:ref], params[:subject], params[:text]))
  end
  
  # get "message/getLatestForPerson", :controller => "Common::Pm:Message", :action => "get_latest_for_person"
  def get_latest_for_person
    messages = (Common::Pm::Message).get_latest_for_person(params[:person_id], params[:limit])
    respond_with_object(messages)
  end
  
  # get "message/getOutboxForPerson", :controller => "Common::Pm:Message", :action => "get_outbox_for_person"
  def get_outbox_for_person
    messages = (Common::Pm::Message).get_outbox_for_person(params[:person_id], params[:limit], params[:start_from])
    respond_with_object(messages)
  end

end
