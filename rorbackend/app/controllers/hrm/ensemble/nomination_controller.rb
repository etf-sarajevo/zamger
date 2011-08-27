class Hrm::Ensemble::NominationController < ApplicationController
  # get "/hrm/ensemble/Nomination/:id", :controller => "Hrm::Ensemble::Nomination", :action => "show"
  def show
    nomination = (Hrm::Ensemble::Nomination).find(params[:id])
    respond_with_object(nomination)
  end
  
  # get "/hrm/ensemble/Nomination/getLatestForPerson", :controller => "Hrm::Ensemble::Nomination", :action => "get_latest_for_person"
  def get_latest_for_person
    nomination = (Core::Nomination).get_latest_for_person(params[:person_id])
    respond_with_object(nomination)
  end

end
