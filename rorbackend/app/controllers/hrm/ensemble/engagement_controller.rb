class Hrm::Ensemble::EngagementController < ApplicationController
  # get "/hrm/ensemble/engagement/from_teacher_and_course", :controller => "Hrm::Ensemble::Engagement", :action => "from_teacher_and_course"
  def from_teacher_and_course
    engagement = (Hrm::Ensemble::Engagement).from_teacher_and_course(params[:person_id], params[:course_unit_id], params[:academic_year_id])
    respond_with_object(engagement)
  end
  
  # get "/hrm/ensemble/engagement/get_teachers_on_course", :controller => "Hrm::Ensemble::Engagement", :action => "get_teachers_on_course"
  def get_teachers_on_course
    engagements = (Hrm::Ensemble::Engagement).get_teachers_on_course(params[:course_unit_id], params[:academic_year_id])
    
    respond_with_object(engagements)
  end

end
