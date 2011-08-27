class Sis::AnnouncementController < ApplicationController
  # get "/sis/Announcement/:id", :controller => "Sis::Announcement", :action => "show"
  def show
    announcement = (Common::Pm::Message).get_announcement_from_id(params[:id])
    
    respond_with_object(announcement)
  end
  
  # get "/sis/Announcement/getLatestForPerson", :controller => "Sis::Announcement", :action => "get_latest_for_person"
  def get_latest_for_person
    announcements = (Common::Pm::Message).get_latest_for_person(params[:person_id], params[:limit], params[:is_student])
    respond_with_object(announcements)
  end

end
