class Core::CourseUnitController < ApplicationController
  # get "/core/CourseUnit/:id", :controller => "Core::CourseUnit", :action => "show"
  def show
    course_unit = (Core::CourseUnit).find(params[:id])
    respond_with_object(course_unit)
  end
end
