class Lms::Project::ProjectParamsController < ApplicationController
  # get "/Lms/Project/ProjectParams/fromCourse", :controller => "Lms::Project::ProjectParams", :action => "from_course"
  def from_course
    project_params = (Lms::Project::ProjectParams).from_course(params[:course_unit_id], params[:academic_year_id])
    respond_with_object(project_params)
  end

end
