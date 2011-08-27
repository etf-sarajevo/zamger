class Core::CourseOfferingController < ApplicationController
  # get "/core/CourseOffering/:id", :controller => "Core::CourseOffering", :action => "show"
  def show
    course_offering = (Core::CourseOffering).find(params[:id])
    respond_with_object(course_offering)
  end
  
  # get "/core/CourseOffering/getCoursesOffered", :controller => "Core::CourseOffering", :action => "get_courses_offered"
  def get_courses_offered
    courses_offered = (Core::CourseOffering).get_courses_offered(params[:academic_year_id], params[:programme_id], params[:semester])
    respond_with_object(courses_offered)
  end

end
