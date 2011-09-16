class Core::ScoringElementController < ApplicationController
  caches_action :from_course_unit_except_exams, :cache_path => Proc.new { |c| c.params }
  # get "/core/ScoringElement/:id", :controller => "Core::ScoringElement", :action => "show"
  def show
    scoring_element = (Core::ScoringElement).find(params[:id])
    respond_with_object(scoring_element)
  end
  
  
  # get "/core/ScoringElement/fromCourseUnitExceptExams", :controller => "Core::ScoringElement", :action => "from_course_unit_except_exams"
  def from_course_unit_except_exams
    scoring_elements = (Core::ScoringElement).from_course_unit_except_exams(params[:course_unit_id], params[:academic_year_id])
    
    respond_with_object(scoring_elements)
  end
end
