class Core::ProgrammeTypeController < ApplicationController
  # get "/core/ProgrammeType/:id", :controller => "Core::ProgrammeType", :action => "show"
  def show
    programme_type = (Core::ProgrammeType).find(params[:id])
    respond_with_object(programme_type)
  end

end
