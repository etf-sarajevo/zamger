class Core::RssController < ApplicationController
  # get "/core/RSS/:id", :controller => "Core::Rss", :action => "show"
  def show
    rss = (Core::Rss).where(:id => params[:id])select(:auth_id).first
    respond_with_object(rss)
  end
  
  # get "/core/RSS/fromPersonId", :controller => "Core::Rss", :action => "from_person_id"
  def from_person_id
    rss = (Core::Rss).from_person_id(params[:person_id])
    respond_with_object(rss)
  end
  
  # post "/core/RSS/:id/updateTimestamp", :controller => "Core::Rss", :action => "update_timestamp"
  def update_timestamp
    respond_save((Core::Rss).update_timestamp(params[:id]))
  end

end
