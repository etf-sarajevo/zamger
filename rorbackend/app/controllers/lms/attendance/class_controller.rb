class Lms::Attendance::ClassController < ApplicationController
  # get "/lms/attendance/Class/:id", :controller => "Lms::Attendance::Class", :action => "show"
  def show
    class_t = (Lms::Attendance::Class).from_id(id)
    respond_with_object(class_t)
  end
  
  # get "/lms/attendance/Class/fromGroupAndScoringElement", :controller => "Lms::Attendance::Class", :action => "from_group_and_scoring_element"
  def from_group_and_scoring_element
    class_t = (Lms::Attendance::Class).from_group_and_scoring_element(params[:group_id], params[:scoring_element_id])
    respond_with_object(class_t)
  end

end
