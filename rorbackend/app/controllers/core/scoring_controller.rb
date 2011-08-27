class Core::ScoringController < ApplicationController
  # get "/core/Scoring/:id", :controller => "Core::Scoring", :action => "show"
  def show
    scoring = (Core::Scoring).where(:id => params[:id]).select(:name).first
    respond_with_object(scoring)
  end
  
  # get "/core/Scoring/:id/getScoringElements", :controller => "Core::Scoring", :action => "get_scoring_elements"
  def get_scoring_elements
    scoring_elements = (Core::Scoring).get_scoring_elements(params[:id], params[:se_type])
    respond_with_object(scoring_elements)
  end

end
