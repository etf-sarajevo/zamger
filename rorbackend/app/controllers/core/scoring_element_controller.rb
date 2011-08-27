class Core::ScoringElementController < ApplicationController
  # get "/core/ScoringElement/:id", :controller => "Core::ScoringElement", :action => "show"
  def show
    scoring_element = (Core::AcademicYear).find(params[:id])
    respond_with_object(scoring_element)
  end

end
