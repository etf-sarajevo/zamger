class Core::ProgrammeController < ApplicationController
  # get "/core/Programme/:id", :controller => "Core::Programme", :action => "show"
  def show
    select_columns = (Core::Programme).from_id(params[:id])
    respond_with_object(programme)
  end

end
